<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 20px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 5px 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 18px; font-weight: bold; color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 8px; margin-bottom: 15px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 15px; }
        .info-item { padding: 10px; background: #f8f9fa; border-radius: 5px; }
        .info-label { font-weight: bold; color: #555; font-size: 12px; text-transform: uppercase; }
        .info-value { color: #333; font-size: 16px; margin-top: 5px; }
        .services-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .services-table th { background: #667eea; color: #fff; padding: 12px; text-align: left; font-size: 14px; }
        .services-table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .services-table tr:hover { background: #f8f9fa; }
        .total-row { background: #f1f3f5; font-weight: bold; font-size: 18px; }
        .total-row td { padding: 15px 12px; border-top: 2px solid #667eea; }
        .action-buttons { display: flex; gap: 15px; justify-content: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; text-align: center; }
        .btn-approve { background: #28a745; color: #fff; }
        .btn-reject { background: #dc3545; color: #fff; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-insurance { background: #3498db; color: #fff; }
        .badge-charity { background: #e67e22; color: #fff; }
        .badge-multi { background: #9b59b6; color: #fff; }
        @media only screen and (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .services-table { font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            @if($settings && $settings->hospital_logo)
                <img src="{{ asset('storage/' . $settings->hospital_logo) }}" alt="Hospital Logo" style="max-height: 60px; margin-bottom: 15px;">
            @endif
            <h1>🏥 {{ $approval->approval_type === 'insurance' ? 'Insurance' : 'Charity' }} Approval Request</h1>
            <p>{{ $settings->hospital_name ?? 'Hospital Management System' }}</p>
        </div>
        
        {{-- Content --}}
        <div class="content">
            {{-- Approval Information --}}
            <div class="section">
                <div class="section-title">📋 Approval Details</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Approval Number</div>
                        <div class="info-value">{{ $approval->approval_number }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Request Date</div>
                        <div class="info-value">{{ $approval->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Type</div>
                        <div class="info-value">
                            <span class="badge {{ $approval->approval_type === 'insurance' ? 'badge-insurance' : 'badge-charity' }}">
                                {{ strtoupper($approval->approval_type) }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Requested By</div>
                        <div class="info-value">{{ $approval->requestedBy->name }}</div>
                    </div>
                </div>
            </div>
            
            {{-- Patient Information --}}
            <div class="section">
                <div class="section-title">👤 Patient Information</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Name</div>
                        <div class="info-value">{{ $patient->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">File Number</div>
                        <div class="info-value">{{ $patient->file_number }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ID / Iqama / Passport</div>
                        <div class="info-value">{{ $patient->id_number ?? $patient->iqama_number ?? $patient->passport_number ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $patient->phone ?? '-' }}</div>
                    </div>
                    @if($patient->age)
                        <div class="info-item">
                            <div class="info-label">Age</div>
                            <div class="info-value">{{ $patient->age }} years</div>
                        </div>
                    @endif
                    @if($patient->gender)
                        <div class="info-item">
                            <div class="info-label">Gender</div>
                            <div class="info-value">{{ ucfirst($patient->gender) }}</div>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Services Requested --}}
            <div class="section">
                <div class="section-title">🏥 Services Requested</div>
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Code</th>
                            <th style="text-align: center">Qty</th>
                            <th style="text-align: right">Unit Price</th>
                            <th style="text-align: right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>
                                    {{ $item->service->name }}
                                    @if($item->service->is_multi_session)
                                        <br>
                                        <span class="badge badge-multi">
                                            Multi-Session: {{ $item->service->session_count }} sessions 
                                            ({{ $item->service->session_wait_time }} {{ $item->service->session_wait_unit }})
                                        </span>
                                    @endif
                                    @if($item->description)
                                        <br><small style="color: #666;">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td><strong>{{ $item->service->code ?? '-' }}</strong></td>
                                <td style="text-align: center">{{ $item->quantity }}</td>
                                <td style="text-align: right">{{ number_format($item->unit_price, 2) }} SAR</td>
                                <td style="text-align: right"><strong>{{ number_format($item->total_price, 2) }} SAR</strong></td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right">TOTAL AMOUNT REQUESTED:</td>
                            <td style="text-align: right">{{ number_format($approval->requested_amount, 2) }} SAR</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            {{-- Hospital Staff Information --}}
            <div class="section">
                <div class="section-title">👥 Hospital Staff</div>
                <div class="info-grid">
                    @if($settings && $settings->manager_name)
                        <div class="info-item">
                            <div class="info-label">Hospital Manager</div>
                            <div class="info-value">{{ $settings->manager_name }}</div>
                        </div>
                    @endif
                    <div class="info-item">
                        <div class="info-label">Request Created By</div>
                        <div class="info-value">{{ $approval->requestedBy->name }}</div>
                    </div>
                    @if($approval->requestedBy->employee)
                        <div class="info-item">
                            <div class="info-label">Employee Position</div>
                            <div class="info-value">{{ $approval->requestedBy->employee->job_title ?? 'Staff' }}</div>
                        </div>
                    @endif
                    <div class="info-item">
                        <div class="info-label">Request Time</div>
                        <div class="info-value">{{ $approval->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>
            
            @if($approval->notes)
                <div class="section">
                    <div class="section-title">📝 Additional Notes</div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #667eea;">
                        {{ $approval->notes }}
                    </div>
                </div>
            @endif
            
            {{-- Banking Information --}}
            @if($settings && ($settings->iban_number || $settings->bank_name))
                <div class="section">
                    <div class="section-title">🏦 Banking Information</div>
                    <div class="info-grid">
                        @if($settings->bank_name)
                            <div class="info-item">
                                <div class="info-label">Bank Name</div>
                                <div class="info-value">{{ $settings->bank_name }}</div>
                            </div>
                        @endif
                        @if($settings->iban_number)
                            <div class="info-item">
                                <div class="info-label">IBAN Number</div>
                                <div class="info-value"><code>{{ $settings->iban_number }}</code></div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            {{-- Reply Instructions --}}
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin: 30px 0;">
                <h3 style="margin: 0 0 20px 0; font-size: 22px; text-align: center;">📧 How to Respond</h3>
                <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                    <p style="margin: 0 0 10px 0; font-size: 16px;"><strong>To APPROVE this request:</strong></p>
                    <p style="margin: 0; font-size: 14px; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 5px;">
                        Reply to this email with: <strong style="font-size: 18px;">APPROVE</strong> or <strong style="font-size: 18px;">قبول</strong>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;">
                    <p style="margin: 0 0 10px 0; font-size: 16px;"><strong>To REJECT this request:</strong></p>
                    <p style="margin: 0; font-size: 14px; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 5px;">
                        Reply to this email with: <strong style="font-size: 18px;">REJECT: Your reason here</strong><br>
                        or <strong style="font-size: 18px;">رفض: السبب هنا</strong>
                    </p>
                </div>
                <p style="text-align: center; margin: 20px 0 0 0; font-size: 13px; opacity: 0.9;">
                    ⚠️ Please reply to this email directly. Do not forward or modify the subject line.
                </p>
            </div>
            
            <div style="text-align: center; color: #666; font-size: 12px; margin-top: 20px;">
                <p><strong>Reference Number:</strong> {{ $approval->approval_token }}</p>
                <p>⚠️ Please review all attached documents before making a decision.</p>
                <p>For questions, contact us at {{ $settings->hospital_email ?? 'info@hospital.com' }}</p>
            </div>
        </div>
        
        {{-- Footer --}}
        <div class="footer">
            <p><strong>{{ $settings->hospital_name ?? 'Hospital Management System' }}</strong></p>
            @if($settings && $settings->hospital_address)
                <p>{{ $settings->hospital_address }}</p>
            @endif
            @if($settings && ($settings->hospital_phone || $settings->hospital_email))
                <p>
                    @if($settings->hospital_phone) Phone: {{ $settings->hospital_phone }} @endif
                    @if($settings->hospital_email) | Email: {{ $settings->hospital_email }} @endif
                </p>
            @endif
            <p style="margin-top: 15px; color: #999;">© {{ date('Y') }} All rights reserved.</p>
        </div>
    </div>
</body>
</html>
