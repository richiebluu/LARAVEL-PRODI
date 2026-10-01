<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RankingBobot extends Model
{
    use HasFactory;

    protected $table = 'ranking_bobot';

    /** ERD: primary key RANKING_BOBOT = id_ranking_bobot. */
    protected $primaryKey = 'id_ranking_bobot';

    protected $fillable = [
        'kode',
        'kriteria',
        'bobot',
        'tipe_bobot',
        // REVISI DOSEN 01-10-2026: alasan/dasar mengapa kriteria diberi bobot tersebut.
        'dasar_pembobotan',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
    ];

    /**
     * Bobot kriteria bersifat TETAP (revisi dosen): nilainya adalah hasil AHP
     * (RankingController::hitungAHP) yang dibulatkan 2 desimal, dan tidak dapat
     * diketik manual lewat interface. Yang dapat disunting Staff Prodi hanya
     * teks `dasar_pembobotan` (alasan pembobotan untuk dokumentasi/laporan).
     */

    /** Persentase bobot, mis. 0.48 -> "48". */
    public function getPersenAttribute(): string
    {
        return rtrim(rtrim(number_format((float) $this->bobot * 100, 2), '0'), '.');
    }

    /** ERD: RANKING_BOBOT (1) -- MENGGUNAKAN --> RANKING (N). */
    public function ranking(): HasMany
    {
        return $this->hasMany(Ranking::class, 'ranking_bobot_id', 'id_ranking_bobot');
    }
}
