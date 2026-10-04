<?php

namespace App\Exceptions;

use RuntimeException;

class GoogleLoginDitolak extends RuntimeException
{
    public const PESAN_UMUM = 'Email Google tidak terdaftar atau tidak menggunakan domain Politala yang diizinkan.';
}
