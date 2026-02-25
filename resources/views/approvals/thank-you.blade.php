<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex items-center justify-center py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden text-center p-12">
            <div class="mb-6">
                @if($approval->status === 'approved')
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-green-900 mb-3">Request Approved!</h1>
                    <p class="text-lg text-slate-600">
                        The approval request <strong>{{ $approval->approval_number }}</strong> has been successfully approved.
                    </p>
                @else
                    <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-red-900 mb-3">Request Rejected</h1>
                    <p class="text-lg text-slate-600">
                        The approval request <strong>{{ $approval->approval_number }}</strong> has been rejected.
                    </p>
                @endif
            </div>

            <div class="bg-slate-50 rounded-lg p-6 mb-6">
                <h3 class="font-bold mb-3">Response Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Approval Number:</span>
                        <span class="font-semibold">{{ $approval->approval_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Patient:</span>
                        <span class="font-semibold">{{ $approval->patient->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Status:</span>
                        <span class="font-bold {{ $approval->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                            {{ strtoupper($approval->status) }}
                        </span>
                    </div>
                    @if($approval->status === 'approved')
                        <div class="flex justify-between">
                            <span class="text-slate-600">Approved Amount:</span>
                            <span class="font-bold text-green-600">{{ number_format($approval->approved_amount, 2) }} SAR</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-600">Processed At:</span>
                        <span class="font-semibold">{{ $approval->approved_at->format('Y-m-d H:i:s') }}</span>
                    </div>
                </div>
            </div>

            <p class="text-slate-600 text-sm">
                Thank you for your prompt response. The hospital will be notified of your decision.
            </p>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="fixed bottom-0 left-0 w-full py-6 text-center text-slate-500 text-sm font-medium">
        <div class="mb-1">
            &copy; {{ date('Y') }} <span class="text-slate-700 font-bold">Abeer Alrwaily</span>. {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All Rights Reserved.' }}
        </div>
        <div>
            {{ app()->getLocale() === 'ar' ? 'تم التطوير بواسطة' : 'Developed by' }}
            <span class="text-indigo-600 font-bold">Zienab Khaled</span>
        </div>
    </footer>
</body>
</html>
