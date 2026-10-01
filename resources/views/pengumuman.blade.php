<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengumuman | Program Studi Teknologi Informasi</title>
<meta name="description" content="Pengumuman resmi Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="pengumuman">

  @include('partials.public-navbar')

  {{-- REVISI DOSEN 01-10-2026: hero memakai foto GTI + overlay warna utama (lihat App\Support\HeroFoto). --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('pengumuman') }}">
    <div class="container">
      <h1>Pengumuman</h1>
      <p>Pengumuman resmi Mahasiswa Berprestasi Program Studi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><span class="current">Pengumuman</span></div>
    </div>
  </section>

  <!-- Pengumuman bersifat pribadi untuk mahasiswa berprestasi yang bersangkutan,
       sehingga TIDAK ditampilkan di halaman publik ini. -->
  <section class="section-pad">
    <div class="container">
      <div class="card reveal" style="padding:44px; text-align:center; max-width:720px; margin:0 auto;">
        <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" style="width:120px; margin:0 auto 18px auto;">
        <span class="eyebrow"><i class="fa-solid fa-user-graduate"></i> Pengumuman Mahasiswa Berprestasi</span>
        <h2 style="margin-top:10px;">Pengumuman Bersifat Pribadi</h2>
        <p>Pengumuman pada Program Studi Teknologi Informasi digunakan Staff Prodi untuk menyampaikan informasi khusus kepada mahasiswa berprestasi yang bersangkutan, sehingga tidak ditampilkan secara umum di halaman ini.</p>
        <p>Pengumuman dikirim melalui email (Gmail) Program Studi ke email mahasiswa berprestasi penerima. Anda juga dapat login untuk melihatnya di dashboard.</p>
        <p style="margin-bottom:8px;">Pengumuman ditujukan kepada mahasiswa berprestasi (bukan berdasarkan ranking), dengan kategori prestasi:</p>
        <p style="margin-top:0;"><strong>{{ implode(' · ', \App\Models\Pengumuman::daftarKategori()) }}</strong></p>
        <a href="{{ url('/login') }}" class="btn btn-primary" style="margin-top:10px;"><i class="fa-solid fa-right-to-bracket"></i> Login Mahasiswa</a>
      </div>
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>