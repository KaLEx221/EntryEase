@extends('layouts.app')

@section('title', 'Edit Admission Officer | EntryEase')
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
        <h1 class="dash-title">Edit Admission Officer</h1>
        <p class="dash-sub">Update account details for {{ $user->name }}</p>
    </div>
    <div class="ee-page-actions">
        <x-back-button variant="secondary" />
    </div>
</div>

<div class="card" style="max-width:600px">
    <div class="card-header">
        <h2><i class="fa-solid fa-user-pen"></i> Account Details</h2>
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

        <form action="{{ route('admin.registrars.update', $user->id) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Full Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="name" name="name"
                       value="{{ old('name', $user->name) }}"
                       placeholder="Full name" required autofocus>
                @error('name')<p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address <span style="color:var(--danger)">*</span></label>
                <input type="email" id="email" name="email"
                       value="{{ old('email', $user->email) }}"
                       placeholder="officer@school.edu" required>
                @error('email')<p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="password">New Password <span style="color:var(--text-muted);font-weight:400;font-size:.8rem">(leave blank to keep current)</span></label>
                <input type="password" id="password" name="password" placeholder="••••••••">
                <span style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;display:block">Minimum 8 characters</span>
                @error('password')<p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                @error('password_confirmation')<p style="color:var(--danger);font-size:.8rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex;gap:.65rem;margin-top:1.5rem">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                <a href="{{ route('admin.registrars') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
