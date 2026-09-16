@extends('layouts.app')

@section('title', 'Admission Officer Dashboard | EntryEase')
@section('body-class', 'role-registrar')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/registrar.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}">
@endsection
@section('role-js')
    <script src="{{ secure_asset('js/registrar.js') }}" defer></script>
@endsection
@section('sidebar')
    @include('admission.partials.sidebar-registrar')
@endsection

@section('content')

{{-- ── Page header ──────────────────────────────────────────── --}}
<div class="dash-header">
    <div>
        <h1 class="dash-title">Dashboard</h1>
        <p class="dash-sub">{{ now()->format('l, F j, Y') }} &mdash; Welcome back, {{ session('sso_name', 'Officer') }}</p>
    </div>
    <div class="dash-header-actions">
        <a href="{{ route('exam.schedules.create') }}" class="registrar-btn registrar-btn-primary">
            <i class="fa-solid fa-plus"></i> New Schedule
        </a>
        <a href="{{ route('registrar.applications') }}" class="registrar-btn registrar-btn-secondary">
            <i class="fa-solid fa-clipboard-list"></i> Review Queue
        </a>
    </div>
</div>

{{-- ── Stat cards ───────────────────────────────────────────── --}}
<div class="registrar-metrics">
    <div class="registrar-metric-card">
        <div class="registrar-metric-icon"><i class="fa-solid fa-file-lines"></i></div>
        <div class="registrar-metric-value">{{ $stats['total'] }}</div>
        <div class="registrar-metric-label">Total</div>
    </div>
    <div class="registrar-metric-card metric-pending">
        <div class="registrar-metric-icon"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="registrar-metric-value">{{ $stats['pending'] }}</div>
        <div class="registrar-metric-label">Pending</div>
    </div>
    <div class="registrar-metric-card metric-review">
        <div class="registrar-metric-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <div class="registrar-metric-value">{{ $stats['under_review'] }}</div>
        <div class="registrar-metric-label">Under Review</div>
    </div>
    <div class="registrar-metric-card metric-approved">
        <div class="registrar-metric-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="registrar-metric-value">{{ $stats['approved'] }}</div>
        <div class="registrar-metric-label">Approved</div>
    </div>
    <div class="registrar-metric-card metric-rejected">
        <div class="registrar-metric-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="registrar-metric-value">{{ $stats['rejected'] }}</div>
        <div class="registrar-metric-label">Rejected</div>
    </div>
    <div class="registrar-metric-card">
        <div class="registrar-metric-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="registrar-metric-value">{{ $stats['scheduled'] }}</div>
        <div class="registrar-metric-label">Scheduled</div>
    </div>
    <div class="registrar-metric-card">
        <div class="registrar-metric-icon"><i class="fa-solid fa-chart-bar"></i></div>
        <div class="registrar-metric-value">{{ $stats['scored'] }}</div>
        <div class="registrar-metric-label">Scored</div>
    </div>
</div>

