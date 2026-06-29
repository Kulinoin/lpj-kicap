@php
    $title = $lpj->title ?: 'Event';
    $code = $lpj->code ?: '-';
    $status = (string) ($lpj->status ?: '-');
    $statusKey = strtolower(trim($status));

    $fmtDate = function ($value): string {
        if (! $value) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->timezone(config('app.timezone'))->format('d M Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $fmtDateTime = function ($value): string {
        if (! $value) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->timezone(config('app.timezone'))->format('d M Y H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $fmtMoney = fn ($value): string => 'Rp' . number_format((float) ($value ?: 0), 0, ',', '.');

    $statusLabel = match ($statusKey) {
        'draft' => 'Draft',
        'aktif', 'dalam_pengisian', 'in_progress' => 'Aktif',
        'diajukan', 'submitted' => 'Diajukan',
        'perlu_revisi', 'revision' => 'Perlu Revisi',
        'disetujui', 'approved' => 'Disetujui',
        'final', 'selesai' => 'Selesai',
        'diarsipkan', 'archived' => 'Diarsipkan',
        default => $status !== '-' ? \Illuminate\Support\Str::headline(str_replace(['_', '-'], ' ', $status)) : '-',
    };

    $statusClass = match ($statusKey) {
        'aktif', 'dalam_pengisian', 'in_progress' => 'badge-blue',
        'diajukan', 'submitted' => 'badge-yellow',
        'perlu_revisi', 'revision' => 'badge-red',
        'final', 'selesai' => 'badge-green',
        default => 'badge-gray',
    };

    $editUrl = \App\Filament\Resources\Lpjs\LpjResource::getUrl('edit', ['record' => $lpj]);
    $menuGroups = [
        [
            'title' => null,
            'items' => [
                ['label' => 'Dashboard', 'url' => url('/admin'), 'icon' => 'home'],
                ['label' => 'Semua Event', 'url' => url('/admin/lpjs'), 'icon' => 'event', 'active' => true],
                ['label' => 'Tipe Event', 'url' => url('/admin/lpj-types'), 'icon' => 'box'],
                ['label' => 'Users', 'url' => url('/admin/users'), 'icon' => 'users'],
            ],
        ],
        [
            'title' => 'Operasional Seleksi',
            'items' => [
                ['label' => 'Tahapan Seleksi', 'url' => url('/admin/activity-selection-stages'), 'icon' => 'list'],
                ['label' => 'Item Tes Seleksi', 'url' => url('/admin/activity-selection-tests'), 'icon' => 'clipboard'],
                ['label' => 'Hasil Tes Peserta', 'url' => url('/admin/activity-participant-test-results'), 'icon' => 'check'],
            ],
        ],
        [
            'title' => 'Data Pelaksanaan',
            'items' => [
                ['label' => 'Catatan Event', 'url' => url('/admin/activity-notes'), 'icon' => 'note'],
                ['label' => 'Dokumentasi Event', 'url' => url('/admin/activity-documentations'), 'icon' => 'image'],
                ['label' => 'Peserta', 'url' => url('/admin/activity-participants'), 'icon' => 'user'],
                ['label' => 'Rundown', 'url' => url('/admin/activity-schedules'), 'icon' => 'calendar'],
            ],
        ],
    ];

    $navIcon = function (string $icon): string {
        $attrs = 'class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

        return match ($icon) {
            'home' => '<svg '.$attrs.'><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9.5 20v-6h5v6"/></svg>',
            'event' => '<svg '.$attrs.'><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/><path d="M8 14h3M8 17h6"/></svg>',
            'box' => '<svg '.$attrs.'><path d="M4 8.5 12 4l8 4.5-8 4.5L4 8.5Z"/><path d="M4 8.5V16l8 4 8-4V8.5"/><path d="M12 13v7"/></svg>',
            'users' => '<svg '.$attrs.'><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'list' => '<svg '.$attrs.'><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>',
            'clipboard' => '<svg '.$attrs.'><rect x="6" y="5" width="12" height="16" rx="2"/><path d="M9 5a3 3 0 0 1 6 0"/><path d="M9 11h6M9 15h6"/></svg>',
            'check' => '<svg '.$attrs.'><path d="M20 7 10 17l-5-5"/><circle cx="12" cy="12" r="9"/></svg>',
            'note' => '<svg '.$attrs.'><path d="M6 3h9l3 3v15H6z"/><path d="M15 3v4h4"/><path d="M9 12h6M9 16h6"/></svg>',
            'image' => '<svg '.$attrs.'><rect x="4" y="5" width="16" height="14" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="m4 17 5-5 4 4 2-2 5 5"/></svg>',
            'user' => '<svg '.$attrs.'><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
            'calendar' => '<svg '.$attrs.'><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01"/></svg>',
            default => '<svg '.$attrs.'><rect x="5" y="5" width="14" height="14" rx="3"/></svg>',
        };
    };

    // KICAP_EVENT_DETAIL_02_QUICK_LINKS
    $quickLinks = [
        [
            'label' => 'Peserta',
            'hint' => ($stats['participants'] ?? 0) . ' data peserta',
            'url' => url('/admin/activity-participants'),
            'icon' => '👥',
        ],
        [
            'label' => 'Rundown',
            'hint' => ($stats['schedules'] ?? 0) . ' item jadwal',
            'url' => url('/admin/activity-schedules'),
            'icon' => '🗓️',
        ],
        [
            'label' => 'Dokumentasi',
            'hint' => ($stats['documentations'] ?? 0) . ' foto/file',
            'url' => url('/admin/activity-documentations'),
            'icon' => '🖼️',
        ],
        [
            'label' => 'Catatan',
            'hint' => ($stats['notes'] ?? 0) . ' catatan event',
            'url' => url('/admin/activity-notes'),
            'icon' => '📝',
        ],
        [
            'label' => 'Transaksi',
            'hint' => 'Lihat/review transaksi',
            'url' => url('/admin/lpj-financial-transactions'),
            'icon' => '💳',
        ],
    ];
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview Event - {{ $title }}</title>

    <style>
        :root {
            color-scheme: dark;
            --bg: #09090b;
            --sidebar: #0b0b0f;
            --panel: #141419;
            --panel-2: #18181d;
            --text: #f8fafc;
            --muted: #94a3b8;
            --border: rgba(148, 163, 184, .16);
            --border-strong: rgba(148, 163, 184, .24);
            --blue: #60a5fa;
            --orange: #f59e0b;
            --green: #22c55e;
            --red: #f87171;
            --shadow: 0 24px 60px rgba(0, 0, 0, .34);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .admin-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            padding: 22px 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 8px 10px 24px;
            font-size: 20px;
            font-weight: 850;
            letter-spacing: -.035em;
        }

        .brand-dot {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(245, 158, 11, .14);
            color: var(--orange);
            border: 1px solid rgba(245, 158, 11, .28);
            font-size: 17px;
        }

        .nav {
            display: grid;
            gap: 7px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 12px;
            border-radius: 14px;
            color: #d4d4d8;
            font-weight: 730;
            border: 1px solid transparent;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, .045);
            border-color: rgba(255, 255, 255, .06);
        }

        .nav a.active {
            background: rgba(245, 158, 11, .10);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, .18);
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            opacity: .9;
        }


        /* Sidebar polish: closer to Admin/Filament navigation */
        .sidebar {
            padding: 20px 14px;
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, .08), transparent 18rem),
                #09090b;
        }

        .brand {
            min-height: 58px;
            padding: 4px 6px 20px;
            margin-bottom: 6px;
            border-bottom: 1px solid rgba(148, 163, 184, .10);
        }

        .brand-dot {
            width: 38px;
            height: 38px;
            font-size: 15px;
            font-weight: 950;
            background: linear-gradient(135deg, rgba(245, 158, 11, .20), rgba(245, 158, 11, .08));
            color: #fbbf24;
            border-color: rgba(245, 158, 11, .34);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.035);
        }

        .nav {
            gap: 5px;
        }

        .nav-group-title {
            margin: 18px 10px 8px;
            color: #8b8b98;
            font-size: 12px;
            line-height: 1;
            font-weight: 760;
        }

        .nav a {
            min-height: 46px;
            padding: 10px 12px;
            color: #d7d7df;
            border-radius: 12px;
            font-size: 15px;
            letter-spacing: -.01em;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, .055);
            border-color: rgba(255, 255, 255, .075);
        }

        .nav a.active {
            background: rgba(245, 158, 11, .16);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, .30);
            box-shadow: inset 0 0 0 1px rgba(245, 158, 11, .08);
        }

        .nav-icon {
            width: 22px;
            height: 22px;
            display: inline-grid;
            place-items: center;
            color: #a1a1aa;
            flex: 0 0 22px;
        }

        .nav a.active .nav-icon {
            color: #fbbf24;
        }

        .nav-svg {
            width: 20px;
            height: 20px;
            display: block;
        }

        .main {
            min-width: 0;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .10), transparent 30rem),
                linear-gradient(180deg, #111118 0%, #09090b 48%);
        }

        .topbar {
            min-height: 72px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 18px 28px;
            border-bottom: 1px solid var(--border);
            background: rgba(15, 15, 18, .76);
            backdrop-filter: blur(14px);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .crumb {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--muted);
            font-weight: 700;
        }

        .top-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 38px;
            padding: 9px 14px;
            border: 1px solid var(--border-strong);
            border-radius: 999px;
            background: rgba(255, 255, 255, .045);
            color: var(--text);
            font-size: 14px;
            font-weight: 800;
        }

        .button.primary {
            border-color: rgba(245, 158, 11, .30);
            background: #ea580c;
            color: white;
        }

        .page {
            max-width: 1240px;
            margin: 0 auto;
            padding: 26px 28px 40px;
        }

        .hero, .card {
            background: rgba(20, 20, 25, .86);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .hero {
            border-radius: 24px;
            padding: 24px;
            display: grid;
            gap: 18px;
            background:
                radial-gradient(circle at top right, rgba(96, 165, 250, .16), transparent 24rem),
                linear-gradient(135deg, rgba(20, 20, 25, .96), rgba(24, 24, 29, .92));
        }

        .eyebrow {
            color: var(--muted);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 900;
        }

        h1 {
            margin: 6px 0 0;
            font-size: clamp(32px, 4vw, 52px);
            line-height: 1.02;
            letter-spacing: -.055em;
        }

        .subtitle {
            margin-top: 10px;
            color: var(--muted);
            font-size: 15px;
        }

        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .badge {
            display: inline-flex;
            padding: 6px 11px;
            border-radius: 999px;
            border: 1px solid;
            font-size: 12px;
            font-weight: 850;
        }

        .badge-blue { background: rgba(37, 99, 235, .16); color: #93c5fd; border-color: rgba(96, 165, 250, .28); }
        .badge-yellow { background: rgba(245, 158, 11, .14); color: #fbbf24; border-color: rgba(245, 158, 11, .28); }
        .badge-red { background: rgba(239, 68, 68, .14); color: #fca5a5; border-color: rgba(248, 113, 113, .28); }
        .badge-green { background: rgba(22, 163, 74, .14); color: #86efac; border-color: rgba(34, 197, 94, .28); }
        .badge-gray { background: rgba(100, 116, 139, .14); color: #cbd5e1; border-color: rgba(148, 163, 184, .25); }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .stat {
            padding: 16px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, .035);
        }

        .stat-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 850;
        }

        .stat-value {
            margin-top: 6px;
            font-size: 22px;
            line-height: 1.1;
            font-weight: 920;
            letter-spacing: -.04em;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr);
            gap: 16px;
            margin-top: 16px;
        }

        .card {
            border-radius: 20px;
            padding: 18px;
        }

        .card-title {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 900;
        }

        .rows {
            display: grid;
            gap: 11px;
        }

        .row {
            display: grid;
            grid-template-columns: 160px minmax(0, 1fr);
            gap: 14px;
            padding-bottom: 11px;
            border-bottom: 1px solid rgba(148, 163, 184, .14);
        }

        .row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .label {
            color: var(--muted);
            font-size: 14px;
            font-weight: 760;
        }

        .value {
            font-size: 14px;
            font-weight: 780;
            word-break: break-word;
        }

        .people {
            display: grid;
            gap: 10px;
        }

        .person {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, .14);
            background: rgba(255, 255, 255, .035);
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
        }

        .note {
            padding: 12px;
            border-radius: 16px;
            background: rgba(255, 255, 255, .035);
            border: 1px solid rgba(148, 163, 184, .14);
        }


        /* KICAP_EVENT_DETAIL_02_QUICK_LINKS */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .quick-card {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 86px;
            padding: 14px;
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, .15);
            background: rgba(20, 20, 25, .78);
            box-shadow: 0 18px 44px rgba(0, 0, 0, .18);
            transition: transform .14s ease, border-color .14s ease, background .14s ease;
        }

        .quick-card:hover {
            transform: translateY(-1px);
            border-color: rgba(245, 158, 11, .32);
            background: rgba(245, 158, 11, .055);
        }

        .quick-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: rgba(245, 158, 11, .12);
            border: 1px solid rgba(245, 158, 11, .18);
            font-size: 19px;
            flex: 0 0 42px;
        }

        .quick-label {
            display: block;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: -.015em;
        }

        .quick-hint {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .section-helper {
            margin: -4px 0 14px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .person-main {
            min-width: 0;
        }

        .person-role {
            display: flex;
            align-items: flex-start;
            justify-content: flex-end;
            flex: 0 0 auto;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 999px;
            background: rgba(59, 130, 246, .12);
            border: 1px solid rgba(96, 165, 250, .22);
            color: #93c5fd;
            font-size: 12px;
            font-weight: 850;
            white-space: nowrap;
        }


        /* KICAP_EVENT_DETAIL_03_ASSIGNED_COMMITTEE */
        .source-note {
            margin: -4px 0 14px;
            padding: 11px 12px;
            border-radius: 14px;
            border: 1px solid rgba(96, 165, 250, .20);
            background: rgba(59, 130, 246, .08);
            color: #cbd5e1;
            font-size: 13px;
            line-height: 1.45;
        }

        .source-note strong {
            color: #93c5fd;
        }

        .mobile-brand {
            display: none;
        }

        @media (max-width: 1050px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

    
        /* KICAP_EVENT_DETAIL_02_QUICK_LINKS */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .quick-card {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 86px;
            padding: 14px;
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, .15);
            background: rgba(20, 20, 25, .78);
            box-shadow: 0 18px 44px rgba(0, 0, 0, .18);
            transition: transform .14s ease, border-color .14s ease, background .14s ease;
        }

        .quick-card:hover {
            transform: translateY(-1px);
            border-color: rgba(245, 158, 11, .32);
            background: rgba(245, 158, 11, .055);
        }

        .quick-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: rgba(245, 158, 11, .12);
            border: 1px solid rgba(245, 158, 11, .18);
            font-size: 19px;
            flex: 0 0 42px;
        }

        .quick-label {
            display: block;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: -.015em;
        }

        .quick-hint {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .section-helper {
            margin: -4px 0 14px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .person-main {
            min-width: 0;
        }

        .person-role {
            display: flex;
            align-items: flex-start;
            justify-content: flex-end;
            flex: 0 0 auto;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 999px;
            background: rgba(59, 130, 246, .12);
            border: 1px solid rgba(96, 165, 250, .22);
            color: #93c5fd;
            font-size: 12px;
            font-weight: 850;
            white-space: nowrap;
        }


        /* KICAP_EVENT_DETAIL_03_ASSIGNED_COMMITTEE */
        .source-note {
            margin: -4px 0 14px;
            padding: 11px 12px;
            border-radius: 14px;
            border: 1px solid rgba(96, 165, 250, .20);
            background: rgba(59, 130, 246, .08);
            color: #cbd5e1;
            font-size: 13px;
            line-height: 1.45;
        }

        .source-note strong {
            color: #93c5fd;
        }

        .mobile-brand {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 900;
            }
        }

        @media (max-width: 900px) {
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }

            .quick-links {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 620px) {
            .topbar {
                align-items: stretch;
                flex-direction: column;
                padding: 16px;
            }

            .page {
                padding: 16px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .quick-links {
                grid-template-columns: 1fr;
            }

            .row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .person {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="admin-shell">
        <aside class="sidebar">
            <div class="brand">
                <span class="brand-dot">K</span>
                <span>Kicap Event</span>
            </div>

            <nav class="nav">
                @foreach ($menuGroups as $group)
                    @if (!empty($group['title']))
                        <div class="nav-group-title">{{ $group['title'] }}</div>
                    @endif

                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['url'] }}" class="{{ !empty($item['active']) ? 'active' : '' }}">
                            <span class="nav-icon">{!! $navIcon($item['icon'] ?? 'box') !!}</span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            </nav>
        </aside>

        <section class="main">
            <header class="topbar">
                <div class="crumb">
                    <span class="mobile-brand">Kicap Event</span>
                    <span>Semua Event</span>
                    <span>›</span>
                    <span>Preview</span>
                </div>

                <div class="top-actions">
                    <a class="button" href="{{ url('/admin/lpjs') }}">← Kembali</a>
                    <a class="button primary" href="{{ $editUrl }}">Edit Event</a>
                </div>
            </header>

            <main class="page">
                <section class="hero">
                    <div>
                        <div class="eyebrow">Event {{ $code !== '-' ? '#'.$code : '' }}</div>
                        <h1>{{ $title }}</h1>
                        <div class="subtitle">
                            {{ $typeName }} · {{ $fmtDate($lpj->start_date) }}{{ $lpj->end_date ? ' - '.$fmtDate($lpj->end_date) : '' }} · {{ $lpj->location ?: '-' }}
                        </div>
                    </div>

                    <div class="badges">
                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        @if ($typeName !== '-')
                            <span class="badge badge-blue">{{ $typeName }}</span>
                        @endif
                    </div>

                    <div class="stats">
                        <div class="stat">
                            <div class="stat-label">Dana Masuk</div>
                            <div class="stat-value">{{ $fmtMoney($stats['funds_received'] ?? 0) }}</div>
                        </div>

                        <div class="stat">
                            <div class="stat-label">Pengeluaran Valid</div>
                            <div class="stat-value">{{ $fmtMoney($stats['valid_expense'] ?? 0) }}</div>
                        </div>

                        <div class="stat">
                            <div class="stat-label">Sisa Dana</div>
                            <div class="stat-value">{{ $fmtMoney($stats['remaining_fund'] ?? 0) }}</div>
                        </div>

                        <div class="stat">
                            <div class="stat-label">Peserta</div>
                            <div class="stat-value">{{ $stats['participants'] ?? 0 }}</div>
                        </div>
                    </div>
                </section>

                <section class="quick-links" aria-label="Akses cepat data event">
                    @foreach ($quickLinks as $link)
                        <a class="quick-card" href="{{ $link['url'] }}">
                            <span class="quick-icon">{{ $link['icon'] }}</span>
                            <span>
                                <span class="quick-label">{{ $link['label'] }}</span>
                                <span class="quick-hint">{{ $link['hint'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </section>

                <div class="grid">
                    <section class="card">
                        <h2 class="card-title">Informasi Event</h2>
                        <div class="rows">
                            <div class="row"><div class="label">Judul Event</div><div class="value">{{ $title }}</div></div>
                            <div class="row"><div class="label">Kode</div><div class="value">{{ $code }}</div></div>
                            <div class="row"><div class="label">Status</div><div class="value">{{ $statusLabel }}</div></div>
                            <div class="row"><div class="label">Periode</div><div class="value">{{ $lpj->period_label ?: '-' }}</div></div>
                            <div class="row"><div class="label">Tanggal</div><div class="value">{{ $fmtDate($lpj->start_date) }}{{ $lpj->end_date ? ' - '.$fmtDate($lpj->end_date) : '' }}</div></div>
                            <div class="row"><div class="label">Lokasi</div><div class="value">{{ $lpj->location ?: '-' }}</div></div>
                            <div class="row"><div class="label">Sumber Dana</div><div class="value">{{ $lpj->funding_source ?: '-' }}</div></div>
                            <div class="row"><div class="label">Penanggung Jawab</div><div class="value">{{ $picName }}</div></div>
                        </div>
                    </section>

                    <section class="card">
                        <h2 class="card-title">Ringkasan Operasional</h2>
                        <div class="rows">
                            <div class="row"><div class="label">Panitia/Pendamping</div><div class="value">{{ $stats['assigned_users'] ?? 0 }}</div></div>
                            <div class="row"><div class="label">Rundown</div><div class="value">{{ $stats['schedules'] ?? 0 }} item</div></div>
                            <div class="row"><div class="label">Dokumentasi</div><div class="value">{{ $stats['documentations'] ?? 0 }} foto/file</div></div>
                            <div class="row"><div class="label">Catatan Event</div><div class="value">{{ $stats['notes'] ?? 0 }} catatan</div></div>
                            <div class="row"><div class="label">Update Terakhir</div><div class="value">{{ $fmtDateTime($lpj->updated_at) }}</div></div>
                        </div>
                    </section>
                </div>

                <div class="grid">
                    <section class="card" id="panitia-pendamping">
                        <h2 class="card-title">Panitia / Pendamping</h2>
                        <p class="section-helper">
                            Panitia/pendamping internal diambil dari <strong>user yang di-assign</strong> ke event. Peran di event mengikuti kolom assignment/role label, sehingga akses, input, dan audit tetap jelas.
                        </p>
                        <div class="source-note">
                            <strong>Sumber data:</strong> daftar ini berasal dari petugas assigned event, bukan input panitia manual. Jika perlu mengubah panitia/pendamping, ubah penugasan user pada event.
                        </div>

                        @if ($assignedUsers->isNotEmpty())
                            <div class="people">
                                @foreach ($assignedUsers as $user)
                                    @php
                                        $rawRole = strtolower(trim((string) ($user->role ?? 'user')));
                                        $roleLabel = match ($rawRole) {
                                            'admin' => 'Admin',
                                            'director', 'direktur' => 'Direktur',
                                            default => 'Petugas',
                                        };
                                    @endphp

                                    <div class="person">
                                        <div class="person-main">
                                            <div class="value">{{ $user->name ?: '-' }}</div>
                                            <div class="muted">{{ $user->username ? '@'.$user->username : ($user->email ?: '-') }}</div>
                                        </div>
                                        <div class="person-role">
                                            <span class="role-badge">{{ $user->role_label ?: $roleLabel }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="muted">Belum ada panitia/pendamping yang di-assign ke event ini.</div>
                        @endif
                    </section>

                    <section class="card">
                        <h2 class="card-title">Catatan Terbaru</h2>

                        @if ($latestNotes->isNotEmpty())
                            <div class="people">
                                @foreach ($latestNotes as $note)
                                    <div class="note">
                                        <div class="value">{{ \Illuminate\Support\Str::limit((string) $note->content, 120) }}</div>
                                        <div class="muted">
                                            {{ $note->type ? \Illuminate\Support\Str::headline(str_replace(['_', '-'], ' ', $note->type)) : 'Catatan' }}
                                            · {{ $note->user_name ?: 'Petugas' }}
                                            · {{ $fmtDateTime($note->created_at) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="muted">Belum ada catatan event.</div>
                        @endif
                    </section>
                </div>
            </main>
        </section>
    </div>
</body>
</html>
