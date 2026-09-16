@extends('layouts.app')

@section('title', 'Applicant Review Queue | EntryEase')
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

<div class="dash-header">
    <div>
        <h1 class="dash-title">Review Queue</h1>
        <p class="dash-sub">{{ $applications->total() }} application{{ $applications->total() !== 1 ? 's' : '' }} total</p>
    </div>
    <div class="ee-page-actions">
        <x-back-button variant="secondary" />
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────────────── --}}
<div class="registrar-card" style="margin-bottom:1.25rem">
    <div class="registrar-card-body" style="padding:1rem 1.4rem">
        <form method="GET" action="{{ route('registrar.applications') }}" class="officer-filter-bar">
            <div class="registrar-form-group select-with-arrow" style="margin:0">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['Pending', 'Under Review', 'Approved', 'Rejected'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="registrar-form-group" style="margin:0">
                <label for="search">Search</label>
                <input id="search" name="search" value="{{ request('search') }}" placeholder="Name or email…" autocomplete="off">
            </div>
            <div class="registrar-form-actions" style="margin:0;align-self:flex-end">
                <button type="submit" class="registrar-btn registrar-btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>
                @if(request('status') || request('search'))
                    <a href="{{ route('registrar.applications') }}" class="registrar-btn registrar-btn-secondary">
                        <i class="fa-solid fa-xmark"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Status tabs ──────────────────────────────────────────── --}}
<div class="status-tabs">
    @php
        $tabCounts = [
            ''             => $applications->total(),
            'Pending'      => \App\Models\Applicant::pending()->count(),
            'Under Review' => \App\Models\Applicant::underReview()->count(),
            'Approved'     => \App\Models\Applicant::approved()->count(),
            'Rejected'     => \App\Models\Applicant::rejected()->count(),
        ];
    @endphp
    @foreach([''=>'All', 'Pending'=>'Pending', 'Under Review'=>'Under Review', 'Approved'=>'Approved', 'Rejected'=>'Rejected'] as $val => $label)
        <a href="{{ route('registrar.applications') }}?status={{ urlencode($val) }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}"
           class="status-tab {{ request('status', '') === $val ? 'active' : '' }}">
            {{ $label }}
            <span class="status-tab-count">{{ $tabCounts[$val] }}</span>
        </a>
    @endforeach
</div>

{{-- ── Table ────────────────────────────────────────────────── --}}
<div class="registrar-card">
    <div class="registrar-card-body" style="padding:0">
        @if($applications->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Grade</th>
                            <th>Exam</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $app)
                            <tr>
                                <td>
                                    <div class="td-student">
                                        <div class="td-avatar">{{ strtoupper(substr($app->student?->name ?? '?', 0, 1)) }}</div>
                                        <div>
                                            <strong>{{ $app->student?->name ?? 'Unknown' }}</strong>
                                            <div class="td-email">{{ $app->student?->email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="grade-chip">{{ $app->grade_level }}</span></td>
                                <td>
                                    @if($app->examSchedule)
                                        <div style="font-size:.82rem;line-height:1.4">
                                            <div>{{ $app->examSchedule->exam_date->format('M d, Y') }}</div>
                                            @if($app->examSchedule->batch)
                                                <div class="td-muted">{{ $app->examSchedule->batch }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="td-none">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($app->examScore)
                                        <span class="exam-result-badge {{ $app->examScore->passed ? 'passed' : 'failed' }}">
                                            <i class="fa-solid {{ $app->examScore->passed ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                            {{ $app->examScore->percentage }}%
                                        </span>
                                    @else
                                        <span class="td-none">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge @switch($app->status)
                                        @case('Pending') pending @break
                                        @case('Under Review') review @break
                                        @case('Approved') approved @break
                                        @case('Rejected') rejected @break
                                    @endswitch">{{ $app->status }}</span>
                                    @if($app->exam_stage_label)
                                        <div class="td-muted" style="font-size:.75rem;margin-top:.2rem;">{{ $app->exam_stage_label }}</div>
                                    @endif
                                </td>
                                <td class="td-muted">{{ $app->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('registrar.application.view', $app) }}"
                                       class="registrar-btn registrar-btn-primary"
                                       style="padding:.4rem .85rem;font-size:.8rem">
                                        <i class="fa-solid fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($applications->hasPages())
                <div style="padding:1rem 1.4rem;border-top:1px solid var(--line-soft)">
                    {{ $applications->links() }}
                </div>
            @endif
        @else
            <div class="dash-empty">
                <i class="fa-solid fa-inbox"></i>
                <p>No applications found{{ request('status') || request('search') ? ' for this filter' : '' }}.</p>
                @if(request('status') || request('search'))
                    <a href="{{ route('registrar.applications') }}" class="registrar-btn registrar-btn-secondary" style="margin-top:.5rem">
                        Clear filters
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
