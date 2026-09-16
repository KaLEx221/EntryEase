<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EntryEase - Admission Management</title>
    <link rel="stylesheet" href="{{ secure_asset('css/entryease.css') }}">
    <script>
        // ── Module configuration injected from Laravel config ─────────────
        // This module's own origin — used for API calls
        window.ENTRYEASE_API_BASE = "{{ config('app.url') }}";

        // The portal that embeds this module — used for SSO postMessage origin check
        window.PORTAL_ORIGIN = "{{ config('app.portal_url') }}";

        // How long to wait for the portal's SSO_TOKEN before showing an error
        window.SSO_TIMEOUT_MS = 8000;
        window.DEORIS_SSO_MODE = "module";
    </script>
</head>
<body>

<!-- Root container — the ONLY static HTML element -->
<div id="entryease-root" style="
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
">
    <!-- Loader — visible by default, hidden after SSO + data load -->
    <div id="entryease-loader" style="
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
    ">
        <div style="
            width: 48px; height: 48px;
            border-radius: 50%;
            border: 4px solid rgba(0,0,0,.1);
            border-top-color: #7C3041;
            animation: spin .8s linear infinite;
        "></div>
        <p style="color: #7C3041; font-weight: 700; font-size: 15px;">Loading…</p>
        <!-- Error message — hidden by default, shown if SSO or API fails -->
        <p id="entryease-loader-error" style="
            color: #dc2626; font-size: 13px;
            display: none; max-width: 360px; text-align: center;
        "></p>
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<!-- module-bridge.js MUST load BEFORE your main app script -->
<script src="{{ rtrim(config('app.portal_url', 'https://deoris.test'), '/') }}/module-bridge.js"></script>
<script src="{{ secure_asset('js/entryease.js') }}?v={{ filemtime(public_path('js/entryease.js')) }}"></script>

</body>
</html>
