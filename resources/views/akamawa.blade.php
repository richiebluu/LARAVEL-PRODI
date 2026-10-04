<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('AKAMAWA | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="AKAMAWA — Layanan Akademik dan Kemahasiswaan Politeknik Negeri Tanah Laut.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="informasi">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('akamawa') }}">
    <div class="container">
      <h1>AKAMAWA</h1>
      <p>{{ __('Layanan Akademik dan Kemahasiswaan Politeknik Negeri Tanah Laut.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><span>{{ __('Informasi') }}</span><span class="sep">/</span><span class="current">AKAMAWA</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="card reveal" id="akamawa" style="padding:44px; text-align:center; max-width:720px; margin:0 auto;">
        <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" style="width:120px; margin:0 auto 18px auto;">
        <span class="eyebrow"><i class="fa-solid fa-building-columns"></i> {{ __('Layanan Akademik dan Kemahasiswaan') }}</span>
        <h2 style="margin-top:10px;">AKAMAWA</h2>
        <p>{{ __('AKAMAWA (Akademik dan Kemahasiswaan) adalah layanan Politeknik Negeri Tanah Laut untuk kebutuhan akademik dan kemahasiswaan.') }}</p>
        <p style="margin-bottom:8px;">{{ __('Layanan yang tersedia antara lain:') }}</p>
        <p style="margin-top:0;"><strong>{{ __('Kalender Akademik · Peraturan Akademik · Beasiswa · Dispensasi Kuliah · Legalisir Dokumen · Penangguhan UKT · ORMAWA') }}</strong></p>
        <a href="{{ $linkAkamawa }}" target="_blank" rel="noopener" class="btn btn-primary" style="margin-top:10px;">{{ __('Kunjungi Website AKAMAWA') }} <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
      </div>
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
