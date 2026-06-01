<?php

namespace App\Mail;

use App\Models\AccountantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountantInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountantInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to join as an Accountant',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.accountant-invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
