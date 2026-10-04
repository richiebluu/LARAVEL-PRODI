<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Akreditasi | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Status, peringkat, dan masa berlaku akreditasi Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('akreditasi') }}">
    <div class="container">
      <h1>{{ __('Akreditasi') }}</h1>
      <p>{{ __('Status, peringkat, dan masa berlaku akreditasi Program Studi Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><a href="{{ url('/profil') }}">{{ __('Profil') }}</a><span class="sep">/</span><span class="current">{{ __('Akreditasi') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      @if ($akreditasi)
        <div class="akreditasi-hero reveal">
          <div class="akreditasi-seal">
            <b>{{ $akreditasi->peringkat }}</b>
            <span>{{ __($akreditasi->status) }}</span>
          </div>
          <div>
            <span class="eyebrow" style="background:rgba(255,255,255,.14); color:#fff;"><i class="fa-solid fa-certificate"></i> {{ __('Sertifikat Akreditasi') }}</span>
            <h2>{{ $akreditasi->berlaku ? __('Mutu Program Studi yang Terakreditasi') : __('Status Akreditasi Program Studi') }}</h2>
            <p>{{ __('Peringkat akreditasi ditetapkan oleh lembaga akreditasi mandiri bidang informatika dan komputer, serta ditinjau secara berkala.') }}</p>
            <dl>
              <div><dt>{{ __('Lembaga Akreditasi') }}</dt><dd>{{ $akreditasi->lembaga ?? '-' }}</dd></div>
              <div><dt>{{ __('Nomor SK') }}</dt><dd>{{ $akreditasi->nomor_sk ?? '-' }}</dd></div>
              <div><dt>{{ __('Tahun Penetapan') }}</dt><dd>{{ $akreditasi->tahun ?? '-' }}</dd></div>
              <div><dt>{{ __('Masa Berlaku') }}</dt><dd>{{ optional($akreditasi->tanggal_berakhir)->translatedFormat('d F Y') ?? '-' }}</dd></div>
            </dl>
            @if ($akreditasi->dokumen_url)
              <a href="{{ $akreditasi->dokumen_url }}" target="_blank" rel="noopener" class="btn btn-light btn-sm" style="margin-top:10px;">
                <i class="fa-solid fa-file-pdf"></i> {{ __('Lihat Dokumen SK') }}
              </a>
            @endif
          </div>
        </div>
      @else
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>{{ __('Belum ada data akreditasi. Data akan tampil setelah Staff Prodi mengisinya.') }}</p>
        </div>
      @endif

      <div class="grid-3 reveal-stagger" style="margin-top:40px;">
        <div class="card benefit-card reveal"><div class="benefit-icon"><i class="fa-solid fa-book-open"></i></div><h3>{{ __('Mata Kuliah') }}</h3><p>{{ __('Ditinjau berkala bersama mitra industri teknologi.') }}</p></div>
        <div class="card benefit-card reveal"><div class="benefit-icon"><i class="fa-solid fa-chalkboard-user"></i></div><h3>{{ __('Dosen') }}</h3><p>{{ __('Seluruh dosen tetap berkualifikasi magister dan doktor.') }}</p></div>
        <div class="card benefit-card reveal"><div class="benefit-icon"><i class="fa-solid fa-user-graduate"></i></div><h3>{{ __('Lulusan') }}</h3><p>{{ __('Masa tunggu kerja rata-rata di bawah enam bulan.') }}</p></div>
      </div>
    </div>
  </section>

  <section class="section-pad bg-grey">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-timeline"></i> {{ __('Perjalanan') }}</span>
        <h2>{{ __('Riwayat Akreditasi Program Studi') }}</h2>
      </div>
      <div class="timeline" id="akrTimeline">
        @forelse ($riwayat as $item)
          <div class="timeline-item reveal">
            <div class="timeline-year">{{ $item->tahun ?? '-' }}</div>
            <h4>{{ __('Peringkat') }} {{ __($item->peringkat) }}
              @if ($akreditasi && $akreditasi->is($item))
                <span class="eyebrow" style="font-size:.7rem; padding:3px 10px; margin-left:6px;"><i class="fa-solid fa-circle-check"></i> {{ __('Berlaku') }}</span>
              @endif
            </h4>
            <p>{{ $item->lembaga ?? __('Lembaga akreditasi') }} &middot; SK {{ $item->nomor_sk ?? '-' }} &middot; {{ __($item->status) }}</p>
          </div>
        @empty
          <div class="empty-public">
            <p>{{ __('Belum ada riwayat akreditasi.') }}</p>
          </div>
        @endforelse
      </div>
      <div class="kodex-tip" style="margin-top:30px;">
        <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
        <div><strong>{{ __('Kata Kodex') }}</strong><p>{{ __('Yuk lihat akreditasi Prodi TI! Data ini dikelola Staff Prodi lewat dashboard.') }}</p></div>
      </div>
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
