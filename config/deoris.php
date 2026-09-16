<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DEORIS Event Bus — EntryEase Module
    |--------------------------------------------------------------------------
    */

    'module_name' => env('DEORIS_MODULE_NAME', 'EntryEase'),

    'event_version' => env('DEORIS_EVENT_VERSION', '1.0'),

    'signing_secret' => env('DEORIS_SIGNING_SECRET', env('APP_KEY')),

    'redis_connection' => env('DEORIS_REDIS_CONNECTION', 'default'),

    'redis_channel' => env('DEORIS_REDIS_CHANNEL', 'deoris.events'),

    /*
    |--------------------------------------------------------------------------
    | Legacy module-to-module Redis bus (DeorisEventEnvelope format).
    | Disabled by default — portal hub is the primary integration path.
    |--------------------------------------------------------------------------
    */
    'module_bus_enabled' => (bool) env('DEORIS_MODULE_BUS_ENABLED', false),

    'redis_queue' => env('DEORIS_REDIS_QUEUE', 'deoris-events'),

    'max_event_age_seconds' => (int) env('DEORIS_MAX_EVENT_AGE', 300),

    'trusted_modules' => array_filter(array_map('trim', explode(',', env('DEORIS_TRUSTED_MODULES', 'Portal,EnrollEase,AssessPay,MediTrack,ClearCheck,GradeTrack,LibrarySys')))),

    'module_secrets' => [
        'Portal' => env('DEORIS_SECRET_PORTAL'),
        'EnrollEase' => env('DEORIS_SECRET_ENROLLEASE'),
        'AssessPay' => env('DEORIS_SECRET_ASSESSPAY'),
        'MediTrack' => env('DEORIS_SECRET_MEDITRACK'),
        'ClearCheck' => env('DEORIS_SECRET_CLEARCHECK'),
        'GradeTrack' => env('DEORIS_SECRET_GRADETRACK'),
        'LibrarySys' => env('DEORIS_SECRET_LIBRARYSYS'),
        'EntryEase' => env('DEORIS_SIGNING_SECRET', env('APP_KEY')),
    ],

    'inbound_events' => [
        'TuitionPaid',
        'MedicalApproved',
        'StudentEnrolled',
    ],

    'publish_events' => [
        'ApplicationSubmitted',
        'ApplicationStatusChanged',
        'AdmissionApproved',
        'AdmissionRejected',
        'ExamAssigned',
        'ExamCompleted',
        'ExamScoreReleased',
    ],

    /*
    |--------------------------------------------------------------------------
    | DEORIS Portal event hub (powers the main portal notification bell)
    |--------------------------------------------------------------------------
    */
    'portal' => [
        'url' => env('DEORIS_PORTAL_URL', env('APP_PORTAL_URL', 'https://deoris.onrender.com')),
        'event_secret' => env('ENTRYEASE_EVENT_SECRET', env('DEORIS_PORTAL_EVENT_SECRET')),
        'verify_ssl' => (bool) env('DEORIS_VERIFY_SSL', true),
        'redis_channel' => env('DEORIS_PORTAL_REDIS_CHANNEL', 'deoris.events'),
        'queue' => env('DEORIS_PORTAL_QUEUE', 'deoris-events'),
        'publish_enabled' => (bool) env('DEORIS_PORTAL_PUBLISH_ENABLED', true),
        // HTTP ingest is the canonical path. Redis publish + deoris:events:listen duplicates notifications unless enabled deliberately.
        'publish_redis' => (bool) env('DEORIS_PORTAL_PUBLISH_REDIS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Portal federated search (Bearer token must match DEORIS ENTRYEASE_SEARCH_TOKEN)
    |--------------------------------------------------------------------------
    */
    'search_token' => env('ENTRYEASE_SEARCH_TOKEN'),

    'redis_pubsub_connection' => env('DEORIS_REDIS_PUBSUB_CONNECTION', 'pubsub'),

    /*
    |--------------------------------------------------------------------------
    | Optional EntryEase-local Echo/Reverb (standalone mode only)
    |--------------------------------------------------------------------------
    */
    'broadcast' => [
        'enabled' => (bool) env('DEORIS_BROADCAST_ENABLED', false),
    ],

];
