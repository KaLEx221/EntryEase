@extends('layouts.app')

@section('title', 'Exam Details | EntryEase')
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
            <h2><i class="fa-solid fa-building"></i><span>On-site Exam Details</span></h2>
            <p>{{ $schedule->title }}</p>
        </div>
        <div class="ee-page-actions">
            <span class="exam-status-badge exam-status-{{ $schedule->status }}">{{ ucfirst($schedule->status) }}</span>
            <span class="exam-type-badge exam-type-onsite">
                <i class="fa-solid fa-building"></i> On-site Exam
            </span>
            <a href="{{ route('student.exam.permit.download', $application) }}"
               class="submit-btn">
                <i class="fa-solid fa-download"></i>
                <span>Download Permit (PDF)</span>
            </a>
            <x-back-button variant="secondary" />
        </div>
    </div>

    @if(!$permit->hasSeating())
    <div class="student-section-card exam-student-notice exam-notice-pending">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            <strong>Room and seat not assigned yet.</strong>
            <p>Your Admission Officer will set your room and seat before exam day. Check back here for updates.</p>
        </div>
    </div>
    @endif

    {{-- ── Instructions banner ─────────────────────────────── --}}
    <div class="student-section-card exam-student-notice">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>Important Information.</strong>
            <p>Please arrive at least 15 minutes before your exam. Bring this permit and a valid ID. The exam will be conducted in the specified room on the date and time shown below.</p>
        </div>
    </div>

    {{-- ── Exam Permit Card ──────────────────────────────── --}}
    <div class="student-section-card exam-onsite-permit">
        <div class="permit-header">
            <div>
                <h3>Exam Admission Permit</h3>
                <p class="permit-id">Permit ID: {{ sprintf('%05d', $application->id) }}</p>
            </div>
            <span class="permit-grade-badge">{{ $application->grade_level }}</span>
        </div>

        <div class="permit-content">
            {{-- Student Info --}}
            <div class="permit-section">
                <h4>Student Information</h4>
                <div class="permit-info-grid">
                    <div class="permit-info-row">
                        <span class="permit-label">Name</span>
                        <span class="permit-value">{{ $student->name ?? '—' }}</span>
                    </div>
                    <div class="permit-info-row">
                        <span class="permit-label">Email</span>
                        <span class="permit-value">{{ $student->email ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Exam Details --}}
            <div class="permit-section">
                <h4>Exam Details</h4>
                <div class="permit-info-grid">
                    <div class="permit-info-row">
                        <span class="permit-label">Exam Title</span>
                        <span class="permit-value">{{ $schedule->title }}</span>
                    </div>
                    <div class="permit-info-row">
                        <span class="permit-label">Grade Level</span>
                        <span class="permit-value">{{ $schedule->batch ?? 'Grade 7' }}</span>
                    </div>
                </div>
            </div>

            {{-- Schedule & Location --}}
            <div class="permit-section">
                <h4>Schedule & Location</h4>
                <div class="permit-info-grid">
                    <div class="permit-info-row">
                        <span class="permit-label">Date</span>
                        <span class="permit-value">
                            <i class="fa-solid fa-calendar"></i>
                            {{ $schedule->exam_date->format('l, F d, Y') }}
                        </span>
                    </div>
                    <div class="permit-info-row">
                        <span class="permit-label">Time</span>
                        <span class="permit-value">
                            <i class="fa-solid fa-clock"></i>
                            {{ $schedule->start_time ? date('g:i A', strtotime($schedule->start_time)) : 'TBA' }}
                            @if($schedule->end_time) - {{ date('g:i A', strtotime($schedule->end_time)) }} @endif
                        </span>
                    </div>
                    <div class="permit-info-row">
                        <span class="permit-label">Room</span>
                        <span class="permit-value permit-room {{ $permit->room() ? '' : 'permit-pending' }}">
                            <i class="fa-solid fa-door-open"></i>
                            {{ $permit->roomDisplay() }}
                        </span>
                    </div>
                    <div class="permit-info-row">
                        <span class="permit-label">Seat Number</span>
                        <span class="permit-value permit-seat {{ $permit->seat() ? '' : 'permit-pending' }}">
                            <i class="fa-solid fa-chair"></i>
                            {{ $permit->seatDisplay() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Venue --}}
            @if($schedule->venue)
            <div class="permit-section">
                <h4>Venue</h4>
                <p class="permit-venue">
                    <i class="fa-solid fa-location-dot"></i>
                    {{ $schedule->venue }}
                </p>
            </div>
            @endif

            {{-- Instructions --}}
            @if($schedule->instructions)
            <div class="permit-section">
                <h4>Instructions</h4>
                <p class="permit-instructions">{{ $schedule->instructions }}</p>
            </div>
            @endif

            {{-- Important Notes --}}
            <div class="permit-section permit-notes">
                <h4>Important Notes</h4>
                <ul>
                    <li>Arrive at least 15 minutes before the exam starts</li>
                    <li>Bring this permit and a valid ID</li>
                    <li>Mobile phones and unauthorized materials are not allowed</li>
                    <li>Follow all instructions from the exam proctors</li>
                    <li>The exam is <strong>{{ ($schedule->end_time && $schedule->start_time) ? (new DateTime($schedule->end_time))->diff(new DateTime($schedule->start_time))->format('%H hours %I minutes') : 'approximately 1-2 hours' }}</strong></li>
                </ul>
            </div>
        </div>

        <div class="permit-footer">
            <p>Issued: {{ now()->format('F d, Y') }}</p>
        </div>
    </div>

    {{-- ── Tips ──────────────────────────────────────────── --}}
    <div class="student-section-card exam-tips">
        <h3><i class="fa-solid fa-lightbulb"></i> What to Bring</h3>
        <ul>
            <li>This exam permit</li>
            <li>Valid government-issued ID (e.g., passport, national ID)</li>
            <li>Extra pencils (2-3) and erasers</li>
            <li>Blank scratch paper (if allowed)</li>
        </ul>
    </div>

