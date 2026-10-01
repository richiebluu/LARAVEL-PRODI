<?php

use App\Http\Controllers\AkreditasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformasiController;
use App\Http\Controllers\LowonganPekerjaanController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\TestimoniController;
use Illuminate\Support\Facades\Route;

/* ---------- Halaman publik ---------- */

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/profil', [ProgramStudiController::class, 'index'])->name('profil');
// REVISI 27-09-2026: Visi & Misi menjadi bagian halaman Tentang (dropdown Profil mengikuti Word).
Route::get('/visi-misi', fn () => redirect()->to(route('profil').'#visi-misi', 301))->name('visi-misi');
Route::get('/prospek-lulusan', [ProgramStudiController::class, 'prospekLulusan'])->name('prospek-lulusan');
// Akreditasi = atribut Profil Program Studi (relasi program_studi -> akreditasi).
Route::get('/akreditasi', [AkreditasiController::class, 'index'])->name('akreditasi');
Route::get('/struktur-organisasi', [ProgramStudiController::class, 'strukturOrganisasi'])->name('struktur-organisasi');
// Dosen Pengajar (Data Master dosen, bukan akun pengguna).
Route::get('/dosen', [DosenController::class, 'index'])->name('dosen');

// REVISI 28-09-2026 ("REVISI BARU.docx"): halaman Kurikulum dibuat kembali di dropdown Profil,
// berisi daftar mata kuliah sesuai SIPADU. Kalender Akademik (revisi 26-09) tetap tidak dibuat.
Route::get('/kurikulum', [\App\Http\Controllers\KurikulumController::class, 'index'])->name('kurikulum');

// REVISI 28-09-2026 tahap 2 ("REVISI BARU(1).docx"): Sarana & Prasarana (termasuk laboratorium) di dropdown Profil.
Route::get('/sarana-prasarana', [ProgramStudiController::class, 'saranaPrasarana'])->name('sarana-prasarana');

/* Mahasiswa Berprestasi */
Route::get('/mahasiswa-berprestasi', [MahasiswaController::class, 'berprestasi'])->name('mahasiswa-berprestasi');
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
// REVISI 28-09-2026 tahap 2: Kegiatan Mahasiswa (dropdown Mahasiswa, urutan ketiga).
Route::get('/kegiatan-mahasiswa', [MahasiswaController::class, 'kegiatan'])->name('kegiatan-mahasiswa');
// REVISI 26-09-2026: halaman /mahasiswa (daftar seluruh mahasiswa) dan /prestasi dihapus.
// Bagian Mahasiswa cukup Mahasiswa Berprestasi + Ranking; link lama diarahkan ke sana.
Route::permanentRedirect('/mahasiswa', '/mahasiswa-berprestasi');
Route::permanentRedirect('/prestasi', '/mahasiswa-berprestasi');

/* Testimoni Alumni (REVISI 27-09-2026: tanpa dropdown, Testimoni Mahasiswa Berprestasi dihapus) */
Route::get('/testimoni', [TestimoniController::class, 'index'])->name('testimoni');
Route::permanentRedirect('/testimoni/alumni', '/testimoni');
Route::permanentRedirect('/testimoni/mahasiswa-berprestasi', '/testimoni');

/* ===== INFORMASI (REVISI 27-09-2026, sebelumnya "Layanan"): Berita, AKAMAWA, Kode Etik Mahasiswa ===== */

/* Berita */
Route::get('/berita', [BeritaController::class, 'index'])->name('berita');
Route::get('/berita/{berita}', [BeritaController::class, 'show'])->name('berita.show');

/* Lowongan Pekerjaan */
Route::get('/lowongan-pekerjaan', [LowonganPekerjaanController::class, 'index'])->name('lowongan-pekerjaan');

/* AKAMAWA & Kode Etik Mahasiswa */
Route::get('/akamawa', [InformasiController::class, 'akamawa'])->name('akamawa');
Route::get('/kode-etik', [InformasiController::class, 'kodeEtik'])->name('kode-etik');
Route::permanentRedirect('/layanan', '/akamawa');

/* Pengumuman (isi pengumuman bersifat pribadi, dilihat mahasiswa setelah login) */
Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman');

/* ---------- Login ---------- */

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

