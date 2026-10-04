<?php

return [
    'kriteria' => [
        'C1' => [
            'nama' => 'Nilai Akademik', 'sumber' => 'IPK', 'bobot' => 0.48, 'tipe' => 'benefit',
            'dasar' => 'Nilai Akademik (IPK) diberi bobot terbesar karena IPK adalah indikator resmi dan terukur atas keberhasilan studi yang dimiliki oleh seluruh mahasiswa, sehingga menjadi dasar paling adil dan konsisten untuk menilai mahasiswa berprestasi. Dalam perbandingan berpasangan AHP, kriteria ini dinilai lebih penting daripada ketiga kriteria lainnya.',
        ],
        'C2' => [
            'nama' => 'Prestasi Akademik', 'sumber' => 'Poin prestasi akademik', 'bobot' => 0.29, 'tipe' => 'benefit',
            'dasar' => 'Prestasi Akademik (lomba, karya ilmiah, atau kompetisi bidang keilmuan) menunjukkan kemampuan mahasiswa menerapkan ilmu Teknologi Informasi di luar perkuliahan. Bobotnya terbesar kedua karena berkaitan langsung dengan kompetensi akademik, tetapi tidak dimiliki semua mahasiswa sehingga ditempatkan di bawah IPK.',
        ],
        'C3' => [
            'nama' => 'Prestasi Non-Akademik', 'sumber' => 'Poin prestasi non-akademik', 'bobot' => 0.15, 'tipe' => 'benefit',
            'dasar' => 'Prestasi Non-Akademik (seni, olahraga, dan kompetisi umum) mengukur pencapaian mahasiswa berdasarkan tingkat kejuaraan (internal, regional, nasional, internasional). Bobotnya lebih kecil daripada prestasi akademik karena bersifat pendukung dan tidak berhubungan langsung dengan bidang keilmuan program studi.',
        ],
        'C4' => [
            'nama' => 'Keaktifan Organisasi', 'sumber' => 'Poin jabatan organisasi', 'bobot' => 0.08, 'tipe' => 'benefit',
            'dasar' => 'Keaktifan Organisasi menilai keterlibatan mahasiswa dalam organisasi berdasarkan jabatan atau tingkat tanggung jawab (anggota sampai ketua). Bobotnya paling kecil karena berfungsi sebagai nilai tambah soft skill (kepemimpinan dan kerja sama), bukan penentu utama prestasi mahasiswa.',
        ],
    ],

    'ahp' => [
        'perbandingan' => [
            'C1' => ['C2' => 2, 'C3' => 3, 'C4' => 5],
            'C2' => ['C3' => 2, 'C4' => 4],
            'C3' => ['C4' => 2],
        ],
        'indeks_random' => [1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49],
        'batas_cr' => 0.10,
        'presisi_bobot' => 2,
        'skala' => [
            1 => 'sama penting',
            2 => 'sedikit lebih penting (nilai antara 1 dan 3)',
            3 => 'sedikit lebih penting',
            4 => 'lebih penting (nilai antara 3 dan 5)',
            5 => 'jelas lebih penting',
            6 => 'jelas lebih penting (nilai antara 5 dan 7)',
            7 => 'sangat jelas lebih penting',
            8 => 'sangat jelas lebih penting (nilai antara 7 dan 9)',
            9 => 'mutlak lebih penting',
        ],
    ],

    'tingkat' => ['Internal', 'Regional', 'Nasional', 'Internasional'],

    'prestasi_akademik' => [
        'skor' => [
            'Internal' => 25,
            'Regional' => 45,
            'Nasional' => 70,
            'Internasional' => 100,
        ],
        'bonus_per_tambahan' => 5,
        'maks_tambahan' => 2,
    ],

    'prestasi_non_akademik' => [
        'skor' => [
            'Internal' => 25,
            'Regional' => 50,
            'Nasional' => 75,
            'Internasional' => 100,
        ],
        'bonus_per_tambahan' => 2,
        'maks_tambahan' => 2,
    ],

    'organisasi' => [
        'jabatan' => [
            'Anggota' => 20,
            'Divisi' => 35,
            'Pengurus Inti' => 50,
            'Sekretaris' => 60,
            'Bendahara' => 60,
            'Koordinator' => 70,
            'Wakil Ketua' => 85,
            'Ketua' => 100,
            'Gubernur' => 100,
        ],
        'bonus_organisasi_kedua' => 0.10,
    ],

    'skor_maksimal' => 100,
];
