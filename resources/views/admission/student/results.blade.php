@extends('layouts.app')

@section('title', 'My Results | EntryEase')
@section('body-class', 'role-student')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/student.css') }}?v={{ filemtime(public_path('css/student.css')) }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}?v={{ filemtime(public_path('css/exam.css')) }}">
@endsection

@section('content')
    <div class="student-page-layer">
        <div class="student-main-box">

            <div class="page-header student-section-card">
                <div class="page-header-text">
                    <h2><i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>My Exam Results</span></h2>
                    <p>Your Grade 7 entrance examination result</p>
                </div>
                <div class="ee-page-actions">
                    <x-back-button route="student.dashboard" />
                </div>
            </div>

            @if(!$application)
                <div class="student-section-card exam-student-notice">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>No application found.</strong>
                        <p>Submit a Grade 7 application first.</p>
                        <a href="{{ route('student.apply') }}" class="submit-btn notice-action">
                            <i class="fa-solid fa-file-pen"></i><span>Apply Now</span>
                        </a>
                    </div>
                </div>

            @elseif(!$application->examScore)
                <div class="student-section-card exam-student-notice exam-notice-pending">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <div>
                        <strong>Results not yet available.</strong>
                        <p>Your exam score has not been recorded yet. Please check back after your examination date.</p>
                        @if($application->examSchedule)
                            <div class="exam-app-status-row">
                                <span><i class="fa-solid fa-calendar"></i> Exam date:</span>
                                <strong>{{ $application->examSchedule->exam_date->format('F d, Y') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

            @else
                @php
                    $schedule = $application->examSchedule;
                @endphp

                {{-- Result hero card --}}
                <div class="student-section-card exam-result-hero result-passed">
                    <div class="exam-result-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="exam-result-verdict">Completed</div>
                </div>

                {{-- Status Info --}}
                <div class="student-section-card">
                    <h3 class="dashboard-section-title">
                        <i class="fa-solid fa-list-check"></i><span>Exam Status</span>
                    </h3>
                    <div class="exam-score-breakdown">
                        <div class="exam-breakdown-item">
                            <span class="exam-breakdown-label">Status</span>
                            <span class="exam-result-badge passed">
                                Completed
                            </span>
                        </div>
                        @if($schedule)
                            <div class="exam-breakdown-item">
                                <span class="exam-breakdown-label">Exam Date</span>
                                <span class="exam-breakdown-value">{{ $schedule->exam_date->format('F d, Y') }}</span>
                            </div>
                            <div class="exam-breakdown-item">
                                <span class="exam-breakdown-label">Exam Type</span>
                                <span class="exam-type-badge exam-type-{{ $schedule->exam_type }}">
                                    <i class="fa-solid {{ $schedule->exam_type === 'online' ? 'fa-globe' : 'fa-building' }}"></i>
                                    {{ ucfirst($schedule->exam_type) }}
                                </span>
                            </div>
                            @if($schedule->venue)
                                <div class="exam-breakdown-item">
                                    <span class="exam-breakdown-label">Venue</span>
                                    <span class="exam-breakdown-value">{{ $schedule->venue }}</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Application status --}}
                <div class="student-section-card">
                    <h3 class="dashboard-section-title">
                        <i class="fa-solid fa-clipboard-list"></i><span>Application Status</span>
                    </h3>
                    <div class="result-status-row">
                        <span>Current status:</span>
                        <span class="status-badge-pill
                            @switch($application->status)
                                @case('Pending') badge-pending @break
                                @case('Under Review') badge-review @break
                                @case('Approved') badge-approved @break
                                @case('Rejected') badge-rejected @break
                                @default badge-pending
                            @endswitch">{{ $application->status }}</span>
                    </div>
                    @if($application->admin_notes)
                        <div class="officer-note">
                            <strong>Officer Notes:</strong>
                            <p>{{ $application->admin_notes }}</p>
                        </div>
                    @endif
                </div>

                <div class="exam-student-actions">
                    <a href="{{ route('student.exam.schedule') }}" class="btn btn-secondary">
                        <i class="fa-solid fa-calendar-days"></i><span>View Exam Schedule</span>
                    </a>
                </div>
            @endif

        </div>
    </div>
@endsection
