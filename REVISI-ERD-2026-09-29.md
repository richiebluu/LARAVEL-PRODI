# Revisi 29-09-2026 — Penyesuaian dengan ERD Terbaru

ERD terbaru adalah acuan utama. Desain (Blade/CSS/JS, navbar, sidebar, Kodex, logo TI) tidak diubah;
yang diubah hanya binding data, form input untuk kolom yang dihapus ERD, dan backend.

## Cara menerapkan

```bash
composer install
php artisan optimize:clear
php artisan migrate:fresh --seed   # WAJIB: primary key berubah, database lama direset
php artisan test
```

> Backup dulu database MySQL lama (phpMyAdmin > Export) bila ada data yang ingin disimpan.

## Mapping ERD → Migration → Model

| Tabel (ERD) | PK | FK / Relasi ERD | Model |
|---|---|---|---|
| users | id_user | 1-1 mahasiswa (`mahasiswa.user_id`), 1-1 staff_prodi (`staff_prodi.id_user`) | User |
| mahasiswa | nim | user_id → users; 1-N prestasi, organisasi, ranking, pengumuman (FK `nim`) | Mahasiswa |
| staff_prodi | id_staff_prodi | id_user → users; 1-N (MENGELOLA/MEMBUAT/MEMVERIFIKASI) lewat `staff_prodi_id` | StaffProdi |
| dosen | nuptk | 1-N struktur_organisasi (`dosen_id` → `nuptk`) | Dosen |
| program_studi | id_program_studi | staff_prodi_id; 1-N akreditasi, struktur_organisasi | ProgramStudi |
| akreditasi | id_akreditasi | program_studi_id | Akreditasi |
| struktur_organisasi | id_struktur_organisasi | program_studi_id, dosen_id | StrukturOrganisasi |
| organisasi | id_organisasi | nim | Organisasi |
| prestasi | id_prestasi | nim (MENGAJUKAN), staff_prodi_id (MEMVERIFIKASI) | Prestasi |
| ranking_bobot | id_ranking_bobot | 1-N ranking | RankingBobot |
| ranking | id_ranking | nim, ranking_bobot_id | Ranking |
| pengumuman | id_pengumuman | staff_prodi_id (MEMBUAT), nim (MENERIMA), prestasi_id (MENDAPAT) | Pengumuman |
| prospek_lulusan | id_prospek_lulusan | staff_prodi_id | ProspekLulusan |
| testimoni | id_testimoni | staff_prodi_id | Testimoni |
| kegiatan_mahasiswa | id_kegiatan_mahasiswa | staff_prodi_id | KegiatanMahasiswa |
| berita | id_berita | staff_prodi_id | Berita |
| lowongan_pekerjaan | id_lowongan_pekerjaan | staff_prodi_id | LowonganPekerjaan |
| sarana_prasarana | id_sarana_prasarana | staff_prodi_id | SaranaPrasarana |
| mata_kuliah *(di luar ERD, dipertahankan)* | id | – | MataKuliah |

## Catatan keputusan

- **pengumuman.nim** dan **prestasi.staff_prodi_id** tidak tertulis sebagai atribut di ERD, tetapi wajib ada
  sebagai FK agar relasi MENERIMA (Mahasiswa 1–N Pengumuman) dan MEMVERIFIKASI (Staff 1–N Prestasi) benar-benar
  tersimpan di database.
- **Notifikasi** memakai atribut `notifikasi` + `dibaca_pada` pada PENGUMUMAN. Pesan sistem (status verifikasi
  prestasi) disimpan sebagai baris pengumuman berstatus `notifikasi` — hanya tampil di menu Notifikasi mahasiswa.
- Kolom di luar ERD dihapus: `urutan` (prospek_lulusan, sarana_prasarana, struktur_organisasi),
  `dosen.keterangan_status`, `testimoni.status`, `staff_prodi.status`. Urutan tampil struktur organisasi kini
  mengikuti hierarki jabatan; sarana: laboratorium dulu lalu nama; prospek: urutan input.
- Nama kolom ranking mengikuti ERD dengan huruf kecil (`nilai_ipk`, `normalisasi_nilai_ipk`, …).
- ERD menulis `google_schoolar`; kolom memakai ejaan benar `google_scholar`.
- Algoritma SAW (config/saw.php + RankingService) tidak diubah; hanya nama kolom penyimpanan.
