<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CharityPaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
        $this->invoice->load(['patient.charityEntity']);
    }

    public function envelope(): Envelope
    {
        $invoiceNumber = $this->invoice->invoice_number;
        $subject = "تذكير بالسداد — فاتورة {$invoiceNumber}";
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $settings = [
            'hospital_name' => Setting::get('hospital_name', ''),
            'bank_name' => Setting::get('bank_name', ''),
            'account_number' => Setting::get('account_number', ''),
            'iban_number' => Setting::get('iban_number', ''),
        ];

        return new Content(
            view: 'emails.charity-payment-reminder',
            with: [
                'invoice' => $this->invoice,
                'settings' => $settings,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
