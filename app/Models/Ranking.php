<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    use HasFactory;

    protected $table = 'ranking';

    protected $primaryKey = 'id_ranking';

    protected $fillable = [
        'nim',
        'ranking_bobot_id',
        'nilai_ipk',
        'poin_prestasi_akademik',
        'poin_prestasi_nonakademik',
        'poin_keaktifan_organisasi',
        'normalisasi_nilai_ipk',
        'normalisasi_prestasi_akademik',
        'normalisasi_prestasi_nonakademik',
        'normalisasi_keaktifan_organisasi',
        'peringkat',
        'nilai_akhir',
        'tahun',
    ];

    protected $casts = [
        'peringkat' => 'integer',
        'nilai_akhir' => 'float',
        'nilai_ipk' => 'float',
        'poin_prestasi_akademik' => 'float',
        'poin_prestasi_nonakademik' => 'float',
        'poin_keaktifan_organisasi' => 'float',
        'normalisasi_nilai_ipk' => 'float',
        'normalisasi_prestasi_akademik' => 'float',
        'normalisasi_prestasi_nonakademik' => 'float',
        'normalisasi_keaktifan_organisasi' => 'float',
        'tahun' => 'integer',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function rankingBobot(): BelongsTo
    {
        return $this->belongsTo(RankingBobot::class, 'ranking_bobot_id', 'id_ranking_bobot');
    }
}
