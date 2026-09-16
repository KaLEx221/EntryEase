@extends('layouts.app')

@section('title', 'Schedule Applicants | EntryEase')
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

{{-- ── Page header ──────────────────────────────────────────── --}}
<div class="es-page-header">
    <div class="es-page-header-left">
        <h1 class="es-page-title">{{ $schedule->title }}</h1>
        <p class="es-page-sub">
            <i class="fa-solid fa-calendar-day"></i> {{ $schedule->exam_date->format('F d, Y') }}
            &nbsp;&middot;&nbsp;
            <i class="fa-solid fa-clock"></i> {{ $schedule->time_range }}
            @if($schedule->venue)
                &nbsp;&middot;&nbsp;
                <i class="fa-solid fa-location-dot"></i> {{ $schedule->venue }}
            @endif
            @if($schedule->exam_type)
                &nbsp;&middot;&nbsp;
                <span class="exam-type-badge exam-type-{{ $schedule->exam_type }}">
                    <i class="fa-solid {{ $schedule->exam_type === 'online' ? 'fa-globe' : 'fa-building' }}"></i>
                    {{ ucfirst($schedule->exam_type) }}
                </span>
            @endif
        </p>
    </div>
    <div class="ee-page-actions">
        <a href="{{ route('exam.schedules.questions', $schedule) }}" class="registrar-btn registrar-btn-secondary">
            <i class="fa-solid fa-list-check"></i> Questions
        </a>
        <a href="{{ route('exam.schedules.analytics', $schedule) }}" class="registrar-btn registrar-btn-secondary">
            <i class="fa-solid fa-chart-bar"></i> Analytics
        </a>
        <x-back-button variant="secondary" />
    </div>
</div>

