<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcome extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $email;
    public $unsubscribeToken;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $unsubscribeToken)
    {
        $this->email = $email;
        $this->unsubscribeToken = $unsubscribeToken;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Ustawi Wa Jamii Newsletter',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.welcome',
            with: [
                'unsubscribeUrl' => url('/newsletter/unsubscribe/' . $this->unsubscribeToken),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
