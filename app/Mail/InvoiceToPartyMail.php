<?php

namespace App\Mail;

use App\Models\InvoicePartySend;
use App\Models\Setting;
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
        $settings = [
            'hospital_name' => Setting::get('hospital_name', ''),
            'hospital_name_en' => Setting::get('hospital_name_en', ''),
            'health_cluster_name' => Setting::get('health_cluster_name', ''),
            'health_cluster_name_en' => Setting::get('health_cluster_name_en', ''),
            'manager_name' => Setting::get('manager_name', ''),
            'logo' => Setting::get('logo', ''),
            'bank_name' => Setting::get('bank_name', ''),
            'account_number' => Setting::get('account_number', ''),
            'iban_number' => Setting::get('iban_number', ''),
            'manager_signature' => Setting::get('manager_signature', ''),
            'department_manager_name' => Setting::get('department_manager_name', ''),
            'department_manager_signature' => Setting::get('department_manager_signature', ''),
        ];

        return new Content(
            view: 'emails.invoice-to-party',
            with: [
                'partySend' => $this->partySend,
                'settings' => $settings,
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
