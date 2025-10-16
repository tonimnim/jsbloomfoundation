<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request): Response
    {
        $query = User::query();
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('county', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%");
            });
        }
        
        // Filter by role
        if ($request->has('role') && $request->get('role')) {
            $query->where('role', $request->get('role'));
        }
        
        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Filter by verification
        if ($request->has('verified')) {
            $verified = $request->get('verified');
            if ($verified === 'yes') {
                $query->whereNotNull('email_verified_at');
            } elseif ($verified === 'no') {
                $query->whereNull('email_verified_at');
            }
        }
        
        $users = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->value ?? 'user', // Convert enum to string
                    'role_label' => $user->role?->label() ?? 'User',
                    'county' => $user->county,
                    'phone' => $user->phone,
                    'organization' => $user->organization,
                    'is_active' => $user->is_active,
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                ];
            })
            ->withQueryString();
        
        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
        ];
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role', 'status', 'verified']),
        ]);
    }
    
    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value ?? 'user',
                'role_label' => $user->role?->label() ?? 'User',
                'county' => $user->county,
                'phone' => $user->phone,
                'organization' => $user->organization,
                'is_active' => $user->is_active,
                'email_verified_at' => $user->email_verified_at,
                'profile_picture' => $user->profile_picture,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'last_login_at' => $user->last_login_at,
            ],
        ]);
    }
    
    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value ?? 'user',
                'county' => $user->county,
                'phone' => $user->phone,
                'organization' => $user->organization,
                'is_active' => $user->is_active,
            ],
            'counties' => $this->getCounties(),
            'roles' => ['user', 'admin'],
        ]);
    }
    
    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin',
            'county' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        // Prevent changing your own role or status
        if ($user->id === auth()->id()) {
            unset($validated['role']);
            unset($validated['is_active']);
        }
        
        $user->update($validated);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }
    
    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }
        
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "User {$status} successfully.");
    }
    
    /**
     * Delete the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
    
    /**
     * Verify user email manually.
     */
    public function verifyEmail(User $user)
    {
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        
        return back()->with('success', 'Email verified successfully.');
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