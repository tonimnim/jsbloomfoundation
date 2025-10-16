<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewBlogPostNotification;

class SendBlogPostNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;
    protected $subscribers;

    /**
     * Create a new job instance.
     */
    public function __construct($post, $subscribers)
    {
        $this->post = $post;
        $this->subscribers = $subscribers;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->subscribers as $subscriber) {
            try {
                // Use the notification system
                Notification::route('mail', $subscriber->email)
                    ->notify(new NewBlogPostNotification($this->post, $subscriber->unsubscribe_token));
            } catch (\Exception $e) {
                // Log error but continue with other subscribers
                \Log::error('Failed to send notification to ' . $subscriber->email . ': ' . $e->getMessage());
            }
        }
    }
}