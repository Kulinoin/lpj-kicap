@php
    $statusLabel = $statusLabels[$record->status] ?? $record->status;
    $sourceLabel = $sourceLabels[$record->source_type] ?? $record->source_type;
    $claim = $record->advanceClaim;
    $claimLabel = $claim ? ($claimStatusLabels[$claim->status] ?? $claim->status) : null;
    $proofExt = strtolower(pathinfo((string) $record->proof_path, PATHINFO_EXTENSION));
    $isImage = in_array($proofExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    $backUrl = $backUrl ?? url('/admin/lpj-financial-transactions');
    $backLabel = $backLabel ?? 'Daftar transaksi';

    $statusTone = match ($record->status) {
        \App\Models\LpjFinancialTransaction::STATUS_VALID => 'good',
        \App\Models\LpjFinancialTransaction::STATUS_REJECTED => 'bad',
        \App\Models\LpjFinancialTransaction::STATUS_NEEDS_REVISION,
        \App\Models\LpjFinancialTransaction::STATUS_WAITING_PROOF => 'warn',
        default => 'info',
    };

    $money = fn ($value): string => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Transaksi Event</title>
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
            --bad: #ef4444;
            --warn: #f59e0b;
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

        .hero {
            border: 1px solid rgba(245, 158, 11, .28);
            background: linear-gradient(135deg, rgba(245,158,11,.16), rgba(24,24,27,.92) 42%);
            border-radius: 26px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 18px 60px rgba(0,0,0,.28);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.4fr .8fr .8fr;
            gap: 12px;
            align-items: stretch;
        }

        .hero-card,
        .card {
            border: 1px solid var(--line);
            background: rgba(255,255,255,.035);
            border-radius: 20px;
            padding: 15px;
            min-width: 0;
        }

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

        .amount {
            font-size: clamp(24px, 6vw, 42px);
            letter-spacing: -0.04em;
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
        .badge.bad { color: #fecaca; border-color: rgba(239,68,68,.35); }
        .badge.warn { color: #fde68a; border-color: rgba(245,158,11,.35); }
        .badge.info { color: #bae6fd; border-color: rgba(56,189,248,.35); }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .full { grid-column: 1 / -1; }

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

        .proof-box {
            display: grid;
            gap: 12px;
        }

        .proof-img {
            display: block;
            width: 100%;
            max-height: 620px;
            object-fit: contain;
            border-radius: 18px;
            background: #fff;
            border: 1px solid var(--line);
        }

        .file-name {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
            word-break: break-all;
        }

        .warn {
            color: #fca5a5;
            font-weight: 900;
        }

        .footer-actions {
            display: flex;
            justify-content: center;
            margin-top: 22px;
        }

        @media (max-width: 820px) {
            .top {
                flex-direction: column;
            }

            .actions {
                justify-content: flex-start;
            }

            .hero-grid,
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="top">
            <div>
                <div class="eyebrow">Detail Transaksi Event</div>
                <h1>{{ $record->category ?? 'Transaksi' }}</h1>
                <div class="subtitle">
                    {{ $record->lpj?->code ?? '-' }} · {{ $record->lpj?->title ?? '-' }}
                </div>
            </div>

            <div class="actions">
                <a class="btn" href="{{ $backUrl }}">← {{ $backLabel }}</a>
                @if ($proofUrl)
                    <a class="btn primary" href="{{ $proofUrl }}" target="_blank" rel="noreferrer">Buka Lampiran</a>
                @endif
            </div>
        </div>

        <section class="hero">
            <div class="hero-grid">
                <div class="hero-card">
                    <div class="label">Nominal</div>
                    <div class="value amount">{{ $money($record->amount) }}</div>
                </div>

                <div class="hero-card">
                    <div class="label">Status Review</div>
                    <div class="badge {{ $statusTone }}">{{ $statusLabel }}</div>
                </div>

                <div class="hero-card">
                    <div class="label">Sumber Dana</div>
                    <div class="value">{{ $sourceLabel }}</div>
                </div>
            </div>
        </section>

        <div class="section-title">
            <h2>Informasi Utama</h2>
        </div>

        <section class="grid">
            <div class="card">
                <div class="label">Event</div>
                <div class="value">{{ $record->lpj?->title ?? '-' }}</div>
                <div class="file-name">{{ $record->lpj?->code ?? '-' }}</div>
            </div>

            <div class="card">
                <div class="label">User Pencatat</div>
                <div class="value">{{ $record->user?->name ?? '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Tanggal Transaksi</div>
                <div class="value">{{ optional($record->spent_at)->format('d/m/Y') ?? '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Reviewer</div>
                <div class="value">{{ $record->reviewer?->name ?? '-' }}</div>
                @if ($record->reviewed_at)
                    <div class="file-name">{{ optional($record->reviewed_at)->format('d/m/Y H:i') }}</div>
                @endif
            </div>

            <div class="card full">
                <div class="label">Keterangan</div>
                <div class="text">{{ $record->description ?: '-' }}</div>
            </div>

            <div class="card full">
                <div class="label">Alasan Jika Tidak Ada Bukti</div>
                <div class="text">{{ $record->no_proof_reason ?: '-' }}</div>
            </div>
        </section>

        <div class="section-title">
            <h2>Bukti / Lampiran</h2>
            @if ($record->proof_path && $proofUrl)
                <span class="badge good">Ada lampiran</span>
            @elseif ($record->proof_path)
                <span class="badge warn">URL belum tersedia</span>
            @else
                <span class="badge info">Tanpa lampiran</span>
            @endif
        </div>

        <section class="card proof-box">
            @if ($record->proof_path && $proofUrl)
                <div>
                    <div class="value">Lampiran transaksi tersedia</div>
                    <div class="file-name">{{ basename((string) $record->proof_path) }}</div>
                </div>

                @if ($isImage)
                    <a href="{{ $proofUrl }}" target="_blank" rel="noreferrer">
                        <img class="proof-img" src="{{ $proofUrl }}" alt="Bukti transaksi">
                    </a>
                    <div class="file-name">Klik gambar untuk membuka ukuran penuh.</div>
                @else
                    <a class="btn primary" href="{{ $proofUrl }}" target="_blank" rel="noreferrer">Buka lampiran</a>
                @endif
            @elseif ($record->proof_path)
                <div class="warn">Lampiran tercatat, tetapi URL file belum tersedia.</div>
                <div class="file-name">{{ basename((string) $record->proof_path) }}</div>
                <div class="file-name">Periksa pengaturan Storage/R2 Public URL.</div>
            @else
                <div class="text">{{ $record->no_proof_reason ?: 'Tidak ada lampiran dan alasan belum diisi.' }}</div>
            @endif
        </section>

        @if ($record->admin_note)
            <div class="section-title">
                <h2>Catatan Admin</h2>
            </div>

            <section class="card">
                <div class="text">{{ $record->admin_note }}</div>
            </section>
        @endif

        @if ($claim)
            <div class="section-title">
                <h2>Klaim Dana Talangan</h2>
                <span class="badge info">{{ $claimLabel }}</span>
            </div>

            <section class="grid">
                <div class="card">
                    <div class="label">Nominal Klaim</div>
                    <div class="value">{{ $money($claim->amount) }}</div>
                </div>

                <div class="card">
                    <div class="label">Status Klaim</div>
                    <div class="value">{{ $claimLabel }}</div>
                </div>

                @if ($claim->admin_note)
                    <div class="card full">
                        <div class="label">Catatan Klaim</div>
                        <div class="text">{{ $claim->admin_note }}</div>
                    </div>
                @endif
            </section>
        @endif

        <div class="footer-actions">
            <a class="btn" href="{{ $backUrl }}">← {{ $backLabel }}</a>
        </div>
    </main>
</body>
</html>