<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);
        
        // Check if already subscribed
        $existing = DB::table('newsletter_subscribers')
            ->where('email', $validated['email'])
            ->first();
            
        if ($existing) {
            if ($existing->is_active) {
                return back()->with('info', 'You are already subscribed to our newsletter!');
            } else {
                // Reactivate subscription
                DB::table('newsletter_subscribers')
                    ->where('id', $existing->id)
                    ->update([
                        'is_active' => true,
                        'subscribed_at' => now(),
                        'unsubscribed_at' => null,
                        'updated_at' => now(),
                    ]);
                    
                return back()->with('success', 'Welcome back! Your subscription has been reactivated.');
            }
        }
        
        // Create new subscription
        $unsubscribeToken = Str::random(32);
        
        DB::table('newsletter_subscribers')->insert([
            'email' => $validated['email'],
            'name' => $validated['name'] ?? null,
            'is_active' => true,
            'unsubscribe_token' => $unsubscribeToken,
            'subscribed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Queue welcome email (don't send synchronously to avoid slow response)
        try {
            Mail::to($validated['email'])->queue(new \App\Mail\NewsletterWelcome($validated['email'], $unsubscribeToken));
        } catch (\Exception $e) {
            // Log error but don't fail the subscription
            \Log::error('Failed to queue welcome email: ' . $e->getMessage());
        }
        
        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }
    
    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe($token)
    {
        $subscriber = DB::table('newsletter_subscribers')
            ->where('unsubscribe_token', $token)
            ->where('is_active', true)
            ->first();
            
        if (!$subscriber) {
            return inertia('Newsletter/UnsubscribeError');
        }
        
        DB::table('newsletter_subscribers')
            ->where('id', $subscriber->id)
            ->update([
                'is_active' => false,
                'unsubscribed_at' => now(),
                'updated_at' => now(),
            ]);
            
        return inertia('Newsletter/UnsubscribeSuccess', [
            'email' => $subscriber->email,
        ]);
    }
}