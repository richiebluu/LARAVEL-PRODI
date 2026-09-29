<?php

/*
|--------------------------------------------------------------------------
| Skema Penilaian Mahasiswa Berprestasi — Metode AHP (bobot) + SAW (ranking)
|--------------------------------------------------------------------------
|
| Bobot kriteria (C1..C4) di bawah ini dihitung dengan metode AHP
| (perbandingan berpasangan antar kriteria, skala Saaty 1-9), BUKAN
| ditentukan langsung. Hasil AHP: C1=0.4758, C2=0.2884, C3=0.1544,
| C4=0.0813 (Consistency Ratio = 0.0078, konsisten karena <= 0.1).
|
| Kolom `ranking_bobot.bobot` di database hanya presisi 2 desimal
| (decimal(5,2)), sehingga nilai di bawah dibulatkan 2 desimal
| (0.48/0.29/0.15/0.08 — tetap berjumlah 1.00) agar tersimpan persis
| tanpa terpotong oleh database.
|
| Skema skor (prestasi_akademik, prestasi_non_akademik, organisasi) TIDAK
| berubah — masih sama seperti file acuan
| "DATA DUMMY MAHASISWA_METODE SAW_KELOMPOK-3.xlsx".
|
*/

return [

    /* ---------- Bobot Kriteria (C1..C4) hasil AHP, semuanya bertipe benefit ---------- */
    'kriteria' => [
        'C1' => ['nama' => 'Nilai Akademik',        'sumber' => 'IPK',                   'bobot' => 0.48, 'tipe' => 'benefit'],
        'C2' => ['nama' => 'Prestasi Akademik',     'sumber' => 'Poin prestasi akademik', 'bobot' => 0.29, 'tipe' => 'benefit'],
        'C3' => ['nama' => 'Prestasi Non-Akademik', 'sumber' => 'Poin prestasi non-akademik', 'bobot' => 0.15, 'tipe' => 'benefit'],
        'C4' => ['nama' => 'Keaktifan Organisasi',  'sumber' => 'Poin jabatan organisasi', 'bobot' => 0.08, 'tipe' => 'benefit'],
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
