<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUsersController extends Controller
{
    /**
     * Display a listing of admin users.
     */
    public function index(): Response
    {
        // Get all users with admin role
        $adminUsers = User::where('role', UserRole::ADMIN)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'county' => $user->county,
                    'profile_picture' => $user->profile_picture,
                    'is_active' => $user->is_active,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

        // Calculate stats
        $stats = [
            'total' => $adminUsers->count(),
            'active' => $adminUsers->where('is_active', true)->count(),
            'inactive' => $adminUsers->where('is_active', false)->count(),
            'recent_logins' => $adminUsers->filter(function ($user) {
                return $user['last_login_at'] && 
                       $user['last_login_at']->isAfter(now()->subDays(7));
            })->count(),
        ];

        return Inertia::render('Admin/AdminUsers/Index', [
            'adminUsers' => $adminUsers,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/AdminUsers/Create', [
            'counties' => $this->getCounties(),
        ]);
    }

    /**
     * Store a newly created admin user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'county' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => UserRole::ADMIN,
            'county' => $validated['county'],
            'phone' => $validated['phone'],
            'organization' => $validated['organization'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Log the creation
        auth()->user()->logActivity('admin_user_created', "Created admin user: {$user->name}", [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user created successfully.');
    }

    /**
     * Show the form for editing the specified admin user.
     */
    public function edit(User $user): Response
    {
        // Ensure the user is an admin
        if ($user->role !== UserRole::ADMIN) {
            abort(404);
        }

        return Inertia::render('Admin/AdminUsers/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'county' => $user->county,
                'phone' => $user->phone,
                'organization' => $user->organization,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ],
            'counties' => $this->getCounties(),
        ]);
    }

    /**
     * Update the specified admin user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Ensure the user is an admin
        if ($user->role !== UserRole::ADMIN) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'county' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        // Log the update
        auth()->user()->logActivity('admin_user_updated', "Updated admin user: {$user->name}", [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user updated successfully.');
    }

    /**
     * Toggle the active status of the specified admin user.
     */
    public function toggleStatus(Request $request, User $user)
    {
        // Ensure the user is an admin
        if ($user->role !== UserRole::ADMIN) {
            abort(404);
        }

        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $user->update(['is_active' => $validated['is_active']]);

        $status = $validated['is_active'] ? 'activated' : 'deactivated';
        
        // Log the status change
        auth()->user()->logActivity('admin_user_status_changed', "Admin user {$status}: {$user->name}", [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'new_status' => $validated['is_active'],
        ]);

        return back()->with('success', "Admin user {$status} successfully.");
    }

    /**
     * Remove the specified admin user from storage.
     */
    public function destroy(User $user)
    {
        // Ensure the user is an admin
        if ($user->role !== UserRole::ADMIN) {
            abort(404);
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Log the deletion before deleting
        auth()->user()->logActivity('admin_user_deleted', "Deleted admin user: {$user->name}", [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        $user->delete();

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user deleted successfully.');
    }

    /**
     * Get list of locations.
     */
    private function getCounties(): array
    {
        // Return a generic list of locations instead of Kenya-specific counties
        return [
            'Africa', 'Asia', 'Europe', 'North America', 'South America',
            'Australia', 'Middle East', 'Remote/Online'
        ];
    }
}