<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Berita | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Berita dan kegiatan terbaru Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="informasi">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('berita') }}">
    <div class="container">
      <h1>{{ __('Berita') }}</h1>
      <p>{{ __('Berita, kegiatan mahasiswa, dan informasi terbaru Program Studi Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><span class="current">{{ __('Berita') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="filter-bar" style="margin-bottom:24px;">
        <a class="filter-btn {{ ! $jenis ? 'active' : '' }}" href="{{ route('berita', array_filter(['q' => $cari])) }}">{{ __('Semua') }}</a>
        @foreach (\App\Models\Berita::SLUG_JENIS as $slug => $kodeJenis)
          <a class="filter-btn {{ $jenis === $slug ? 'active' : '' }}" href="{{ route('berita', array_filter(['jenis' => $slug, 'q' => $cari])) }}">{{ __(\App\Models\Berita::LABEL_JENIS[$kodeJenis]) }}</a>
        @endforeach
      </div>
      <form class="search-bar" method="GET" action="{{ route('berita') }}">
        @if ($jenis)<input type="hidden" name="jenis" value="{{ $jenis }}">@endif
        <input type="text" name="q" value="{{ $cari }}" placeholder="{{ __('Cari judul, kategori, atau lokasi...') }}">
      </form>
      <div class="grid-3 reveal-stagger">
        @forelse ($daftarBerita as $b)
            <a href="{{ route('berita.show', $b) }}" class="card news-card reveal">
              <div class="news-photo" style="background-image:url('{{ $b->gambar_url ?? asset('images/Kodex.png') }}'); @if (! $b->gambar_url) background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50); @endif">
                @if ($b->kategori || $b->is_kegiatan)<span class="news-cat">{{ $b->kategori ?: $b->label_jenis }}</span>@endif
              </div>
              <div class="news-body">
                <div class="news-date"><i class="fa-regular fa-calendar"></i> {{ $b->tanggal->translatedFormat('d F Y') }}@if ($b->is_kegiatan) &middot; {{ __($b->label_jenis) }}@endif
                  @if ($b->media_sosial) &middot; <i class="{{ $b->media_sosial['ikon'] }}" title="{{ __('Ada postingan :platform', ['platform' => $b->media_sosial['label']]) }}" aria-label="{{ __('Ada postingan :platform', ['platform' => $b->media_sosial['label']]) }}"></i>@endif</div>
                <h3>{{ $b->judul }}</h3>
                <p style="font-size:.9rem;">{{ $b->cuplikan }}</p>
              </div>
            </a>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>{{ $cari !== '' ? __('Berita tidak ditemukan.') : ($jenis === 'kegiatan-mahasiswa' ? __('Belum ada kegiatan mahasiswa.') : __('Belum ada berita.')) }}</p>
          </div>
        @endforelse
      </div>
      @if ($daftarBerita->hasPages())
        <div class="text-center" style="margin-top:36px; display:flex; gap:10px; justify-content:center;">
          @if ($daftarBerita->previousPageUrl())<a class="btn btn-outline btn-sm" href="{{ $daftarBerita->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i> {{ __('Sebelumnya') }}</a>@endif
          @if ($daftarBerita->nextPageUrl())<a class="btn btn-outline btn-sm" href="{{ $daftarBerita->nextPageUrl() }}">{{ __('Berikutnya') }} <i class="fa-solid fa-chevron-right"></i></a>@endif
        </div>
      @endif
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
