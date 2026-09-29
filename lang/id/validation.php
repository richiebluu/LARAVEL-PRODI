<?php

/*
|--------------------------------------------------------------------------
| Pesan validasi Bahasa Indonesia (REVISI 28-09-2026 tahap 2)
|--------------------------------------------------------------------------
| Dipakai otomatis karena APP_LOCALE=id. Sebelumnya project belum punya berkas
| bahasa sehingga pesan bawaan Laravel tampil dalam Bahasa Inggris (mis. pada
| hasil impor CSV). Aturan yang tidak tercantum di sini kembali ke Bahasa Inggris.
*/

return [
    'accepted' => ':attribute harus disetujui.',
    'after' => ':attribute harus tanggal setelah :date.',
    'after_or_equal' => ':attribute harus tanggal setelah atau sama dengan :date.',
    'array' => ':attribute harus berupa daftar.',
    'before' => ':attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => ':attribute harus bernilai antara :min sampai :max.',
        'file' => 'Ukuran :attribute harus antara :min sampai :max kilobita.',
        'string' => ':attribute harus terdiri dari :min sampai :max karakter.',
        'array' => ':attribute harus berisi :min sampai :max item.',
    ],
    'boolean' => ':attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_format' => ':attribute tidak sesuai format :format.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus :digits digit.',
    'digits_between' => ':attribute harus :min sampai :max digit.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'file' => ':attribute harus berupa berkas.',
    'filled' => ':attribute wajib diisi.',
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'numeric' => ':attribute maksimal bernilai :max.',
        'file' => 'Ukuran :attribute maksimal :max kilobita.',
        'string' => ':attribute maksimal :max karakter.',
        'array' => ':attribute maksimal berisi :max item.',
    ],
    'mimes' => ':attribute harus berupa berkas berjenis: :values.',
    'mimetypes' => ':attribute harus berupa berkas berjenis: :values.',
    'min' => [
        'numeric' => ':attribute minimal bernilai :min.',
        'file' => 'Ukuran :attribute minimal :min kilobita.',
        'string' => ':attribute minimal :min karakter.',
        'array' => ':attribute minimal berisi :min item.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':attribute wajib diisi.',
    'required_if' => ':attribute wajib diisi bila :other adalah :value.',
    'required_with' => ':attribute wajib diisi bila :values diisi.',
    'same' => ':attribute dan :other harus sama.',
    'size' => [
        'numeric' => ':attribute harus bernilai :size.',
        'file' => 'Ukuran :attribute harus :size kilobita.',
        'string' => ':attribute harus :size karakter.',
        'array' => ':attribute harus berisi :size item.',
    ],
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal diunggah.',
    'url' => 'Format :attribute tidak valid (gunakan URL lengkap https://...).',

    'custom' => [],

    'attributes' => [
        'nama' => 'Nama',
        'jenis' => 'Jenis',
        'lokasi' => 'Lokasi',
        'kapasitas' => 'Kapasitas',
        'status' => 'Status',
        'judul' => 'Judul',
        'kategori' => 'Kategori',
        'tanggal' => 'Tanggal',
        'deskripsi' => 'Deskripsi',
        'foto' => 'Foto',
        'berkas' => 'Berkas',
        'password' => 'Password',
        'email' => 'Email',
        'isi' => 'Isi',
        'urutan' => 'Urutan',
    ],
];
