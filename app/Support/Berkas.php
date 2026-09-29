<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Helper kecil untuk mengubah nilai kolom foto/dokumen menjadi URL yang bisa dipakai.
 * Mendukung dua bentuk nilai:
 *  - path hasil upload ke disk "public"  -> dijadikan /storage/...
 *  - URL lengkap (http/https)            -> dipakai apa adanya
 */
class Berkas
{
    public static function url(?string $nilai, ?string $bawaan = null): ?string
    {
        if (blank($nilai)) {
            return $bawaan;
        }

        if (Str::startsWith($nilai, ['http://', 'https://', '//', 'data:'])) {
            return $nilai;
        }

        return Storage::disk('public')->url($nilai);
    }

    /** Hapus berkas upload lama di disk "public" (URL eksternal diabaikan). */
    public static function hapus(?string $nilai): void
    {
        if (blank($nilai) || Str::startsWith($nilai, ['http://', 'https://', '//', 'data:'])) {
            return;
        }

        Storage::disk('public')->delete($nilai);
    }
}
