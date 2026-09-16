<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EntryEase')</title>
    <script>
        if (window.self !== window.top) {
            document.documentElement.classList.add('is-framed');
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @yield('role-css')
    @stack('styles')
    @stack('head-scripts')
</head>
@php
    $isEmbeddedRequest = request()->boolean('embedded')
        || request()->headers->get('Sec-Fetch-Dest') === 'iframe';
    $isEmbeddedBody = (bool) session('sso_embedded', false) || $isEmbeddedRequest;
@endphp
<body class="@yield('body-class') {{ $isEmbeddedBody ? 'is-embedded' : '' }}">
    @php
        $isAdminRoute     = request()->routeIs('admin.*');
        $isRegistrarRoute = request()->routeIs('registrar.*') || request()->routeIs('exam.*');
        $isStudentRoute   = request()->routeIs('student.*');
        $isEmbedded       = $isEmbeddedBody;

        $role      = session('sso_role', 'student');
        $userName  = session('sso_name', 'User');
        $initial   = strtoupper(substr($userName, 0, 1)) ?: 'U';

        $roleLabel = match($role) {
            'admin'             => 'Admin',
            'registrar'         => 'Admission Officer',
            'hr'                => 'Admission Officer',
            'admission_officer' => 'Admission Officer',
            default             => 'Student',
        };
    @endphp

    <div class="app-shell">

        {{-- ── Navbar — hidden when embedded in portal iframe ─────────── --}}
        @if(!$isEmbedded)
        <header class="app-header" role="banner">
            <div class="app-header-inner">

                {{-- Brand --}}
                <a href="{{ $isAdminRoute ? route('admin.dashboard') : ($isRegistrarRoute ? route('registrar.dashboard') : route('student.dashboard')) }}"
                   class="app-brand" aria-label="EntryEase home">
                    <div class="app-brand-badge" aria-hidden="true">E</div>
                    <div>
                        <div class="app-brand-name">EntryEase</div>
                        <small class="app-brand-sub">Grade 7 Admission</small>
                    </div>
                </a>

                {{-- Nav links --}}
                <nav class="app-nav" aria-label="Main navigation">
                    @if($isAdminRoute)
                        @include('admission.partials.header-nav-admin')
                    @elseif($isRegistrarRoute)
                        @include('admission.partials.header-nav-registrar')
                    @else
                        @include('admission.partials.header-nav-student')
                    @endif
                </nav>

                @include('admission.partials.notification-bell')

                {{-- User chip --}}
                <div class="app-user-chip" aria-label="Signed in as {{ $userName }}">
                    <div class="app-user-avatar" aria-hidden="true">{{ $initial }}</div>
                    <span>{{ $userName }}</span>
                    <span class="app-role-badge">{{ $roleLabel }}</span>
                </div>

            </div>
        </header>
        @endif

        {{-- ── Main ───────────────────────────────────────────────────── --}}
        <main class="app-main {{ $isEmbedded ? 'app-main--embedded' : '' }}" id="main-content">

            {{-- Sidebar layout wrapper --}}
            @if(View::hasSection('sidebar'))
            <div class="app-layout-with-sidebar">
                <aside class="app-sidebar" aria-label="Sidebar navigation">
                    @yield('sidebar')
                </aside>
                <div class="app-sidebar-content">
            @endif

            {{-- Optional page title --}}
            @hasSection('page-title')
                <div class="app-page-title">
                    <h2>@yield('page-title')</h2>
                    @hasSection('page-subtitle')
                        <p>@yield('page-subtitle')</p>
                    @endif
                </div>
            @endif

            <div class="app-content">
                @if($isStudentRoute)
                    @yield('content')
                @else
                    <div class="role-page-layer">
                        <div class="role-main-box">
                            @yield('content')
                        </div>
                    </div>
                @endif
            </div>

            @if(View::hasSection('sidebar'))
                </div>{{-- .app-sidebar-content --}}
            </div>{{-- .app-layout-with-sidebar --}}
            @endif

        </main>
    </div>

    @php
        $initialNotifications = [];
        if (session('success')) {
            $initialNotifications[] = ['title' => 'Success', 'message' => session('success'), 'type' => 'success'];
        }
        if (session('error')) {
            $initialNotifications[] = ['title' => 'Error', 'message' => session('error'), 'type' => 'error'];
        }
        if (session('info')) {
            $initialNotifications[] = ['title' => 'Notice', 'message' => session('info'), 'type' => 'info'];
        }
    @endphp
    <script>
        (function () {
            var fromSession = @json($initialNotifications);
            window.ENTRYEASE_INITIAL_NOTIFICATIONS = (window.ENTRYEASE_INITIAL_NOTIFICATIONS || []).concat(fromSession);
        })();
    </script>

    @php
        $useModuleNotifications = ! $isEmbeddedBody;
        $broadcastEnabled = $useModuleNotifications && \App\Support\DeorisBroadcast::isEnabled();
        $broadcastDriver = config('broadcasting.default');
        $broadcastKey = config('broadcasting.connections.reverb.key')
            ?: config('broadcasting.connections.pusher.key');
        $studentIdForRealtime = null;
        if ($isStudentRoute && isset($student)) {
            $studentIdForRealtime = $student->id ?? null;
        }
    @endphp
    @if($broadcastEnabled && $broadcastKey)
        <script>
            window.DEORIS_BROADCAST = {
                enabled: true,
                key: @json($broadcastKey),
                cluster: @json(env('PUSHER_APP_CLUSTER', 'mt1')),
                host: @json(env('REVERB_HOST', env('PUSHER_HOST', '127.0.0.1'))),
                port: @json((int) env('REVERB_PORT', env('PUSHER_PORT', 8080))),
                scheme: @json(env('REVERB_SCHEME', env('PUSHER_SCHEME', 'http'))),
                authEndpoint: @json(url('/broadcasting/auth')),
                debug: @json((bool) config('app.debug')),
            };
            window.DEORIS_REALTIME = {
                admissionsChannel: @json($isRegistrarRoute || $isAdminRoute ? 'entryease.admissions' : null),
                studentChannel: @json($studentIdForRealtime ? 'entryease.student.'.$studentIdForRealtime : null),
                admissionEvents: ['ApplicationSubmitted','ApplicationStatusChanged','AdmissionApproved','AdmissionRejected','ExamAssigned','ExamCompleted','ExamScoreReleased'],
                studentEvents: ['ApplicationSubmitted','ApplicationStatusChanged','ExamAssigned','ExamCompleted','ExamScoreReleased'],
            };
        </script>
        <script src="{{ secure_asset('js/deoris-echo.js') }}?v={{ filemtime(public_path('js/deoris-echo.js')) }}" defer></script>
        <script src="{{ secure_asset('js/deoris-realtime.js') }}?v={{ filemtime(public_path('js/deoris-realtime.js')) }}" defer></script>
    @endif

    @if($useModuleNotifications)
        <script src="{{ secure_asset('js/notifications.js') }}?v={{ filemtime(public_path('js/notifications.js')) }}" defer></script>
    @endif
    <script src="{{ secure_asset('js/app.js') }}" defer></script>
    @yield('role-js')
    @stack('scripts')
</body>
</html>
