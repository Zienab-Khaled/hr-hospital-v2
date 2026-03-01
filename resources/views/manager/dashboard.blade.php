@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم المدير - تطوير الإيرادات' : 'Manager Dashboard - Revenue Development')

@section('content')
<div class="space-y-6" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form action="{{ route('manager.dashboard') }}" method="GET" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'من' : 'From' }}</label>
                    <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}"
                           class="rounded-lg border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }}</label>
                    <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}"
                           class="rounded-lg border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 text-slate-900">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold text-sm shadow-md shadow-blue-100 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"/></svg>
                    {{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
                </button>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @php
                    $today = date('Y-m-d');
                    $thisWeek = date('Y-m-d', strtotime('monday this week'));
                    $thisMonth = date('Y-m-01');
                    $thisYear = date('Y-01-01');
                @endphp
                <a href="{{ route('manager.dashboard', ['start_date' => $today, 'end_date' => $today]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ request('start_date') == $today ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ app()->getLocale() === 'ar' ? 'اليوم' : 'Today' }}
                </a>
                <a href="{{ route('manager.dashboard', ['start_date' => $thisWeek, 'end_date' => $today]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ request('start_date') == $thisWeek ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ app()->getLocale() === 'ar' ? 'هذا الأسبوع' : 'This Week' }}
                </a>
                <a href="{{ route('manager.dashboard', ['start_date' => $thisMonth, 'end_date' => $today]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ request('start_date') == $thisMonth ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ app()->getLocale() === 'ar' ? 'هذا الشهر' : 'This Month' }}
                </a>
                <a href="{{ route('manager.dashboard', ['start_date' => $thisYear, 'end_date' => $today]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ request('start_date') == $thisYear ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ app()->getLocale() === 'ar' ? 'هذه السنة' : 'This Year' }}
                </a>
            </div>
        </form>
    </div>
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">
            {{ app()->getLocale() === 'ar' ? 'لوحة تحكم المدير - تطوير الإيرادات' : 'Manager Control Panel - Revenue Development' }}
        </h1>
        <div class="text-sm font-medium text-slate-500 bg-white px-3 py-1 rounded-full shadow-sm border border-slate-200">
            {{ \Carbon\Carbon::now()->format('d M Y') }}
        </div>
    </div>

    {{-- مؤشرات الأداء الرئيسية --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 opacity-10 group-hover:scale-110 transition-transform">💰</div>
            <p class="text-sm text-emerald-800 font-medium">{{ app()->getLocale() === 'ar' ? 'الإيرادات اليوم' : 'Revenue Today' }}</p>
            <p class="text-xl font-black text-emerald-700 mt-1">@currency($revenueToday)</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 opacity-10 group-hover:scale-110 transition-transform">📈</div>
            <p class="text-sm text-blue-800 font-medium">{{ app()->getLocale() === 'ar' ? 'الإجمالي المحصل' : 'Total Collected' }}</p>
            <p class="text-xl font-black text-blue-700 mt-1">@currency($totalCollected)</p>
        </div>
        <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 opacity-10 group-hover:scale-110 transition-transform">🛑</div>
            <p class="text-sm text-rose-800 font-medium">{{ app()->getLocale() === 'ar' ? 'إجمالي الديون' : 'Total Debts' }}</p>
            <p class="text-xl font-black text-rose-700 mt-1">@currency($totalDebts)</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 opacity-10 group-hover:scale-110 transition-transform">⏳</div>
            <p class="text-sm text-amber-800 font-medium">{{ app()->getLocale() === 'ar' ? 'المتبقي غير محصل' : 'Remaining Uncollected' }}</p>
            <p class="text-xl font-black text-amber-700 mt-1">@currency($remainingUncollected)</p>
        </div>
        <div class="bg-white border-2 border-slate-200 rounded-xl p-4 shadow-sm flex flex-col justify-center">
            <p class="text-sm text-slate-700 font-medium">{{ app()->getLocale() === 'ar' ? 'نسبة التحصيل' : 'Collection Rate' }}</p>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-black text-slate-800">{{ $collectionRate }}%</p>
                <div class="flex-grow bg-slate-100 h-2 rounded-full mb-2 overflow-hidden">
                    <div class="bg-blue-500 h-full" style="width: {{ $collectionRate }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row of 3 Analytics Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Card 1: أداء الأقسام (Pie Chart) - NEW --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 overflow-hidden relative min-h-[400px]">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="p-2 bg-slate-100 rounded-lg text-lg">🏥</span>
                {{ request('start_date') ? (app()->getLocale() === 'ar' ? 'أداء الأقسام (الفترة)' : 'Department Performance (Period)') : (app()->getLocale() === 'ar' ? 'أداء الأقسام (اليوم)' : 'Department Performance (Today)') }}
            </h3>

            <div class="h-52 flex items-center justify-center mb-6">
                <canvas id="deptPerformancePieChart" width="220" height="220"></canvas>
            </div>

            <div class="space-y-2 text-xs font-bold overflow-y-auto max-h-48 pr-2">
                @foreach($deptPerformance as $dept)
                <p class="flex justify-between items-center group cursor-pointer hover:bg-slate-50 p-1.5 rounded transition-colors" onclick="showDeptDetails({{ json_encode($dept) }})">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background-color: {{ $dept->color }}"></span>
                        <span class="text-slate-600 truncate max-w-[120px]">{{ $dept->name_ar }}</span>
                    </span>
                    <span class="font-black text-slate-800 underline">@currency($dept->total)</span>
                </p>
                @endforeach
            </div>

            {{-- Detail Overlay --}}
            <div id="deptOverlay" class="hidden absolute inset-0 bg-white/95 backdrop-blur-sm z-50 flex items-center justify-center p-6 transition-all rounded-2xl">
                <button onclick="closeDeptDetails()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-2xl transition-colors">✕</button>
                <div class="text-center space-y-4 max-w-sm w-full">
                    <div id="deptDetailIcon" class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-slate-200"></div>
                    <h2 id="deptDetailName" class="text-xl font-black text-slate-800"></h2>
                    <div id="deptDetailLevel" class="inline-block px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase"></div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[10px] text-slate-500 mb-1 font-bold uppercase">{{ request('start_date') ? (app()->getLocale() === 'ar' ? 'تحصيل الفترة' : 'Period Collection') : (app()->getLocale() === 'ar' ? 'تحصيل اليوم' : 'Daily Collection') }}</p>
                            <p id="deptDetailPatients" class="text-xl font-black text-slate-700"></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[10px] text-slate-500 mb-1 font-bold uppercase">{{ app()->getLocale() === 'ar' ? 'إجمالي التحصيل' : 'Total Amount' }}</p>
                            <p id="deptDetailTotal" class="text-xl font-black text-blue-600"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: توزيع الإيرادات --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 min-h-[400px]">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="p-2 bg-slate-100 rounded-lg text-lg">🍰</span>
                {{ app()->getLocale() === 'ar' ? 'توزيع الإيرادات' : 'Revenue Distribution' }}
            </h3>
            <div class="h-52 flex items-center justify-center mb-6">
                <canvas id="revenuePieChart" width="220" height="220"></canvas>
            </div>
            <div class="space-y-3 text-xs font-bold">
                <div class="flex justify-between items-center p-2 bg-emerald-50 rounded-lg">
                    <span class="flex items-center gap-2"><div class="w-3 h-3 bg-emerald-500 rounded-sm"></div>{{ app()->getLocale() === 'ar' ? 'جمعيات' : 'Charities' }}</span>
                    <span class="text-emerald-700">{{ $revenueCharity }}%</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-orange-50 rounded-lg">
                    <span class="flex items-center gap-2"><div class="w-3 h-3 bg-orange-500 rounded-sm"></div>{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</span>
                    <span class="text-orange-700">{{ $revenueInsurance }}%</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-blue-50 rounded-lg">
                    <span class="flex items-center gap-2"><div class="w-3 h-3 bg-blue-500 rounded-sm"></div>{{ app()->getLocale() === 'ar' ? 'كاش' : 'Cash' }}</span>
                    <span class="text-blue-700">{{ $revenueCash }}%</span>
                </div>
            </div>
        </div>

        {{-- Card 3: حالة الديون --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 min-h-[400px]">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="p-2 bg-slate-100 rounded-lg text-lg">�</span>
                {{ app()->getLocale() === 'ar' ? 'حالة الديون' : 'Debt Status' }}
            </h3>
            <div class="space-y-4">
                <div class="bg-slate-50 p-4 rounded-xl flex justify-between items-center">
                    <span class="font-bold text-slate-600">{{ app()->getLocale() === 'ar' ? 'إجمالي الديون' : 'Total Debts' }}</span>
                    <span class="text-xl font-black text-rose-600">@currency($totalDebts)</span>
                </div>
                <div class="flex justify-between items-center p-3 border-b text-sm">
                    <span class="text-slate-500 font-medium">{{ app()->getLocale() === 'ar' ? 'متأخر 30 يوم' : 'Overdue 30 days' }}</span>
                    <span class="font-bold text-amber-600">@currency($overdue30)</span>
                </div>
                <div class="flex justify-between items-center p-3 border-b text-sm">
                    <span class="text-slate-500 font-medium">{{ app()->getLocale() === 'ar' ? 'متأخر 60 يوم' : 'Overdue 60 days' }}</span>
                    <span class="font-bold text-orange-600">@currency($overdue60)</span>
                </div>
                <div class="flex justify-between items-center p-3 text-sm">
                    <span class="text-slate-500 font-medium">{{ app()->getLocale() === 'ar' ? 'متأخر 90 يوم' : 'Overdue 90 days' }}</span>
                    <span class="font-bold text-rose-600">@currency($overdue90)</span>
                </div>
                <a href="{{ route('patients.section.collection') }}" class="w-full mt-2 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-center font-bold block shadow-md shadow-rose-100 transition-all">
                    {{ app()->getLocale() === 'ar' ? 'إجراءات التحصيل' : 'Collection Actions' }}
                </a>
            </div>
        </div>
    </div>

    {{-- الصف الأوسط: نمو شهري --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
            <span class="p-2 bg-slate-100 rounded-lg text-lg">📉</span>
            {{ app()->getLocale() === 'ar' ? 'نمو الإيرادات الشهرية' : 'Monthly Revenue Growth' }}
        </h3>
        <div class="h-64">
            <canvas id="monthlyLineChart"></canvas>
        </div>
    </div>

    {{-- إحصائيات متقدمة (الأكثر تعاملاً) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="text-xl">🏢</span> {{ app()->getLocale() === 'ar' ? 'أقوى شركات التأمين' : 'Top Insurance' }}
            </h4>
            <div class="space-y-3">
                @foreach($topInsurances as $item)
                <div class="flex justify-between items-center bg-white p-3 rounded-xl shadow-sm border border-slate-100">
                    <span class="font-bold text-sm text-slate-700 truncate max-w-[120px]">{{ $item->name }}</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-[10px] font-black">@currency($item->total)</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="text-xl">🤝</span> {{ app()->getLocale() === 'ar' ? 'أكثر الجمعيات' : 'Top Charities' }}
            </h4>
            <div class="space-y-3">
                @foreach($topCharities as $item)
                <div class="flex justify-between items-center bg-white p-3 rounded-xl shadow-sm border border-slate-100">
                    <span class="font-bold text-sm text-slate-700 truncate max-w-[120px]">{{ $item->name }}</span>
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-[10px] font-black">@currency($item->total)</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="text-xl">🔬</span> {{ app()->getLocale() === 'ar' ? 'الخدمات الأكثر طلباً' : 'Most Used Services' }}
            </h4>
            <div class="space-y-3">
                @foreach($topServices as $item)
                <div class="flex justify-between items-center bg-white p-3 rounded-xl shadow-sm border border-slate-100">
                    <span class="font-bold text-sm text-slate-700 truncate max-w-[120px]">{{ $item->name }}</span>
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-[10px] font-black">{{ $item->qty }} {{ app()->getLocale() === 'ar' ? 'طلب' : 'Requests' }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Row for Alerts --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-rose-600">
            <span>⚠️</span> {{ app()->getLocale() === 'ar' ? 'تنبيهات المتابعة' : 'Follow-up Alerts' }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($alerts as $alert)
            <div class="flex items-center gap-4 bg-rose-50 p-4 rounded-xl border border-rose-100 text-sm">
                <div class="w-8 h-8 bg-rose-200 text-rose-700 rounded-full flex items-center justify-center shrink-0">!</div>
                <span class="font-bold text-rose-800">{{ $alert }}</span>
            </div>
            @empty
            <p class="text-slate-400 text-center italic py-4 col-span-full">{{ app()->getLocale() === 'ar' ? 'لا توجد تنبيهات حالياً' : 'No alerts currently.' }}</p>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRtl = document.documentElement.dir === 'rtl' || document.documentElement.lang === 'ar';

    // 1. أداء الأقسام (Doughnut Chart)
    const deptCtx = document.getElementById('deptPerformancePieChart').getContext('2d');
    const deptData = @json($deptPerformance);
    const hasDeptData = deptData.some(d => d.total > 0);

    new Chart(deptCtx, {
        type: 'doughnut',
        data: {
            labels: hasDeptData ? deptData.map(d => d.name_ar) : ['No Data'],
            datasets: [{
                data: hasDeptData ? deptData.map(d => d.total) : [1],
                backgroundColor: hasDeptData ? deptData.map(d => d.color) : ['#f1f5f9'],
                borderWidth: hasDeptData ? 2 : 0,
                borderColor: '#fff',
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: hasDeptData }
            },
            onClick: (event, elements) => {
                if (hasDeptData && elements.length > 0) {
                    const index = elements[0].index;
                    showDeptDetails(deptData[index]);
                }
            }
        }
    });

    // 2. توزيع الإيرادات (دائري)
    const pieCtx = document.getElementById('revenuePieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: isRtl ? ['جمعيات', 'تأمين', 'كاش'] : ['Charities', 'Insurance', 'Cash'],
            datasets: [{
                data: [{{ $revenueCharity }}, {{ $revenueInsurance }}, {{ $revenueCash }}],
                backgroundColor: ['#10b981', '#f97316', '#3b82f6'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // نمو الإيرادات الشهرية (خط)
    const lineCtx = document.getElementById('monthlyLineChart').getContext('2d');
    const months = @json($monthlyRevenue->pluck('month'));
    const totals = @json($monthlyRevenue->pluck('total'));

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: isRtl ? 'الإيرادات (ريال)' : 'Revenue (SAR)',
                data: totals,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 10 } }
                } ,
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
});

function showDeptDetails(dept) {
    const overlay = document.getElementById('deptOverlay');
    const nameEl = document.getElementById('deptDetailName');
    const iconEl = document.getElementById('deptDetailIcon');
    const patientsEl = document.getElementById('deptDetailPatients');
    const totalEl = document.getElementById('deptDetailTotal');
    const levelEl = document.getElementById('deptDetailLevel');

    nameEl.textContent = dept.name_ar;
    patientsEl.textContent = dept.patient_count;
    totalEl.textContent = new Intl.NumberFormat('ar-SA', { style: 'currency', currency: 'SAR' }).format(dept.total);

    iconEl.style.backgroundColor = dept.color;
    iconEl.textContent = dept.level === 'high' ? '⭐' : (dept.level === 'medium' ? '⚡' : '📉');

    levelEl.textContent = dept.level.toUpperCase();
    levelEl.style.backgroundColor = dept.color + '20';
    levelEl.style.color = dept.color;

    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
}

function closeDeptDetails() {
    const overlay = document.getElementById('deptOverlay');
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
}
</script>

<style>
    @font-face {
        font-family: 'Cairo';
        src: url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800;900&display=swap');
    }
    body { font-family: 'Cairo', sans-serif; }
</style>
@endsection
