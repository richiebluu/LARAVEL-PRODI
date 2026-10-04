<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RankingBobot extends Model
{
    use HasFactory;

    protected $table = 'ranking_bobot';

    protected $primaryKey = 'id_ranking_bobot';

    protected $fillable = [
        'kode',
        'kriteria',
        'bobot',
        'tipe_bobot',
        'dasar_pembobotan',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
    ];

    public function getPersenAttribute(): string
    {
        return rtrim(rtrim(number_format((float) $this->bobot * 100, 2), '0'), '.');
    }

    public function ranking(): HasMany
    {
        return $this->hasMany(Ranking::class, 'ranking_bobot_id', 'id_ranking_bobot');
    }
}
