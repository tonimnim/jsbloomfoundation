<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminProfileController extends Controller
{
    /**
     * Display the admin's profile.
     */
    public function show(): Response
    {
        $user = auth()->user();
        
        return Inertia::render('Admin/Profile/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'county' => $user->county,
                'organization' => $user->organization,
                'address' => $user->address,
                'profile_picture' => $user->profile_picture,
                'role' => $user->role->value,
                'last_login_at' => $user->last_login_at?->format('M d, Y g:i A'),
                'created_at' => $user->created_at->format('M d, Y'),
                'newsletter_subscribed' => $user->newsletter_subscribed,
                'email_notifications' => $user->email_notifications,
                'sms_notifications' => $user->sms_notifications,
                'preferred_causes' => $user->preferred_causes ?? [],
                'total_donations' => $user->isUser() ? $user->total_donated : null,
                'last_donation' => $user->isUser() ? $user->last_donation?->created_at?->format('M d, Y') : null,
            ],
            'counties' => $this->getCounties(),
        ]);
    }

    /**
     * Show the form for editing the admin's profile.
     */
    public function edit(): Response
    {
        $user = auth()->user();
        
        return Inertia::render('Admin/Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'county' => $user->county,
                'organization' => $user->organization,
                'address' => $user->address,
                'profile_picture' => $user->profile_picture,
                'newsletter_subscribed' => $user->newsletter_subscribed,
                'email_notifications' => $user->email_notifications,
                'sms_notifications' => $user->sms_notifications,
                'preferred_causes' => $user->preferred_causes ?? [],
            ],
            'counties' => $this->getCounties(),
            'causes' => $this->getAvailableCauses(),
        ]);
    }

    /**
     * Update the admin's profile information.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $validated['profile_picture'] = $path;
        }

        // Update user information
        $user->update($validated);

        // Log the profile update activity
        $user->logActivity('profile_updated', 'Profile information updated');

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the admin's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log the password change activity
        $user->logActivity('password_changed', 'Password was changed');

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Delete profile picture.
     */
    public function deleteProfilePicture()
    {
        $user = auth()->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->update(['profile_picture' => null]);
            
            // Log the activity
            $user->logActivity('profile_picture_deleted', 'Profile picture was deleted');
        }

        return back()->with('success', 'Profile picture deleted successfully.');
    }

    /**
     * Get locations for dropdown.
     */
    private function getCounties(): array
    {
        // Return a generic list of locations instead of Kenya-specific counties
        return [
            'Africa', 'Asia', 'Europe', 'North America', 'South America',
            'Australia', 'Middle East', 'Remote/Online'
        ];
    }

    /**
     * Get available causes for user preferences.
     */
    private function getAvailableCauses(): array
    {
        return [
            'Harvest of Tomorrow Initiative',
            'Waste to Art',
            'Community Paralegal Office',
            'Voice of the Silent Workers',
            'Toto Smart Move',
            'Sanitary Pad Drives',
            'Tree Planting Campaigns',
            'Community Clean-up Campaigns',
        ];
    }
}