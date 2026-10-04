<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\LowonganPekerjaan;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Organisasi;
use App\Models\SaranaPrasarana;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Revisi03OktoberTest extends TestCase
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

    public function test_jenis_berita_berita_prodi_menjadi_kegiatan_prodi(): void
    {
        $this->assertSame('Kegiatan Prodi', Berita::LABEL_JENIS[Berita::JENIS_BERITA]);
        $staff = $this->actingAs($this->staff())->get('/staff-berita')->assertOk()->getContent();
        $this->assertStringContainsString('>Kegiatan Prodi</option>', $staff);
        $this->assertStringNotContainsString('Berita Prodi', $staff);

        Berita::create(['judul' => 'Rapat Kerja Prodi', 'slug' => 'rapat-kerja-prodi', 'isi' => 'Isi', 'tanggal' => '2026-10-01', 'status' => 'terbit']);
        $this->get('/berita')->assertSee('Kegiatan Prodi')->assertDontSee('Berita Prodi');
        $this->get('/berita?jenis=kegiatan-prodi')->assertOk()->assertSee('Rapat Kerja Prodi');
    }

    public function test_sidebar_mahasiswa_dapat_disusutkan(): void
    {
        $this->actingAs($this->mahasiswa()->user);
        foreach (['/mahasiswa-dashboard', '/mahasiswa-profile', '/mahasiswa-prestasi', '/mahasiswa-ajukan-prestasi', '/mahasiswa-pengumuman'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('id="mahasiswaSidebar"', $html, $url);
            $this->assertStringContainsString("classList.add('sidebar-bisa-susut')", $html, $url);
            $this->assertStringContainsString('"tiSidebarMahasiswa"', $html, $url);
            $this->assertStringContainsString('<span class="side-text">Ajukan Prestasi</span>', $html, $url);
            $this->assertStringContainsString('title="Pengumuman"', $html, $url);
            $this->assertStringContainsString('images/logo-ti.png', $html, $url);
        }
    }

    public function test_dashboard_mahasiswa_tanpa_tombol_ajukan_prestasi(): void
    {
        $html = $this->actingAs($this->mahasiswa()->user)->get('/mahasiswa-dashboard')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'href="'.url('/mahasiswa-ajukan-prestasi').'"'));
        $this->assertStringNotContainsString('class="btn btn-light"><i class="fa-solid fa-plus"></i> Ajukan Prestasi', $html);
    }

    public function test_lowongan_tanpa_status_dan_lowongan_nonaktif_lama_tetap_tersembunyi(): void
    {
        $this->assertFalse(Schema::hasColumn('lowongan_pekerjaan', 'status'));
        $this->actingAs($this->staff());
        $this->get('/staff-lowongan')->assertOk()->assertDontSee('name="status"', false)->assertSee('Batas Lamaran');
        $this->post('/staff-lowongan', ['posisi' => 'Backend Dev', 'perusahaan' => 'PT A', 'link' => 'https://contoh.id/a',
            'batas_lamaran' => now()->addDays(3)->format('Y-m-d')])->assertSessionHasNoErrors();
        $this->get('/lowongan-pekerjaan')->assertSee('Backend Dev');

        $migrasi = require database_path('migrations/2026_10_04_000001_sinkronkan_struktur_database.php');
        Schema::table('lowongan_pekerjaan', fn ($t) => $t->string('status', 20)->default('aktif'));
        DB::table('lowongan_pekerjaan')->insert(['posisi' => 'Lama Nonaktif', 'perusahaan' => 'PT B', 'link' => 'https://contoh.id/b',
            'status' => 'nonaktif', 'created_at' => now(), 'updated_at' => now()]);
        $migrasi->up();
        $this->assertFalse(Schema::hasColumn('lowongan_pekerjaan', 'status'));
        $this->assertSame(2, LowonganPekerjaan::count(), 'tidak ada data yang dihapus');
        $this->get('/lowongan-pekerjaan')->assertSee('Backend Dev')->assertDontSee('Lama Nonaktif');
    }

    public function test_mata_kuliah_publik_tanpa_keterangan_sipadu(): void
    {
        MataKuliah::create(['kode_mata_kuliah' => 'TI101', 'nama' => 'Algoritma', 'semester' => 1, 'sks' => 3, 'jenis' => 'Wajib']);
        $this->get('/mata-kuliah')->assertOk()->assertSee('Algoritma')->assertDontSee('SIPADU');
    }

    public function test_sarana_prasarana_gedung_tanpa_lokasi(): void
    {
        $this->assertTrue(Schema::hasColumn('sarana_prasarana', 'gedung'));
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'jenis'));
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'lokasi'));
        $this->assertSame(['Gedung Teknik Informatika', 'Adriansyah 1', 'Adriansyah 2'], SaranaPrasarana::GEDUNG);

        $this->actingAs($this->staff());
        $html = $this->get('/staff-sarana-prasarana')->assertOk()->getContent();
        $this->assertStringContainsString('<label>Gedung *</label>', $html);
        $this->assertStringNotContainsString('<label>Lokasi</label>', $html);
        $this->assertStringNotContainsString('<label>Jenis *</label>', $html);

        $this->post('/staff-sarana-prasarana', ['nama' => 'Lab Jaringan', 'gedung' => 'Gedung Lain', 'status' => 'aktif'])->assertSessionHasErrors('gedung');
        $this->post('/staff-sarana-prasarana', ['nama' => 'Lab Jaringan', 'gedung' => 'Adriansyah 2', 'kapasitas' => 30, 'status' => 'aktif'])->assertSessionHasNoErrors();
        $this->post('/staff-sarana-prasarana', ['nama' => 'Lab Pemrograman', 'gedung' => 'Gedung Teknik Informatika', 'status' => 'aktif'])->assertSessionHasNoErrors();

        $publik = $this->get('/sarana-prasarana')->assertOk()->getContent();
        $this->assertLessThan(strpos($publik, 'Lab Jaringan'), strpos($publik, 'Lab Pemrograman'), 'urut gedung');
        $this->assertStringContainsString('Adriansyah 2', $publik);
        $this->get('/sarana-prasarana?gedung=Adriansyah%202')->assertSee('Lab Jaringan')->assertDontSee('Lab Pemrograman');
    }

    public function test_migrasi_gedung_memetakan_lokasi_lama_tanpa_menghapus_data(): void
    {
        $migrasi = require database_path('migrations/2026_10_04_000001_sinkronkan_struktur_database.php');
        Schema::table('sarana_prasarana', fn ($t) => $t->dropColumn('gedung'));
        Schema::table('sarana_prasarana', function ($t) {
            $t->string('jenis', 50)->nullable();
            $t->string('lokasi', 150)->nullable();
        });
        $this->assertTrue(Schema::hasColumn('sarana_prasarana', 'lokasi'));
        foreach ([['Lab A', 'Gedung Adriansyah 2 Lt. 1'], ['Lab B', 'Gedung TI Lantai 2'], ['Ruang C', 'Gedung Teknik Informatika'], ['Ruang D', 'Kampus utama']] as [$n, $l]) {
            DB::table('sarana_prasarana')->insert(['nama' => $n, 'jenis' => 'Laboratorium', 'lokasi' => $l, 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()]);
        }
        $migrasi->up();

        $this->assertSame('Adriansyah 2', SaranaPrasarana::where('nama', 'Lab A')->value('gedung'));
        $this->assertSame('Gedung Teknik Informatika', SaranaPrasarana::where('nama', 'Lab B')->value('gedung'));
        $this->assertSame('Gedung Teknik Informatika', SaranaPrasarana::where('nama', 'Ruang C')->value('gedung'));
        $this->assertNull(SaranaPrasarana::where('nama', 'Ruang D')->value('gedung'));
        $this->assertSame(4, SaranaPrasarana::count());
        $this->actingAs($this->staff())->get('/staff-sarana-prasarana')->assertOk()->assertSee('Belum dipilih');
    }

    public function test_mahasiswa_berprestasi_tanpa_kategori_keaktifan_organisasi(): void
    {
        $html = $this->get('/mahasiswa-berprestasi')->assertOk()->getContent();
        $this->assertStringNotContainsString('kategori=Keaktifan', $html);
        $this->assertStringContainsString('kategori=Prestasi%20Akademik', $html);
        $this->get('/mahasiswa-berprestasi?kategori=Keaktifan%20Organisasi')->assertOk()->assertSee('class="tabel-publik"', false);
    }

    public function test_akamawa_bertampilan_seperti_halaman_pengumuman(): void
    {
        $html = $this->get('/akamawa')->assertOk()->getContent();
        $this->assertStringContainsString('<div class="card reveal" id="akamawa" style="padding:44px; text-align:center; max-width:720px; margin:0 auto;">', $html);
        $this->assertStringContainsString('Kunjungi Website AKAMAWA', $html);
        $this->assertStringNotContainsString('grid-2', $html);
    }

    public function test_tambah_keaktifan_organisasi_fleksibel_lebih_dari_dua(): void
    {
        $m = $this->mahasiswa();
        Organisasi::where('nim', $m->nim)->delete();
        Organisasi::create(['nim' => $m->nim, 'nama_organisasi' => 'HIMA TI', 'jabatan' => 'Anggota']);
        $this->actingAs($m->user);

        $html = $this->get('/mahasiswa-profile')->assertOk()->getContent();
        $this->assertStringContainsString('<i class="fa-solid fa-plus"></i> Tambah Keaktifan Organisasi', $html);
        $this->assertStringNotContainsString('Input Keaktifan Organisasi', $html);
        $this->assertStringContainsString('data-baris-dinamis', $html);
        $this->assertStringContainsString('data-tambah-baris', $html);
        $this->assertStringContainsString('organisasi[__i__][nama_organisasi]', $html);
        $this->assertStringNotContainsString('maksimal 2', $html);

        $this->post('/mahasiswa-profile/organisasi', ['organisasi' => [
            ['nama_organisasi' => 'BEM', 'jabatan' => 'Ketua'],
            ['nama_organisasi' => 'UKM Robotik', 'jabatan' => 'Sekretaris'],
            ['nama_organisasi' => 'UKM Fotografi', 'jabatan' => 'Anggota'],
        ]])->assertSessionHasNoErrors()->assertSessionHas('success', '3 organisasi berhasil ditambahkan. Poin dihitung pada perhitungan ranking berikutnya.');
        $this->assertSame(4, Organisasi::where('nim', $m->nim)->count());

        $this->post('/mahasiswa-profile/organisasi', ['organisasi' => [['nama_organisasi' => '', 'jabatan' => '']]])->assertSessionHasErrors('organisasi');
        $this->post('/mahasiswa-profile/organisasi', [])->assertSessionHasErrors('organisasi');

        $this->actingAs($this->staff());
        $staff = $this->get('/staff-mahasiswa?q='.$m->nim)->assertOk()->getContent();
        $this->assertStringContainsString('name="organisasi[3][nama_organisasi]"', $staff);
    }

    public function test_profil_saya_data_mahasiswa_tanpa_ikon_edit_hapus(): void
    {
        $html = $this->actingAs($this->mahasiswa()->user)->get('/mahasiswa-profile')->assertOk()->getContent();
        [$dataMahasiswa] = explode('Keaktifan Organisasi</h2>', explode('<h2>Data Mahasiswa</h2>', $html, 2)[1], 2);
        $this->assertStringNotContainsString('btn-icon', $dataMahasiswa);
        $this->assertStringContainsString('badge badge-green', $dataMahasiswa);
    }

    public function test_ikon_prospek_lulusan_tampil_nama_dan_penjelasan_bukan_kelas_mentah(): void
    {
        $this->assertSame(array_keys(\App\Models\ProspekLulusan::IKON), array_keys(\App\Models\ProspekLulusan::IKON_DESKRIPSI));
        \App\Models\ProspekLulusan::create(['nama' => 'Penetration Tester', 'ikon' => 'fa-user-secret', 'status' => 'aktif']);

        $html = $this->actingAs($this->staff())->get('/staff-prospek-lulusan')->assertOk()->getContent();
        $this->assertStringContainsString('type="hidden" name="ikon"', $html);
        $this->assertStringContainsString('data-ikon-info="Biasanya digunakan untuk hal yang berhubungan dengan keamanan, hacking', $html);
        $this->assertStringContainsString('data-ikon-label="Ethical Hacking"', $html);
        $this->assertStringContainsString('<dd><i class="fa-solid fa-user-secret" style="color:var(--blue-600);"></i> Ethical Hacking</dd>', $html);
        $this->assertStringNotContainsString('title="fa-', $html);
        $this->assertStringNotContainsString('<code>fa-', $html);
        $this->assertStringNotContainsString('placeholder="Contoh: fa-code"', $html);
        $this->assertDoesNotMatchRegularExpression('#>\s*fa-[a-z-]+\s*<#', $html);
    }
}
