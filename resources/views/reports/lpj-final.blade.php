@php
    use App\Models\ActivityDocumentation;
    use App\Models\ActivityNote;
    use App\Models\ActivityParticipant;
    use App\Models\LpjFinancialTransaction;
    use Illuminate\Support\Str;

    $lpj = $report['lpj'];
    $organization = $report['organization'];
    $approval = $report['approval'];
    $forPdf = $forPdf ?? false;

    $money = fn ($amount): string => 'Rp '.number_format((float) $amount, 0, ',', '.');
    $date = fn ($value): string => $value ? $value->translatedFormat('d F Y') : '-';
    $dateTime = fn ($value): string => $value ? $value->translatedFormat('d F Y H:i') : '-';
    $period = $lpj->period_label ?: trim(($lpj->start_date?->translatedFormat('d F Y') ?? '-').' - '.($lpj->end_date?->translatedFormat('d F Y') ?? '-'));
    $footer = $organization?->footer_text ?: 'Kicap Event - PT. Kazoku Indonesia Center';
    $logoPath = $forPdf ? app(\App\Services\LpjReportService::class)->publicFilePath($organization?->logo_path) : app(\App\Services\LpjReportService::class)->publicFileUrl($organization?->logo_path);
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>LPJ {{ $lpj->code }} - {{ $lpj->title }}</title>
    <style>
        @page {
            margin: 22mm 18mm 20mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.55;
            margin: 0;
        }

        .page {
            page-break-after: always;
            position: relative;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .cover {
            align-items: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 247mm;
            text-align: center;
        }

        .cover-title {
            margin-top: 26mm;
        }

        h1, h2, h3 {
            letter-spacing: 0;
            margin: 0;
        }

        h1 {
            font-size: 24px;
            text-transform: uppercase;
        }

        h2 {
            border-bottom: 2px solid #0f766e;
            color: #0f172a;
            font-size: 16px;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        h3 {
            color: #0f172a;
            font-size: 13px;
            margin: 14px 0 6px;
        }

        .document-title {
            color: #b91c1c;
            font-size: 20px;
            font-weight: 700;
            margin-top: 16px;
            text-transform: uppercase;
        }

        .logo-box {
            align-items: center;
            border: 1px solid #cbd5e1;
            color: #64748b;
            display: flex;
            font-size: 11px;
            height: 90px;
            justify-content: center;
            margin: 26px auto;
            width: 90px;
        }

        .logo-box img {
            max-height: 86px;
            max-width: 86px;
        }

        .cover-meta {
            color: #334155;
            font-size: 13px;
            margin-top: 18px;
        }

        .cover-footer {
            color: #334155;
            margin-bottom: 18mm;
        }

        .letterhead {
            align-items: center;
            border-bottom: 3px double #0f172a;
            display: flex;
            gap: 14px;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .letterhead-logo {
            align-items: center;
            border: 1px solid #cbd5e1;
            color: #64748b;
            display: flex;
            flex: 0 0 72px;
            height: 72px;
            justify-content: center;
            width: 72px;
        }

        .letterhead-logo img {
            max-height: 68px;
            max-width: 68px;
        }

        .letterhead-text {
            flex: 1;
            text-align: center;
        }

        .institution-type {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .institution-name {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .muted {
            color: #64748b;
        }

        .center {
            text-align: center;
        }

        .grid-two {
            display: table;
            width: 100%;
        }

        .grid-two > div {
            display: table-cell;
            width: 50%;
        }

        .identity-table,
        .data-table {
            border-collapse: collapse;
            margin: 8px 0 12px;
            width: 100%;
        }

        .identity-table th,
        .identity-table td,
        .data-table th,
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 7px;
            vertical-align: top;
        }

        .identity-table th {
            background: #f8fafc;
            text-align: left;
            width: 30%;
        }

        .data-table th {
            background: #e0f2f1;
            color: #0f172a;
            font-size: 11px;
            text-align: left;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .summary-grid {
            display: table;
            margin: 12px 0;
            table-layout: fixed;
            width: 100%;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            display: table-cell;
            padding: 10px;
            width: 33.333%;
        }

        .summary-card strong {
            display: block;
            font-size: 15px;
            margin-top: 4px;
        }

        .signature-row {
            display: table;
            margin-top: 30px;
            table-layout: fixed;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            text-align: center;
            width: 33.333%;
        }

        .signature-space {
            height: 70px;
        }

        .doc-grid {
            display: table;
            table-layout: fixed;
            width: 100%;
        }

        .doc-item {
            display: table-cell;
            padding: 6px;
            width: 50%;
        }

        .doc-frame {
            border: 1px solid #cbd5e1;
            min-height: 140px;
            padding: 8px;
        }

        .doc-frame img {
            display: block;
            max-height: 160px;
            max-width: 100%;
            object-fit: contain;
        }

        .print-actions {
            background: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            padding: 10px 18px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .print-actions a,
        .print-actions button {
            background: #0f766e;
            border: 0;
            color: #ffffff;
            cursor: pointer;
            font: inherit;
            padding: 8px 12px;
            text-decoration: none;
        }

        .screen-wrap {
            margin: 0 auto;
            max-width: 900px;
            padding: 24px;
        }

        .page-number::after {
            content: counter(page);
        }

        .footer {
            bottom: -12mm;
            color: #64748b;
            font-size: 10px;
            left: 0;
            position: fixed;
            right: 0;
            text-align: center;
        }

        @media screen {
            body {
                background: #e5e7eb;
            }

            .page {
                background: #ffffff;
                box-shadow: 0 12px 35px rgba(15, 23, 42, .12);
                margin: 0 auto 24px;
                min-height: 297mm;
                padding: 22mm 18mm 20mm;
                width: 210mm;
            }

            .footer {
                position: absolute;
            }
        }

        @media print {
            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
@unless ($forPdf)
    <div class="print-actions">
        <a href="{{ route('admin.lpjs.report.pdf', $lpj) }}">Unduh PDF</a>
        <button type="button" onclick="window.print()">Cetak</button>
    </div>
@endunless

<div class="{{ $forPdf ? '' : 'screen-wrap' }}">
    <section class="page cover">
        <div class="cover-title">
            <h1>Laporan Pertanggungjawaban</h1>
            <div class="document-title">{{ $lpj->title }}</div>

            <div class="logo-box">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo lembaga">
                @else
                    Logo Lembaga
                @endif
            </div>

            <div class="cover-meta">
                <div><strong>Lokasi Kegiatan:</strong> {{ $lpj->location ?: '-' }}</div>
                <div><strong>Tanggal:</strong> {{ $period }}</div>
            </div>
        </div>

        <div class="cover-footer">
            <div><strong>{{ $organization?->institution_name ?: 'PT. Kazoku Indonesia Center' }}</strong></div>
            <div>{{ $organization?->address ?: '[Diisi kemudian]' }}</div>
            <div>{{ $organization?->email ?: '[Diisi kemudian]' }} | {{ $organization?->website ?: '[Diisi kemudian]' }}</div>
            <div>{{ $lpj->finalized_at?->format('Y') ?? now()->format('Y') }}</div>
        </div>
    </section>

    <section class="page">
        <div class="letterhead">
            <div class="letterhead-logo">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo lembaga">
                @else
                    Logo
                @endif
            </div>
            <div class="letterhead-text">
                <div class="institution-type">{{ $organization?->institution_type ?: 'Lembaga Pelatihan Kerja' }}</div>
                <div class="institution-name">{{ $organization?->institution_name ?: 'PT. Kazoku Indonesia Center' }}</div>
                @if ($organization?->unit_name)
                    <div>{{ $organization->unit_name }}</div>
                @endif
                <div>{{ $organization?->address ?: '[Diisi kemudian]' }}</div>
                <div>Telp. {{ $organization?->phone ?: '[Diisi kemudian]' }} | HP {{ $organization?->mobile ?: '[Diisi kemudian]' }}</div>
                <div>Email {{ $organization?->email ?: '[Diisi kemudian]' }} | Website {{ $organization?->website ?: '[Diisi kemudian]' }}</div>
            </div>
        </div>

        <h1 class="center">Halaman Pengesahan</h1>
        <table class="identity-table">
            <tr><th>Nama Kegiatan</th><td>{{ $lpj->title }}</td></tr>
            <tr><th>Kode LPJ</th><td>{{ $lpj->code }}</td></tr>
            <tr><th>Tipe Kegiatan</th><td>{{ $lpj->type?->name ?: '-' }}</td></tr>
            <tr><th>Periode</th><td>{{ $period }}</td></tr>
            <tr><th>Lokasi</th><td>{{ $lpj->location ?: '-' }}</td></tr>
            <tr><th>Dibuat oleh</th><td>{{ $approval['made_by'] }}</td></tr>
            <tr><th>Diperiksa oleh</th><td>{{ $approval['checked_by'] }}</td></tr>
            <tr><th>Disetujui oleh</th><td>{{ $approval['approved_by'] }}</td></tr>
        </table>

        <p class="center">
            {{ $approval['city'] }}, {{ $date($approval['date']) }}
        </p>

        <div class="signature-row">
            <div class="signature-box">
                Dibuat oleh
                <div class="signature-space"></div>
                <strong>{{ $approval['made_by'] }}</strong>
            </div>
            <div class="signature-box">
                Diperiksa oleh
                <div class="signature-space"></div>
                <strong>{{ $approval['checked_by'] }}</strong>
            </div>
            <div class="signature-box">
                Disetujui oleh
                <div class="signature-space"></div>
                <strong>{{ $approval['approved_by'] }}</strong>
                <div>{{ $approval['approved_position'] }}</div>
            </div>
        </div>

        <div class="footer">{{ $footer }} | Halaman <span class="page-number"></span></div>
    </section>

    <section class="page">
        <h2>Identitas Kegiatan</h2>
        <table class="identity-table">
            <tr><th>Nama kegiatan</th><td>{{ $lpj->title }}</td></tr>
            <tr><th>Tipe kegiatan</th><td>{{ $lpj->type?->name ?: '-' }}</td></tr>
            <tr><th>Tanggal</th><td>{{ $period }}</td></tr>
            <tr><th>Lokasi</th><td>{{ $lpj->location ?: '-' }}</td></tr>
            <tr><th>Penanggung jawab</th><td>{{ $lpj->personInCharge?->name ?: '-' }}</td></tr>
            <tr><th>Sumber dana</th><td>{{ $lpj->funding_source ?: '-' }}</td></tr>
            <tr><th>Nomor surat/tugas</th><td>{{ $lpj->assignment_letter_number ?: '-' }}</td></tr>
            <tr><th>Periode LPJ</th><td>{{ $period }}</td></tr>
        </table>

        <h2>Latar Belakang</h2>
        <p>
            Kegiatan {{ $lpj->title }} dilaksanakan sebagai bagian dari program kerja
            {{ $organization?->institution_name ?: 'PT. Kazoku Indonesia Center' }}. Laporan ini disusun sebagai bentuk pertanggungjawaban
            pelaksanaan kegiatan dan penggunaan dana berdasarkan data yang telah direview dan difinalisasi.
        </p>

        <h2>Maksud dan Tujuan</h2>
        <p>
            LPJ ini bertujuan mendokumentasikan pelaksanaan kegiatan, hasil yang dicapai, evaluasi pelaksanaan,
            serta penggunaan dana secara ringkas, formal, dan mudah dibaca oleh pihak terkait.
        </p>

        <h2>Penyelenggara / Peran Organisasi</h2>
        @if ($lpj->external_organizer)
            <p><strong>Penyelenggara eksternal:</strong> {{ $lpj->external_organizer }}</p>
        @endif
        <p>{{ $lpj->organization_role ?: ($organization?->institution_name ?: 'Lembaga').' berperan dalam pelaksanaan dan pendampingan kegiatan sesuai kebutuhan operasional.' }}</p>

        <div class="footer">{{ $footer }} | Halaman <span class="page-number"></span></div>
    </section>

    <section class="page">
        <h2>Data Peserta</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Peserta</th>
                    <th>Asal/Kelas/Divisi</th>
                    <th>Nomor Peserta</th>
                    <th>Kehadiran</th>
                    <th>Hasil</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['participants'] as $participant)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $participant->name ?: '-' }}</td>
                        <td>{{ $participant->origin ?: '-' }}</td>
                        <td>{{ $participant->participant_number ?: '-' }}</td>
                        <td>{{ ActivityParticipant::attendanceOptions()[$participant->attendance_status] ?? '-' }}</td>
                        <td>{{ $participant->result_status ?: '-' }}</td>
                        <td>{{ $participant->note ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="center muted">Belum ada data peserta.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Data Panitia / Pendamping</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jabatan/Peran</th>
                    <th>Tugas</th>
                    <th>Kontak</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['committees'] as $committee)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $committee->name ?: '-' }}</td>
                        <td>{{ $committee->role ?: '-' }}</td>
                        <td>{{ $committee->task ?: '-' }}</td>
                        <td>{{ $committee->contact ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center muted">Belum ada data panitia/pendamping.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Rundown / Pelaksanaan Kegiatan</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Kegiatan</th>
                    <th>Penanggung Jawab</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['schedules'] as $schedule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $dateTime($schedule->start_time) }} - {{ $dateTime($schedule->end_time) }}</td>
                        <td>{{ $schedule->activity_name ?: '-' }}</td>
                        <td>{{ $schedule->responsible_person ?: '-' }}</td>
                        <td>{{ $schedule->note ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center muted">Belum ada rundown kegiatan.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">{{ $footer }} | Halaman <span class="page-number"></span></div>
    </section>

    <section class="page">
        <h2>Hasil Kegiatan</h2>
        @forelse (($report['notes'][ActivityNote::TYPE_RESULT] ?? collect()) as $note)
            <p>{{ $note->content }}</p>
        @empty
            <p>Kegiatan telah dilaksanakan sesuai data pelaksanaan yang tercatat pada sistem.</p>
        @endforelse

        <h2>Evaluasi, Kendala, dan Saran</h2>
        @foreach ([ActivityNote::TYPE_EVALUATION, ActivityNote::TYPE_OBSTACLE, ActivityNote::TYPE_SUGGESTION] as $type)
            <h3>{{ ActivityNote::typeLabels()[$type] }}</h3>
            @forelse (($report['notes'][$type] ?? collect()) as $note)
                <p>{{ $note->content }}</p>
            @empty
                <p class="muted">Tidak ada catatan.</p>
            @endforelse
        @endforeach

        <h2>Laporan Keuangan Global</h2>
        <div class="summary-grid">
            <div class="summary-card">
                Total dana diterima
                <strong>{{ $money($lpj->total_funds_received) }}</strong>
            </div>
            <div class="summary-card">
                Total pengeluaran
                <strong>{{ $money($lpj->total_valid_expense) }}</strong>
            </div>
            <div class="summary-card">
                Total sisa dana
                <strong>{{ $money($lpj->total_remaining_fund) }}</strong>
            </div>
        </div>

        <h3>Rincian Dana Diterima</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Sumber</th>
                    <th>Uraian</th>
                    <th class="amount">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['fund_receipts'] as $receipt)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $date($receipt->received_at) }}</td>
                        <td>{{ $receipt->source_name }}</td>
                        <td>{{ $receipt->description ?: '-' }}</td>
                        <td class="amount">{{ $money($receipt->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center muted">Belum ada dana diterima.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h3>Rincian Per Kategori</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th class="amount">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['category_totals'] as $category)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $category['category'] }}</td>
                        <td class="amount">{{ $money($category['amount']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="center muted">Belum ada pengeluaran valid.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">{{ $footer }} | Halaman <span class="page-number"></span></div>
    </section>

    <section class="page">
        <h2>Rincian Transaksi Valid</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Uraian</th>
                    <th>Metode</th>
                    <th class="amount">Nominal</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['transactions'] as $transaction)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $date($transaction->spent_at) }}</td>
                        <td>{{ $transaction->category }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ LpjFinancialTransaction::sourceLabels()[$transaction->source_type] ?? $transaction->source_type }}</td>
                        <td class="amount">{{ $money($transaction->amount) }}</td>
                        <td>{{ $transaction->no_proof_reason ?: ($transaction->proof_path ? 'Bukti tersedia' : '-') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="center muted">Belum ada transaksi valid.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Dokumentasi Kegiatan</h2>
        @forelse ($report['documentations']->chunk(2) as $row)
            <div class="doc-grid">
                @foreach ($row as $documentation)
                    @php
                        $docPath = $forPdf
                            ? app(\App\Services\LpjReportService::class)->publicFilePath($documentation->file_path, $documentation->file_disk)
                            : app(\App\Services\LpjReportService::class)->publicFileUrl($documentation->file_path, $documentation->file_disk);
                    @endphp
                    <div class="doc-item">
                        <div class="doc-frame">
                            @if (Str::startsWith((string) $documentation->mime_type, 'image/') && $docPath)
                                <img src="{{ $docPath }}" alt="{{ $documentation->caption ?: $documentation->original_name }}">
                            @else
                                <strong>{{ $documentation->original_name }}</strong>
                            @endif
                            <div><strong>{{ ActivityDocumentation::categoryOptions()[$documentation->category] ?? $documentation->category }}</strong></div>
                            <div>{{ $documentation->caption ?: '-' }}</div>
                        </div>
                    </div>
                @endforeach
                @if ($row->count() === 1)
                    <div class="doc-item"></div>
                @endif
            </div>
        @empty
            <p class="muted">Tidak ada dokumentasi yang ditandai masuk LPJ.</p>
        @endforelse

        <div class="footer">{{ $footer }} | Halaman <span class="page-number"></span></div>
    </section>

    <section class="page">
        <h2>Lampiran</h2>
        <h3>Bukti Transaksi Valid</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Uraian</th>
                    <th>Nama Berkas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['proof_attachments'] as $transaction)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $date($transaction->spent_at) }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ basename((string) $transaction->proof_path) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="center muted">Tidak ada bukti transaksi terlampir.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h3>Lampiran Pendukung</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>Nama Berkas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['attachments'] as $attachment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attachment->title }}</td>
                        <td>{{ $attachment->description ?: '-' }}</td>
                        <td>{{ $attachment->original_name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="center muted">Tidak ada lampiran pendukung yang ditandai masuk LPJ.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Penutup</h2>
        <p>
            Demikian LPJ ini disusun sebagai bentuk pertanggungjawaban atas pelaksanaan kegiatan
            {{ $lpj->title }} dan penggunaan dana yang telah direview. Dokumen ini dihasilkan pada
            {{ $dateTime($report['generated_at']) }}.
        </p>

        <div class="footer">{{ $footer }} | Halaman <span class="page-number"></span></div>
    </section>
</div>
</body>
</html>
