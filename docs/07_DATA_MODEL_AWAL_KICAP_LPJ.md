# Data Model Awal Kicap Event v1.0

**Status:** Draft awal data model MVP
**Catatan utama:** Transfer saldo antar user tidak memakai approval Admin.

---

## 0.0 Decision — Kicap Event sebagai Konsep Utama

Keputusan 20 Juni 2026:

```text
Event / Kegiatan = objek utama
LPJ = output dokumen akhir
```

Data model konseptual memakai Event/Kegiatan sebagai objek utama. Nama tabel, kolom, model, dan route internal yang sudah memakai `lpj` boleh tetap dipertahankan sebagai legacy code sampai ada slice teknis rename yang aman.

---

## 0. Keputusan Revisi 20 Juni 2026

Data model MVP mengikuti keputusan terbaru:

1. Event/Kegiatan hanya dibuat oleh Admin.
2. Kolom izin `can_create_lpj` adalah legacy/internal untuk izin membuat event/kegiatan; User bernilai `false`.
3. Status event/kegiatan disederhanakan menjadi `draft`, `aktif`, `finish`, `arsipkan`.
4. Query event/kegiatan untuk PWA User hanya mengambil data yang ditugaskan dengan status `aktif` atau `finish`.
5. Auth memakai satu halaman login dan redirect berdasarkan role.
6. Remember-me harus didukung oleh session/auth layer.

---

## 1. Prinsip Data Model

Data model harus mendukung:

1. Admin dan User.
2. Tipe event/kegiatan.
3. Template narasi.
4. Data kegiatan lengkap.
5. Saldo pegangan user.
6. Transfer saldo antar user langsung tercatat.
7. Dana talangan dan klaim internal.
8. Transaksi valid/ditolak/revisi.
9. Transaksi tanpa bukti dengan alasan.
10. Split transaksi sederhana.
11. Dokumentasi kegiatan.
12. Lampiran LPJ dan lampiran internal.
13. Checklist kelengkapan.
14. Rekonsiliasi saldo.
15. Audit trail.
16. Export dokumen.

---

## 2. users

| Field | Keterangan |
|---|---|
| id | Primary key |
| name | Nama |
| email | Email |
| password | Password |
| role | admin/user |
| is_active | Status aktif |
| can_create_lpj | Legacy/opsional; User MVP bernilai false karena LPJ hanya dibuat Admin |
| can_transfer_balance | Izin transfer saldo |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 3. organization_profiles

| Field | Keterangan |
|---|---|
| id | Primary key |
| institution_name | Nama lembaga |
| institution_type | Jenis lembaga |
| unit_name | Unit/divisi opsional |
| address | Alamat |
| phone | Telepon |
| mobile | HP |
| email | Email |
| website | Website |
| logo_path | Logo |
| footer_text | Footer dokumen |
| default_city | Kota pengesahan |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

Default:

```text
institution_name = PT. Kazoku Indonesia Center
institution_type = Lembaga Pelatihan Kerja
```

---

## 4. lpj_types

| Field | Keterangan |
|---|---|
| id | Primary key |
| name | Nama tipe |
| slug | Slug |
| description | Deskripsi |
| is_external_event | Untuk pendampingan/event pihak luar |
| is_active | Status aktif |
| sort_order | Urutan |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

Tipe awal:

- Penyelenggaraan Event.
- Pendampingan Peserta Seleksi.
- Delegasi / Perwakilan.
- Bantuan Dana / Sponsorship.
- Kegiatan Internal.

---

## 5. lpjs

| Field | Keterangan |
|---|---|
| id | Primary key |
| code | Kode event/kegiatan |
| title | Judul event/kegiatan |
| lpj_type_id | Tipe event/kegiatan, nama kolom legacy |
| created_by | Pembuat |
| person_in_charge_id | Penanggung jawab |
| status | draft/aktif/finish/arsipkan |
| completeness_status | belum_lengkap/siap_review/siap_finalisasi |
| start_date | Tanggal mulai |
| end_date | Tanggal selesai |
| location | Lokasi |
| funding_source | Sumber dana |
| assignment_letter_number | Nomor surat/tugas |
| period_label | Periode |
| external_organizer | Penyelenggara eksternal |
| organization_role | Peran organisasi |
| total_funds_received | Total dana diterima |
| total_valid_expense | Total pengeluaran valid |
| total_remaining_fund | Total sisa dana |
| submitted_at | Waktu diajukan |
| approved_at | Waktu disetujui |
| approved_by | Admin penyetuju |
| finalized_at | Waktu final |
| finalized_by | Admin finalisasi |
| archived_at | Waktu arsip |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 6. lpj_narratives

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | Relasi LPJ |
| section | background/purpose/objective/execution/result/evaluation/closing |
| content | Isi narasi |
| generated_from_template_id | Template asal |
| is_edited | Apakah pernah diedit |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 7. narrative_templates

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_type_id | Tipe event/kegiatan, nama kolom legacy |
| section | Bagian narasi |
| title | Nama template |
| content | Isi template |
| is_active | Status aktif |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 8. lpj_assigned_users

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| user_id | User bertugas |
| role_label | Peran dalam LPJ |
| can_input_transaction | Izin input transaksi |
| can_upload_documentation | Izin upload dokumentasi |
| can_edit_activity_data | Izin edit data kegiatan |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 9. activity_participants

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| name | Nama peserta |
| origin | Asal/kelas/divisi |
| participant_number | Nomor peserta |
| attendance_status | Status kehadiran |
| result_status | Status hasil |
| note | Keterangan |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 10. activity_committees

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| name | Nama |
| role | Jabatan/peran |
| task | Tugas |
| contact | Kontak opsional |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 11. activity_schedules

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| start_time | Waktu mulai |
| end_time | Waktu selesai |
| activity_name | Nama aktivitas |
| responsible_person | Penanggung jawab |
| note | Keterangan |
| sort_order | Urutan |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 12. activity_notes

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| user_id | Pembuat catatan |
| type | kendala/evaluasi/saran/hasil/perubahan_lapangan/lainnya |
| content | Isi catatan |
| include_in_report | Masuk LPJ akhir |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 13. activity_documentations

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| uploaded_by | User |
| category | lokasi/peserta/briefing/registrasi/pelaksanaan/hasil/lainnya |
| file_path | Path file |
| caption | Caption |
| include_in_report | Masuk LPJ akhir |
| sort_order | Urutan |
| created_at | Waktu upload |
| updated_at | Waktu update |

