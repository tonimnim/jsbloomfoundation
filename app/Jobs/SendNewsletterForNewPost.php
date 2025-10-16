<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewBlogPostNotification;

class SendNewsletterForNewPost implements ShouldQueue
{
    use Queueable;

    protected $post;

    /**
     * Create a new job instance.
     */
    public function __construct($post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all active newsletter subscribers
        $subscribers = DB::table('newsletter_subscribers')
            ->where('is_active', true)
            ->get();

        // Send notification to each subscriber
        foreach ($subscribers as $subscriber) {
            // Create a notifiable object for the subscriber
            $notifiable = new class($subscriber->email) {
                public $email;
                
                public function __construct($email)
                {
                    $this->email = $email;
                }
                
                public function routeNotificationForMail()
                {
                    return $this->email;
                }
            };

            // Send the notification
            Notification::send($notifiable, new NewBlogPostNotification($this->post, $subscriber->unsubscribe_token));
        }
    }
}
