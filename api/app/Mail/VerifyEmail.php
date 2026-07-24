<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

final class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {
        $this->onQueue('mailers');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Подтвердите ваш email в Sentiment Analyzer',
            tags: ['verification', 'transactional'],
            metadata: [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
        );

        return new Content(
            view: 'emails.verify',
            with: [
                'verificationUrl' => $verificationUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
