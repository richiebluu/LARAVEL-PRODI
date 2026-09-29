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
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
    ];

    /**
     * Bobot kriteria bersifat TETAP (revisi dosen): nilainya berasal dari
     * config/saw.php (salinan sheet "Bobot Kriteria" Excel) dan tidak dapat
     * diubah lewat interface. Tidak ada route/action untuk mengedit bobot.
     */

    /** ERD: RANKING_BOBOT (1) -- MENGGUNAKAN --> RANKING (N). */
    public function ranking(): HasMany
    {
        return $this->hasMany(Ranking::class, 'ranking_bobot_id', 'id_ranking_bobot');
    }
}
