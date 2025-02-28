<?php

namespace App\Mail;

use App\Models\PropertyInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyInquiryCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PropertyInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Property Inquiry Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.property-inquiry-created',
            with: [
                'inquiry' => $this->inquiry,
            ],
        );
    }
}
