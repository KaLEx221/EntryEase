@extends('layouts.app')

@section('title', 'Create Admission Officer | EntryEase')
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
        <h1 class="dash-title">Add Admission Officer</h1>
        <p class="dash-sub">Create a new officer account in DEORIS</p>
    </div>
    <div class="ee-page-actions">
        <x-back-button variant="secondary" />
    </div>
</div>

<div class="card" style="max-width:600px">
    <div class="card-header">
        <h2><i class="fa-solid fa-user-plus"></i> New Officer Account</h2>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="admin-monitor-notice" style="background:var(--danger-bg);border-color:rgba(220,38,38,.2);border-left-color:var(--danger);color:#991b1b;margin-bottom:1.25rem">
                <i class="fa-solid fa-circle-exclamation"></i>
                <ul style="margin:0;padding-left:1rem">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.registrars.store') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label for="name">Full Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="name" name="name"
                       value="{{ old('name') }}"
                       placeholder="Full name" required autofocus>
                @error('name')<p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address <span style="color:var(--danger)">*</span></label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="officer@school.edu" required>
                @error('email')<p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color:var(--danger)">*</span></label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <span style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;display:block">Minimum 8 characters</span>
                @error('password')<p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span style="color:var(--danger)">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
                @error('password_confirmation')<p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex;gap:.65rem;margin-top:1.5rem">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-user-plus"></i> Create Officer
                </button>
                <a href="{{ route('admin.registrars') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
