<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CharityServicesCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
        $this->invoice->load([
            'patient.charityEntity',
            'items.service',
            'items.completedByUser',
        ]);
    }

    public function envelope(): Envelope
    {
        $invoiceNumber = $this->invoice->invoice_number;
        $patientName   = $this->invoice->patient?->name ?? '';
        $subject = "إشعار اكتمال تنفيذ الخدمات — فاتورة {$invoiceNumber} — {$patientName}";
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $settings = [
            'hospital_name' => Setting::get('hospital_name', ''),
            'logo' => Setting::get('logo', ''),
            'manager_signature' => Setting::get('manager_signature', ''),
            'stamp' => Setting::get('stamp', ''),
        ];

        $manager = User::getManagerForSignature();

        return new Content(
            view: 'emails.charity-services-completed',
            with: [
                'invoice' => $this->invoice,
                'settings' => $settings,
                'manager' => $manager,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
