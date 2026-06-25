@php
    $typeLabel = $typeLabels[$record->type] ?? $record->type;
    $reportLabel = $record->include_in_report ? 'Masuk LPJ' : 'Internal';
    $reportTone = $record->include_in_report ? 'good' : 'info';
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Catatan Event</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #101010;
            --panel: #18181b;
            --panel-soft: #202024;
            --line: #333338;
            --text: #f8fafc;
            --muted: #a1a1aa;
            --accent: #f59e0b;
            --good: #22c55e;
            --info: #38bdf8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, .16), transparent 34%),
                linear-gradient(180deg, #151515 0%, var(--bg) 58%);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; }

        .page {
            width: min(980px, 100%);
            margin: 0 auto;
            padding: 26px 18px 48px;
        }

        .top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }

        .eyebrow {
            color: var(--accent);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 5px 0 8px;
            font-size: clamp(24px, 5vw, 38px);
            line-height: 1.08;
        }

        .subtitle {
            color: var(--muted);
            line-height: 1.45;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            border-radius: 999px;
            border: 1px solid var(--line);
            padding: 10px 14px;
            background: var(--panel-soft);
            color: var(--text);
            text-decoration: none;
            font-size: 13px;
            font-weight: 850;
            white-space: nowrap;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .card {
            border: 1px solid var(--line);
            background: rgba(255,255,255,.035);
            border-radius: 20px;
            padding: 15px;
            min-width: 0;
        }

        .full { grid-column: 1 / -1; }

        .label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 850;
            margin-bottom: 6px;
        }

        .value {
            color: var(--text);
            font-size: 16px;
            font-weight: 900;
            word-break: break-word;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            border-radius: 999px;
            border: 1px solid var(--line);
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 900;
            background: rgba(255,255,255,.045);
        }

        .badge.good { color: #bbf7d0; border-color: rgba(34,197,94,.35); }
        .badge.info { color: #bae6fd; border-color: rgba(56,189,248,.35); }

        .section-title {
            margin: 18px 0 10px;
        }

        .section-title h2 {
            margin: 0;
            font-size: 18px;
        }

        .text {
            white-space: pre-wrap;
            line-height: 1.65;
            font-size: 15px;
            font-weight: 550;
        }

        .footer-actions {
            display: flex;
            justify-content: center;
            margin-top: 22px;
        }

        @media (max-width: 820px) {
            .top { flex-direction: column; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main class="page">
    <div class="top">
        <div>
            <div class="eyebrow">Detail Catatan Event</div>
            <h1>{{ $typeLabel }}</h1>
            <div class="subtitle">
                {{ $record->lpj?->code ?? '-' }} · {{ $record->lpj?->title ?? '-' }}
            </div>
        </div>

        <a class="btn" href="{{ $backUrl }}">← {{ $backLabel }}</a>
    </div>

    <section class="grid">
        <div class="card">
            <div class="label">Jenis Catatan</div>
            <div class="value">{{ $typeLabel }}</div>
        </div>

        <div class="card">
            <div class="label">Status LPJ</div>
            <span class="badge {{ $reportTone }}">{{ $reportLabel }}</span>
        </div>

        <div class="card">
            <div class="label">Event</div>
            <div class="value">{{ $record->lpj?->title ?? '-' }}</div>
            <div class="subtitle">{{ $record->lpj?->code ?? '-' }}</div>
        </div>

        <div class="card">
            <div class="label">User</div>
            <div class="value">{{ $record->user?->name ?? '-' }}</div>
        </div>

        <div class="card">
            <div class="label">Dibuat</div>
            <div class="value">{{ optional($record->created_at)->format('d/m/Y H:i') ?? '-' }}</div>
        </div>

        <div class="card">
            <div class="label">Diperbarui</div>
            <div class="value">{{ optional($record->updated_at)->format('d/m/Y H:i') ?? '-' }}</div>
        </div>
    </section>

    <div class="section-title">
        <h2>Isi Catatan</h2>
    </div>

    <section class="card">
        <div class="text"><!-- KICAP_ACTIVITY_NOTE_EMPTY_SAFE -->{{ filled($record->content) ? $record->content : 'Belum ada isi catatan.' }}</div>
    </section>

    <div class="footer-actions">
        <a class="btn" href="{{ $backUrl }}">← {{ $backLabel }}</a>
    </div>
</main>
</body>
</html>