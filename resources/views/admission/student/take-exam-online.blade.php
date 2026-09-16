@extends('layouts.app')

@section('title', 'Take Exam | EntryEase')
@section('body-class', 'role-student')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/student.css') }}?v={{ filemtime(public_path('css/student.css')) }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}?v={{ filemtime(public_path('css/exam.css')) }}">
@endsection

@section('content')
@php
    $totalPoints = $questions->sum('points');
@endphp

<div class="student-page-layer">
<div class="student-main-box">

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="page-header student-section-card">
        <div class="page-header-text">
            <h2><i class="fa-solid fa-pen-to-square"></i><span>Grade 7 Entrance Exam</span></h2>
            <p>{{ $application->examSchedule->title }}</p>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
            <span class="exam-status-badge exam-status-ongoing">In Progress</span>
            <span class="exam-counter-badge">
                <i class="fa-solid fa-list-check"></i>
                {{ $questions->count() }} question{{ $questions->count() !== 1 ? 's' : '' }}
                &middot; {{ $totalPoints }} pt{{ $totalPoints !== 1 ? 's' : '' }}
            </span>
        </div>
    </div>

    {{-- ── Instructions banner ─────────────────────────────── --}}
    <div class="student-section-card exam-student-notice">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>Read before you start.</strong>
            <p>Answer every question — unanswered questions count as wrong. You can only submit <strong>once</strong>. Your score is recorded immediately.</p>
        </div>
    </div>

    {{-- ── Progress bar ─────────────────────────────────────── --}}
    <div class="exam-progress-wrap student-section-card" style="padding:.85rem 1.5rem">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
            <span style="font-size:.8rem;font-weight:700;color:var(--text-muted)">Progress</span>
            <span id="progressLabel" style="font-size:.8rem;font-weight:700;color:var(--primary)">0 / {{ $questions->count() }} answered</span>
        </div>
        <div class="exam-progress-track">
            <div class="exam-progress-fill" id="progressFill" style="width:0%"></div>
        </div>
    </div>

    {{-- ── Questions ────────────────────────────────────────── --}}
    <form action="{{ route('student.exam.submit') }}"
          method="POST"
          id="examForm"
          class="student-exam-form">
        @csrf

        @foreach($questions as $question)
        <div class="student-section-card exam-take-question" id="q{{ $question->id }}">
            <div class="exam-take-question-head">
                <span class="exam-q-num">
                    <span class="exam-q-dot" id="dot{{ $question->id }}"></span>
                    Question {{ $loop->iteration }}
                </span>
                <span class="exam-q-pts">{{ $question->points }} {{ Str::plural('pt', $question->points) }}</span>
            </div>
            <p class="exam-take-question-text">{{ $question->question_text }}</p>
            <div class="exam-answer-list">
                @foreach($question->choices as $key => $choice)
                    <label class="exam-answer-option" for="q{{ $question->id }}_{{ $key }}">
                        <input type="radio"
                               id="q{{ $question->id }}_{{ $key }}"
                               name="answers[{{ $question->id }}]"
                               value="{{ $key }}"
                               data-qid="{{ $question->id }}">
                        <span class="exam-answer-key">{{ $key }}</span>
                        <span class="exam-answer-text">{{ $choice }}</span>
                    </label>
                @endforeach
            </div>
            @error('answers.' . $question->id)
                <span class="exam-field-error">{{ $message }}</span>
            @enderror
        </div>
        @endforeach

        {{-- ── Submit panel ─────────────────────────────────── --}}
        <div class="student-section-card exam-submit-panel" id="submitPanel">
            <div class="exam-submit-info">
                <p id="submitWarning" class="exam-submit-warning" style="display:none">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span id="unansweredMsg"></span>
                </p>
                <p style="font-size:.82rem;color:var(--text-muted);margin:.25rem 0 0">
                    Once submitted, your answers cannot be changed.
                </p>
            </div>
            <button type="button" class="submit-btn" id="submitBtn" onclick="confirmSubmit()">
                <i class="fa-solid fa-paper-plane"></i><span>Submit Exam</span>
            </button>
        </div>

    </form>

    {{-- ── Confirm modal ────────────────────────────────────── --}}
    <div class="exam-confirm-overlay" id="confirmOverlay" style="display:none" role="dialog" aria-modal="true">
        <div class="exam-confirm-box">
            <div class="exam-confirm-icon">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
            <h3>Submit your exam?</h3>
            <p id="confirmMsg">You have answered all {{ $questions->count() }} questions.</p>
            <p style="font-size:.82rem;color:var(--text-muted);margin-top:.35rem">This cannot be undone.</p>
            <div class="exam-confirm-actions">
                <button type="button" class="btn btn-secondary" onclick="closeConfirm()">
                    <i class="fa-solid fa-arrow-left"></i> Go Back
                </button>
                <button type="button" class="submit-btn" id="confirmSubmitBtn" onclick="doSubmit()">
                    <i class="fa-solid fa-check"></i> Yes, Submit
                </button>
            </div>
        </div>
    </div>

