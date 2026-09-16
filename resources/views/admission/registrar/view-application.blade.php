@extends('layouts.app')

@section('title', 'Application Review | EntryEase')
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
    <div class="registrar-page-header">
        <div>
            <h2>Application Details</h2>
            <p>Reviewing application #{{ $applicant->id }}</p>
        </div>
        <div class="ee-page-actions">
            <a href="{{ route('registrar.student.documents', $applicant) }}" class="registrar-btn registrar-btn-primary">
                <i class="fa-solid fa-file-pdf"></i> View Documents
            </a>
            @if($applicant->examSchedule)
                <a href="{{ route('exam.schedules.applicants', $applicant->examSchedule) }}" class="registrar-btn registrar-btn-secondary">
                    <i class="fa-solid fa-calendar-days"></i> Exam Schedule
                </a>
            @endif
            <x-back-button variant="secondary" />
        </div>
    </div>

    {{-- Student info --}}
    <div class="registrar-card">
        <div class="registrar-card-header"><h3><i class="fa-solid fa-user-graduate"></i> Student Information</h3></div>
        <div class="registrar-card-body">
            @php
                $additionalInfo = json_decode($applicant->additional_info, true) ?? [];
            @endphp
            <div class="registrar-info-grid">
                <div class="registrar-info-item"><label>Full Name</label><span>{{ $applicant->student?->name ?? '—' }}</span></div>
                <div class="registrar-info-item"><label>Email</label><span>{{ $applicant->student?->email ?? '—' }}</span></div>
                <div class="registrar-info-item"><label>Phone</label><span>{{ $additionalInfo['phone'] ?? '—' }}</span></div>
                <div class="registrar-info-item"><label>Previous School</label><span>{{ $additionalInfo['previous_school'] ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    {{-- Application details --}}
    <div class="registrar-card">
        <div class="registrar-card-header"><h3><i class="fa-solid fa-file-lines"></i> Application Details</h3></div>
        <div class="registrar-card-body">
            <div class="registrar-info-grid">
                <div class="registrar-info-item"><label>Grade Level</label><span>{{ $applicant->grade_level ?? '—' }}</span></div>
                <div class="registrar-info-item"><label>Admission Status</label><span>{{ str_replace('_', ' ', $applicant->admission_status ?? 'pending') }}</span></div>
                <div class="registrar-info-item"><label>Date Submitted</label><span>{{ $applicant->created_at->format('F d, Y') }}</span></div>
                <div class="registrar-info-item">
                    <label>Current Status</label>
                    <span class="status-badge
                        @switch($applicant->status)
                            @case('Pending') pending @break
                            @case('Under Review') review @break
                            @case('Approved') approved @break
                            @case('Rejected') rejected @break
                        @endswitch">{{ $applicant->status }}</span>
                </div>
            </div>
            @if($applicant->admin_notes)
                <hr class="registrar-divider">
                <div class="registrar-info-item">
                    <label>Officer Notes</label>
                    <div class="registrar-notes-box">{{ $applicant->admin_notes }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Exam schedule --}}
    <div class="registrar-card">
        <div class="registrar-card-header">
            <h3><i class="fa-solid fa-calendar-days"></i> Exam Schedule</h3>
        </div>
        <div class="registrar-card-body">
            @if($applicant->examSchedule)
                @php $schedule = $applicant->examSchedule; @endphp
                <div class="registrar-info-grid">
                    <div class="registrar-info-item">
                        <label>Schedule</label>
                        <span>{{ $schedule->title }}</span>
                    </div>
                    <div class="registrar-info-item">
                        <label>Date</label>
                        <span>{{ $schedule->exam_date->format('F d, Y') }}</span>
                    </div>
                    <div class="registrar-info-item">
                        <label>Time</label>
                        <span>{{ $schedule->time_range }}</span>
                    </div>
                    <div class="registrar-info-item">
                        <label>Venue</label>
                        <span>{{ $schedule->venue ?? '—' }}</span>
                    </div>
                    @if($schedule->batch)
                        <div class="registrar-info-item">
                            <label>Batch</label>
                            <span>{{ $schedule->batch }}</span>
                        </div>
                    @endif
                    <div class="registrar-info-item">
                        <label>Status</label>
                        <span class="exam-status-badge exam-status-{{ $schedule->status }}">{{ ucfirst($schedule->status) }}</span>
                    </div>
                </div>
            @else
                <p style="color:var(--text-muted);margin:0"><i class="fa-solid fa-calendar-xmark"></i> No exam schedule assigned yet.
                    <a href="{{ route('exam.schedules') }}" style="color:var(--primary)">Assign one →</a>
                </p>
            @endif
        </div>
    </div>

    {{-- Exam score --}}
    <div class="registrar-card">
        <div class="registrar-card-header">
            <h3><i class="fa-solid fa-chart-line"></i> Exam Score</h3>
        </div>
        <div class="registrar-card-body">
            @if($applicant->examScore)
                @php $score = $applicant->examScore; @endphp
                <div class="registrar-info-grid">
                    <div class="registrar-info-item">
                        <label>Score</label>
                        <span style="font-size:1.2rem;font-weight:700;color:var(--primary)">
                            {{ $score->score }} / {{ $score->total_items }}
                        </span>
                    </div>
                    <div class="registrar-info-item">
                        <label>Percentage</label>
                        <span style="font-size:1.2rem;font-weight:700;color:var(--primary)">{{ $score->percentage }}%</span>
                    </div>
                    <div class="registrar-info-item">
                        <label>Result</label>
                        <span class="exam-result-badge {{ $score->passed ? 'passed' : 'failed' }}">
                            <i class="fa-solid {{ $score->passed ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                            {{ $score->passed ? 'Passed' : 'Failed' }}
                        </span>
                    </div>
                    <div class="registrar-info-item">
                        <label>Recorded By</label>
                        <span>{{ $score->recorded_by ?? '—' }}</span>
                    </div>
                    @if($score->remarks)
                        <div class="registrar-info-item" style="grid-column:1/-1">
                            <label>Remarks</label>
                            <span>{{ $score->remarks }}</span>
                        </div>
                    @endif
                </div>
            @elseif($applicant->examSchedule)
                <p style="color:#999;margin:0">
                    <i class="fa-solid fa-hourglass-half"></i> Score not yet recorded.
                    <a href="{{ route('exam.schedules.applicants', $applicant->examSchedule) }}" style="color:var(--primary)">Enter score →</a>
                </p>
            @else
                <p style="color:#999;margin:0"><i class="fa-solid fa-minus"></i> Assign an exam schedule first before recording a score.</p>
            @endif
        </div>
    </div>

    {{-- Update status --}}
    <div class="registrar-card">
        <div class="registrar-card-header">
            <h3><i class="fa-solid fa-edit"></i> Update Application Status</h3>
            @if(in_array($applicant->status, ['Approved', 'Rejected']))
                <span style="color:#999;font-size:0.9rem;font-weight:normal"><i class="fa-solid fa-lock"></i> Locked</span>
            @endif
        </div>
        <div class="registrar-card-body">
            @php
                $nextStatuses = $applicant->nextStatuses();
                $isLocked = in_array($applicant->status, [\App\Models\Applicant::STATUS_APPROVED, \App\Models\Applicant::STATUS_REJECTED], true);
            @endphp
            @if($applicant->status === \App\Models\Applicant::STATUS_UNDER_REVIEW && ! $applicant->hasPassingExamScore())
                <div style="margin-bottom:1rem;padding:.85rem 1rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:.9rem;">
                    <i class="fa-solid fa-circle-info"></i>
                    <strong>Approval requires a passing exam score.</strong>
                    Assign an exam, record or complete the exam, then approve only if the student passes (75% or higher).
                </div>
            @endif
            @if(! empty($applicant->exam_stage_label))
                <p style="margin:0 0 1rem;color:var(--text-muted);font-size:.9rem;">
                    <i class="fa-solid fa-clipboard-check"></i> Exam stage: <strong>{{ $applicant->exam_stage_label }}</strong>
                </p>
            @endif
            <form action="{{ route('registrar.application.update', $applicant) }}" method="POST" class="registrar-status-form">
                @csrf
                @method('PUT')
                <div class="registrar-form-group select-with-arrow">
                    <label for="status">Status</label>
                    @if($isLocked || empty($nextStatuses))
                        <input type="text" class="form-control" value="{{ $applicant->status }}" disabled>
                    @else
                        <select name="status" id="status" required>
                            @foreach($nextStatuses as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if($isLocked)
                        <span style="color:#999;font-size:.85rem;margin-top:0.5rem;display:block"><i class="fa-solid fa-lock"></i> This status is locked and cannot be changed.</span>
                    @endif
                    @error('status')<span style="color:#C62828;font-size:.85rem">{{ $message }}</span>@enderror
                </div>
                <div class="registrar-form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes"
                              placeholder="Add remarks or notes about this application..."
                              {{ $isLocked ? 'disabled' : '' }}>{{ old('notes', $applicant->admin_notes) }}</textarea>
                    @error('notes')<span style="color:#C62828;font-size:.85rem">{{ $message }}</span>@enderror
                </div>
                <div class="registrar-form-actions">
                    @if(! $isLocked && ! empty($nextStatuses))
                    <button type="submit" class="registrar-btn registrar-btn-primary">
                        <i class="fa-solid fa-save"></i> Update Status
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
