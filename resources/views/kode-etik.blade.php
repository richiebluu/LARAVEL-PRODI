<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Kode Etik Mahasiswa | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Dokumen Kode Etik Mahasiswa Program Studi Teknologi Informasi, dapat dibaca langsung dalam format PDF.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="informasi">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('kode-etik') }}">
    <div class="container">
      <h1>{{ __('Kode Etik Mahasiswa') }}</h1>
      <p>{{ __('Dokumen Kode Etik Mahasiswa yang berlaku di Program Studi Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><span>{{ __('Informasi') }}</span><span class="sep">/</span><span class="current">{{ __('Kode Etik Mahasiswa') }}</span></div>
    </div>
  </section>

  <section class="section-pad" style="padding-top:48px;">
    <div class="container">
      @if ($prodi?->kode_etik_url)
        <div class="kodetik-reader reveal" id="kodetikReader" data-pdf="{{ $prodi->kode_etik_url }}">
          <div class="kodetik-bar" role="toolbar" aria-label="{{ __('Kontrol pembaca Kode Etik') }}">
            <div class="kodetik-grup" data-kodetik-hanya-pdfjs>
              <button type="button" class="kodetik-alat" data-kodetik="prev" title="{{ __('Halaman sebelumnya') }}" aria-label="{{ __('Halaman sebelumnya') }}"><i class="fa-solid fa-chevron-up"></i></button>
              <label class="kodetik-hal">{{ __('Hal.') }} <input type="number" min="1" value="1" data-kodetik-lompat aria-label="{{ __('Nomor halaman') }}"> / <span data-kodetik-total>-</span></label>
              <button type="button" class="kodetik-alat" data-kodetik="next" title="{{ __('Halaman berikutnya') }}" aria-label="{{ __('Halaman berikutnya') }}"><i class="fa-solid fa-chevron-down"></i></button>
            </div>
            <div class="kodetik-grup" data-kodetik-hanya-pdfjs>
              <button type="button" class="kodetik-alat" data-kodetik="zoom-out" title="{{ __('Perkecil') }}" aria-label="{{ __('Perkecil') }}"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
              <span class="kodetik-zoom" data-kodetik-zoom>100%</span>
              <button type="button" class="kodetik-alat" data-kodetik="zoom-in" title="{{ __('Perbesar') }}" aria-label="{{ __('Perbesar') }}"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
              <button type="button" class="kodetik-alat aktif" data-kodetik="lebar" title="{{ __('Lembar penuh selebar layar') }}" aria-label="{{ __('Lembar penuh selebar layar') }}"><i class="fa-solid fa-arrows-left-right"></i><span>{{ __('Lebar Penuh') }}</span></button>
              <button type="button" class="kodetik-alat" data-kodetik="halaman" title="{{ __('Satu lembar utuh terlihat di layar') }}" aria-label="{{ __('Satu lembar utuh') }}"><i class="fa-regular fa-file"></i><span>{{ __('Satu Halaman') }}</span></button>
            </div>
            <div class="kodetik-grup">
              <button type="button" class="kodetik-alat" data-kodetik="layar" title="{{ __('Layar penuh') }}" aria-label="{{ __('Layar penuh') }}"><i class="fa-solid fa-expand"></i></button>
              <a class="kodetik-alat" href="{{ $prodi->kode_etik_url }}" target="_blank" rel="noopener" title="{{ __('Buka di tab baru') }}" aria-label="{{ __('Buka di tab baru') }}"><i class="fa-solid fa-up-right-from-square"></i></a>
              <a class="kodetik-alat" href="{{ $prodi->kode_etik_url }}" download title="{{ __('Unduh PDF') }}" aria-label="{{ __('Unduh PDF') }}"><i class="fa-solid fa-download"></i><span>{{ __('Unduh') }}</span></a>
            </div>
          </div>

          <div class="kodetik-stage" data-kodetik-stage>
            <div class="kodetik-loading" data-kodetik-loading>
              <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
              <p>{{ __('Menyiapkan dokumen Kode Etik Mahasiswa...') }}</p>
            </div>
            <div class="kodetik-halaman" data-kodetik-halaman></div>

            <div class="kodetik-fallback" data-kodetik-fallback hidden>
              <iframe data-src="{{ $prodi->kode_etik_url }}#view=FitH&amp;toolbar=1" title="{{ __('Dokumen Kode Etik Mahasiswa') }}"></iframe>
            </div>
          </div>

          <p class="form-hint kodetik-petunjuk" data-kodetik-hanya-pdfjs>{{ __('Gulir untuk membaca seluruh halaman. Gunakan tombol') }} <strong>{{ __('Satu Halaman') }}</strong> {{ __('agar satu lembar PDF terlihat utuh di layar, atau tombol ← → / PgUp PgDn untuk berpindah halaman.') }}</p>
        </div>
      @else
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>{{ __('Dokumen Kode Etik Mahasiswa belum diunggah oleh Staff Prodi.') }}</p>
        </div>
      @endif
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
@if ($prodi?->kode_etik_url)
<script src="{{ asset('js/kode-etik.js') }}"></script>
@endif
</body>
</html>
