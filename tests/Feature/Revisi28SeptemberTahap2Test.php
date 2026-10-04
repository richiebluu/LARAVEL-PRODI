<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\ProspekLulusan;
use App\Models\SaranaPrasarana;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Revisi28SeptemberTahap2Test extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function staff(): User
    {
        return User::where('role', 'staff')->firstOrFail();
    }

    private function mahasiswa(): User
    {
        return User::where('role', 'mahasiswa')->firstOrFail();
    }

    private function csv(string $isi, string $nama = 'data.csv'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nama, $isi);
    }

    public function test_struktur_database_tabel_baru(): void
    {
        foreach (['id_sarana_prasarana', 'nama', 'gedung', 'kapasitas', 'fasilitas', 'foto', 'status', 'staff_prodi_id'] as $k) {
            $this->assertTrue(Schema::hasColumn('sarana_prasarana', $k), 'sarana_prasarana.'.$k);
        }
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'deskripsi'));
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'jenis'));
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'lokasi'));
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'urutan'), 'ERD: tanpa kolom urutan');
        $this->assertFalse(Schema::hasTable('kegiatan_mahasiswa'));
    }

    public function test_hak_akses_halaman_staff_baru(): void
    {
        $urlStaff = ['/staff-sarana-prasarana', '/staff-berita', '/staff-sarana-prasarana/template',
            '/staff-mahasiswa/template', '/staff-dosen/template', '/staff-prospek-lulusan/template'];

        foreach ($urlStaff as $url) {
            $this->get($url)->assertRedirect(route('login'));
            $this->actingAs($this->mahasiswa())->get($url)->assertRedirect(route('mahasiswa-dashboard'));
            Auth::logout();
        }

        $this->post('/staff-sarana-prasarana', ['nama' => 'Lab X', 'gedung' => 'Adriansyah 1', 'status' => 'aktif'])->assertRedirect(route('login'));
        $this->post('/staff-mahasiswa/impor', ['berkas' => $this->csv("nim,nama,email\n1,a,b\n")])->assertRedirect(route('login'));
        $this->actingAs($this->mahasiswa())->post('/staff-berita', ['judul' => 'X', 'jenis' => 'kegiatan_mahasiswa'])->assertRedirect(route('mahasiswa-dashboard'));
        $this->assertSame(0, SaranaPrasarana::count());
        $this->assertSame(0, \App\Models\Berita::count());

        Auth::logout();
        foreach (['/staff-sarana-prasarana', '/staff-berita?jenis=kegiatan_mahasiswa'] as $url) {
            $this->actingAs($this->staff())->get($url)->assertOk();
        }
        Auth::logout();
        $this->get('/sarana-prasarana')->assertOk()->assertSee('belum diisi');
        $this->get('/kegiatan-mahasiswa')->assertRedirect(route('berita', ['jenis' => 'kegiatan-mahasiswa']));
        $this->get('/berita?jenis=kegiatan-mahasiswa')->assertOk()->assertSee('Belum ada kegiatan');
    }

    public function test_navbar_dropdown_dan_capital_each_word(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertMatchesRegularExpression('#/mata-kuliah">Mata Kuliah</a>\s*<a href="[^"]*/sarana-prasarana">Sarana &amp; Prasarana</a>#', $html);
        $this->assertMatchesRegularExpression('#<div class="dropdown">\s*<a href="[^"]*/mahasiswa-berprestasi">Mahasiswa Berprestasi</a>\s*<a href="[^"]*/ranking">Ranking Mahasiswa</a>\s*</div>#', $html);
        $this->assertStringNotContainsString('/kegiatan-mahasiswa"', $html);
        foreach (['>Beranda</a>', '>Profil <i', '>Mahasiswa <i', '>Testimoni</a>', '>Lowongan Kerja</a>', '>Informasi <i'] as $label) {
            $this->assertStringContainsString($label, $html, $label);
        }
        $this->assertStringNotContainsString('text-transform: uppercase; }', file_get_contents(public_path('css/app.css')));
        $sidebar = $this->actingAs($this->staff())->get('/staff-dashboard')->getContent();
        $this->assertStringContainsString('/staff-sarana-prasarana', $sidebar);
        $this->assertStringNotContainsString('/staff-kegiatan-mahasiswa', $sidebar);
        $this->assertStringContainsString('/staff-berita', $sidebar);
    }

    public function test_crud_sarana_prasarana_dan_halaman_publik(): void
    {
        Storage::fake('public');
        $this->actingAs($this->staff());

        $this->post('/staff-sarana-prasarana', ['nama' => '', 'gedung' => 'Gedung Lain', 'status' => 'aktif'])
            ->assertSessionHasErrors(['nama', 'gedung']);

        $this->post('/staff-sarana-prasarana', [
            'nama' => 'Laboratorium Pemrograman', 'gedung' => 'Gedung Teknik Informatika', 'kapasitas' => 30,
            'fasilitas' => "30 unit PC\nProyektor", 'status' => 'aktif',
            'foto' => $this->gambarPalsu('lab.jpg'),
        ])->assertSessionHasNoErrors();
        $this->post('/staff-sarana-prasarana', ['nama' => 'Ruang Baca', 'gedung' => 'Adriansyah 2', 'status' => 'aktif'])->assertSessionHasNoErrors();
        $this->post('/staff-sarana-prasarana', ['nama' => 'Gudang Lama', 'gedung' => 'Adriansyah 1', 'status' => 'nonaktif'])->assertSessionHasNoErrors();

        $lab = SaranaPrasarana::where('nama', 'Laboratorium Pemrograman')->firstOrFail();
        $this->assertSame(['30 unit PC', 'Proyektor'], $lab->daftar_fasilitas);
        Storage::disk('public')->assertExists($lab->foto);

        $publik = $this->get('/sarana-prasarana')->assertOk()->getContent();
        foreach (['Laboratorium Pemrograman', 'Gedung Teknik Informatika', '30 orang', '30 unit PC', 'Ruang Baca', 'Adriansyah 2'] as $t) {
            $this->assertStringContainsString($t, $publik, $t);
        }
        $this->assertStringNotContainsString('Gudang Lama', $publik, 'data nonaktif tidak tampil');
        $this->assertLessThan(strpos($publik, 'Ruang Baca'), strpos($publik, 'Laboratorium Pemrograman'), 'urut sesuai gedung');
        $this->get('/sarana-prasarana?gedung=Adriansyah%202')->assertSee('Ruang Baca')->assertDontSee('Laboratorium Pemrograman');

        $fotoLama = $lab->foto;
        $this->put('/staff-sarana-prasarana/'.$lab->id_sarana_prasarana, ['nama' => 'Lab Pemrograman', 'gedung' => 'Gedung Teknik Informatika', 'kapasitas' => 32, 'status' => 'aktif', 'hapus_foto' => 1])->assertSessionHasNoErrors();
        $this->assertSame(32, $lab->fresh()->kapasitas);
        $this->assertNull($lab->fresh()->foto);
        Storage::disk('public')->assertMissing($fotoLama);

        $this->get('/staff-sarana-prasarana?q=Baca')->assertSee('Ruang Baca')->assertDontSee('Lab Pemrograman');
        $this->delete('/staff-sarana-prasarana/'.$lab->id_sarana_prasarana)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('sarana_prasarana', ['id_sarana_prasarana' => $lab->id_sarana_prasarana]);
    }

    public function test_crud_kegiatan_mahasiswa_dan_halaman_publik(): void
    {
        $this->actingAs($this->staff());

        $this->post('/staff-berita', ['judul' => 'Seminar', 'jenis' => 'bukan-jenis', 'isi' => 'x', 'tanggal' => 'bukan-tanggal', 'status' => 'terbit'])
            ->assertSessionHasErrors(['jenis', 'tanggal']);

        $this->post('/staff-berita', ['judul' => 'Seminar Nasional TI', 'jenis' => 'kegiatan_mahasiswa', 'kategori' => 'Seminar & Workshop', 'tanggal' => '2026-09-01',
            'lokasi' => 'Aula Politala', 'penyelenggara' => 'HIMA TI', 'isi' => "Paragraf satu.\nParagraf dua.", 'status' => 'terbit'])->assertSessionHasNoErrors();
        $this->post('/staff-berita', ['judul' => 'Lomba Web Design', 'jenis' => 'kegiatan_mahasiswa', 'kategori' => 'Lomba & Kompetisi', 'isi' => 'Lomba', 'tanggal' => '2026-08-10', 'status' => 'terbit'])->assertSessionHasNoErrors();
        $this->post('/staff-berita', ['judul' => 'Draft Kegiatan', 'jenis' => 'kegiatan_mahasiswa', 'isi' => 'Draft', 'tanggal' => '2026-07-01', 'status' => 'draft'])->assertSessionHasNoErrors();
        $this->post('/staff-berita', ['judul' => 'Berita Prodi Biasa', 'jenis' => 'berita', 'lokasi' => 'Diabaikan', 'isi' => 'Isi', 'tanggal' => '2026-08-20', 'status' => 'terbit'])->assertSessionHasNoErrors();
        $this->assertNull(\App\Models\Berita::where('judul', 'Berita Prodi Biasa')->value('lokasi'), 'lokasi hanya untuk kegiatan');

        $publik = $this->get('/berita?jenis=kegiatan-mahasiswa')->assertOk()->getContent();
        foreach (['Seminar Nasional TI', 'Lomba Web Design'] as $t) {
            $this->assertStringContainsString($t, $publik, $t);
        }
        $this->assertStringNotContainsString('Draft Kegiatan', $publik);
        $this->assertStringNotContainsString('Berita Prodi Biasa', $publik);
        $this->assertLessThan(strpos($publik, 'Lomba Web Design'), strpos($publik, 'Seminar Nasional TI'), 'kegiatan terbaru lebih dulu');
        $this->get('/berita')->assertSee('Seminar Nasional TI')->assertSee('Berita Prodi Biasa');
        $this->get('/berita?q=Aula')->assertSee('Seminar Nasional TI')->assertDontSee('Lomba Web Design');
        $this->get('/berita/seminar-nasional-ti')->assertOk()->assertSee('Aula Politala')->assertSee('HIMA TI')->assertSee('Paragraf dua.');

        $k = \App\Models\Berita::where('judul', 'Seminar Nasional TI')->firstOrFail();
        $this->get('/staff-berita?jenis=kegiatan_mahasiswa')->assertOk()->assertSee('Seminar Nasional TI')->assertDontSee('Berita Prodi Biasa');
        $this->put('/staff-berita/'.$k->id_berita, ['judul' => 'Seminar Nasional TI 2026', 'jenis' => 'kegiatan_mahasiswa', 'isi' => 'Isi',
            'tanggal' => '2026-09-02', 'status' => 'draft'])->assertSessionHasNoErrors();
        $this->assertSame('2026-09-02', $k->fresh()->tanggal->format('Y-m-d'));
        $this->get('/berita?jenis=kegiatan-mahasiswa')->assertDontSee('Seminar Nasional TI 2026');

        $this->delete('/staff-berita/'.$k->id_berita)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('berita', ['id_berita' => $k->id_berita]);
    }

    public function test_impor_csv_sarana_prasarana(): void
    {
        $this->actingAs($this->staff());
        SaranaPrasarana::create(['nama' => 'Laboratorium Jaringan', 'gedung' => 'Gedung Teknik Informatika', 'kapasitas' => 20, 'status' => 'aktif']);

        $this->get('/staff-sarana-prasarana/template')->assertOk()->assertSee('nama,gedung,kapasitas,fasilitas,status');

        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv("nama,kapasitas\nLab A,20\n")])
            ->assertSessionHasErrors('berkas')->assertSessionHas('impor_gagal', true);
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->gambarPalsu('foto.png')])->assertSessionHasErrors('berkas');
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv("nama,gedung\nLab Baru,Adriansyah 1\nRuang X,Gedung Lain\n")])->assertSessionHasErrors('berkas');
        $this->assertDatabaseMissing('sarana_prasarana', ['nama' => 'Lab Baru']);
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv("nama,gedung\nLab Baru,Adriansyah 1\nlab baru,Adriansyah 1\n")])->assertSessionHasErrors('berkas');

        $csv = "Nama;Gedung;Kapasitas;Fasilitas;Status\n"
            ."Laboratorium Pemrograman;gedung teknik informatika;30;30 unit PC | Proyektor;Aktif\n"
            ."Laboratorium Jaringan;Gedung Teknik Informatika;40;;\n"
            ."Ruang Kelas 1;adriansyah 1;35;;nonaktif\n";
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv($csv)])
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Impor selesai: 2 sarana & prasarana ditambahkan, 1 dilewati (sudah terdaftar).');
        $this->assertSame(20, SaranaPrasarana::where('nama', 'Laboratorium Jaringan')->value('kapasitas'), 'data lama tidak diubah');
        $lab = SaranaPrasarana::where('nama', 'Laboratorium Pemrograman')->firstOrFail();
        $this->assertSame(['30 unit PC', 'Proyektor'], $lab->daftar_fasilitas);
        $this->assertSame('Gedung Teknik Informatika', $lab->gedung);
        $this->assertSame('nonaktif', SaranaPrasarana::where('nama', 'Ruang Kelas 1')->value('status'));

        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv("nama,gedung,kapasitas\nLaboratorium Jaringan,Gedung Teknik Informatika,40\n"), 'duplikat' => 'perbarui'])
            ->assertSessionHas('success', 'Impor selesai: 0 sarana & prasarana ditambahkan, 1 diperbarui.');
        $this->assertSame(40, SaranaPrasarana::where('nama', 'Laboratorium Jaringan')->value('kapasitas'));
    }

    public function test_impor_csv_mahasiswa_membuat_akun(): void
    {
        $this->actingAs($this->staff());
        $lama = Mahasiswa::firstOrFail();
        $jumlahAwal = Mahasiswa::count();

        $this->get('/staff-mahasiswa')->assertOk()->assertSee('modalImporMahasiswa', false)->assertSee('Impor CSV');
        $this->get('/staff-mahasiswa/template')->assertOk()->assertSee('nim,nama,email,angkatan,kelas,no_hp,ipk,status_mahasiswa,password');

        $salah = "nim,nama,email,ipk\n2301301901,Mahasiswa Satu,satu@gmail.com,3.5\n2301301902,Mahasiswa Dua,dua@mhs.politala.ac.id,5\n";
        $this->post('/staff-mahasiswa/impor', ['berkas' => $this->csv($salah)])->assertSessionHasErrors('berkas');
        $this->assertSame($jumlahAwal, Mahasiswa::count());
        $ganda = "nim,nama,email\n2301301901,A,sama@mhs.politala.ac.id\n2301301902,B,sama@mhs.politala.ac.id\n";
        $this->post('/staff-mahasiswa/impor', ['berkas' => $this->csv($ganda)])->assertSessionHasErrors('berkas');

        $csv = "nim,nama,email,angkatan,kelas,no_hp,ipk,status_mahasiswa,password\n"
            ."2301301901,Mahasiswa Satu,2301301901@mhs.politala.ac.id,2023,TI-3B,0812-3456-7890,\"3,75\",aktif,\n"
            ."2301301902,Mahasiswa Dua,2301301902@mhs.politala.ac.id,2023,TI-3B,,3.10,Cuti,rahasia123\n"
            .$lama->nim.",Nama Diubah,{$lama->email},,,,,,\n";
        $this->post('/staff-mahasiswa/impor', ['berkas' => $this->csv($csv)])
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Impor selesai: 2 mahasiswa ditambahkan, 1 dilewati (sudah terdaftar).');

        $m1 = Mahasiswa::with('user')->where('nim', '2301301901')->firstOrFail();
        $this->assertSame('3.75', (string) $m1->ipk);
        $this->assertSame('081234567890', $m1->no_hp);
        $this->assertSame('mahasiswa', $m1->user->role);
        $this->assertTrue(Hash::check('2301301901', $m1->user->password), 'password awal = NIM');
        $m2 = Mahasiswa::with('user')->where('nim', '2301301902')->firstOrFail();
        $this->assertSame('cuti', $m2->status_mahasiswa);
        $this->assertTrue(Hash::check('rahasia123', $m2->user->password));
        $this->assertNotSame('Nama Diubah', $lama->fresh()->nama, 'data lama tidak diubah (dilewati)');

        Auth::logout();
        $this->post('/login', ['role' => 'mahasiswa', 'email' => '2301301901@mhs.politala.ac.id', 'password' => '2301301901'])
            ->assertRedirect(route('mahasiswa-dashboard'));
    }

    public function test_impor_csv_dosen_dan_prospek_lulusan(): void
    {
        $this->actingAs($this->staff());
        $this->get('/staff-dosen')->assertOk()->assertSee('modalImporDosen', false);

        $csv = "nuptk,nama,pendidikan_terakhir,email,google_scholar,alamat,tanggal_lahir,status\n"
            ."1001,Dr. Satu,S3 Informatika,satu@politala.ac.id,,,31/12/1980,Aktif\n"
            ."1002,Bu Dua,S2 Ilmu Komputer,,,,1985-05-17,pendidikan\n";
        $this->post('/staff-dosen/impor', ['berkas' => $this->csv($csv)])
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Impor selesai: 2 dosen ditambahkan.');
        $this->assertSame('1980-12-31', Dosen::where('nuptk', '1001')->first()->tanggal_lahir->format('Y-m-d'));
        $this->assertSame('pendidikan', Dosen::where('nuptk', '1002')->value('status'));
        $this->post('/staff-dosen/impor', ['berkas' => $this->csv("nuptk,nama,status\n1003,Pak Tiga,Cuti\n")])->assertSessionHasErrors('berkas');
        $this->post('/staff-dosen/impor', ['berkas' => $this->csv("nuptk,nama,email\n1004,Pak Empat,empat@gmail.com\n")])->assertSessionHasErrors('berkas');

        $this->get('/staff-prospek-lulusan')->assertOk()->assertSee('modalImporProspek', false);
        $csv = "nama,ikon,deskripsi,status\nWeb Developer,Pengembangan Web,,\nNetwork Engineer,fa-network-wired,,Aktif\nData Engineer,cloud,,\n";
        $this->post('/staff-prospek-lulusan/impor', ['berkas' => $this->csv($csv)])->assertSessionHasNoErrors();
        $this->assertSame('fa-laptop-code', ProspekLulusan::where('nama', 'Web Developer')->value('ikon'));
        $this->assertSame('aktif', ProspekLulusan::where('nama', 'Network Engineer')->value('status'));
        $this->assertSame('fa-cloud', ProspekLulusan::where('nama', 'Data Engineer')->value('ikon'));
        $this->post('/staff-prospek-lulusan/impor', ['berkas' => $this->csv("nama,ikon\nX,<b>bukan ikon</b>\n")])->assertSessionHasErrors('berkas');

        $this->post('/staff-mata-kuliah/impor', ['berkas' => $this->csv("Kode MK,Nama MK,Semester,SKS,Jenis\nti101,Algoritma,1,3,wajib\n")])->assertSessionHasNoErrors();
        $this->assertSame('Wajib', MataKuliah::where('kode_mata_kuliah', 'TI101')->value('jenis'));
    }

    public function test_dosen_publik_search_dan_ringkasan(): void
    {
        Dosen::create(['nuptk' => '2001', 'nama' => 'Dr. Andi Saputra', 'status' => 'aktif', 'pendidikan_terakhir' => 'S3']);
        Dosen::create(['nuptk' => '2002', 'nama' => 'Budi Santoso, M.Kom', 'status' => 'pendidikan']);

        $html = $this->get('/dosen')->assertOk()->getContent();
        $this->assertStringContainsString('data-search-target=".baris-dosen"', $html);
        $this->assertLessThan(strpos($html, 'class="tabel-publik"'), strpos($html, 'name="q"'), 'kolom cari berada di atas tabel');
        $this->assertStringContainsString('Dosen Pendidikan', $html);

        $this->get('/dosen?q=Andi')->assertSee('Dr. Andi Saputra')->assertDontSee('Budi Santoso')->assertSee('Menampilkan 1 dari 2 dosen');
        $this->get('/dosen?q=2002')->assertSee('Budi Santoso')->assertDontSee('Dr. Andi Saputra');
        $this->get('/dosen?q=tidakada')->assertSee('tidak ditemukan');
    }

    public function test_akreditasi_staff_tabel_di_atas_form(): void
    {
        $html = $this->actingAs($this->staff())->get('/staff-akreditasi')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'Tambah Data Akreditasi</h2>'), strpos($html, 'Riwayat Akreditasi'));

        $this->post('/staff-akreditasi', ['peringkat' => 'Baik Sekali', 'lembaga' => 'LAM INFOKOM'])->assertSessionHasNoErrors();
        $this->get('/staff-akreditasi')->assertSee('Baik Sekali')->assertSee('LAM INFOKOM');
    }

    public function test_halaman_publik_tetap_bisa_dibuka(): void
    {
        foreach (['/', '/profil', '/prospek-lulusan', '/akreditasi', '/struktur-organisasi', '/dosen', '/mata-kuliah', '/sarana-prasarana',
            '/mahasiswa-berprestasi', '/ranking', '/testimoni', '/lowongan-pekerjaan', '/berita', '/akamawa',
            '/kode-etik', '/pengumuman', '/login'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
