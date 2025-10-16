<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBlogPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $unsubscribeToken;

    /**
     * Create a new notification instance.
     */
    public function __construct($post, $unsubscribeToken)
    {
        $this->post = $post;
        $this->unsubscribeToken = $unsubscribeToken;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $postUrl = url('/blog/' . $this->post->slug);
        $unsubscribeUrl = url('/newsletter/unsubscribe/' . $this->unsubscribeToken);

        return (new MailMessage)
            ->subject('New Post: ' . $this->post->title)
            ->greeting('Hello!')
            ->line('We have published a new blog post that might interest you:')
            ->line('')
            ->line('**' . $this->post->title . '**')
            ->line($this->post->excerpt ?: 'Click below to read the full article.')
            ->action('Read More', $postUrl)
            ->line('')
            ->line('Category: ' . ($this->post->category_name ?? 'General'))
            ->line('Author: ' . ($this->post->author_name ?? 'Ustawi Wa Jamii Team'))
            ->line('')
            ->line('Thank you for being part of the Ustawi Wa Jamii community!')
            ->salutation('Best regards, Ustawi Wa Jamii Team')
            ->line('')
            ->line('[Unsubscribe](' . $unsubscribeUrl . ') from our newsletter');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
        ];
    }
}