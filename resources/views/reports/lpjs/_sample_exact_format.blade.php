{{-- KICAP_EXACT_SAMPLE_FORMAT_V2 --}}
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    if (! function_exists('kicap_doc_value')) {
        function kicap_doc_value($row, $keys, $default = '') {
            foreach ((array) $keys as $key) {
                $value = data_get($row, $key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
            return $default;
        }
    }

    if (! function_exists('kicap_doc_collect')) {
        function kicap_doc_collect($value) {
            if ($value instanceof \Illuminate\Support\Collection) return $value;
            if ($value === null || $value === false) return collect();
            return collect($value);
        }
    }

    if (! function_exists('kicap_doc_rows')) {
        function kicap_doc_rows(array $tables, $lpjId, array $opts = []) {
            foreach ($tables as $table) {
                if (! $lpjId || ! Schema::hasTable($table) || ! Schema::hasColumn($table, 'lpj_id')) {
                    continue;
                }

                $query = DB::table($table)->where('lpj_id', $lpjId);

                if (($opts['valid'] ?? false) && Schema::hasColumn($table, 'status')) {
                    $query->where(function ($q) {
                        $q->whereIn('status', ['valid', 'approved', 'disetujui', 'final', 'posted'])
                          ->orWhereNull('status');
                    });
                }

                if (($opts['include_report'] ?? false)) {
                    foreach (['include_in_report', 'is_in_report', 'show_in_report', 'is_final'] as $col) {
                        if (Schema::hasColumn($table, $col)) {
                            $query->where(function ($q) use ($col) {
                                $q->where($col, 1)->orWhereNull($col);
                            });
                        }
                    }
                }

                foreach (($opts['order'] ?? []) as $col) {
                    if (Schema::hasColumn($table, $col)) {
                        $query->orderBy($col);
                    }
                }

                if (Schema::hasColumn($table, 'sort_order')) {
                    $query->orderBy('sort_order');
                }

                if (Schema::hasColumn($table, 'id')) {
                    $query->orderBy('id');
                }

                $rows = $query->get();
                if ($rows->isNotEmpty()) {
                    return $rows;
                }
            }

            return collect();
        }
    }

    if (! function_exists('kicap_doc_money_raw')) {
        function kicap_doc_money_raw($value) {
            if (is_object($value) || is_array($value)) {
                $value = kicap_doc_value($value, ['amount', 'nominal', 'total', 'total_amount'], 0);
            }

            if (is_numeric($value)) return (float) $value;

            $raw = preg_replace('/[^\d,\.\-]/', '', (string) $value);
            if (str_contains($raw, ',')) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            } else {
                $raw = str_replace('.', '', $raw);
            }

            return (float) $raw;
        }
    }

    if (! function_exists('kicap_doc_money')) {
        function kicap_doc_money($value) {
            return 'Rp' . number_format(kicap_doc_money_raw($value), 0, ',', '.');
        }
    }

    if (! function_exists('kicap_doc_date')) {
        function kicap_doc_date($date, $withDay = false) {
            if (! $date) return '-';

            try {
                $carbon = Carbon::parse($date)->locale('id');
                return $withDay
                    ? $carbon->translatedFormat('l, d M Y')
                    : $carbon->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                return (string) $date;
            }
        }
    }

    if (! function_exists('kicap_doc_user_name')) {
        function kicap_doc_user_name($id, $default = '-') {
            if (! $id || ! Schema::hasTable('users')) return $default;
            try {
                return DB::table('users')->where('id', $id)->value('name') ?: $default;
            } catch (\Throwable $e) {
                return $default;
            }
        }
    }

    if (! function_exists('kicap_doc_file_url')) {
        function kicap_doc_file_url($path) {
            if (! $path) {
                return null;
            }

            $raw = trim(str_replace('\\', '/', (string) $path));

            if ($raw === '') {
                return null;
            }

            if (Str::startsWith($raw, ['data:', 'http://', 'https://'])) {
                return $raw;
            }

            $clean = ltrim($raw, '/');

            foreach ([
                'storage/app/public/',
                'storage/app/private/',
                'storage/app/',
                'public/storage/',
                'storage/',
                'public/',
                'private/',
                'app/public/',
                'app/private/',
                'app/',
            ] as $prefix) {
                if (Str::startsWith($clean, $prefix)) {
                    $clean = substr($clean, strlen($prefix));
                }
            }

            $encoded = collect(explode('/', $clean))
                ->map(fn ($part) => rawurlencode($part))
                ->implode('/');

            return url('/lpj-report-media/' . $encoded);
        }
    }

    if (! function_exists('kicap_doc_category_name')) {
        function kicap_doc_category_name($row) {
            $direct = kicap_doc_value($row, ['category_name', 'category', 'kategori', 'type_label', 'category_label']);
            if ($direct) return $direct;

            $categoryId = kicap_doc_value($row, ['category_id', 'transaction_category_id']);

            foreach (['transaction_categories', 'lpj_transaction_categories'] as $table) {
                if ($categoryId && Schema::hasTable($table)) {
                    $name = DB::table($table)->where('id', $categoryId)->value('name');
                    if ($name) return $name;
                }
            }

            return '-';
        }
    }

    if (! function_exists('kicap_doc_source_label')) {
        function kicap_doc_source_label($row) {
            $mode = Str::lower((string) kicap_doc_value($row, ['funding_mode', 'source_mode', 'source_type']));

            if (str_contains($mode, 'talangan')) return 'Dana Talangan';
            if (str_contains($mode, 'saldo')) return 'Saldo Pegangan';

            return kicap_doc_value($row, ['source', 'sumber', 'funding_source', 'payment_source', 'method'], 'Saldo Pegangan');
        }
    }

    $routeLpj = request()->route('lpj') ?? request()->route('id') ?? null;

    if (! isset($lpj) || ! $lpj) {
        if (is_object($routeLpj)) {
            $lpj = $routeLpj;
        } elseif ($routeLpj && Schema::hasTable('lpjs')) {
            $lpj = DB::table('lpjs')->where('id', $routeLpj)->first();
        }
    }

    $lpjId = kicap_doc_value($lpj ?? null, 'id');

    $org = $organizationProfile ?? $organization ?? null;
    if (! $org && Schema::hasTable('organization_profiles')) {
        $org = DB::table('organization_profiles')->orderBy('id')->first();
    }

    $title = kicap_doc_value($lpj ?? null, ['title', 'name', 'event_name'], 'Judul Kegiatan');
    $code = kicap_doc_value($lpj ?? null, ['code', 'event_code'], '-');

    $typeLabel = kicap_doc_value($lpj ?? null, ['type_name', 'lpj_type_name', 'event_type_name', 'type_label'], '');
    $typeId = kicap_doc_value($lpj ?? null, ['lpj_type_id', 'event_type_id']);
    if (! $typeLabel && $typeId && Schema::hasTable('lpj_types')) {
        $typeLabel = DB::table('lpj_types')->where('id', $typeId)->value('name') ?: '';
    }
    $typeLabel = $typeLabel ?: kicap_doc_value($lpj ?? null, ['type', 'event_type'], 'Kegiatan');

    $startDate = kicap_doc_value($lpj ?? null, ['start_date', 'date_start', 'started_at']);
    $endDate = kicap_doc_value($lpj ?? null, ['end_date', 'date_end', 'finished_at']);
    $periodLabel = kicap_doc_value($lpj ?? null, ['period_label', 'period', 'periode']);

    if (! $periodLabel) {
        if ($startDate && $endDate) {
            $periodLabel = kicap_doc_date($startDate) . ' - ' . kicap_doc_date($endDate);
        } elseif ($startDate) {
            $periodLabel = kicap_doc_date($startDate);
        } else {
            $periodLabel = '-';
        }
    }

    $location = kicap_doc_value($lpj ?? null, ['location', 'place', 'event_location'], '-');
    $fundingSource = kicap_doc_value($lpj ?? null, ['funding_source', 'source_fund'], '-');
    $letterNumber = kicap_doc_value($lpj ?? null, ['assignment_letter_number', 'letter_number', 'nomor_surat'], '-');

    $picId = kicap_doc_value($lpj ?? null, ['person_in_charge_id', 'pic_id', 'responsible_user_id', 'created_by']);
    $picName = kicap_doc_value($lpj ?? null, ['person_in_charge_name', 'pic_name', 'responsible_name'], '');
    $picName = $picName ?: kicap_doc_user_name($picId, '-');

    $orgName = kicap_doc_value($org, ['institution_name', 'organization_name', 'name'], 'PT. KAZOKU INDONESIA CENTER');
    $orgType = kicap_doc_value($org, ['institution_type', 'type'], 'Lembaga Pelatihan Kerja');
    $orgAddress = kicap_doc_value($org, ['address'], '');
    $orgPhone = kicap_doc_value($org, ['phone'], '');
    $orgMobile = kicap_doc_value($org, ['mobile', 'hp', 'whatsapp'], '');
    $orgEmail = kicap_doc_value($org, ['email'], '');
    $orgWebsite = kicap_doc_value($org, ['website'], '');
    $orgCity = kicap_doc_value($org, ['default_city', 'city'], 'Kediri');
    $logoUrl = kicap_doc_file_url(kicap_doc_value($org, ['logo_path', 'logo', 'logo_url'], ''));

    $narrativeRows = kicap_doc_rows(['lpj_narratives', 'event_narratives'], $lpjId, ['order' => ['id']]);
    $narratives = [];
    foreach ($narrativeRows as $nar) {
        $section = kicap_doc_value($nar, ['section', 'key', 'name']);
        if ($section) {
            $narratives[$section] = kicap_doc_value($nar, ['content', 'body', 'text']);
        }
    }

    $participants = kicap_doc_collect($participants ?? $activityParticipants ?? $participantRows ?? null);
    if ($participants->isEmpty()) {
        $participants = kicap_doc_rows(['activity_participants', 'lpj_participants', 'event_participants'], $lpjId, ['order' => ['participant_number', 'name']]);
    }

    $committees = kicap_doc_collect($committees ?? $activityCommittees ?? $assignedUsers ?? $committeeRows ?? null);
    if ($committees->isEmpty()) {
        $committees = kicap_doc_rows(['activity_committees', 'lpj_committees', 'event_committees', 'lpj_assigned_users'], $lpjId, ['order' => ['id']]);
    }

    $schedules = kicap_doc_collect($schedules ?? $activitySchedules ?? $executionRows ?? null);
    if ($schedules->isEmpty()) {
        $schedules = kicap_doc_rows(['activity_schedules', 'lpj_schedules', 'event_schedules', 'lpj_execution_items'], $lpjId, ['order' => ['start_time', 'date', 'id']]);
    }

    $fundRows = kicap_doc_collect($fundReceipts ?? $funds ?? $fundRows ?? null);
    if ($fundRows->isEmpty()) {
        $fundRows = kicap_doc_rows(['lpj_fund_receipts', 'lpj_funds', 'event_funds'], $lpjId, ['order' => ['fund_date', 'date', 'id']]);
    }

    $transactions = kicap_doc_collect($validTransactions ?? $transactions ?? $transactionRows ?? null);
    if ($transactions->isEmpty()) {
        $transactions = kicap_doc_rows(['lpj_financial_transactions', 'lpj_transactions', 'financial_transactions'], $lpjId, [
            'valid' => true,
            'order' => ['transaction_date', 'date', 'id'],
        ]);
    }

    $documentations = kicap_doc_collect($documentations ?? $activityDocumentations ?? $documentationRows ?? null);
    if ($documentations->isEmpty()) {
        $documentations = kicap_doc_rows(['activity_documentations', 'lpj_documentations', 'event_documentations'], $lpjId, [
            'include_report' => true,
            'order' => ['sort_order', 'id'],
        ]);
    }

    $categorySummaries = kicap_doc_collect($categorySummaries ?? $expenseByCategories ?? $expenseByCategory ?? null);
    if ($categorySummaries->isEmpty() && $transactions->isNotEmpty()) {
        $categorySummaries = $transactions
            ->groupBy(fn ($item) => kicap_doc_category_name($item))
            ->map(fn ($items, $category) => (object) [
                'category' => $category,
                'total' => $items->sum(fn ($item) => kicap_doc_money_raw($item)),
            ])
            ->values();
    }

    $totalFundsStored = kicap_doc_value($lpj ?? null, ['total_funds_received', 'total_fund_received', 'total_funds'], null);
    $totalExpenseStored = kicap_doc_value($lpj ?? null, ['total_valid_expense', 'total_expense', 'total_valid_transactions'], null);
    $totalRemainingStored = kicap_doc_value($lpj ?? null, ['total_remaining_fund', 'remaining_fund', 'remaining_balance'], null);

    $totalFunds = $totalFundsStored !== null && $totalFundsStored !== ''
        ? kicap_doc_money_raw($totalFundsStored)
        : $fundRows->sum(fn ($item) => kicap_doc_money_raw($item));

    $totalExpense = $totalExpenseStored !== null && $totalExpenseStored !== ''
        ? kicap_doc_money_raw($totalExpenseStored)
        : $transactions->sum(fn ($item) => kicap_doc_money_raw($item));

    $totalRemaining = $totalRemainingStored !== null && $totalRemainingStored !== ''
        ? kicap_doc_money_raw($totalRemainingStored)
        : ($totalFunds - $totalExpense);

    $closingDate = kicap_doc_value($lpj ?? null, ['finalized_at', 'approved_at', 'updated_at', 'created_at'], now());

    $preparedBy = $picName ?: kicap_doc_user_name(kicap_doc_value($lpj ?? null, ['created_by']), 'Petugas');
    $checkedBy = kicap_doc_user_name(kicap_doc_value($lpj ?? null, ['approved_by', 'reviewed_by']), 'Admin');
    $approvedBy = kicap_doc_value($org, ['leader_name', 'director_name', 'approver_name'], 'Moh. Doni Irwanto');
    $approvedPosition = kicap_doc_value($org, ['leader_position', 'director_position', 'approver_position'], 'Direktur');

    $introText = $narratives['introduction'] ?? $narratives['background'] ?? "Laporan pertanggungjawaban ini disusun sebagai bentuk pertanggungjawaban atas pelaksanaan kegiatan {$title}. Laporan ini memuat informasi kegiatan, pelaksanaan, peserta, panitia/pendamping, laporan keuangan, dokumentasi, serta penutup sebagai bahan administrasi dan evaluasi.";
    $closingText = $narratives['closing'] ?? "Demikian laporan pertanggungjawaban kegiatan {$title} ini disusun sebagai bentuk pertanggungjawaban atas pelaksanaan kegiatan dan penggunaan dana. Laporan ini diharapkan dapat menjadi dokumen administrasi serta bahan evaluasi untuk pelaksanaan kegiatan berikutnya.";
    $evaluationText = $narratives['evaluation'] ?? kicap_doc_value($lpj ?? null, ['evaluation_note', 'evaluation'], 'Tidak ada catatan evaluasi khusus.');
    $obstacleText = kicap_doc_value($lpj ?? null, ['obstacle_note', 'kendala_note', 'notes'], "Kendala:\nTidak ada kendala khusus yang dicatat.");
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>LPJ {{ $title }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 0.22in 0.30in 0.28in 0.30in;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #111827;
            font-family: "Arial Narrow", "Roboto Condensed", "DejaVu Sans Condensed", Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.28;
        }

        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .cover-page {
            position: relative;
            min-height: 10.50in;
            text-align: center;
            page-break-after: always;
            overflow: hidden;
        }

        .cover-kicker {
            margin-top: 0;
            font-size: 14.5pt;
            font-weight: 700;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .cover-title {
            margin: 0.22in auto 0;
            max-width: 7.55in;
            font-size: 31pt;
            font-weight: 400;
            line-height: 1.08;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }

        .cover-subtitle {
            margin-top: 0.32in;
            font-size: 13.5pt;
            font-weight: 600;
        }

        .cover-period {
            margin-top: 0.24in;
            font-size: 9.5pt;
            font-style: italic;
        }

        .cover-logo-wrap {
            margin-top: 1.38in;
            width: 100%;
            text-align: center;
        }

        .cover-logo {
            width: 3.75in;
            height: 3.75in;
            object-fit: contain;
        }

        .cover-logo-placeholder {
            display: inline-block;
            width: 3.45in;
            height: 3.45in;
            line-height: 3.45in;
            border: 3px solid #991b1b;
            border-radius: 50%;
            color: #991b1b;
            font-weight: 800;
            font-size: 30pt;
        }

        .cover-footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0.18in;
            text-align: center;
        }

        .cover-org-type {
            font-size: 12pt;
            margin-bottom: 0.07in;
        }

        .cover-org-name {
            font-size: 15pt;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 0.10in;
        }

        .cover-address,
        .cover-contact,
        .cover-website,
        .cover-year {
            font-size: 8.6pt;
            line-height: 1.35;
        }

        .cover-year { margin-top: 0.08in; }

        .page-break {
            break-after: page;
            page-break-after: always;
            height: 0;
        }

        .section-title {
            margin: 0 0 0.22in;
            padding: 0;
            font-size: 15.5pt;
            font-weight: 800;
            line-height: 1.1;
            color: #173f4a;
            text-transform: uppercase;
        }

        .section-title span {
            display: inline-block;
            border-bottom: 2px solid #173f4a;
            padding-bottom: 1px;
        }

        .section-title:after {
            content: "";
            display: block;
            width: 100%;
            border-top: 2px solid #222;
            margin-top: 0.07in;
        }

        .sub-title {
            margin: 0.18in 0 0.10in;
            font-size: 11.5pt;
            font-weight: 800;
        }

        p {
            margin: 0 0 0.14in;
            text-align: left;
        }

        .compact-text { line-height: 1.36; }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.05in 0 0.16in;
            font-size: 10.6pt;
        }

        .meta-table td {
            border: none;
            padding: 2px 3px;
            vertical-align: top;
        }

        .meta-table .label { width: 1.35in; }
        .meta-table .colon { width: 0.10in; }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            margin: 0.08in 0 0.18in;
            font-size: 10.2pt;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #cfd6da;
            padding: 3px 5px;
            vertical-align: top;
            line-height: 1.20;
        }

        .report-table th {
            font-weight: 800;
            text-align: left;
        }

        .report-table thead { display: table-header-group; }

        .report-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .report-table .no {
            width: 0.35in;
            text-align: center;
            white-space: nowrap;
        }

        .center { text-align: center; }
        .money {
            text-align: right;
            white-space: nowrap;
        }

        .participant-danger td {
            color: #d1242f;
            font-style: italic;
            font-weight: 700;
        }

        .finance-summary {
            margin-top: 0.12in;
            width: 3.35in;
            margin-left: auto;
            font-size: 10.7pt;
        }

        .finance-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .finance-summary td {
            padding: 2px 0;
            font-weight: 700;
        }

        .finance-summary .value {
            text-align: right;
            white-space: nowrap;
        }

        .doc-page {
            break-before: page;
            page-break-before: always;
            min-height: 10.48in;
        }

        .doc-page-follow { padding-top: 0.58in; }

        .doc-intro {
            margin-top: -0.05in;
            margin-bottom: 0.20in;
            font-size: 10pt;
        }

        .doc-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0.10in 0.28in;
            margin-left: -0.10in;
            margin-right: -0.10in;
        }

        .doc-table td {
            width: 50%;
            vertical-align: top;
        }

        .doc-frame {
            width: 100%;
            height: 1.58in;
            border: 1px solid #d5d5d5;
            text-align: center;
            overflow: hidden;
            background: #fff;
        }

        .doc-frame img {
            max-width: 100%;
            max-height: 1.58in;
            object-fit: contain;
        }

        .doc-caption {
            margin-top: 0.05in;
            font-size: 5.6pt;
            line-height: 1.15;
            font-weight: 700;
        }

        .doc-caption span { font-weight: 400; }

        .closing-page {
            break-before: page;
            page-break-before: always;
            min-height: 10.48in;
        }

        .box-note {
            border: 1px solid #d8d8d8;
            min-height: 0.40in;
            padding: 0.06in 0.08in;
            margin: 0.08in 0 0.22in;
            font-size: 10.5pt;
            line-height: 1.35;
        }

        .box-note.tall { min-height: 1.55in; }

        .signature-date {
            margin-top: 0.70in;
            text-align: right;
            font-size: 10.5pt;
        }

        .signature-grid {
            width: 100%;
            margin-top: 0.12in;
            table-layout: fixed;
            border-collapse: collapse;
            text-align: center;
            font-size: 10.5pt;
        }

        .signature-grid td {
            width: 33.33%;
            vertical-align: top;
        }

        .signature-space { height: 0.72in; }
        .signature-name { margin-bottom: 0.12in; }

        @media screen {
            body { background: #e5e7eb; }

            .cover-page,
            .screen-sheet,
            .doc-page,
            .closing-page {
                width: 8.5in;
                min-height: 11in;
                margin: 16px auto;
                padding: 0.22in 0.30in 0.28in;
                background: white;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.18);
            }

            .cover-page { padding-top: 0.22in; }
        }
    </style>
