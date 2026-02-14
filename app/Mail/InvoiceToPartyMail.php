<?php

namespace App\Mail;

use App\Models\InvoicePartySend;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceToPartyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InvoicePartySend $partySend,
        public ?string $pdfPath = null
    ) {
        $this->partySend->load(['invoice.patient', 'invoice.items.service']);
    }

    public function envelope(): Envelope
    {
        $inv = $this->partySend->invoice;
        $subject = (app()->getLocale() === 'ar' ? 'عرض سعر / فاتورة ' : 'Price offer / Invoice ') . $inv->invoice_number;
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-to-party',
            with: [
                'partySend' => $this->partySend,
                'confirmUrl' => route('invoice-party-response.show', ['token' => $this->partySend->token, 'action' => 'confirm']),
                'rejectUrl' => route('invoice-party-response.show', ['token' => $this->partySend->token, 'action' => 'reject']),
            ]
        );
    }

    public function attachments(): array
    {
        if ($this->pdfPath && \Illuminate\Support\Facades\Storage::disk('local')->exists($this->pdfPath)) {
            return [
                Attachment::fromStorageDisk('local', $this->pdfPath)
                    ->as('price-offer-' . $this->partySend->invoice->invoice_number . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }
        return [];
    }
}
