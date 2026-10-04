<?php

namespace Tests\Feature;

use App\Models\Akreditasi;
use App\Models\Berita;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Organisasi;
use App\Models\Pengumuman;
use App\Models\Prestasi;
use App\Models\SaranaPrasarana;
use App\Models\StrukturOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Revisi02OktoberTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function staff(): User
    {
        return User::where('role', 'staff')->firstOrFail();
    }

    private function mahasiswa(): Mahasiswa
    {
        return Mahasiswa::with('user')->whereHas('user')->firstOrFail();
    }

    public function test_a1_sidebar_staff_dapat_disusutkan_dengan_logo_ti(): void
    {
        $html = $this->actingAs($this->staff())->get('/staff-dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('id="staffSidebar"', $html);
        $this->assertStringContainsString('images/logo-ti.png', $html);
        $this->assertStringContainsString("classList.add('sidebar-bisa-susut')", $html);
        $this->assertStringContainsString('class="sidebar-toggle"', $html);
        $this->assertStringContainsString('<span class="side-text">Dashboard</span>', $html);
        $this->assertStringContainsString('title="Struktur Organisasi"', $html);

        $css = file_get_contents(public_path('admin/css/admin.css'));
        $this->assertStringContainsString('body.sidebar-mini .side-link .side-text{display:none;}', $css);
        $this->assertStringContainsString('body.sidebar-mini .sidebar-brand .sidebar-brand-text{display:none;}', $css);
        $js = file_get_contents(public_path('js/dashboard.js'));
        $this->assertStringContainsString("toggle('sidebar-mini')", $js);
    }

    public function test_a2_dashboard_staff_tanpa_quick_action_dan_alur_sistem(): void
    {
        $this->actingAs($this->staff())->get('/staff-dashboard')->assertOk()
            ->assertDontSee('Quick Action')
            ->assertDontSee('Alur Sistem Prodi TI')
            ->assertSee('Total Mahasiswa')
            ->assertSee('Aktivitas Terbaru');
    }

    public function test_a4_struktur_organisasi_dropdown_jabatan_berhierarki(): void
    {
        $this->actingAs($this->staff());
        $html = $this->get('/staff-struktur-organisasi')->assertOk()->getContent();

        $this->assertStringContainsString('<select name="jabatan" required>', $html);
        $this->assertStringNotContainsString('list="saranJabatan"', $html);
        $posisi = array_map(fn ($j) => strpos($html, '>1. '.$j) ?: strpos($html, '. '.$j.'</option>'), StrukturOrganisasi::daftarJabatan());
        $urut = $posisi;
        sort($urut);
        $this->assertSame($urut, $posisi, 'urutan dropdown mengikuti hierarki');
        $this->assertSame('Koordinator Program Studi', StrukturOrganisasi::daftarJabatan()[0]);

        foreach (['Daftar Dosen', 'Nama Staff', '<label>Foto</label>'] as $label) {
            $this->assertStringContainsString($label, $html, $label);
        }
        foreach (['Pejabat dari Data Dosen', 'Nama Pejabat', 'Foto Pejabat'] as $lama) {
            $this->assertStringNotContainsString($lama, $html, $lama);
        }

        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Raja Prodi', 'nama' => 'X'])->assertSessionHasErrors('jabatan');
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Staff Prodi'])
            ->assertSessionHasErrors(['nama' => 'Pilih dari Daftar Dosen atau isi Nama Staff.']);

        Storage::fake('public');
        $dosen = Dosen::create(['nuptk' => '777001', 'nama' => 'Dr. Kaprodi', 'status' => 'aktif']);
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Staff Prodi', 'nama' => 'Ibu Sylvi, A.Md'])->assertSessionHasNoErrors();
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Gugus TEFA', 'nama' => 'Pak Tefa'])->assertSessionHasNoErrors();
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Gugus Kendali Mutu', 'nama' => 'Bu Mutu',
            'foto' => $this->gambarPalsu('f.jpg')])->assertSessionHasNoErrors();
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Program Studi', 'dosen_id' => $dosen->nuptk])->assertSessionHasNoErrors();
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Sekretaris Program Studi', 'nama' => 'Pak Sekretaris'])->assertSessionHasNoErrors();

        $publik = $this->get('/struktur-organisasi')->assertOk()->getContent();
        $urutanTampil = array_map(fn ($n) => strpos($publik, $n), ['Dr. Kaprodi', 'Pak Sekretaris', 'Pak Tefa', 'Ibu Sylvi, A.Md']);
        $sorted = $urutanTampil;
        sort($sorted);
        $this->assertSame($sorted, $urutanTampil, 'publik tersusun otomatis dari jabatan tertinggi');
        $this->assertSame(4, substr_count($publik, 'class="org-level'), 'tingkat 1,2,3,5');
        $this->assertStringContainsString('org-link-up', $publik);
        $this->assertStringContainsString('fa-caret-down', $publik);
        $this->assertStringContainsString('org-connector', $publik);
        $this->assertMatchesRegularExpression('#Pak Tefa.*?</div>\s*</div>\s*<span class="org-link-down".*?Bu Mutu#s', $publik);

        $s = StrukturOrganisasi::where('nama', 'Bu Mutu')->firstOrFail();
        Storage::disk('public')->assertExists($s->foto);
        $this->put('/staff-struktur-organisasi/'.$s->id_struktur_organisasi, ['jabatan' => 'Koordinator Laboratorium', 'nama' => 'Bu Mutu'])
            ->assertSessionHasNoErrors();
        $this->assertSame(4, $s->fresh()->tingkat);
        $this->delete('/staff-struktur-organisasi/'.$s->id_struktur_organisasi)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('struktur_organisasi', ['id_struktur_organisasi' => $s->id_struktur_organisasi]);
    }

    public function test_a4_jabatan_lama_di_luar_daftar_tetap_tampil_paling_bawah(): void
    {
        StrukturOrganisasi::create(['nama' => 'Pak Lama', 'jabatan' => 'Ketua Panitia']);
        StrukturOrganisasi::create(['nama' => 'Bu Kaprodi', 'jabatan' => 'Koordinator Program Studi']);

        $publik = $this->get('/struktur-organisasi')->assertOk()->getContent();
        $this->assertLessThan(strpos($publik, 'Pak Lama'), strpos($publik, 'Bu Kaprodi'));
        $this->actingAs($this->staff())->get('/staff-struktur-organisasi')->assertSee('Jabatan lama di luar daftar');
    }

    public function test_a7_sarana_prasarana_tanpa_deskripsi_singkat(): void
    {
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'deskripsi'));
        $this->actingAs($this->staff());
        $this->get('/staff-sarana-prasarana')->assertOk()->assertDontSee('Deskripsi Singkat')->assertDontSee('name="deskripsi"', false)
            ->assertSee('name="fasilitas"', false)->assertSee('name="gedung"', false)
            ->assertDontSee('name="lokasi"', false)->assertDontSee('name="jenis"', false);
        foreach (SaranaPrasarana::GEDUNG as $g) {
            $this->get('/staff-sarana-prasarana')->assertSee('<option value="'.$g.'"', false);
        }

        $this->post('/staff-sarana-prasarana', ['nama' => 'Lab Jaringan', 'gedung' => 'Adriansyah 2', 'lokasi' => 'diabaikan', 'kapasitas' => 25,
            'fasilitas' => "Router\nSwitch", 'deskripsi' => 'tidak dipakai', 'status' => 'aktif'])->assertSessionHasNoErrors();
        $lab = SaranaPrasarana::where('nama', 'Lab Jaringan')->firstOrFail();
        $this->assertSame(['Router', 'Switch'], $lab->daftar_fasilitas);
        $this->put('/staff-sarana-prasarana/'.$lab->id_sarana_prasarana, ['nama' => 'Lab Jaringan', 'gedung' => 'Adriansyah 1', 'status' => 'nonaktif'])
            ->assertSessionHasNoErrors();
        $this->delete('/staff-sarana-prasarana/'.$lab->id_sarana_prasarana)->assertSessionHasNoErrors();
    }

    public function test_a8_akreditasi_tanpa_tombol_tambah_di_atas(): void
    {
        $this->actingAs($this->staff());
        $html = $this->get('/staff-akreditasi')->assertOk()->getContent();
        $this->assertStringNotContainsString('href="#tambahAkreditasi"', $html);
        $this->assertSame(1, substr_count($html, 'Tambah Data Akreditasi'));
        $this->assertStringContainsString('id="formAkreditasi"', $html);

        $this->post('/staff-akreditasi', ['peringkat' => 'Unggul', 'lembaga' => 'LAM INFOKOM', 'tanggal_mulai' => '2025-01-01'])->assertSessionHasNoErrors();
        $a = Akreditasi::firstOrFail();
        $this->put('/staff-akreditasi/'.$a->id_akreditasi, ['peringkat' => 'Baik Sekali', 'lembaga' => 'LAM INFOKOM'])->assertSessionHasNoErrors();
        $this->get('/staff-akreditasi')->assertSee('Baik Sekali');
        $this->delete('/staff-akreditasi/'.$a->id_akreditasi)->assertSessionHasNoErrors();
        $this->assertSame(0, Akreditasi::count());
    }

    public function test_a9_dosen_publik_tanpa_alamat_dan_tanggal_lahir(): void
    {
        Dosen::create(['nuptk' => '880001', 'nama' => 'Dr. Rahasia', 'status' => 'aktif', 'alamat' => 'Jl. Rumah Pribadi No. 9', 'tanggal_lahir' => '1980-02-03']);

        $publik = $this->get('/dosen')->assertOk()->getContent();
        $this->assertStringContainsString('Dr. Rahasia', $publik);
        foreach (['Jl. Rumah Pribadi No. 9', 'Alamat:', 'Tanggal Lahir:', '03 Februari 1980'] as $t) {
            $this->assertStringNotContainsString($t, $publik, $t);
        }
        $this->assertSame('Jl. Rumah Pribadi No. 9', Dosen::find('880001')->alamat);
        $this->actingAs($this->staff())->get('/staff-dosen')->assertOk()->assertSee('Jl. Rumah Pribadi No. 9');
    }

    public function test_a6_kurikulum_menjadi_mata_kuliah_di_semua_tempat(): void
    {
        $this->get('/kurikulum')->assertRedirect('/mata-kuliah');
        $publik = $this->get('/mata-kuliah')->assertOk()->getContent();
        $this->assertStringContainsString('<h1>Mata Kuliah</h1>', $publik);
        $this->assertStringContainsString('<span class="current">Mata Kuliah</span>', $publik);

        $this->actingAs($this->staff());
        $this->get('/staff-kurikulum')->assertRedirect('/staff-mata-kuliah');
        $staff = $this->get('/staff-mata-kuliah')->assertOk()->getContent();
        $this->assertStringContainsString('<h1>Mata Kuliah</h1>', $staff);

        foreach (['/', '/profil', '/mata-kuliah', '/berita'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Kurikulum');
        }
        foreach (['/staff-dashboard', '/staff-mata-kuliah'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Kurikulum');
        }
    }

    public function test_a11_data_kegiatan_mahasiswa_lama_pindah_ke_berita_tanpa_hilang(): void
    {
        $sid = $this->staff()->staffProdi->id_staff_prodi;
        Schema::create('kegiatan_mahasiswa', function ($table) {
            $table->id('id_kegiatan_mahasiswa');
            $table->unsignedBigInteger('staff_prodi_id')->nullable();
            $table->string('judul', 150);
            $table->string('kategori', 50);
            $table->date('tanggal');
            $table->string('lokasi', 150)->nullable();
            $table->string('penyelenggara', 150)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->timestamps();
        });
        $id1 = DB::table('kegiatan_mahasiswa')->insertGetId(['staff_prodi_id' => $sid, 'judul' => 'Kunjungan Industri Telkom', 'kategori' => 'Kunjungan Industri',
            'tanggal' => '2026-08-01', 'lokasi' => 'Banjarmasin', 'penyelenggara' => 'Prodi TI', 'deskripsi' => "Paragraf A.\nParagraf B.", 'foto' => 'kegiatan-mahasiswa/a.jpg',
            'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('kegiatan_mahasiswa')->insert(['staff_prodi_id' => $sid, 'judul' => 'Rapat Internal', 'kategori' => 'Lainnya',
            'tanggal' => '2026-08-02', 'status' => 'nonaktif', 'created_at' => now(), 'updated_at' => now()]);
        Berita::create(['judul' => 'Kunjungan Industri Telkom', 'slug' => 'kunjungan-industri-telkom', 'isi' => 'Berita lain', 'tanggal' => '2026-07-01', 'status' => 'terbit']);

        $migrasi = require database_path('migrations/2026_10_04_000001_sinkronkan_struktur_database.php');
        $migrasi->up();
        $migrasi->up();

        $this->assertSame(2, Berita::where('jenis', Berita::JENIS_KEGIATAN)->count());
        $k = Berita::where('jenis', Berita::JENIS_KEGIATAN)->where('judul', 'Kunjungan Industri Telkom')->firstOrFail();
        $this->assertSame('kunjungan-industri-telkom-2', $k->slug, 'slug unik');
        $this->assertSame('terbit', $k->status);
        $this->assertSame('Banjarmasin', $k->lokasi);
        $this->assertSame('Prodi TI', $k->penyelenggara);
        $this->assertSame('kegiatan-mahasiswa/a.jpg', $k->gambar);
        $this->assertSame('Kunjungan Industri', $k->kategori);
        $this->assertSame('draft', Berita::where('judul', 'Rapat Internal')->value('status'));
        $this->assertFalse(Schema::hasTable('kegiatan_mahasiswa'));

        $this->get('/berita?jenis=kegiatan-mahasiswa')->assertOk()->assertSee('Kunjungan Industri Telkom')->assertDontSee('Rapat Internal');
        $this->get('/berita/'.$k->slug)->assertOk()->assertSee('Banjarmasin')->assertSee('Paragraf B.');
    }

    public function test_a11_menu_kegiatan_mahasiswa_tidak_lagi_terpisah(): void
    {
        $this->get('/kegiatan-mahasiswa')->assertRedirect(route('berita', ['jenis' => 'kegiatan-mahasiswa']));
        $home = $this->get('/')->getContent();
        $this->assertStringNotContainsString('/kegiatan-mahasiswa"', $home);

        $this->actingAs($this->staff());
        $this->get('/staff-kegiatan-mahasiswa')->assertRedirect(route('staff-berita', ['jenis' => 'kegiatan_mahasiswa']));
        $staff = $this->get('/staff-berita')->assertOk()->getContent();
        $this->assertStringNotContainsString('/staff-kegiatan-mahasiswa', $staff);
        $this->assertStringContainsString('<select name="jenis" required>', $staff);
        $this->assertStringContainsString('data-tampil-jika="jenis=kegiatan_mahasiswa"', $staff);
    }

    public function test_b1_profil_saya_mahasiswa_update_dan_delete(): void
    {
        $m = $this->mahasiswa();
        $m->update(['no_hp' => '081200000000', 'kelas' => 'TI-3B']);
        $this->actingAs($m->user);

        $html = $this->get('/mahasiswa-profile')->assertOk()->getContent();
        $this->assertStringNotContainsString('title="Update / Edit data diri"', $html);
        $this->assertStringNotContainsString('Hapus Nomor Telepon dan Kelas', $html);
        $this->assertStringContainsString('id="pStatus"', $html);
        $this->assertStringContainsString('Edit Profil', $html);
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('mahasiswa-profile.hapus'));

        $this->post('/mahasiswa-profile', ['email' => $m->user->email, 'no_hp' => '081299999999', 'kelas' => 'TI-3A'])->assertSessionHasNoErrors();
        $this->assertSame('081299999999', $m->fresh()->no_hp);

        $this->post('/mahasiswa-profile/organisasi', ['organisasi' => [['nama_organisasi' => 'HIMA TI', 'jabatan' => 'Anggota']]])->assertSessionHasNoErrors();
        $o = Organisasi::where('nim', $m->nim)->where('nama_organisasi', 'HIMA TI')->firstOrFail();
        $this->get('/mahasiswa-profile')->assertSee(route('mahasiswa-profile.organisasi.update', $o), false)
            ->assertSee(route('mahasiswa-profile.organisasi.destroy', $o), false);
        $this->put('/mahasiswa-profile/organisasi/'.$o->id_organisasi, ['nama_organisasi' => 'HIMA TI', 'jabatan' => 'Raja'])->assertSessionHasErrors('jabatan');
        $this->put('/mahasiswa-profile/organisasi/'.$o->id_organisasi, ['nama_organisasi' => 'HIMA Teknologi Informasi', 'jabatan' => 'Ketua'])->assertSessionHasNoErrors();
        $this->assertSame('Ketua', $o->fresh()->jabatan);

        $lain = Mahasiswa::where('nim', '!=', $m->nim)->firstOrFail();
        $milikLain = Organisasi::create(['nim' => $lain->nim, 'nama_organisasi' => 'BEM', 'jabatan' => 'Anggota']);
        $this->put('/mahasiswa-profile/organisasi/'.$milikLain->id_organisasi, ['nama_organisasi' => 'X', 'jabatan' => 'Ketua'])->assertForbidden();
        $this->delete('/mahasiswa-profile/organisasi/'.$milikLain->id_organisasi)->assertForbidden();

        $this->delete('/mahasiswa-profile/organisasi/'.$o->id_organisasi)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('organisasi', ['id_organisasi' => $o->id_organisasi]);
        $this->assertDatabaseHas('organisasi', ['id_organisasi' => $milikLain->id_organisasi]);
    }

    public function test_b2_upload_bukti_menjadi_upload_sertifikat(): void
    {
        Storage::fake('public');
        $m = $this->mahasiswa();
        $this->actingAs($m->user);
        $this->get('/mahasiswa-ajukan-prestasi')->assertOk()->assertSee('Upload Sertifikat')->assertDontSee('Upload Bukti');

        $this->post('/mahasiswa-ajukan-prestasi', ['judul' => 'Juara 1 UI/UX', 'kategori' => Prestasi::KATEGORI_NON_AKADEMIK, 'tingkat' => 'Nasional',
            'tanggal' => now()->toDateString(), 'dokumen' => UploadedFile::fake()->create('sertifikat.exe', 10)])
            ->assertSessionHasErrors(['dokumen' => 'Sertifikat harus berupa PDF atau gambar (JPG/PNG).']);
        $this->post('/mahasiswa-ajukan-prestasi', ['judul' => 'Juara 1 UI/UX', 'kategori' => Prestasi::KATEGORI_NON_AKADEMIK, 'tingkat' => 'Nasional',
            'tanggal' => now()->toDateString(), 'dokumen' => UploadedFile::fake()->create('sertifikat.pdf', 10, 'application/pdf')])
            ->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists(Prestasi::where('judul', 'Juara 1 UI/UX')->value('dokumen'));
        $this->get('/mahasiswa-prestasi')->assertSee('Lihat sertifikat');
    }

    public function test_b3_notifikasi_digabung_ke_pengumuman(): void
    {
        $m = $this->mahasiswa();
        $this->actingAs($m->user)->post('/mahasiswa-ajukan-prestasi', ['judul' => 'Lomba Gemastik', 'kategori' => Prestasi::KATEGORI_AKADEMIK,
            'tingkat' => 'Nasional', 'tanggal' => now()->toDateString()])->assertSessionHasNoErrors();
        $p = Prestasi::where('judul', 'Lomba Gemastik')->firstOrFail();
        $this->actingAs($this->staff())->put('/staff-prestasi/'.$p->id_prestasi.'/verifikasi', ['status' => 'ditolak', 'catatan' => 'Sertifikat buram'])
            ->assertSessionHasNoErrors();

        $this->actingAs($m->user);
        foreach (['/mahasiswa-dashboard', '/mahasiswa-profile', '/mahasiswa-prestasi', '/mahasiswa-ajukan-prestasi'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('/mahasiswa-notifikasi', $html, $url);
            $this->assertStringNotContainsString('> Notifikasi</a>', $html, $url);
        }
        $this->get('/mahasiswa-notifikasi')->assertRedirect('/mahasiswa-pengumuman');

        $this->get('/mahasiswa-dashboard')->assertSee('Pengumuman Terbaru')->assertSee('Prestasi ditolak');
        $this->assertSame(2, Pengumuman::notifikasiUntuk($m->nim)->whereNull('dibaca_pada')->count());
        $this->get('/mahasiswa-pengumuman')->assertOk()
            ->assertSee('Prestasi sedang diverifikasi')
            ->assertSee('Prestasi ditolak')
            ->assertSee('Sertifikat buram')
            ->assertSee('Informasi Pengajuan')
            ->assertSee('Baru');
        $this->assertSame(0, Pengumuman::notifikasiUntuk($m->nim)->whereNull('dibaca_pada')->count(), 'ditandai dibaca');
        $this->get('/mahasiswa-pengumuman')->assertDontSee('/mahasiswa-notifikasi', false)->assertDontSee('> Notifikasi</a>', false);
        $this->get('/mahasiswa-pengumuman?kategori='.urlencode(Prestasi::KATEGORI_NON_AKADEMIK))->assertDontSee('Prestasi ditolak');

        $lain = Mahasiswa::where('nim', '!=', $m->nim)->whereHas('user')->firstOrFail();
        $this->actingAs($lain->user)->get('/mahasiswa-pengumuman')->assertDontSee('Lomba Gemastik');
        $this->actingAs($this->staff())->get('/staff-pengumuman')->assertDontSee('Prestasi ditolak');
    }

    public function test_c1_media_sosial_memakai_konfigurasi_tanpa_url_karangan(): void
    {
        config(['prodi.sosial_media.instagram.url' => null, 'prodi.sosial_media.youtube.url' => null, 'prodi.sosial_media.tiktok.url' => null]);
        $html = $this->get('/')->assertOk()->getContent();
        foreach (['fa-instagram', 'fa-youtube', 'fa-tiktok'] as $ikon) {
            $this->assertStringContainsString($ikon, $html);
        }
        $this->assertDoesNotMatchRegularExpression('#<a href="\#"[^>]*(social-item|aria-label="(Instagram|YouTube|TikTok))#', $html);

        config([
            'prodi.sosial_media.instagram.url' => 'https://www.instagram.com/akun-uji',
            'prodi.sosial_media.youtube.url' => 'https://www.youtube.com/@akun-uji',
            'prodi.sosial_media.tiktok.url' => 'https://www.tiktok.com/@akun-uji',
        ]);
        $html = $this->get('/')->getContent();
        foreach (['https://www.instagram.com/akun-uji', 'https://www.youtube.com/@akun-uji', 'https://www.tiktok.com/@akun-uji'] as $url) {
            $this->assertSame(2, substr_count($html, 'href="'.$url.'" target="_blank" rel="noopener noreferrer"'), $url.' (footer + beranda)');
        }
        $this->get('/berita')->assertSee('href="https://www.tiktok.com/@akun-uji"', false);
    }

    public function test_c2_menu_induk_navbar_bukan_link_halaman(): void
    {
        $html = $this->get('/')->getContent();
        foreach (['profil', 'mahasiswa', 'informasi'] as $grup) {
            $this->assertMatchesRegularExpression('#<a class="nav-link nav-induk" data-group="'.$grup.'" role="button" tabindex="0"#', $html, $grup);
        }
        $this->assertDoesNotMatchRegularExpression('#<a href="[^"]*" class="nav-link" data-group="(profil|mahasiswa|informasi)"#', $html);
        foreach (['/profil">Tentang', '/mata-kuliah">Mata Kuliah', '/prospek-lulusan">Prospek Lulusan', '/akreditasi">Akreditasi',
            '/mahasiswa-berprestasi">Mahasiswa Berprestasi', '/berita">Berita'] as $sub) {
            $this->assertStringContainsString($sub, $html, $sub);
        }
        $this->assertStringContainsString('class="nav-link" data-group="beranda"', $html);
        $this->assertStringContainsString('/testimoni" class="nav-link" data-group="testimoni"', $html);
    }

    public function test_d_struktur_database_sesudah_revisi(): void
    {
        $this->assertFalse(Schema::hasColumn('prospek_lulusan', 'kategori'));
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'deskripsi'));
        foreach (['jenis', 'lokasi', 'penyelenggara'] as $k) {
            $this->assertTrue(Schema::hasColumn('berita', $k), 'berita.'.$k);
        }
        $this->assertFalse(Schema::hasColumn('berita', 'kegiatan_mahasiswa_id'));
        $this->assertFalse(Schema::hasTable('kegiatan_mahasiswa'));
        $this->assertTrue(Schema::hasTable('pengumuman_penerima'));
        $this->assertTrue(Schema::hasColumn('pengumuman', 'notifikasi'));

        $b = Berita::create(['judul' => 'Tanpa Jenis', 'slug' => 'tanpa-jenis', 'isi' => 'x', 'tanggal' => '2026-10-01', 'status' => 'terbit']);
        $this->assertSame(Berita::JENIS_BERITA, $b->fresh()->jenis);
    }
}
