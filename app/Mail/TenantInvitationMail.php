<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Central\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TenantInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly TenantInvitation $invitation,
        public readonly string $invitationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Zaproszenie do SoundBased — skonfiguruj swoją organizację',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-invitation',
            with: [
                'invitationUrl' => $this->invitationUrl,
                'expiresAt' => $this->invitation->expires_at->format('d.m.Y H:i'),
            ],
        );
    }

    /** @return array<int, \Illuminate\Mail\Mailables\Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
