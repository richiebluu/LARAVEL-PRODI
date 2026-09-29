<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\KegiatanMahasiswa;
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

/** Uji revisi 28 September 2026 tahap 2 (dokumen "REVISI BARU(1).docx"). */
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
        foreach (['id_sarana_prasarana', 'nama', 'jenis', 'lokasi', 'kapasitas', 'fasilitas', 'deskripsi', 'foto', 'status', 'staff_prodi_id'] as $k) {
            $this->assertTrue(Schema::hasColumn('sarana_prasarana', $k), 'sarana_prasarana.'.$k);
        }
        $this->assertFalse(Schema::hasColumn('sarana_prasarana', 'urutan'), 'ERD: tanpa kolom urutan');
        foreach (['judul', 'kategori', 'tanggal', 'lokasi', 'penyelenggara', 'deskripsi', 'foto', 'status', 'staff_prodi_id'] as $k) {
            $this->assertTrue(Schema::hasColumn('kegiatan_mahasiswa', $k), 'kegiatan_mahasiswa.'.$k);
        }
    }

    public function test_hak_akses_halaman_staff_baru(): void
    {
        $urlStaff = ['/staff-sarana-prasarana', '/staff-kegiatan-mahasiswa', '/staff-sarana-prasarana/template',
            '/staff-mahasiswa/template', '/staff-dosen/template', '/staff-prospek-lulusan/template'];

        foreach ($urlStaff as $url) {
            $this->get($url)->assertRedirect(route('login'));
            $this->actingAs($this->mahasiswa())->get($url)->assertRedirect(route('mahasiswa-dashboard'));
            Auth::logout();
        }

        // Tamu & mahasiswa tidak bisa menambah data maupun mengimpor CSV.
        $this->post('/staff-sarana-prasarana', ['nama' => 'Lab X', 'jenis' => 'Laboratorium', 'status' => 'aktif'])->assertRedirect(route('login'));
        $this->post('/staff-mahasiswa/impor', ['berkas' => $this->csv("nim,nama,email\n1,a,b\n")])->assertRedirect(route('login'));
        $this->actingAs($this->mahasiswa())->post('/staff-kegiatan-mahasiswa', ['judul' => 'X'])->assertRedirect(route('mahasiswa-dashboard'));
        $this->assertSame(0, SaranaPrasarana::count());
        $this->assertSame(0, KegiatanMahasiswa::count());

        Auth::logout();
        foreach (['/staff-sarana-prasarana', '/staff-kegiatan-mahasiswa'] as $url) {
            $this->actingAs($this->staff())->get($url)->assertOk();
        }
        // Halaman publik dapat dibuka tanpa login (termasuk saat data kosong).
        Auth::logout();
        $this->get('/sarana-prasarana')->assertOk()->assertSee('belum diisi');
        $this->get('/kegiatan-mahasiswa')->assertOk()->assertSee('Belum ada kegiatan');
    }

    public function test_navbar_dropdown_dan_capital_each_word(): void
    {
        $html = $this->get('/')->getContent();
        // Profil: Sarana & Prasarana ada di dropdown Profil.
        $this->assertMatchesRegularExpression('#/kurikulum">Kurikulum</a>\s*<a href="[^"]*/sarana-prasarana">Sarana &amp; Prasarana</a>#', $html);
        // Mahasiswa: Kegiatan Mahasiswa urutan KETIGA.
        $this->assertMatchesRegularExpression('#<div class="dropdown">\s*<a href="[^"]*/mahasiswa-berprestasi">Mahasiswa Berprestasi</a>\s*<a href="[^"]*/ranking">Ranking Mahasiswa</a>\s*<a href="[^"]*/kegiatan-mahasiswa">Kegiatan Mahasiswa</a>#', $html);
        foreach (['>Beranda</a>', '>Profil <i', '>Mahasiswa <i', '>Testimoni</a>', '>Lowongan Kerja</a>', '>Informasi <i'] as $label) {
            $this->assertStringContainsString($label, $html, $label);
        }
        $this->assertStringNotContainsString('text-transform: uppercase; }', file_get_contents(public_path('css/app.css')));
        $sidebar = $this->actingAs($this->staff())->get('/staff-dashboard')->getContent();
        $this->assertStringContainsString('/staff-sarana-prasarana', $sidebar);
        $this->assertStringContainsString('/staff-kegiatan-mahasiswa', $sidebar);
    }

    public function test_crud_sarana_prasarana_dan_halaman_publik(): void
    {
        Storage::fake('public');
        $this->actingAs($this->staff());

        $this->post('/staff-sarana-prasarana', ['nama' => '', 'jenis' => 'Gudang', 'status' => 'aktif'])
            ->assertSessionHasErrors(['nama', 'jenis']);

        $this->post('/staff-sarana-prasarana', [
            'nama' => 'Laboratorium Pemrograman', 'jenis' => 'Laboratorium', 'lokasi' => 'Gedung TI Lt. 2', 'kapasitas' => 30,
            'fasilitas' => "30 unit PC\nProyektor", 'deskripsi' => 'Praktikum pemrograman.', 'status' => 'aktif',
            'foto' => UploadedFile::fake()->image('lab.jpg'),
        ])->assertSessionHasNoErrors();
        $this->post('/staff-sarana-prasarana', ['nama' => 'Ruang Baca', 'jenis' => 'Ruang Penunjang', 'status' => 'aktif'])->assertSessionHasNoErrors();
        $this->post('/staff-sarana-prasarana', ['nama' => 'Gudang Lama', 'jenis' => 'Fasilitas Pendukung', 'status' => 'nonaktif'])->assertSessionHasNoErrors();

        $lab = SaranaPrasarana::where('nama', 'Laboratorium Pemrograman')->firstOrFail();
        $this->assertSame(['30 unit PC', 'Proyektor'], $lab->daftar_fasilitas);
        Storage::disk('public')->assertExists($lab->foto);

        $publik = $this->get('/sarana-prasarana')->assertOk()->getContent();
        foreach (['Laboratorium Pemrograman', 'Gedung TI Lt. 2', '30 orang', '30 unit PC', 'Ruang Baca'] as $t) {
            $this->assertStringContainsString($t, $publik, $t);
        }
        $this->assertStringNotContainsString('Gudang Lama', $publik, 'data nonaktif tidak tampil');
        $this->assertLessThan(strpos($publik, 'Ruang Baca'), strpos($publik, 'Laboratorium Pemrograman'), 'laboratorium tampil lebih dulu');
        $this->get('/sarana-prasarana?jenis=Ruang%20Penunjang')->assertSee('Ruang Baca')->assertDontSee('Laboratorium Pemrograman');

        $fotoLama = $lab->foto;
        $this->put('/staff-sarana-prasarana/'.$lab->id_sarana_prasarana, ['nama' => 'Lab Pemrograman', 'jenis' => 'Laboratorium', 'kapasitas' => 32, 'status' => 'aktif', 'hapus_foto' => 1])->assertSessionHasNoErrors();
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

        $this->post('/staff-kegiatan-mahasiswa', ['judul' => 'Seminar', 'kategori' => 'Tidak Ada', 'tanggal' => 'bukan-tanggal', 'status' => 'aktif'])
            ->assertSessionHasErrors(['kategori', 'tanggal']);

        $this->post('/staff-kegiatan-mahasiswa', ['judul' => 'Seminar Nasional TI', 'kategori' => 'Seminar & Workshop', 'tanggal' => '2026-09-01',
            'lokasi' => 'Aula Politala', 'penyelenggara' => 'HIMA TI', 'deskripsi' => "Paragraf satu.\nParagraf dua.", 'status' => 'aktif'])->assertSessionHasNoErrors();
        $this->post('/staff-kegiatan-mahasiswa', ['judul' => 'Lomba Web Design', 'kategori' => 'Lomba & Kompetisi', 'tanggal' => '2026-08-10', 'status' => 'aktif'])->assertSessionHasNoErrors();
        $this->post('/staff-kegiatan-mahasiswa', ['judul' => 'Draft Kegiatan', 'kategori' => 'Lainnya', 'tanggal' => '2026-07-01', 'status' => 'nonaktif'])->assertSessionHasNoErrors();

        $publik = $this->get('/kegiatan-mahasiswa')->assertOk()->getContent();
        foreach (['Seminar Nasional TI', 'Aula Politala', 'Lomba Web Design', 'Paragraf dua.'] as $t) {
            $this->assertStringContainsString($t, $publik, $t);
        }
        $this->assertStringNotContainsString('Draft Kegiatan', $publik);
        $this->assertLessThan(strpos($publik, 'Lomba Web Design'), strpos($publik, 'Seminar Nasional TI'), 'kegiatan terbaru lebih dulu');
        $this->get('/kegiatan-mahasiswa?kategori=Lomba%20%26%20Kompetisi')->assertSee('Lomba Web Design')->assertDontSee('Seminar Nasional TI');
        $this->get('/kegiatan-mahasiswa?q=Aula')->assertSee('Seminar Nasional TI')->assertDontSee('Lomba Web Design');

        $k = KegiatanMahasiswa::where('judul', 'Seminar Nasional TI')->firstOrFail();
        $this->put('/staff-kegiatan-mahasiswa/'.$k->id_kegiatan_mahasiswa, ['judul' => 'Seminar Nasional TI 2026', 'kategori' => 'Seminar & Workshop',
            'tanggal' => '2026-09-02', 'status' => 'nonaktif'])->assertSessionHasNoErrors();
        $this->assertSame('2026-09-02', $k->fresh()->tanggal->format('Y-m-d'));
        $this->get('/kegiatan-mahasiswa')->assertDontSee('Seminar Nasional TI 2026');

        $this->delete('/staff-kegiatan-mahasiswa/'.$k->id_kegiatan_mahasiswa)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('kegiatan_mahasiswa', ['id_kegiatan_mahasiswa' => $k->id_kegiatan_mahasiswa]);
    }

    public function test_impor_csv_sarana_prasarana(): void
    {
        $this->actingAs($this->staff());
        SaranaPrasarana::create(['nama' => 'Laboratorium Jaringan', 'jenis' => 'Laboratorium', 'kapasitas' => 20, 'status' => 'aktif']);

        $this->get('/staff-sarana-prasarana/template')->assertOk()->assertSee('nama,jenis,lokasi,kapasitas,fasilitas,deskripsi,status');

        // Judul kolom wajib tidak ada.
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv("nama,lokasi\nLab A,Gedung\n")])
            ->assertSessionHasErrors('berkas')->assertSessionHas('impor_gagal', true);
        // Bukan CSV.
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => UploadedFile::fake()->image('foto.png')])->assertSessionHasErrors('berkas');
        // Baris salah -> seluruh impor dibatalkan.
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv("nama,jenis\nLab Baru,Laboratorium\nRuang X,Kantin\n")])->assertSessionHasErrors('berkas');
        $this->assertDatabaseMissing('sarana_prasarana', ['nama' => 'Lab Baru']);
        // Nama ganda di dalam file.
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv("nama,jenis\nLab Baru,Laboratorium\nlab baru,Laboratorium\n")])->assertSessionHasErrors('berkas');

        // Titik koma (Excel Indonesia), jenis huruf kecil, fasilitas dipisah "|", data lama dilewati.
        $csv = "Nama;Jenis;Lokasi;Kapasitas;Fasilitas;Deskripsi;Status\n"
            ."Laboratorium Pemrograman;laboratorium;Gedung TI Lt. 2;30;30 unit PC | Proyektor;;Aktif\n"
            ."Laboratorium Jaringan;Laboratorium;;40;;;\n"
            ."Ruang Kelas 1;Ruang Kuliah;;35;;;nonaktif\n";
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv($csv)])
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Impor selesai: 2 sarana & prasarana ditambahkan, 1 dilewati (sudah terdaftar).');
        $this->assertSame(20, SaranaPrasarana::where('nama', 'Laboratorium Jaringan')->value('kapasitas'), 'data lama tidak diubah');
        $lab = SaranaPrasarana::where('nama', 'Laboratorium Pemrograman')->firstOrFail();
        $this->assertSame(['30 unit PC', 'Proyektor'], $lab->daftar_fasilitas);
        $this->assertSame('Laboratorium', $lab->jenis);
        $this->assertSame('nonaktif', SaranaPrasarana::where('nama', 'Ruang Kelas 1')->value('status'));

        // Pilihan "perbarui".
        $this->post('/staff-sarana-prasarana/impor', ['berkas' => $this->csv("nama,jenis,kapasitas\nLaboratorium Jaringan,Laboratorium,40\n"), 'duplikat' => 'perbarui'])
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

        // Domain email salah & IPK di luar rentang -> dibatalkan, tidak ada akun yang dibuat.
        $salah = "nim,nama,email,ipk\n2301301901,Mahasiswa Satu,satu@gmail.com,3.5\n2301301902,Mahasiswa Dua,dua@mhs.politala.ac.id,5\n";
        $this->post('/staff-mahasiswa/impor', ['berkas' => $this->csv($salah)])->assertSessionHasErrors('berkas');
        $this->assertSame($jumlahAwal, Mahasiswa::count());
        // Email ganda di dalam file.
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

        // Akun hasil impor dapat login.
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

        // Prospek Lulusan: ikon dari label dropdown (ERD: tanpa kolom urutan).
        $this->get('/staff-prospek-lulusan')->assertOk()->assertSee('modalImporProspek', false);
        $csv = "nama,kategori,ikon,deskripsi,status\nWeb Developer,Pengembangan Web,Pengembangan Web,,\nNetwork Engineer,Jaringan,fa-network-wired,,Aktif\n";
        $this->post('/staff-prospek-lulusan/impor', ['berkas' => $this->csv($csv)])->assertSessionHasNoErrors();
        $this->assertSame('fa-laptop-code', ProspekLulusan::where('nama', 'Web Developer')->value('ikon'));
        $this->assertSame('aktif', ProspekLulusan::where('nama', 'Network Engineer')->value('status'));
        $this->post('/staff-prospek-lulusan/impor', ['berkas' => $this->csv("nama,kategori,ikon\nX,Y,fa-bukan-ikon\n")])->assertSessionHasErrors('berkas');

        // Kurikulum tetap memakai alur impor yang sama; judul kolom SIPADU (Kode MK, Nama MK) dikenali.
        $this->post('/staff-kurikulum/impor', ['berkas' => $this->csv("Kode MK,Nama MK,Semester,SKS,Jenis\nti101,Algoritma,1,3,wajib\n")])->assertSessionHasNoErrors();
        $this->assertSame('Wajib', MataKuliah::where('kode', 'TI101')->value('jenis'));
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
        foreach (['/', '/profil', '/prospek-lulusan', '/akreditasi', '/struktur-organisasi', '/dosen', '/kurikulum', '/sarana-prasarana',
            '/mahasiswa-berprestasi', '/ranking', '/kegiatan-mahasiswa', '/testimoni', '/lowongan-pekerjaan', '/berita', '/akamawa',
            '/kode-etik', '/pengumuman', '/login'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
