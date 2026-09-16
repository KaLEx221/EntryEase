@extends('layouts.app')

@section('title', 'My Application | EntryEase')
@section('body-class', 'role-student')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/student.css') }}?v={{ filemtime(public_path('css/student.css')) }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}?v={{ filemtime(public_path('css/exam.css')) }}">
@endsection

@section('content')
<div class="student-page-layer">
<div class="student-main-box">

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="page-header student-section-card">
        <div class="page-header-text">
            <h2><i class="fa-solid fa-clipboard-list"></i><span>My Application</span></h2>
            <p>Your Grade 7 admission application status.</p>
        </div>
        <div class="ee-page-actions">
            <x-back-button />
        </div>
    </div>

    {{-- ── Flash messages ──────────────────────────────────── --}}
    @if(session('success'))
        <div class="apply-success-banner student-section-card">
            <i class="fa-solid fa-circle-check"></i>
            <div>
                <strong>Application submitted!</strong>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('info'))
        <div class="apply-info-banner student-section-card">
            <i class="fa-solid fa-circle-info"></i>
            <div>{{ session('info') }}</div>
        </div>
    @endif

    @forelse($applications as $application)
    @php
        $statusClass = match($application->status) {
            'Pending'      => 'badge-pending',
            'Under Review' => 'badge-review',
            'Approved'     => 'badge-approved',
            'Rejected'     => 'badge-rejected',
            default        => 'badge-pending',
        };
    @endphp

    {{-- ── Application card ───────────────────────────────── --}}
    <div class="app-detail-card student-section-card">
        <div class="app-detail-header">
            <div class="app-detail-header-left">
                <div class="app-detail-grade">
                    <i class="fa-solid fa-graduation-cap"></i>
                    {{ $application->grade_level }}
                </div>
                <span class="status-badge-pill {{ $statusClass }}">{{ $application->status }}</span>
            </div>
            <div class="app-detail-date">
                Submitted {{ $application->created_at->format('F d, Y') }}
            </div>
        </div>

        {{-- Lock notice --}}
        <div class="app-locked-notice">
            <i class="fa-solid fa-lock"></i>
            <span>This application is locked and cannot be edited or deleted.</span>
        </div>

        {{-- Student info --}}
        <div class="app-detail-section">
            <div class="app-detail-section-title">
                <i class="fa-solid fa-user"></i> Personal Information
            </div>
            <div class="app-detail-grid">
                <div class="app-detail-item">
                    <span class="app-detail-label">Full Name</span>
                    <span class="app-detail-value">{{ $student->name ?? '—' }}</span>
                </div>
                <div class="app-detail-item">
                    <span class="app-detail-label">Email</span>
                    <span class="app-detail-value">{{ $student->email ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="app-detail-section">
            <div class="app-detail-section-title">
                <i class="fa-solid fa-folder-open"></i> Submitted Documents
            </div>
            <div class="app-docs-row">
                <div class="app-doc-chip {{ $application->photo_2x2 ? 'uploaded' : 'missing' }}">
                    <i class="fa-solid {{ $application->photo_2x2 ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                    <span>2×2 Photo</span>
                    @if($application->photo_2x2)
                        <a href="{{ route('student.documents.download', 'photo_2x2') }}"
                           class="app-doc-download"
                           title="Download">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    @endif
                </div>
                <div class="app-doc-chip {{ $application->psa_birth_cert ? 'uploaded' : 'missing' }}">
                    <i class="fa-solid {{ $application->psa_birth_cert ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                    <span>PSA Birth Certificate</span>
                    @if($application->psa_birth_cert)
                        <a href="{{ route('student.documents.download', 'psa_birth_cert') }}"
                           class="app-doc-download"
                           title="Download">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Exam schedule --}}
        @if($application->examSchedule)
        <div class="app-detail-section">
            <div class="app-detail-section-title">
                <i class="fa-solid fa-calendar-days"></i> Exam Schedule
            </div>
            <div class="app-detail-grid">
                <div class="app-detail-item">
                    <span class="app-detail-label">Schedule</span>
                    <span class="app-detail-value">{{ $application->examSchedule->title }}</span>
                </div>
                <div class="app-detail-item">
                    <span class="app-detail-label">Date</span>
                    <span class="app-detail-value">{{ $application->examSchedule->exam_date->format('F d, Y') }}</span>
                </div>
                <div class="app-detail-item">
                    <span class="app-detail-label">Time</span>
                    <span class="app-detail-value">{{ $application->examSchedule->time_range }}</span>
                </div>
                @if($application->examSchedule->venue)
                <div class="app-detail-item">
                    <span class="app-detail-label">Venue</span>
                    <span class="app-detail-value">{{ $application->examSchedule->venue }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Exam result --}}
        @if($application->examScore)
        <div class="app-detail-section">
            <div class="app-detail-section-title">
                <i class="fa-solid fa-list-check"></i> Exam Result
            </div>
            <div class="app-score-display">
                <span class="exam-result-badge {{ $application->examScore->passed ? 'passed' : 'failed' }}">
                    <i class="fa-solid {{ $application->examScore->passed ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                    {{ $application->examScore->passed ? 'Passed' : 'Failed' }} — {{ $application->examScore->percentage }}%
                </span>
            </div>
        </div>
        @endif

        {{-- Officer notes --}}
        @if($application->admin_notes)
        <div class="app-detail-section">
            <div class="app-detail-section-title">
                <i class="fa-solid fa-comment-dots"></i> Officer Notes
            </div>
            <div class="app-officer-note">{{ $application->admin_notes }}</div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="app-detail-actions">
            @if($application->examSchedule && !$application->examScore && optional($application->latestExamAttempt)->status !== 'submitted')
                @if($application->examSchedule->activeQuestions->isNotEmpty())
                    <a href="{{ route('student.exam.take') }}" class="submit-btn" style="padding:.65rem 1.4rem">
                        <i class="fa-solid fa-pen-to-square"></i> Take Exam
                    </a>
                @endif
            @endif
            <a href="{{ route('student.exam.schedule') }}" class="btn btn-secondary">
                <i class="fa-solid fa-calendar-days"></i> Exam Schedule
            </a>
        </div>
    </div>

    @empty
    {{-- ── No application yet ───────────────────────────────── --}}
    <div class="student-section-card" style="text-align:center;padding:3rem 2rem">
        <div style="font-size:3rem;color:var(--line);margin-bottom:1rem">
            <i class="fa-solid fa-file-circle-plus"></i>
        </div>
        <h3 style="color:var(--primary);margin-bottom:.5rem">No application yet</h3>
        <p style="color:var(--text-muted);margin-bottom:1.5rem">
            No application on file.
        </p>
        <a href="{{ route('student.apply') }}" class="submit-btn" style="display:inline-flex">
            <i class="fa-solid fa-file-pen"></i> Apply Now
        </a>
    </div>
    @endforelse

</div>
</div>
@endsection
