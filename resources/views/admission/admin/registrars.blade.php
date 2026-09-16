@extends('layouts.app')

@section('title', 'Admission Officers | EntryEase')
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
        <h1 class="dash-title">Admission Officers</h1>
        <p class="dash-sub">{{ $registrars->count() }} officer{{ $registrars->count() !== 1 ? 's' : '' }} registered</p>
    </div>
    <div class="ee-page-actions">
        <a href="{{ route('admin.registrars.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add Officer
        </a>
        <x-back-button variant="secondary" />
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-user-tie"></i> Officer Accounts</h2>
    </div>
    <div class="card-body" style="padding:0">
        @if($registrars->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrars as $registrar)
                            <tr>
                                <td>
                                    <div class="td-student">
                                        <div class="td-avatar">{{ strtoupper(substr($registrar->name, 0, 1)) }}</div>
                                        <strong>{{ $registrar->name }}</strong>
                                    </div>
                                </td>
                                <td class="td-muted">{{ $registrar->email }}</td>
                                <td>
                                    <span class="status-badge approved">
                                        <i class="fa-solid fa-circle-check"></i>
                                        {{ ucwords(str_replace('_', ' ', $registrar->role)) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:.4rem">
                                        <a href="{{ route('admin.registrars.edit', $registrar->id) }}" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.registrars.delete', $registrar->id) }}" method="POST"
                                              onsubmit="return confirm('Delete {{ addslashes($registrar->name) }}? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:1px solid rgba(220,38,38,.25)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="dash-empty">
                <i class="fa-solid fa-user-slash"></i>
                <p>No admission officers yet.</p>
                <a href="{{ route('admin.registrars.create') }}" class="btn btn-primary" style="margin-top:.5rem">
                    <i class="fa-solid fa-user-plus"></i> Add First Officer
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
