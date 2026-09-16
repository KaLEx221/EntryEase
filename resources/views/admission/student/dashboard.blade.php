@extends('layouts.app')

@section('title', 'Student Dashboard | EntryEase')
@section('body-class', 'role-student')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/student.css') }}?v={{ filemtime(public_path('css/student.css')) }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}?v={{ filemtime(public_path('css/exam.css')) }}">
@endsection
@section('role-js')
    <script src="{{ secure_asset('js/student.js') }}" defer></script>
@endsection

@section('content')

@php
    $latestApp   = $applications->first();
    $hasApp      = $latestApp !== null;
    $hasSchedule = $hasApp && $latestApp->examSchedule;
    $hasScore    = $hasApp && $latestApp->examScore;
    $examDone    = $hasApp && optional($latestApp->latestExamAttempt)->status === 'submitted';
    $isUnderReview = $hasApp && $latestApp->status !== 'Pending';
    $isOnlineExam = $hasSchedule && $latestApp->examSchedule->exam_type === 'online';
    $isOnsiteExam = $hasSchedule && $latestApp->examSchedule->exam_type === 'onsite';
    $canTakeExam = $isOnlineExam && !$hasScore && !$examDone
                   && $latestApp->examSchedule->activeQuestions->isNotEmpty();
    $canDownloadPermit = $isOnsiteExam && !$hasScore && !$examDone;
@endphp

