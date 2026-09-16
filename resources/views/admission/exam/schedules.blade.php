@extends('layouts.app')

@section('title', 'Exam Schedules | EntryEase')
@php $isAdmin = session('sso_role') === 'admin'; @endphp
@section('body-class', $isAdmin ? 'role-admin' : 'role-registrar')
@section('role-css')
    @if($isAdmin)<link rel="stylesheet" href="{{ secure_asset('css/admin.css') }}">
    @else<link rel="stylesheet" href="{{ secure_asset('css/registrar.css') }}">@endif
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam-schedules.css') }}">
@endsection
@section('sidebar')
    @if($isAdmin)@include('admission.partials.sidebar-admin')
    @else@include('admission.partials.sidebar-registrar')@endif
@endsection

@section('content')

{{-- Header with Back Button --}}
<div class="es-page-header">
    <div class="es-page-header-left">
        <h1 class="es-page-title">Exam Schedules</h1>
        <p class="es-page-sub">Grade 7 Entrance Examination &mdash; {{ $schedules->count() }} schedule{{ $schedules->count() !== 1 ? 's' : '' }}</p>
    </div>
    <div class="ee-page-actions">
        @if(!$isAdmin)
            <a href="{{ route('exam.schedules.create') }}" class="registrar-btn registrar-btn-primary">
                <i class="fa-solid fa-plus"></i> New Schedule
            </a>
        @endif
        <x-back-button variant="secondary" />
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="es-flash es-flash-ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="es-flash es-flash-err"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
@endif

{{-- Empty --}}
@if($schedules->isEmpty())
<div class="es-empty">
    <div class="es-empty-icon"><i class="fa-solid fa-calendar-days"></i></div>
    <h3>No exam schedules yet</h3>
    <p>Create your first schedule to start assigning applicants and adding questions.</p>
    @if(!$isAdmin)
        <a href="{{ route('exam.schedules.create') }}" class="registrar-btn registrar-btn-primary">
            <i class="fa-solid fa-plus"></i> Create First Schedule
        </a>
    @endif
</div>

@else
<div class="es-grid">
    @foreach($schedules as $schedule)
    @php
        $isOfficer  = !$isAdmin;
        $qCount     = $schedule->active_questions_count ?? ($schedule->questions_count ?? 0);
        $hasQ       = $qCount > 0;
        $filled     = $schedule->applicants_count ?? 0;
        $slots      = $schedule->slots;
        $pct        = $slots > 0 ? min(100, round(($filled / $slots) * 100)) : 0;
        $statusColor = match($schedule->status) {
            'upcoming'  => 'es-status-upcoming',
            'ongoing'   => 'es-status-ongoing',
            'completed' => 'es-status-completed',
            'cancelled' => 'es-status-cancelled',
            default     => 'es-status-upcoming',
        };
    @endphp

    <div class="es-card {{ !$hasQ && $isOfficer ? 'es-card-warn' : '' }}">

        {{-- Top bar: status stripe --}}
        <div class="es-card-stripe es-stripe-{{ $schedule->status }}"></div>

        {{-- Card body --}}
        <div class="es-card-body">

            {{-- Title row --}}
            <div class="es-card-title-row">
                <div class="es-card-title-wrap">
                    <h3 class="es-card-title">{{ $schedule->title }}</h3>
                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                        @if($schedule->batch)
                            <span class="es-batch-tag">{{ $schedule->batch }}</span>
                        @endif
                        @if($schedule->exam_type)
                            <span class="exam-type-badge exam-type-{{ $schedule->exam_type }}">
                                <i class="fa-solid {{ $schedule->exam_type === 'online' ? 'fa-globe' : 'fa-building' }}"></i>
                                {{ ucfirst($schedule->exam_type) }}
                            </span>
                        @endif
                    </div>
                </div>
                <span class="es-status-pill {{ $statusColor }}">{{ ucfirst($schedule->status) }}</span>
            </div>

            {{-- Date / time / venue --}}
            <div class="es-info-list">
                <div class="es-info-row">
                    <i class="fa-solid fa-calendar-day"></i>
                    <span>{{ $schedule->exam_date->format('l, F d, Y') }}</span>
                </div>
                <div class="es-info-row">
                    <i class="fa-solid fa-clock"></i>
                    <span>{{ $schedule->time_range }}</span>
                </div>
                @if($schedule->venue)
                <div class="es-info-row">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>{{ $schedule->venue }}</span>
                </div>
                @endif
            </div>

            {{-- Slot bar --}}
            <div class="es-slot-section">
                <div class="es-slot-label">
                    <span>Slots</span>
                    <span class="es-slot-count">{{ $filled }} <span class="es-slot-of">of</span> {{ $slots }}</span>
                </div>
                <div class="es-slot-bar">
                    <div class="es-slot-fill" style="width:{{ $pct }}%"></div>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="es-stats-row">
                <div class="es-stat {{ $hasQ ? 'es-stat-ok' : 'es-stat-warn' }}">
                    <i class="fa-solid {{ $hasQ ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                    <span>{{ $qCount }} question{{ $qCount !== 1 ? 's' : '' }}</span>
                </div>
                <div class="es-stat {{ $filled > 0 ? 'es-stat-ok' : 'es-stat-neutral' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>{{ $filled }} assigned</span>
                </div>
            </div>

            {{-- No-questions alert --}}
            @if(!$hasQ && $isOfficer)
            <div class="es-no-q-alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Add questions so students can take this exam.</span>
            </div>
            @endif

            {{-- Instructions --}}
            @if($schedule->instructions)
            <div class="es-instructions">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ Str::limit($schedule->instructions, 90) }}</span>
            </div>
            @endif

        </div>

        {{-- Action footer --}}
        <div class="es-card-footer">
            @if($isOfficer)
                <a href="{{ route('exam.schedules.questions', $schedule) }}"
                   class="es-action-btn {{ $hasQ ? 'es-btn-ghost' : 'es-btn-primary' }}">
                    <i class="fa-solid fa-list-check"></i>
                    {{ $hasQ ? 'Quiz' : 'Add Quiz' }}
                </a>
                <a href="{{ route('exam.schedules.applicants', $schedule) }}"
                   class="es-action-btn es-btn-ghost">
                    <i class="fa-solid fa-users"></i> Applicants
                </a>
            @endif
            <a href="{{ route('exam.schedules.analytics', $schedule) }}"
               class="es-action-btn es-btn-ghost">
                <i class="fa-solid fa-chart-bar"></i> Analytics
            </a>
            @if($isOfficer)
                <div class="es-footer-right">
                    <a href="{{ route('exam.schedules.edit', $schedule) }}"
                       class="es-icon-btn" title="Edit schedule">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <form action="{{ route('exam.schedules.delete', $schedule) }}" method="POST"
                          onsubmit="return confirm('Delete \'{{ addslashes($schedule->title) }}\'?\nThis cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="es-icon-btn es-icon-danger" title="Delete schedule">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            @endif
        </div>

    </div>
    @endforeach
</div>
@endif

@endsection
