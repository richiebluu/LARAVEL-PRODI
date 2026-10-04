<?php

namespace Tests\Feature;

use App\Http\Controllers\Staff\RankingController;
use App\Mail\PengumumanMahasiswaBerprestasi;
use App\Models\Akreditasi;
use App\Models\Mahasiswa;
use App\Models\Pengumuman;
use App\Models\ProgramStudi;
use App\Models\Ranking;
use App\Models\RankingBobot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Revisi01OktoberTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function staff(): User
    {
        return User::where('role', 'staff')->firstOrFail();
    }

    public function test_akreditasi_utama_berdasarkan_status_dan_tanggal(): void
    {
        $prodi = ProgramStudi::create(['nama_prodi' => 'Teknologi Informasi']);
        $buat = fn (array $d) => Akreditasi::create($d + ['program_studi_id' => $prodi->id_program_studi]);

        $c = $buat(['peringkat' => 'Unggul', 'nomor_sk' => 'SK-C', 'tanggal_mulai' => now()->subMonths(2)->toDateString(), 'tanggal_berakhir' => now()->addYears(5)->toDateString()]);
        $b = $buat(['peringkat' => 'Baik Sekali', 'nomor_sk' => 'SK-B', 'tanggal_mulai' => now()->subYears(2)->toDateString(), 'tanggal_berakhir' => now()->addYears(3)->toDateString()]);
        $a = $buat(['peringkat' => 'Tidak Terakreditasi', 'nomor_sk' => 'SK-A', 'tanggal_mulai' => now()->subYears(4)->toDateString()]);
        $lama = $buat(['peringkat' => 'B', 'nomor_sk' => 'SK-LAMA', 'tanggal_mulai' => '2015-01-01', 'tanggal_berakhir' => '2020-01-01']);
        $nanti = $buat(['peringkat' => 'Unggul', 'nomor_sk' => 'SK-NANTI', 'tanggal_mulai' => now()->addMonth()->toDateString()]);

        $this->assertSame(Akreditasi::STATUS_TIDAK_TERAKREDITASI, $a->status);
        $this->assertSame(Akreditasi::STATUS_TERAKREDITASI, $b->status);
        $this->assertSame(Akreditasi::STATUS_BERAKHIR, $lama->status);
        $this->assertSame(Akreditasi::STATUS_BELUM_BERLAKU, $nanti->status);

        $this->assertTrue(Akreditasi::utama()->is($c));
        $this->assertSame([$c->id_akreditasi, $b->id_akreditasi], Akreditasi::terakreditasi()->terbaru()->pluck('id_akreditasi')->all());

        $this->get('/akreditasi')->assertOk()->assertSee('SK-C')->assertSee('Unggul');
        $this->get('/')->assertOk()->assertSee('SK-C');
        $this->actingAs($this->staff())->get('/staff-akreditasi')->assertOk()->assertSee('Tampil di publik')->assertSee('SK-LAMA');

        Akreditasi::whereIn('id_akreditasi', [$b->id_akreditasi, $c->id_akreditasi])->delete();
        $this->assertTrue(Akreditasi::utama()->is($nanti));
        $this->assertFalse(Akreditasi::utama()->berlaku);
    }

    public function test_data_mahasiswa_default_10_dan_pilihan_per_halaman(): void
    {
        $this->actingAs($this->staff());
        $total = Mahasiswa::count();

        $res = $this->get('/staff-mahasiswa')->assertOk();
        $this->assertSame(10, $res->viewData('daftarMahasiswa')->perPage());
        $res->assertSee('Tampilkan')->assertSee('name="per_page"', false);

        foreach ([5, 10, 15, 20] as $n) {
            $hal = $this->get('/staff-mahasiswa?per_page='.$n)->assertOk()->viewData('daftarMahasiswa');
            $this->assertSame($n, $hal->perPage());
            $this->assertCount(min($n, $total), $hal->items());
        }

        $this->assertSame(10, $this->get('/staff-mahasiswa?per_page=999')->viewData('daftarMahasiswa')->perPage());

        $hal = $this->get('/staff-mahasiswa?per_page=5&q=a')->viewData('daftarMahasiswa');
        if ($hal->hasMorePages()) {
            $this->assertStringContainsString('per_page=5', $hal->nextPageUrl());
            $this->assertStringContainsString('q=a', $hal->nextPageUrl());
        }
        $this->get('/staff-mahasiswa?per_page=20&page=2')->assertOk()->assertSee('Menampilkan 21');
    }

    public function test_perhitungan_ahp_di_controller(): void
    {
        $ahp = app(RankingController::class)->hitungAHP(['C1', 'C2', 'C3', 'C4'], config('saw.ahp.perbandingan'));

        $this->assertSame(1.0, $ahp['matriks']['C1']['C1']);
        $this->assertEqualsWithDelta(0.5, $ahp['matriks']['C2']['C1'], 1e-9);
        $this->assertEqualsWithDelta(0.2, $ahp['matriks']['C4']['C1'], 1e-9);

        foreach ($ahp['kode'] as $kolom) {
            $this->assertEqualsWithDelta(1.0, array_sum(array_column($ahp['normalisasi'], $kolom)), 1e-9);
        }
        $this->assertEqualsWithDelta(1.0, array_sum($ahp['priority_vector']), 1e-9);

        $this->assertEqualsWithDelta(0.4758, $ahp['priority_vector']['C1'], 0.0001);
        $this->assertEqualsWithDelta(0.2884, $ahp['priority_vector']['C2'], 0.0001);
        $this->assertEqualsWithDelta(0.1544, $ahp['priority_vector']['C3'], 0.0001);
        $this->assertEqualsWithDelta(0.0813, $ahp['priority_vector']['C4'], 0.0001);
        $this->assertEqualsWithDelta(4.0211, $ahp['lambda_max'], 0.0001);
        $this->assertEqualsWithDelta(0.0078, $ahp['cr'], 0.0001);
        $this->assertTrue($ahp['konsisten']);

        foreach (config('saw.kriteria') as $kode => $k) {
            $this->assertSame($k['bobot'], $ahp['bobot_dibulatkan'][$kode]);
        }

        $buruk = app(RankingController::class)->hitungAHP(['A', 'B', 'C'], ['A' => ['B' => 9, 'C' => 1 / 9], 'B' => ['C' => 9]]);
        $this->assertFalse($buruk['konsisten']);
    }

    public function test_halaman_ranking_menampilkan_ahp_dan_dasar_pembobotan(): void
    {
        $this->actingAs($this->staff());

        $this->assertNotNull(RankingBobot::where('kode', 'C1')->value('dasar_pembobotan'));

        $this->get('/staff-ranking')->assertOk()
            ->assertSee('Perhitungan Bobot AHP')
            ->assertSee('Matriks Perbandingan Berpasangan')
            ->assertSee('0.0078')
            ->assertSee('Dasar Pembobotan')
            ->assertSee('Bobot tersimpan = hasil AHP');

        $dasar = [];
        foreach (array_keys(config('saw.kriteria')) as $k) {
            $dasar[$k] = 'Alasan baru untuk kriteria '.$k.' sebagai bahan laporan proyek.';
        }
        $this->put('/staff-ranking/dasar-pembobotan', ['dasar' => $dasar])->assertSessionHasNoErrors();
        $this->assertSame($dasar['C2'], RankingBobot::where('kode', 'C2')->value('dasar_pembobotan'));
        $this->assertEqualsWithDelta(0.29, (float) RankingBobot::where('kode', 'C2')->value('bobot'), 1e-9);
        $this->put('/staff-ranking/dasar-pembobotan', ['dasar' => ['C1' => 'pendek']])->assertSessionHasErrors();

        RankingBobot::where('kode', 'C1')->update(['bobot' => 0.10]);
        $this->get('/staff-ranking')->assertSee('belum sama dengan AHP');
        $this->post('/staff-ranking/generate', ['tahun' => date('Y')])->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(0.48, (float) RankingBobot::where('kode', 'C1')->value('bobot'), 1e-9);
        $this->assertGreaterThan(0, Ranking::count());

        $mhs = User::where('role', 'mahasiswa')->firstOrFail();
        $this->actingAs($mhs)->put('/staff-ranking/dasar-pembobotan', ['dasar' => $dasar])->assertRedirect('/mahasiswa-dashboard');
    }

    public function test_pengumuman_untuk_banyak_mahasiswa(): void
    {
        Mail::fake();
        $this->actingAs($this->staff());

        $berprestasi = Mahasiswa::berprestasi()->with('user')->orderBy('nama')->take(3)->get();
        $this->assertCount(3, $berprestasi);
        $lain = Mahasiswa::whereNotIn('nim', $berprestasi->pluck('nim'))->with('user')->firstOrFail();
        $nim = $berprestasi->pluck('nim')->all();

        $this->post('/staff-pengumuman', [
            'penerima' => $nim, 'kategori' => 'Prestasi Akademik',
            'judul' => 'Pengumuman Mahasiswa Berprestasi', 'isi' => 'Selamat untuk kalian semua.', 'status' => 'terkirim',
        ])->assertSessionHasNoErrors();

        $g = Pengumuman::where('judul', 'Pengumuman Mahasiswa Berprestasi')->firstOrFail();
        $this->assertSame(1, Pengumuman::where('judul', 'Pengumuman Mahasiswa Berprestasi')->count(), 'satu pengumuman, bukan duplikat');
        $this->assertEqualsCanonicalizing($nim, $g->penerima->pluck('nim')->all());
        $this->assertSame(3, DB::table('pengumuman_penerima')->where('pengumuman_id', $g->id_pengumuman)->count());
        Mail::assertSent(PengumumanMahasiswaBerprestasi::class, 3);

        foreach ($berprestasi as $m) {
            $this->actingAs($m->user)->get('/mahasiswa-dashboard')->assertSee('Pengumuman Mahasiswa Berprestasi');
        }
        $this->actingAs($lain->user)->get('/mahasiswa-pengumuman')->assertDontSee('Selamat untuk kalian semua.');
        $this->actingAs($lain->user)->get('/mahasiswa-dashboard')->assertDontSee('Pengumuman Mahasiswa Berprestasi');

        $this->actingAs($berprestasi[0]->user)->get('/mahasiswa-pengumuman')->assertSee('Selamat untuk kalian semua.');
        $this->assertNotNull($g->penerima()->where('mahasiswa.nim', $nim[0])->first()->pivot->dibaca_pada);
        $this->assertNull($g->penerima()->where('mahasiswa.nim', $nim[1])->first()->pivot->dibaca_pada);

        $this->actingAs($this->staff())->put('/staff-pengumuman/'.$g->id_pengumuman, [
            'penerima' => [$nim[0], $nim[1]], 'kategori' => 'Prestasi Akademik',
            'judul' => 'Pengumuman Mahasiswa Berprestasi', 'isi' => 'Selamat untuk kalian semua.', 'status' => 'terkirim',
        ])->assertSessionHasNoErrors();
        $this->assertEqualsCanonicalizing([$nim[0], $nim[1]], $g->fresh()->penerima->pluck('nim')->all());
        $this->actingAs($berprestasi[2]->user)->get('/mahasiswa-pengumuman')->assertDontSee('Selamat untuk kalian semua.');

        $this->actingAs($this->staff());
        $data = ['kategori' => 'Prestasi Akademik', 'judul' => 'X', 'isi' => 'Y', 'status' => 'draft'];
        $this->post('/staff-pengumuman', $data + ['penerima' => [$nim[0], $nim[0]]])->assertSessionHasErrors('penerima.1');
        $this->post('/staff-pengumuman', $data + ['penerima' => []])->assertSessionHasErrors('penerima');
        if (! $lain->prestasiDisetujui()->exists()) {
            $this->post('/staff-pengumuman', $data + ['penerima' => [$lain->nim]])->assertSessionHasErrors('penerima');
        }

        $this->get('/staff-pengumuman')->assertOk()->assertSee('2 mahasiswa');
    }

    public function test_cari_mahasiswa_berdasarkan_email_domain_politala(): void
    {
        $this->actingAs($this->staff());
        $m = Mahasiswa::berprestasi()->with('user')->firstOrFail();
        $email = $m->email_kontak;
        $this->assertStringEndsWith('@mhs.politala.ac.id', $email);

        $json = $this->getJson('/staff-pengumuman/cari-mahasiswa?q='.urlencode($email))->assertOk()->json();
        $this->assertSame($m->nim, $json['data'][0]['nim']);
        $this->assertTrue($json['data'][0]['bisa_dipilih']);
        $this->assertSame($email, $json['data'][0]['email']);

        $json = $this->getJson('/staff-pengumuman/cari-mahasiswa?q='.urlencode($email).'&kecuali[]='.$m->nim)->json();
        $this->assertNotContains($m->nim, array_column($json['data'], 'nim'));

        $this->assertNotNull($this->getJson('/staff-pengumuman/cari-mahasiswa?q=budi@gmail.com')->json('pesan'));
        $res = $this->getJson('/staff-pengumuman/cari-mahasiswa?q=tidakadaorangini@mhs')->json();
        $this->assertSame([], $res['data']);
        $this->assertStringContainsString('tidak ditemukan', $res['pesan']);

        $this->actingAs($m->user)->get('/staff-pengumuman/cari-mahasiswa?q=a')->assertRedirect('/mahasiswa-dashboard');
    }

    public function test_hero_halaman_publik_memakai_foto(): void
    {
        foreach (['/profil', '/akreditasi', '/mata-kuliah', '/prospek-lulusan', '/dosen', '/mahasiswa-berprestasi',
            '/ranking', '/testimoni', '/berita', '/lowongan-pekerjaan', '/akamawa', '/kode-etik',
            '/pengumuman', '/struktur-organisasi', '/sarana-prasarana'] as $url) {
            $this->get($url)->assertOk()->assertSee('page-hero page-hero--foto', false)->assertSee('--hero-foto:url(', false);
        }
        $this->get('/')->assertOk()->assertSee('hero-slide active', false);
    }
}
