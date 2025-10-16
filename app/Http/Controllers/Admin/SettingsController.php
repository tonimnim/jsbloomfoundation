<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): Response
    {
        // Get all settings from database or return defaults
        $settings = $this->getSettings();
        
        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'counties' => $this->getCounties(),
        ]);
    }

    /**
     * Update specific settings section.
     */
    public function update(Request $request, string $section)
    {
        switch ($section) {
            case 'organization':
                return $this->updateOrganizationSettings($request);
            case 'donations':
                return $this->updateDonationSettings($request);
            case 'email':
                return $this->updateEmailSettings($request);
            case 'social':
                return $this->updateSocialSettings($request);
            case 'homepage':
                return $this->updateHomepageSettings($request);
            default:
                return back()->withErrors(['section' => 'Invalid settings section']);
        }
    }

    /**
     * Update organization settings.
     */
    private function updateOrganizationSettings(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_description' => 'nullable|string|max:1000',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'physical_address' => 'nullable|string|max:500',
            'mission_statement' => 'nullable|string|max:1000',
        ]);

        foreach ($validated as $key => $value) {
            $this->updateSetting($key, $value);
        }

        return back()->with('success', 'Organization settings updated successfully.');
    }

    /**
     * Update donation settings.
     */
    private function updateDonationSettings(Request $request)
    {
        $validated = $request->validate([
            'mpesa_paybill' => 'required|string|max:10',
            'default_amounts' => 'required|array|min:1',
            'default_amounts.*' => 'required|integer|min:1',
            'receipt_email_template' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            $this->updateSetting($key, $value);
        }

        return back()->with('success', 'Donation settings updated successfully.');
    }

    /**
     * Update email settings.
     */
    private function updateEmailSettings(Request $request)
    {
        $validated = $request->validate([
            'from_email' => 'required|email|max:255',
            'contact_form_recipient' => 'required|email|max:255',
            'admin_email' => 'required|email|max:255',
            'support_email' => 'nullable|email|max:255',
            'donations_email' => 'nullable|email|max:255',
            'partnerships_email' => 'nullable|email|max:255',
            'email_signature' => 'nullable|string|max:1000',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl,none',
        ]);

        foreach ($validated as $key => $value) {
            $this->updateSetting($key, $value);
        }

        return back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Update social media settings.
     */
    private function updateSocialSettings(Request $request)
    {
        $validated = $request->validate([
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'whatsapp_url' => 'nullable|url|max:255',
        ]);

        foreach ($validated as $key => $value) {
            $this->updateSetting($key, $value);
        }

        return back()->with('success', 'Social media settings updated successfully.');
    }

    /**
     * Update homepage images.
     */
    private function updateHomepageSettings(Request $request)
    {
        try {
            $request->validate([
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:20480',
                'existing_images' => 'nullable|string',
            ]);

            $uploadedImages = [];
            
            // Handle existing images to keep
            $existingImages = [];
            if ($request->has('existing_images')) {
                $existingImagesRaw = json_decode($request->existing_images, true);
                $existingImages = is_array($existingImagesRaw) ? $existingImagesRaw : [];
            }

        // Process new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                if ($file && $file->isValid()) {
                    $filename = time() . '_' . $index . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $path = $file->storeAs('homepage', $filename, 'public');
                    $url = \Storage::disk('public')->url($path);
                    
                    $uploadedImages[] = [
                        'id' => uniqid() . '_' . time(),
                        'filename' => $filename,
                        'path' => $path,
                        'url' => $url,
                        'original_name' => $file->getClientOriginalName(),
                        'uploaded_at' => now()->toISOString(),
                    ];
                }
            }
        }

        // Get current homepage images
        $currentImages = [];
        try {
            $currentImagesJson = \DB::table('site_settings')
                ->where('key', 'homepage_images')
                ->value('value');
            if ($currentImagesJson) {
                $currentImages = json_decode($currentImagesJson, true) ?: [];
            }
        } catch (\Exception $e) {
            // If table doesn't exist or error, start with empty array
            $currentImages = [];
        }

        // Filter current images to keep only existing ones
        $filteredCurrentImages = [];
        if (!empty($existingImages) && !empty($currentImages)) {
            $filteredCurrentImages = array_filter($currentImages, function($image) use ($existingImages) {
                return isset($image['id']) && in_array($image['id'], $existingImages);
            });
        }

        // Combine existing and new images
        $allImages = array_merge(array_values($filteredCurrentImages), $uploadedImages);
        
        // Enforce maximum of 4 images
        if (count($allImages) > 4) {
            // Clean up any uploaded images if limit exceeded
            foreach ($uploadedImages as $uploaded) {
                \Storage::disk('public')->delete($uploaded['path']);
            }
            return back()->withErrors(['images' => 'Maximum 4 images allowed on homepage.']);
        }

        // Save updated images list
        $this->updateSetting('homepage_images', $allImages);

        // Clean up removed image files from bucket
        if (!empty($currentImages)) {
            foreach ($currentImages as $currentImage) {
                if (isset($currentImage['id']) && !in_array($currentImage['id'], $existingImages)) {
                    // Delete the file from storage bucket
                    if (isset($currentImage['path'])) {
                        \Storage::disk('public')->delete($currentImage['path']);
                    } elseif (isset($currentImage['filename'])) {
                        // Fallback to filename if path not set
                        \Storage::disk('public')->delete('homepage/' . $currentImage['filename']);
                    }
                }
            }
        }

        $message = count($uploadedImages) > 0 
            ? 'Homepage images uploaded successfully.' 
            : 'Homepage images updated successfully.';

        return back()->with('success', $message);
        
        } catch (\Exception $e) {
            \Log::error('Homepage settings update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update homepage settings: ' . $e->getMessage()]);
        }
    }


    /**
     * Get all settings from database or return defaults.
     */
    private function getSettings(): array
    {
        // This would typically fetch from a settings table
        // For now, return defaults with any existing values
        $settings = [];
        
        // Try to get settings from database, handle if table doesn't exist
        try {
            $dbSettings = \DB::table('site_settings')->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            // If table doesn't exist or other error, use empty array
            $dbSettings = [];
        }
        
        // Default settings with database overrides
        $defaults = [
            'organization_name' => 'JS Bloom Foundation',
            'organization_description' => 'Empowering children, youth, and the elderly to bloom into their full potential.',
            'contact_email' => 'info@jsbloomfoundation.org',
            'contact_phone' => '+234 800 000 0000',
            'physical_address' => 'Lagos, Nigeria',
            'mission_statement' => 'To empower the most vulnerable members of our communities—children, youth, and the elderly—through essential resources, education, and care that create lasting positive impact.',
            'mpesa_paybill' => '',
            'default_amounts' => [500, 1000, 2500, 5000],
            'receipt_email_template' => "Dear {donor_name},\n\nThank you for your generous donation of NGN {amount} to JS Bloom Foundation.\n\nYour support helps us continue our mission of empowering children, youth, and the elderly.\n\nBest regards,\nJS Bloom Foundation Team",
            'from_email' => 'noreply@jsbloomfoundation.org',
            'contact_form_recipient' => 'info@jsbloomfoundation.org',
            'admin_email' => 'admin@jsbloomfoundation.org',
            'support_email' => 'support@jsbloomfoundation.org',
            'donations_email' => 'donations@jsbloomfoundation.org',
            'partnerships_email' => 'partnerships@jsbloomfoundation.org',
            'email_signature' => "Best regards,\nJS Bloom Foundation Team\nEmail: info@jsbloomfoundation.org\nWebsite: www.jsbloomfoundation.org",
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'youtube_url' => '',
            'tiktok_url' => '',
            'whatsapp_url' => '',
            'homepage_images' => [],
        ];

        foreach ($defaults as $key => $defaultValue) {
            $settings[$key] = $dbSettings[$key] ?? $defaultValue;
            
            // Handle JSON fields
            if (in_array($key, ['default_amounts', 'homepage_images']) && is_string($settings[$key])) {
                $settings[$key] = json_decode($settings[$key], true) ?: $defaultValue;
            }
        }

        // Fix homepage image URLs (same approach as gallery)
        if (!empty($settings['homepage_images']) && is_array($settings['homepage_images'])) {
            foreach ($settings['homepage_images'] as &$image) {
                if (isset($image['url']) && !str_starts_with($image['url'], 'https://')) {
                    if (isset($image['path'])) {
                        $image['url'] = \Storage::disk('public')->url($image['path']);
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * Update a single setting in the database.
     */
    private function updateSetting(string $key, $value): void
    {
        try {
            // Convert arrays to JSON
            $originalValue = $value;
            if (is_array($value)) {
                $value = json_encode($value);
            }

            \DB::table('site_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => is_array($originalValue) ? 'json' : (is_numeric($value) ? 'number' : 'text'),
                    'group' => $this->getSettingGroup($key),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            // If there's an error (like table doesn't exist), log it but don't fail
            \Log::warning("Could not update setting {$key}: " . $e->getMessage());
        }
    }

    /**
     * Get the group for a setting key.
     */
    private function getSettingGroup(string $key): string
    {
        $groups = [
            'organization_name' => 'organization',
            'organization_description' => 'organization',
            'contact_email' => 'organization',
            'contact_phone' => 'organization',
            'physical_address' => 'organization',
            'mission_statement' => 'organization',
            'mpesa_paybill' => 'donations',
            'default_amounts' => 'donations',
            'receipt_email_template' => 'donations',
            'from_email' => 'email',
            'contact_form_recipient' => 'email',
            'admin_email' => 'email',
            'support_email' => 'email',
            'donations_email' => 'email',
            'partnerships_email' => 'email',
            'email_signature' => 'email',
            'smtp_host' => 'email',
            'smtp_port' => 'email',
            'smtp_username' => 'email',
            'smtp_password' => 'email',
            'smtp_encryption' => 'email',
            'facebook_url' => 'social',
            'twitter_url' => 'social',
            'instagram_url' => 'social',
            'linkedin_url' => 'social',
            'youtube_url' => 'social',
            'tiktok_url' => 'social',
            'whatsapp_url' => 'social',
            'homepage_images' => 'homepage',
        ];

        return $groups[$key] ?? 'general';
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