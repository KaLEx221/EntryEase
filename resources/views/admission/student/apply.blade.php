@extends('layouts.app')

@section('title', 'Apply | EntryEase')
@section('body-class', 'role-student')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/student.css') }}?v={{ filemtime(public_path('css/student.css')) }}">
@endsection

@section('content')
<div class="student-page-layer">
<div class="student-main-box">

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="page-header student-section-card">
        <div class="page-header-text">
            <h2><i class="fa-solid fa-file-pen"></i><span>Grade 7 Application</span></h2>
        </div>
        <div class="ee-page-actions">
            <x-back-button />
        </div>
    </div>

    {{-- ── Validation errors ───────────────────────────────── --}}
    @if($errors->any())
        <div class="apply-error-box student-section-card">
            <div class="apply-error-title">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Please fix the following errors:
            </div>
            <ul class="apply-error-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Application form ────────────────────────────────── --}}
    <form action="{{ route('student.apply.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="applyForm"
          novalidate>
        @csrf

        {{-- ── Section 1: Grade level (fixed) ─────────────── --}}
        <div class="apply-section student-section-card">
            <div class="apply-section-head">
                <div class="apply-section-num">1</div>
                <div>
                    <h3>Grade Level</h3>
                </div>
            </div>
            <div class="apply-grade-display">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Grade 7 — Entrance Examination</span>
            </div>
            <input type="hidden" name="grade_level" value="Grade 7">
        </div>

        {{-- ── Section 2: Personal info ────────────────────── --}}
        <div class="apply-section student-section-card">
            <div class="apply-section-head">
                <div class="apply-section-num">2</div>
                <div>
                    <h3>Personal Information</h3>
                </div>
            </div>

            <div class="apply-fields-grid">
                <div class="apply-field-group">
                    <label for="full_name">Full Name</label>
                    <input type="text"
                           id="full_name"
                           value="{{ $student->name ?? '' }}"
                           disabled
                           class="apply-input apply-input-disabled">
                </div>

                <div class="apply-field-group">
                    <label for="email">Email Address</label>
                    <input type="email"
                           id="email"
                           value="{{ $student->email ?? '' }}"
                           disabled
                           class="apply-input apply-input-disabled">
                </div>

                <div class="apply-field-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                    <label for="phone">Contact Number <span class="apply-required">*</span></label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="{{ old('phone') }}"
                           placeholder="e.g. 09171234567"
                           class="apply-input"
                           required
                           maxlength="20">
                    @error('phone')
                        <span class="apply-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="apply-field-group {{ $errors->has('previous_school') ? 'has-error' : '' }}">
                    <label for="previous_school">Previous School <span class="apply-required">*</span></label>
                    <input type="text"
                           id="previous_school"
                           name="previous_school"
                           value="{{ old('previous_school') }}"
                           placeholder="e.g. Mabini Elementary School"
                           class="apply-input"
                           required
                           maxlength="255">
                    @error('previous_school')
                        <span class="apply-field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ── Section 3: Documents ────────────────────────── --}}
        <div class="apply-section student-section-card">
            <div class="apply-section-head">
                <div class="apply-section-num">3</div>
                <div>
                    <h3>Required Documents</h3>
                </div>
            </div>

            <div class="apply-docs-grid">

                {{-- Photo 2x2 --}}
                <div class="apply-doc-card {{ $errors->has('photo_2x2') ? 'has-error' : '' }}">
                    <div class="apply-doc-header">
                        <div class="apply-doc-icon">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div>
                            <div class="apply-doc-title">2×2 Photo <span class="apply-required">*</span></div>
                        </div>
                    </div>

                    <div class="file-upload-area" id="photo2x2Area">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p><strong>Click to upload or drag & drop</strong></p>
                        <p class="upload-hint"></p>
                        <input type="file"
                               id="photo2x2Input"
                               name="photo_2x2"
                               accept="image/jpeg,image/png,image/jpg"
                               required>
                    </div>
                    <div id="photo2x2Preview" class="apply-file-preview" style="display:none"></div>
                    @error('photo_2x2')
                        <span class="apply-field-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- PSA Birth Certificate --}}
                <div class="apply-doc-card {{ $errors->has('psa_birth_cert') ? 'has-error' : '' }}">
                    <div class="apply-doc-header">
                        <div class="apply-doc-icon">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div>
                            <div class="apply-doc-title">PSA Birth Certificate <span class="apply-required">*</span></div>
                        </div>
                    </div>

                    <div class="file-upload-area" id="psaArea">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p><strong>Click to upload or drag & drop</strong></p>
                        <p class="upload-hint"></p>
                        <input type="file"
                               id="psaInput"
                               name="psa_birth_cert"
                               accept="application/pdf,image/jpeg,image/png,image/jpg"
                               required>
                    </div>
                    <div id="psaPreview" class="apply-file-preview" style="display:none"></div>
                    @error('psa_birth_cert')
                        <span class="apply-field-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ── Submit ───────────────────────────────────────── --}}
        <div class="apply-submit-row student-section-card">
            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Submit Application</span>
            </button>
        </div>

    </form>

</div>
</div>

<script>
(function () {
    // File upload area wiring
    function wireUpload(areaId, inputId, previewId) {
        const area    = document.getElementById(areaId);
        const input   = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!area || !input) return;

        area.addEventListener('click', () => input.click());

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            showPreview(file, area, preview);
        });

        area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('dragover'); });
        area.addEventListener('dragleave', () => area.classList.remove('dragover'));
        area.addEventListener('drop', e => {
            e.preventDefault();
            area.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showPreview(e.dataTransfer.files[0], area, preview);
            }
        });
    }

    function showPreview(file, area, preview) {
        const size = file.size > 1024 * 1024
            ? (file.size / 1024 / 1024).toFixed(1) + ' MB'
            : Math.round(file.size / 1024) + ' KB';

        area.classList.add('has-file');
        area.querySelector('p strong').textContent = file.name;
        const hint = area.querySelector('.upload-hint');
        if (hint) hint.textContent = size;

        if (preview && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else if (preview) {
            preview.innerHTML = `<div class="apply-file-name"><i class="fa-solid fa-file-pdf"></i> ${file.name}</div>`;
            preview.style.display = 'block';
        }
    }

    wireUpload('photo2x2Area', 'photo2x2Input', 'photo2x2Preview');
    wireUpload('psaArea',      'psaInput',      'psaPreview');

    // Submit button loading state
    document.getElementById('applyForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Submitting…</span>';
        }
    });
})();
</script>
@endsection