{{-- ── Exam Management ──────────────────────────────────────── --}}
<div class="registrar-card">
    <div class="registrar-card-header" style="justify-content:space-between">
        <h3><i class="fa-solid fa-calendar-days"></i> Exam Schedules &amp; Question Banks</h3>
        <div style="display:flex;gap:.5rem">
            <a href="{{ route('exam.schedules.create') }}" class="registrar-btn registrar-btn-primary">
                <i class="fa-solid fa-plus"></i> New Schedule
            </a>
            <a href="{{ route('exam.schedules') }}" class="registrar-btn registrar-btn-secondary">
                View All <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="registrar-card-body">
        @if($examSchedules->isEmpty())
            <div class="dash-empty" style="padding:2rem 1rem">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p>No exam schedules yet. Create one to start adding questions and assigning applicants.</p>
                <a href="{{ route('exam.schedules.create') }}" class="registrar-btn registrar-btn-primary" style="margin-top:.75rem">
                    <i class="fa-solid fa-plus"></i> Create First Schedule
                </a>
            </div>
        @else
            <div class="dash-schedule-grid">
                @foreach($examSchedules as $schedule)
                @php
                    $hasQ = $schedule->active_questions_count > 0;
                    $hasA = $schedule->applicants_count > 0;
                @endphp
                <div class="dash-schedule-card">
                    {{-- Card top: title + status --}}
                    <div class="dash-schedule-top">
                        <div class="dash-schedule-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div class="dash-schedule-head">
                            <div class="dash-schedule-title">{{ $schedule->title }}</div>
                            <span class="exam-status-badge exam-status-{{ $schedule->status }}">{{ ucfirst($schedule->status) }}</span>
                        </div>
                    </div>

                    {{-- Meta row --}}
                    <div class="dash-schedule-meta">
                        <span><i class="fa-solid fa-calendar"></i> {{ $schedule->exam_date->format('M d, Y') }}</span>
                        <span><i class="fa-solid fa-clock"></i> {{ $schedule->time_range }}</span>
                        @if($schedule->venue)
                            <span><i class="fa-solid fa-location-dot"></i> {{ $schedule->venue }}</span>
                        @endif
                    </div>

                    {{-- Readiness indicators --}}
                    <div class="dash-schedule-indicators">
                        <div class="dash-indicator {{ $hasQ ? 'ind-ok' : 'ind-warn' }}">
                            <i class="fa-solid {{ $hasQ ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                            <div>
                                <div class="ind-value">{{ $schedule->active_questions_count }}</div>
                                <div class="ind-label">Question{{ $schedule->active_questions_count !== 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <div class="dash-indicator {{ $hasA ? 'ind-ok' : 'ind-neutral' }}">
                            <i class="fa-solid fa-users"></i>
                            <div>
                                <div class="ind-value">{{ $schedule->applicants_count }}</div>
                                <div class="ind-label">Assigned</div>
                            </div>
                        </div>
                        <div class="dash-indicator ind-neutral">
                            <i class="fa-solid fa-database"></i>
                            <div>
                                <div class="ind-value">{{ $schedule->slots }}</div>
                                <div class="ind-label">Slots</div>
                            </div>
                        </div>
                    </div>

                    {{-- Warning if no questions --}}
                    @if(!$hasQ)
                        <div class="dash-schedule-warn">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>No questions yet — students can't take this exam.</span>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="dash-schedule-actions">
                        <a href="{{ route('exam.schedules.questions', $schedule) }}"
                           class="registrar-btn {{ $hasQ ? 'registrar-btn-secondary' : 'registrar-btn-primary' }} dash-sched-btn">
                            <i class="fa-solid fa-list-check"></i>
                            {{ $hasQ ? 'Quiz' : 'Add Quiz' }}
                        </a>
                        <a href="{{ route('exam.schedules.applicants', $schedule) }}"
                           class="registrar-btn registrar-btn-secondary dash-sched-btn">
                            <i class="fa-solid fa-users"></i> Applicants
                        </a>
                        <a href="{{ route('exam.schedules.analytics', $schedule) }}"
                           class="registrar-btn registrar-btn-secondary dash-sched-btn">
                            <i class="fa-solid fa-chart-bar"></i> Analytics
                        </a>
                        <a href="{{ route('exam.schedules.edit', $schedule) }}"
                           class="registrar-btn registrar-btn-secondary dash-sched-btn">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ── Review queue ─────────────────────────────────────────── --}}
<div class="registrar-card">
    <div class="registrar-card-header" style="justify-content:space-between">
        <h3><i class="fa-solid fa-clipboard-list"></i> Applicant Review Queue</h3>
        <a href="{{ route('registrar.applications') }}" class="registrar-btn registrar-btn-secondary">
            View All <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    <div class="registrar-card-body" style="padding:0">
        @if($applications->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Grade</th>
                            <th>Exam Date</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $app)
                            <tr>
                                <td>
                                    <div class="td-student">
                                        <div class="td-avatar">{{ strtoupper(substr($app->student?->name ?? '?', 0, 1)) }}</div>
                                        <div>
                                            <strong>{{ $app->student?->name ?? 'Unknown' }}</strong>
                                            <div class="td-email">{{ $app->student?->email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="grade-chip">{{ $app->grade_level }}</span></td>
                                <td>
                                    @if($app->examSchedule)
                                        <div style="font-size:.82rem;line-height:1.4">
                                            <div>{{ $app->examSchedule->exam_date->format('M d, Y') }}</div>
                                            @if($app->examSchedule->batch)
                                                <div class="td-muted">{{ $app->examSchedule->batch }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="td-none">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($app->examScore)
                                        <span class="exam-result-badge {{ $app->examScore->passed ? 'passed' : 'failed' }}">
                                            <i class="fa-solid {{ $app->examScore->passed ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                            {{ $app->examScore->percentage }}%
                                        </span>
                                    @else
                                        <span class="td-none">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge @switch($app->status)
                                        @case('Pending') pending @break
                                        @case('Under Review') review @break
                                        @case('Approved') approved @break
                                        @case('Rejected') rejected @break
                                    @endswitch">{{ $app->status }}</span>
                                    @if($app->exam_stage_label)
                                        <div class="td-muted" style="font-size:.75rem;margin-top:.2rem;">{{ $app->exam_stage_label }}</div>
                                    @endif
                                </td>
                                <td class="td-muted">{{ $app->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('registrar.application.view', $app) }}"
                                       class="registrar-btn registrar-btn-primary"
                                       style="padding:.4rem .85rem;font-size:.8rem">
                                        <i class="fa-solid fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($applications->hasPages())
                <div style="padding:1rem 1.4rem;border-top:1px solid var(--line-soft)">
                    {{ $applications->links() }}
                </div>
            @endif
        @else
            <div class="dash-empty">
                <i class="fa-solid fa-inbox"></i>
                <p>No applications to review yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
