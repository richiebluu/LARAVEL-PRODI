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

// Halaman Publik
Route::get('/', [HomeController::class, 'index'])->name('home');

// Profil
Route::get('/profil', [ProgramStudiController::class, 'index'])->name('profil');
Route::get('/visi-misi', fn () => redirect()->to(route('profil').'#visi-misi', 301))->name('visi-misi');
Route::get('/prospek-lulusan', [ProgramStudiController::class, 'prospekLulusan'])->name('prospek-lulusan');
Route::get('/akreditasi', [AkreditasiController::class, 'index'])->name('akreditasi');
Route::get('/struktur-organisasi', [ProgramStudiController::class, 'strukturOrganisasi'])->name('struktur-organisasi');
Route::get('/dosen', [DosenController::class, 'index'])->name('dosen');
Route::get('/mata-kuliah', [\App\Http\Controllers\MataKuliahController::class, 'index'])->name('mata-kuliah');
Route::permanentRedirect('/kurikulum', '/mata-kuliah');
Route::get('/sarana-prasarana', [ProgramStudiController::class, 'saranaPrasarana'])->name('sarana-prasarana');

// Mahasiswa
Route::get('/mahasiswa-berprestasi', [MahasiswaController::class, 'berprestasi'])->name('mahasiswa-berprestasi');
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
Route::get('/kegiatan-mahasiswa', fn () => redirect()->route('berita', ['jenis' => 'kegiatan-mahasiswa'], 301))
    ->name('kegiatan-mahasiswa');
Route::permanentRedirect('/mahasiswa', '/mahasiswa-berprestasi');
Route::permanentRedirect('/prestasi', '/mahasiswa-berprestasi');

// Testimoni
Route::get('/testimoni', [TestimoniController::class, 'index'])->name('testimoni');
Route::permanentRedirect('/testimoni/alumni', '/testimoni');
Route::permanentRedirect('/testimoni/mahasiswa-berprestasi', '/testimoni');

// Informasi
Route::get('/berita', [BeritaController::class, 'index'])->name('berita');
Route::get('/berita/{berita}', [BeritaController::class, 'show'])->name('berita.show');
Route::get('/lowongan-pekerjaan', [LowonganPekerjaanController::class, 'index'])->name('lowongan-pekerjaan');
Route::get('/akamawa', [InformasiController::class, 'akamawa'])->name('akamawa');
Route::get('/kode-etik', [InformasiController::class, 'kodeEtik'])->name('kode-etik');
Route::permanentRedirect('/layanan', '/akamawa');
Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman');

// Bahasa
Route::get('/bahasa/{bahasa}', function (string $bahasa) {
    abort_unless(in_array($bahasa, \App\Http\Middleware\AturBahasa::BAHASA, true), 404);
    session(['bahasa' => $bahasa]);

    return redirect()->back(fallback: route('home'));
})->name('bahasa');

// Login & Logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('login.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('login.google.callback');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Dashboard Mahasiswa
Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    $mhs = \App\Http\Controllers\Mahasiswa\DashboardController::class;

    Route::get('/mahasiswa-dashboard', [$mhs, 'index'])->name('mahasiswa-dashboard');

    Route::get('/mahasiswa-profile', [$mhs, 'profil'])->name('mahasiswa-profile');
    Route::post('/mahasiswa-profile', [$mhs, 'simpanPerubahan'])->name('mahasiswa-profile.simpan');
    Route::post('/mahasiswa-profile/organisasi', [$mhs, 'simpanOrganisasi'])->name('mahasiswa-profile.organisasi');
    Route::put('/mahasiswa-profile/organisasi/{organisasi}', [$mhs, 'ubahOrganisasi'])->name('mahasiswa-profile.organisasi.update');
    Route::delete('/mahasiswa-profile/organisasi/{organisasi}', [$mhs, 'hapusOrganisasi'])->name('mahasiswa-profile.organisasi.destroy');

    Route::get('/mahasiswa-prestasi', [$mhs, 'prestasi'])->name('mahasiswa-prestasi');
    Route::get('/mahasiswa-ajukan-prestasi', [$mhs, 'formPrestasi'])->name('mahasiswa-ajukan-prestasi');
    Route::post('/mahasiswa-ajukan-prestasi', [$mhs, 'simpanPrestasi'])->name('mahasiswa-ajukan-prestasi.store');

    Route::get('/mahasiswa-pengumuman', [$mhs, 'pengumuman'])->name('mahasiswa-pengumuman');
    Route::redirect('/mahasiswa-notifikasi', '/mahasiswa-pengumuman', 301);
});

