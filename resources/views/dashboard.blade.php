@extends('layouts.app')

@section('title', __('Dashboard'))

@section('tabs')
    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-t bg-blue-600  text-sm font-medium">{{ __("Patient File") }}</a>
    @can('invoices.view')
        <a href="{{ route('invoices.index') }}" class="px-4 py-2 rounded-t bg-slate-100 text-slate-600 text-sm hover:bg-slate-200">{{ __("Invoices") }}</a>
    @endcan
    @can('authorizations.view')
        <a href="{{ route('authorizations.index') }}" class="px-4 py-2 rounded-t bg-slate-100 text-slate-600 text-sm hover:bg-slate-200">{{ __("Authorizations") }}</a>
    @endcan

    {{-- Quick Action Tabs (حسب الصلاحيات — توديك لصفحات الإنشاء) --}}
    @can('visits.create')
        <a href="{{ route('visits.create') }}" class="ml-4 px-4 py-2 rounded-t bg-green-600 text-sm font-bold hover:bg-green-700 flex items-center gap-1 "
        {{ app()->getLocale() === 'ar' ? 'إضافة زيارة' : 'Add Visit' }}>
            <span>🩺</span> {{ app()->getLocale() === 'ar' ? 'إضافة زيارة' : 'Add Visit' }}
        </a>
    @endcan
    @can('invoices.create')
        <a href="{{ route('invoices.create') }}" class="ml-1 px-4 py-2 rounded-t bg-amber-600 text-sm font-bold hover:bg-orange-600 flex items-center gap-1 ">
            <span>💰</span> {{ app()->getLocale() === 'ar' ? 'إضافة فاتورة' : 'Add Invoice' }}
        </a>
    @endcan
@endsection

