@php
    $user = auth()->user();
    $isManager = $user->hasRole('admin') || $user->hasRole('manager');


    $activeClass = 'bg-blue-600 text-white shadow-md';
    $inactiveClass = 'text-slate-700 hover:bg-blue-600 hover:text-white';
@endphp

<aside class="w-64 bg-white border-e border-slate-200 min-h-[calc(100vh-4rem)] py-4 shadow-sm">
    <nav class="px-3 space-y-1">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
           {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
            <span class="text-lg">📊</span>
            <span>{{ __('Dashboard') }}</span>
        </a>

        {{-- Patients --}}
        @if ($user->can('patients.view') || $isManager)
            <div class="pt-2">
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase mb-2">
                    {{ app()->getLocale() === 'ar' ? 'إدارة المرضى' : 'Patient Management' }}
                </p>

                @foreach ([
                    ['patients.section.charity', '🤝', __('Charity')],
                    ['patients.section.cash', '💵', __('Cash')],
                    ['patients.section.insurance', '🏥', __('Insurance')],
                    ['patients.section.followup', '👤', __('Follow-up')],
                    ['patients.section.collection', '💰', __('Collection')],
                ] as [$route, $icon, $label])
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
                       {{ request()->routeIs($route) ? $activeClass : $inactiveClass }}">
                        <span class="text-lg">{{ $icon }}</span>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Invoices --}}
        @if ($user->can('invoices.view') || $isManager)
            <a href="{{ route('invoices.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
               {{ request()->routeIs('invoices.*') ? $activeClass : $inactiveClass }}">
                <span class="text-lg">🧾</span>
                <span>{{ __('Invoices') }}</span>
            </a>
        @endif

        {{-- Authorizations --}}
        @if ($user->can('authorizations.view') || $isManager)
            <a href="{{ route('authorizations.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
               {{ request()->routeIs('authorizations.*') ? $activeClass : $inactiveClass }}">
                <span class="text-lg">📄</span>
                <span>{{ __('Authorizations') }}</span>
            </a>
        @endif

        {{-- Payments --}}
        @if ($user->can('payments.view') || $user->can('payments.approve') || $isManager)
            <a href="{{ route('payments.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
               {{ request()->routeIs('payments.*') ? $activeClass : $inactiveClass }}">
                <span class="text-lg">💳</span>
                <span>{{ __('Payments') }}</span>
            </a>
        @endif

        {{-- Claims --}}
        @if ($user->can('claims.view') || $isManager)
            <a href="{{ route('claims.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
               {{ request()->routeIs('claims.*') ? $activeClass : $inactiveClass }}">
                <span class="text-lg">📋</span>
                <span>{{ __('Insurance/Charity Claims') }}</span>
            </a>
        @endif

        {{-- Reports --}}
        @if ($user->can('reports.view') || $isManager)
            <a href="{{ route('reports.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
               {{ request()->routeIs('reports.*') ? $activeClass : $inactiveClass }}">
                <span class="text-lg">📈</span>
                <span>{{ __('Reports') }}</span>
            </a>
        @endif

        {{-- System Admin --}}
        @if ($isManager)
            <div class="pt-3 mt-3 border-t border-slate-200">
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase mb-2">
                    {{ app()->getLocale() === 'ar' ? 'إدارة النظام' : 'System Admin' }}
                </p>

                @foreach ([
                    ['departments.*', 'departments.index', '🏢', __('Departments')],
                    ['services.*', 'services.index', '🩺', __('Services')],
                    ['users.*', 'users.index', '👥', __('Users')],
                    ['settings.*', 'settings.index', '⚙️', __('Settings')],
                    ['codes.*', 'codes.upload', '📤', __('Upload Official Codes')],
                ] as [$pattern, $route, $icon, $label])
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
                       {{ request()->routeIs($pattern) ? $activeClass : $inactiveClass }}">
                        <span class="text-lg">{{ $icon }}</span>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Upload Cluster --}}
        @if ($isManager && $user->can('reports.upload_cluster'))
            <div class="pt-3 mt-3 border-t border-slate-200">
                <a href="{{ route('reports.upload-cluster') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
                   {{ request()->routeIs('reports.upload-cluster') ? $activeClass : $inactiveClass }}">
                    <span class="text-lg">📤</span>
                    <span>{{ __('Upload Reports to Cluster') }}</span>
                </a>
            </div>
        @endif

        {{-- Activity Log (for anyone with activity.view: manager, accountant, etc.) --}}
        @if ($user->can('activity.view'))
            <div class="pt-3 mt-3 border-t border-slate-200">
                <a href="{{ route('activity.index') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
                   {{ request()->routeIs('activity.*') ? $activeClass : $inactiveClass }}">
                    <span class="text-lg">📜</span>
                    <span>{{ app()->getLocale() === 'ar' ? 'سجل النشاط' : 'Activity Log' }}</span>
                </a>
            </div>
        @endif

    </nav>
</aside>
