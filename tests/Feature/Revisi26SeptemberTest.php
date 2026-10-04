<?php

namespace Tests\Feature;

use App\Exceptions\GoogleLoginDitolak;
use App\Mail\PengumumanMahasiswaBerprestasi;
use App\Models\Berita;
use App\Models\Dosen;
use App\Models\LowonganPekerjaan;
use App\Models\Mahasiswa;
use App\Models\StaffProdi;
use App\Models\StrukturOrganisasi;
use App\Models\User;
use App\Services\GoogleLoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Revisi26SeptemberTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function staff(): User
    {
        return User::where('role', 'staff')->firstOrFail();
    }

    public function test_struktur_database_revisi(): void
    {
        $this->assertFalse(Schema::hasColumn('dosen', 'user_id'), 'dosen tidak lagi punya akun');
        $this->assertTrue(Schema::hasColumn('dosen', 'pendidikan_terakhir'));
        $this->assertFalse(Schema::hasColumn('dosen', 'jabatan'), 'jabatan dosen dihapus (revisi 28-09)');
        $this->assertFalse(Schema::hasColumn('dosen', 'riwayat_pendidikan'));
        $this->assertFalse(Schema::hasTable('verifikasi'));
        $this->assertFalse(Schema::hasTable('publikasi'));
        foreach (['pengajuan_perubahan', 'notifikasi'] as $t) {
            $this->assertFalse(Schema::hasTable($t), $t);
        }
        $this->assertTrue(Schema::hasTable('pengumuman_penerima'));
        foreach (['struktur_organisasi', 'berita', 'lowongan_pekerjaan', 'testimoni'] as $t) {
            $this->assertTrue(Schema::hasTable($t), $t);
        }
        $this->assertTrue(Schema::hasColumn('users', 'google_id'));
        $this->assertSame(['mahasiswa', 'staff'], array_keys(User::ROLE));
    }

    public function test_crud_struktur_organisasi_dan_tampil_di_profil(): void
    {
        $this->actingAs($this->staff());
        $dosen = Dosen::create(['nuptk' => '123', 'nama' => 'Dr. Budi', 'status' => 'aktif']);

        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Program Studi'])
            ->assertSessionHasErrors('nama');
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Program Studi', 'dosen_id' => $dosen->nuptk])
            ->assertSessionHasNoErrors();
        $this->post('/staff-struktur-organisasi', ['jabatan' => 'Koordinator Gugus Kendali Mutu', 'nama' => 'Ibu Rina, M.Kom'])
            ->assertSessionHasNoErrors();

        $kaprodi = StrukturOrganisasi::where('jabatan', 'Koordinator Program Studi')->firstOrFail();
        $this->assertSame('Dr. Budi', $kaprodi->nama_pejabat);

        $this->get('/struktur-organisasi')->assertOk()->assertSee('Koordinator Program Studi')->assertSee('Dr. Budi')
            ->assertSee('Koordinator Gugus Kendali Mutu')->assertSee('Ibu Rina, M.Kom');
        $this->get('/profil')->assertSee('Koordinator Gugus Kendali Mutu');

        $this->put('/staff-struktur-organisasi/'.$kaprodi->id_struktur_organisasi, ['jabatan' => 'Koordinator Program Studi', 'nama' => 'Pak Andi'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Pak Andi', $kaprodi->fresh()->nama_pejabat);

        $gugus = StrukturOrganisasi::where('jabatan', 'Koordinator Gugus Kendali Mutu')->first();
        $gugus->update(['dosen_id' => $dosen->nuptk]);
        $dosen->delete();
        $this->assertNull($gugus->fresh()->dosen_id);

        $this->delete('/staff-struktur-organisasi/'.$kaprodi->id_struktur_organisasi)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('struktur_organisasi', ['id_struktur_organisasi' => $kaprodi->id_struktur_organisasi]);
    }

    public function test_crud_berita(): void
    {
        Storage::fake('public');
        $this->actingAs($this->staff());

        $this->post('/staff-berita', ['judul' => '', 'isi' => '', 'tanggal' => '', 'status' => 'terbit'])
            ->assertSessionHasErrors(['judul', 'isi', 'tanggal']);
        $this->post('/staff-berita', ['judul' => 'Kuliah Umum AI', 'kategori' => 'Kegiatan', 'isi' => "Paragraf satu.\nParagraf dua.",
            'tanggal' => '2026-09-20', 'status' => 'terbit', 'gambar' => $this->gambarPalsu('a.jpg')])->assertSessionHasNoErrors();
        $this->post('/staff-berita', ['judul' => 'Kuliah Umum AI', 'isi' => 'Draft', 'tanggal' => '2026-09-21', 'status' => 'draft'])
            ->assertSessionHasNoErrors();

        $terbit = Berita::where('status', 'terbit')->firstOrFail();
        $draft = Berita::where('status', 'draft')->firstOrFail();
        $this->assertSame('kuliah-umum-ai', $terbit->slug);
        $this->assertSame('kuliah-umum-ai-2', $draft->slug);
        Storage::disk('public')->assertExists($terbit->gambar);

        $this->get('/berita')->assertOk()->assertSee('Kuliah Umum AI')->assertSee('Kegiatan');
        $this->get('/berita/kuliah-umum-ai')->assertOk()->assertSee('Paragraf dua.');
        $this->get('/berita/kuliah-umum-ai-2')->assertNotFound();
        $this->get('/')->assertSee('Kuliah Umum AI');

        $this->put('/staff-berita/'.$terbit->id_berita, ['judul' => 'Kuliah Umum Kecerdasan Buatan', 'isi' => 'Isi baru', 'tanggal' => '2026-09-20', 'status' => 'terbit'])
            ->assertSessionHasNoErrors();
        $this->assertSame('kuliah-umum-kecerdasan-buatan', $terbit->fresh()->slug);

        $this->delete('/staff-berita/'.$draft->id_berita)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('berita', ['id_berita' => $draft->id_berita]);
    }

    public function test_crud_lowongan_pekerjaan(): void
    {
        $this->actingAs($this->staff());

        $this->post('/staff-lowongan', ['posisi' => 'Web Dev', 'perusahaan' => 'PT A', 'link' => 'bukan-url'])
            ->assertSessionHasErrors('link');
        $this->post('/staff-lowongan', ['posisi' => 'Web Developer', 'perusahaan' => 'PT Maju', 'tipe' => 'Magang',
            'link' => 'https://karier.example.com/web', 'batas_lamaran' => now()->addWeek()->format('Y-m-d')])
            ->assertSessionHasNoErrors();
        $this->post('/staff-lowongan', ['posisi' => 'Lowongan Lama', 'perusahaan' => 'PT Lama',
            'link' => 'https://karier.example.com/lama', 'batas_lamaran' => now()->subDay()->format('Y-m-d')])
            ->assertSessionHasNoErrors();
        $this->post('/staff-lowongan', ['posisi' => 'Lowongan Tanpa Batas', 'perusahaan' => 'PT X',
            'link' => 'https://karier.example.com/x'])->assertSessionHasNoErrors();
        $this->get('/staff-lowongan')->assertOk()->assertDontSee('name="status"', false)->assertDontSee('<th>Status</th>', false);

        $this->get('/lowongan-pekerjaan')->assertOk()->assertSee('Web Developer')->assertSee('https://karier.example.com/web')
            ->assertDontSee('Lowongan Lama')->assertSee('Lowongan Tanpa Batas');
        $this->get('/lowongan-pekerjaan?tipe=Magang')->assertSee('Web Developer');
        $this->get('/')->assertSee('Web Developer');

        $l = LowonganPekerjaan::where('posisi', 'Web Developer')->first();
        $this->put('/staff-lowongan/'.$l->id_lowongan_pekerjaan, ['posisi' => 'Web Developer', 'perusahaan' => 'PT Maju', 'link' => 'https://karier.example.com/web', 'batas_lamaran' => now()->subDays(2)->format('Y-m-d')])
            ->assertSessionHasNoErrors();
        $this->get('/lowongan-pekerjaan')->assertDontSee('Web Developer');
        $this->delete('/staff-lowongan/'.$l->id_lowongan_pekerjaan)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('lowongan_pekerjaan', ['id_lowongan_pekerjaan' => $l->id_lowongan_pekerjaan]);
    }

    public function test_email_pengumuman_ke_mahasiswa_berprestasi(): void
    {
        Mail::fake();
        $this->actingAs($this->staff());
        $m = Mahasiswa::berprestasi()->with('user')->firstOrFail();
        $p = $m->prestasiDisetujui()->first();

        $this->post('/staff-pengumuman', ['penerima' => [$m->nim], 'prestasi_id' => $p->id_prestasi, 'kategori' => $p->kategori,
            'judul' => 'Draft dulu', 'isi' => 'x', 'status' => 'draft'])->assertSessionHasNoErrors();
        Mail::assertNothingSent();

        $this->post('/staff-pengumuman', ['penerima' => [$m->nim], 'prestasi_id' => $p->id_prestasi, 'kategori' => $p->kategori,
            'judul' => 'Undangan Apresiasi', 'isi' => 'Selamat atas prestasinya.', 'status' => 'terkirim'])->assertSessionHasNoErrors();
        Mail::assertSent(PengumumanMahasiswaBerprestasi::class, fn ($mail) => $mail->hasTo($m->user->email)
            && $mail->pengumuman->judul === 'Undangan Apresiasi');

        $html = (new PengumumanMahasiswaBerprestasi(\App\Models\Pengumuman::latest('id_pengumuman')->first(), $m))->render();
        $this->assertStringContainsString('Selamat atas prestasinya.', $html);
    }

    public function test_perubahan_data_tanpa_verifikasi(): void
    {
        $m = Mahasiswa::firstOrFail();
        $this->actingAs($m->user);
        $baru = $m->nim.'x@mhs.politala.ac.id';

        $this->post('/mahasiswa-profile', ['email' => $baru, 'no_hp' => '085712345678'])
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame($baru, $m->user->fresh()->email);
        $this->assertSame($baru, $m->fresh()->email);
        $this->assertFalse(Schema::hasTable('pengajuan_perubahan'), 'riwayat perubahan tidak ada di ERD');
    }

    public function test_tombol_dan_route_login_google(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login dengan Google')->assertSee(route('login.google'), false)
            ->assertSee('fa-google', false)->assertDontSee('Dosen</option>', false);

        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);
        $this->get('/auth/google')->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_aturan_domain_google_mahasiswa_dan_staff(): void
    {
        $svc = app(GoogleLoginService::class);
        $mhs = Mahasiswa::with('user')->firstOrFail()->user;

        $u = $svc->cariAkun(strtoupper($mhs->email), true, 'g-1');
        $this->assertTrue($u->is($mhs));
        $this->assertSame('g-1', $mhs->fresh()->google_id);

        $this->assertDitolak(fn () => $svc->cariAkun($mhs->email, true, 'g-2'));

        $this->assertTrue($svc->cariAkun('staff@politala.ac.id', true, 'g-staff')->is($this->staff()));

        $this->assertDitolak(fn () => $svc->cariAkun('staff@politala.ac.id', false, 'g-staff'));

        foreach (['example@gmail.com', 'example@yahoo.com', 'example@mhs.universitaslain.ac.id',
            'example@politala.ac.id.evil.com', 'example@xmhs.politala.ac.id'] as $email) {
            $this->assertDitolak(fn () => $svc->cariAkun($email, true, 'g-x'), $email);
        }

        $jumlah = User::count();
        $this->assertDitolak(fn () => $svc->cariAkun('baru@politala.ac.id', true, 'g-3'));
        $this->assertDitolak(fn () => $svc->cariAkun('999999@mhs.politala.ac.id', true, 'g-4'));
        $this->assertSame($jumlah, User::count());

        $palsu = User::create(['name' => 'Palsu', 'email' => 'palsu@mhs.politala.ac.id', 'password' => 'x', 'role' => 'staff']);
        StaffProdi::create(['id_user' => $palsu->id_user, 'nip' => '1', 'nama' => 'Palsu']);
        $this->assertDitolak(fn () => $svc->cariAkun('palsu@mhs.politala.ac.id', true, 'g-5'));

        $this->staff()->staffProdi->delete();
        $this->assertDitolak(fn () => $svc->cariAkun('staff@politala.ac.id', true, 'g-staff'));
    }

    private function assertDitolak(callable $fn, string $pesan = ''): void
    {
        try {
            $fn();
            $this->fail('Seharusnya ditolak: '.$pesan);
        } catch (GoogleLoginDitolak $e) {
            $this->assertNotSame('', $e->getMessage());
        }
    }
}
