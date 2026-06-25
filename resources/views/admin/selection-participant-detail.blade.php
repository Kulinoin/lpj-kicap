<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Peserta — {{ $participant->name }}</title>
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
            --green: #22c55e;
            --red: #ef4444;
            --blue: #38bdf8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, .15), transparent 34%),
                linear-gradient(180deg, #151515 0%, var(--bg) 58%);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; }

        .page {
            width: min(1120px, 100%);
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
            margin: 6px 0 8px;
            font-size: clamp(24px, 5vw, 40px);
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

        .hero {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr);
            gap: 16px;
            margin-bottom: 18px;
        }

        .photo {
            border: 1px solid var(--line);
            background: rgba(255,255,255,.04);
            border-radius: 24px;
            min-height: 220px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-weight: 850;
            text-align: center;
            padding: 12px;
        }

        .photo img {
            width: 100%;
            height: 100%;
            min-height: 220px;
            object-fit: cover;
            display: block;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
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
            font-size: 15px;
            font-weight: 900;
            word-break: break-word;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 900;
            background: rgba(255,255,255,.07);
            border: 1px solid var(--line);
        }

        .badge.pass { color: var(--green); border-color: rgba(34,197,94,.35); }
        .badge.fail { color: var(--red); border-color: rgba(239,68,68,.35); }
        .badge.note { color: var(--blue); border-color: rgba(56,189,248,.35); }

        .section-title {
            margin: 22px 0 10px;
        }

        .section-title h2 {
            margin: 0;
            font-size: 18px;
        }

        .stage {
            margin-bottom: 14px;
        }

        .stage-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid var(--line);
            border-bottom: 0;
            background: rgba(245,158,11,.08);
            padding: 13px 15px;
            border-radius: 18px 18px 0 0;
            font-weight: 950;
        }

        .tests {
            border: 1px solid var(--line);
            border-radius: 0 0 18px 18px;
            overflow: hidden;
        }

        .test-row {
            display: grid;
            grid-template-columns: 1.1fr .8fr .9fr 1.4fr .8fr;
            gap: 10px;
            padding: 13px 15px;
            background: rgba(255,255,255,.03);
            border-top: 1px solid rgba(255,255,255,.05);
            align-items: start;
        }

        .test-row:first-child { border-top: 0; }

        .small {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 4px;
            font-weight: 800;
        }

        .text {
            white-space: pre-wrap;
            line-height: 1.6;
            font-size: 14px;
            font-weight: 550;
        }

        @media (max-width: 900px) {
            .top { flex-direction: column; }
            .hero { grid-template-columns: 1fr; }
            .grid { grid-template-columns: 1fr; }
            .test-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main class="page">
    <div class="top">
        <div>
            <div class="eyebrow">Detail Peserta Seleksi</div>
            <h1>{{ $participant->name }}</h1>
            <div class="subtitle">
                {{ $participant->lpj?->code ?? '-' }} · {{ $participant->lpj?->title ?? '-' }}
            </div>
        </div>

        <a class="btn" href="{{ $backUrl }}">← {{ $backLabel }}</a>
    </div>

    <section class="hero">
        <div class="photo">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="Foto peserta {{ $participant->name }}">
            @else
                Belum ada foto peserta
            @endif
        </div>

        <div class="grid">
            <div class="card">
                <div class="label">Nomor Peserta</div>
                <div class="value">{{ $participant->participant_number ?: 'Belum registrasi' }}</div>
            </div>

            <div class="card">
                <div class="label">WhatsApp</div>
                <div class="value">{{ $participant->whatsapp ?: '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Asal</div>
                <div class="value">{{ $participant->origin ?: '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Status Registrasi</div>
                <div class="value">
                    {{ \App\Models\ActivityParticipant::registrationStatusLabels()[$participant->selection_registration_status] ?? ($participant->selection_registration_status ?: '-') }}
                </div>
            </div>

            <div class="card">
                <div class="label">Status Seleksi</div>
                <div class="value">
                    {{ \App\Models\ActivityParticipant::selectionStatusLabels()[$participant->selection_status] ?? ($participant->selection_status ?: '-') }}
                </div>
            </div>

            <div class="card">
                <div class="label">Registrasi Oleh</div>
                <div class="value">{{ $participant->registeredBy?->name ?: '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Tahap Gugur</div>
                <div class="value">{{ $participant->eliminatedStage?->name ?: '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Tes Gugur</div>
                <div class="value">{{ $participant->eliminatedTest?->name ?: '-' }}</div>
            </div>

            <div class="card">
                <div class="label">Waktu Registrasi</div>
                <div class="value">{{ $participant->registered_at?->format('d/m/Y H:i') ?: '-' }}</div>
            </div>

            <div class="card full">
                <div class="label">Catatan Peserta</div>
                <div class="text">{{ filled($participant->note) ? $participant->note : 'Belum ada catatan peserta.' }}</div>
            </div>
        </div>
    </section>

    <div class="section-title">
        <h2>Hasil Tes Peserta</h2>
    </div>

    @forelse ($stages as $stage)
        <section class="stage">
            <div class="stage-head">
                <span>{{ $stage->name }}</span>
                <span class="badge">{{ $stage->is_elimination ? 'Sistem Gugur' : 'Non Gugur' }}</span>
            </div>

            <div class="tests">
                @forelse ($stage->tests as $test)
                    @php
                        $result = $resultsByTestId->get($test->id);
                        $status = $result?->status ?? \App\Models\ActivityParticipantTestResult::STATUS_PENDING;
                        $statusLabel = \App\Models\ActivityParticipantTestResult::statusLabels()[$status] ?? $status;
                        $statusClass = match ($status) {
                            \App\Models\ActivityParticipantTestResult::STATUS_PASSED => 'pass',
                            \App\Models\ActivityParticipantTestResult::STATUS_PASSED_WITH_NOTE => 'note',
                            \App\Models\ActivityParticipantTestResult::STATUS_FAILED,
                            \App\Models\ActivityParticipantTestResult::STATUS_ABSENT => 'fail',
                            default => '',
                        };
                    @endphp

                    <div class="test-row">
                        <div>
                            <div class="small">Item Tes</div>
                            <div class="value">{{ $test->name }}</div>
                        </div>

                        <div>
                            <div class="small">Status</div>
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div>
                            <div class="small">{{ $test->result_label ?: 'Nilai/Hasil' }}</div>
                            <div class="value">{{ $result?->result_value ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="small">{{ $test->note_label ?: 'Keterangan' }}</div>
                            <div class="text">{{ filled($result?->note) ? $result->note : '-' }}</div>
                        </div>

                        <div>
                            <div class="small">Update</div>
                            <div class="value">{{ $result?->updater?->name ?: '-' }}</div>
                            <div class="small">{{ $result?->assessed_at?->format('d/m/Y H:i') ?: '' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="test-row">
                        <div class="text">Belum ada item tes pada tahap ini.</div>
                    </div>
                @endforelse
            </div>
        </section>
    @empty
        <section class="card">
            <div class="text">Belum ada tahapan seleksi untuk event ini.</div>
        </section>
    @endforelse
</main>
</body>
</html>