</head>
<body>
    <section class="cover-page">
        <div class="cover-kicker">LAPORAN PERTANGGUNGJAWABAN</div>
        <div class="cover-title">{{ $title }}</div>
        <div class="cover-subtitle">{{ $typeLabel }}</div>
        <div class="cover-period">{{ $location !== '-' ? $location . ', ' : '' }}{{ $periodLabel }}</div>

        <div class="cover-logo-wrap">
            @if($logoUrl)
                <img class="cover-logo" src="{{ $logoUrl }}" alt="Logo">
            @else
                <div class="cover-logo-placeholder">K</div>
            @endif
        </div>

        <div class="cover-footer">
            <div class="cover-org-type">{{ $orgType }}</div>
            <div class="cover-org-name">{{ $orgName }}</div>
            @if($orgAddress)
                <div class="cover-address">{{ $orgAddress }}</div>
            @endif
            <div class="cover-contact">
                @if($orgPhone) Telp: {{ $orgPhone }} @endif
                @if($orgPhone && $orgMobile) | @endif
                @if($orgMobile) Hp: {{ $orgMobile }} @endif
                @if(($orgPhone || $orgMobile) && $orgEmail) | @endif
                @if($orgEmail) Email: {{ $orgEmail }} @endif
            </div>
            @if($orgWebsite)
                <div class="cover-website">{{ $orgWebsite }}</div>
            @endif
            <div class="cover-year">{{ date('Y', strtotime($closingDate)) }}</div>
        </div>
    </section>

    <section class="screen-sheet">
        <h2 class="section-title"><span>I. PENDAHULUAN</span></h2>
        <p class="compact-text">{!! nl2br(e($introText)) !!}</p>

        <h2 class="section-title"><span>II. IDENTITAS KEGIATAN</span></h2>
        <table class="meta-table">
            <tr><td class="label">Nama Kegiatan</td><td class="colon">:</td><td>{{ $title }}</td></tr>
            <tr><td class="label">Kode Event</td><td class="colon">:</td><td>{{ $code }}</td></tr>
            <tr><td class="label">Tipe Kegiatan</td><td class="colon">:</td><td>{{ $typeLabel }}</td></tr>
            <tr><td class="label">Tanggal / Periode</td><td class="colon">:</td><td>{{ $periodLabel }}</td></tr>
            <tr><td class="label">Lokasi</td><td class="colon">:</td><td>{{ $location }}</td></tr>
            <tr><td class="label">Penanggung Jawab</td><td class="colon">:</td><td>{{ $picName }}</td></tr>
            <tr><td class="label">Sumber Dana</td><td class="colon">:</td><td>{{ $fundingSource }}</td></tr>
            <tr><td class="label">Nomor Surat/Tugas</td><td class="colon">:</td><td>{{ $letterNumber }}</td></tr>
        </table>

        <h2 class="section-title"><span>III. PELAKSANAAN KEGIATAN</span></h2>
        <p class="compact-text">
            Pelaksanaan kegiatan dilakukan sesuai jadwal dan kebutuhan operasional di lapangan.
            Rincian agenda pelaksanaan disajikan pada tabel berikut.
        </p>

        <table class="report-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th>Waktu</th>
                    <th>Kegiatan</th>
                    <th>Penanggung Jawab</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                    @php
                        $scheduleDate = kicap_doc_value($schedule, ['activity_date', 'schedule_date', 'date', 'start_time', 'created_at']);
                        $activity = kicap_doc_value($schedule, ['activity_name', 'name', 'title', 'activity', 'description'], '-');
                        $responsible = kicap_doc_value($schedule, ['responsible_person', 'responsible', 'pic', 'person_in_charge'], '-');
                        $note = kicap_doc_value($schedule, ['note', 'description_note', 'remarks'], '-');
                    @endphp
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ kicap_doc_date($scheduleDate, true) }}</td>
                        <td>{!! nl2br(e($activity)) !!}</td>
                        <td>{{ $responsible ?: '-' }}</td>
                        <td>{!! nl2br(e($note ?: '-')) !!}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="no">1</td>
                        <td>{{ $periodLabel }}</td>
                        <td>Pelaksanaan kegiatan {{ $title }}</td>
                        <td>{{ $picName }}</td>
                        <td>-</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="page-break"></div>

    <section class="screen-sheet">
        <h2 class="section-title"><span>IV. PESERTA DAN PANITIA/PENDAMPING</span></h2>

        <div class="sub-title">4.1 Data Peserta</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th>Nama Peserta</th>
                    <th>Asal/Keterangan</th>
                    <th class="center">Nomor</th>
                    <th class="center">Kehadiran</th>
                    <th class="center">Hasil</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participants as $participant)
                    @php
                        $participantName = kicap_doc_value($participant, ['name', 'participant_name', 'nama'], '-');
                        $origin = kicap_doc_value($participant, ['origin', 'asal', 'description', 'note'], '-');
                        $number = kicap_doc_value($participant, ['participant_number', 'number', 'nomor'], '-');
                        $attendance = kicap_doc_value($participant, ['attendance_status', 'attendance', 'kehadiran'], '-');
                        $result = kicap_doc_value($participant, ['result_status', 'result', 'hasil'], '-');
                        $resultLower = Str::lower((string) $result);
                        $isDanger = str_contains($resultLower, 'gugur') || str_contains($resultLower, 'tidak') || str_contains($resultLower, 'failed');
                    @endphp
                    <tr class="{{ $isDanger ? 'participant-danger' : '' }}">
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ $participantName }}</td>
                        <td class="center">{{ $origin }}</td>
                        <td class="center">{{ $number }}</td>
                        <td class="center">{{ $attendance }}</td>
                        <td class="center">{{ $result }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center">Belum ada data peserta.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="sub-title">4.2 Panitia/Pendamping</div>
        <p class="compact-text">Panitia dan pendamping internal pada laporan ini diambil dari petugas yang ditugaskan pada event.</p>

        <table class="report-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th>Nama</th>
                    <th>Peran</th>
                    <th>Tugas/Kontak</th>
                </tr>
            </thead>
            <tbody>
                @forelse($committees as $committee)
                    @php
                        $committeeUserId = kicap_doc_value($committee, ['user_id']);
                        $committeeName = kicap_doc_value($committee, ['name', 'user_name', 'nama'], '');
                        $committeeName = $committeeName ?: kicap_doc_user_name($committeeUserId, '-');
                        $role = kicap_doc_value($committee, ['role', 'role_label', 'position', 'jabatan'], '-');
                        $task = kicap_doc_value($committee, ['task', 'description', 'contact', 'email'], '-');
                    @endphp
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ $committeeName }}</td>
                        <td>{{ $role }}</td>
                        <td>{{ $task }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="no">1</td>
                        <td>{{ $picName }}</td>
                        <td>Penanggung Jawab</td>
                        <td>Penanggung Jawab</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2 class="section-title"><span>V. LAPORAN KEUANGAN</span></h2>

        <div class="sub-title">5.1 Rincian Dana Masuk</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th>Tanggal</th>
                    <th>Sumber Dana</th>
                    <th class="money">Nominal</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fundRows as $fund)
                    @php
                        $fundDate = kicap_doc_value($fund, ['fund_date', 'date', 'received_at', 'created_at']);
                        $source = kicap_doc_value($fund, ['source_name', 'source', 'funding_source'], $fundingSource);
                        $note = kicap_doc_value($fund, ['note', 'description', 'created_by_name'], '-');
                    @endphp
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ kicap_doc_date($fundDate) }}</td>
                        <td>{{ $source }}</td>
                        <td class="money">{{ kicap_doc_money($fund) }}</td>
                        <td>{{ $note }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Belum ada data dana masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="sub-title">5.2 Rekap Pengeluaran per Kategori</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th>Kategori</th>
                    <th class="money">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorySummaries as $summary)
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ kicap_doc_value($summary, ['category', 'category_name', 'name'], '-') }}</td>
                        <td class="money">{{ kicap_doc_money(kicap_doc_value($summary, ['total', 'amount', 'nominal'], 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="center">Belum ada rekap pengeluaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="finance-summary">
            <table>
                <tr><td>Total Dana Diterima</td><td class="value">{{ kicap_doc_money($totalFunds) }}</td></tr>
                <tr><td>Total Pengeluaran Valid</td><td class="value">{{ kicap_doc_money($totalExpense) }}</td></tr>
                <tr><td>Sisa Dana Pegangan</td><td class="value">{{ kicap_doc_money($totalRemaining) }}</td></tr>
            </table>
        </div>
    </section>

    <div class="page-break"></div>

    <section class="screen-sheet">
        <div class="sub-title" style="font-size: 12pt; margin-top: 0;">5.3 Rincian Transaksi Valid</div>

        <table class="report-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Uraian</th>
                    <th>Sumber</th>
                    <th class="money">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $transactionDate = kicap_doc_value($transaction, ['transaction_date', 'date', 'spent_at', 'created_at']);
                        $description = kicap_doc_value($transaction, ['description', 'uraian', 'note', 'title'], '-');
                    @endphp
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ kicap_doc_date($transactionDate) }}</td>
                        <td>{{ kicap_doc_category_name($transaction) }}</td>
                        <td>{!! nl2br(e($description)) !!}</td>
                        <td>{{ kicap_doc_source_label($transaction) }}</td>
                        <td class="money">{{ kicap_doc_money($transaction) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center">Belum ada transaksi valid.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if($documentations->isNotEmpty())
        @foreach($documentations->chunk(8)->values() as $docChunkIndex => $docChunk)
            <section class="doc-page {{ $docChunkIndex > 0 ? 'doc-page-follow' : '' }}">
                @if($docChunkIndex === 0)
                    <h2 class="section-title"><span>VI. DOKUMENTASI KEGIATAN</span></h2>
                    <p class="doc-intro">Dokumentasi berikut merupakan foto atau berkas yang ditandai masuk ke LPJ final.</p>
                @endif

                <table class="doc-table">
                    @foreach($docChunk->chunk(2) as $docRow)
                        <tr>
                            @foreach($docRow as $doc)
                                @php
                                    $docNumber = ($docChunkIndex * 8) + (($loop->parent->iteration - 1) * 2) + $loop->iteration;
                                    $docPath = kicap_doc_value($doc, ['file_path', 'path', 'file', 'url', 'attachment_path', 'image_path', 'photo_path', 'documentation_path', 'media_path', 'file_url', 'image_url', 'photo_url', 'filename', 'file_name']);
                                    $docUrl = kicap_doc_file_url($docPath);
                                    $caption = kicap_doc_value($doc, ['caption', 'title', 'description'], 'Dokumentasi kegiatan');
                                    $category = kicap_doc_value($doc, ['category', 'category_name', 'type'], '-');
                                @endphp
                                <td>
                                    <div class="doc-frame">
                                        @if($docUrl)
                                            <img src="{!! $docUrl !!}" alt="{{ $caption }}">
                                        @endif
                                    </div>
                                    <div class="doc-caption">
                                        Gambar {{ $docNumber }}. <span>{{ $caption }}</span>
                                        &nbsp;&nbsp; Kategori: <span>{{ $category }}</span>
                                    </div>
                                </td>
                            @endforeach

                            @if(count($docRow) < 2)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </section>
        @endforeach
    @else
        <section class="doc-page">
            <h2 class="section-title"><span>VI. DOKUMENTASI KEGIATAN</span></h2>
            <p class="doc-intro">Belum ada dokumentasi yang ditandai masuk ke LPJ final.</p>
        </section>
    @endif

    <section class="closing-page">
        <h2 class="section-title"><span>VII. PENUTUP DAN PENGESAHAN</span></h2>

        <p class="compact-text">{!! nl2br(e($closingText)) !!}</p>

        <div class="sub-title" style="font-weight: 400;">Catatan Evaluasi</div>
        <div class="box-note">{!! nl2br(e($evaluationText)) !!}</div>

        <div class="sub-title" style="font-weight: 400;">Kendala dan Saran</div>
        <div class="box-note tall">{!! nl2br(e($obstacleText)) !!}</div>

        <div class="signature-date">{{ $orgCity }}, {{ kicap_doc_date($closingDate) }}</div>

        <table class="signature-grid">
            <tr>
                <td>
                    <div>Dibuat oleh,</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $preparedBy }}</div>
                    <div>Penanggung Jawab</div>
                </td>
                <td>
                    <div>Diperiksa oleh,</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $checkedBy }}</div>
                    <div>Admin</div>
                </td>
                <td>
                    <div>Disetujui oleh</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $approvedBy }}</div>
                    <div>{{ $approvedPosition }}</div>
                </td>
            </tr>
        </table>
    </section>
</body>
</html>
