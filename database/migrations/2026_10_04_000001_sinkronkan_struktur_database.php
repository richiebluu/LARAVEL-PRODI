<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->rankingBobot();
        $this->programStudi();
        $this->prospekLulusan();
        $this->saranaPrasarana();
        $this->lowonganPekerjaan();
        $this->berita();
        $this->mataKuliah();

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('job_batches');
    }

    public function down(): void
    {
    }

    private function rankingBobot(): void
    {
        if (Schema::hasColumn('ranking_bobot', 'dasar_pembobotan')) {
            return;
        }

        Schema::table('ranking_bobot', function (Blueprint $table) {
            $table->text('dasar_pembobotan')->nullable()->after('tipe_bobot');
        });

        foreach ((array) config('saw.kriteria') as $kode => $kriteria) {
            if (filled($kriteria['dasar'] ?? null)) {
                DB::table('ranking_bobot')->where('kode', $kode)->update(['dasar_pembobotan' => $kriteria['dasar']]);
            }
        }
    }

    private function programStudi(): void
    {
        if (! Schema::hasColumn('program_studi', 'link_media_sosial')) {
            Schema::table('program_studi', function (Blueprint $table) {
                $table->text('link_media_sosial')->nullable()->after('kode_etik');
            });
        }
    }

    private function prospekLulusan(): void
    {
        $this->hapusKolom('prospek_lulusan', ['kategori']);
    }

    private function saranaPrasarana(): void
    {
        if (! Schema::hasColumn('sarana_prasarana', 'gedung')) {
            Schema::table('sarana_prasarana', function (Blueprint $table) {
                $table->string('gedung', 100)->nullable()->after('nama');
            });

            if (Schema::hasColumn('sarana_prasarana', 'lokasi')) {
                DB::table('sarana_prasarana')->get(['id_sarana_prasarana', 'nama', 'lokasi'])->each(function ($s) {
                    $teks = mb_strtolower($s->lokasi.' '.$s->nama);
                    $gedung = match (true) {
                        (bool) preg_match('/adriansyah\s*(2|ii)\b/', $teks) => 'Adriansyah 2',
                        (bool) preg_match('/adriansyah\s*(1|i)\b/', $teks) => 'Adriansyah 1',
                        str_contains($teks, 'teknik informatika'), (bool) preg_match('/gedung\s+ti\b/', $teks) => 'Gedung Teknik Informatika',
                        default => null,
                    };

                    if ($gedung) {
                        DB::table('sarana_prasarana')->where('id_sarana_prasarana', $s->id_sarana_prasarana)->update(['gedung' => $gedung]);
                    }
                });
            }
        }

        $this->hapusKolom('sarana_prasarana', ['jenis', 'lokasi', 'deskripsi']);
    }

    private function lowonganPekerjaan(): void
    {
        if (! Schema::hasColumn('lowongan_pekerjaan', 'status')) {
            return;
        }

        DB::table('lowongan_pekerjaan')
            ->where('status', 'nonaktif')
            ->where(fn ($q) => $q->whereNull('batas_lamaran')->orWhereDate('batas_lamaran', '>=', now()->toDateString()))
            ->update(['batas_lamaran' => now()->subDay()->toDateString()]);

        $this->hapusKolom('lowongan_pekerjaan', ['status']);
    }

    private function berita(): void
    {
        Schema::table('berita', function (Blueprint $table) {
            if (! Schema::hasColumn('berita', 'jenis')) {
                $table->string('jenis', 30)->default('berita')->after('slug');
            }
            if (! Schema::hasColumn('berita', 'lokasi')) {
                $table->string('lokasi', 150)->nullable()->after('kategori');
            }
            if (! Schema::hasColumn('berita', 'penyelenggara')) {
                $table->string('penyelenggara', 150)->nullable()->after('lokasi');
            }
            if (! Schema::hasColumn('berita', 'link_media_sosial')) {
                $table->string('link_media_sosial', 500)->nullable()->after('gambar');
            }
        });

        if (Schema::hasTable('kegiatan_mahasiswa')) {
            $sudahDisalin = Schema::hasColumn('berita', 'kegiatan_mahasiswa_id')
                ? DB::table('berita')->whereNotNull('kegiatan_mahasiswa_id')->pluck('kegiatan_mahasiswa_id')->all()
                : null;

            if ($sudahDisalin !== null) {
                DB::table('kegiatan_mahasiswa')
                    ->whereNotIn('id_kegiatan_mahasiswa', $sudahDisalin ?: [0])
                    ->orderBy('id_kegiatan_mahasiswa')
                    ->get()
                    ->each(fn ($k) => $this->salinKegiatan($k));
            } elseif (! DB::table('berita')->where('jenis', 'kegiatan_mahasiswa')->exists()) {
                DB::table('kegiatan_mahasiswa')->orderBy('id_kegiatan_mahasiswa')->get()->each(fn ($k) => $this->salinKegiatan($k));
            }
        }

        if (Schema::hasColumn('berita', 'kegiatan_mahasiswa_id')) {
            Schema::table('berita', function (Blueprint $table) {
                $table->dropUnique(['kegiatan_mahasiswa_id']);
            });
            $this->hapusKolom('berita', ['kegiatan_mahasiswa_id']);
        }

        Schema::dropIfExists('kegiatan_mahasiswa');
    }

    private function salinKegiatan(object $k): void
    {
        $dasar = Str::slug((string) $k->judul) ?: 'kegiatan-mahasiswa';
        $slug = $dasar;
        $i = 2;
        while (DB::table('berita')->where('slug', $slug)->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        DB::table('berita')->insert([
            'staff_prodi_id' => $k->staff_prodi_id,
            'judul' => $k->judul,
            'slug' => $slug,
            'jenis' => 'kegiatan_mahasiswa',
            'kategori' => $k->kategori,
            'lokasi' => $k->lokasi,
            'penyelenggara' => $k->penyelenggara,
            'isi' => filled($k->deskripsi) ? $k->deskripsi : $k->judul,
            'gambar' => $k->foto,
            'tanggal' => $k->tanggal,
            'status' => $k->status === 'aktif' ? 'terbit' : 'draft',
            'created_at' => $k->created_at ?? now(),
            'updated_at' => $k->updated_at ?? now(),
        ]);
    }

    private function mataKuliah(): void
    {
        if (! Schema::hasColumn('mata_kuliah', 'id')) {
            return;
        }

        if (Schema::hasColumn('mata_kuliah', 'program_studi_id')) {
            Schema::table('mata_kuliah', function (Blueprint $table) {
                $table->dropForeign(['program_studi_id']);
            });
        }

        Schema::rename('mata_kuliah', 'mata_kuliah_lama');

        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->string('kode_mata_kuliah', 20)->primary();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi', 'id_program_studi')->nullOnDelete();
            $table->string('nama', 150);
            $table->unsignedTinyInteger('semester');
            $table->unsignedTinyInteger('sks');
            $table->string('jenis', 50);
            $table->timestamps();
        });

        $prodiTunggal = DB::table('program_studi')->count() === 1
            ? DB::table('program_studi')->value('id_program_studi')
            : null;
        $adaProdi = Schema::hasColumn('mata_kuliah_lama', 'program_studi_id');

        DB::table('mata_kuliah_lama')->orderBy('id')->get()->each(function ($mk) use ($prodiTunggal, $adaProdi) {
            DB::table('mata_kuliah')->insertOrIgnore([
                'kode_mata_kuliah' => strtoupper(trim($mk->kode)),
                'program_studi_id' => ($adaProdi ? $mk->program_studi_id : null) ?? $prodiTunggal,
                'nama' => $mk->nama,
                'semester' => $mk->semester,
                'sks' => $mk->sks,
                'jenis' => $mk->jenis,
                'created_at' => $mk->created_at,
                'updated_at' => $mk->updated_at,
            ]);
        });

        Schema::drop('mata_kuliah_lama');
    }

    private function hapusKolom(string $tabel, array $kolom): void
    {
        foreach ($kolom as $k) {
            if (Schema::hasColumn($tabel, $k)) {
                Schema::table($tabel, function (Blueprint $table) use ($k) {
                    $table->dropColumn($k);
                });
            }
        }
    }
};
