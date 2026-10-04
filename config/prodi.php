<?php

return [
    'sosial_media' => [
        'instagram' => [
            'label' => 'Instagram',
            'ikon' => 'fa-brands fa-instagram',
            'url' => env('PRODI_INSTAGRAM_URL'),
            'nama_akun' => env('PRODI_INSTAGRAM_NAMA', '@ti.politala'),
        ],
        'youtube' => [
            'label' => 'YouTube',
            'ikon' => 'fa-brands fa-youtube',
            'url' => env('PRODI_YOUTUBE_URL'),
            'nama_akun' => env('PRODI_YOUTUBE_NAMA', 'Teknologi Informasi Channel'),
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'ikon' => 'fa-brands fa-tiktok',
            'url' => env('PRODI_TIKTOK_URL'),
            'nama_akun' => env('PRODI_TIKTOK_NAMA', '@ti.politala'),
        ],
    ],

];
