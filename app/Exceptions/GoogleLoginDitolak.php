<?php

namespace App\Exceptions;

use RuntimeException;

/** Akun Google ditolak (domain tidak diizinkan / tidak terdaftar / tidak terverifikasi). */
class GoogleLoginDitolak extends RuntimeException
{
    public const PESAN_UMUM = 'Email Google tidak terdaftar atau tidak menggunakan domain Politala yang diizinkan.';
}
