@extends('layouts.app')

@section('title', 'Page Not Found | EntryEase')
@section('body-class', 'no-sidebar')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/error.css') }}">
@endsection

@section('content')
    <div class="error-wrap">
        <div class="error-card">
            <h1 class="error-code">404</h1>
            <div class="error-title">Page Not Found</div>
            <p>The page you're looking for doesn't exist or has been moved.</p>
            <div class="error-actions">
                <a href="{{ route('home') }}" class="error-btn primary"><i class="fa-solid fa-house"></i> Home</a>
                <a href="{{ url()->previous() }}" class="error-btn secondary"><i class="fa-solid fa-arrow-left"></i> Go Back</a>
            </div>
        </div>
    </div>
@endsection