</div>
</div>

<style>
.exam-onsite-permit {
    background: #f8f9fa;
    border: 2px solid var(--primary);
    border-radius: var(--radius-md);
}

.permit-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 1.5rem;
    border-bottom: 2px solid var(--primary);
    background: linear-gradient(135deg, var(--primary-light), #f0f4ff);
    border-radius: var(--radius-md) var(--radius-md) 0 0;
}

.permit-header h3 {
    margin: 0 0 0.25rem;
    color: var(--primary);
    font-size: 1.25rem;
}

.permit-id {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin: 0;
}

.permit-grade-badge {
    background: var(--primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-sm);
    font-weight: 700;
    font-size: 0.9rem;
}

.permit-content {
    padding: 1.5rem;
}

.permit-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--line);
}

.permit-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.permit-section h4 {
    font-size: 0.95rem;
    color: var(--primary);
    margin: 0 0 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.permit-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.permit-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: white;
    border-radius: var(--radius-xs);
}

.permit-label {
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.permit-value {
    font-size: 0.95rem;
    color: var(--text-dark);
    font-weight: 600;
}

.permit-room,
.permit-seat {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(99, 102, 241, 0.1));
    color: var(--primary);
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius-xs);
}
.permit-pending {
    background: var(--warning-bg) !important;
    color: #92400e !important;
    border: 1px dashed rgba(217, 119, 6, 0.45);
}

.permit-venue {
    background: white;
    padding: 1rem;
    border-radius: var(--radius-sm);
    margin: 0;
    border-left: 3px solid var(--primary);
}

.permit-instructions {
    background: white;
    padding: 1rem;
    border-radius: var(--radius-sm);
    margin: 0;
    line-height: 1.6;
    color: var(--text-dark);
}

.permit-notes ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.permit-notes li {
    padding: 0.5rem 0;
    padding-left: 1.75rem;
    position: relative;
    color: var(--text-dark);
}

.permit-notes li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--success);
    font-weight: 700;
}

.permit-footer {
    padding: 1rem 1.5rem;
    background: var(--panel);
    border-top: 1px solid var(--line);
    font-size: 0.8rem;
    color: var(--text-muted);
    text-align: right;
    border-radius: 0 0 var(--radius-md) var(--radius-md);
}

.exam-tips {
    background: #f0f7ff;
    border-left: 4px solid var(--primary);
}

.exam-tips h3 {
    color: var(--primary);
    margin: 0 0 1rem;
}

.exam-tips ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.exam-tips li {
    padding: 0.5rem 0;
    padding-left: 1.75rem;
    position: relative;
    color: var(--text-dark);
}

.exam-tips li:before {
    content: "→";
    position: absolute;
    left: 0;
    color: var(--primary);
    font-weight: 700;
}

@media (max-width: 640px) {
    .permit-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .permit-info-grid {
        grid-template-columns: 1fr;
    }
    
    .permit-info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

@media print {
    .student-main-box > .student-section-card:not(.exam-onsite-permit),
    .submit-btn,
    .btn {
        display: none !important;
    }
    
    .exam-onsite-permit {
        box-shadow: none;
        border: 1px solid #ccc;
    }
}
</style>

@endsection
