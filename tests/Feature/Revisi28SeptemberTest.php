<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\ProspekLulusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Revisi28SeptemberTest extends TestCase
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

    public function test_struktur_database(): void
    {
        $this->assertTrue(Schema::hasTable('prospek_lulusan'));
        foreach (['nama', 'ikon', 'deskripsi', 'status', 'staff_prodi_id'] as $k) {
            $this->assertTrue(Schema::hasColumn('prospek_lulusan', $k), $k);
        }
        $this->assertFalse(Schema::hasColumn('prospek_lulusan', 'kategori'));
        $this->assertFalse(Schema::hasColumn('prospek_lulusan', 'urutan'));
        $this->assertFalse(Schema::hasColumn('program_studi', 'prospek_kerja'));
        $this->assertFalse(Schema::hasColumn('dosen', 'jabatan'));
        $this->assertFalse(Schema::hasColumn('dosen', 'keterangan_status'));
        $this->assertFalse(Schema::hasColumn('dosen', 'id'));
        foreach (['kode_mata_kuliah', 'nama', 'semester', 'sks', 'jenis'] as $k) {
            $this->assertTrue(Schema::hasColumn('mata_kuliah', $k), $k);
        }
    }

    public function test_halaman_staff_hanya_untuk_staff(): void
    {
        foreach (['/staff-prospek-lulusan', '/staff-mata-kuliah'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
            $this->actingAs($this->mahasiswa())->get($url)->assertRedirect(route('mahasiswa-dashboard'));
            $this->actingAs($this->staff())->get($url)->assertOk();
            auth()->logout();
        }
        $this->post('/staff-prospek-lulusan', ['nama' => 'X'])->assertRedirect(route('login'));
        $this->assertSame(0, ProspekLulusan::count());
    }

    public function test_crud_prospek_lulusan_dengan_dropdown_ikon(): void
    {
        $this->actingAs($this->staff());

        $halaman = $this->get('/staff-prospek-lulusan')->assertOk();
        $halaman->assertSee('Tambah Prospek Lulusan')->assertSee('Nama Prospek Lulusan')->assertSee('name="ikon"', false)
            ->assertSee('data-pratinjau-ikon', false)->assertSee('Deskripsi Singkat')->assertSee('name="status"', false)
            ->assertDontSee('name="kategori"', false)->assertDontSee('<select name="nama"', false)->assertDontSee('<select name="ikon"', false);
        foreach (array_keys(ProspekLulusan::IKON) as $kelas) {
            $halaman->assertSee('data-ikon="'.$kelas.'"', false);
        }
        $this->get('/staff-dashboard')->assertSee('/staff-prospek-lulusan', false);

        $this->post('/staff-prospek-lulusan', ['nama' => 'Web Developer', 'ikon' => 'bukan ikon <x>', 'status' => 'aktif'])
            ->assertSessionHasErrors('ikon');
        $this->post('/staff-prospek-lulusan', ['ikon' => 'fa-code', 'status' => 'aktif'])
            ->assertSessionHasErrors('nama');
        $this->post('/staff-prospek-lulusan', ['nama' => 'Web Developer', 'ikon' => 'fa-laptop-code',
            'deskripsi' => 'Membangun aplikasi berbasis web.', 'status' => 'aktif'])->assertSessionHasNoErrors();
        $this->post('/staff-prospek-lulusan', ['nama' => 'Cloud Engineer', 'ikon' => 'fa-cloud', 'status' => 'aktif'])->assertSessionHasNoErrors();
        $this->post('/staff-prospek-lulusan', ['nama' => 'Python Developer', 'ikon' => 'fab fa-python', 'status' => 'aktif'])->assertSessionHasErrors('ikon');
        $lama = ProspekLulusan::create(['nama' => 'Python Developer', 'ikon' => 'fa-brands fa-python', 'status' => 'aktif']);
        $this->put('/staff-prospek-lulusan/'.$lama->id_prospek_lulusan, ['nama' => 'Python Developer', 'ikon' => 'fa-brands fa-python', 'status' => 'aktif'])->assertSessionHasNoErrors();

        $p = ProspekLulusan::where('nama', 'Web Developer')->firstOrFail();
        $this->assertSame('fa-laptop-code', $p->ikon);
        $this->assertSame('fa-cloud', ProspekLulusan::where('nama', 'Cloud Engineer')->value('ikon'));
        $this->assertSame('fa-brands fa-python', ProspekLulusan::where('nama', 'Python Developer')->value('ikon'));

        $html = $this->get('/prospek-lulusan')->assertOk()->getContent();
        $this->assertStringContainsString('card ta-card', $html);
        $this->assertStringContainsString('fa-solid fa-laptop-code', $html);
        $this->assertStringContainsString('fa-solid fa-cloud', $html);
        $this->assertStringContainsString('fa-brands fa-python', $html);
        $this->assertStringContainsString('Membangun aplikasi berbasis web.', $html);
        $this->assertStringNotContainsString('?kategori=', $html);

        $this->put('/staff-prospek-lulusan/'.$p->id_prospek_lulusan, ['nama' => 'Web Developer', 'ikon' => 'fa-solid fa-code', 'status' => 'nonaktif'])->assertSessionHasNoErrors();
        $this->assertSame('fa-code', $p->fresh()->ikon);
        $this->get('/prospek-lulusan')->assertDontSee('Web Developer');
        $this->get('/staff-prospek-lulusan')->assertSee('Web Developer')->assertSee('modalDetailProspek'.$p->id_prospek_lulusan, false);

        $this->delete('/staff-prospek-lulusan/'.$p->id_prospek_lulusan)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('prospek_lulusan', ['id_prospek_lulusan' => $p->id_prospek_lulusan]);

        $this->get('/staff-profil')->assertDontSee('name="prospek_kerja"', false)->assertSee('/staff-prospek-lulusan', false);
    }

    public function test_dosen_status_tanpa_jabatan(): void
    {
        $this->actingAs($this->staff());

        $form = $this->get('/staff-dosen')->assertOk();
        $form->assertDontSee('name="jabatan"', false)->assertSee('value="pendidikan"', false)->assertDontSee('name="keterangan_status"', false);

        $this->post('/staff-dosen', ['nuptk' => '11', 'nama' => 'Dr. Aktif', 'status' => 'cuti'])->assertSessionHasErrors('status');
        $this->post('/staff-dosen', ['nuptk' => '11', 'nama' => 'Dr. Aktif', 'status' => 'aktif'])->assertSessionHasNoErrors();
        $this->post('/staff-dosen', ['nuptk' => '12', 'nama' => 'Bu Studi', 'status' => 'pendidikan'])->assertSessionHasNoErrors();
        $this->post('/staff-dosen', ['nuptk' => '13', 'nama' => 'Pak Nonaktif', 'status' => 'nonaktif'])->assertSessionHasNoErrors();

        $this->assertSame('Pendidikan', Dosen::where('nuptk', '12')->first()->label_status);

        $publik = $this->get('/dosen')->assertOk()->getContent();
        foreach (['Dr. Aktif', 'Bu Studi', 'Pak Nonaktif', 'status-aktif', 'status-pendidikan', 'status-nonaktif'] as $t) {
            $this->assertStringContainsString($t, $publik, $t);
        }
        $this->assertStringNotContainsString('<th class="kolom-opsional">Jabatan</th>', $publik);
        $this->assertLessThan(strpos($publik, 'Pak Nonaktif'), strpos($publik, 'Dr. Aktif'), 'Aktif tampil lebih dulu');
        $this->get('/profil')->assertSee('status-pendidikan', false);
    }

    public function test_crud_dan_impor_mata_kuliah(): void
    {
        $this->get('/kurikulum')->assertRedirect('/mata-kuliah');
        $this->get('/mata-kuliah')->assertOk()->assertSee('Data mata kuliah belum diisi')->assertDontSee('Kurikulum');
        $this->get('/')->assertSee('/mata-kuliah', false)->assertDontSee('>Kurikulum<', false);

        $this->actingAs($this->staff());
        $this->post('/staff-mata-kuliah', ['kode' => 'TI 101', 'nama' => 'X', 'semester' => 1, 'sks' => 2, 'jenis' => 'Wajib'])->assertSessionHasErrors('kode');
        $this->post('/staff-mata-kuliah', ['kode' => 'ti101', 'nama' => 'Algoritma dan Pemrograman', 'semester' => 1, 'sks' => 3, 'jenis' => 'Wajib'])->assertSessionHasNoErrors();
        $this->post('/staff-mata-kuliah', ['kode' => 'TI101', 'nama' => 'Duplikat', 'semester' => 1, 'sks' => 3, 'jenis' => 'Wajib'])->assertSessionHasErrors('kode');
        $this->post('/staff-mata-kuliah', ['kode' => 'TI999', 'nama' => 'X', 'semester' => 9, 'sks' => 3, 'jenis' => 'Bebas'])->assertSessionHasErrors(['semester', 'jenis']);

        $mk = MataKuliah::findOrFail('TI101');
        $this->put('/staff-mata-kuliah/'.$mk->kode_mata_kuliah, ['kode' => 'TI101', 'nama' => 'Algoritma & Pemrograman', 'semester' => 1, 'sks' => 4, 'jenis' => 'Wajib'])->assertSessionHasNoErrors();
        $this->assertSame(4, $mk->fresh()->sks);

        $csv = "kode;nama;semester;sks;jenis\nTI101;Algoritma dan Pemrograman;1;3;wajib\nTI201;Basis Data;2;3;Wajib\nTI501;Machine Learning;5;2;Pilihan\n";
        $this->post('/staff-mata-kuliah/impor', ['berkas' => UploadedFile::fake()->createWithContent('mata-kuliah.csv', $csv), 'duplikat' => 'perbarui'])
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Impor selesai: 2 mata kuliah ditambahkan, 1 diperbarui.');
        $this->assertSame(3, MataKuliah::count());

        $salah = "kode,nama,semester,sks,jenis\nTI301,Jaringan,3,3,Wajib\nTI302,,3,3,Wajib\n";
        $this->post('/staff-mata-kuliah/impor', ['berkas' => UploadedFile::fake()->createWithContent('salah.csv', $salah)])->assertSessionHasErrors('berkas');
        $this->assertDatabaseMissing('mata_kuliah', ['kode_mata_kuliah' => 'TI301']);

        $this->get('/staff-mata-kuliah/template')->assertOk()->assertSee('kode,nama,semester,sks,jenis');

        $html = $this->get('/mata-kuliah')->assertOk()->getContent();
        foreach (['Semester 1', 'Semester 2', 'Semester 5', 'TI201', 'Basis Data', 'Machine Learning', 'Pilihan', '8 SKS'] as $t) {
            $this->assertStringContainsString($t, $html, $t);
        }
        $this->get('/mata-kuliah?semester=2')->assertSee('Basis Data')->assertDontSee('Machine Learning');

        $this->delete('/staff-mata-kuliah/'.$mk->kode_mata_kuliah)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('mata_kuliah', ['kode_mata_kuliah' => 'TI101']);
    }

    public function test_navbar_capital_each_word_dan_kurikulum_di_profil(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertMatchesRegularExpression('#<a href="[^"]*/dosen">Dosen Pengajar</a>\s*<a href="[^"]*/mata-kuliah">Mata Kuliah</a>#', $html);
        $css = file_get_contents(public_path('css/app.css'));
        $this->assertStringNotContainsString('.navbar .nav-menu .dropdown a { text-transform: uppercase; }', $css);
    }

    public function test_kode_etik_pdf_ukuran_penuh(): void
    {
        ProgramStudi::query()->updateOrCreate([], ['nama_prodi' => 'Teknologi Informasi', 'kode_etik' => 'kode-etik/contoh.pdf']);
        $html = $this->get('/kode-etik')->assertOk()->getContent();
        foreach (['kodetik-bar', 'data-kodetik-halaman', 'data-kodetik="zoom-in"', 'data-kodetik="zoom-out"', 'data-kodetik="layar"',
            'data-kodetik="lebar"', 'data-kodetik="halaman"', 'data-kodetik-lompat', 'data-kodetik-fallback', 'js/kode-etik.js'] as $t) {
            $this->assertStringContainsString($t, $html, $t);
        }
        $this->assertStringNotContainsString('kodetik-book', $html);
    }
}