/* Login dengan Google (Google OAuth via Laravel Socialite) */
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('login.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('login.google.callback');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/* ---------- Dashboard Mahasiswa ---------- */

Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    $mhs = \App\Http\Controllers\Mahasiswa\DashboardController::class;

    Route::get('/mahasiswa-dashboard', [$mhs, 'index'])->name('mahasiswa-dashboard');

    Route::get('/mahasiswa-profile', [$mhs, 'profil'])->name('mahasiswa-profile');
    // REVISI 26-09-2026: perubahan data tanpa verifikasi (validasi form -> langsung tersimpan).
    Route::post('/mahasiswa-profile', [$mhs, 'simpanPerubahan'])->name('mahasiswa-profile.simpan');
    Route::post('/mahasiswa-profile/organisasi', [$mhs, 'simpanOrganisasi'])->name('mahasiswa-profile.organisasi');

    Route::get('/mahasiswa-prestasi', [$mhs, 'prestasi'])->name('mahasiswa-prestasi');
    Route::get('/mahasiswa-ajukan-prestasi', [$mhs, 'formPrestasi'])->name('mahasiswa-ajukan-prestasi');
    Route::post('/mahasiswa-ajukan-prestasi', [$mhs, 'simpanPrestasi'])->name('mahasiswa-ajukan-prestasi.store');

    Route::get('/mahasiswa-pengumuman', [$mhs, 'pengumuman'])->name('mahasiswa-pengumuman');

    Route::get('/mahasiswa-notifikasi', [$mhs, 'notifikasi'])->name('mahasiswa-notifikasi');
    Route::post('/mahasiswa-notifikasi/baca', [$mhs, 'bacaNotifikasi'])->name('mahasiswa-notifikasi.baca');
});

