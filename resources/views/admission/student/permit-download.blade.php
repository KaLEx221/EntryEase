<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Admission Permit | EntryEase</title>
    <style>
        :root {
            --primary: #7C3041;
            --primary-2: #9B3D50;
            --accent: #C9A84C;
            --text: #1E2530;
            --muted: #6B7A8D;
            --line: #E8ECF0;
            --panel: #FFFFFF;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #F4F6F9;
            color: var(--text);
            padding: 24px;
            line-height: 1.5;
        }

        .permit-wrap {
            max-width: 820px;
            margin: 0 auto;
        }

        .permit-sheet {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.08), 0 8px 24px rgba(0,0,0,.06);
        }

        .permit-brand {
            background: linear-gradient(135deg, #7C3041 0%, #9B3D50 100%);
            color: #fff;
            padding: 28px 32px 24px;
            border-bottom: 4px solid var(--accent);
        }

        .permit-brand-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
        }

        .permit-brand h1 {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .permit-brand p {
            font-size: .9rem;
            opacity: .92;
        }

        .permit-brand-meta {
            text-align: right;
            font-size: .8rem;
            opacity: .9;
        }

        .permit-brand-meta strong {
            display: block;
            font-size: 1rem;
            letter-spacing: .06em;
        }

        .permit-body { padding: 28px 32px 32px; }

        .permit-section { margin-bottom: 24px; }

        .permit-section-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--primary);
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--line);
        }

        .permit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .permit-field {
            background: #FAFBFC;
            border: 1px solid var(--line);
            border-left: 3px solid var(--primary);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .permit-field.full { grid-column: 1 / -1; }

        .permit-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .permit-value {
            font-size: .95rem;
            font-weight: 600;
            color: var(--text);
            word-break: break-word;
        }

        .permit-value.permit-pending {
            color: #92400e;
            font-style: italic;
            font-weight: 600;
        }

        .permit-assigned {
            background: #F0FDF4;
            border: 1px solid rgba(22, 163, 74, 0.25);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .85rem;
            color: #166534;
            margin-bottom: 16px;
        }

        .permit-highlight {
            background: #FFFBEB;
            border: 1px solid rgba(201,168,76,.45);
            border-radius: 8px;
            padding: 16px 18px;
        }

        .permit-highlight h3 {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #92400e;
            margin-bottom: 10px;
        }

        .permit-highlight ul {
            list-style: none;
            font-size: .85rem;
            color: #78350f;
        }

        .permit-highlight li {
            padding: 4px 0 4px 1.1rem;
            position: relative;
        }

        .permit-highlight li::before {
            content: "";
            position: absolute;
            left: 0;
            top: .65rem;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--accent);
        }

        .permit-checklist {
            background: #F9EEF1;
            border: 1px solid rgba(124,48,65,.15);
            border-radius: 8px;
            padding: 16px 18px;
        }

        .permit-checklist h3 {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .permit-checklist ul {
            list-style: none;
            font-size: .85rem;
            color: var(--text);
        }

        .permit-checklist li {
            padding: 3px 0 3px 1.25rem;
            position: relative;
        }

        .permit-checklist li::before {
            content: "\2713";
            position: absolute;
            left: 0;
            color: var(--primary);
            font-weight: 700;
        }

        .permit-footer {
            padding: 14px 32px;
            background: #FAFBFC;
            border-top: 1px solid var(--line);
            font-size: .78rem;
            color: var(--muted);
            text-align: center;
        }

        .permit-toolbar {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
        }

        .permit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            height: 38px;
            padding: 0 1.1rem;
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            border: 1.5px solid transparent;
            transition: background .15s, border-color .15s;
        }

        .permit-btn-primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .permit-btn-primary:hover { background: var(--primary-2); }

        .permit-btn-secondary {
            background: #fff;
            color: var(--text);
            border-color: var(--line);
        }

        .permit-btn-secondary:hover { background: #F0F3F6; }

        @media (max-width: 640px) {
            body { padding: 12px; }
            .permit-body { padding: 20px 16px; }
            .permit-brand { padding: 20px 16px; }
            .permit-grid { grid-template-columns: 1fr; }
            .permit-brand-meta { text-align: left; width: 100%; }
        }

        @media print {
            body { background: #fff; padding: 0; }
            .permit-wrap { max-width: none; }
            .permit-sheet { box-shadow: none; border-radius: 0; border: none; }
            .permit-toolbar { display: none !important; }
            .permit-footer { background: #fff; }
        }
    </style>
</head>
<body>
@php
    $permit = $permit ?? \App\Support\PermitPresenter::make($application, $schedule, $student);
@endphp
<div class="permit-wrap">
    <div class="permit-sheet">
        <header class="permit-brand">
            <div class="permit-brand-top">
                <div>
                    <h1>Exam Admission Permit</h1>
                    <p>EntryEase &mdash; Grade 7 Entrance Examination (On-Site)</p>
                </div>
                <div class="permit-brand-meta">
                    <strong>Permit #{{ $permit->permitNumber() }}</strong>
                    Issued {{ now()->format('F d, Y') }}
                </div>
            </div>
        </header>

        <main class="permit-body">
            <section class="permit-section">
                <h2 class="permit-section-title">Student Information</h2>
                <div class="permit-grid">
                    <div class="permit-field">
                        <div class="permit-label">Full Name</div>
                        <div class="permit-value">{{ $student->name ?? 'N/A' }}</div>
                    </div>
                    <div class="permit-field">
                        <div class="permit-label">Email</div>
                        <div class="permit-value">{{ $student->email ?? 'N/A' }}</div>
                    </div>
                    <div class="permit-field">
                        <div class="permit-label">Grade Level</div>
                        <div class="permit-value">{{ $permit->gradeLabel() }}</div>
                    </div>
                    <div class="permit-field">
                        <div class="permit-label">Application ID</div>
                        <div class="permit-value">{{ $application->id }}</div>
                    </div>
                </div>
            </section>

            <section class="permit-section">
                <h2 class="permit-section-title">Examination Details</h2>
                <div class="permit-grid">
                    <div class="permit-field">
                        <div class="permit-label">Exam Title</div>
                        <div class="permit-value">{{ $schedule->title ?? 'Grade 7 Admission Exam' }}</div>
                    </div>
                    <div class="permit-field">
                        <div class="permit-label">Exam Date</div>
                        <div class="permit-value">{{ $schedule->exam_date ? $schedule->exam_date->format('l, F d, Y') : 'To be announced' }}</div>
                    </div>
                    <div class="permit-field">
                        <div class="permit-label">Time</div>
                        <div class="permit-value">{{ $permit->timeRange() }}</div>
                    </div>
                    <div class="permit-field">
                        <div class="permit-label">Exam Type</div>
                        <div class="permit-value">On-Site Examination</div>
                    </div>
                </div>
            </section>

            <section class="permit-section">
                <h2 class="permit-section-title">Venue &amp; Seating</h2>
                @if($permit->hasSeating())
                    <p class="permit-assigned">Assigned: Room <strong>{{ $permit->room() }}</strong> &middot; Seat <strong>{{ $permit->seat() }}</strong></p>
                @else
                    <p class="permit-assigned" style="background:#FFFBEB;color:#92400e;border-color:rgba(217,119,6,.35)">Room and seat pending — your Admission Officer will assign these before exam day.</p>
                @endif
                <div class="permit-grid">
                    <div class="permit-field">
                        <div class="permit-label">Room</div>
                        <div class="permit-value">{{ $permit->roomDisplay() }}</div>
                    </div>
                    <div class="permit-field">
                        <div class="permit-label">Seat Number</div>
                        <div class="permit-value">{{ $permit->seatDisplay() }}</div>
                    </div>
                    @if($schedule->venue)
                    <div class="permit-field full">
                        <div class="permit-label">Venue</div>
                        <div class="permit-value">{{ $schedule->venue }}</div>
                    </div>
                    @endif
                </div>
            </section>

            @if($schedule->instructions)
            <section class="permit-section">
                <h2 class="permit-section-title">Additional Instructions</h2>
                <div class="permit-field full">
                    <div class="permit-value">{{ $schedule->instructions }}</div>
                </div>
            </section>
            @endif

            <section class="permit-section">
                <div class="permit-highlight">
                    <h3>Important Instructions</h3>
                    <ul>
                        <li>Arrive at least <strong>15 minutes</strong> before the scheduled start time.</li>
                        <li>Present this permit and a <strong>valid government-issued ID</strong>.</li>
                        <li>Mobile phones and unauthorized materials are not allowed in the examination room.</li>
                        <li>Follow all instructions from the examination proctors.</li>
                    </ul>
                </div>
            </section>

            <section class="permit-section">
                <div class="permit-checklist">
                    <h3>What to Bring</h3>
                    <ul>
                        <li>This exam admission permit (printed or digital)</li>
                        <li>Valid government-issued ID or school ID</li>
                        <li>Blue or black ink pens (at least two)</li>
                        <li>Pencils and eraser (if permitted)</li>
                    </ul>
                </div>
            </section>

            <div class="permit-toolbar">
                <button type="button" class="permit-btn permit-btn-primary" onclick="window.print()">Print / Save as PDF</button>
                <a href="{{ \App\Support\BackNavigation::resolve('student.exam.take') }}" class="permit-btn permit-btn-secondary" data-ee-instant-back>Back</a>
            </div>
        </main>

        <footer class="permit-footer">
            <p>This document is official proof of your on-site examination registration. Keep it safe and present it on examination day.</p>
            <p style="margin-top:6px;">EntryEase &mdash; Grade 7 Admission Module</p>
        </footer>
    </div>
</div>
<script src="{{ secure_asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
