@php
    $statusLabel = $statusLabels[$record->status] ?? $record->status;
    $sourceLabel = $sourceLabels[$record->source_type] ?? $record->source_type;
    $claim = $record->advanceClaim;
    $claimLabel = $claim ? ($claimStatusLabels[$claim->status] ?? $claim->status) : null;
    $proofExt = strtolower(pathinfo((string) $record->proof_path, PATHINFO_EXTENSION));
    $isImage = in_array($proofExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Transaksi Event</title>
    <style>
        body {
            margin: 0;
            background: #111;
            color: #f8fafc;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .page {
            max-width: 980px;
            margin: 0 auto;
            padding: 28px 18px 48px;
        }
        .top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 24px;
        }
        .back {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 10px 14px;
            background: #27272a;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
        }
        h1 {
            margin: 0;
            font-size: 28px;
        }
        .muted {
            color: #a1a1aa;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .card {
            border: 1px solid #333;
            background: #18181b;
            border-radius: 18px;
            padding: 16px;
        }
        .label {
            color: #fbbf24;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .value {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            word-break: break-word;
        }
        .full {
            grid-column: 1 / -1;
        }
        .text {
            white-space: pre-wrap;
            line-height: 1.55;
            font-weight: 500;
        }
        .proof-img {
            display: block;
            width: 100%;
            max-height: 560px;
            object-fit: contain;
            border-radius: 16px;
            background: #fff;
            border: 1px solid #333;
            margin-top: 14px;
        }
        .button {
            display: inline-flex;
            margin-top: 12px;
            border-radius: 999px;
            padding: 10px 16px;
            background: #f59e0b;
            color: #111;
            text-decoration: none;
            font-weight: 900;
        }
        .warn {
            color: #fca5a5;
            font-weight: 800;
        }
        @media (max-width: 720px) {
            .grid {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 22px;
            }
            .top {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="top">
            <div>
                <div class="muted">Transaksi Event</div>
                <h1>Detail Transaksi</h1>
            </div>
            <a class="back" href="{{ url('/admin/lpj-financial-transactions') }}">← Kembali</a>
        </div>

        <section class="grid">
            <div class="card">
                <div class="label">Event</div>
                <div class="value">{{ $record->lpj?->title ?? '-' }}</div>
                <div class="muted">{{ $record->lpj?->code ?? '-' }}</div>
            </div>

            <div class="card">
                <div class="label">User Pencatat</div>
                <div class="value">{{ $record->user?->name ?? '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Kategori</div>
                <div class="value">{{ $record->category ?? '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Nominal</div>
                <div class="value">Rp {{ number_format((float) $record->amount, 0, ',', '.') }}</div>
            </div>

            <div class="card">
                <div class="label">Tanggal</div>
                <div class="value">{{ optional($record->spent_at)->format('d/m/Y') ?? '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Sumber Dana</div>
                <div class="value">{{ $sourceLabel }}</div>
            </div>

            <div class="card">
                <div class="label">Status</div>
                <div class="value">{{ $statusLabel }}</div>
            </div>

            <div class="card">
                <div class="label">Reviewer</div>
                <div class="value">{{ $record->reviewer?->name ?? '-' }}</div>
            </div>

            <div class="card full">
                <div class="label">Keterangan</div>
                <div class="text">{{ $record->description ?: '-' }}</div>
            </div>

            <div class="card full">
                <div class="label">Alasan Jika Tidak Ada Bukti</div>
                <div class="text">{{ $record->no_proof_reason ?: '-' }}</div>
            </div>

            <div class="card full">
                <div class="label">Bukti / Lampiran</div>

                @if ($record->proof_path && $proofUrl)
                    <div class="value">Ada lampiran</div>
                    <div class="muted">{{ basename((string) $record->proof_path) }}</div>

                    @if ($isImage)
                        <a href="{{ $proofUrl }}" target="_blank" rel="noreferrer">
                            <img class="proof-img" src="{{ $proofUrl }}" alt="Bukti transaksi">
                        </a>
                        <a class="button" href="{{ $proofUrl }}" target="_blank" rel="noreferrer">Buka ukuran penuh</a>
                    @else
                        <a class="button" href="{{ $proofUrl }}" target="_blank" rel="noreferrer">Buka lampiran</a>
                    @endif
                @elseif ($record->proof_path)
                    <div class="value warn">Lampiran tercatat, tetapi URL belum tersedia.</div>
                    <div class="muted">{{ basename((string) $record->proof_path) }}</div>
                    <div class="muted">Periksa pengaturan Storage/R2 Public URL.</div>
                @else
                    <div class="text">{{ $record->no_proof_reason ?: 'Tidak ada lampiran dan alasan belum diisi.' }}</div>
                @endif
            </div>

            @if ($record->admin_note)
                <div class="card full">
                    <div class="label">Catatan Admin</div>
                    <div class="text">{{ $record->admin_note }}</div>
                </div>
            @endif

            @if ($claim)
                <div class="card full">
                    <div class="label">Klaim Dana Talangan</div>
                    <div class="value">Status: {{ $claimLabel }}</div>
                    <div class="value">Nominal: Rp {{ number_format((float) $claim->amount, 0, ',', '.') }}</div>
                    @if ($claim->admin_note)
                        <div class="text">{{ $claim->admin_note }}</div>
                    @endif
                </div>
            @endif
        </section>
    </main>
</body>
</html>