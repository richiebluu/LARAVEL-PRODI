<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Sarana & Prasarana | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Laboratorium, ruang kuliah, dan fasilitas pendukung Program Studi Teknologi Informasi Politala.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('sarana-prasarana', optional($daftarSarana->first(fn ($s) => filled($s->foto_url)))->foto_url) }}">
    <div class="container">
      <h1>{{ __('Sarana & Prasarana') }}</h1>
      <p>{{ __('Laboratorium dan fasilitas penunjang pembelajaran Program Studi Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><a href="{{ url('/profil') }}">{{ __('Profil') }}</a><span class="sep">/</span><span class="current">{{ __('Sarana & Prasarana') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="konten-split">
        <div class="konten-split-teks reveal">
          <span class="eyebrow"><i class="fa-solid fa-flask"></i> {{ __('Fasilitas') }}</span>
          <h2>{{ __('Mendukung Pembelajaran Berbasis Praktik') }}</h2>
          <p>{{ __('Perkuliahan dan praktikum Teknologi Informasi didukung laboratorium komputer serta ruang dan fasilitas penunjang yang dikelola Program Studi.') }}</p>
          @if ($jumlahSarana)
            <div class="ringkas-list">
              <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-flask"></i></div><div><b>{{ $jumlahSarana }}</b><span>{{ __('Seluruh sarana & prasarana') }}</span></div></div>
              @if ($jumlahGedung)
                <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-building"></i></div><div><b>{{ $jumlahGedung }}</b><span>{{ __('Gedung') }}</span></div></div>
              @endif
              @if ($totalKapasitas)
                <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-users"></i></div><div><b>{{ $totalKapasitas }}</b><span>{{ __('Kapasitas (orang)') }}</span></div></div>
              @endif
            </div>
          @endif
        </div>

        <div>
          @if ($daftarGedung->count() > 1)
            <div class="filter-bar" style="margin-bottom:24px;">
              <a class="filter-btn {{ ! $gedung ? 'active' : '' }}" href="{{ route('sarana-prasarana') }}">{{ __('Semua') }}</a>
              @foreach ($daftarGedung as $g)
                <a class="filter-btn {{ $gedung === $g ? 'active' : '' }}" href="{{ route('sarana-prasarana', ['gedung' => $g]) }}">{{ $g }}</a>
              @endforeach
            </div>
          @endif

          <div class="grid-2 reveal-stagger">
            @forelse ($daftarSarana as $s)
              <div class="card news-card tanpa-link reveal">
                @if ($s->foto_url)
                  <div class="news-photo" style="background-image:url('{{ $s->foto_url }}');">@if ($s->gedung)<span class="news-cat">{{ $s->gedung }}</span>@endif</div>
                @else
                  <div class="news-photo ikon-saja"><i class="fa-solid {{ $s->ikon }}"></i>@if ($s->gedung)<span class="news-cat">{{ $s->gedung }}</span>@endif</div>
                @endif
                <div class="news-body">
                  <h3>{{ $s->nama }}</h3>
                  <div class="ta-meta">
                    @if ($s->gedung)<span><i class="fa-solid fa-building"></i> {{ $s->gedung }}</span>@endif
                    @if ($s->kapasitas)<span><i class="fa-solid fa-users"></i> {{ $s->kapasitas }} {{ __('orang') }}</span>@endif
                  </div>
                  @if (count($s->daftar_fasilitas))
                    <ul class="fasilitas-list" aria-label="{{ __('Fasilitas') }}">
                      @foreach ($s->daftar_fasilitas as $f)<li>{{ $f }}</li>@endforeach
                    </ul>
                  @endif
                </div>
              </div>
            @empty
              <div class="empty-public">
                <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
                <p>{{ __('Data sarana & prasarana belum diisi oleh Staff Prodi.') }}</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