/* ---------- Dashboard Staff Prodi ---------- */

Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff-dashboard', [\App\Http\Controllers\Staff\DashboardController::class, 'index'])
        ->name('staff-dashboard');

    /* ===== DATA MASTER ===== */

    /* CRUD Mahasiswa */
    $sMhs = \App\Http\Controllers\Staff\MahasiswaController::class;
    Route::get('/staff-mahasiswa', [$sMhs, 'index'])->name('staff-mahasiswa');
    Route::post('/staff-mahasiswa', [$sMhs, 'store'])->name('staff-mahasiswa.store');
    Route::post('/staff-mahasiswa/impor', [$sMhs, 'impor'])->name('staff-mahasiswa.impor');       // REVISI 28-09 tahap 2
    Route::get('/staff-mahasiswa/template', [$sMhs, 'template'])->name('staff-mahasiswa.template');
    Route::put('/staff-mahasiswa/{mahasiswa}', [$sMhs, 'update'])->name('staff-mahasiswa.update');
    Route::delete('/staff-mahasiswa/{mahasiswa}', [$sMhs, 'destroy'])->name('staff-mahasiswa.destroy');

    /* CRUD Dosen (Data Master, tanpa akun login) */
    $sDsn = \App\Http\Controllers\Staff\DosenController::class;
    Route::get('/staff-dosen', [$sDsn, 'index'])->name('staff-dosen');
    Route::post('/staff-dosen', [$sDsn, 'store'])->name('staff-dosen.store');
    Route::post('/staff-dosen/impor', [$sDsn, 'impor'])->name('staff-dosen.impor');               // REVISI 28-09 tahap 2
    Route::get('/staff-dosen/template', [$sDsn, 'template'])->name('staff-dosen.template');
    Route::put('/staff-dosen/{dosen}', [$sDsn, 'update'])->name('staff-dosen.update');
    Route::delete('/staff-dosen/{dosen}', [$sDsn, 'destroy'])->name('staff-dosen.destroy');

    /* Prestasi */
    $sPres = \App\Http\Controllers\Staff\PrestasiController::class;
    Route::get('/staff-prestasi', [$sPres, 'index'])->name('staff-prestasi');
    Route::put('/staff-prestasi/{prestasi}/verifikasi', [$sPres, 'verifikasi'])->name('staff-prestasi.verifikasi');
    Route::delete('/staff-prestasi/{prestasi}', [$sPres, 'destroy'])->name('staff-prestasi.destroy');

    // REVISI 26-09-2026: menu Verifikasi perubahan data dihapus (tanpa proses persetujuan).

    /* Ranking */
    $sRank = \App\Http\Controllers\Staff\RankingController::class;
    Route::get('/staff-ranking', [$sRank, 'index'])->name('staff-ranking');
    // Bobot kriteria bersifat tetap (read-only, hasil AHP) sesuai revisi dosen,
    // sehingga route POST /staff-ranking/bobot untuk mengubah bobot dihapus.
    Route::post('/staff-ranking/generate', [$sRank, 'generate'])->name('staff-ranking.generate');
    // REVISI DOSEN 01-10-2026: dasar/alasan pembobotan setiap kriteria (teks dokumentasi).
    Route::put('/staff-ranking/dasar-pembobotan', [$sRank, 'simpanDasar'])->name('staff-ranking.dasar');
    Route::delete('/staff-ranking', [$sRank, 'reset'])->name('staff-ranking.reset');

    /* Pengumuman */
    $sPeng = \App\Http\Controllers\Staff\PengumumanController::class;
    Route::get('/staff-pengumuman', [$sPeng, 'index'])->name('staff-pengumuman');
    // REVISI DOSEN 01-10-2026: cari penerima (email @mhs.politala.ac.id) untuk input chip banyak penerima.
    Route::get('/staff-pengumuman/cari-mahasiswa', [$sPeng, 'cariMahasiswa'])->name('staff-pengumuman.cari-mahasiswa');
    Route::post('/staff-pengumuman', [$sPeng, 'store'])->name('staff-pengumuman.store');
    Route::put('/staff-pengumuman/{pengumuman}', [$sPeng, 'update'])->name('staff-pengumuman.update');
    Route::delete('/staff-pengumuman/{pengumuman}', [$sPeng, 'destroy'])->name('staff-pengumuman.destroy');

    /* Profil Prodi */
    $sProf = \App\Http\Controllers\Staff\ProfilController::class;
    Route::get('/staff-profil', [$sProf, 'index'])->name('staff-profil');
    Route::post('/staff-profil', [$sProf, 'simpan'])->name('staff-profil.simpan');

    /* Profil Saya (data Staff Prodi yang login) — REVISI 24-09-2026 */
    Route::get('/staff-profile', [$sProf, 'profilSaya'])->name('staff-profile');
    Route::post('/staff-profile', [$sProf, 'simpanProfilSaya'])->name('staff-profile.simpan');

    /* Akreditasi */
    $sAkr = \App\Http\Controllers\Staff\AkreditasiController::class;
    Route::get('/staff-akreditasi', [$sAkr, 'index'])->name('staff-akreditasi');
    Route::post('/staff-akreditasi', [$sAkr, 'store'])->name('staff-akreditasi.store');
    Route::put('/staff-akreditasi/{akreditasi}', [$sAkr, 'update'])->name('staff-akreditasi.update');
    Route::delete('/staff-akreditasi/{akreditasi}', [$sAkr, 'destroy'])->name('staff-akreditasi.destroy');

    /* Struktur Organisasi */
    $sStr = \App\Http\Controllers\Staff\StrukturOrganisasiController::class;
    Route::get('/staff-struktur-organisasi', [$sStr, 'index'])->name('staff-struktur-organisasi');
    Route::post('/staff-struktur-organisasi', [$sStr, 'store'])->name('staff-struktur-organisasi.store');
    Route::put('/staff-struktur-organisasi/{struktur}', [$sStr, 'update'])->name('staff-struktur-organisasi.update');
    Route::delete('/staff-struktur-organisasi/{struktur}', [$sStr, 'destroy'])->name('staff-struktur-organisasi.destroy');

    /* Prospek Lulusan — REVISI 28-09-2026 */
    $sPro = \App\Http\Controllers\Staff\ProspekLulusanController::class;
    Route::get('/staff-prospek-lulusan', [$sPro, 'index'])->name('staff-prospek-lulusan');
    Route::post('/staff-prospek-lulusan', [$sPro, 'store'])->name('staff-prospek-lulusan.store');
    Route::post('/staff-prospek-lulusan/impor', [$sPro, 'impor'])->name('staff-prospek-lulusan.impor');       // REVISI 28-09 tahap 2
    Route::get('/staff-prospek-lulusan/template', [$sPro, 'template'])->name('staff-prospek-lulusan.template');
    Route::put('/staff-prospek-lulusan/{prospek}', [$sPro, 'update'])->name('staff-prospek-lulusan.update');
    Route::delete('/staff-prospek-lulusan/{prospek}', [$sPro, 'destroy'])->name('staff-prospek-lulusan.destroy');

    /* Kurikulum (mata kuliah sesuai SIPADU) — REVISI 28-09-2026 */
    $sKur = \App\Http\Controllers\Staff\KurikulumController::class;
    Route::get('/staff-kurikulum', [$sKur, 'index'])->name('staff-kurikulum');
    Route::post('/staff-kurikulum', [$sKur, 'store'])->name('staff-kurikulum.store');
    Route::post('/staff-kurikulum/impor', [$sKur, 'impor'])->name('staff-kurikulum.impor');
    Route::get('/staff-kurikulum/template', [$sKur, 'template'])->name('staff-kurikulum.template');
    Route::put('/staff-kurikulum/{mataKuliah}', [$sKur, 'update'])->name('staff-kurikulum.update');
    Route::delete('/staff-kurikulum/{mataKuliah}', [$sKur, 'destroy'])->name('staff-kurikulum.destroy');

    /* Sarana & Prasarana (termasuk Laboratorium) — REVISI 28-09-2026 tahap 2 */
    $sSar = \App\Http\Controllers\Staff\SaranaPrasaranaController::class;
    Route::get('/staff-sarana-prasarana', [$sSar, 'index'])->name('staff-sarana-prasarana');
    Route::post('/staff-sarana-prasarana', [$sSar, 'store'])->name('staff-sarana-prasarana.store');
    Route::post('/staff-sarana-prasarana/impor', [$sSar, 'impor'])->name('staff-sarana-prasarana.impor');
    Route::get('/staff-sarana-prasarana/template', [$sSar, 'template'])->name('staff-sarana-prasarana.template');
    Route::put('/staff-sarana-prasarana/{sarana}', [$sSar, 'update'])->name('staff-sarana-prasarana.update');
    Route::delete('/staff-sarana-prasarana/{sarana}', [$sSar, 'destroy'])->name('staff-sarana-prasarana.destroy');

    /* ===== INFORMASI PUBLIK ===== */

    /* Kegiatan Mahasiswa — REVISI 28-09-2026 tahap 2 */
    $sKeg = \App\Http\Controllers\Staff\KegiatanMahasiswaController::class;
    Route::get('/staff-kegiatan-mahasiswa', [$sKeg, 'index'])->name('staff-kegiatan-mahasiswa');
    Route::post('/staff-kegiatan-mahasiswa', [$sKeg, 'store'])->name('staff-kegiatan-mahasiswa.store');
    Route::put('/staff-kegiatan-mahasiswa/{kegiatan}', [$sKeg, 'update'])->name('staff-kegiatan-mahasiswa.update');
    Route::delete('/staff-kegiatan-mahasiswa/{kegiatan}', [$sKeg, 'destroy'])->name('staff-kegiatan-mahasiswa.destroy');

    /* Testimoni */
    $sTes = \App\Http\Controllers\Staff\TestimoniController::class;
    Route::get('/staff-testimoni', [$sTes, 'index'])->name('staff-testimoni');
    Route::post('/staff-testimoni', [$sTes, 'store'])->name('staff-testimoni.store');
    Route::put('/staff-testimoni/{testimoni}', [$sTes, 'update'])->name('staff-testimoni.update');
    Route::delete('/staff-testimoni/{testimoni}', [$sTes, 'destroy'])->name('staff-testimoni.destroy');

    /* Berita (binding memakai id_berita di dashboard; publik memakai slug) */
    $sBer = \App\Http\Controllers\Staff\BeritaController::class;
    Route::get('/staff-berita', [$sBer, 'index'])->name('staff-berita');
    Route::post('/staff-berita', [$sBer, 'store'])->name('staff-berita.store');
    Route::put('/staff-berita/{berita:id_berita}', [$sBer, 'update'])->name('staff-berita.update');
    Route::delete('/staff-berita/{berita:id_berita}', [$sBer, 'destroy'])->name('staff-berita.destroy');

    /* Lowongan Pekerjaan */
    $sLow = \App\Http\Controllers\Staff\LowonganPekerjaanController::class;
    Route::get('/staff-lowongan', [$sLow, 'index'])->name('staff-lowongan');
    Route::post('/staff-lowongan', [$sLow, 'store'])->name('staff-lowongan.store');
    Route::put('/staff-lowongan/{lowongan}', [$sLow, 'update'])->name('staff-lowongan.update');
    Route::delete('/staff-lowongan/{lowongan}', [$sLow, 'destroy'])->name('staff-lowongan.destroy');
});
