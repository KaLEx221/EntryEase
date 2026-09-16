@extends('layouts.app')

@section('title', 'Score Analytics | EntryEase')
@php $isAdmin = session('sso_role') === 'admin'; @endphp
@section('body-class', $isAdmin ? 'role-admin' : 'role-registrar')
@section('role-css')
    @if($isAdmin)
        <link rel="stylesheet" href="{{ secure_asset('css/admin.css') }}">
    @else
        <link rel="stylesheet" href="{{ secure_asset('css/registrar.css') }}">
    @endif
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam-schedules.css') }}">
@endsection
@section('role-js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js" defer></script>
    <script src="{{ secure_asset('js/exam.js') }}" defer></script>
@endsection
@section('sidebar')
    @if($isAdmin)
        @include('admission.partials.sidebar-admin')
    @else
        @include('admission.partials.sidebar-registrar')
    @endif
@endsection

@section('content')
    <div class="es-page-header">
        <div class="es-page-header-left">
            <h1 class="es-page-title">Score Analytics</h1>
            <p class="es-page-sub">{{ $schedule->title }} &mdash; {{ $schedule->exam_date->format('F d, Y') }}</p>
        </div>
        <div class="ee-page-actions">
            <a href="{{ route('exam.schedules.applicants', $schedule) }}" class="registrar-btn registrar-btn-secondary">
                <i class="fa-solid fa-users"></i> Applicants
            </a>
            <x-back-button variant="secondary" />
        </div>
    </div>

    {{-- Summary metrics --}}
    <div class="exam-analytics-metrics">
        <div class="exam-metric-card">
            <div class="exam-metric-icon"><i class="fa-solid fa-users"></i></div>
            <div class="exam-metric-value">{{ $stats['total'] }}</div>
            <div class="exam-metric-label">Scores Recorded</div>
        </div>
        <div class="exam-metric-card passed">
            <div class="exam-metric-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="exam-metric-value">{{ $stats['passed'] }}</div>
            <div class="exam-metric-label">Passed</div>
        </div>
        <div class="exam-metric-card failed">
            <div class="exam-metric-icon"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="exam-metric-value">{{ $stats['failed'] }}</div>
            <div class="exam-metric-label">Failed</div>
        </div>
        <div class="exam-metric-card">
            <div class="exam-metric-icon"><i class="fa-solid fa-calculator"></i></div>
            <div class="exam-metric-value">{{ $stats['average'] ?? '—' }}</div>
            <div class="exam-metric-label">Average Score</div>
        </div>
        <div class="exam-metric-card">
            <div class="exam-metric-icon"><i class="fa-solid fa-arrow-up"></i></div>
            <div class="exam-metric-value">{{ $stats['highest'] ?? '—' }}</div>
            <div class="exam-metric-label">Highest Score</div>
        </div>
        <div class="exam-metric-card">
            <div class="exam-metric-icon"><i class="fa-solid fa-arrow-down"></i></div>
            <div class="exam-metric-value">{{ $stats['lowest'] ?? '—' }}</div>
            <div class="exam-metric-label">Lowest Score</div>
        </div>
    </div>

    @if($scores->isNotEmpty())
        {{-- Charts --}}
        <div class="exam-charts-row">
            <div class="registrar-card">
                <div class="registrar-card-header">
                    <h3><i class="fa-solid fa-chart-pie"></i> Pass / Fail Ratio</h3>
                </div>
                <div class="registrar-card-body" style="display:flex;justify-content:center">
                    <div style="max-width:280px;width:100%">
                        <canvas id="passFailChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="registrar-card">
                <div class="registrar-card-header">
                    <h3><i class="fa-solid fa-chart-bar"></i> Score Distribution</h3>
                </div>
                <div class="registrar-card-body">
                    <canvas id="distributionChart" style="max-height:260px"></canvas>
                </div>
            </div>
        </div>

        {{-- Score table --}}
        <div class="registrar-card">
            <div class="registrar-card-header">
                <h3><i class="fa-solid fa-table-list"></i> Individual Results</h3>
            </div>
            <div class="registrar-card-body">
                <div class="table-responsive">
                    <table class="exam-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Score</th>
                                <th>Total</th>
                                <th>Percentage</th>
                                <th>Result</th>
                                <th>Remarks</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scores->sortByDesc('score') as $s)
                                <tr>
                                    <td><strong>{{ $s->applicant?->student?->name ?? '—' }}</strong></td>
                                    <td>{{ $s->applicant?->student?->email ?? '—' }}</td>
                                    <td>{{ $s->score }}</td>
                                    <td>{{ $s->total_items }}</td>
                                    <td>{{ $s->percentage }}%</td>
                                    <td>
                                        <span class="exam-result-badge {{ $s->passed ? 'passed' : 'failed' }}">
                                            <i class="fa-solid {{ $s->passed ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                            {{ $s->passed ? 'Passed' : 'Failed' }}
                                        </span>
                                    </td>
                                    <td>{{ $s->remarks ?? '—' }}</td>
                                    <td>{{ $s->recorded_by ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Chart data for exam.js --}}
        <div id="analyticsData"
             data-passed="{{ $stats['passed'] }}"
             data-failed="{{ $stats['failed'] }}"
             data-scores="{{ e(json_encode($scores->pluck('percentage')->values())) }}"
             hidden></div>
    @else
        <div class="registrar-card">
            <div class="registrar-card-body exam-empty-state">
                <i class="fa-solid fa-chart-simple"></i>
                <p>No scores recorded for this schedule yet.</p>
                <a href="{{ route('exam.schedules.applicants', $schedule) }}" class="registrar-btn registrar-btn-primary">
                    <i class="fa-solid fa-pen-to-square"></i> Enter Scores
                </a>
            </div>
        </div>
    @endif
@endsection
