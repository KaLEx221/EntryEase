@extends('layouts.app')

@section('title', 'Admin Applications | EntryEase')
@section('body-class', 'role-admin')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/exam.css') }}">
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
            <h1 class="dash-title">Applications</h1>
            <p class="dash-sub">Manage and review student applications</p>
        </div>
        <div class="ee-page-actions">
            <x-back-button variant="secondary" />
        </div>
    </div>
    <div id="adminApplications" data-index-url="{{ route('admin.applications') }}">
    <form method="GET" action="{{ route('admin.applications') }}" class="filter-bar" id="adminFilterForm">
        <div class="form-group select-with-arrow">
            <label for="status-filter">Filter by Status</label>
            <select id="status-filter" name="status">
                <option value="">All Status</option>
                <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Under Review" {{ request('status') === 'Under Review' ? 'selected' : '' }}>Under Review</option>
                <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="form-group">
            <label for="search-input">Search Students</label>
            <input type="text" id="search-input" name="search"
                   value="{{ request('search') }}"
                   placeholder="By name or email...">
        </div>
        <div style="align-self:flex-end;display:flex;gap:.5rem;flex-shrink:0">
            <button class="btn btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
            @if(request('status') || request('search'))
                <a href="{{ route('admin.applications') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Clear
                </a>
            @endif
        </div>
    </form>
    <div class="admin-monitor-notice">
        <i class="fa-solid fa-eye"></i>
        <span>Admin view is <strong>monitoring only</strong>. Only Admission Officers can approve or reject applications.</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-file-lines"></i> Student Applications</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Grade Level</th>
                            <th>Exam Schedule</th>
                            <th>Exam Score</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicants as $app)
                            <tr>
                                <td><strong>{{ $app->student?->name ?? 'Unknown' }}</strong></td>
                                <td>{{ $app->student?->email ?? '—' }}</td>
                                <td>{{ json_decode($app->additional_info, true)['phone'] ?? '—' }}</td>
                                <td>{{ $app->grade_level }}</td>
                                <td>
                                    @if($app->examSchedule)
                                        <span style="font-size:.82rem">
                                            {{ $app->examSchedule->exam_date->format('M d, Y') }}<br>
                                            <span style="color:#888">{{ $app->examSchedule->batch }}</span>
                                        </span>
                                    @else
                                        <span style="color:#bbb">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($app->examScore)
                                        <span class="exam-result-badge {{ $app->examScore->passed ? 'passed' : 'failed' }}">
                                            {{ $app->examScore->percentage }}%
                                        </span>
                                    @else
                                        <span style="color:#bbb">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge
                                        @switch($app->effective_status)
                                            @case('Pending') pending @break
                                            @case('Under Review') review @break
                                            @case('Approved') approved @break
                                            @case('Rejected') rejected @break
                                            @default pending
                                        @endswitch">
                                        @switch($app->effective_status)
                                            @case('Pending')<i class="fa-solid fa-hourglass-half"></i>@break
                                            @case('Under Review')<i class="fa-solid fa-eye"></i>@break
                                            @case('Approved')<i class="fa-solid fa-circle-check"></i>@break
                                            @case('Rejected')<i class="fa-solid fa-circle-xmark"></i>@break
                                        @endswitch
                                        {{ $app->effective_status }}
                                    </span>
                                </td>
                                <td>{{ $app->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.student.documents', $app) }}"
                                       class="btn btn-secondary btn-sm">
                                        <i class="fa-solid fa-file"></i> Docs
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center;padding:2rem;color:var(--warm-brown-light)">
                                    <i class="fa-solid fa-inbox" style="font-size:2rem;margin-bottom:1rem;display:block"></i>
                                    No applications found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $applicants->links() }}</div>
        </div>
    </div>
    </div>
@endsection