{{-- ── Flash ────────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="es-flash es-flash-ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="es-flash es-flash-err"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
@endif

{{-- ── Slot progress ────────────────────────────────────────── --}}
@php
    $filled = $applicants->count();
    $slots  = $schedule->slots;
    $pct    = $slots > 0 ? min(100, round(($filled / $slots) * 100)) : 0;
    // Default total items for scoring: number of active questions (fallback 1)
    $defaultTotal = $schedule->activeQuestions()->where('is_active', true)->count() ?: 1;
@endphp
<div class="sa-slot-card">
    <div class="sa-slot-numbers">
        <div class="sa-slot-num">
            <span class="sa-slot-big">{{ $filled }}</span>
            <span class="sa-slot-label">Assigned</span>
        </div>
        <div class="sa-slot-divider">/</div>
        <div class="sa-slot-num">
            <span class="sa-slot-big">{{ $slots }}</span>
            <span class="sa-slot-label">Total Slots</span>
        </div>
        <div class="sa-slot-num">
            <span class="sa-slot-big {{ $slots - $filled > 0 ? 'sa-available' : 'sa-full' }}">{{ max(0, $slots - $filled) }}</span>
            <span class="sa-slot-label">Available</span>
        </div>
    </div>
    <div class="sa-slot-bar-wrap">
        <div class="sa-slot-bar">
            <div class="sa-slot-fill" style="width:{{ $pct }}%"></div>
        </div>
        <span class="sa-slot-pct">{{ $pct }}% filled</span>
    </div>
</div>

{{-- ── Assign applicant ─────────────────────────────────────── --}}
@if(!$isAdmin && $unassigned->isNotEmpty())
<div class="registrar-card">
    <div class="registrar-card-header">
        <h3><i class="fa-solid fa-user-plus"></i> Assign Applicant</h3>
    </div>
    <div class="registrar-card-body">
        <form action="{{ route('exam.schedules.applicants.assign', $schedule) }}" method="POST"
              class="sa-assign-form">
            @csrf
            <div class="registrar-form-group" style="flex:1;min-width:220px;margin:0">
                <label for="applicant_id">Select Applicant</label>
                <div class="es-select-wrap">
                    <select name="applicant_id" id="applicant_id" class="es-input" required>
                        <option value="" disabled selected>— choose applicant —</option>
                        @foreach($unassigned as $a)
                            <option value="{{ $a->id }}">
                                {{ $a->student?->name ?? 'Unknown' }}
                                ({{ $a->student?->email ?? '—' }})
                                &middot; {{ $a->status }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down es-select-arrow"></i>
                </div>
            </div>
            @if($schedule->exam_type === 'onsite')
            <div class="registrar-form-group" style="min-width:140px;margin:0">
                <label for="exam_room">Exam room</label>
                <input type="text" id="exam_room" name="exam_room" class="es-input"
                       value="{{ old('exam_room', $schedule->venue) }}"
                       placeholder="e.g. Room 201">
            </div>
            <div class="registrar-form-group" style="min-width:120px;margin:0">
                <label for="exam_seat_number">Seat <span style="font-weight:400;color:var(--text-muted)">(optional)</span></label>
                <input type="text" id="exam_seat_number" name="exam_seat_number" class="es-input"
                       value="{{ old('exam_seat_number') }}"
                       placeholder="Auto">
            </div>
            @endif
            <button type="submit" class="registrar-btn registrar-btn-primary" style="align-self:flex-end">
                <i class="fa-solid fa-user-plus"></i> Assign
            </button>
        </form>
    </div>
</div>
@endif

{{-- ── Applicants table ─────────────────────────────────────── --}}
<div class="registrar-card">
    <div class="registrar-card-header" style="justify-content:space-between">
        <h3><i class="fa-solid fa-clipboard-list"></i> Assigned Applicants</h3>
        <span style="color:rgba(255,255,255,.75);font-size:.82rem;font-weight:600">
            {{ $applicants->count() }} applicant{{ $applicants->count() !== 1 ? 's' : '' }}
        </span>
    </div>
    <div class="registrar-card-body" style="padding:0">
        @if($applicants->isEmpty())
            <div class="es-empty" style="border:none;box-shadow:none;padding:3rem 1rem">
                <div class="es-empty-icon"><i class="fa-solid fa-user-slash"></i></div>
                <h3>No applicants assigned</h3>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            @if($schedule->exam_type === 'onsite')
                                <th>Room</th>
                                <th>Seat</th>
                            @endif
                            <th>App. Status</th>
                            <th>Score</th>
                            <th>Result</th>
                            @if(!$isAdmin)<th></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applicants as $applicant)
                        @php $score = $applicant->examScore; @endphp
                        <tr>
                            <td>
                                <div class="td-student">
                                    <div class="td-avatar">{{ strtoupper(substr($applicant->student?->name ?? '?', 0, 1)) }}</div>
                                    <div>
                                        <strong>{{ $applicant->student?->name ?? '—' }}</strong>
                                        <div class="td-email">{{ $applicant->student?->email ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            @if($schedule->exam_type === 'onsite')
                            <td colspan="2" style="min-width:220px">
                                @if(!$isAdmin)
                                <form action="{{ route('exam.schedules.applicants.seating', [$schedule, $applicant]) }}"
                                      method="POST" class="sa-seating-form">
                                    @csrf
                                    @method('PATCH')
                                    <div class="sa-seating-fields">
                                        <input type="text" name="exam_room" class="es-input"
                                               value="{{ old('exam_room', $applicant->exam_room ?? $schedule->venue) }}"
                                               placeholder="Room" required aria-label="Exam room">
                                        <input type="text" name="exam_seat_number" class="es-input"
                                               value="{{ old('exam_seat_number', $applicant->exam_seat_number) }}"
                                               placeholder="Seat" required aria-label="Seat number">
                                        <button type="submit" class="es-icon-btn" title="Save seating">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </div>
                                </form>
                                @else
                                <span>{{ $applicant->exam_room ?? '—' }} / {{ $applicant->exam_seat_number ?? '—' }}</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                <span class="status-badge @switch($applicant->status)
                                    @case('Pending') pending @break
                                    @case('Under Review') review @break
                                    @case('Approved') approved @break
                                    @case('Rejected') rejected @break
                                @endswitch">{{ $applicant->status }}</span>
                            </td>
                            <td>
                                @if($score)
                                    <span class="sa-score">
                                        {{ $score->score }}<span class="sa-score-sep">/</span>{{ $score->total_items }}
                                        <span class="sa-score-pct">({{ $score->percentage }}%)</span>
                                    </span>
                                @else
                                    <span class="td-none">—</span>
                                @endif
                            </td>
                            <td>
                                @if($score)
                                    <span class="exam-result-badge {{ $score->passed ? 'passed' : 'failed' }}">
                                        <i class="fa-solid {{ $score->passed ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                        {{ $score->passed ? 'Passed' : 'Failed' }}
                                    </span>
                                @else
                                    <span class="exam-result-badge pending">Not recorded</span>
                                @endif
                            </td>
                            @if(!$isAdmin)
                            <td>
                                <div style="display:flex;gap:.4rem;align-items:center">
                                    @if($schedule->exam_type === 'onsite')
                                        <button type="button"
                                                class="registrar-btn registrar-btn-primary sa-score-btn"
                                                data-applicant-id="{{ $applicant->id }}"
                                                data-applicant-name="{{ $applicant->student?->name }}"
                                                data-score="{{ $score?->score ?? '' }}"
                                                data-total="{{ $score?->total_items ?? $defaultTotal }}"
                                                data-remarks="{{ $score?->remarks ?? '' }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            {{ $score ? 'Edit Score' : 'Enter Score' }}
                                        </button>
                                    @else
                                        <span style="font-size:.78rem;color:var(--text-muted)">
                                            @if($score)
                                                <i class="fa-solid fa-circle-check" style="color:var(--success)"></i> Auto-graded
                                            @else
                                                <i class="fa-solid fa-hourglass-half"></i> Pending
                                            @endif
                                        </span>
                                    @endif
                                    <form action="{{ route('exam.schedules.applicants.unassign', [$schedule, $applicant]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Remove {{ addslashes($applicant->student?->name ?? '') }} from this schedule?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="es-icon-btn es-icon-danger" title="Unassign">
                                            <i class="fa-solid fa-user-minus"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ── Score modal ──────────────────────────────────────────── --}}
@if(!$isAdmin && $schedule->exam_type === 'onsite')
<div class="exam-modal-overlay" id="scoreModal" style="display:none"
     role="dialog" aria-modal="true" aria-labelledby="scoreModalTitle">
    <div class="exam-modal">
        <div class="exam-modal-header">
            <h3 id="scoreModalTitle"><i class="fa-solid fa-pen-to-square"></i> Record Exam Score</h3>
            <button type="button" class="exam-modal-close" id="closeScoreModal" aria-label="Close">&times;</button>
        </div>
        <div class="exam-modal-body">
            <p id="scoreModalApplicantName"
               style="margin:0 0 1.1rem;font-weight:700;color:var(--primary);font-size:.95rem"></p>
            <form id="scoreForm" method="POST" action="">
                @csrf
                <div class="es-form-row" style="margin-bottom:1rem">
                    <div class="es-field">
                        <label for="modalScore">Score <span class="es-req">*</span></label>
                        <input type="number" id="modalScore" name="score"
                               min="0" step="0.01" required
                               placeholder="e.g. 87"
                               class="es-input">
                    </div>
                    <div class="es-field">
                        <label for="modalTotal">Total Items <span class="es-req">*</span></label>
                           <input type="number" id="modalTotal" name="total_items"
                               min="1" step="0.01" value="{{ $defaultTotal }}" required
                               class="es-input" readonly>
                    </div>
                </div>
                <div class="es-field" style="margin-bottom:1.1rem">
                    <label for="modalRemarks">Remarks</label>
                    <textarea id="modalRemarks" name="remarks" rows="2"
                              placeholder="Optional notes…"
                              class="es-input es-textarea"></textarea>
                </div>
                <div style="display:flex;gap:.65rem">
                    <button type="submit" class="registrar-btn registrar-btn-primary" style="flex:1;justify-content:center">
                        <i class="fa-solid fa-save"></i> Save Score
                    </button>
                    <button type="button" id="cancelScoreModal"
                            class="registrar-btn registrar-btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const scheduleId   = {{ $schedule->id }};
    const defaultTotal = {{ $defaultTotal }};
    const modal        = document.getElementById('scoreModal');
    const form         = document.getElementById('scoreForm');
    const nameEl       = document.getElementById('scoreModalApplicantName');
    const scoreInput   = document.getElementById('modalScore');
    const totalInput   = document.getElementById('modalTotal');
    const remarksInput = document.getElementById('modalRemarks');

    document.querySelectorAll('.sa-score-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            nameEl.textContent   = btn.dataset.applicantName;
            scoreInput.value     = btn.dataset.score;
            totalInput.value     = btn.dataset.total || defaultTotal;
            totalInput.readOnly  = true;
            remarksInput.value   = btn.dataset.remarks;
            scoreInput.max       = totalInput.value;
            form.action = '/exam/schedules/' + scheduleId + '/applicants/' + btn.dataset.applicantId + '/score';
            modal.style.display  = 'flex';
            scoreInput.focus();
        });
    });

    totalInput.addEventListener('input', function () { scoreInput.max = this.value; });

    function closeModal() { modal.style.display = 'none'; }
    document.getElementById('closeScoreModal').addEventListener('click', closeModal);
    document.getElementById('cancelScoreModal').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
})();
</script>
@endif

@endsection
