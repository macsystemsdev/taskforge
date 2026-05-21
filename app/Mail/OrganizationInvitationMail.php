<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Models\OrganizationUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
      public function __construct(
        public Invitation $invitation
    ) {}


    /**
     * Get the message content definition.
     */
     public function build()
    {
        return $this->subject('Organization Invitation')
            ->view('emails.organization_invitation');
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
