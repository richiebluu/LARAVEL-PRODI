# Website Program Studi Teknologi Informasi — POLITALA

Aplikasi Laravel 13 untuk Program Studi Teknologi Informasi (PBL Kelompok 3).
Seluruh data aplikasi berasal dari database MySQL melalui Model, Controller,
Eloquent relationship, dan Blade.

## Menjalankan

```bash
cp .env.example .env          # sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD
composer install
php artisan key:generate
php artisan migrate:fresh     # data bisnis kosong
php artisan db:seed           # akun Staff Prodi pertama + kriteria ranking
php artisan storage:link      # wajib, untuk foto & dokumen
php artisan serve
```

Akun Staff Prodi pertama diatur lewat `.env`
(`STAFF_EMAIL`, `STAFF_PASSWORD`). Staff tambahan:

```bash
php artisan staff:buat
```

## Role (revisi 26-09-2026)

| Role | Akses |
|---|---|
| `staff` (Admin/Staf) | `/staff-*` — Data Master (profil prodi, struktur organisasi, akreditasi, dosen, mahasiswa), prestasi, ranking, pengumuman, testimoni, berita, lowongan pekerjaan |
| `mahasiswa` | `/mahasiswa-*` — profil & keaktifan organisasi, ajukan prestasi, pengumuman, notifikasi |
| pengunjung | halaman publik tanpa login |

Role Dosen **ditiadakan**; data dosen tetap ada sebagai Data Master (tanpa akun login).
Akun mahasiswa dibuat oleh Staff Prodi. Login bisa dengan email/password atau
**Login dengan Google** (email Politala yang sudah terdaftar) — lihat REVISI-2026-09-26.md.

## Struktur

```
app/Http/Controllers/        controller publik
app/Http/Controllers/Staff/  dashboard Staff Prodi
app/Http/Controllers/Mahasiswa/
app/Http/Middleware/EnsureRole.php
app/Models/                  17 model sesuai ERD
app/Services/                StatistikService, RankingService, NotifikasiService, GoogleLoginService
app/Mail/                    email pengumuman mahasiswa berprestasi
app/Support/Berkas.php       helper URL foto/dokumen
database/migrations/         struktur tabel
database/seeders/            hanya akun staff + kriteria ranking
resources/views/             Blade (desain tidak diubah)
resources/views/partials/    potongan Blade yang dipakai ulang
public/js/main.js            animasi, slider, filter, pencarian halaman
public/js/dashboard.js       interaksi UI dashboard (bukan penyimpan data)
```

Rincian revisi, daftar migration yang berubah, cara ranking dihitung, dan
skenario uji lengkap ada di **REVISI-DATABASE.md**; revisi terbaru di
**REVISI-2026-09-26.md** (role, ERD, fitur baru, Login Google).
