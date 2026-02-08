<?php

namespace App\Mail;

use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class ApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public Approval $approval;
    public $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(Approval $approval)
    {
        $this->approval = $approval->load([
            'patient',
            'invoice.items.service',
            'insuranceCompany',
            'charityEntity',
            'requestedBy'
        ]);
        
        // Load system settings (logo, IBAN, etc.)
        $this->settings = \App\Models\Setting::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->approval->approval_type === 'insurance' 
            ? 'Insurance Approval Request - ' . $this->approval->approval_number
            : 'Charity Approval Request - ' . $this->approval->approval_number;
            
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.approval-request',
            with: [
                'approval' => $this->approval,
                'patient' => $this->approval->patient,
                'invoice' => $this->approval->invoice,
                'company' => $this->approval->insuranceCompany ?? $this->approval->charityEntity,
                'settings' => $this->settings,
                'approveUrl' => route('approvals.respond', ['token' => $this->approval->approval_token, 'action' => 'approve']),
                'rejectUrl' => route('approvals.respond', ['token' => $this->approval->approval_token, 'action' => 'reject']),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        // Attach medical reports from approval
        foreach ($this->approval->getMedia('medical-reports') as $media) {
            $attachments[] = Attachment::fromPath($media->getPath())
                ->as($media->file_name)
                ->withMime($media->mime_type);
        }
        
        // Attach patient data documents
        foreach ($this->approval->getMedia('patient-data') as $media) {
            $attachments[] = Attachment::fromPath($media->getPath())
                ->as($media->file_name)
                ->withMime($media->mime_type);
        }
        
        return $attachments;
    }
}
