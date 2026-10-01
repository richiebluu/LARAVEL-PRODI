<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $berita->judul }} | Program Studi Teknologi Informasi</title>
<meta name="description" content="{{ $berita->cuplikan }}">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="informasi">

  @include('partials.public-navbar')

  {{-- REVISI DOSEN 01-10-2026: hero memakai foto GTI + overlay warna utama (lihat App\Support\HeroFoto). --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('berita-detail', $berita->gambar_url) }}">
    <div class="container">
      <h1>{{ $berita->judul }}</h1>
      <p><i class="fa-regular fa-calendar"></i> {{ $berita->tanggal->translatedFormat('d F Y') }}@if ($berita->kategori) &middot; {{ $berita->kategori }}@endif</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/berita') }}">Berita</a><span class="sep">/</span><span class="current">Detail Berita</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container" style="max-width:860px;">
      @if ($berita->gambar_url)
        <img src="{{ $berita->gambar_url }}" alt="{{ $berita->judul }}" class="reveal" style="border-radius:26px; box-shadow:var(--shadow-lg); width:100%; margin-bottom:32px;">
      @endif
      <div class="reveal">
        @foreach ($berita->paragraf as $paragraf)
          <p>{{ $paragraf }}</p>
        @endforeach
      </div>
      <div style="margin-top:32px;">
        <a href="{{ url('/berita') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali ke Berita</a>
      </div>
    </div>
  </section>

  @if ($beritaLain->isNotEmpty())
    <section class="section-pad bg-soft">
      <div class="container">
        <div class="section-head">
          <span class="eyebrow"><i class="fa-solid fa-newspaper"></i> Berita Lainnya</span>
          <h2>Informasi Terbaru Program Studi</h2>
        </div>
        <div class="grid-3 reveal-stagger">
          @foreach ($beritaLain as $b)
            <a href="{{ route('berita.show', $b) }}" class="card news-card reveal">
              <div class="news-photo" style="background-image:url('{{ $b->gambar_url ?? asset('images/Kodex.png') }}'); @if (! $b->gambar_url) background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50); @endif">
                @if ($b->kategori)<span class="news-cat">{{ $b->kategori }}</span>@endif
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa-regular fa-calendar"></i> {{ $b->tanggal->translatedFormat('d F Y') }}</div>
                <h3>{{ $b->judul }}</h3>
                <p style="font-size:.9rem;">{{ $b->cuplikan }}</p>
              </div>
            </a>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
