<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AKAMAWA | Program Studi Teknologi Informasi</title>
<meta name="description" content="AKAMAWA — Layanan Akademik dan Kemahasiswaan Politeknik Negeri Tanah Laut.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="informasi">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>AKAMAWA</h1>
      <p>Layanan Akademik dan Kemahasiswaan Politeknik Negeri Tanah Laut.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><span>Informasi</span><span class="sep">/</span><span class="current">AKAMAWA</span></div>
    </div>
  </section>

  {{-- REVISI 27-09-2026: menu Informasi > AKAMAWA (link tutorial dihapus).
       Isi: nama AKAMAWA, penjelasan singkat, tombol "Kunjungi Website AKAMAWA". --}}
  <section class="section-pad">
    <div class="container">
      <div class="grid-2" style="align-items:center;">
        <div class="card link-card reveal" id="akamawa">
          <div class="benefit-icon"><i class="fa-solid fa-building-columns"></i></div>
          <h3>AKAMAWA</h3>
          <p>AKAMAWA (Akademik dan Kemahasiswaan) adalah layanan Politeknik Negeri Tanah Laut untuk kebutuhan akademik dan kemahasiswaan, seperti informasi kalender akademik, peraturan akademik, beasiswa, dispensasi kuliah, legalisir dokumen, penangguhan UKT, ORMAWA, dan layanan lainnya.</p>
          <div class="hero-actions" style="margin-top:auto;">
            <a href="{{ $linkAkamawa }}" target="_blank" rel="noopener" class="btn btn-primary">Kunjungi Website AKAMAWA <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
          </div>
        </div>
        <div class="reveal text-center">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" style="width:220px; margin:0 auto; animation:floaty 4.5s ease-in-out infinite;">
        </div>
      </div>
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
