<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Response</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-slate-100 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-8 text-center">
                <h1 class="text-3xl font-bold mb-2">
                    {{ $action === 'approve' ? '✅ Approve Request' : '❌ Reject Request' }}
                </h1>
                <p class="text-blue-100">{{ $approval->approval_number }}</p>
            </div>
            
            {{-- Content --}}
            <div class="p-8">
                {{-- Patient & Invoice Info --}}
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-lg mb-4">Patient & Invoice Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-600">Patient:</span>
                            <span class="font-semibold ml-2">{{ $approval->patient->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-600">File Number:</span>
                            <span class="font-semibold ml-2">{{ $approval->patient->file_number }}</span>
                        </div>
                        <div>
                            <span class="text-slate-600">Invoice Number:</span>
                            <span class="font-semibold ml-2">{{ $approval->invoice->invoice_number }}</span>
                        </div>
                        <div>
                            <span class="text-slate-600">Requested Amount:</span>
                            <span class="font-bold text-blue-600 ml-2">{{ number_format($approval->requested_amount, 2) }} SAR</span>
                        </div>
                    </div>
                </div>
                
                {{-- Services Table --}}
                <div class="mb-6">
                    <h3 class="font-bold text-lg mb-3">Services</h3>
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-3 text-left">Service</th>
                                <th class="p-3 text-center">Qty</th>
                                <th class="p-3 text-right">Price</th>
                                <th class="p-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approval->invoice->items as $item)
                                <tr class="border-b">
                                    <td class="p-3">{{ $item->service->name }} @if($item->service->code)({{ $item->service->code }})@endif</td>
                                    <td class="p-3 text-center">{{ $item->quantity }}</td>
                                    <td class="p-3 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="p-3 text-right font-semibold">{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Response Form --}}
                <form action="{{ route('approvals.process', $approval->approval_token) }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="action" value="{{ $action }}">
                    
                    @if($action === 'approve')
                        {{-- Approval Form --}}
                        <div class="bg-green-50 border-2 border-green-300 rounded-lg p-6">
                            <h3 class="font-bold text-green-900 text-lg mb-4">Approval Details</h3>
                            
                            <div class="mb-4">
                                <label class="block font-semibold text-slate-700 mb-2">
                                    Approved Amount (SAR) *
                                </label>
                                <input type="number" name="approved_amount" step="0.01" 
                                       value="{{ old('approved_amount', $approval->requested_amount) }}" 
                                       required
                                       class="w-full px-4 py-3 rounded-lg border-2 border-slate-300 focus:border-green-500 focus:outline-none text-lg font-bold">
                                <p class="text-xs text-slate-600 mt-1">You can approve a different amount than requested</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block font-semibold text-slate-700 mb-2">
                                    Notes (Optional)
                                </label>
                                <textarea name="notes" rows="3" 
                                          class="w-full px-4 py-3 rounded-lg border-2 border-slate-300 focus:border-green-500 focus:outline-none"
                                          placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                            </div>
                            
                            <button type="submit" class="w-full bg-green-600 text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-green-700 shadow-lg">
                                ✅ Confirm Approval
                            </button>
                        </div>
                    @else
                        {{-- Rejection Form --}}
                        <div class="bg-red-50 border-2 border-red-300 rounded-lg p-6">
                            <h3 class="font-bold text-red-900 text-lg mb-4">Rejection Details</h3>
                            
                            <div class="mb-4">
                                <label class="block font-semibold text-slate-700 mb-2">
                                    Reason for Rejection *
                                </label>
                                <textarea name="rejection_reason" rows="4" required
                                          class="w-full px-4 py-3 rounded-lg border-2 border-slate-300 focus:border-red-500 focus:outline-none"
                                          placeholder="Please provide a reason for rejection...">{{ old('rejection_reason') }}</textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block font-semibold text-slate-700 mb-2">
                                    Additional Notes (Optional)
                                </label>
                                <textarea name="notes" rows="2" 
                                          class="w-full px-4 py-3 rounded-lg border-2 border-slate-300 focus:border-red-500 focus:outline-none"
                                          placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                            </div>
                            
                            <button type="submit" class="w-full bg-red-600 text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-red-700 shadow-lg">
                                ❌ Confirm Rejection
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</body>
</html>
