<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

class NotificationsController extends Controller
{
    /**
     * Get notifications for the current admin user
     */
    public function index()
    {
        $userId = Auth::id();
        
        // Get recent notifications (last 30 days or last 50 notifications)
        $notifications = DB::table('notifications')
            ->where(function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhereNull('user_id'); // Include system-wide notifications
            })
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
            
        // Format notifications for frontend
        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'time' => Carbon::parse($notification->created_at)->diffForHumans(),
                'unread' => !$notification->is_read,
                'action_url' => $notification->action_url,
                'priority' => $notification->priority,
                'data' => json_decode($notification->data, true),
            ];
        });
        
        return response()->json([
            'notifications' => $formattedNotifications,
            'unread_count' => $notifications->where('is_read', false)->count(),
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->where(function($query) {
                $query->where('user_id', Auth::id())
                      ->orWhereNull('user_id');
            })
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            
        return response()->json(['success' => true]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        DB::table('notifications')
            ->where(function($query) {
                $query->where('user_id', Auth::id())
                      ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            
        return response()->json(['success' => true]);
    }
    
    /**
     * Create notification helper (called from other controllers/services)
     */
    public static function createNotification($type, $title, $message, $options = [])
    {
        $data = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'user_id' => $options['user_id'] ?? null,
            'related_id' => $options['related_id'] ?? null,
            'related_type' => $options['related_type'] ?? null,
            'action_url' => $options['action_url'] ?? null,
            'priority' => $options['priority'] ?? 'normal',
            'data' => isset($options['data']) ? json_encode($options['data']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        return DB::table('notifications')->insert($data);
    }
    
    /**
     * Generate notifications based on recent activities
     */
    public static function generateRecentNotifications()
    {
        // Check for new donations in the last hour
        $recentDonations = DB::table('donations')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->where('status', 'completed')
            ->get();
            
        foreach ($recentDonations as $donation) {
            // Check if notification already exists
            $exists = DB::table('notifications')
                ->where('type', 'donation')
                ->where('related_id', $donation->id)
                ->where('related_type', 'donation')
                ->exists();
                
            if (!$exists) {
                $amount = number_format($donation->amount, 0);
                $donor = $donation->donor_name ?: 'Anonymous';
                
                self::createNotification(
                    'donation',
                    'New Donation Received',
                    "KES {$amount} donation from {$donor}",
                    [
                        'related_id' => $donation->id,
                        'related_type' => 'donation',
                        'action_url' => '/admin/donations/' . $donation->id,
                        'priority' => $donation->amount > 50000 ? 'high' : 'normal',
                        'data' => [
                            'amount' => $donation->amount,
                            'currency' => $donation->currency,
                            'payment_method' => $donation->payment_method,
                        ]
                    ]
                );
            }
        }

        // Check for new contact submissions
        $recentContacts = DB::table('contact_submissions')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->where('is_read', false)
            ->get();
            
        foreach ($recentContacts as $contact) {
            $exists = DB::table('notifications')
                ->where('type', 'contact')
                ->where('related_id', $contact->id)
                ->where('related_type', 'contact_submission')
                ->exists();
                
            if (!$exists) {
                self::createNotification(
                    'contact',
                    'New Contact Form Submission',
                    "{$contact->subject} from {$contact->first_name} {$contact->last_name}",
                    [
                        'related_id' => $contact->id,
                        'related_type' => 'contact_submission',
                        'action_url' => '/admin/contacts/' . $contact->id,
                        'priority' => isset($contact->inquiry_type) && $contact->inquiry_type === 'urgent' ? 'high' : 'normal',
                        'data' => [
                            'sender_email' => $contact->email,
                            'inquiry_type' => $contact->inquiry_type ?? 'general',
                        ]
                    ]
                );
            }
        }
        
        // Check for new newsletter subscribers
        $newSubscribers = DB::table('newsletter_subscribers')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->where('is_active', true)
            ->count();
            
        if ($newSubscribers > 0) {
            $lastNotification = DB::table('notifications')
                ->where('type', 'newsletter')
                ->where('created_at', '>=', Carbon::now()->subHour())
                ->first();
                
            if (!$lastNotification) {
                self::createNotification(
                    'newsletter',
                    'New Newsletter Subscribers',
                    "{$newSubscribers} new subscriber(s) joined the newsletter",
                    [
                        'action_url' => '/admin/newsletter',
                        'data' => ['count' => $newSubscribers]
                    ]
                );
            }
        }
    }
}