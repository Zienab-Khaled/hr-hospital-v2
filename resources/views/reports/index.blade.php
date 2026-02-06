@extends('layouts.app')

@section('title',
    app()->getLocale() === 'ar'
    ? 'لوحة تحكم المدير – تنمية الإيرادات'
    : 'Manager Control Panel - Revenue
    Development')

@section('content')
    <div class="space-y-4" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

        {{-- مؤشرات الأداء: كلهم في صف واحد جنب بعض --}}
        <div class="flex flex-nowrap gap-4 overflow-x-auto pb-2">
            <div class="flex-1 min-w-[200px] bg-green-100 border border-green-300 rounded-xl p-5 shadow text-center">
                <p class="text-2xl font-bold text-green-800 mb-1">@currency($revenueToday)</p>
                <p class="text-sm text-green-700 font-semibold">
                    {{ app()->getLocale() === 'ar' ? 'الإيرادات اليوم' : "Today's Revenue" }}</p>
            </div>
            <div class="flex-1 min-w-[200px] bg-blue-100 border border-blue-300 rounded-xl p-5 shadow text-center">
                <p class="text-2xl font-bold text-blue-800 mb-1">@currency($totalCollected)</p>
                <p class="text-sm text-blue-700 font-semibold">
                    {{ app()->getLocale() === 'ar' ? 'الإجمالي المحصل' : 'Total Collected' }}</p>
            </div>
            <div class="flex-1 min-w-[200px] bg-red-100 border border-red-300 rounded-xl p-5 shadow text-center">
                <p class="text-2xl font-bold text-red-800 mb-1">@currency($totalDebts)</p>
                <p class="text-sm text-red-700 font-semibold">
                    {{ app()->getLocale() === 'ar' ? 'إجمالي الديون' : 'Total Debts' }}</p>
            </div>
            <div class="flex-1 min-w-[200px] bg-slate-200 border-2 border-slate-400 rounded-xl p-5 shadow text-center">
                <p class="text-2xl font-bold text-slate-800 mb-1">@currency($remainingUncollected)</p>
                <p class="text-sm text-slate-700 font-bold">
                    {{ app()->getLocale() === 'ar' ? 'المتبقي غير محصل' : 'Remaining Uncollected' }}</p>
            </div>
            <div class="flex-1 min-w-[200px] bg-white border-2 border-slate-300 rounded-xl p-5 shadow-md text-center">
                <p class="text-4xl font-bold text-slate-800 mb-1">{{ $collectionRate }}%</p>
                <p class="text-sm text-slate-600 font-bold">
                    {{ app()->getLocale() === 'ar' ? 'نسبة التحصيل' : 'Collection Rate' }}</p>
            </div>
        </div>

        {{-- الصف الأول: 3 كروت --}}
        <div class="grid grid-cols-1 p-4 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 mb-4 text-base">
                    {{ app()->getLocale() === 'ar' ? 'توزيع الإيرادات' : 'Revenue Distribution' }}</h3>
                <div class="h-52 flex items-center justify-center mb-3">
                    <canvas id="revenuePieChart" width="220" height="220"></canvas>
                </div>
                <div class="space-y-2 text-sm font-medium">
                    <p class="flex justify-between">
                        <span>{{ app()->getLocale() === 'ar' ? 'جمعيات' : 'Charities' }}</span>
                        <span class="font-bold">{{ $revenueCharity }}%</span>
                    </p>
                    <p class="flex justify-between">
                        <span>{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</span>
                        <span class="font-bold">{{ $revenueInsurance }}%</span>
                    </p>
                    <p class="flex justify-between">
                        <span>{{ app()->getLocale() === 'ar' ? 'نقدي' : 'Cash' }}</span>
                        <span class="font-bold">{{ $revenueCash }}%</span>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 mb-4 text-base">
                    {{ app()->getLocale() === 'ar' ? 'نمو الإيرادات الشهرية' : 'Monthly Revenue Growth' }}</h3>
                <div class="h-52">
                    <canvas id="monthlyLineChart" height="200"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 mb-4 text-base">
                    {{ app()->getLocale() === 'ar' ? 'حالة الديون' : 'Debt Status' }}</h3>
                <table class="w-full text-sm">
                    <tbody>
                        <tr class="border-b">
                            <td class="py-2.5 font-medium">
                                {{ app()->getLocale() === 'ar' ? 'إجمالي الديون' : 'Total Debts' }}</td>
                            <td class="py-2.5 text-end font-bold">@currency($totalDebts)</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2.5 font-medium">
                                {{ app()->getLocale() === 'ar' ? 'متأخر 30 يوم' : 'Overdue 30 days' }}</td>
                            <td class="py-2.5 text-end font-bold text-amber-600">@currency($overdue30)</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2.5 font-medium">
                                {{ app()->getLocale() === 'ar' ? 'متأخر 60 يوم' : 'Overdue 60 days' }}</td>
                            <td class="py-2.5 text-end font-bold text-orange-600">@currency($overdue60)</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2.5 font-medium">
                                {{ app()->getLocale() === 'ar' ? 'متأخر 90 يوم' : 'Overdue 90 days' }}</td>
                            <td class="py-2.5 text-end font-bold text-red-600">@currency($overdue90)</td>
                        </tr>
                    </tbody>
                </table>
                <a href="{{ route('patients.section.collection') }}"
                    class="mt-4 inline-block w-full text-center   bg-green-100  p-4 py-2.5 rounded-lg text-sm font-bold hover:bg-red-700 shadow">
                    {{ app()->getLocale() === 'ar' ? 'عرض الحالات الحرجة' : 'Show Critical Cases' }}
                </a>
            </div>
        </div>

        {{-- الصف الثاني: 3 كروت --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 mb-4 text-base">
                    {{ app()->getLocale() === 'ar' ? 'متابعة التحصيل' : 'Collection Follow-up' }}</h3>
                <div class="space-y-3 text-sm mb-4">
                    <p class="flex justify-between">
                        <span class="font-medium">{{ app()->getLocale() === 'ar' ? 'ليوم' : 'Today' }}</span>
                        <span class="font-bold">@currency($collectionToday)</span>
                    </p>
                    <p class="flex justify-between">
                        <span
                            class="font-medium">{{ app()->getLocale() === 'ar' ? 'تحصيل الشهر' : 'Month collected' }}</span>
                        <span class="font-bold">@currency($collectionMonth)</span>
                    </p>
                    <p class="flex justify-between">
                        <span
                            class="font-medium">{{ app()->getLocale() === 'ar' ? 'الأسماء الأخرى بتحصيل' : 'Other items' }}</span>
                        <span class="font-bold">-</span>
                    </p>
                </div>
                <a href="{{ route('payments.index') }}" class="text-sm text-blue-600 hover:underline font-semibold">
                    {{ app()->getLocale() === 'ar' ? 'تفاصيل المدفوعات' : 'Payment details' }}
                </a>
            </div>

            <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 mb-4 text-base">
                    {{ app()->getLocale() === 'ar' ? 'التنبيهات' : 'Alerts' }}</h3>
                <ul class="space-y-3">
                    @foreach ($alerts as $alert)
                        <li class="flex items-start gap-2 text-sm text-amber-800 font-medium">
                            <span class="text-amber-500 text-lg">⚠️</span>
                            <span>{{ $alert }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 mb-4 text-base">
                    {{ app()->getLocale() === 'ar' ? 'التقارير' : 'Reports' }}</h3>
                <ul class="space-y-3 mb-5">
                    @foreach ($alerts as $index => $alert)
                        @if ($index < 2)
                            <li class="flex items-start gap-2 text-sm text-slate-600 font-medium">
                                <span class="text-amber-500 text-lg">⚠️</span>
                                <span>{{ $alert }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>

            </div>
        </div>

        {{-- أزرار التقارير والتصدير --}}
        <div class="flex flex-wrap items-center gap-3 p-4 border-t-2 border-slate-200">
            <a href="{{ route('reports.index') }}"
                class="px-6 p-3 py-2.5 bg-blue-600  rounded-lg text-sm font-bold hover:bg-blue-700 shadow">
                {{ app()->getLocale() === 'ar' ? 'تقرير شهري' : 'Monthly Report' }}
            </a>
            <a href="{{ route('reports.index') }}"
                class="px-6 p-3 py-2.5 bg-green-600  rounded-lg text-sm font-bold hover:bg-green-700 shadow">
                {{ app()->getLocale() === 'ar' ? 'تقرير ربعي' : 'Quarterly Report' }}
            </a>
            <a href="{{ route('patients.section.collection') }}"
                class="px-6 p-3 py-2.5 bg-blue-600  rounded-lg text-sm font-bold hover:bg-blue-700 shadow">
                {{ app()->getLocale() === 'ar' ? 'تقرير الديون' : 'Debts Report' }}
            </a>
            <a href="{{ route('reports.upload-cluster') }}"
                class="px-6 p-3 py-2.5 bg-green-600  rounded-lg text-sm font-bold hover:bg-green-700 shadow">
                {{ app()->getLocale() === 'ar' ? 'التجمع الصحي' : 'Health Cluster' }}
            </a>

            <div class="hidden md:block w-px h-8 bg-slate-300 mx-2"></div>

            <a href="{{ route('reports.index') }}"
                class="inline-flex items-center justify-center gap-2 px-6 p-3 py-2.5 bg-red-700 rounded-lg text-sm font-bold hover:bg-red-800 shadow">
                <span class="text-base">📄</span>
                <span>{{ app()->getLocale() === 'ar' ? 'تصدير PDF' : 'Export PDF' }}</span>
            </a>
            <a href="{{ route('reports.index') }}"
                class="inline-flex items-center justify-center gap-2 px-6 p-3 py-2.5 bg-green-700  rounded-lg text-sm font-bold hover:bg-green-800 shadow">
                <span class="text-base">📊</span>
                <span>{{ app()->getLocale() === 'ar' ? 'تصدير Excel' : 'Export Excel' }}</span>
            </a>
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
                    labels: isRtl ? ['جمعيات', 'تأمين', 'نقدي'] : ['Charities', 'Insurance', 'Cash'],
                    datasets: [{
                        data: [{{ $revenueCharity }}, {{ $revenueInsurance }},
                            {{ $revenueCash }}
                        ],
                        backgroundColor: ['#22c55e', '#f97316', '#3b82f6'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                padding: 15
                            }
                        }
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
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
