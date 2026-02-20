@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إنشاء مطالبة تأمين' : 'Create Insurance Claim')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                🏥 {{ app()->getLocale() === 'ar' ? 'إنشاء مطالبة تأمين جديدة' : 'Create New Insurance Claim' }}
            </h2>
            <a href="{{ route('charity-claims.index', ['tab' => 'insurance']) }}" class="text-slate-500 hover:text-slate-700 font-medium">
                {{ app()->getLocale() === 'ar' ? '← العودة للمطالبات' : 'Back to Claims' }}
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
            <form action="{{ route('insurance-claims.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Patient Search --}}
                    <div class="relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            👤 {{ app()->getLocale() === 'ar' ? 'بحث عن المريض' : 'Search Patient' }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="patient_search"
                                   autocomplete="off"
                                   placeholder="{{ app()->getLocale() === 'ar' ? 'ادخل الاسم، رقم الهوية، أو رقم الملف...' : 'Enter name, ID, or file number...' }}"
                                   class="w-full border-2 border-slate-200 rounded-lg px-3 py-2.5 ps-10 focus:border-red-500 focus:ring-0 transition-all">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                        <input type="hidden" name="patient_id" id="patient_id">

                        {{-- Search Results Dropdown --}}
                        <div id="patient_results" class="absolute z-50 w-full mt-1 bg-white border-2 border-slate-200 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
                        </div>

                        <div id="selected_patient_badge" class="mt-2 hidden">
                            <div class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-3 py-1.5 rounded-lg text-sm">
                                <span id="selected_patient_name"></span>
                                <button type="button" id="clear_patient" class="text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                </button>
                            </div>
                        </div>
                        @error('patient_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Invoice Selection --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            📄 {{ app()->getLocale() === 'ar' ? 'اختر الفاتورة' : 'Select Invoice' }} <span class="text-red-500">*</span>
                        </label>
                        <select name="invoice_id" id="invoice_id" class="w-full border-2 border-slate-200 rounded-lg px-3 py-2.5 focus:border-red-500 focus:ring-0 transition-all" required disabled>
                            <option value="">{{ app()->getLocale() === 'ar' ? '--- ابحث عن مريض أولاً ---' : '--- Search for a patient first ---' }}</option>
                        </select>
                        @error('invoice_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Status & File --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            🚦 {{ app()->getLocale() === 'ar' ? 'حالة المطالبة' : 'Claim Status' }} <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" class="w-full border-2 border-slate-200 rounded-lg px-3 py-2.5 focus:border-red-500 focus:ring-0 transition-all" required>
                            <option value="draft">{{ app()->getLocale() === 'ar' ? 'مسودة' : 'Draft' }}</option>
                            <option value="sent">{{ app()->getLocale() === 'ar' ? 'مرسلة' : 'Sent' }}</option>
                            <option value="under_review">{{ app()->getLocale() === 'ar' ? 'قيد المراجعة' : 'Under Review' }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            📂 {{ app()->getLocale() === 'ar' ? 'ملف Arqos Saudi' : 'Arqos Saudi File' }}
                        </label>
                        <input type="file" name="arqos_file" class="w-full border-2 border-slate-200 border-dashed rounded-lg px-3 py-2 text-sm">
                        <p class="text-slate-400 text-[10px] mt-1">{{ app()->getLocale() === 'ar' ? 'PDF, JPG, PNG (بحد أقصى 2 ميجا)' : 'PDF, JPG, PNG (Max 2MB)' }}</p>
                    </div>
                </div>

                {{-- Items Selection --}}
                <div class="mb-6 hidden" id="items_section">
                    <label class="block text-sm font-bold text-slate-700 mb-3">
                        💉 {{ app()->getLocale() === 'ar' ? 'الخدمات التي لم تنتهِ بعد' : 'Pending Services' }}
                    </label>
                    <div id="items_container" class="border-2 border-slate-100 rounded-lg bg-slate-50 p-4 space-y-2 max-h-60 overflow-y-auto">
                        <!-- AJAX content -->
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        📝 {{ app()->getLocale() === 'ar' ? 'ملاحظات إضافية' : 'Additional Notes' }}
                    </label>
                    <textarea name="notes" rows="3" class="w-full border-2 border-slate-200 rounded-lg px-3 py-2 focus:border-red-500 focus:ring-0" placeholder="{{ app()->getLocale() === 'ar' ? 'أضف أي ملاحظات هنا...' : 'Add any notes here...' }}"></textarea>
                </div>

                <div class="border-t border-slate-100 pt-6 flex justify-end">
                    <button type="submit" class="bg-red-600 px-8 py-3 rounded-lg font-bold hover:bg-red-700 shadow-lg shadow-red-100 transition-all">
                        💾 {{ app()->getLocale() === 'ar' ? 'حفظ المطالبة' : 'Save Claim' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const patientSearch  = document.getElementById('patient_search');
            const patientResults = document.getElementById('patient_results');
            const patientIdInput = document.getElementById('patient_id');
            const selectedBadge  = document.getElementById('selected_patient_badge');
            const selectedName   = document.getElementById('selected_patient_name');
            const clearPatient   = document.getElementById('clear_patient');

            const invoiceSelect  = document.getElementById('invoice_id');
            const itemsSection   = document.getElementById('items_section');
            const itemsContainer = document.getElementById('items_container');

            let debounceTimer;

            // --- PATIENT SEARCH LOGIC ---
            patientSearch.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const q = this.value.trim();

                if (q.length < 2) {
                    patientResults.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('insurance-claims.patients-search') }}?q=${encodeURIComponent(q)}`)
                        .then(res => res.json())
                        .then(data => {
                            patientResults.innerHTML = '';
                            if (data.length === 0) {
                                patientResults.innerHTML = '<div class="p-3 text-slate-500 text-sm">لم يتم العثور على مرضى تأمين مطابقين</div>';
                            } else {
                                data.forEach(p => {
                                    const div = document.createElement('div');
                                    div.className = 'p-3 hover:bg-red-50 cursor-pointer border-b border-slate-100 last:border-0 transition-colors';
                                    div.innerHTML = `
                                        <div class="font-bold text-slate-800">${p.name_ar || p.name}</div>
                                        <div class="text-xs text-slate-500">رقم الملف: ${p.file_number}</div>
                                    `;
                                    div.onclick = () => selectPatient(p);
                                    patientResults.appendChild(div);
                                });
                            }
                            patientResults.classList.remove('hidden');
                        });
                }, 300);
            });

            function selectPatient(p) {
                patientIdInput.value = p.id;
                selectedName.textContent = `${p.name_ar || p.name} (${p.file_number})`;

                patientSearch.value = '';
                patientResults.classList.add('hidden');
                patientSearch.parentElement.classList.add('hidden'); // Hide search box
                selectedBadge.classList.remove('hidden');

                loadInvoices(p.id);
            }

            clearPatient.onclick = () => {
                patientIdInput.value = '';
                selectedBadge.classList.add('hidden');
                patientSearch.parentElement.classList.remove('hidden');
                patientSearch.focus();

                invoiceSelect.innerHTML = '<option value="">{{ app()->getLocale() === 'ar' ? '--- ابحث عن مريض أولاً ---' : '--- Search for a patient first ---' }}</option>';
                invoiceSelect.disabled = true;
                itemsSection.classList.add('hidden');
            };

            // Hide dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!patientSearch.contains(e.target) && !patientResults.contains(e.target)) {
                    patientResults.classList.add('hidden');
                }
            });

            // --- INVOICE & ITEMS LOGIC ---
            function loadInvoices(patientId) {
                invoiceSelect.innerHTML = '<option value="">{{ app()->getLocale() === 'ar' ? '--- جاري التحميل... ---' : '--- Loading... ---' }}</option>';
                invoiceSelect.disabled = true;
                itemsSection.classList.add('hidden');

                fetch(`/insurance-claims/get-invoices/${patientId}`)
                    .then(res => res.json())
                    .then(data => {
                        invoiceSelect.innerHTML = '<option value="">{{ app()->getLocale() === 'ar' ? '--- اختر الفاتورة ---' : '--- Select Invoice ---' }}</option>';
                        if (data.length === 0) {
                            invoiceSelect.innerHTML = '<option value="">{{ app()->getLocale() === 'ar' ? 'لا يوجد فواتير بهذا الاسم' : 'No invoices found' }}</option>';
                        } else {
                            data.forEach(inv => {
                                invoiceSelect.innerHTML += `<option value="${inv.id}">${inv.invoice_number} (${inv.invoice_date}) - ${inv.total_amount} ر.س (${inv.pending_items_count} خدمات معلقة)</option>`;
                            });
                            invoiceSelect.disabled = false;
                        }
                    });
            }

            invoiceSelect.addEventListener('change', function() {
                const invoiceId = this.value;
                if (!invoiceId) {
                    itemsSection.classList.add('hidden');
                    return;
                }

                itemsContainer.innerHTML = '{{ app()->getLocale() === 'ar' ? 'جاري تحميل الخدمات...' : 'Loading services...' }}';
                itemsSection.classList.remove('hidden');

                fetch(`/insurance-claims/get-items/${invoiceId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) {
                            itemsContainer.innerHTML = '<p class="text-slate-500 text-sm italic">{{ app()->getLocale() === 'ar' ? 'لا توجد خدمات معلقة لهذه الفاتورة' : 'No pending services for this invoice' }}</p>';
                        } else {
                            itemsContainer.innerHTML = '';
                            data.forEach(item => {
                                itemsContainer.innerHTML += `
                                    <div class="flex items-center gap-3 bg-white p-2 rounded border border-slate-200">
                                        <input type="checkbox" name="item_ids[]" value="${item.id}" checked class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold">${item.service ? (item.service.name_ar || item.service.name) : 'خدمة غير معروفة'}</div>
                                            <div class="text-xs text-slate-500">${item.total_price} ر.س</div>
                                        </div>
                                    </div>
                                `;
                            });
                        }
                    });
            });
        });
    </script>
@endsection