@section('content')
    {{-- Shared Dynamic Modal --}}
    <div id="dashboardModal" class="hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.5); z-index: 99999; padding: 1rem; align-items: center; justify-content: center; display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-800"></h3>
                <button onclick="closeDashboardModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">&times;</button>
            </div>
            <div class="p-6">
                <form id="dashboardModalForm" onsubmit="handleDashboardSubmit(event)">
                    @csrf
                    <input type="hidden" id="modalActionType" name="type">

                    {{-- Patient Search Section (Hidden for view or patient_create) --}}
                    <div id="patientSearchSection" class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">{{ __("Patients") }}</label>
                        <div class="relative">
                            <input type="text" id="patientSearchInput" oninput="debounceSearch(this.value)" autocomplete="off"
                                   placeholder="{{ app()->getLocale() === 'ar' ? 'ابحث باسم المريض أو رقم الهوية...' : 'Search by name or ID...' }}"
                                   class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none">
                            <div id="patientSearchResults" class="absolute z-10 w-full bg-white shadow-lg rounded-b-lg border border-slate-200 mt-1 hidden max-h-48 overflow-y-auto"></div>
                        </div>
                        <input type="hidden" id="selectedPatientId" name="patient_id" required>
                        <div id="selectedPatientBadge" class="mt-2 hidden px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium flex justify-between items-center">
                            <span id="selectedPatientName"></span>
                            <button type="button" onclick="clearSelectedPatient()" class="text-blue-500 hover:text-blue-700 font-bold">&times;</button>
                        </div>
                    </div>

                    {{-- Invoice Selection Section (For commitment/non-commitment) --}}
                    <div id="invoiceSearchSection" class="mb-4 hidden">
                        <label class="block text-sm font-bold text-slate-700 mb-1">{{ __("Invoices") }}</label>
                        <select id="invoiceSelect" name="invoice_id" class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر الفاتورة...' : 'Select invoice...' }}</option>
                        </select>
                    </div>

                    {{-- Dynamic Content Area --}}
                    <div id="modalDynamicFields"></div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeDashboardModal()" class="px-6 py-2 rounded-lg border border-slate-300 text-slate-600 font-semibold hover:bg-slate-50">
                            {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                        </button>
                        <button type="submit" id="submitBtn" class="px-8 py-2 bg-blue-600  rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50">
                            {{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Visits Section (حسب صلاحية visits.view) --}}
        @can('visits.view')
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>🩺</span> {{ __("Visits") }}
            </h2>
            <div class="flex flex-wrap gap-2 mb-4">
                @can('visits.create')
                    <a href="{{ route('visits.create') }}" class="bg-green-600  px-4 py-2 rounded text-white text-sm font-bold hover:bg-green-700">+ {{ __("Add Visit") }}</a>
                @endcan
                <a href="{{ route('visits.index') }}" class="bg-slate-100 text-slate-600 px-4 py-2 rounded text-sm hover:bg-slate-200">{{ __("Full Page") }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ __("Patients") }}</th><th class="text-start py-2">{{ __("Department") }}</th></tr></thead>
                    <tbody>
                        @forelse($recentVisits as $v)
                            <tr class="border-b border-slate-100 hover:bg-slate-50 cursor-pointer" onclick="openDashboardModal('view', {type: 'visit', id: {{ $v->id }}})">
                                <td class="py-2 text-blue-600 font-medium">{{ $v->patient?->name }}</td>
                                <td class="py-2">{{ app()->getLocale() === 'ar' ? ($v->department?->name_ar ?? $v->department?->name) : $v->department?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('visits.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>
        @endcan

        {{-- Invoices Section (حسب صلاحية invoices.view) --}}
        @can('invoices.view')
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>💰</span> {{ __("Invoices") }}
            </h2>
            <div class="flex flex-wrap gap-2 mb-4">
                @can('invoices.create')
                    <a href="{{ route('invoices.create') }}" class="bg-orange-500  px-4 py-2 rounded text-white text-sm font-bold hover:bg-orange-600">+ {{ __("Add Invoice") }}</a>
                @endcan
                <a href="{{ route('invoices.index') }}" class="bg-slate-100 text-slate-600 px-4 py-2 rounded text-sm hover:bg-slate-200">{{ __("Full Page") }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ __("Invoice Number") }}</th><th class="text-start py-2">{{ __("Total") }}</th></tr></thead>
                    <tbody>
                        @forelse($recentInvoices as $inv)
                            <tr class="border-b border-slate-100 hover:bg-slate-50 cursor-pointer" onclick="window.location.href='{{ route('invoices.show', $inv) }}'">
                                <td class="py-2 text-blue-600 font-medium">{{ $inv->invoice_number }}</td>
                                <td class="py-2">@currency($inv->total_amount)</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('invoices.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>
        @endcan

        {{-- Claims Section (حسب صلاحية claims.view) --}}
        @can('claims.view')
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>📑</span> {{ app()->getLocale() === 'ar' ? 'المطالبات' : 'Claims' }}
            </h2>
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('charity-claims.index', ['tab' => 'insurance']) }}" class="bg-blue-600  px-4 py-2 rounded text-sm font-bold hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'مطالبة جديدة' : 'New Claim' }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ __("Patients") }}</th><th class="text-start py-2">{{ __("Status") }}</th></tr></thead>
                    <tbody>
                        @forelse($recentClaims as $claim)
                            @php
                                $type = $claim instanceof \App\Models\InsuranceClaim ? 'insurance' : 'charity';
                                $url = $type === 'insurance' ? route('insurance-claims.show', $claim) : route('charity-claims.show', $claim);
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50 cursor-pointer" onclick="window.location.href='{{ $url }}'">
                                <td class="py-2 text-blue-600 font-medium">{{ $claim->patient?->name ?? $claim->invoice?->patient?->name }}</td>
                                <td class="py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $claim->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $claim->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('charity-claims.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>
        @endcan
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Patients Summary (حسب صلاحية patients.view) --}}
        @can('patients.view')
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>📋</span> {{ __("Patients") }}
            </h2>
            <div class="flex flex-wrap gap-2 mb-4">
                @can('patients.create')
                    <a href="{{ route('patients.create') }}"
                    class="bg-blue-600  px-4 py-2 rounded text-sm font-bold hover:bg-blue-700 text-white">{{ app()->getLocale() === 'ar' ? 'إضافة مريض جديد' : 'Add Patient' }}</a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ __("Patients") }}</th><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment' }}</th></tr></thead>
                    <tbody>
                        @forelse($recentPatients as $p)
                            <tr class="border-b border-slate-100 hover:bg-slate-50 cursor-pointer" onclick="window.location.href='{{ route('patients.show', $p) }}'">
                                <td class="py-2 text-blue-600 font-medium">{{ $p->name_ar ?? $p->name }}</td>
                                <td class="py-2">{{ $p->payment_type }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا يوجد مرضى' : 'No patients yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ auth()->user()->hasRole('insurance_clerk') ? route('patients.section.insurance') : route('patients.section.followup') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>
        @endcan

        {{-- Financial Summary (حسب صلاحية reports.view أو payments.view) --}}
        @if(auth()->user()->can('reports.view') || auth()->user()->can('payments.view'))
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ app()->getLocale() === 'ar' ? 'ملخص مالي' : 'Financial summary' }}</h2>
            <div class="space-y-4">
                <div class="p-4 bg-slate-50 rounded-lg">
                    <p class="flex justify-between text-slate-600 mb-1"><span>{{ app()->getLocale() === 'ar' ? 'إجمالي الفواتير' : 'Total invoiced' }}</span> <span class="font-bold text-slate-900">@currency($totalInvoiced)</span></p>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                         <div class="bg-blue-500 h-full" style="width: 100%"></div>
                    </div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="flex justify-between text-green-700 mb-1"><span>{{ app()->getLocale() === 'ar' ? 'المحصل' : 'Collected' }}</span> <span class="font-bold text-green-900">@currency($totalCollected)</span></p>
                    <div class="w-full bg-green-200 h-2 rounded-full overflow-hidden">
                         @php $collectRate = $totalInvoiced > 0 ? ($totalCollected / $totalInvoiced) * 100 : 0; @endphp
                         <div class="bg-green-500 h-full" style="width: {{ $collectRate }}%"></div>
                    </div>
                </div>
                <div class="p-4 bg-orange-50 rounded-lg">
                    <p class="flex justify-between text-orange-700 mb-1"><span>{{ app()->getLocale() === 'ar' ? 'المتبقي' : 'Remaining' }}</span> <span class="font-bold text-orange-900">@currency($totalRemaining)</span></p>
                    <div class="w-full bg-orange-200 h-2 rounded-full overflow-hidden">
                         @php $remainRate = $totalInvoiced > 0 ? ($totalRemaining / $totalInvoiced) * 100 : 0; @endphp
                         <div class="bg-orange-500 h-full" style="width: {{ $remainRate }}%"></div>
                    </div>
                </div>
            </div>
            @can('payments.view')
                <a href="{{ route('payments.index') }}" class="mt-4 inline-block text-sm text-blue-600 hover:underline font-medium">{{ __("View Detailed Reports") }}</a>
            @elsecan('reports.view')
                <a href="{{ route('revenue.control-room') }}" class="mt-4 inline-block text-sm text-blue-600 hover:underline font-medium">{{ __("View Detailed Reports") }}</a>
            @endcan
        </div>
        @endif
    </div>


    <script>
        const modal = document.getElementById('dashboardModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalActionType = document.getElementById('modalActionType');
        const dynamicFields = document.getElementById('modalDynamicFields');
        const searchInput = document.getElementById('patientSearchInput');
        const searchResults = document.getElementById('patientSearchResults');
        const selectedIdInput = document.getElementById('selectedPatientId');
        const selectedBadge = document.getElementById('selectedPatientBadge');
        const selectedNameSpan = document.getElementById('selectedPatientName');
        const invoiceSearchSection = document.getElementById('invoiceSearchSection');
        const invoiceSelect = document.getElementById('invoiceSelect');

        const templates = {
            commitment: "{{ app()->getLocale() === 'ar' ? 'أتعهد بأنني سوف أقوم بدفع كافة المصاريف العلاجية الإضافية التي تكون خارج نطاق التغطية التأمينية وعلى هذا يتم التوقيع .' : 'I pledge that I will pay all additional therapeutic expenses that are outside the scope of insurance coverage, and this is signed.' }}",
            non_commitment: "{{ app()->getLocale() === 'ar' ? 'أفيدكم بأنني امتنعت عن التوقيع على محضر التعهد الخطي بسداد كافة المصاريف العلاجية الإضافية التي تكون خارج نطاق التغطية التأمينية ، وقد أُشعرت بالتبعات المترتبة على ذلك وعلى هذا يتم التوقيع .' : 'I inform you that I refused to sign the written pledge to pay all additional therapeutic expenses that are outside the scope of insurance coverage, and I have been informed of the consequences of that, and this is signed.' }}"
        };

        function openDashboardModal(type, data = null) {
            modalActionType.value = type;
            dynamicFields.innerHTML = '';
            clearSelectedPatient();
            searchInput.value = '';
            modal.style.display = 'flex';
            document.getElementById('patientSearchSection').classList.remove('hidden');
            invoiceSearchSection.classList.add('hidden');
            document.getElementById('submitBtn').classList.remove('hidden');

            const isAr = "{{ app()->getLocale() }}" === 'ar';

            if (type === 'view') {
                document.getElementById('patientSearchSection').classList.add('hidden');
                document.getElementById('submitBtn').classList.add('hidden');
                modalTitle.innerText = isAr ? '🔍 عرض التفاصيل' : '🔍 View Details';
                dynamicFields.innerHTML = `<p class="text-center italic py-4 text-slate-400">${isAr ? 'جاري التحميل...' : 'Loading...'}</p>`;

                fetch(`{{ route('api.dashboard.get-details') }}?type=${data.type}&id=${data.id}`)
                    .then(res => res.json())
                    .then(json => {
                        if (json.success) {
                            let html = '<div class="space-y-3">';
                            const d = json.data;
                            if (data.type === 'patient') {
                                html += `<p><strong>${isAr ? 'الاسم' : 'Name'}:</strong> ${d.name}</p>`;
                                if (d.name_ar) html += `<p><strong>${isAr ? 'الاسم (عربي)' : 'Name (Ar)'}:</strong> ${d.name_ar}</p>`;
                                html += `<p><strong>${isAr ? 'رقم الملف' : 'File No'}:</strong> ${d.file_number}</p>`;
                                html += `<p><strong>${isAr ? 'الهوية' : 'Identity'}:</strong> ${d.identity_value}</p>`;
                                html += `<p><strong>${isAr ? 'الهاتف' : 'Phone'}:</strong> ${d.phone || '—'}</p>`;
                                html += `<p><strong>${isAr ? 'نوع الدفع' : 'Payment'}:</strong> ${d.payment_type}</p>`;
                            } else if (data.type === 'visit') {
                                html += `<p><strong>${isAr ? 'المريض' : 'Patient'}:</strong> ${d.patient?.name || '—'}</p>`;
                                html += `<p><strong>${isAr ? 'القسم' : 'Department'}:</strong> ${isAr ? (d.department?.name_ar || d.department?.name) : d.department?.name}</p>`;
                                html += `<p><strong>${isAr ? 'الشيفت' : 'Shift'}:</strong> ${isAr ? (d.shift?.name_ar || d.shift?.name) : d.shift?.name}</p>`;
                                html += `<p><strong>${isAr ? 'نوع الحالة' : 'Case Type'}:</strong> ${d.case_type}</p>`;
                                html += `<p><strong>${isAr ? 'التاريخ' : 'Date'}:</strong> ${new Date(d.visit_date).toLocaleDateString()}</p>`;
                            } else {
                                html += `<p><strong>${isAr ? 'المريض' : 'Patient'}:</strong> ${d.patient?.name || '—'}</p>`;
                                if (d.amount) html += `<p><strong>${isAr ? 'المبلغ' : 'Amount'}:</strong> ${d.amount} SAR</p>`;
                                if (d.total_debt) html += `<p><strong>${isAr ? 'إجمالي الدين' : 'Total Debt'}:</strong> ${d.total_debt} SAR</p>`;
                                if (d.result) html += `<p><strong>${isAr ? 'النتيجة' : 'Result'}:</strong> ${d.result}</p>`;
                                if (d.notes) html += `<p><strong>${isAr ? 'ملاحظات' : 'Notes'}:</strong> ${d.notes}</p>`;
                                if (d.details) html += `<p><strong>${isAr ? 'التفاصيل' : 'Details'}:</strong> ${d.details}</p>`;
                                html += `<p><strong>${isAr ? 'التاريخ' : 'Date'}:</strong> ${d.created_at || d.inventory_date || d.contact_date || d.report_date || ''}</p>`;
                            }
                            html += '</div>';
                            dynamicFields.innerHTML = html;
                        } else {
                            dynamicFields.innerHTML = `<p class="text-red-500">${json.message}</p>`;
                        }
                    });
            } else if (type === 'patient_create') {
                document.getElementById('patientSearchSection').classList.add('hidden');
                modalTitle.innerText = isAr ? '👤 إضافة مريض جديد' : '👤 Add New Patient';
                dynamicFields.innerHTML = `
                    <div class="grid grid-cols-1 gap-3">
                        <div><label class="block text-sm font-bold text-slate-700">${isAr ? 'الاسم' : 'Name'} *</label><input type="text" name="name" required class="w-full rounded border px-3 py-2"></div>
                        <div><label class="block text-sm font-bold text-slate-700">${isAr ? 'رقم الهوية' : 'Identity ID'} *</label><input type="text" name="identity_value" required class="w-full rounded border px-3 py-2"></div>
                        <div class="grid grid-cols-2 gap-2">
                             <div><label class="block text-sm font-bold text-slate-700">${isAr ? 'نوع الهوية' : 'Id Type'}</label><select name="identity_type" class="w-full rounded border px-3 py-2"><option value="national_id">National ID</option><option value="iqama">Iqama</option><option value="passport">Passport</option></select></div>
                             <div><label class="block text-sm font-bold text-slate-700">${isAr ? 'نوع الدفع' : 'Payment'}</label><select name="payment_type" class="w-full rounded border px-3 py-2"><option value="cash">Cash</option><option value="insurance">Insurance</option><option value="charity">Charity</option></select></div>
                        </div>
                        <div><label class="block text-sm font-bold text-slate-700">${isAr ? 'الهاتف' : 'Phone'}</label><input type="text" name="phone" class="w-full rounded border px-3 py-2"></div>
                    </div>
                `;
                // Set patient_id to something to bypass JS validation if needed, or update handleDashboardSubmit
                selectedIdInput.value = 'NEW';
            } else if (type === 'visit') {
                modalTitle.innerText = isAr ? '🩺 إضافة زيارة جديدة' : '🩺 Add New Visit';
                // We'll show Department and Case Type after patient select in `selectPatient` or here if already selected?
                // For Visit, we need Dept and Case Type. I'll add them as hidden fields and show them once patient selected.
                dynamicFields.innerHTML = `
                    <div id="visitExtraFields" class="hidden mt-4 space-y-3 border-t pt-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'القسم' : 'Department'}</label>
                            <select name="department_id" class="w-full rounded border-2 px-3 py-2 focus:border-blue-500">
                                @foreach(\App\Models\Department::where('is_active', true)->get() as $dept)
                                    <option value="{{ $dept->id }}">{{ app()->getLocale() === 'ar' ? ($dept->name_ar ?? $dept->name) : $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'نوع الحالة' : 'Case Type'}</label>
                            <select name="case_type" class="w-full rounded border-2 px-3 py-2 focus:border-blue-500">
                                <option value="clinics">${isAr ? 'عيادات' : 'Clinics'}</option>
                                <option value="emergency">${isAr ? 'طوارئ' : 'Emergency'}</option>
                            </select>
                        </div>
                    </div>
                `;
            } else if (type === 'invoice') {
                modalTitle.innerText = isAr ? '💰 إضافة فاتورة جديدة' : '💰 Add New Invoice';
                dynamicFields.innerHTML = `<p class="py-2 text-slate-600 italic text-sm">${isAr ? 'سيتم تحويلك إلى صفحة إنشاء الفاتورة بعد اختيار المريض.' : 'You will be redirected to create invoice after selecting patient.'}</p>`;
            } else if (type === 'commitment') {
                modalTitle.innerText = isAr ? '📑 إضافة تعهد خطي' : '📑 Add Written Commitment';
                dynamicFields.innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'المبلغ (ريال)' : 'Amount (SAR)'}</label>
                        <input type="number" name="amount" step="0.01" min="0" required class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'ملاحظات' : 'Notes'}</label>
                        <textarea name="notes" rows="3" class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none">${templates.commitment}</textarea>
                    </div>
                `;
            } else if (type === 'non_commitment') {
                modalTitle.innerText = isAr ? '⚠️ إضافة محضر عدم التوقيع' : '⚠️ Add Non-Commitment Report';
                dynamicFields.innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'ملاحظات' : 'Notes'}</label>
                        <textarea name="notes" rows="3" class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none">${templates.non_commitment}</textarea>
                    </div>
                `;
            } else if (type === 'contact') {
                modalTitle.innerText = isAr ? '📄 إضافة محضر اتصال' : '📄 Add Contact Report';
                dynamicFields.innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'النتيجة' : 'Result'}</label>
                        <input type="text" name="result" class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'ملاحظات' : 'Notes'}</label>
                        <textarea name="notes" rows="3" class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none"></textarea>
                    </div>
                `;
            } else if (type === 'debt') {
                modalTitle.innerText = isAr ? '📊 إضافة حصر ديون' : '📊 Add Debt Inventory';
                dynamicFields.innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'إجمالي الدين' : 'Total Debt'}</label>
                        <input type="number" name="total_debt" step="0.01" min="0" required class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">${isAr ? 'التفاصيل' : 'Details'}</label>
                        <textarea name="details" rows="3" class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-blue-500 focus:outline-none"></textarea>
                    </div>
                `;
            }
        }

        function closeDashboardModal() {
            modal.style.display = 'none';
        }

        let searchTimeout;
        function debounceSearch(q) {
            clearTimeout(searchTimeout);
            if (q.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }
            searchTimeout = setTimeout(() => searchPatients(q), 300);
        }

        async function searchPatients(q) {
            try {
                const res = await fetch(`{{ route('api.dashboard.patients-search') }}?q=${encodeURIComponent(q)}`);
                const patients = await res.json();

                searchResults.innerHTML = '';
                if (patients.length > 0) {
                    patients.forEach(p => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-2 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0';
                        const displayName = "{{ app()->getLocale() }}" === 'ar' ? (p.name_ar || p.name) : p.name;
                        div.innerHTML = `<span class="font-bold">${displayName}</span> <span class="text-xs text-slate-500">(${p.file_number} / ${p.identity_value})</span>`;
                        div.onclick = () => selectPatient(p);
                        searchResults.appendChild(div);
                    });
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = `<div class="px-4 py-2 text-slate-400 italic text-sm">{{ app()->getLocale() === 'ar' ? 'لا توجد نتائج' : 'No results found' }}</div>`;
                    searchResults.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        }

        async function selectPatient(p) {
            const type = modalActionType.value;
            selectedIdInput.value = p.id;
            const displayName = "{{ app()->getLocale() }}" === 'ar' ? (p.name_ar || p.name) : p.name;
            selectedNameSpan.innerText = displayName;
            selectedBadge.classList.remove('hidden');
            searchInput.classList.add('hidden');
            searchResults.classList.add('hidden');

            // Fetch invoices for commitment/non-commitment
            if (['commitment', 'non_commitment'].includes(type)) {
                invoiceSearchSection.classList.remove('hidden');
                invoiceSelect.innerHTML = `<option value="">{{ app()->getLocale() === 'ar' ? 'جاري تحميل الفواتير...' : 'Loading invoices...' }}</option>`;
                try {
                    const res = await fetch(`{{ route('api.dashboard.patient-invoices') }}?patient_id=${p.id}`);
                    const invoices = await res.json();
                    invoiceSelect.innerHTML = `<option value="">{{ app()->getLocale() === 'ar' ? 'اختر الفاتورة...' : 'Select invoice...' }}</option>`;
                    invoices.forEach(inv => {
                        const opt = document.createElement('option');
                        opt.value = inv.id;
                        opt.innerText = `#${inv.invoice_number} - Total: ${inv.total} (${inv.status})`;
                        invoiceSelect.appendChild(opt);
                    });
                } catch (e) {
                    console.error('Invoice fetch error:', e);
                }
            }

            // Show visit fields if needed
            const visitFields = document.getElementById('visitExtraFields');
            if (visitFields && type === 'visit') {
                visitFields.classList.remove('hidden');
            }
        }

        function clearSelectedPatient() {
            selectedIdInput.value = '';
            selectedBadge.classList.add('hidden');
            searchInput.classList.remove('hidden');
        }

        async function handleDashboardSubmit(e) {
            e.preventDefault();
            const type = modalActionType.value;
            const patientId = selectedIdInput.value;

            if (!patientId && type !== 'patient_create') {
                alert("{{ app()->getLocale() === 'ar' ? 'يرجى اختيار مريض أولاً' : 'Please select a patient first' }}");
                return;
            }

            // High priority redirects
            if (type === 'invoice') {
                window.location.href = `{{ route('invoices.create') }}?patient_id=${patientId}`;
                return;
            }

            if (type === 'commitment' || type === 'non_commitment') {
                const invoiceId = invoiceSelect.value;
                if (!invoiceId) {
                    alert("{{ app()->getLocale() === 'ar' ? 'يرجى اختيار فاتورة أولاً' : 'Please select an invoice first' }}");
                    return;
                }
                const routeName = type === 'commitment' ? 'invoices.print-commitment' : 'invoices.print-non-commitment';
                // We need to generate the URL dynamically since we don't have the invoiceId in JS directly with Laravels route()
                let url = type === 'commitment' ? "{{ route('invoices.print-commitment', ':id') }}" : "{{ route('invoices.print-non-commitment', ':id') }}";
                url = url.replace(':id', invoiceId);
                window.location.href = url;
                return;
            }

            // AJAX submissions
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerText = "{{ app()->getLocale() === 'ar' ? 'جاري الحفظ...' : 'Saving...' }}";

            const formData = new FormData(e.target);
            const urls = {
                patient_create: "{{ route('api.dashboard.patient.store') }}",
                visit: "{{ route('api.dashboard.visit.store') }}",
                commitment: "{{ route('api.dashboard.written-commitment.store') }}",
                non_commitment: "{{ route('api.dashboard.non-commitment-report.store') }}",
                contact: "{{ route('api.dashboard.contact-report.store') }}",
                debt: "{{ route('api.dashboard.debt-inventory.store') }}"
            };

            try {
                const res = await fetch(urls[type], {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (data.success) {
                    closeDashboardModal();
                    alert(data.message);
                    location.reload(); // Quick way to see changes, could be optimized to update UI only
                } else {
                    alert('Error saving data');
                }
            } catch (error) {
                console.error('Submit error:', error);
                alert('An error occurred');
            } finally {
                btn.disabled = false;
                btn.innerText = "{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}";
            }
        }
    </script>
@endsection
