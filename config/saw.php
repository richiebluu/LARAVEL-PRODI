<?php

/*
|--------------------------------------------------------------------------
| Skema Penilaian Mahasiswa Berprestasi — Metode AHP (bobot) + SAW (ranking)
|--------------------------------------------------------------------------
|
| AHP (Analytic Hierarchy Process) dipakai untuk MENENTUKAN BOBOT kriteria,
| SAW (Simple Additive Weighting) dipakai untuk PERANGKINGAN mahasiswa.
|
| REVISI DOSEN 01-10-2026: perhitungan AHP sekarang dijalankan dan terlihat
| langkah demi langkah di App\Http\Controllers\Staff\RankingController::hitungAHP()
| dari matriks perbandingan berpasangan pada bagian 'ahp' di bawah.
| Hasil AHP: C1=0.4758, C2=0.2884, C3=0.1544, C4=0.0813,
| lambda max = 4.0211, CI = 0.0070, CR = 0.0078 (konsisten karena <= 0.1).
|
| Kolom `ranking_bobot.bobot` di database hanya presisi 2 desimal
| (decimal(5,2)), sehingga bobot hasil AHP dibulatkan 2 desimal
| (0.48/0.29/0.15/0.08 — tetap berjumlah 1.00). Nilai 'bobot' pada
| 'kriteria' di bawah adalah hasil pembulatan tersebut (dipakai seeder);
| RankingController memeriksa dan menerapkan ulang bobot hasil AHP
| sebelum ranking SAW disimpan.
|
| Skema skor (prestasi_akademik, prestasi_non_akademik, organisasi) TIDAK
| berubah — masih sama seperti file acuan
| "DATA DUMMY MAHASISWA_METODE SAW_KELOMPOK-3.xlsx".
|
*/

return [

    /* ---------- Bobot Kriteria (C1..C4) hasil AHP, semuanya bertipe benefit ----------
     * 'dasar' = DASAR PEMBOBOTAN bawaan (revisi dosen 01-10-2026). Teks ini disalin ke
     * kolom ranking_bobot.dasar_pembobotan dan dapat disunting Staff Prodi. */
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

    /* ---------- AHP — penentuan bobot kriteria ----------
     * Matriks perbandingan berpasangan (skala Saaty 1-9), cukup diisi segitiga atas:
     * 'C1' => ['C2' => 2] artinya C1 dua kali lebih penting daripada C2.
     * Sel diagonal (=1) dan resiprokal (C2 vs C1 = 1/2) dibentuk otomatis di controller.
     *
     *            C1     C2     C3     C4
     *      C1    1      2      3      5
     *      C2    1/2    1      2      4
     *      C3    1/3    1/2    1      2
     *      C4    1/5    1/4    1/2    1
     */
    'ahp' => [
        'perbandingan' => [
            'C1' => ['C2' => 2, 'C3' => 3, 'C4' => 5],
            'C2' => ['C3' => 2, 'C4' => 4],
            'C3' => ['C4' => 2],
        ],
        // Random Index (RI) Saaty berdasarkan ukuran matriks n.
        'indeks_random' => [1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49],
        // Matriks dianggap konsisten bila CR <= 0.1.
        'batas_cr' => 0.10,
        // Presisi kolom ranking_bobot.bobot (decimal(5,2)).
        'presisi_bobot' => 2,
        // Arti nilai skala Saaty (untuk penjelasan dasar pembobotan).
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

    /* ---------- Tingkat prestasi (dipakai C2 dan C3) ---------- */
    // Pada Excel, tingkat terendah prestasi akademik ditulis "Kampus",
    // sedangkan prestasi non-akademik ditulis "Internal". Keduanya adalah
    // tingkat yang sama (lingkup kampus), sehingga di website disatukan
    // menjadi "Internal".
    'tingkat' => ['Internal', 'Regional', 'Nasional', 'Internasional'],

    /* ---------- C2 — Prestasi Akademik ---------- */
    'prestasi_akademik' => [
        'skor' => [
            'Internal' => 25,       // Excel: "Kampus"
            'Regional' => 45,
            'Nasional' => 70,
            'Internasional' => 100,
        ],
        'bonus_per_tambahan' => 5,  // Bonus per prestasi tambahan
        'maks_tambahan' => 2,       // Maks. jumlah tambahan dihitung
    ],

    /* ---------- C3 — Prestasi Non-Akademik ---------- */
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

    /* ---------- C4 — Skor Jabatan Organisasi ---------- */
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
        // C4 = skor jabatan tertinggi + (bonus x skor jabatan tertinggi kedua),
        // dibulatkan, maksimal 100.
        'bonus_organisasi_kedua' => 0.10,
    ],

    /* Semua skor kriteria dibatasi maksimal 100. */
    'skor_maksimal' => 100,
];
