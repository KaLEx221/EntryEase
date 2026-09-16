@extends('layouts.app')

@section('title', 'Student Documents | EntryEase')
@section('body-class', 'role-admin')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/admin.css') }}">
@endsection
@section('role-js')
    <script src="{{ secure_asset('js/admin.js') }}" defer></script>
@endsection
@section('sidebar')
    @include('admission.partials.sidebar-admin')
@endsection

@section('content')

    <div class="dash-header">
        <div>
            <h1 class="dash-title"><i class="fa-solid fa-folder-open"></i> Student Documents</h1>
            <p class="dash-sub">Application #{{ $applicant->id }}</p>
        </div>
        <div class="ee-page-actions">
            <x-back-button variant="secondary" />
        </div>
    </div>

    {{-- Applicant info --}}
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-user-graduate"></i> Applicant Information</h2>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Grade Level</div>
                    <div>{{ $applicant->grade_level }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Status</div>
                    <span class="status-badge @switch($applicant->status)
                        @case('Pending') pending @break
                        @case('Under Review') review @break
                        @case('Approved') approved @break
                        @case('Rejected') rejected @break
                    @endswitch">{{ $applicant->status }}</span>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Submitted</div>
                    <div>{{ $applicant->created_at->format('M d, Y') }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Documents Updated</div>
                    <div>{{ $applicant->documents_updated_at ? $applicant->documents_updated_at->format('M d, Y H:i') : 'Never' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-file-lines"></i> Uploaded Documents</h2>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem">

                {{-- 2x2 Photo --}}
                <div class="card" style="margin-bottom:0">
                    <div class="card-header" style="background:linear-gradient(135deg,#4b5563,#6b7280)">
                        <h3><i class="fa-solid fa-camera"></i> 2×2 Photo</h3>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        @if($applicant->photo_2x2)
                            @php
                                $photoPreviewVersion = md5($applicant->photo_2x2 . '|' . optional($applicant->documents_updated_at)->timestamp);
                                $photoPreviewUrl = route('admin.student.document.preview', ['applicant' => $applicant, 'documentType' => 'photo_2x2', 'v' => $photoPreviewVersion]);
                            @endphp
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <i class="fa-solid fa-circle-check" style="color:var(--success)"></i>
                                <span class="status-badge approved">Uploaded</span>
                            </div>
                            <div style="border:1px solid var(--border,#e5e7eb);border-radius:8px;overflow:hidden;background:#f8fafc;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center">
                                <img src="{{ $photoPreviewUrl }}"
                                     alt="2x2 photo preview"
                                     style="width:100%;height:100%;object-fit:contain">
                            </div>
                            <div style="font-size:.82rem;color:var(--text-muted);line-height:1.6">
                                <strong>Format:</strong> {{ strtoupper(pathinfo($applicant->photo_2x2, PATHINFO_EXTENSION)) }}<br>
                                @if($applicant->documents_updated_at)
                                    <strong>Uploaded:</strong> {{ $applicant->documents_updated_at->format('M d, Y H:i') }}
                                @endif
                            </div>
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                                <a href="{{ route('admin.student.document.download', ['applicant' => $applicant, 'documentType' => 'photo_2x2']) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-download"></i> Download
                                </a>
                                <a href="{{ $photoPreviewUrl }}"
                                   target="_blank" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Preview
                                </a>
                            </div>
                        @else
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i>
                                <span class="status-badge pending">Not uploaded</span>
                            </div>
                            <p style="color:var(--text-muted);font-size:.875rem;margin:0">
                                <i class="fa-solid fa-inbox"></i> No 2×2 photo uploaded yet.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- PSA Birth Certificate --}}
                <div class="card" style="margin-bottom:0">
                    <div class="card-header" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6)">
                        <h3><i class="fa-solid fa-file-pdf"></i> PSA Birth Certificate</h3>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        @if($applicant->psa_birth_cert)
                            @php
                                $psaPreviewVersion = md5($applicant->psa_birth_cert . '|' . optional($applicant->documents_updated_at)->timestamp);
                                $psaPreviewUrl = route('admin.student.document.preview', ['applicant' => $applicant, 'documentType' => 'psa_birth_cert', 'v' => $psaPreviewVersion]);
                                $psaExtension = strtolower(pathinfo($applicant->psa_birth_cert, PATHINFO_EXTENSION));
                            @endphp
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <i class="fa-solid fa-circle-check" style="color:var(--success)"></i>
                                <span class="status-badge approved">Uploaded</span>
                            </div>
                            <div style="border:1px solid var(--border,#e5e7eb);border-radius:8px;overflow:hidden;background:#f8fafc;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center">
                                @if($psaExtension === 'pdf')
                                    <iframe src="{{ $psaPreviewUrl }}"
                                            title="PSA birth certificate preview"
                                            style="width:100%;height:100%;border:0"></iframe>
                                @else
                                    <img src="{{ $psaPreviewUrl }}"
                                         alt="PSA birth certificate preview"
                                         style="width:100%;height:100%;object-fit:contain">
                                @endif
                            </div>
                            <div style="font-size:.82rem;color:var(--text-muted);line-height:1.6">
                                <strong>Format:</strong> {{ strtoupper(pathinfo($applicant->psa_birth_cert, PATHINFO_EXTENSION)) }}<br>
                                @if($applicant->documents_updated_at)
                                    <strong>Uploaded:</strong> {{ $applicant->documents_updated_at->format('M d, Y H:i') }}
                                @endif
                            </div>
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                                <a href="{{ route('admin.student.document.download', ['applicant' => $applicant, 'documentType' => 'psa_birth_cert']) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-download"></i> Download
                                </a>
                                <a href="{{ $psaPreviewUrl }}"
                                   target="_blank" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Preview
                                </a>
                            </div>
                        @else
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i>
                                <span class="status-badge pending">Not uploaded</span>
                            </div>
                            <p style="color:var(--text-muted);font-size:.875rem;margin:0">
                                <i class="fa-solid fa-inbox"></i> No PSA Birth Certificate uploaded yet.
                            </p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Application summary --}}
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-clipboard-list"></i> Application Summary</h2>
        </div>
        <div class="card-body">
            @php $info = json_decode($applicant->additional_info, true) ?? []; @endphp
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1.25rem">
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Student Name</div>
                    <div>{{ $applicant->student?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Email</div>
                    <div>{{ $applicant->student?->email ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Phone</div>
                    <div>{{ $info['phone'] ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Previous School</div>
                    <div>{{ $info['previous_school'] ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Grade Level</div>
                    <div>{{ $applicant->grade_level }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Status</div>
                    <span class="status-badge @switch($applicant->status)
                        @case('Pending') pending @break
                        @case('Under Review') review @break
                        @case('Approved') approved @break
                        @case('Rejected') rejected @break
                    @endswitch">{{ $applicant->status }}</span>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Submitted</div>
                    <div>{{ $applicant->created_at->format('F d, Y') }}</div>
                </div>
                @if($applicant->examSchedule)
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Exam Date</div>
                    <div>{{ $applicant->examSchedule->exam_date->format('M d, Y') }}</div>
                </div>
                @endif
                @if($applicant->examScore)
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Exam Score</div>
                    <span class="exam-result-badge {{ $applicant->examScore->passed ? 'passed' : 'failed' }}">
                        <i class="fa-solid {{ $applicant->examScore->passed ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                        {{ $applicant->examScore->percentage }}%
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>

@endsection
