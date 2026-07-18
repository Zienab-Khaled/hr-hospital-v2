@php
    use App\Support\RoleNav;
    $user = auth()->user();
    $isManager = RoleNav::hasSupervisorVisibility($user);
    $invoicesOnly = RoleNav::isInvoicesOnly($user);
    $admissionOnly = RoleNav::isAdmissionOnly($user);
    $isRtl = app()->getLocale() === 'ar';
@endphp

<aside
    class="w-64 flex-shrink-0 sticky top-0 self-start bg-slate-900 text-slate-50 h-screen overflow-y-auto border-e border-slate-700/50 shadow-xl shadow-black/10 relative group">
    {{-- Background Image Overlay (Better implementation) --}}
    <div class="absolute inset-0 opacity-[0.03] pointer-events-none bg-center bg-cover z-0"
        style="background-image: url('https://images.unsplash.com/photo-1576091160550-217359f42f8c?auto=format&fit=crop&q=80&w=2070');">
    </div>

    {{-- Logo/Brand --}}
    <div class="relative z-10 flex items-start gap-3 px-5 py-6 border-b border-white/5 bg-white/5 backdrop-blur-sm">
        <div
            class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/20">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <div class="flex flex-col gap-1">
            <span class="text-[14px] font-bold tracking-tight leading-tight">
                {{ __('IRD -Internal Revenue & Development') }}
            </span>
            <div class="h-[1px] w-12 bg-white/10 my-1"></div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="relative z-10 px-3 py-4 space-y-0.5">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
           {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>{{ __('Dashboard') }}</span>
        </a>

        {{-- Notifications (hidden for collector / محصل) --}}
        @if (!$user->hasRole('collection'))
            @php
                $unreadCount = $user->unreadNotifications->count();
            @endphp
            <a href="{{ route('notifications.index') }}"
                class="flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
               {{ request()->routeIs('notifications.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <div class="flex items-center gap-2.5">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الإشعارات' : 'Notifications' }}</span>
                </div>
                @if ($unreadCount > 0)
                    <span
                        class="flex items-center justify-center min-w-[18px] h-[18px] px-1 bg-amber-500 text-[10px] font-bold rounded-full animate-pulse">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
        @endif

        {{-- Patients Section --}}
        @if (RoleNav::canSeePatientManagement($user))
            <div class="mt-5 pt-4 border-t border-white/10">
                <p class="px-3 py-2 text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2">
                    {{ app()->getLocale() === 'ar' ? 'إدارة المرضى' : 'Patient Management' }}
                </p>
                <a href="{{ route('visits.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('visits.index') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'مكتب الدخول' : 'Admission office' }}</span>
                </a>

                {{-- Charity: مخفي عن موظف التأمين (يرى مرضى التأمين فقط) --}}
                @if (!$user->hasRole('insurance_clerk'))
                    <a href="{{ route('patients.section.charity') }}"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                       {{ request()->routeIs('patients.section.charity') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span>{{ __('Charity') }}</span>
                    </a>
                @endif

                {{-- Cash: مخفي عن موظف التأمين --}}
                @if (!$user->hasRole('insurance_clerk'))
                    <a href="{{ route('patients.section.cash') }}"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                       {{ request()->routeIs('patients.section.cash') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>{{ __('Cash') }}</span>
                    </a>
                @endif

                <a href="{{ route('patients.section.insurance') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('patients.section.insurance') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('Insurance') }}</span>
                </a>

                <a href="{{ route('patients.section.followup') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('patients.section.followup') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ __('Follow-up') }}</span>
                </a>

                {{-- <a href="{{ route('patients.section.collection') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('patients.section.collection') ? 'bg-red-600 ' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('Collection') }}</span>
                </a> --}}
            </div>
        @endif

        {{-- Invoices --}}
        @if ($user->can('invoices.view') || $isManager || $invoicesOnly)
            <a href="{{ route('invoices.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
               {{ request()->routeIs('invoices.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Invoices') }}</span>
            </a>
        @endif

        {{-- Authorizations --}}
        <!-- @if ($user->can('authorizations.view') || $isManager)
<a href="{{ route('authorizations.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
           {{ request()->routeIs('authorizations.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Authorizations') }}</span>
            </a>
@endif -->

        {{-- Payments --}}
        @if (RoleNav::canSeePaymentsMenu($user))
            <a href="{{ route('payments.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
               {{ request()->routeIs('payments.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>{{ __('Payments') }}</span>
            </a>
        @endif

        {{-- تقارير التأمين (رئيس قسم التأمين فقط) --}}
        @if ($user->can('insurance_reports.view'))
            <a href="{{ route('insurance-reports.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
               {{ request()->routeIs('insurance-reports.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'تقارير التأمين' : 'Insurance Reports' }}</span>
            </a>
        @endif

        {{-- المديونيات (حصر الفواتير غير المسددة + تبليغ المريض) --}}
        @if ($user->can('procedures.debt_inventory') || $isManager)
            <a href="{{ route('debts.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
               {{ request()->routeIs('debts.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'المديونيات' : 'Debts' }}</span>
            </a>
        @endif

        {{-- Claims (المطالبات) --}}
        @if (RoleNav::canSeeClaimsMenu($user))
            <a href="{{ route('charity-claims.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
               {{ request()->routeIs('charity-claims.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'المطالبات ' : ' Claims' }}</span>
            </a>
        @endif

        {{-- Delegations --}}
        @if (RoleNav::canSeeDelegations($user))
        <a href="{{ route('delegations.index') }}"
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
           {{ request()->routeIs('delegations.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span>{{ app()->getLocale() === 'ar' ? 'التفويضات' : 'Delegations' }}</span>
        </a>
        @endif

        {{-- System Admin Section --}}
        @if (RoleNav::canSeeSystemAdminSection($user))
            <div class="mt-5 pt-4 border-t border-white/10">
                <p class="px-3 py-2 text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2">
                    {{ app()->getLocale() === 'ar' ? 'إدارة النظام' : 'System Admin' }}
                </p>

                @if (RoleNav::canSeeFullSystemAdmin($user))
                <a href="{{ route('departments.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('departments.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>{{ __('Departments') }}</span>
                </a>

                <a href="{{ route('services.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('services.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <span>{{ __('Services') }}</span>
                </a>
                @endif

                @if ($user->can('insurance_companies.manage'))
                <a href="{{ route('insurance-companies.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('insurance-companies.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'شركات التأمين' : 'Insurance Companies' }}</span>
                </a>
                @endif

                @if ($user->can('charity_entities.manage'))
                <a href="{{ route('charity-entities.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('charity-entities.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الجمعيات الخيرية' : 'Charity Entities' }}</span>
                </a>
                @endif

                @if (RoleNav::canSeeFullSystemAdmin($user))
                <a href="{{ route('users.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>{{ __('Users') }}</span>
                </a>

                <a href="{{ route('shifts.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('shifts.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'المناوبات' : 'Shifts' }}</span>
                </a>

                <a href="{{ route('settings.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('settings.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ __('Settings') }}</span>
                </a>

                <a href="{{ route('codes.upload') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('codes.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span>{{ __('Upload Official Codes') }}</span>
                </a>
                @endif
            </div>
        @endif

        {{-- إيرادات: المحاسب — غرفة التحكم --}}
        @if (RoleNav::canSeeControlRoom($user))
            <a href="{{ route('revenue.control-room') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('revenue.control-room') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'غرفة التحكم (المحاسب)' : 'Control Room (Accountant)' }}</span>
            </a>
        @endif

        {{-- إيرادات: أمين الصندوق — الخزينة --}}
        @if (RoleNav::canSeeTreasury($user))
            <a href="{{ route('revenue.treasury.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('revenue.treasury.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v2m9 4H10m4 0v2a2 2 0 01-2 2h-2m-4 0H5a2 2 0 01-2-2v-2m4 4h4m-4 0h4m-4 0H9" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'أمين الصندوق' : 'Treasury' }}</span>
            </a>
            @if (RoleNav::canOperateTreasury($user))
            <a href="{{ route('cashier.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('cashier.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'استلام الإيداع' : 'Receive deposit' }}</span>
            </a>
            @endif
        @endif

        @if (RoleNav::canSeeRevenueSummary($user))
            <a href="{{ route('revenue.daily-summary') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('revenue.daily-summary') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'ملخص الإيرادات ' : 'Revenue Summary' }}</span>
            </a>
        @endif

        {{-- Reports (الإدارة فقط) --}}
        @if (RoleNav::canSeeReportsMenu($user))
            <a href="{{ route('revenue.control-room') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('revenue.control-room') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'فواتير المحاسب (Control Room)' : 'Accountant Invoices (Control Room)' }}</span>
            </a>
            {{-- أمين الصندوق: مخفي عن المحاسب (يرى Control Room فقط) --}}
            @if (!$user->hasRole('accountant') || RoleNav::isAdministration($user))
                <a href="{{ route('revenue.treasury.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                   {{ request()->routeIs('revenue.treasury.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v2m9 4H10m4 0v2a2 2 0 01-2 2h-2m-4 0H5a2 2 0 01-2-2v-2m4 4h4m-4 0h4m-4 0H9" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'أمين الصندوق' : 'Treasury' }}</span>
                </a>
            @endif
            <a href="{{ route('reports.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
               {{ request()->routeIs('reports.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>{{ __('Reports') }}</span>
            </a>
        @endif

        {{-- Activity Log (الإدارة فقط) --}}
        @if (RoleNav::canSeeActivityLog($user))
            <a href="{{ route('activity.index') }}"
                class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
               {{ request()->routeIs('activity.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'سجل النشاط' : 'Activity Log' }}</span>
            </a>
        @endif
    </nav>
</aside>