// Dashboard Staff Prodi
Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff-dashboard', [\App\Http\Controllers\Staff\DashboardController::class, 'index'])
        ->name('staff-dashboard');

    // Data Mahasiswa
    $sMhs = \App\Http\Controllers\Staff\MahasiswaController::class;
    Route::get('/staff-mahasiswa', [$sMhs, 'index'])->name('staff-mahasiswa');
    Route::post('/staff-mahasiswa', [$sMhs, 'store'])->name('staff-mahasiswa.store');
    Route::post('/staff-mahasiswa/impor', [$sMhs, 'impor'])->name('staff-mahasiswa.impor');
    Route::get('/staff-mahasiswa/template', [$sMhs, 'template'])->name('staff-mahasiswa.template');
    Route::put('/staff-mahasiswa/{mahasiswa}', [$sMhs, 'update'])->name('staff-mahasiswa.update');
    Route::delete('/staff-mahasiswa/{mahasiswa}', [$sMhs, 'destroy'])->name('staff-mahasiswa.destroy');

    // Data Dosen
    $sDsn = \App\Http\Controllers\Staff\DosenController::class;
    Route::get('/staff-dosen', [$sDsn, 'index'])->name('staff-dosen');
    Route::post('/staff-dosen', [$sDsn, 'store'])->name('staff-dosen.store');
    Route::post('/staff-dosen/impor', [$sDsn, 'impor'])->name('staff-dosen.impor');
    Route::get('/staff-dosen/template', [$sDsn, 'template'])->name('staff-dosen.template');
    Route::put('/staff-dosen/{dosen}', [$sDsn, 'update'])->name('staff-dosen.update');
    Route::delete('/staff-dosen/{dosen}', [$sDsn, 'destroy'])->name('staff-dosen.destroy');

    // Prestasi
    $sPres = \App\Http\Controllers\Staff\PrestasiController::class;
    Route::get('/staff-prestasi', [$sPres, 'index'])->name('staff-prestasi');
    Route::put('/staff-prestasi/{prestasi}/verifikasi', [$sPres, 'verifikasi'])->name('staff-prestasi.verifikasi');
    Route::delete('/staff-prestasi/{prestasi}', [$sPres, 'destroy'])->name('staff-prestasi.destroy');

    // Ranking
    $sRank = \App\Http\Controllers\Staff\RankingController::class;
    Route::get('/staff-ranking', [$sRank, 'index'])->name('staff-ranking');
    Route::post('/staff-ranking/generate', [$sRank, 'generate'])->name('staff-ranking.generate');
    Route::put('/staff-ranking/dasar-pembobotan', [$sRank, 'simpanDasar'])->name('staff-ranking.dasar');
    Route::delete('/staff-ranking', [$sRank, 'reset'])->name('staff-ranking.reset');

    // Pengumuman
    $sPeng = \App\Http\Controllers\Staff\PengumumanController::class;
    Route::get('/staff-pengumuman', [$sPeng, 'index'])->name('staff-pengumuman');
    Route::get('/staff-pengumuman/cari-mahasiswa', [$sPeng, 'cariMahasiswa'])->name('staff-pengumuman.cari-mahasiswa');
    Route::post('/staff-pengumuman', [$sPeng, 'store'])->name('staff-pengumuman.store');
    Route::put('/staff-pengumuman/{pengumuman}', [$sPeng, 'update'])->name('staff-pengumuman.update');
    Route::delete('/staff-pengumuman/{pengumuman}', [$sPeng, 'destroy'])->name('staff-pengumuman.destroy');

    // Profil Prodi & Profil Saya
    $sProf = \App\Http\Controllers\Staff\ProfilController::class;
    Route::get('/staff-profil', [$sProf, 'index'])->name('staff-profil');
    Route::post('/staff-profil', [$sProf, 'simpan'])->name('staff-profil.simpan');
    Route::get('/staff-profile', [$sProf, 'profilSaya'])->name('staff-profile');
    Route::post('/staff-profile', [$sProf, 'simpanProfilSaya'])->name('staff-profile.simpan');

    // Akreditasi
    $sAkr = \App\Http\Controllers\Staff\AkreditasiController::class;
    Route::get('/staff-akreditasi', [$sAkr, 'index'])->name('staff-akreditasi');
    Route::post('/staff-akreditasi', [$sAkr, 'store'])->name('staff-akreditasi.store');
    Route::put('/staff-akreditasi/{akreditasi}', [$sAkr, 'update'])->name('staff-akreditasi.update');
    Route::delete('/staff-akreditasi/{akreditasi}', [$sAkr, 'destroy'])->name('staff-akreditasi.destroy');

    // Struktur Organisasi
    $sStr = \App\Http\Controllers\Staff\StrukturOrganisasiController::class;
    Route::get('/staff-struktur-organisasi', [$sStr, 'index'])->name('staff-struktur-organisasi');
    Route::post('/staff-struktur-organisasi', [$sStr, 'store'])->name('staff-struktur-organisasi.store');
    Route::put('/staff-struktur-organisasi/{struktur}', [$sStr, 'update'])->name('staff-struktur-organisasi.update');
    Route::delete('/staff-struktur-organisasi/{struktur}', [$sStr, 'destroy'])->name('staff-struktur-organisasi.destroy');

    // Prospek Lulusan
    $sPro = \App\Http\Controllers\Staff\ProspekLulusanController::class;
    Route::get('/staff-prospek-lulusan', [$sPro, 'index'])->name('staff-prospek-lulusan');
    Route::post('/staff-prospek-lulusan', [$sPro, 'store'])->name('staff-prospek-lulusan.store');
    Route::post('/staff-prospek-lulusan/impor', [$sPro, 'impor'])->name('staff-prospek-lulusan.impor');
    Route::get('/staff-prospek-lulusan/template', [$sPro, 'template'])->name('staff-prospek-lulusan.template');
    Route::put('/staff-prospek-lulusan/{prospek}', [$sPro, 'update'])->name('staff-prospek-lulusan.update');
    Route::delete('/staff-prospek-lulusan/{prospek}', [$sPro, 'destroy'])->name('staff-prospek-lulusan.destroy');

    // Mata Kuliah
    $sMk = \App\Http\Controllers\Staff\MataKuliahController::class;
    Route::get('/staff-mata-kuliah', [$sMk, 'index'])->name('staff-mata-kuliah');
    Route::post('/staff-mata-kuliah', [$sMk, 'store'])->name('staff-mata-kuliah.store');
    Route::post('/staff-mata-kuliah/impor', [$sMk, 'impor'])->name('staff-mata-kuliah.impor');
    Route::get('/staff-mata-kuliah/template', [$sMk, 'template'])->name('staff-mata-kuliah.template');
    Route::put('/staff-mata-kuliah/{mataKuliah}', [$sMk, 'update'])->name('staff-mata-kuliah.update');
    Route::delete('/staff-mata-kuliah/{mataKuliah}', [$sMk, 'destroy'])->name('staff-mata-kuliah.destroy');
    Route::redirect('/staff-kurikulum', '/staff-mata-kuliah', 301);

    // Sarana & Prasarana
    $sSar = \App\Http\Controllers\Staff\SaranaPrasaranaController::class;
    Route::get('/staff-sarana-prasarana', [$sSar, 'index'])->name('staff-sarana-prasarana');
    Route::post('/staff-sarana-prasarana', [$sSar, 'store'])->name('staff-sarana-prasarana.store');
    Route::post('/staff-sarana-prasarana/impor', [$sSar, 'impor'])->name('staff-sarana-prasarana.impor');
    Route::get('/staff-sarana-prasarana/template', [$sSar, 'template'])->name('staff-sarana-prasarana.template');
    Route::put('/staff-sarana-prasarana/{sarana}', [$sSar, 'update'])->name('staff-sarana-prasarana.update');
    Route::delete('/staff-sarana-prasarana/{sarana}', [$sSar, 'destroy'])->name('staff-sarana-prasarana.destroy');

    Route::get('/staff-kegiatan-mahasiswa', fn () => redirect()->route('staff-berita', ['jenis' => \App\Models\Berita::JENIS_KEGIATAN], 301))
        ->name('staff-kegiatan-mahasiswa');

    // Testimoni
    $sTes = \App\Http\Controllers\Staff\TestimoniController::class;
    Route::get('/staff-testimoni', [$sTes, 'index'])->name('staff-testimoni');
    Route::post('/staff-testimoni', [$sTes, 'store'])->name('staff-testimoni.store');
    Route::put('/staff-testimoni/{testimoni}', [$sTes, 'update'])->name('staff-testimoni.update');
    Route::delete('/staff-testimoni/{testimoni}', [$sTes, 'destroy'])->name('staff-testimoni.destroy');

    // Berita
    $sBer = \App\Http\Controllers\Staff\BeritaController::class;
    Route::get('/staff-berita', [$sBer, 'index'])->name('staff-berita');
    Route::post('/staff-berita', [$sBer, 'store'])->name('staff-berita.store');
    Route::put('/staff-berita/{berita:id_berita}', [$sBer, 'update'])->name('staff-berita.update');
    Route::delete('/staff-berita/{berita:id_berita}', [$sBer, 'destroy'])->name('staff-berita.destroy');

    // Lowongan Pekerjaan
    $sLow = \App\Http\Controllers\Staff\LowonganPekerjaanController::class;
    Route::get('/staff-lowongan', [$sLow, 'index'])->name('staff-lowongan');
    Route::post('/staff-lowongan', [$sLow, 'store'])->name('staff-lowongan.store');
    Route::put('/staff-lowongan/{lowongan}', [$sLow, 'update'])->name('staff-lowongan.update');
    Route::delete('/staff-lowongan/{lowongan}', [$sLow, 'destroy'])->name('staff-lowongan.destroy');
});
