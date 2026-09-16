@extends('layouts.app')

@section('title', 'New Exam Schedule | EntryEase')
@section('body-class', 'role-registrar')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/registrar.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam-schedules.css') }}">
@endsection
@section('sidebar')
    @include('admission.partials.sidebar-registrar')
@endsection

@section('content')

<div class="es-page-header">
    <div class="es-page-header-left">
        <h1 class="es-page-title">New Exam Schedule</h1>
    </div>
    <div class="ee-page-actions">
        <x-back-button variant="secondary" />
    </div>
</div>

<form action="{{ route('exam.schedules.store') }}" method="POST" class="es-form-layout">
    @csrf

    {{-- Left: main fields --}}
    <div class="es-form-main">

        <div class="registrar-card">
            <div class="registrar-card-header">
                <h3><i class="fa-solid fa-circle-info"></i> Basic Information</h3>
            </div>
            <div class="registrar-card-body es-form-fields">

                <div class="es-field es-field-full">
                    <label for="title">Schedule Title <span class="es-req">*</span></label>
                    <input type="text" id="title" name="title"
                           value="{{ old('title') }}"
                           placeholder="e.g. Grade 7 Entrance Exam – Batch A"
                           class="es-input {{ $errors->has('title') ? 'es-input-err' : '' }}"
                           required>
                    @error('title')<span class="es-err">{{ $message }}</span>@enderror
                </div>

                <div class="es-field">
                    <label for="exam_date">Exam Date <span class="es-req">*</span></label>
                    <input type="date" id="exam_date" name="exam_date"
                           value="{{ old('exam_date') }}"
                           min="{{ date('Y-m-d') }}"
                           class="es-input {{ $errors->has('exam_date') ? 'es-input-err' : '' }}"
                           required>
                    @error('exam_date')<span class="es-err">{{ $message }}</span>@enderror
                </div>

                <div class="es-field">
                    <label for="batch">Batch Label</label>
                    <input type="text" id="batch" name="batch"
                           value="{{ old('batch') }}"
                           placeholder="e.g. Batch A"
                           class="es-input">
                    @error('batch')<span class="es-err">{{ $message }}</span>@enderror
                </div>

                <div class="es-field">
                    <label for="exam_type">Exam Type <span class="es-req">*</span></label>
                    <div class="es-select-wrap">
                        <select id="exam_type" name="exam_type" class="es-input {{ $errors->has('exam_type') ? 'es-input-err' : '' }}" required>
                            <option value="">Select exam type...</option>
                            <option value="online" {{ old('exam_type') === 'online' ? 'selected' : '' }}>Online (Auto-graded)</option>
                            <option value="onsite" {{ old('exam_type') === 'onsite' ? 'selected' : '' }}>On-site (Manual Score Entry)</option>
                        </select>
                        <i class="fa-solid fa-chevron-down es-select-arrow"></i>
                    </div>
                    @error('exam_type')<span class="es-err">{{ $message }}</span>@enderror
                </div>

                <div class="es-field">
                    <label for="start_time">Start Time <span class="es-req">*</span></label>
                    <input type="time" id="start_time" name="start_time"
                           value="{{ old('start_time') }}"
                           class="es-input {{ $errors->has('start_time') ? 'es-input-err' : '' }}"
                           required>
                    @error('start_time')<span class="es-err">{{ $message }}</span>@enderror
                </div>

                <div class="es-field">
                    <label for="end_time">End Time <span class="es-req">*</span></label>
                    <input type="time" id="end_time" name="end_time"
                           value="{{ old('end_time') }}"
                           class="es-input {{ $errors->has('end_time') ? 'es-input-err' : '' }}"
                           required>
                    @error('end_time')<span class="es-err">{{ $message }}</span>@enderror
                </div>

                <div class="es-field">
                    <label for="venue">Venue</label>
                    <input type="text" id="venue" name="venue"
                           value="{{ old('venue') }}"
                           placeholder="e.g. Room 101, Main Building"
                           class="es-input">
                    @error('venue')<span class="es-err">{{ $message }}</span>@enderror
                </div>

                <div class="es-field">
                    <label for="slots">Maximum Slots <span class="es-req">*</span></label>
                    <input type="number" id="slots" name="slots"
                           value="{{ old('slots', 50) }}"
                           min="1" max="500"
                           class="es-input {{ $errors->has('slots') ? 'es-input-err' : '' }}"
                           required>
                    @error('slots')<span class="es-err">{{ $message }}</span>@enderror
                </div>

                <div class="es-field es-field-full">
                    <label for="instructions">Instructions for Examinees</label>
                    <textarea id="instructions" name="instructions"
                              rows="4"
                              placeholder="What should students bring? Any special reminders?"
                              class="es-input es-textarea">{{ old('instructions') }}</textarea>
                    @error('instructions')<span class="es-err">{{ $message }}</span>@enderror
                </div>

            </div>
        </div>

    </div>

    {{-- Right: summary + submit --}}
    <div class="es-form-side">
        <div class="registrar-card es-sticky-card">
            <div class="registrar-card-header">
                <h3><i class="fa-solid fa-paper-plane"></i> Create Schedule</h3>
            </div>
            <div class="registrar-card-body" style="display:flex;flex-direction:column;gap:.85rem">
                <p style="font-size:.85rem;color:var(--text-muted);line-height:1.6">
                    After creating the schedule you can add exam questions and assign applicants to it.
                </p>
                <button type="submit" class="registrar-btn registrar-btn-primary" style="width:100%;justify-content:center">
                    <i class="fa-solid fa-calendar-check"></i> Create Schedule
                </button>
                <a href="{{ route('exam.schedules') }}" class="registrar-btn registrar-btn-secondary" style="width:100%;justify-content:center">
                    Cancel
                </a>
            </div>
        </div>
    </div>

</form>
@endsection
