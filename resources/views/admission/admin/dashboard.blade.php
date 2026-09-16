@extends('layouts.app')

@section('title', 'Admin Dashboard | EntryEase')
@section('body-class', 'role-admin')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/admin.css') }}">
@endsection
@section('role-js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js" defer></script>
    <script src="{{ secure_asset('js/admin.js') }}" defer></script>
@endsection
@section('sidebar')
    @include('admission.partials.sidebar-admin')
@endsection

@section('content')

{{-- ── Page header ──────────────────────────────────────────── --}}
<div class="dash-header">
    <div>
        <h1 class="dash-title">Dashboard</h1>
        <p class="dash-sub">Monitoring overview — {{ now()->format('l, F j, Y') }}</p>
    </div>
    <div class="dash-header-actions">
        <a href="{{ route('exam.schedules') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-calendar-days"></i> Exam Schedules
        </a>
        <a href="{{ route('admin.applications') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-file-lines"></i> All Applications
        </a>
    </div>
</div>

{{-- ── Monitoring notice ────────────────────────────────────── --}}
<div class="admin-monitor-notice">
    <i class="fa-solid fa-shield-halved"></i>
    <span><strong>Monitoring mode.</strong> You can view all data but only Admission Officers can approve, reject, or modify applications.</span>
</div>

{{-- ── Stat cards ───────────────────────────────────────────── --}}
<div class="metrics-grid">
    <a href="{{ route('admin.applications') }}" class="metric-card" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-file-lines"></i></div>
        <div class="metric-value">{{ $stats['total'] }}</div>
        <div class="metric-label">Total Applications</div>
    </a>
    <a href="{{ route('admin.applications') }}?status=Pending" class="metric-card pending" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="metric-value">{{ $stats['pending'] }}</div>
        <div class="metric-label">Pending</div>
    </a>
    <a href="{{ route('admin.applications') }}?status=Under+Review" class="metric-card review" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <div class="metric-value">{{ $stats['under_review'] }}</div>
        <div class="metric-label">Under Review</div>
    </a>
    <a href="{{ route('admin.applications') }}?status=Approved" class="metric-card approved" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="metric-value">{{ $stats['approved'] }}</div>
        <div class="metric-label">Approved</div>
    </a>
    <a href="{{ route('admin.applications') }}?status=Rejected" class="metric-card rejected" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="metric-value">{{ $stats['rejected'] }}</div>
        <div class="metric-label">Rejected</div>
    </a>
    <a href="{{ route('admin.applications') }}" class="metric-card" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-user-graduate"></i></div>
        <div class="metric-value">{{ $stats['total_students'] }}</div>
        <div class="metric-label">Applicant Students</div>
    </a>
    <a href="{{ route('exam.schedules') }}" class="metric-card" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="metric-value">{{ $stats['upcoming_exams'] }}</div>
        <div class="metric-label">Upcoming Exams</div>
    </a>
</div>

<div class="metrics-grid" style="margin-top:1rem;">
    <a href="{{ route('exam.schedules') }}" class="metric-card" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-chart-pie"></i></div>
        <div class="metric-value">{{ $scoreStats['total'] }}</div>
        <div class="metric-label">Scores Recorded</div>
    </a>
    <a href="{{ route('admin.applications') }}?status=Approved" class="metric-card approved" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="metric-value">{{ $scoreStats['passed'] }}</div>
        <div class="metric-label">Passed</div>
    </a>
    <a href="{{ route('admin.applications') }}?status=Rejected" class="metric-card rejected" style="text-decoration:none">
        <div class="metric-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="metric-value">{{ $scoreStats['failed'] }}</div>
        <div class="metric-label">Failed</div>
    </a>
    <div class="metric-card">
        <div class="metric-icon"><i class="fa-solid fa-calculator"></i></div>
        <div class="metric-value">{{ $scoreStats['average'] }}%</div>
        <div class="metric-label">Average Score</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon"><i class="fa-solid fa-arrow-up"></i></div>
        <div class="metric-value">{{ $scoreStats['highest'] }}%</div>
        <div class="metric-label">Highest Score</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon"><i class="fa-solid fa-arrow-down"></i></div>
        <div class="metric-value">{{ $scoreStats['lowest'] }}%</div>
        <div class="metric-label">Lowest Score</div>
    </div>
</div>

{{-- ── Charts ───────────────────────────────────────────────── --}}
<div class="admin-charts-row">
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-chart-pie"></i> Pass / Fail Ratio</h2>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="passFailChart"></canvas>
                <div class="chart-fallback">
                    <p><strong>Pass / Fail ratio</strong></p>
                    <ul>
                        <li><span>Passed</span><span>{{ $scoreStats['passed'] }}</span></li>
                        <li><span>Failed</span><span>{{ $scoreStats['failed'] }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-chart-bar"></i> Score Distribution</h2>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="scoreDistChart"></canvas>
                <div class="chart-fallback">
                    <p><strong>Score distribution</strong></p>
                    <ul>
                        @foreach(['0–9','10–19','20–29','30–39','40–49','50–59','60–69','70–79','80–89','90–100'] as $index => $label)
                            <li><span>{{ $label }}</span><span>{{ $scoreDistribution[$index] ?? 0 }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Recent applications ──────────────────────────────────── --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> Recent Applications</h2>
        <a href="{{ route('admin.applications') }}" class="card-header-link">View all <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="card-body" style="padding:0">
        @if($recentActivity->isEmpty())
            <div class="dash-empty">
                <i class="fa-solid fa-inbox"></i>
                <p>No applications yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivity as $app)
                            <tr>
                                <td>
                                    <div class="td-student">
                                        <div class="td-avatar">{{ strtoupper(substr($app->student?->name ?? '?', 0, 1)) }}</div>
                                        <strong>{{ $app->student?->name ?? 'Unknown' }}</strong>
                                    </div>
                                </td>
                                <td class="td-muted">{{ $app->student?->email ?? '—' }}</td>
                                <td><span class="grade-chip">{{ $app->grade_level }}</span></td>
                                <td>
                                    <span class="status-badge @switch($app->effective_status)
                                        @case('Pending') pending @break
                                        @case('Under Review') review @break
                                        @case('Approved') approved @break
                                        @case('Rejected') rejected @break
                                    @endswitch">{{ $app->effective_status }}</span>
                                </td>
                                <td class="td-muted">{{ $app->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.student.documents', $app) }}" class="btn btn-secondary btn-sm">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div id="adminDashboardData"
     data-score="{{ e(json_encode($scoreStats)) }}"
     data-distribution="{{ e(json_encode($scoreDistribution)) }}"
     hidden></div>
@endsection
