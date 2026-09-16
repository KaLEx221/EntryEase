@extends('layouts.app')

@section('title', 'Student Documents | EntryEase')
@section('body-class', 'role-registrar')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/registrar.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/registrar-documents.css') }}">
@endsection
@section('sidebar')
    @include('admission.partials.sidebar-registrar')
@endsection

@section('content')
    <div class="registrar-page-header">
        <div>
            <h2><i class="fa-solid fa-file-lines"></i> Student Documents</h2>
            <p>Application #{{ $applicant->id }}</p>
        </div>
        <div class="ee-page-actions">
            <x-back-button variant="secondary" />
        </div>
    </div>

    {{-- Student info --}}
    <div class="registrar-card">
        <div class="registrar-card-header">
            <h3><i class="fa-solid fa-user-graduate"></i> Applicant Information</h3>
        </div>
        <div class="registrar-card-body">
            <div class="registrar-info-grid">
                <div class="registrar-info-item">
                    <label>Grade Level</label>
                    <span>{{ $applicant->grade_level }}</span>
                </div>
                <div class="registrar-info-item">
                    <label>Status</label>
                    <span class="status-badge
                        @switch($applicant->status)
                            @case('Pending') pending @break
                            @case('Under Review') review @break
                            @case('Approved') approved @break
                            @case('Rejected') rejected @break
                        @endswitch">{{ $applicant->status }}</span>
                </div>
                <div class="registrar-info-item">
                    <label>Submitted</label>
                    <span>{{ $applicant->created_at->format('M d, Y') }}</span>
                </div>
                <div class="registrar-info-item">
                    <label>Documents Updated</label>
                    <span>{{ $applicant->documents_updated_at ? $applicant->documents_updated_at->format('M d, Y H:i') : 'Never' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="registrar-card">
        <div class="registrar-card-header">
            <h3><i class="fa-solid fa-folder-open"></i> Uploaded Documents</h3>
        </div>
        <div class="registrar-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem">

                {{-- 2x2 Photo --}}
                <div class="registrar-card" style="margin-bottom:0;border:1px solid var(--line)">
                    <div class="registrar-card-header" style="background:linear-gradient(135deg,#4b5563,#6b7280)">
                        <h3><i class="fa-solid fa-camera"></i> 2x2 Photo</h3>
                    </div>
                    <div class="registrar-card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        @if($applicant->photo_2x2)
                            @php
                                $photoPreviewVersion = md5($applicant->photo_2x2 . '|' . optional($applicant->documents_updated_at)->timestamp);
                                $photoPreviewUrl = route('registrar.student.document.preview', ['applicant' => $applicant, 'documentType' => 'photo_2x2', 'v' => $photoPreviewVersion]);
                            @endphp
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <i class="fa-solid fa-circle-check" style="color:#16a34a"></i>
                                <span class="status-badge approved">Uploaded</span>
                            </div>
                            <div style="border:1px solid var(--line);border-radius:8px;overflow:hidden;background:#f8fafc;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center">
                                <img src="{{ $photoPreviewUrl }}"
                                     alt="2x2 photo preview"
                                     style="width:100%;height:100%;object-fit:contain">
                            </div>
                            <div style="font-size:.82rem;color:#666;line-height:1.6">
                                <strong>Format:</strong> {{ strtoupper(pathinfo($applicant->photo_2x2, PATHINFO_EXTENSION)) }}<br>
                                @if($applicant->documents_updated_at)
                                    <strong>Uploaded:</strong> {{ $applicant->documents_updated_at->format('M d, Y H:i') }}
                                @endif
                            </div>
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                                <a href="{{ route('registrar.student.document.download', ['applicant' => $applicant, 'documentType' => 'photo_2x2']) }}"
                                   class="registrar-btn registrar-btn-primary">
                                    <i class="fa-solid fa-download"></i> Download
                                </a>
                                <a href="{{ $photoPreviewUrl }}"
                                   target="_blank" class="registrar-btn registrar-btn-secondary">
                                    <i class="fa-solid fa-eye"></i> Preview
                                </a>
                            </div>
                        @else
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <i class="fa-solid fa-circle-xmark" style="color:#dc2626"></i>
                                <span class="status-badge pending">Not uploaded</span>
                            </div>
                            <p style="color:#999;font-size:.875rem;margin:0">
                                <i class="fa-solid fa-inbox"></i> No 2x2 photo uploaded yet.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- PSA Birth Certificate --}}
                <div class="registrar-card" style="margin-bottom:0;border:1px solid var(--line)">
                    <div class="registrar-card-header" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6)">
                        <h3><i class="fa-solid fa-file-pdf"></i> PSA Birth Certificate</h3>
                    </div>
                    <div class="registrar-card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        @if($applicant->psa_birth_cert)
                            @php
                                $psaPreviewVersion = md5($applicant->psa_birth_cert . '|' . optional($applicant->documents_updated_at)->timestamp);
                                $psaPreviewUrl = route('registrar.student.document.preview', ['applicant' => $applicant, 'documentType' => 'psa_birth_cert', 'v' => $psaPreviewVersion]);
                                $psaExtension = strtolower(pathinfo($applicant->psa_birth_cert, PATHINFO_EXTENSION));
                            @endphp
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <i class="fa-solid fa-circle-check" style="color:#16a34a"></i>
                                <span class="status-badge approved">Uploaded</span>
                            </div>
                            <div style="border:1px solid var(--line);border-radius:8px;overflow:hidden;background:#f8fafc;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center">
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
                            <div style="font-size:.82rem;color:#666;line-height:1.6">
                                <strong>Format:</strong> {{ strtoupper(pathinfo($applicant->psa_birth_cert, PATHINFO_EXTENSION)) }}<br>
                                @if($applicant->documents_updated_at)
                                    <strong>Uploaded:</strong> {{ $applicant->documents_updated_at->format('M d, Y H:i') }}
                                @endif
                            </div>
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                                <a href="{{ route('registrar.student.document.download', ['applicant' => $applicant, 'documentType' => 'psa_birth_cert']) }}"
                                   class="registrar-btn registrar-btn-primary">
                                    <i class="fa-solid fa-download"></i> Download
                                </a>
                                <a href="{{ $psaPreviewUrl }}"
                                   target="_blank" class="registrar-btn registrar-btn-secondary">
                                    <i class="fa-solid fa-eye"></i> Preview
                                </a>
                            </div>
                        @else
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <i class="fa-solid fa-circle-xmark" style="color:#dc2626"></i>
                                <span class="status-badge pending">Not uploaded</span>
                            </div>
                            <p style="color:#999;font-size:.875rem;margin:0">
                                <i class="fa-solid fa-inbox"></i> No PSA Birth Certificate uploaded yet.
                            </p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Application summary --}}
    <div class="registrar-card">
        <div class="registrar-card-header">
            <h3><i class="fa-solid fa-clipboard-list"></i> Application Summary</h3>
        </div>
        <div class="registrar-card-body">
            <div class="registrar-info-grid">
                <div class="registrar-info-item">
                    <label>Grade Level</label>
                    <span>{{ $applicant->grade_level }}</span>
                </div>
                <div class="registrar-info-item">
                    <label>Status</label>
                    <span class="status-badge
                        @switch($applicant->status)
                            @case('Pending') pending @break
                            @case('Under Review') review @break
                            @case('Approved') approved @break
                            @case('Rejected') rejected @break
                        @endswitch">{{ $applicant->status }}</span>
                </div>
                <div class="registrar-info-item">
                    <label>Submitted</label>
                    <span>{{ $applicant->created_at->format('F d, Y') }}</span>
                </div>
            </div>
            <div style="margin-top:1rem">
                <a href="{{ route('registrar.application.view', $applicant) }}" class="registrar-btn registrar-btn-primary">
                    <i class="fa-solid fa-eye"></i> View Full Application
                </a>
            </div>
        </div>
    </div>
@endsection