</div>
</div>

<script>
(function () {
    const total     = {{ $questions->count() }};
    const answered  = {};
    const fillEl    = document.getElementById('progressFill');
    const labelEl   = document.getElementById('progressLabel');
    const warnEl    = document.getElementById('submitWarning');
    const warnMsg   = document.getElementById('unansweredMsg');

    function updateProgress() {
        const n = Object.keys(answered).length;
        const pct = total > 0 ? Math.round((n / total) * 100) : 0;
        if (fillEl)  fillEl.style.width = pct + '%';
        if (labelEl) labelEl.textContent = n + ' / ' + total + ' answered';
    }

    // Wire radio buttons
    document.querySelectorAll('input[type="radio"][data-qid]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const qid = this.dataset.qid;
            answered[qid] = this.value;
            updateProgress();

            // Mark dot green
            const dot = document.getElementById('dot' + qid);
            if (dot) dot.classList.add('answered');
        });
    });

    // Confirm submit
    window.confirmSubmit = function () {
        const n = Object.keys(answered).length;
        const missing = total - n;
        const confirmMsg = document.getElementById('confirmMsg');

        if (missing > 0) {
            if (warnEl)  warnEl.style.display = 'flex';
            if (warnMsg) warnMsg.textContent = missing + ' question' + (missing !== 1 ? 's' : '') + ' unanswered — they will count as wrong.';
            if (confirmMsg) confirmMsg.textContent = 'You have answered ' + n + ' of ' + total + ' questions. ' + missing + ' unanswered question' + (missing !== 1 ? 's' : '') + ' will be marked wrong.';
        } else {
            if (warnEl) warnEl.style.display = 'none';
            if (confirmMsg) confirmMsg.textContent = 'You have answered all ' + total + ' questions.';
        }

        document.getElementById('confirmOverlay').style.display = 'flex';
    };

    window.closeConfirm = function () {
        document.getElementById('confirmOverlay').style.display = 'none';
    };

    window.navigateBack = function () {
        // Navigate back to exam schedule
        window.location.href = '{{ \App\Support\BackNavigation::resolve("student.exam.schedule") }}';
    };

    window.doSubmit = function () {
        const btn = document.getElementById('confirmSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting…';
        }
        document.getElementById('examForm').submit();
    };

    // Close on backdrop click
    document.getElementById('confirmOverlay').addEventListener('click', function (e) {
        if (e.target === this) closeConfirm();
    });

    // Keyboard escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeConfirm();
    });

    // Warn before leaving
    window.addEventListener('beforeunload', function (e) {
        if (Object.keys(answered).length > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Remove beforeunload on actual submit
    document.getElementById('examForm').addEventListener('submit', function () {
        window.onbeforeunload = null;
    });

    updateProgress();
})();
</script>
@endsection
