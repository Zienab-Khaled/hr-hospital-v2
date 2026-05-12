@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل المطالبة' : 'Claim Details')

@section('content')
    @php
        $statusColors = [
            'draft' => 'bg-slate-100 text-slate-700 border-slate-300',
            'sent' => 'bg-blue-100 text-blue-800 border-blue-300',
            'under_review' => 'bg-amber-100 text-amber-800 border-amber-300',
            'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'rejected' => 'bg-red-100 text-red-800 border-red-300',
            'paid' => 'bg-purple-100 text-purple-800 border-purple-300',
        ];
        $statusLabels = [
            'draft' => 'مسودة',
            'sent' => 'مرسلة',
            'under_review' => 'قيد المراجعة',
            'approved' => 'معتمدة',
            'rejected' => 'مرفوضة',
            'paid' => 'مدفوعة',
        ];
        $color = $statusColors[$charityClaim->status] ?? 'bg-slate-100 text-slate-700 border-slate-300';
        $label = $statusLabels[$charityClaim->status] ?? $charityClaim->status;
    @endphp

    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                📋 {{ app()->getLocale() === 'ar' ? 'مطالبة جمعية' : 'Charity Claim' }}
                <span class="text-slate-500 text-lg font-normal ms-2">{{ $charityClaim->invoice?->invoice_number }}</span>
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('charity-claims.index') }}"
                    class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-slate-300">
                    ← {{ app()->getLocale() === 'ar' ? 'قائمة المطالبات' : 'Claims List' }}
                </a>
                @if($charityClaim->status === 'draft')
                    <a href="{{ route('charity-claims.preview', $charityClaim) }}" target="_blank"
                        class="bg-amber-500 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-amber-600">
                        👁️ {{ app()->getLocale() === 'ar' ? 'معاينة قبل الإرسال' : 'Preview before send' }}
                    </a>
                    <form method="POST" action="{{ route('charity-claims.send', $charityClaim) }}" class="inline"
                        onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل تريد إرسال المطالبة للجمعية؟ لا يمكن التراجع عن الإرسال.' : 'Send this claim to the charity? This cannot be undone.' }}');">
                        @csrf
                        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-emerald-700">
                            📤 {{ app()->getLocale() === 'ar' ? 'إرسال للجمعية' : 'Send to charity' }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('invoices.show', $charityClaim->invoice) }}"
                    class="bg-blue-600 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-blue-700">
                    {{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View Invoice' }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-300 text-red-800 text-sm">
                @foreach ($errors->all() as $e)
                    <p>⚠️ {{ $e }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Claim Info --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Claim Summary --}}
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="font-bold text-slate-800 mb-4 text-lg border-b pb-2">
                        {{ app()->getLocale() === 'ar' ? 'بيانات المطالبة' : 'Claim Info' }}</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span
                                class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</span>
                            <span
                                class="inline-block px-3 py-1 rounded-full text-sm font-bold border {{ $color }}">{{ $label }}</span>
                        </div>
                        <div>
                            <span
                                class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent Date' }}</span>
                            <span
                                class="font-medium text-slate-800">{{ $charityClaim->sent_date?->format('Y-m-d') ?? '—' }}</span>
                        </div>
                        <div>
                            <span
                                class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Charity Entity' }}</span>
                            <span
                                class="font-medium text-slate-800">{{ $charityClaim->charityEntity?->name_ar ?? ($charityClaim->charityEntity?->name ?? '—') }}</span>
                        </div>
                        <div>
                            <span
                                class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved Amount' }}</span>
                            <span class="font-bold text-emerald-700 text-lg">
                                {{ $charityClaim->approved_amount ? number_format((float) $charityClaim->approved_amount, 2) : '—' }}
                            </span>
                        </div>
                        <div>
                            <span
                                class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'أرسل بواسطة' : 'Sent By' }}</span>
                            <span
                                class="font-medium text-slate-800">{{ $charityClaim->sentByUser?->name ?? ($charityClaim->sentByUser?->username ?? '—') }}</span>
                        </div>
                        <div>
                            <p class="text-slate-600 text-[10px] font-semibold uppercase mb-1">
                                {{ app()->getLocale() === 'ar' ? 'إجمالي الفاتورة' : 'Invoice Total' }}</p>
                            <p class="text-lg font-bold text-slate-900">@currency($charityClaim->invoice->total_amount)</p>
                            <a href="{{ route('invoices.show', $charityClaim->invoice) }}"
                                class="inline-block mt-2 bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-slate-300 transition-colors">
                                📄 {{ app()->getLocale() === 'ar' ? 'عرض تفاصيل الفاتورة' : 'View Invoice Details' }}
                            </a>
                        </div>
                    </div>
                    @if ($charityClaim->notes)
                        <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span
                                class="text-slate-500 font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'ملاحظات:' : 'Notes:' }}</span>
                            <p class="text-slate-800 text-sm mt-1">{{ $charityClaim->notes }}</p>
                        </div>
                    @endif
                    @if ($charityClaim->entity_response_notes)
                        <div class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                            <span
                                class="text-amber-700 font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'رد الجمعية:' : 'Entity Response:' }}</span>
                            <p class="text-slate-800 text-sm mt-1">{{ $charityClaim->entity_response_notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- الملاحظات (متعددة) --}}
                <div id="notes" class="bg-white rounded-lg shadow p-5 mt-5">
                    <h3 class="font-bold text-slate-800 mb-3 border-b pb-2">
                        {{ app()->getLocale() === 'ar' ? 'الملاحظات' : 'Notes' }}</h3>
                    @forelse(($claimNotes ?? collect()) as $note)
                        <div
                            class="mb-4 p-3 rounded-lg border {{ $loop->first ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200' }}">
                            <p class="text-slate-800 text-sm">{{ $note->body }}</p>
                            <p class="text-[10px] text-slate-500 mt-2">
                                {{ western_digits($note->created_at->translatedFormat('d/m/Y H:i')) }}
                                @if ($note->createdByUser)
                                    — {{ $note->createdByUser->name_ar ?? ($note->createdByUser->name ?? '') }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد ملاحظات بعد.' : 'No notes yet.' }}</p>
                    @endforelse
                    <form method="POST" action="{{ route('charity-claims.notes.store', $charityClaim) }}"
                        class="mt-4 pt-4 border-t border-slate-200">
                        @csrf
                        <p class="text-xs text-slate-500 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'اكتب الملاحظة في المربع أدناه ثم اضغط زر «حفظ الملاحظة».' : 'Type your note in the box below then click «Save note».' }}
                        </p>
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'نص الملاحظة' : 'Note text' }}</label>
                        <textarea name="body" rows="3" required
                            placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب الملاحظة هنا...' : 'Write your note here...' }}"
                            class="w-full border-2 border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                        <button type="submit"
                            class="mt-3 w-full sm:w-auto bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700 shadow">
                            {{ app()->getLocale() === 'ar' ? 'حفظ الملاحظة' : 'Save note' }}
                        </button>
                    </form>
                </div>

                {{-- Approval Documents Display --}}
                @php
                    $patientApprovals = $charityClaim->invoice?->patient?->getMedia('charity-approvals') ?? collect();
                    $visitApprovals = $charityClaim->invoice?->visit?->getMedia('charity_approval') ?? collect();
                    $allApprovals = $patientApprovals->merge($visitApprovals);
                @endphp

                @if ($allApprovals->isNotEmpty())
                    <div class="bg-emerald-50 border-2 border-emerald-200 rounded-lg shadow-sm p-4">
                        <h3 class="font-bold text-emerald-800 mb-3 flex items-center gap-2">
                            📎
                            {{ app()->getLocale() === 'ar' ? 'مستندات الاعتماد المرفوعة' : 'Uploaded Approval Documents' }}
                        </h3>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($allApprovals as $media)
                                <a href="{{ $media->getUrl() }}" target="_blank"
                                    class="inline-flex items-center gap-2 bg-white border border-emerald-300 text-emerald-700 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-100 transition shadow-sm">
                                    📄 {{ $media->file_name }}
                                    <span
                                        class="text-[10px] text-emerald-500 bg-emerald-50 px-1 rounded border border-emerald-200 uppercase">{{ $media->extension }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Patient Info --}}
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="font-bold text-slate-800 mb-3 border-b pb-2">
                        {{ app()->getLocale() === 'ar' ? 'بيانات المريض' : 'Patient Info' }}</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span
                                class="text-slate-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span>
                            <span
                                class="font-medium text-slate-800 ms-1">{{ $charityClaim->invoice?->patient?->fullArabicName() ?: ($charityClaim->invoice?->patient?->name ?? '—') }}</span>
                        </div>
                        <div>
                            <span
                                class="text-slate-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span>
                            <span
                                class="font-medium text-slate-800 ms-1">{{ $charityClaim->invoice?->patient?->file_number ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Services --}}
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="font-bold text-slate-800 mb-3 border-b pb-2">
                        {{ app()->getLocale() === 'ar' ? 'الخدمات' : 'Services' }}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border border-slate-300 px-3 py-2 text-start font-bold text-slate-700">
                                        {{ app()->getLocale() === 'ar' ? 'الخدمة' : 'Service' }}</th>
                                    <th class="border border-slate-300 px-3 py-2 text-center font-bold text-slate-700">
                                        {{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                    <th class="border border-slate-300 px-3 py-2 text-center font-bold text-slate-700">
                                        {{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                    <th class="border border-slate-300 px-3 py-2 text-center font-bold text-slate-700">
                                        {{ app()->getLocale() === 'ar' ? 'التنفيذ' : 'Status' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($charityClaim->invoice?->items ?? [] as $item)
                                    <tr
                                        class="border-b border-slate-200 {{ $item->isCompleted() ? 'bg-emerald-50/40' : '' }}">
                                        <td class="border border-slate-200 px-3 py-2">
                                            {{ $item->service?->name_ar ?? ($item->service?->name ?? '—') }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->quantity }}
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2 text-center font-medium">
                                            @currency($item->total_price)</td>
                                        <td class="border border-slate-200 px-3 py-2 text-center">
                                            @if ($item->isCompleted())
                                                <span
                                                    class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-semibold">✅
                                                    {{ app()->getLocale() === 'ar' ? 'منفذ' : 'Done' }}</span>
                                            @else
                                                <span
                                                    class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-semibold">⏳
                                                    {{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right: Update Status --}}
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow p-5 sticky top-4">
                    <h3 class="font-bold text-slate-800 mb-4 text-lg border-b pb-2">
                        🔄 {{ app()->getLocale() === 'ar' ? 'تحديث الحالة' : 'Update Status' }}
                    </h3>

                    <form method="POST" action="{{ route('charity-claims.update-status', $charityClaim) }}"
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label
                                class="block text-sm font-semibold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'الحالة الجديدة' : 'New Status' }}
                                *</label>
                            <select name="status" required
                                class="w-full border-2 border-slate-400 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">{{ app()->getLocale() === 'ar' ? 'اختر الحالة' : 'Select status' }}
                                </option>
                                <option value="under_review"
                                    {{ $charityClaim->status === 'under_review' ? 'selected' : '' }}>قيد المراجعة</option>
                                <option value="approved" {{ $charityClaim->status === 'approved' ? 'selected' : '' }}>
                                    معتمدة ✅</option>
                                <option value="rejected" {{ $charityClaim->status === 'rejected' ? 'selected' : '' }}>
                                    مرفوضة ❌</option>
                                <option value="paid" {{ $charityClaim->status === 'paid' ? 'selected' : '' }}>مدفوعة 💰
                                </option>
                            </select>
                        </div>

                        <div id="approved-amount-field"
                            class="{{ $charityClaim->status === 'approved' ? '' : 'hidden' }}">
                            <label
                                class="block text-sm font-semibold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved Amount' }}</label>
                            <input type="number" name="approved_amount" step="0.01" min="0"
                                value="{{ $charityClaim->approved_amount ?? $charityClaim->invoice?->remaining_amount }}"
                                class="w-full border-2 border-slate-400 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'ملاحظات رد الجمعية' : 'Entity Response Notes' }}</label>
                            <textarea name="entity_response_notes" rows="3"
                                class="w-full border-2 border-slate-400 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">{{ $charityClaim->entity_response_notes }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                📎
                                {{ app()->getLocale() === 'ar' ? 'رفع مستند اعتماد (اختياري)' : 'Upload Approval Document (Optional)' }}
                            </label>
                            <input type="file" name="approval_document"
                                class="w-full border-2 border-dashed border-slate-300 rounded-lg px-3 py-4 text-xs bg-slate-50 cursor-pointer hover:bg-white transition">
                            <p class="text-[10px] text-slate-500 mt-1 italic">
                                {{ app()->getLocale() === 'ar' ? 'سيتم حفظ الملف في ملف المريض ضمن "مستندات الجمعية"' : 'File will be saved to patient profile under "Charity Documents"' }}
                            </p>
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 py-2.5 rounded-lg font-bold hover:bg-blue-700 shadow">
                            ✅ {{ app()->getLocale() === 'ar' ? 'حفظ التحديث' : 'Save Update' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('select[name="status"]').addEventListener('change', function() {
            const field = document.getElementById('approved-amount-field');
            field.classList.toggle('hidden', this.value !== 'approved');
        });
    </script>
@endsection
