<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Organisasi;
use App\Models\Pengumuman;
use App\Models\Ranking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevisiSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function staff(): User
    {
        return User::where('role', 'staff')->firstOrFail();
    }

    public function test_halaman_publik(): void
    {
        foreach (['/', '/profil', '/prospek-lulusan', '/akreditasi', '/struktur-organisasi', '/dosen',
            '/mahasiswa-berprestasi', '/ranking', '/testimoni',
            '/berita', '/lowongan-pekerjaan', '/akamawa', '/kode-etik', '/pengumuman', '/login'] as $url) {
            $r = $this->get($url);
            $r->assertOk();
            $html = $r->getContent();
            $this->assertStringNotContainsString('kalender-akademik', $html, $url);
            $this->assertDoesNotMatchRegularExpression('#localhost(:\d+)?/prestasi"#', $html, $url);
            $this->assertDoesNotMatchRegularExpression('#localhost(:\d+)?/mahasiswa"#', $html, $url);
            $this->assertStringContainsString('images/logo-ti.png', $html, $url);
        }

        $this->get('/mata-kuliah')->assertOk();
        $this->get('/kurikulum')->assertRedirect('/mata-kuliah');
        $this->get('/prestasi')->assertRedirect('/mahasiswa-berprestasi');
        $this->get('/mahasiswa')->assertRedirect('/mahasiswa-berprestasi');

        $this->get('/')->assertSee('Peringkat Berdasarkan Mahasiswa Berprestasi');
        $this->get('/dosen')->assertSee('tabel-publik', false)->assertSee('Dosen Pengajar');

        $semua = $this->get('/mahasiswa-berprestasi')->getContent();
        $this->assertStringContainsString('class="tabel-publik"', $semua);
        $this->assertStringNotContainsString('kategori=Keaktifan', $semua);
        foreach (collect(config('saw.kriteria'))->except('C4') as $k) {
            $html = $this->get('/mahasiswa-berprestasi?kategori='.urlencode($k['nama']))->assertOk()->getContent();
            $this->assertStringContainsString('person-card', $html, $k['nama']);
            $this->assertStringNotContainsString('class="tabel-publik"', $html, $k['nama']);
        }
    }

    public function test_login_berdasarkan_role(): void
    {
        $mhs = Mahasiswa::first()->user;

        $this->post('/login', ['role' => 'dosen', 'email' => $mhs->email, 'password' => 'password123'])
            ->assertSessionHasErrors('role');
        $this->assertGuest();

        $this->post('/login', ['role' => 'mahasiswa', 'email' => 'staff@politala.ac.id', 'password' => 'password123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $mhs->email, 'password' => 'password123'])->assertSessionHasErrors('role');

        $this->post('/login', ['role' => 'mahasiswa', 'email' => $mhs->email, 'password' => 'password123'])
            ->assertRedirect('/mahasiswa-dashboard');
        $this->assertAuthenticatedAs($mhs);

        $this->get('/staff-dashboard')->assertRedirect('/mahasiswa-dashboard');
        $this->get('/dosen-dashboard')->assertNotFound();

        $this->post('/logout');
        $this->post('/login', ['role' => 'staff', 'email' => 'staff@politala.ac.id', 'password' => 'password123'])
            ->assertRedirect('/staff-dashboard');
    }

    public function test_alur_staff_dan_data_master_dosen(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff);

        foreach (['/staff-dashboard', '/staff-profile', '/staff-profil', '/staff-struktur-organisasi', '/staff-dosen',
            '/staff-mahasiswa', '/staff-prestasi', '/staff-ranking', '/staff-pengumuman', '/staff-akreditasi',
            '/staff-testimoni', '/staff-berita', '/staff-lowongan'] as $url) {
            $this->get($url)->assertOk()->assertSee('DATA MASTER')->assertDontSee('staff-verifikasi');
        }
        $this->get('/staff-verifikasi')->assertNotFound();
        $this->get('/staff-prestasi')->assertDontSee('Approved');
        $this->get('/staff-dosen')->assertSee('Pendidikan Terakhir')->assertDontSee('Riwayat Pendidikan')
            ->assertDontSee('name="password"', false);
        $this->get('/staff-mahasiswa')->assertSee('DISPEN')->assertSee('>DO<', false)->assertDontSee('email_akun', false);

        $this->post('/staff-profile', ['nama' => 'Sylvi, A.Md', 'jabatan' => 'Staff Prodi', 'email' => 'staff@politala.ac.id', 'no_hp' => '08abc'])
            ->assertSessionHasErrors('no_hp');
        $this->post('/staff-profile', ['nama' => 'Sylvi, A.Md', 'jabatan' => 'Staff Prodi', 'email' => 'staff@politala.ac.id', 'no_hp' => '081234567890'])
            ->assertSessionHasNoErrors()->assertRedirect('/staff-profile');
        $this->assertSame('081234567890', $staff->fresh()->staffProdi->no_hp);

        $jumlahUser = User::count();
        $this->post('/staff-dosen', ['nuptk' => '1', 'nama' => 'Dr. A', 'jabatan' => 'Lektor', 'email' => 'a@politala.ac.id', 'status' => 'aktif',
            'pendidikan_terakhir' => 'S3 Informatika', 'google_scholar' => 'https://scholar.google.com/citations?user=abc'])
            ->assertSessionHasNoErrors();
        $this->assertSame($jumlahUser, User::count());
        $dosen = Dosen::where('nuptk', '1')->firstOrFail();
        $this->assertSame('S3 Informatika', $dosen->pendidikan_terakhir);
        $this->assertNull($dosen->getAttribute('jabatan'), 'Jabatan dosen dihapus (revisi 28-09)');

        $this->put('/staff-dosen/'.$dosen->nuptk, ['nuptk' => '1', 'nama' => 'Dr. A', 'email' => 'a2@politala.ac.id', 'status' => 'aktif',
            'pendidikan_terakhir' => 'S3 Informatika', 'google_scholar' => 'https://scholar.google.com/citations?user=abc'])
            ->assertSessionHasNoErrors();
        $this->assertSame('a2@politala.ac.id', $dosen->fresh()->email);

        $this->get('/dosen')->assertSee('Lihat Detail')->assertSee('Publikasi Google Scholar')->assertSee('scholar.google.com/citations?user=abc');
        $this->get('/profil')->assertSee('Dosen Pengajar')->assertSee('Dr. A')->assertSee('Struktur Organisasi');

        foreach (['do', 'dispen'] as $i => $st) {
            $this->post('/staff-mahasiswa', ['nim' => '99'.$i, 'nama' => 'M '.$st, 'email' => '99'.$i.'@mhs.politala.ac.id',
                'status_mahasiswa' => $st, 'password' => 'rahasia123'])->assertSessionHasNoErrors();
            $this->assertSame($st, Mahasiswa::where('nim', '99'.$i)->value('status_mahasiswa'));
        }
        $this->post('/staff-mahasiswa', ['nim' => '980', 'nama' => 'X', 'email' => 'x@politala.ac.id', 'status_mahasiswa' => 'aktif', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('email');

        $this->put('/staff-mahasiswa/990', ['nim' => '990', 'nama' => 'M do', 'email' => '990@mhs.politala.ac.id',
            'status_mahasiswa' => 'do', 'no_hp' => 'abc'])->assertSessionHasErrors('no_hp');

        $this->delete('/staff-dosen/'.$dosen->nuptk)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('dosen', ['nuptk' => $dosen->nuptk]);
    }

    public function test_organisasi_mahasiswa_langsung_tersimpan_dan_pengumuman(): void
    {
        $m = Mahasiswa::where('nama', 'Siska Handayani')->firstOrFail();
        $c4Lama = (float) Ranking::where('nim', $m->nim)->value('poin_keaktifan_organisasi');

        $this->actingAs($m->user);
        foreach (['/mahasiswa-dashboard', '/mahasiswa-profile', '/mahasiswa-prestasi', '/mahasiswa-ajukan-prestasi',
            '/mahasiswa-pengumuman'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('/mahasiswa-notifikasi', false);
        }
        $this->get('/mahasiswa-notifikasi')->assertRedirect('/mahasiswa-pengumuman');
        $this->get('/mahasiswa-profile')->assertSee('Tambah Keaktifan Organisasi')->assertDontSee('Input Keaktifan Organisasi')->assertSee('id="poinC4"', false)
            ->assertDontSee('menunggu verifikasi');

        $this->post('/mahasiswa-profile/organisasi', ['organisasi' => [['nama_organisasi' => 'BEM', 'jabatan' => 'Raja']]])
            ->assertSessionHasErrors('organisasi.0.jabatan');

        Organisasi::where('nim', $m->nim)->delete();
        $this->post('/mahasiswa-profile/organisasi', ['organisasi' => [
            ['nama_organisasi' => 'BEM', 'jabatan' => 'Ketua'],
            ['nama_organisasi' => '', 'jabatan' => ''],
        ]])->assertSessionHasNoErrors();
        $this->assertSame(['BEM' => 'Ketua'], Organisasi::where('nim', $m->nim)->pluck('jabatan', 'nama_organisasi')->all());
        $this->get('/mahasiswa-profile')->assertSee('BEM')->assertSee('Ketua')->assertDontSee('Riwayat Perubahan Data');

        $this->post('/mahasiswa-profile', ['email' => $m->user->email, 'no_hp' => '0812-abc'])->assertSessionHasErrors('no_hp');
        $this->post('/mahasiswa-profile', ['email' => 'siska@gmail.com', 'no_hp' => '081234567890'])->assertSessionHasErrors('email');
        $this->post('/mahasiswa-profile', ['email' => $m->user->email, 'no_hp' => '081234567890', 'kelas' => 'TI-3B'])->assertSessionHasNoErrors();
        $this->assertSame('081234567890', $m->fresh()->no_hp);
        $this->assertSame('TI-3B', $m->fresh()->kelas);

        $this->post('/logout');
        $this->actingAs($this->staff());
        $this->post('/staff-ranking/generate', ['tahun' => (int) date('Y')])->assertSessionHasNoErrors();
        $c4Baru = (float) Ranking::where('nim', $m->nim)->value('poin_keaktifan_organisasi');
        $this->assertSame(100.0, $c4Baru);
        $this->assertNotSame($c4Lama, $c4Baru);

        $tanpaPrestasi = Mahasiswa::create(['user_id' => User::create(['name' => 'Z', 'email' => '777@mhs.politala.ac.id', 'password' => 'x', 'role' => 'mahasiswa'])->id_user,
            'nim' => '777', 'nama' => 'Z', 'status_mahasiswa' => 'aktif']);
        $this->post('/staff-pengumuman', ['penerima' => [$tanpaPrestasi->nim], 'kategori' => 'Prestasi Akademik', 'judul' => 'a', 'isi' => 'b', 'status' => 'terkirim'])
            ->assertSessionHasErrors('penerima');
        $this->assertSame(0, Pengumuman::where('judul', 'a')->count());

        $this->post('/staff-pengumuman', ['penerima' => [$m->nim], 'kategori' => 'Nilai Akademik', 'judul' => 'a', 'isi' => 'b', 'status' => 'terkirim'])
            ->assertSessionHasErrors('kategori');
        $prestasi = $m->prestasiDisetujui()->first();
        $this->post('/staff-pengumuman', ['penerima' => [$m->nim], 'prestasi_id' => $prestasi->id_prestasi, 'kategori' => 'Prestasi Akademik',
            'judul' => 'Selamat', 'isi' => 'Info di website', 'status' => 'terkirim'])->assertSessionHasNoErrors();
        $baru = Pengumuman::latest('id_pengumuman')->first();
        $this->assertSame($prestasi->kategori, $baru->kategori);
        $this->assertSame([$m->nim], $baru->penerima->pluck('nim')->all());
        $this->assertSame('Selamat', $baru->notifikasi);
        $this->assertNull($baru->penerima->first()->pivot->dibaca_pada);

        $this->post('/logout');
        $this->actingAs($m->user);
        $this->get('/mahasiswa-dashboard')->assertSee('Pengumuman Terbaru')->assertSee('Selamat');
        $this->get('/mahasiswa-pengumuman')->assertSee('Selamat');
        $this->assertNotNull($baru->fresh()->penerima->first()->pivot->dibaca_pada);
    }
}
