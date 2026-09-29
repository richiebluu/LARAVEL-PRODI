<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Akreditasi extends Model
{
    use HasFactory;

    protected $table = 'akreditasi';

    /** ERD: primary key AKREDITASI = id_akreditasi. */
    protected $primaryKey = 'id_akreditasi';

    protected $fillable = [
        'program_studi_id',
        'peringkat',
        'nomor_sk',
        'tanggal_mulai',
        'tanggal_berakhir',
        'lembaga',
        'dokumen',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    /** ERD: AKREDITASI (N) -- MEMILIKI --> PROGRAM_STUDI (1). */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id', 'id_program_studi');
    }

    /** URL dokumen SK akreditasi. */
    public function getDokumenUrlAttribute(): ?string
    {
        return \App\Support\Berkas::url($this->dokumen);
    }

    /**
     * Status akreditasi diturunkan dari masa berlaku, bukan kolom hardcode.
     */
    public function getStatusAttribute(): string
    {
        if ($this->tanggal_berakhir && $this->tanggal_berakhir->isPast()) {
            return 'Masa Berlaku Berakhir';
        }

        return 'Terakreditasi';
    }

    /** Tahun penetapan diambil dari tanggal mulai. */
    public function getTahunAttribute(): ?string
    {
        return $this->tanggal_mulai?->format('Y');
    }
}
