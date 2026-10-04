<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Prospek Lulusan | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Prospek karier lulusan Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('prospek-lulusan') }}">
    <div class="container">
      <h1>{{ __('Prospek Lulusan') }}</h1>
      <p>{{ __('Peluang karier digital bagi lulusan Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><a href="{{ url('/profil') }}">{{ __('Profil') }}</a><span class="sep">/</span><span class="current">{{ __('Prospek Lulusan') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="konten-split">
        <div class="konten-split-teks reveal">
          <span class="eyebrow"><i class="fa-solid fa-briefcase"></i> {{ __('Karier') }}</span>
          <h2>{{ __('Bidang Karier Lulusan Teknologi Informasi') }}</h2>
          <p>{{ __('Lulusan siap berkarier di berbagai bidang teknologi yang terus berkembang, dengan bekal pembelajaran berbasis project, praktikum laboratorium, dan kolaborasi dengan industri.') }}</p>
          @if ($jumlahProspek)
            <div class="ringkas-list">
              <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-user-tie"></i></div><div><b>{{ $jumlahProspek }}</b><span>{{ __('Profesi / prospek karier') }}</span></div></div>
            </div>
          @endif
          <div class="hero-actions" style="margin-top:22px;">
            <a href="{{ route('lowongan-pekerjaan') }}" class="btn btn-outline btn-sm">{{ __('Lihat Lowongan Kerja') }} <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <div>
          <div class="grid-2 reveal-stagger" id="bindProspek">
            @forelse ($daftarProspek as $p)
              <div class="card ta-card reveal">
                <span class="ta-field"><i class="{{ $p->kelas_ikon }}" style="margin-right:6px;"></i>{{ __('Prospek Lulusan') }}</span>
                <h4>{{ $p->nama }}</h4>
                @if ($p->deskripsi)<p style="font-size:.88rem; margin:0;">{{ $p->deskripsi }}</p>@endif
              </div>
            @empty
              <div class="empty-public">
                <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
                <p>{{ __('Data prospek lulusan belum diisi oleh Staff Prodi.') }}</p>
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
