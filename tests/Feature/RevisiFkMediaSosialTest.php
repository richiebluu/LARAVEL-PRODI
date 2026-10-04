<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RevisiFkMediaSosialTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function staff(): User
    {
        return User::where('role', 'staff')->firstOrFail();
    }

    public function test_struktur_database(): void
    {
        $this->assertTrue(Schema::hasColumn('mata_kuliah', 'program_studi_id'));
        $this->assertTrue(Schema::hasColumn('program_studi', 'link_media_sosial'));
        $this->assertTrue(Schema::hasColumn('berita', 'link_media_sosial'));
    }

    public function test_mata_kuliah_otomatis_terhubung_ke_prodi_ti(): void
    {
        $ti = ProgramStudi::create(['nama_prodi' => 'Program Studi Teknologi Informasi']);
        $this->actingAs($this->staff());

        $this->get('/staff-mata-kuliah')->assertOk()->assertDontSee('name="program_studi_id"', false);

        $data = ['kode' => 'TI101', 'nama' => 'Algoritma', 'semester' => 1, 'sks' => 3, 'jenis' => 'Wajib'];
        $this->post('/staff-mata-kuliah', $data)->assertSessionHasNoErrors();
        $mk = MataKuliah::where('kode_mata_kuliah', 'TI101')->firstOrFail();
        $this->assertSame($ti->id_program_studi, $mk->program_studi_id);
        $this->assertSame('Program Studi Teknologi Informasi', $mk->programStudi->nama_prodi);
        $this->assertTrue($ti->mataKuliah->contains($mk));

        $this->put('/staff-mata-kuliah/'.$mk->id, ['nama' => 'Algoritma & Pemrograman'] + $data)->assertSessionHasNoErrors();
        $this->assertSame($ti->id_program_studi, $mk->fresh()->program_studi_id);

        $csv = "kode,nama,semester,sks,jenis\nTI201,Basis Data,2,3,Wajib\n";
        $this->post('/staff-mata-kuliah/impor', ['berkas' => \Illuminate\Http\UploadedFile::fake()->createWithContent('mk.csv', $csv)])
            ->assertSessionHasNoErrors();
        $this->assertSame($ti->id_program_studi, MataKuliah::where('kode_mata_kuliah', 'TI201')->value('program_studi_id'));
    }

    public function test_staff_mengelola_link_media_sosial_website(): void
    {
        $this->actingAs($this->staff());
        $this->get('/staff-profil')->assertOk()->assertSee('Link Media Sosial')->assertSee('https://instagram.com/namaprodi');

        $this->post('/staff-profil', ['nama_prodi' => 'Teknologi Informasi', 'link_media_sosial' => ['bukan-url']])
            ->assertSessionHasErrors('link_media_sosial.0');

        $this->post('/staff-profil', ['nama_prodi' => 'Teknologi Informasi', 'link_media_sosial' => [
            'https://instagram.com/prodi-uji', '', 'https://facebook.com/prodi-uji',
        ]])->assertSessionHasNoErrors();
        $prodi = ProgramStudi::firstOrFail();
        $this->assertSame("https://instagram.com/prodi-uji\nhttps://facebook.com/prodi-uji", $prodi->link_media_sosial);

        auth()->logout();
        $html = $this->get('/')->assertOk()->getContent();
        foreach (['https://instagram.com/prodi-uji', 'https://facebook.com/prodi-uji'] as $url) {
            $this->assertSame(2, substr_count($html, 'href="'.$url.'" target="_blank" rel="noopener noreferrer"'), $url);
        }
        $this->assertStringContainsString('fa-facebook-f', $html);
        $this->get('/berita')->assertSee('href="https://facebook.com/prodi-uji"', false);

        $this->actingAs($this->staff())->post('/staff-profil', ['nama_prodi' => 'Teknologi Informasi', 'link_media_sosial' => ['']])
            ->assertSessionHasNoErrors();
        $this->assertNull($prodi->fresh()->link_media_sosial);
    }

    public function test_berita_dengan_dan_tanpa_link_media_sosial(): void
    {
        $this->actingAs($this->staff());
        $dasar = ['jenis' => Berita::JENIS_BERITA, 'isi' => 'Isi berita.', 'tanggal' => '2026-10-03', 'status' => 'terbit'];

        $this->post('/staff-berita', $dasar + ['judul' => 'Seminar Nasional TI', 'link_media_sosial' => 'bukan url'])
            ->assertSessionHasErrors('link_media_sosial');

        $this->post('/staff-berita', $dasar + ['judul' => 'Seminar Nasional TI', 'link_media_sosial' => 'https://instagram.com/p/contoh'])
            ->assertSessionHasNoErrors();
        $this->post('/staff-berita', $dasar + ['judul' => 'Berita Tanpa Link'])->assertSessionHasNoErrors();

        $seminar = Berita::where('judul', 'Seminar Nasional TI')->firstOrFail();
        $this->assertSame('https://instagram.com/p/contoh', $seminar->link_media_sosial);

        $this->get('/staff-berita')->assertSee('https:\/\/instagram.com\/p\/contoh', false);

        $this->get('/berita/'.$seminar->slug)->assertOk()
            ->assertSee('Lihat Postingan Media Sosial')
            ->assertSee('href="https://instagram.com/p/contoh" target="_blank" rel="noopener noreferrer"', false);
        $this->get('/berita/'.Berita::where('judul', 'Berita Tanpa Link')->value('slug'))->assertOk()
            ->assertDontSee('Lihat Postingan Media Sosial');

        $this->put('/staff-berita/'.$seminar->id_berita, $dasar + ['judul' => 'Seminar Nasional TI', 'link_media_sosial' => 'https://facebook.com/prodi/posts/1'])
            ->assertSessionHasNoErrors();
        $this->assertSame('https://facebook.com/prodi/posts/1', $seminar->fresh()->link_media_sosial);
        $this->put('/staff-berita/'.$seminar->id_berita, $dasar + ['judul' => 'Seminar Nasional TI', 'link_media_sosial' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($seminar->fresh()->link_media_sosial);
    }
}
