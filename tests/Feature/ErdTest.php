<?php

namespace Tests\Feature;

use App\Models\Akreditasi;
use App\Models\Berita;
use App\Models\Dosen;
use App\Models\KegiatanMahasiswa;
use App\Models\LowonganPekerjaan;
use App\Models\Mahasiswa;
use App\Models\Organisasi;
use App\Models\Pengumuman;
use App\Models\Prestasi;
use App\Models\ProgramStudi;
use App\Models\ProspekLulusan;
use App\Models\Ranking;
use App\Models\RankingBobot;
use App\Models\SaranaPrasarana;
use App\Models\StaffProdi;
use App\Models\StrukturOrganisasi;
use App\Models\Testimoni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Uji kesesuaian database, model, dan relasi dengan ERD terbaru (29-09-2026).
 */
class ErdTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** Kolom setiap tabel persis sesuai ERD (urutan tidak dipermasalahkan). */
    private const KOLOM_ERD = [
        'users' => ['id_user', 'name', 'email', 'google_id', 'email_verified_at', 'password', 'role', 'remember_token', 'created_at', 'updated_at'],
        'mahasiswa' => ['nim', 'user_id', 'nama', 'foto', 'angkatan', 'kelas', 'email', 'no_hp', 'ipk', 'status_mahasiswa', 'created_at', 'updated_at'],
        'staff_prodi' => ['id_staff_prodi', 'id_user', 'nip', 'nama', 'foto', 'jabatan', 'email', 'no_hp', 'created_at', 'updated_at'],
        'dosen' => ['nuptk', 'nama', 'foto', 'pendidikan_terakhir', 'google_scholar', 'email', 'alamat', 'tanggal_lahir', 'status', 'created_at', 'updated_at'],
        'program_studi' => ['id_program_studi', 'staff_prodi_id', 'nama_prodi', 'deskripsi', 'visi', 'misi', 'jumlah_alumni', 'jumlah_dosen', 'link_akamawa', 'kode_etik', 'created_at', 'updated_at'],
        'akreditasi' => ['id_akreditasi', 'program_studi_id', 'peringkat', 'nomor_sk', 'tanggal_mulai', 'tanggal_berakhir', 'lembaga', 'dokumen', 'created_at', 'updated_at'],
        'struktur_organisasi' => ['id_struktur_organisasi', 'program_studi_id', 'dosen_id', 'nama', 'jabatan', 'foto', 'created_at', 'updated_at'],
        'organisasi' => ['id_organisasi', 'nim', 'nama_organisasi', 'jabatan', 'created_at', 'updated_at'],
        'prestasi' => ['id_prestasi', 'nim', 'staff_prodi_id', 'judul', 'kategori', 'tingkat', 'penyelenggara', 'tanggal', 'dokumen', 'deskripsi', 'status', 'catatan', 'created_at', 'updated_at'],
        // REVISI DOSEN 01-10-2026: + dasar_pembobotan (alasan bobot AHP).
        'ranking_bobot' => ['id_ranking_bobot', 'kode', 'kriteria', 'bobot', 'tipe_bobot', 'dasar_pembobotan', 'created_at', 'updated_at'],
        'ranking' => ['id_ranking', 'nim', 'ranking_bobot_id', 'nilai_ipk', 'poin_prestasi_akademik', 'poin_prestasi_nonakademik', 'poin_keaktifan_organisasi',
            'normalisasi_nilai_ipk', 'normalisasi_prestasi_akademik', 'normalisasi_prestasi_nonakademik', 'normalisasi_keaktifan_organisasi',
            'peringkat', 'nilai_akhir', 'tahun', 'created_at', 'updated_at'],
        // REVISI DOSEN 01-10-2026: satu pengumuman -> banyak penerima.
        'pengumuman_penerima' => ['id_pengumuman_penerima', 'pengumuman_id', 'nim', 'dibaca_pada', 'created_at', 'updated_at'],
        'pengumuman' => ['id_pengumuman', 'prestasi_id', 'kategori', 'staff_prodi_id', 'nim', 'judul', 'isi', 'status', 'tanggal_dikirim', 'notifikasi', 'dibaca_pada', 'created_at', 'updated_at'],
        'prospek_lulusan' => ['id_prospek_lulusan', 'staff_prodi_id', 'nama', 'kategori', 'deskripsi', 'ikon', 'status', 'created_at', 'updated_at'],
        'testimoni' => ['id_testimoni', 'staff_prodi_id', 'nama', 'tahun_kelulusan', 'nama_perusahaan', 'jabatan', 'foto', 'isi', 'created_at', 'updated_at'],
        'kegiatan_mahasiswa' => ['id_kegiatan_mahasiswa', 'staff_prodi_id', 'judul', 'kategori', 'tanggal', 'lokasi', 'penyelenggara', 'deskripsi', 'foto', 'status', 'created_at', 'updated_at'],
        'berita' => ['id_berita', 'staff_prodi_id', 'judul', 'slug', 'ringkasan', 'kategori', 'isi', 'gambar', 'tanggal', 'status', 'created_at', 'updated_at'],
        'lowongan_pekerjaan' => ['id_lowongan_pekerjaan', 'staff_prodi_id', 'posisi', 'perusahaan', 'lokasi', 'tipe', 'deskripsi', 'link', 'batas_lamaran', 'status', 'created_at', 'updated_at'],
        'sarana_prasarana' => ['id_sarana_prasarana', 'staff_prodi_id', 'nama', 'jenis', 'lokasi', 'kapasitas', 'fasilitas', 'deskripsi', 'foto', 'status', 'created_at', 'updated_at'],
    ];

    /** FK ERD: tabel.kolom => tabel_induk.kolom_induk. */
    private const FK_ERD = [
        'mahasiswa.user_id' => 'users.id_user',
        'staff_prodi.id_user' => 'users.id_user',
        'program_studi.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
        'akreditasi.program_studi_id' => 'program_studi.id_program_studi',
        'struktur_organisasi.program_studi_id' => 'program_studi.id_program_studi',
        'struktur_organisasi.dosen_id' => 'dosen.nuptk',
        'organisasi.nim' => 'mahasiswa.nim',
        'prestasi.nim' => 'mahasiswa.nim',
        'prestasi.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
        'ranking.nim' => 'mahasiswa.nim',
        'ranking.ranking_bobot_id' => 'ranking_bobot.id_ranking_bobot',
        'pengumuman.prestasi_id' => 'prestasi.id_prestasi',
        'pengumuman.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
        'pengumuman.nim' => 'mahasiswa.nim',
        'pengumuman_penerima.pengumuman_id' => 'pengumuman.id_pengumuman',
        'pengumuman_penerima.nim' => 'mahasiswa.nim',
        'prospek_lulusan.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
        'testimoni.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
        'kegiatan_mahasiswa.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
        'berita.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
        'lowongan_pekerjaan.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
        'sarana_prasarana.staff_prodi_id' => 'staff_prodi.id_staff_prodi',
    ];

    public function test_kolom_setiap_tabel_sesuai_erd(): void
    {
        foreach (self::KOLOM_ERD as $tabel => $kolom) {
            $this->assertTrue(Schema::hasTable($tabel), $tabel);
            $ada = Schema::getColumnListing($tabel);
            sort($ada);
            $harap = $kolom;
            sort($harap);
            $this->assertSame($harap, $ada, 'kolom tabel '.$tabel);
        }

        foreach (['pengajuan_perubahan', 'notifikasi', 'verifikasi', 'publikasi', 'profil_lulusan'] as $lama) {
            $this->assertFalse(Schema::hasTable($lama), 'tabel di luar ERD: '.$lama);
        }
    }

    public function test_primary_key_dan_foreign_key_sesuai_erd(): void
    {
        $pk = [
            User::class => 'id_user', Mahasiswa::class => 'nim', StaffProdi::class => 'id_staff_prodi', Dosen::class => 'nuptk',
            ProgramStudi::class => 'id_program_studi', Akreditasi::class => 'id_akreditasi', StrukturOrganisasi::class => 'id_struktur_organisasi',
            Organisasi::class => 'id_organisasi', Prestasi::class => 'id_prestasi', RankingBobot::class => 'id_ranking_bobot',
            Ranking::class => 'id_ranking', Pengumuman::class => 'id_pengumuman', ProspekLulusan::class => 'id_prospek_lulusan',
            Testimoni::class => 'id_testimoni', KegiatanMahasiswa::class => 'id_kegiatan_mahasiswa', Berita::class => 'id_berita',
            LowonganPekerjaan::class => 'id_lowongan_pekerjaan', SaranaPrasarana::class => 'id_sarana_prasarana',
        ];
        foreach ($pk as $model => $kunci) {
            $m = new $model;
            $this->assertSame($kunci, $m->getKeyName(), $model);
            $indeks = collect(Schema::getIndexes($m->getTable()))->firstWhere('primary', true);
            $this->assertSame([$kunci], $indeks['columns'] ?? null, 'PK database '.$m->getTable());
        }

        foreach (self::FK_ERD as $anak => $induk) {
            [$tabel, $kolom] = explode('.', $anak);
            [$tabelInduk, $kolomInduk] = explode('.', $induk);
            $fk = collect(Schema::getForeignKeys($tabel))->first(fn ($f) => $f['columns'] === [$kolom]);
            $this->assertNotNull($fk, 'FK '.$anak);
            $this->assertSame($tabelInduk, $fk['foreign_table'], 'FK '.$anak);
            $this->assertSame([$kolomInduk], $fk['foreign_columns'], 'FK '.$anak);
        }
    }

    public function test_relasi_eloquent_sesuai_erd(): void
    {
        $staff = StaffProdi::firstOrFail();
        $m = Mahasiswa::berprestasi()->firstOrFail();

        // USERS 1-1 MAHASISWA, USERS 1-1 STAFF_PRODI
        $this->assertTrue($m->user->mahasiswa->is($m));
        $this->assertTrue($staff->user->staffProdi->is($staff));

        // MAHASISWA 1-N PRESTASI / ORGANISASI / RANKING, STAFF 1-N MEMVERIFIKASI PRESTASI
        $p = $m->prestasi()->firstOrFail();
        $this->assertTrue($p->mahasiswa->is($m));
        $this->assertTrue($p->staffProdi->is($staff), 'prestasi disetujui memiliki verifikator');
        $this->assertTrue($m->organisasi->every(fn ($o) => $o->mahasiswa->is($m)));
        $r = $m->ranking()->firstOrFail();
        $this->assertTrue($r->mahasiswa->is($m));

        // RANKING N-1 RANKING_BOBOT
        $this->assertNotNull($r->rankingBobot);
        $this->assertTrue($r->rankingBobot->ranking->contains($r));
        $this->assertSame(['C1', 'C2', 'C3', 'C4'], RankingBobot::orderBy('kode')->pluck('kode')->all());
        $this->assertSame('benefit', RankingBobot::value('tipe_bobot'));

        // PROGRAM_STUDI -> AKREDITASI, STRUKTUR_ORGANISASI -> DOSEN
        $prodi = ProgramStudi::create(['nama_prodi' => 'Teknologi Informasi', 'staff_prodi_id' => $staff->id_staff_prodi]);
        $akr = Akreditasi::create(['program_studi_id' => $prodi->id_program_studi, 'peringkat' => 'Baik Sekali']);
        $dosen = Dosen::create(['nuptk' => '9988', 'nama' => 'Dr. Uji', 'status' => 'aktif']);
        $so = StrukturOrganisasi::create(['program_studi_id' => $prodi->id_program_studi, 'dosen_id' => $dosen->nuptk, 'jabatan' => 'Koordinator Program Studi']);
        $this->assertTrue($akr->programStudi->is($prodi));
        $this->assertTrue($prodi->akreditasi->contains($akr));
        $this->assertTrue($prodi->staffProdi->is($staff));
        $this->assertTrue($so->dosen->is($dosen));
        $this->assertTrue($dosen->strukturOrganisasi->contains($so));
        $this->assertTrue($prodi->strukturOrganisasi->contains($so));

        // PENGUMUMAN: MEMBUAT (staff), MENERIMA (mahasiswa), MENDAPAT (prestasi)
        $g = Pengumuman::create(['staff_prodi_id' => $staff->id_staff_prodi, 'nim' => $m->nim, 'prestasi_id' => $p->id_prestasi,
            'kategori' => $p->kategori, 'judul' => 'Uji', 'isi' => 'Isi', 'status' => 'terkirim']);
        $this->assertTrue($g->staffProdi->is($staff));
        $this->assertTrue($g->mahasiswa->is($m));
        $this->assertTrue($g->prestasi->is($p));
        $this->assertTrue($p->pengumuman->is($g));
        $this->assertTrue($m->pengumuman->contains($g));
        $this->assertTrue($staff->pengumuman->contains($g));
    }

    public function test_verifikasi_prestasi_mengisi_staff_dan_notifikasi(): void
    {
        $m = Mahasiswa::firstOrFail();
        $this->actingAs($m->user)->post('/mahasiswa-ajukan-prestasi', [
            'judul' => 'Juara Hackathon', 'kategori' => Prestasi::KATEGORI_AKADEMIK, 'tingkat' => 'Nasional', 'tanggal' => now()->toDateString(),
        ])->assertSessionHasNoErrors();
        $p = Prestasi::where('judul', 'Juara Hackathon')->firstOrFail();
        $this->assertSame($m->nim, $p->nim);
        $this->assertNull($p->staff_prodi_id);

        $staff = User::where('role', 'staff')->firstOrFail();
        $this->actingAs($staff)->put('/staff-prestasi/'.$p->id_prestasi.'/verifikasi', ['status' => 'disetujui'])->assertSessionHasNoErrors();
        $this->assertSame($staff->staffProdi->id_staff_prodi, $p->fresh()->staff_prodi_id);

        // Notifikasi = baris PENGUMUMAN milik mahasiswa (kolom notifikasi), tidak tampil di daftar pengumuman.
        $this->actingAs($m->user);
        $this->get('/mahasiswa-notifikasi')->assertOk()->assertSee('Prestasi disetujui')->assertSee('Prestasi sedang diverifikasi');
        $this->get('/mahasiswa-pengumuman')->assertOk()->assertDontSee('Prestasi disetujui');
        $this->post('/mahasiswa-notifikasi/baca')->assertRedirect('/mahasiswa-notifikasi');
        $this->assertSame(0, Pengumuman::notifikasiUntuk($m->nim)->whereNull('dibaca_pada')->count());
        $this->actingAs($staff)->get('/staff-pengumuman')->assertOk()->assertDontSee('Prestasi disetujui');
    }

    public function test_ubah_nim_mengalir_ke_tabel_anak(): void
    {
        $m = Mahasiswa::berprestasi()->with('user')->firstOrFail();
        $nimLama = $m->nim;
        $jumlah = [
            'prestasi' => Prestasi::where('nim', $nimLama)->count(),
            'organisasi' => Organisasi::where('nim', $nimLama)->count(),
            'ranking' => Ranking::where('nim', $nimLama)->count(),
        ];

        $this->actingAs(User::where('role', 'staff')->firstOrFail())
            ->put('/staff-mahasiswa/'.$nimLama, ['nim' => '2599999999', 'nama' => $m->nama, 'email' => $m->user->email,
                'status_mahasiswa' => 'aktif', 'organisasi' => $m->organisasi->map->only('nama_organisasi', 'jabatan')->all()])
            ->assertSessionHasNoErrors();

        $this->assertNull(Mahasiswa::find($nimLama));
        $this->assertNotNull(Mahasiswa::find('2599999999'));
        $this->assertSame($jumlah['prestasi'], Prestasi::where('nim', '2599999999')->count());
        $this->assertSame($jumlah['organisasi'], Organisasi::where('nim', '2599999999')->count());
        $this->assertSame($jumlah['ranking'], Ranking::where('nim', '2599999999')->count());
    }

    public function test_halaman_dengan_data_di_setiap_tabel(): void
    {
        $staff = User::where('role', 'staff')->firstOrFail();
        $sid = $staff->staffProdi->id_staff_prodi;

        $prodi = ProgramStudi::create(['nama_prodi' => 'Teknologi Informasi', 'staff_prodi_id' => $sid, 'visi' => 'Visi', 'misi' => "Misi 1\nMisi 2"]);
        Akreditasi::create(['program_studi_id' => $prodi->id_program_studi, 'peringkat' => 'Baik Sekali', 'tanggal_mulai' => '2024-01-01']);
        $dosen = Dosen::create(['nuptk' => '1234567890', 'nama' => 'Dr. Dosen Uji', 'status' => 'aktif', 'email' => 'dosen@politala.ac.id']);
        StrukturOrganisasi::create(['program_studi_id' => $prodi->id_program_studi, 'dosen_id' => $dosen->nuptk, 'jabatan' => 'Koordinator Program Studi']);
        StrukturOrganisasi::create(['program_studi_id' => $prodi->id_program_studi, 'nama' => 'Sylvi', 'jabatan' => 'Staff Prodi']);
        ProspekLulusan::create(['staff_prodi_id' => $sid, 'nama' => 'Web Developer', 'kategori' => 'Software', 'ikon' => 'fa-code', 'status' => 'aktif']);
        Testimoni::create(['staff_prodi_id' => $sid, 'nama' => 'Alumni Uji', 'isi' => 'Mantap']);
        KegiatanMahasiswa::create(['staff_prodi_id' => $sid, 'judul' => 'Seminar Uji', 'kategori' => 'Lainnya', 'tanggal' => '2026-09-01', 'status' => 'aktif']);
        Berita::create(['staff_prodi_id' => $sid, 'judul' => 'Berita Uji', 'slug' => 'berita-uji', 'isi' => 'Isi', 'tanggal' => '2026-09-01', 'status' => 'terbit']);
        LowonganPekerjaan::create(['staff_prodi_id' => $sid, 'posisi' => 'Programmer', 'perusahaan' => 'PT Uji', 'link' => 'https://contoh.id', 'status' => 'aktif']);
        SaranaPrasarana::create(['staff_prodi_id' => $sid, 'nama' => 'Lab Uji', 'jenis' => 'Laboratorium', 'status' => 'aktif']);

        foreach (['/', '/profil', '/prospek-lulusan', '/akreditasi', '/struktur-organisasi', '/dosen', '/kurikulum', '/sarana-prasarana',
            '/mahasiswa-berprestasi', '/ranking', '/kegiatan-mahasiswa', '/testimoni', '/lowongan-pekerjaan', '/berita', '/berita/berita-uji',
            '/akamawa', '/kode-etik', '/pengumuman'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/struktur-organisasi')->assertSee('modalDosen1234567890', false);
        $this->get('/dosen')->assertSee('modalDosen1234567890', false);

        $this->actingAs($staff);
        foreach (['/staff-dashboard', '/staff-mahasiswa', '/staff-dosen', '/staff-prestasi', '/staff-ranking', '/staff-pengumuman',
            '/staff-profil', '/staff-profile', '/staff-akreditasi', '/staff-struktur-organisasi', '/staff-prospek-lulusan', '/staff-kurikulum',
            '/staff-sarana-prasarana', '/staff-kegiatan-mahasiswa', '/staff-testimoni', '/staff-berita', '/staff-lowongan'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/staff-berita')->assertSee(route('staff-berita.update', ['berita' => Berita::first()->id_berita]), false);
        $this->get('/staff-struktur-organisasi')->assertSee('value="1234567890"', false);

        // Urutan struktur organisasi mengikuti hierarki jabatan (tanpa kolom urutan).
        $this->assertSame('Koordinator Program Studi', StrukturOrganisasi::urut()->value('jabatan'));
        $this->assertSame(0, DB::table('pengumuman')->whereNull('nim')->count());
    }
}
