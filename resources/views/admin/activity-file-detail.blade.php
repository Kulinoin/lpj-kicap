@php
    $mime = (string) ($record->mime_type ?? '');
    $isImage = str_starts_with($mime, 'image/');
    $isPdf = str_contains($mime, 'pdf');
    $fileName = $record->original_name ?: basename((string) ($record->file_path ?? ''));
    $fileSize = $record->file_size ? number_format(((int) $record->file_size) / 1024, 1, ',', '.') . ' KB' : '-';
    $reportLabel = $record->include_in_report ? 'Masuk LPJ' : 'Internal';
    $reportTone = $record->include_in_report ? 'good' : 'info';
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subtitle }} — {{ $title }}</title>
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
            --warn: #f59e0b;
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
            width: min(1040px, 100%);
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

        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
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

        .btn.primary {
            border-color: var(--accent);
            background: var(--accent);
            color: #111;
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

        .muted {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
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
        .badge.warn { color: #fde68a; border-color: rgba(245,158,11,.35); }

        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 18px 0 10px;
        }

        .section-title h2 {
            margin: 0;
            font-size: 18px;
        }

        .text {
            white-space: pre-wrap;
            line-height: 1.55;
            font-weight: 550;
        }

        .preview {
            display: grid;
            gap: 12px;
        }

        .preview-img {
            width: 100%;
            max-height: 680px;
            object-fit: contain;
            display: block;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .filebox {
            border: 1px dashed var(--line);
            border-radius: 18px;
            padding: 20px;
            background: rgba(255,255,255,.025);
        }

        .footer-actions {
            display: flex;
            justify-content: center;
            margin-top: 22px;
        }

        @media (max-width: 820px) {
            .top { flex-direction: column; }
            .actions { justify-content: flex-start; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main class="page">
    <div class="top">
        <div>
            <div class="eyebrow">{{ $subtitle }}</div>
            <h1>{{ $title }}</h1>
            <div class="subtitle">
                {{ $record->lpj?->code ?? '-' }} · {{ $record->lpj?->title ?? '-' }}
            </div>
        </div>

        <div class="actions">
            <a class="btn" href="{{ $backUrl }}">← {{ $backLabel }}</a>
            @if ($fileUrl)
                <a class="btn primary" href="{{ $fileUrl }}" target="_blank" rel="noreferrer">Buka File</a>
            @endif
        </div>
    </div>

    <section class="grid">
        <div class="card">
            <div class="label">{{ $primaryLabel }}</div>
            <div class="value">{{ $primaryValue ?: '-' }}</div>
        </div>

        <div class="card">
            <div class="label">Status LPJ</div>
            <span class="badge {{ $reportTone }}">{{ $reportLabel }}</span>
        </div>

        <div class="card">
            <div class="label">Uploader</div>
            <div class="value">{{ $record->uploader?->name ?? '-' }}</div>
        </div>

        <div class="card">
            <div class="label">Waktu Upload</div>
            <div class="value">{{ optional($record->created_at)->format('d/m/Y H:i') ?? '-' }}</div>
        </div>

        <div class="card full">
            <div class="label">{{ $descriptionLabel }}</div>
            <div class="text">{{ $descriptionValue ?: '-' }}</div>
        </div>
    </section>

    <div class="section-title">
        <h2>File</h2>
        <span class="badge info">{{ $mime ?: 'File' }}</span>
    </div>

    <section class="card preview">
        <div>
            <div class="value">{{ $fileName ?: '-' }}</div>
            <div class="muted">Ukuran: {{ $fileSize }}</div>
        </div>

        @if ($fileUrl && $isImage)
            <a href="{{ $fileUrl }}" target="_blank" rel="noreferrer">
                <img src="{{ $fileUrl }}" alt="{{ $title }}" class="preview-img">
            </a>
            <div class="muted">Klik gambar untuk membuka ukuran penuh.</div>
        @elseif ($fileUrl && $isPdf)
            <div class="filebox">
                <div class="value">PDF tersedia</div>
                <div class="muted">Klik tombol Buka File untuk melihat dokumen.</div>
                <a class="btn primary" href="{{ $fileUrl }}" target="_blank" rel="noreferrer">Buka PDF</a>
            </div>
        @elseif ($fileUrl)
            <div class="filebox">
                <div class="value">File tersedia</div>
                <div class="muted">Preview tidak tersedia untuk tipe file ini. Gunakan tombol Buka File.</div>
                <a class="btn primary" href="{{ $fileUrl }}" target="_blank" rel="noreferrer">Buka File</a>
            </div>
        @else
            <div class="filebox">
                <div class="value">URL file belum tersedia</div>
                <div class="muted">Periksa pengaturan storage/public URL.</div>
            </div>
        @endif
    </section>

    <div class="footer-actions">
        <a class="btn" href="{{ $backUrl }}">← {{ $backLabel }}</a>
    </div>
</main>
</body>
</html>