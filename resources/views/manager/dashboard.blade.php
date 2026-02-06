@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم المدير - تطوير الإيرادات' : 'Manager Dashboard - Revenue Development')

@section('content')
<div class="space-y-6" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <h1 class="text-2xl font-bold text-slate-800">
        {{ app()->getLocale() === 'ar' ? 'لوحة تحكم المدير - تطوير الإيرادات' : 'Manager Control Panel - Revenue Development' }}
    </h1>

    {{-- مؤشرات الأداء الرئيسية --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-green-800 font-medium">{{ app()->getLocale() === 'ar' ? 'الإيرادات اليوم' : 'Revenue Today' }}</p>
            <p class="text-xl font-bold text-green-700 mt-1">@currency($revenueToday)</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-blue-800 font-medium">{{ app()->getLocale() === 'ar' ? 'الإجمالي المحصل' : 'Total Collected' }}</p>
            <p class="text-xl font-bold text-blue-700 mt-1">@currency($totalCollected)</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-red-800 font-medium">{{ app()->getLocale() === 'ar' ? 'إجمالي الديون' : 'Total Debts' }}</p>
            <p class="text-xl font-bold text-red-700 mt-1">@currency($totalDebts)</p>
        </div>
        <div class="bg-slate-100 border border-slate-300 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-slate-700 font-medium">{{ app()->getLocale() === 'ar' ? 'المتبقي غير محصل' : 'Remaining Uncollected' }}</p>
            <p class="text-xl font-bold text-slate-800 mt-1">@currency($remainingUncollected)</p>
        </div>
        <div class="bg-white border-2 border-slate-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-slate-700 font-medium">{{ app()->getLocale() === 'ar' ? 'نسبة التحصيل' : 'Collection Rate' }}</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $collectionRate }}%</p>
        </div>
    </div>

    {{-- الصف الأوسط: توزيع الإيرادات + نمو شهري + حالة الديون --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'توزيع الإيرادات' : 'Revenue Distribution' }}</h3>
            <div class="h-48 flex items-center justify-center">
                <canvas id="revenuePieChart" width="200" height="200"></canvas>
            </div>
            <div class="mt-2 space-y-1 text-sm">
                <p class="flex justify-between"><span>{{ app()->getLocale() === 'ar' ? 'جمعيات' : 'Charities' }}</span> <span class="font-medium">{{ $revenueCharity }}%</span></p>
                <p class="flex justify-between"><span>{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</span> <span class="font-medium">{{ $revenueInsurance }}%</span></p>
                <p class="flex justify-between"><span>{{ app()->getLocale() === 'ar' ? 'كاش' : 'Cash' }}</span> <span class="font-medium">{{ $revenueCash }}%</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'نمو الإيرادات الشهرية' : 'Monthly Revenue Growth' }}</h3>
            <div class="h-48">
                <canvas id="monthlyLineChart" height="180"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'حالة الديون' : 'Debt Status' }}</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b"><td class="py-2">{{ app()->getLocale() === 'ar' ? 'إجمالي الديون' : 'Total Debts' }}</td><td class="py-2 text-end font-medium">@currency($totalDebts)</td></tr>
                    <tr class="border-b"><td class="py-2">{{ app()->getLocale() === 'ar' ? 'متأخر 30 يوم' : 'Overdue 30 days' }}</td><td class="py-2 text-end font-medium text-amber-600">@currency($overdue30)</td></tr>
                    <tr class="border-b"><td class="py-2">{{ app()->getLocale() === 'ar' ? 'متأخر 60 يوم' : 'Overdue 60 days' }}</td><td class="py-2 text-end font-medium text-orange-600">@currency($overdue60)</td></tr>
                    <tr class="border-b"><td class="py-2">{{ app()->getLocale() === 'ar' ? 'متأخر 90 يوم' : 'Overdue 90 days' }}</td><td class="py-2 text-end font-medium text-red-600">@currency($overdue90)</td></tr>
                </tbody>
            </table>
            <a href="{{ route('patients.section.collection') }}" class="mt-3 inline-block w-full text-center bg-red-100 text-red-700 py-2 rounded-lg text-sm font-medium hover:bg-red-200">
                {{ app()->getLocale() === 'ar' ? 'عرض الحالات الحرجة' : 'View Critical Cases' }}
            </a>
        </div>
    </div>

    {{-- الصف السفلي: متابعة التحصيل + التنبيهات + التقارير --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'متابعة التحصيل' : 'Collection Follow-up' }}</h3>
            <div class="space-y-2 text-sm">
                <p class="flex justify-between"><span>{{ app()->getLocale() === 'ar' ? 'ليوم' : 'Today' }}</span> <span class="font-medium">@currency($collectionToday)</span></p>
                <p class="flex justify-between"><span>{{ app()->getLocale() === 'ar' ? 'تحصيل الشهر' : 'Month collected' }}</span> <span class="font-medium">@currency($collectionMonth)</span></p>
            </div>
            <a href="{{ route('payments.index') }}" class="mt-3 text-sm text-blue-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'تفاصيل المدفوعات' : 'Payment details' }}</a>
        </div>
        <div class="bg-white rounded-xl shadow border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'التنبيهات' : 'Alerts' }}</h3>
            <ul class="space-y-2">
                @foreach($alerts as $alert)
                <li class="flex items-center gap-2 text-sm text-amber-800">
                    <span class="text-amber-500">⚠️</span> {{ $alert }}
                </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-xl shadow border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'التقارير' : 'Reports' }}</h3>
            <ul class="space-y-2 mb-4">
                @foreach($alerts as $alert)
                <li class="flex items-center gap-2 text-sm text-slate-600"><span class="text-amber-500">⚠️</span> {{ $alert }}</li>
                @endforeach
            </ul>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="flex-1 text-center bg-slate-700 text-white py-2 rounded text-sm hover:bg-slate-800">{{ app()->getLocale() === 'ar' ? 'تصدير PDF' : 'Export PDF' }}</a>
                <a href="{{ route('reports.index') }}" class="flex-1 text-center bg-green-700 text-white py-2 rounded text-sm hover:bg-green-800">{{ app()->getLocale() === 'ar' ? 'تصدير Excel' : 'Export Excel' }}</a>
            </div>
        </div>
    </div>

    {{-- أزرار التقارير السريعة --}}
    <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-200">
        <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'تقرير شهري' : 'Monthly Report' }}</a>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">{{ app()->getLocale() === 'ar' ? 'تقرير ربعي' : 'Quarterly Report' }}</a>
        <a href="{{ route('patients.section.collection') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'تقرير الديون' : 'Debt Report' }}</a>
        <a href="{{ route('reports.upload-cluster') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">{{ app()->getLocale() === 'ar' ? 'التجمع الصحي' : 'Health Cluster' }}</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRtl = document.documentElement.dir === 'rtl' || document.documentElement.lang === 'ar';

    // توزيع الإيرادات (دائري)
    const pieCtx = document.getElementById('revenuePieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: isRtl ? ['جمعيات', 'تأمين', 'كاش'] : ['Charities', 'Insurance', 'Cash'],
            datasets: [{
                data: [{{ $revenueCharity }}, {{ $revenueInsurance }}, {{ $revenueCash }}],
                backgroundColor: ['#22c55e', '#f97316', '#3b82f6'],
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
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
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endsection
