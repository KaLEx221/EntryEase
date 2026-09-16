@extends('layouts.app')

@section('title', config('app.name'))
@section('body-class', 'no-sidebar')
@section('role-css')
    <link rel="stylesheet" href="{{ secure_asset('css/landing.css') }}">
@endsection

@section('content')
    <div class="landing">
        <div class="landing-card">
            <div class="landing-head">
                <h1 class="landing-title">{{ config('app.name', 'EntryEase') }}</h1>
                <div class="landing-sub">Welcome</div>
            </div>
            <div class="landing-body">
                <p>Continue to the admission portal.</p>
                <div class="landing-cta">
                    <a href="{{ route('home') }}" class="landing-btn primary"><i class="fa-solid fa-arrow-right"></i> Go to Portal</a>
                </div>
            </div>
        </div>
    </div>
@endsection
