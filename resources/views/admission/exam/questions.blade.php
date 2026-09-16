@extends('layouts.app')

@section('title', 'Exam Questions | EntryEase')
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

{{-- Header --}}
<div class="es-page-header">
    <div class="es-page-header-left">
        <h1 class="es-page-title">Question Bank</h1>
        <p class="es-page-sub">
            {{ $schedule->title }}
            &mdash; {{ $schedule->exam_date->format('F d, Y') }}
            &mdash; <strong>{{ $questions->where('is_active', true)->count() }}</strong> active question{{ $questions->where('is_active', true)->count() !== 1 ? 's' : '' }}
        </p>
    </div>
    <div class="ee-page-actions">
        <a href="{{ route('exam.schedules.applicants', $schedule) }}" class="registrar-btn registrar-btn-secondary">
            <i class="fa-solid fa-users"></i> Applicants
        </a>
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

<div class="es-q-layout">

    {{-- ── Add question panel ───────────────────────────────── --}}
    <div class="es-q-add-panel">
        <div class="registrar-card" style="position:sticky;top:88px">
            <div class="registrar-card-header">
                <h3><i class="fa-solid fa-plus"></i> Add Question</h3>
            </div>
            <div class="registrar-card-body">
                <form action="{{ route('exam.schedules.questions.store', $schedule) }}" method="POST">
                    @csrf

                    <div class="es-field es-field-full" style="margin-bottom:1rem">
                        <label for="question_text">Question Text <span class="es-req">*</span></label>
                        <textarea id="question_text" name="question_text"
                                  rows="3"
                                  placeholder="Type the question here…"
                                  class="es-input es-textarea {{ $errors->has('question_text') ? 'es-input-err' : '' }}"
                                  required>{{ old('question_text') }}</textarea>
                        @error('question_text')<span class="es-err">{{ $message }}</span>@enderror
                    </div>

                    <div class="es-choices-grid">
                        @foreach(['a'=>'A','b'=>'B','c'=>'C','d'=>'D'] as $k => $label)
                        <div class="es-choice-input-wrap">
                            <span class="es-choice-letter">{{ $label }}</span>
                            <input type="text"
                                   id="choice_{{ $k }}"
                                   name="choice_{{ $k }}"
                                   value="{{ old('choice_'.$k) }}"
                                   placeholder="Choice {{ $label }}"
                                   class="es-input {{ $errors->has('choice_'.$k) ? 'es-input-err' : '' }}"
                                   required>
                        </div>
                        @endforeach
                    </div>

                    <div class="es-form-row" style="margin-top:1rem">
                        <div class="es-field">
                            <label for="correct_answer">Correct Answer <span class="es-req">*</span></label>
                            <div class="es-select-wrap">
                                <select id="correct_answer" name="correct_answer" class="es-input" required>
                                    @foreach(['A','B','C','D'] as $c)
                                        <option value="{{ $c }}" {{ old('correct_answer') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                <i class="fa-solid fa-chevron-down es-select-arrow"></i>
                            </div>
                            @error('correct_answer')<span class="es-err">{{ $message }}</span>@enderror
                        </div>
                        <div class="es-field">
                            <label for="points">Points <span class="es-req">*</span></label>
                            <input type="number" id="points" name="points"
                                   min="1" max="100"
                                   value="{{ old('points', 1) }}"
                                   class="es-input {{ $errors->has('points') ? 'es-input-err' : '' }}"
                                   required>
                            @error('points')<span class="es-err">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <button type="submit" class="registrar-btn registrar-btn-primary" style="width:100%;justify-content:center;margin-top:1.1rem">
                        <i class="fa-solid fa-plus"></i> Add Question
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Question list ────────────────────────────────────── --}}
    <div class="es-q-list-panel">

        @if($questions->isEmpty())
            <div class="es-empty" style="padding:3rem 1rem">
                <div class="es-empty-icon"><i class="fa-solid fa-circle-question"></i></div>
                <h3>No questions yet</h3>
                <p>Use the form on the left to add your first question.</p>
            </div>
        @else
            {{-- Summary bar --}}
            <div class="es-q-summary">
                <span>
                    <strong>{{ $questions->where('is_active', true)->count() }}</strong> active
                    &middot;
                    <strong>{{ $questions->sum('points') }}</strong> total points
                </span>
                @if($questions->where('is_active', false)->count() > 0)
                    <span class="es-q-inactive-note">
                        {{ $questions->where('is_active', false)->count() }} inactive (have existing answers)
                    </span>
                @endif
            </div>

            <div class="es-q-list">
                @foreach($questions as $q)
                <div class="es-q-card {{ !$q->is_active ? 'es-q-inactive' : '' }}">
                    <div class="es-q-card-head">
                        <div class="es-q-num">
                            <span class="es-q-num-badge">{{ $loop->iteration }}</span>
                            <span class="es-q-pts-badge">{{ $q->points }} pt{{ $q->points !== 1 ? 's' : '' }}</span>
                            @if(!$q->is_active)
                                <span class="es-q-inactive-badge">Inactive</span>
                            @endif
                        </div>
                        <form action="{{ route('exam.schedules.questions.delete', [$schedule, $q]) }}" method="POST"
                              onsubmit="return confirm('Delete this question?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="es-icon-btn es-icon-danger" title="Delete question">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>

                    <p class="es-q-text">{{ $q->question_text }}</p>

                    <div class="es-q-choices">
                        @foreach($q->choices as $key => $choice)
                        <div class="es-q-choice {{ $key === $q->correct_answer ? 'es-q-choice-correct' : '' }}">
                            <span class="es-q-choice-key">{{ $key }}</span>
                            <span class="es-q-choice-text">{{ $choice }}</span>
                            @if($key === $q->correct_answer)
                                <i class="fa-solid fa-check es-q-correct-icon"></i>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        @endif

    </div>

</div>
@endsection
