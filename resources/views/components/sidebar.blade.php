@php
    $user = auth()->user();
    $isManager = $user->hasRole('admin') || $user->hasRole('manager');
    $isRtl = app()->getLocale() === 'ar';
@endphp

<aside class="w-64 flex-shrink-0 sticky top-0 self-start bg-white h-screen overflow-y-auto border-e border-slate-200">
    {{-- Logo/Brand --}}
    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-slate-200">
        <div class="w-7 h-7 bg-emerald-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <span class="text-base font-bold text-slate-800">{{ __('Hospital') }}</span>
    </div>

    {{-- Navigation --}}
    <nav class="px-3 py-3">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
           {{ request()->routeIs('dashboard') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>{{ __('Dashboard') }}</span>
        </a>

        {{-- Patients Section --}}
        @if ($user->can('patients.view') || $isManager)
            <div class="mt-3 mb-1">
                <p class="px-3 py-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wide">
                    {{ app()->getLocale() === 'ar' ? 'إدارة المرضى' : 'Patient Management' }}
                </p>

                <a href="{{ route('patients.section.charity') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('patients.section.charity') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span>{{ __('Charity') }}</span>
                </a>

                <a href="{{ route('patients.section.cash') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('patients.section.cash') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ __('Cash') }}</span>
                </a>

                <a href="{{ route('patients.section.insurance') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('patients.section.insurance') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('Insurance') }}</span>
                </a>

                <a href="{{ route('patients.section.followup') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('patients.section.followup') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ __('Follow-up') }}</span>
                </a>

                <a href="{{ route('patients.section.collection') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('patients.section.collection') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('Collection') }}</span>
                </a>
            </div>
        @endif

        {{-- Invoices --}}
        @if ($user->can('invoices.view') || $isManager)
            <a href="{{ route('invoices.index') }}"
                class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
               {{ request()->routeIs('invoices.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Invoices') }}</span>
            </a>
        @endif

        {{-- Authorizations --}}
        @if ($user->can('authorizations.view') || $isManager)
            <a href="{{ route('authorizations.index') }}"
                class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
               {{ request()->routeIs('authorizations.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Authorizations') }}</span>
            </a>
        @endif

        {{-- Payments --}}
        @if ($user->can('payments.view') || $user->can('payments.approve') || $isManager)
            <a href="{{ route('payments.index') }}"
                class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
               {{ request()->routeIs('payments.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>{{ __('Payments') }}</span>
            </a>
        @endif

        {{-- Claims --}}
        @if ($user->can('claims.view') || $isManager)
            <a href="{{ route('claims.index') }}"
                class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
               {{ request()->routeIs('claims.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>{{ app()->getLocale() === 'ar' ? 'المطالبات' : 'Claims' }}</span>
            </a>
        @endif

        {{-- System Admin Section --}}
        @if ($isManager)
            <div class="mt-3 mb-1">
                <p class="px-3 py-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wide">
                    {{ app()->getLocale() === 'ar' ? 'إدارة النظام' : 'System Admin' }}
                </p>

                <a href="{{ route('departments.index') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('departments.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>{{ __('Departments') }}</span>
                </a>

                <a href="{{ route('services.index') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('services.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <span>{{ __('Services') }}</span>
                </a>

                <a href="{{ route('insurance-companies.index') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('insurance-companies.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'شركات التأمين' : 'Insurance Companies' }}</span>
                </a>

                <a href="{{ route('charity-entities.index') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('charity-entities.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الجمعيات الخيرية' : 'Charity Entities' }}</span>
                </a>

                <a href="{{ route('users.index') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('users.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>{{ __('Users') }}</span>
                </a>

                <a href="{{ route('settings.index') }}"
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('settings.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
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
                    class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
                   {{ request()->routeIs('codes.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span>{{ __('Upload Official Codes') }}</span>
                </a>
            </div>
        @endif

        {{-- Reports --}}
        @if ($user->can('reports.view') || $isManager)
            <a href="{{ route('reports.index') }}"
                class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
               {{ request()->routeIs('reports.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>{{ __('Reports') }}</span>
            </a>
        @endif

        {{-- Activity Log --}}
        @if ($user->can('activity.view'))
            <a href="{{ route('activity.index') }}"
                class="flex items-center gap-2.5 px-3 py-1.5 mb-0.5 rounded-md text-sm font-normal transition-colors
               {{ request()->routeIs('activity.*') ? 'bg-red-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
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
