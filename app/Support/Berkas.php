<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public static function hapus(?string $nilai): void
    {
        if (blank($nilai) || Str::startsWith($nilai, ['http://', 'https://', '//', 'data:'])) {
            return;
        }

        Storage::disk('public')->delete($nilai);
    }
}
