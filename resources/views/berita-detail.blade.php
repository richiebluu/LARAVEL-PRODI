<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $berita->judul }} | {{ __('Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="{{ $berita->cuplikan }}">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="informasi">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('berita-detail', $berita->gambar_url) }}">
    <div class="container">
      <h1>{{ $berita->judul }}</h1>
      <p><i class="fa-regular fa-calendar"></i> {{ $berita->tanggal->translatedFormat('d F Y') }} &middot; {{ __($berita->label_jenis) }}@if ($berita->kategori) &middot; {{ __($berita->kategori) }}@endif</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><a href="{{ url('/berita') }}">{{ __('Berita') }}</a><span class="sep">/</span><span class="current">{{ __('Detail Berita') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container" style="max-width:860px;">
      @if ($berita->gambar_url)
        <img src="{{ $berita->gambar_url }}" alt="{{ $berita->judul }}" class="reveal" style="border-radius:26px; box-shadow:var(--shadow-lg); width:100%; margin-bottom:32px;">
      @endif
      @if ($berita->lokasi || $berita->penyelenggara)
        <div class="ta-meta reveal" style="margin-bottom:20px;">
          @if ($berita->lokasi)<span><i class="fa-solid fa-location-dot"></i> {{ $berita->lokasi }}</span>@endif
          @if ($berita->penyelenggara)<span><i class="fa-solid fa-building"></i> {{ $berita->penyelenggara }}</span>@endif
        </div>
      @endif
      <div class="reveal">
        @foreach ($berita->paragraf as $paragraf)
          <p>{{ $paragraf }}</p>
        @endforeach
      </div>
      <div style="margin-top:32px; display:flex; gap:12px; flex-wrap:wrap;">
        <a href="{{ url('/berita') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> {{ __('Kembali ke Berita') }}</a>
        @if ($berita->media_sosial)
          <a href="{{ $berita->link_media_sosial }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary"><i class="{{ $berita->media_sosial['ikon'] }}"></i> {{ __('Lihat Postingan Media Sosial') }}</a>
        @endif
      </div>
    </div>
  </section>

  @if ($beritaLain->isNotEmpty())
    <section class="section-pad bg-soft">
      <div class="container">
        <div class="section-head">
          <span class="eyebrow"><i class="fa-solid fa-newspaper"></i> {{ __('Berita Lainnya') }}</span>
          <h2>{{ __('Informasi Terbaru Program Studi') }}</h2>
        </div>
        <div class="grid-3 reveal-stagger">
          @foreach ($beritaLain as $b)
            <a href="{{ route('berita.show', $b) }}" class="card news-card reveal">
              <div class="news-photo" style="background-image:url('{{ $b->gambar_url ?? asset('images/Kodex.png') }}'); @if (! $b->gambar_url) background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50); @endif">
                @if ($b->kategori || $b->is_kegiatan)<span class="news-cat">{{ $b->kategori ?: $b->label_jenis }}</span>@endif
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

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
