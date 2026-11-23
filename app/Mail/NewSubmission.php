<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notification interne : nouveau message / nouvelle candidature reçus via le site. */
class NewSubmission extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array<string, ?string> $lines */
    public function __construct(
        public string $title,
        public array $lines,
        public string $replyToAddress,
        public string $adminUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Site ELC] '.$this->title,
            replyTo: [new Address($this->replyToAddress)],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.submission');
    }
}
