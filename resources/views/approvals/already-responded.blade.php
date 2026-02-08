<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Already Responded</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-50 to-orange-50 min-h-screen flex items-center justify-center py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden text-center p-12">
            <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-slate-900 mb-3">Already Responded</h1>
            <p class="text-lg text-slate-600 mb-6">
                This approval request has already been processed and cannot be modified.
            </p>
            
            <div class="bg-slate-50 rounded-lg p-6 mb-6">
                <h3 class="font-bold mb-3">Current Status</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Approval Number:</span>
                        <span class="font-semibold">{{ $approval->approval_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Status:</span>
                        <span class="font-bold {{ $approval->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                            {{ strtoupper($approval->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Processed At:</span>
                        <span class="font-semibold">{{ $approval->approved_at?->format('Y-m-d H:i:s') }}</span>
                    </div>
                </div>
            </div>
            
            <p class="text-slate-600 text-sm">
                If you believe this is an error, please contact the hospital directly.
            </p>
        </div>
    </div>
</body>
</html>
