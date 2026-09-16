@extends('layouts.app')

@section('title', 'Exam Schedule | EntryEase')
@section('body-class', 'role-student')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/student.css') }}?v={{ filemtime(public_path('css/student.css')) }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}?v={{ filemtime(public_path('css/exam.css')) }}">
@endsection

@section('content')
@php
    $schedule   = $application?->examSchedule;
    $score      = $application?->examScore;
    $attempt    = $application?->latestExamAttempt;
    $submitted  = optional($attempt)->status === 'submitted';
    $hasQ       = $schedule && $schedule->activeQuestions->isNotEmpty();
    $canTake    = $schedule && $hasQ && !$score && !$submitted;
@endphp

<div class="student-page-layer">
<div class="student-main-box">

    {{-- Header --}}
    <div class="page-header student-section-card">
        <div class="page-header-text">
            <h2><i class="fa-solid fa-calendar-days"></i><span>My Exam Schedule</span></h2>
            <p>Your assigned Grade 7 entrance examination slot</p>
        </div>
        <div class="ee-page-actions">
            <x-back-button />
        </div>
    </div>

    {{-- ── No application ──────────────────────────────────── --}}
    @if(!$application)
        <div class="student-section-card exam-student-notice">
            <i class="fa-solid fa-circle-info"></i>
            <div>
                <strong>No application found.</strong>
                <p>Submit a Grade 7 application first before an exam schedule can be assigned.</p>
                <a href="{{ route('student.apply') }}" class="submit-btn notice-action">
                    <i class="fa-solid fa-file-pen"></i><span>Apply Now</span>
                </a>
            </div>
        </div>

    {{-- ── No schedule assigned yet ────────────────────────── --}}
    @elseif(!$schedule)
        <div class="student-section-card exam-student-notice exam-notice-pending">
            <i class="fa-solid fa-hourglass-half"></i>
            <div>
                <strong>Exam schedule not yet assigned.</strong>
                <p>An Admission Officer will assign your exam slot soon. Check back later.</p>
                <div class="exam-app-status-row">
                    <span>Application status:</span>
                    <span class="status-badge-pill
                        @switch($application->status)
                            @case('Pending') badge-pending @break
                            @case('Under Review') badge-review @break
                            @case('Approved') badge-approved @break
                            @case('Rejected') badge-rejected @break
                            @default badge-pending
                        @endswitch">{{ $application->status }}</span>
                </div>
            </div>
        </div>

    {{-- ── Schedule assigned ───────────────────────────────── --}}
    @else
        {{-- Schedule detail card --}}
        <div class="student-section-card exam-schedule-detail-card">
            <div class="exam-schedule-detail-header">
                <div class="exam-schedule-detail-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h3>{{ $schedule->title }}</h3>
                    <span class="exam-status-badge exam-status-{{ $schedule->status }}">
                        {{ ucfirst($schedule->status) }}
                    </span>
                </div>
            </div>

            <div class="exam-schedule-detail-grid">
                <div class="exam-detail-item">
                    <div class="exam-detail-label"><i class="fa-solid fa-calendar"></i> Date</div>
                    <div class="exam-detail-value">{{ $schedule->exam_date->format('l, F d, Y') }}</div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label"><i class="fa-solid fa-clock"></i> Time</div>
                    <div class="exam-detail-value">{{ $schedule->time_range }}</div>
                </div>
                @if($schedule->venue)
                <div class="exam-detail-item">
                    <div class="exam-detail-label"><i class="fa-solid fa-location-dot"></i> Venue</div>
                    <div class="exam-detail-value">{{ $schedule->venue }}</div>
                </div>
                @endif
                @if($schedule->batch)
                <div class="exam-detail-item">
                    <div class="exam-detail-label"><i class="fa-solid fa-layer-group"></i> Batch</div>
                    <div class="exam-detail-value">{{ $schedule->batch }}</div>
                </div>
                @endif
                <div class="exam-detail-item">
                    <div class="exam-detail-label"><i class="fa-solid fa-list-check"></i> Questions</div>
                    <div class="exam-detail-value">
                        @if($hasQ)
                            {{ $schedule->activeQuestions->count() }} question{{ $schedule->activeQuestions->count() !== 1 ? 's' : '' }}
                        @else
                            <span style="color:var(--warning);font-size:.85rem">Not ready yet</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($schedule->instructions)
                <div class="exam-instructions-box">
                    <div class="exam-instructions-title">
                        <i class="fa-solid fa-circle-info"></i> Instructions
                    </div>
                    <div class="exam-instructions-body">{{ $schedule->instructions }}</div>
                </div>
            @endif
        </div>

        {{-- ── Exam status banner ───────────────────────────── --}}
        @if($score || $submitted)
            {{-- Already done --}}
            <div class="student-section-card exam-student-notice" style="background:var(--success-bg);border-color:rgba(22,163,74,.2)">
                <i class="fa-solid fa-circle-check" style="color:var(--success)"></i>
                <div>
                    <strong>Exam submitted.</strong>
                    <p>Your answers have been recorded. Results will be communicated by the admissions team.</p>
                </div>
            </div>

        @elseif(!$hasQ)
            {{-- Schedule exists but no questions yet --}}
            <div class="student-section-card exam-student-notice exam-notice-pending">
                <i class="fa-solid fa-hourglass-half"></i>
                <div>
                    <strong>Exam not ready yet.</strong>
                    <p>The Admission Officer hasn't added questions to this schedule yet. You'll be able to take the exam once questions are available.</p>
                </div>
            </div>

        @elseif($schedule->status === 'cancelled')
            <div class="student-section-card exam-student-notice" style="background:var(--danger-bg);border-color:rgba(220,38,38,.2)">
                <i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i>
                <div>
                    <strong>This exam schedule has been cancelled.</strong>
                    <p>Please contact the Admission Office for further instructions.</p>
                </div>
            </div>

        @else
            {{-- Ready to take --}}
            <div class="student-section-card exam-student-notice" style="background:var(--primary-light);border-color:rgba(124,48,65,.2)">
                <i class="fa-solid fa-pen-to-square" style="color:var(--primary)"></i>
                <div>
                    <strong>Your exam is ready!</strong>
                    <p>{{ $schedule->activeQuestions->count() }} questions &mdash; make sure you have a stable connection before starting. You can only submit once.</p>
                </div>
            </div>
        @endif

        {{-- ── Actions ──────────────────────────────────────── --}}
        <div class="exam-student-actions student-section-card" style="padding:1.1rem 1.5rem">
            @if($canTake)
                <a href="{{ route('student.exam.take') }}" class="submit-btn">
                    <i class="fa-solid fa-play"></i><span>Start Exam Now</span>
                </a>
            @endif
        </div>
    @endif

</div>
</div>
@endsection