<div class="student-page-layer student-dashboard-page">
<div class="student-main-box student-dashboard-shell">

    {{-- ── Welcome ──────────────────────────────────────────── --}}
    <div class="dashboard-card dashboard-welcome-card">
        <div class="welcome-text">
            <h2>Hello, {{ data_get($student, 'full_name') ?: 'Student' }} 👋</h2>
            <p>{{ now()->format('l, F j, Y') }}</p>
        </div>
        @if($hasApp)
            <div class="welcome-status-chip">
                <span class="welcome-status-label">Application</span>
                <span class="status-badge-pill @switch($latestApp->status)
                    @case('Pending') badge-pending @break
                    @case('Under Review') badge-review @break
                    @case('Approved') badge-approved @break
                    @case('Rejected') badge-rejected @break
                    @default badge-pending
                @endswitch">{{ $latestApp->status }}</span>
            </div>
        @endif
    </div>

    {{-- ── Status timeline ─────────────────────────────────── --}}
    @if($hasApp)
    <div class="student-section-card">
        <h3 class="dashboard-section-title">
            <i class="fa-solid fa-list-check"></i><span>Your Progress</span>
        </h3>
        <div class="progress-steps">
            {{-- Step 1: Submitted --}}
            <div class="progress-step done">
                <div class="progress-step-icon"><i class="fa-solid fa-file-pen"></i></div>
                <div class="progress-step-body">
                    <div class="progress-step-label">Submitted</div>
                    <div class="progress-step-sub">{{ $latestApp->created_at->format('M d, Y') }}</div>
                </div>
            </div>
            {{-- Step 2: Under Review --}}
            <div class="progress-step-connector {{ $isUnderReview ? 'done' : '' }}"></div>
            <div class="progress-step {{ $latestApp->status === 'Pending' ? 'active' : ($isUnderReview ? 'done' : 'waiting') }}">
                <div class="progress-step-icon"><i class="fa-solid fa-eye"></i></div>
                <div class="progress-step-body">
                    <div class="progress-step-label">Under Review</div>
                    <div class="progress-step-sub">
                        @if($latestApp->status === 'Pending')
                            Waiting for officer
                        @elseif($latestApp->status === 'Under Review')
                            In progress
                        @else
                            Completed
                        @endif
                    </div>
                </div>
            </div>
            {{-- Step 3: Exam Scheduled --}}
            <div class="progress-step-connector {{ $hasSchedule ? 'done' : '' }}"></div>
            <div class="progress-step {{ $hasSchedule ? 'done' : 'waiting' }}">
                <div class="progress-step-icon">
                    @if($hasSchedule) <i class="fa-solid fa-calendar-check"></i>
                    @else <i class="fa-solid fa-calendar-days"></i>
                    @endif
                </div>
                <div class="progress-step-body">
                    <div class="progress-step-label">Exam Scheduled</div>
                    <div class="progress-step-sub">
                        @if($hasSchedule)
                            {{ $latestApp->examSchedule->exam_date->format('M d, Y') }}
                        @else
                            Waiting for assignment
                        @endif
                    </div>
                </div>
            </div>
            {{-- Step 4: Exam Taken --}}
            <div class="progress-step-connector {{ $hasScore || $examDone ? 'done' : '' }}"></div>
            <div class="progress-step {{ $hasScore || $examDone ? 'done' : ($hasSchedule ? 'active' : 'waiting') }}">
                <div class="progress-step-icon">
                    @if($hasScore || $examDone) <i class="fa-solid fa-pen-to-square"></i>
                    @else <i class="fa-solid fa-pen-to-square"></i>
                    @endif
                </div>
                <div class="progress-step-body">
                    <div class="progress-step-label">Exam Taken</div>
                    <div class="progress-step-sub">
                        @if($hasScore || $examDone) Submitted
                        @elseif($canTakeExam) Ready to take
                        @else Pending
                        @endif
                    </div>
                </div>
            </div>
            {{-- Step 5: Result --}}
            <div class="progress-step-connector {{ $hasScore ? 'done' : '' }}"></div>
            <div class="progress-step {{ $hasScore ? 'done' : 'waiting' }}">
                <div class="progress-step-icon">
                    @if($hasScore)
                        <i class="fa-solid fa-circle-check"></i>
                    @else
                        <i class="fa-solid fa-chart-line"></i>
                    @endif
                </div>
                <div class="progress-step-body">
                    <div class="progress-step-label">Result</div>
                    <div class="progress-step-sub">
                        @if($hasScore)
                            @if($latestApp->examScore->passed)
                                Passed ({{ $latestApp->examScore->percentage }}%)
                            @else
                                Failed ({{ $latestApp->examScore->percentage }}%)
                            @endif
                        @else
                            Not yet available
                        @endif
                    </div>
                </div>
            </div>
            {{-- Step 6: Decision --}}
            <div class="progress-step-connector {{ in_array($latestApp->status, ['Approved','Rejected']) ? 'done' : '' }}"></div>
            <div class="progress-step {{ $latestApp->status === 'Approved' ? 'done approved' : ($latestApp->status === 'Rejected' ? 'done rejected' : 'waiting') }}">
                <div class="progress-step-icon">
                    @if($latestApp->status === 'Approved') <i class="fa-solid fa-graduation-cap"></i>
                    @elseif($latestApp->status === 'Rejected') <i class="fa-solid fa-circle-xmark"></i>
                    @else <i class="fa-solid fa-hourglass-half"></i>
                    @endif
                </div>
                <div class="progress-step-body">
                    <div class="progress-step-label">Decision</div>
                    <div class="progress-step-sub">{{ $latestApp->status }}</div>
                </div>
            </div>
        </div>

        {{-- Contextual call-to-action --}}
        @if($canTakeExam)
            <div class="dash-cta-banner cta-exam">
                <i class="fa-solid fa-pen-to-square"></i>
                <div>
                    <strong>Your exam is ready!</strong>
                    <span>{{ $latestApp->examSchedule->title }} — {{ $latestApp->examSchedule->time_range }}</span>
                </div>
                <a href="{{ route('student.exam.take') }}" class="submit-btn" style="margin-top:0;padding:.6rem 1.25rem">
                    <i class="fa-solid fa-play"></i> Start Exam
                </a>
            </div>
        @elseif($canDownloadPermit)
            <div class="dash-cta-banner cta-exam">
                <i class="fa-solid fa-building"></i>
                <div>
                    <strong>Your on-site exam is scheduled!</strong>
                    <span>{{ $latestApp->examSchedule->title }} — {{ $latestApp->examSchedule->time_range }}</span>
                </div>
                <a href="{{ route('student.exam.take') }}" class="submit-btn" style="margin-top:0;padding:.6rem 1.25rem">
                    <i class="fa-solid fa-download"></i> View Admit Permit
                </a>
            </div>
        @elseif($latestApp->status === 'Approved')
            <div class="dash-cta-banner cta-approved">
                <i class="fa-solid fa-graduation-cap"></i>
                <div>
                    <strong>Congratulations! Your application was approved.</strong>
                    <span>You have been admitted to Grade 7.</span>
                </div>
            </div>
        @elseif($hasScore && !in_array($latestApp->status, ['Approved','Rejected']))
            <div class="dash-cta-banner cta-waiting">
                <i class="fa-solid fa-hourglass-half"></i>
                <div>
                    <strong>Exam result recorded.</strong>
                    <span>The admission office will review your score before making a final decision.</span>
                </div>
                <a href="{{ route('student.results') }}" class="registrar-btn registrar-btn-secondary" style="white-space:nowrap">
                    View Results
                </a>
            </div>
        @endif
    </div>
    @endif

    {{-- ── Quick actions ────────────────────────────────────── --}}
    <div class="student-section-card">
        <h3 class="dashboard-section-title">
            <i class="fa-solid fa-bolt"></i><span>Quick Actions</span>
        </h3>
        <div class="quick-actions dashboard-actions-grid">
            @if(!$hasApp)
                <a href="{{ route('student.apply') }}" class="quick-card dashboard-card qc-highlight">
                    <div class="quick-card-icon"><i class="fa-solid fa-file-pen"></i></div>
                    <h3>Apply Now</h3>
                    <p>Submit your Grade 7 application.</p>
                    <div class="quick-card-arrow"><i class="fa-solid fa-arrow-right"></i><span>Apply</span></div>
                </a>
            @else
                <a href="{{ route('student.applications') }}" class="quick-card dashboard-card">
                    <div class="quick-card-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                    <h3>My Application</h3>
                    <p>View your submitted application and documents.</p>
                    <div class="quick-card-arrow"><i class="fa-solid fa-arrow-right"></i><span>View</span></div>
                </a>
            @endif

            <a href="{{ route('student.exam.schedule') }}" class="quick-card dashboard-card {{ !$hasSchedule ? 'qc-dim' : '' }}">
                <div class="quick-card-icon"><i class="fa-solid fa-calendar-days"></i></div>
                <h3>Exam Schedule</h3>
                <p>
                    @if($hasSchedule)
                        {{ $latestApp->examSchedule->exam_date->format('M d, Y') }} &mdash; {{ $latestApp->examSchedule->time_range }}
                    @else
                        Not yet assigned.
                    @endif
                </p>
                <div class="quick-card-arrow"><i class="fa-solid fa-arrow-right"></i><span>View</span></div>
            </a>

            @if($canTakeExam)
                <a href="{{ route('student.exam.take') }}" class="quick-card dashboard-card qc-highlight">
                    <div class="quick-card-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                    <h3>Take Exam</h3>
                    <p>Your online exam is ready. Click to begin.</p>
                    <div class="quick-card-arrow"><i class="fa-solid fa-play"></i><span>Start</span></div>
                </a>
            @elseif($canDownloadPermit)
                <a href="{{ route('student.exam.take') }}" class="quick-card dashboard-card qc-highlight">
                    <div class="quick-card-icon"><i class="fa-solid fa-building"></i></div>
                    <h3>On-Site Exam</h3>
                    <p>View your admit permit and exam details.</p>
                    <div class="quick-card-arrow"><i class="fa-solid fa-download"></i><span>Permit</span></div>
                </a>
            @elseif($hasScore || $examDone)
                <a href="{{ route('student.results') }}" class="quick-card dashboard-card">
                    <div class="quick-card-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <h3>My Results</h3>
                    <p>
                        @if($hasScore)
                            Status: Completed
                        @else
                            Score being processed.
                        @endif
                    </p>
                    <div class="quick-card-arrow"><i class="fa-solid fa-arrow-right"></i><span>View</span></div>
                </a>
            @else
                <a href="{{ route('student.exam.take') }}" class="quick-card dashboard-card qc-dim">
                    <div class="quick-card-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                    <h3>Take Exam</h3>
                    <p>Available once your exam schedule is assigned.</p>
                    <div class="quick-card-arrow"><i class="fa-solid fa-arrow-right"></i><span>View</span></div>
                </a>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
