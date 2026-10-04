# Catatan Project

Project ini adalah website Program Studi Teknologi Informasi berbasis Laravel 13.

Aturan yang berlaku saat mengubah project ini:

1. **Database adalah satu-satunya sumber data aplikasi.** Jangan memakai
   localStorage, array JavaScript, atau angka hardcode sebagai sumber data.
2. **ERD adalah acuan struktur.** Jangan menambah tabel baru tanpa alasan kuat,
   dan jangan mengubah nama kolom sembarangan.
3. **Desain tidak diubah.** Layout, warna, font, navbar, sidebar, card, ikon,
   animasi, Kodex, dan CSS dipertahankan. Blade hanya diubah pada bagian yang
   perlu menjadi dinamis.
4. **Nama tabel singular & PK sesuai ERD.** `mahasiswa` (PK `nim`), `dosen` (PK `nuptk`),
   `staff_prodi` (PK `id_staff_prodi`), `users` (PK `id_user`), tabel lain PK `id_<nama_tabel>`.
   Setiap model wajib menuliskan `protected $table` dan `protected $primaryKey`.
5. **Status memakai Bahasa Indonesia** dan konsisten: `menunggu`, `disetujui`,
   `ditolak`, `draft`, `terkirim`, `aktif`, `alumni`, `cuti`, `nonaktif`, `do`, `dispen`.
   Label UI: "Menunggu Verifikasi", "Disetujui", "Ditolak" (tanpa Approved/Terverifikasi/Tercatat).
6. **Seeder tidak boleh mengisi data bisnis.** Hanya akun Staff Prodi pertama
   dan 4 baris kriteria ranking.
7. **Database kosong harus tetap aman.** Setiap daftar wajib punya empty state.

Lihat REVISI-DATABASE.md dan REVISI-2026-09-24.md untuk rincian lengkap.

> Revisi 26-09-2026: role Dosen ditiadakan (data dosen = Data Master), perubahan data tanpa verifikasi, fitur baru Struktur Organisasi/Testimoni/Berita/Lowongan/Layanan/Login Google. Lihat CLAUDE.md dan REVISI-2026-09-26.md.

> Revisi 27-09-2026 (dokumen "Website Revisi.docx"): Layanan → Informasi (Berita, AKAMAWA, Kode Etik Mahasiswa interaktif seperti buku), Testimoni hanya Testimoni Alumni, Bidang Keahlian dosen dihapus, foto Struktur Organisasi, tabel "Semua" Mahasiswa Berprestasi = prestasi terbaru + "Lihat Prestasi Lainnya". Lihat REVISI-2026-09-27.md.

> Revisi 28-09-2026 ("REVISI BARU.docx"): Prospek Lulusan jadi CRUD Staff (tabel prospek_lulusan, card = card Lowongan Kerja, ikon kategori via dropdown); Dosen: kolom Jabatan dihapus, Status Aktif/Pendidikan/Nonaktif (+ keterangan); Kurikulum (tabel mata_kuliah, Profil > Kurikulum, CRUD + impor CSV Staff); Kode Etik tampilan buku ala Univ. Bosowa; navbar huruf kapital. Lihat REVISI-2026-09-28.md.

> Revisi 28-09-2026 tahap 2 ("REVISI BARU(1).docx"): Impor CSV bersama (trait `App\Http\Controllers\Concerns\MengimporCsv` + `App\Support\ImporCsv` + partial `partials/impor-csv-modal`) di Mahasiswa, Dosen, Kurikulum, Prospek Lulusan, Sarana & Prasarana; CRUD baru Sarana & Prasarana (tabel `sarana_prasarana`, Profil > Sarana & Prasarana) dan Kegiatan Mahasiswa (tabel `kegiatan_mahasiswa`, Mahasiswa > Kegiatan Mahasiswa urutan ketiga); navbar kembali Capital Each Word; Akreditasi Staff tabel di atas; search di atas tabel Dosen publik; Kode Etik PDF ukuran penuh (bukan buku); pesan validasi Bahasa Indonesia (`lang/id/validation.php`).

> Revisi 29-09-2026 (ERD terbaru): struktur database disamakan dengan ERD — PK `id_user`/`nim`/`nuptk`/`id_*`,
> FK `nim`, `staff_prodi_id`, `staff_prodi.id_user`, `struktur_organisasi.dosen_id -> dosen.nuptk`; tabel
> `pengajuan_perubahan`, `pengumuman_penerima`, `notifikasi` dihapus (penerima & notifikasi kini kolom
> `pengumuman.nim`, `pengumuman.notifikasi`, `pengumuman.dibaca_pada`). `mata_kuliah` (Kurikulum) dipertahankan
> di luar ERD atas permintaan pemilik project. Lihat REVISI-ERD-2026-09-29.md.

> Revisi dosen 01-10-2026: akreditasi utama (Terakreditasi + tanggal terbaru), per_page mahasiswa 5/10/15/20, dasar pembobotan + AHP di Staff\RankingController::hitungAHP(), pengumuman banyak penerima (tabel pengumuman_penerima), hero publik foto GTI (App\Support\HeroFoto). Lihat REVISI-2026-10-01.md.

> Revisi dosen 02-10-2026: sidebar Staff collapse, dashboard Staff tanpa Quick Action/Alur Sistem, jabatan Struktur Organisasi dropdown berhierarki + bagan otomatis, Prospek Lulusan tanpa kategori (ikon bebas), Kurikulum → Mata Kuliah, Sarana tanpa deskripsi, Kegiatan Mahasiswa digabung ke Berita (`berita.jenis`), Notifikasi mahasiswa digabung ke Pengumuman, media sosial via `config/prodi.php`. Lihat REVISI-2026-10-02.md.

> Revisi review 03-10-2026: Kegiatan Prodi (label jenis berita), sidebar Mahasiswa collapse, lowongan tanpa status, sarana pakai `gedung` (tanpa jenis/lokasi), berprestasi tanpa tab Keaktifan Organisasi, AKAMAWA ala Pengumuman, Tambah Keaktifan Organisasi fleksibel. Lihat REVISI-2026-10-03.md.

> Revisi 03-10-2026 tahap 2: `mata_kuliah.program_studi_id` (FK -> `program_studi.id_program_studi`, diisi otomatis dari Profil Prodi, tanpa dropdown); `program_studi.link_media_sosial` (link media sosial website, satu baris satu URL, dikelola di Profil Prodi; config/prodi.php jadi cadangan); `berita.link_media_sosial` (link postingan media sosial, opsional, tombol "Lihat Postingan Media Sosial" di detail berita). Lihat REVISI-2026-10-03-TAHAP-2.md.

> Revisi 04-10-2026: komentar kode dibersihkan (hanya label bagian), migration dirapikan satu file per tabel + `sinkronkan_struktur_database`, tabel `kegiatan_mahasiswa`/`password_reset_tokens`/`job_batches` dihapus, PK `mata_kuliah` = `kode_mata_kuliah`. Lihat REVISI-2026-10-04.md.
