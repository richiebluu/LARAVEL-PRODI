<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Prestasi;
use App\Models\ProgramStudi;
use App\Models\StrukturOrganisasi;
use App\Models\Testimoni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Revisi27SeptemberTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function staff(): User
    {
        return User::where('role', 'staff')->firstOrFail();
    }

    public function test_struktur_database(): void
    {
        $this->assertTrue(Schema::hasColumn('struktur_organisasi', 'foto'));
        foreach (['tahun_kelulusan', 'nama_perusahaan', 'jabatan', 'foto', 'isi', 'nama'] as $k) {
            $this->assertTrue(Schema::hasColumn('testimoni', $k), $k);
        }
        foreach (['jenis', 'mahasiswa_id', 'keterangan', 'status'] as $k) {
            $this->assertFalse(Schema::hasColumn('testimoni', $k), $k);
        }
        $this->assertFalse(Schema::hasColumn('dosen', 'keahlian'));
        $this->assertFalse(Schema::hasColumn('program_studi', 'link_tutorial_akamawa'));
        $this->assertTrue(Schema::hasColumn('program_studi', 'link_akamawa'));
    }

    public function test_navigasi_informasi_testimoni_dan_profil(): void
    {
        $html = $this->get('/')->getContent();
        foreach (['Tentang', 'Prospek Lulusan', 'Akreditasi', 'Struktur Organisasi', 'Dosen Pengajar',
            'Mahasiswa Berprestasi', 'Ranking Mahasiswa', 'Testimoni', 'Lowongan Kerja',
            'Informasi', 'Berita', 'AKAMAWA', 'Kode Etik Mahasiswa'] as $menu) {
            $this->assertStringContainsString($menu, $html, $menu);
        }
        foreach (['Daftar Mahasiswa Berprestasi', 'Testimoni Mahasiswa Berprestasi', 'AKAMAWA / Tutorial',
            '>Layanan', 'Visi &amp; Misi</a>', 'Kalender Akademik'] as $lama) {
            $this->assertStringNotContainsString($lama, $html, $lama);
        }

        $this->assertMatchesRegularExpression('#Informasi <i[^>]*></i></a>\s*<div class="dropdown">\s*<a href="[^"]*/berita">Berita</a>\s*<a href="[^"]*/akamawa">AKAMAWA</a>\s*<a href="[^"]*/kode-etik">Kode Etik Mahasiswa</a>#', $html);
        $this->assertMatchesRegularExpression('#<a href="[^"]*/testimoni" class="nav-link" data-group="testimoni"[^>]*>Testimoni</a></div>#', $html);
    }

    public function test_redirect_halaman_lama(): void
    {
        $this->get('/layanan')->assertRedirect('/akamawa');
        $this->get('/testimoni/alumni')->assertRedirect('/testimoni');
        $this->get('/testimoni/mahasiswa-berprestasi')->assertRedirect('/testimoni');
        $this->get('/visi-misi')->assertRedirect(route('profil').'#visi-misi');
        $this->get('/profil')->assertOk()->assertSee('id="visi-misi"', false)->assertSee('Misi Program Studi')
            ->assertSee('MAHASISWA AKTIF')->assertSee('ALUMNI')->assertSee('PRESTASI DISETUJUI');
    }

    public function test_akamawa_dan_kode_etik_interaktif(): void
    {
        Storage::fake('public');
        $this->get('/akamawa')->assertOk()->assertSee('Kunjungi Website AKAMAWA')
            ->assertSee(ProgramStudi::LINK_AKAMAWA_BAWAAN)->assertDontSee('Tutorial');
        $this->get('/kode-etik')->assertOk()->assertSee('Kode Etik Mahasiswa')->assertSee('belum diunggah')
            ->assertDontSee('js/kode-etik.js');

        $this->actingAs($this->staff());
        $this->get('/staff-profil')->assertDontSee('link_tutorial_akamawa');
        $this->post('/staff-profil', ['nama_prodi' => 'Teknologi Informasi', 'link_akamawa' => 'https://akamawa.politala.ac.id/',
            'kode_etik' => UploadedFile::fake()->create('kode-etik.pdf', 100, 'application/pdf')])->assertSessionHasNoErrors();

        $lama = ProgramStudi::firstOrFail()->kode_etik;
        Storage::disk('public')->assertExists($lama);

        $this->get('/kode-etik')->assertSee('id="kodetikReader"', false)->assertSee('data-pdf=', false)
            ->assertSee('js/kode-etik.js')->assertSee('Unduh PDF')->assertSee('data-kodetik-fallback', false);

        $this->post('/staff-profil', ['nama_prodi' => 'Teknologi Informasi',
            'kode_etik' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($lama);
    }

    public function test_crud_testimoni_alumni(): void
    {
        Storage::fake('public');
        $this->actingAs($this->staff());

        $this->post('/staff-testimoni', ['isi' => 'Bagus'])->assertSessionHasErrors('nama');
        $this->post('/staff-testimoni', ['nama' => 'X', 'isi' => 'Y', 'tahun_kelulusan' => 1990])
            ->assertSessionHasErrors('tahun_kelulusan');
        $this->post('/staff-testimoni', ['nama' => 'Andi Alumni', 'tahun_kelulusan' => 2023, 'nama_perusahaan' => 'PT Nusantara Digital',
            'jabatan' => 'Web Developer', 'isi' => 'Kuliah di TI sangat membantu karier saya.',
            'foto' => $this->gambarPalsu('andi.jpg')])->assertSessionHasNoErrors();

        $t = Testimoni::firstOrFail();
        Storage::disk('public')->assertExists($t->foto);
        $this->assertSame('Alumni 2023 · Web Developer · PT Nusantara Digital', $t->keterangan_alumni);

        $this->get('/staff-testimoni')->assertOk()->assertSee('Andi Alumni')->assertSee('PT Nusantara Digital')
            ->assertDontSee('Mahasiswa Berprestasi</option>', false);
        $this->get('/staff-testimoni?q=nusantara')->assertSee('Andi Alumni');
        $this->get('/testimoni')->assertOk()->assertSee('Testimoni Alumni')->assertSee('Andi Alumni')
            ->assertSee('Alumni 2023 · Web Developer · PT Nusantara Digital');
        $this->get('/')->assertSee('Andi Alumni')->assertSee('Kata Alumni');

        $this->put('/staff-testimoni/'.$t->id_testimoni, ['nama' => 'Andi Alumni', 'isi' => 'Diperbarui.'])
            ->assertSessionHasNoErrors();
        $this->get('/testimoni')->assertSee('Diperbarui.');
        $this->assertNotNull($t->fresh()->foto, 'foto lama tetap bila tidak diganti');

        $foto = $t->fresh()->foto;
        $this->delete('/staff-testimoni/'.$t->id_testimoni)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('testimoni', ['id_testimoni' => $t->id_testimoni]);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_struktur_organisasi_upload_foto(): void
    {
        Storage::fake('public');
        $this->actingAs($this->staff());
        $dosen = Dosen::create(['nuptk' => '99', 'nama' => 'Dr. Budi', 'status' => 'aktif']);

        $this->get('/staff-struktur-organisasi')->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="foto"', false)->assertSee('Koordinator Gugus TEFA');

        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Gugus TEFA', 'nama' => 'Ibu Rina',
            'foto' => UploadedFile::fake()->create('dok.pdf', 10, 'application/pdf')])->assertSessionHasErrors('foto');
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Gugus TEFA', 'nama' => 'Ibu Rina',
            'foto' => $this->gambarPalsu('rina.png')])->assertSessionHasNoErrors();
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Program Studi', 'dosen_id' => $dosen->nuptk])
            ->assertSessionHasNoErrors();

        $tefa = StrukturOrganisasi::where('jabatan', 'Koordinator Gugus TEFA')->firstOrFail();
        Storage::disk('public')->assertExists($tefa->foto);
        $this->get('/struktur-organisasi')->assertSee($tefa->foto_url)->assertSee('Ibu Rina');
        $this->get('/profil')->assertSee($tefa->foto_url);

        $lama = $tefa->foto;
        $this->put('/staff-struktur-organisasi/'.$tefa->id_struktur_organisasi, ['jabatan' => 'Koordinator Gugus TEFA', 'nama' => 'Ibu Rina',
            'foto' => $this->gambarPalsu('baru.png')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($lama);
        $this->put('/staff-struktur-organisasi/'.$tefa->id_struktur_organisasi, ['jabatan' => 'Koordinator Gugus TEFA', 'nama' => 'Ibu Rina',
            'hapus_foto' => '1'])->assertSessionHasNoErrors();
        $this->assertNull($tefa->fresh()->foto);
    }

    public function test_dosen_tanpa_bidang_keahlian(): void
    {
        $this->actingAs($this->staff());
        $this->post('/staff-dosen', ['nuptk' => '777', 'nama' => 'Dr. Sari', 'email' => 'sari@gmail.com', 'status' => 'aktif'])
            ->assertSessionHasErrors('email');
        $this->post('/staff-dosen', ['nuptk' => '777', 'nama' => 'Dr. Sari', 'jabatan' => 'Lektor', 'email' => 'sari@politala.ac.id',
            'google_scholar' => 'https://scholar.google.com/citations?user=abc', 'status' => 'aktif'])->assertSessionHasNoErrors();

        foreach (['/staff-dosen', '/dosen', '/profil', '/'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Bidang Keahlian')->assertDontSee('name="keahlian"', false);
        }
        $this->get('/dosen')->assertSee('Lihat Detail')->assertSee('Publikasi Google Scholar')
            ->assertSee('https://scholar.google.com/citations?user=abc')->assertSee('sari@politala.ac.id');
    }

    public function test_mahasiswa_berprestasi_semua_tabel_prestasi_terbaru(): void
    {
        $m = Mahasiswa::berprestasi()->with('prestasiDisetujui')->firstOrFail();
        $jumlahAwal = $m->prestasiDisetujui->count();
        Prestasi::create(['nim' => $m->nim, 'judul' => 'Juara Lama Sekali', 'kategori' => Prestasi::KATEGORI_AKADEMIK,
            'tingkat' => 'Regional', 'tanggal' => '2001-01-01', 'status' => Prestasi::STATUS_DISETUJUI]);
        Prestasi::create(['nim' => $m->nim, 'judul' => 'Juara Paling Baru', 'kategori' => Prestasi::KATEGORI_NON_AKADEMIK,
            'tingkat' => 'Nasional', 'tanggal' => now()->addDay()->toDateString(), 'status' => Prestasi::STATUS_DISETUJUI]);

        $html = $this->get('/mahasiswa-berprestasi')->assertOk()->getContent();

        $this->assertStringContainsString('Lihat Prestasi Lainnya', $html);
        $this->assertStringContainsString('Prestasi Terbaru', $html);
        $this->assertStringNotContainsString('Poin Prestasi Akademik', $html);
        $this->assertStringNotContainsString('Poin Prestasi Non-Akademik', $html);
        $this->assertStringNotContainsString('Ranking #', $html);

        [$tabel, $modal] = explode('id="prestasiModals"', $html, 2);
        $this->assertStringContainsString('Juara Paling Baru', $tabel);
        $this->assertStringNotContainsString('Juara Lama Sekali', $tabel);
        $this->assertStringContainsString('Juara Lama Sekali', $modal);
        $baris = explode('<tr class="baris-berprestasi">', $tabel);
        $this->assertStringContainsString('Juara Paling Baru', $baris[1], 'prestasi terbaru di baris pertama');
        $this->assertStringContainsString('(+'.($jumlahAwal + 1).')', $tabel);

        foreach (collect(config('saw.kriteria'))->except('C4') as $k) {
            $card = $this->get('/mahasiswa-berprestasi?kategori='.urlencode($k['nama']))->assertOk()->getContent();
            $this->assertStringContainsString('person-card', $card);
            $this->assertStringNotContainsString('Ranking #', $card);
        }

        $this->actingAs($this->staff())->post('/staff-ranking/generate', ['tahun' => (int) date('Y')])->assertSessionHasNoErrors();
        $this->get('/ranking')->assertOk()->assertSee('rank', false);
    }
}
