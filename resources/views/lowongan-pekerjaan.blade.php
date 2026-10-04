<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Lowongan Kerja | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Informasi lowongan pekerjaan dan magang untuk mahasiswa serta alumni Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="lowongan">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('lowongan-pekerjaan') }}">
    <div class="container">
      <h1>{{ __('Lowongan Kerja') }}</h1>
      <p>{{ __('Informasi lowongan kerja dan magang bagi mahasiswa serta alumni Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><span class="current">{{ __('Lowongan Kerja') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="filter-bar">
        <a class="filter-btn {{ ! $tipe ? 'active' : '' }}" href="{{ route('lowongan-pekerjaan', array_filter(['q' => $cari])) }}">{{ __('Semua') }}</a>
        @foreach (\App\Models\LowonganPekerjaan::TIPE as $t)
          <a class="filter-btn {{ $tipe === $t ? 'active' : '' }}" href="{{ route('lowongan-pekerjaan', array_filter(['tipe' => $t, 'q' => $cari])) }}">{{ __($t) }}</a>
        @endforeach
      </div>
      <form class="search-bar" method="GET" action="{{ route('lowongan-pekerjaan') }}">
        @if ($tipe)<input type="hidden" name="tipe" value="{{ $tipe }}">@endif
        <input type="text" name="q" value="{{ $cari }}" placeholder="{{ __('Cari posisi, perusahaan, atau lokasi...') }}">
      </form>
      <div class="grid-3 reveal-stagger">
        @forelse ($daftarLowongan as $l)
            <div class="card ta-card reveal">
              <span class="ta-field">{{ __($l->tipe ?? 'Lowongan') }}</span>
              <h4>{{ $l->posisi }}</h4>
              <div class="ta-meta"><i class="fa-solid fa-building"></i> {{ $l->perusahaan }}@if ($l->lokasi) &middot; <i class="fa-solid fa-location-dot"></i> {{ $l->lokasi }}@endif</div>
              @if ($l->deskripsi)<p style="font-size:.88rem; margin:0;">{{ \Illuminate\Support\Str::limit($l->deskripsi, 160) }}</p>@endif
              <div class="ta-meta"><i class="fa-regular fa-calendar"></i> {{ __('Batas lamaran') }}: {{ $l->batas_lamaran ? $l->batas_lamaran->translatedFormat('d F Y') : __('Tidak ditentukan') }}</div>
              <a href="{{ $l->link }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline" style="align-self:flex-start; margin-top:auto;">{{ __('Lihat Lowongan') }} <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>{{ __('Belum ada lowongan pekerjaan yang dibuka.') }}</p>
          </div>
        @endforelse
      </div>
      @if ($daftarLowongan->hasPages())
        <div class="text-center" style="margin-top:36px; display:flex; gap:10px; justify-content:center;">
          @if ($daftarLowongan->previousPageUrl())<a class="btn btn-outline btn-sm" href="{{ $daftarLowongan->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i> {{ __('Sebelumnya') }}</a>@endif
          @if ($daftarLowongan->nextPageUrl())<a class="btn btn-outline btn-sm" href="{{ $daftarLowongan->nextPageUrl() }}">{{ __('Berikutnya') }} <i class="fa-solid fa-chevron-right"></i></a>@endif
        </div>
      @endif
      <p class="tabel-publik-info">
        <i class="fa-solid fa-circle-info" style="color:var(--blue-600);"></i>
        {{ __('Informasi lowongan dikelola Staff Prodi. Pendaftaran/lamaran dilakukan langsung melalui tautan sumber lowongan.') }}
      </p>
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