---

## 14. lpj_funds

Dana masuk kegiatan/global.

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| fund_date | Tanggal dana |
| source_name | Sumber dana |
| amount | Nominal |
| note | Catatan |
| created_by | Admin/User |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 15. user_lpj_balances

Saldo pegangan user per LPJ/kegiatan.

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| user_id | User |
| current_balance | Saldo saat ini |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 16. balance_mutations

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| user_id | User |
| type | fund_allocation/expense/transfer_in/transfer_out/talangan/return/adjustment/reversal |
| direction | in/out/neutral |
| amount | Nominal |
| balance_before | Saldo sebelum |
| balance_after | Saldo sesudah |
| reference_type | Referensi modul |
| reference_id | ID referensi |
| description | Keterangan |
| created_by | Pembuat |
| created_at | Waktu mutasi |
| updated_at | Waktu update |

---

## 17. balance_transfers

Transfer saldo antar user langsung tercatat tanpa approval Admin.

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| code | Kode transfer |
| from_user_id | User pengirim |
| to_user_id | User penerima |
| amount | Nominal |
| note | Catatan |
| status | completed/cancelled/reversed |
| transferred_at | Waktu transfer |
| reversed_at | Waktu reversal |
| reversed_by | Admin yang membalik transaksi |
| reversal_reason | Alasan reversal |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

Catatan:

- Tidak ada status menunggu approval.
- Tidak ada approved_by.
- Jika salah, gunakan reversal/adjustment.

---

## 18. transaction_categories

| Field | Keterangan |
|---|---|
| id | Primary key |
| name | Nama kategori |
| is_active | Status aktif |
| sort_order | Urutan |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 19. payment_methods

| Field | Keterangan |
|---|---|
| id | Primary key |
| name | Nama metode |
| is_active | Status aktif |
| sort_order | Urutan |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 20. lpj_transactions

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| user_id | User pencatat |
| transaction_date | Tanggal |
| category_id | Kategori |
| description | Uraian |
| amount | Nominal |
| payment_method_id | Metode pembayaran |
| funding_mode | saldo_pegangan/dana_talangan |
| proof_status | ada_bukti/bukti_menyusul/tanpa_bukti |
| no_proof_reason | Alasan tanpa bukti |
| status | draft/menunggu_bukti/perlu_review/valid/ditolak/direvisi |
| reviewed_by | Admin reviewer |
| reviewed_at | Waktu review |
| rejection_reason | Alasan ditolak |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 21. transaction_items

Untuk split transaksi.

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_transaction_id | Transaksi |
| category_id | Kategori item |
| description | Uraian item |
| qty | Jumlah |
| unit | Satuan |
| unit_price | Harga satuan |
| amount | Total |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 22. transaction_attachments

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_transaction_id | Transaksi |
| file_path | Path file |
| file_name | Nama file |
| file_type | image/pdf |
| caption | Caption |
| sort_order | Urutan |
| created_at | Waktu upload |
| updated_at | Waktu update |

---

## 23. reimbursement_claims

Klaim internal dana talangan.

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| user_id | User pengaju |
| transaction_id | Transaksi sumber dana talangan |
| code | Kode klaim |
| amount | Nominal |
| status | diajukan/diverifikasi/ditolak/dibayar |
| verified_by | Admin verifikator |
| verified_at | Waktu verifikasi |
| rejected_by | Admin penolak |
| rejected_at | Waktu tolak |
| rejection_reason | Alasan tolak |
| paid_by | Admin pembayar |
| paid_at | Waktu dibayar |
| payment_note | Catatan pembayaran |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 24. lpj_checklist_items

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| key | Kode checklist |
| label | Label |
| is_required | Wajib/tidak |
| is_completed | Selesai/tidak |
| completed_at | Waktu selesai |
| completed_by | User |
| note | Catatan |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 25. lpj_reconciliations

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| user_id | User |
| system_balance | Saldo sistem |
| physical_balance | Sisa fisik |
| difference | Selisih |
| note | Catatan |
| reconciled_by | Admin |
| reconciled_at | Waktu rekonsiliasi |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 26. lpj_exports

| Field | Keterangan |
|---|---|
| id | Primary key |
| lpj_id | LPJ |
| export_type | pdf/print_view/word/internal_audit |
| file_path | Path file |
| exported_by | User |
| exported_at | Waktu export |
| created_at | Waktu dibuat |
| updated_at | Waktu update |

---

## 27. audit_logs

| Field | Keterangan |
|---|---|
| id | Primary key |
| actor_id | User pelaku |
| action | Aksi |
| auditable_type | Model |
| auditable_id | ID model |
| before_values | Data sebelum |
| after_values | Data sesudah |
| note | Catatan |
| created_at | Waktu dibuat |
