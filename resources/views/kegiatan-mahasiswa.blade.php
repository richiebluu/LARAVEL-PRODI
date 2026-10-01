<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kegiatan Mahasiswa | Program Studi Teknologi Informasi</title>
<meta name="description" content="Kegiatan mahasiswa Program Studi Teknologi Informasi Politala: seminar, lomba, pengabdian masyarakat, kunjungan industri, dan lainnya.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="mahasiswa">

  @include('partials.public-navbar')

  {{-- REVISI DOSEN 01-10-2026: hero memakai foto GTI + overlay warna utama (lihat App\Support\HeroFoto). --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('kegiatan-mahasiswa', optional(collect($daftarKegiatan->items())->first(fn ($k) => filled($k->foto_url)))->foto_url) }}">
    <div class="container">
      <h1>Kegiatan Mahasiswa</h1>
      <p>Aktivitas dan kegiatan mahasiswa Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/mahasiswa-berprestasi') }}">Mahasiswa</a><span class="sep">/</span><span class="current">Kegiatan Mahasiswa</span></div>
    </div>
  </section>

  {{-- REVISI 28-09-2026 tahap 2 ("REVISI BARU(1).docx"): Mahasiswa > Kegiatan Mahasiswa (urutan ketiga).
       Data dari tabel kegiatan_mahasiswa (dikelola Staff Prodi). Card = .news-card (sama dengan Berita),
       detail dibuka dalam modal yang sudah ada. --}}
  <section class="section-pad">
    <div class="container">
      <div class="intro-baris reveal">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-people-group"></i> Kegiatan</span>
          <h2>Belajar, Berkarya, dan Berkontribusi</h2>
          <p>Dokumentasi kegiatan mahasiswa Teknologi Informasi di luar perkuliahan: seminar dan workshop, lomba, pengabdian kepada masyarakat, kunjungan industri, hingga kegiatan organisasi.</p>
        </div>
        <div class="ringkas-grid">
          <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-calendar-check"></i></div><div><b>{{ $totalKegiatan }}</b><span>Total kegiatan</span></div></div>
          <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-calendar-day"></i></div><div><b>{{ $kegiatanTahunIni }}</b><span>Kegiatan {{ now()->year }}</span></div></div>
          <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-layer-group"></i></div><div><b>{{ $daftarKategori->count() }}</b><span>Kategori</span></div></div>
        </div>
      </div>

      @if ($daftarKategori->count() > 1)
        <div class="filter-bar">
          <a class="filter-btn {{ ! $kategori ? 'active' : '' }}" href="{{ route('kegiatan-mahasiswa', array_filter(['q' => $cari])) }}">Semua</a>
          @foreach ($daftarKategori as $k => $ikon)
            <a class="filter-btn {{ $kategori === $k ? 'active' : '' }}" href="{{ route('kegiatan-mahasiswa', array_filter(['kategori' => $k, 'q' => $cari])) }}">{{ $k }}</a>
          @endforeach
        </div>
      @endif
      @if ($totalKegiatan)
        <form class="search-bar" method="GET" action="{{ route('kegiatan-mahasiswa') }}" role="search">
          @if ($kategori)<input type="hidden" name="kategori" value="{{ $kategori }}">@endif
          <input type="text" name="q" value="{{ $cari }}" placeholder="Cari nama kegiatan, lokasi, atau penyelenggara..." aria-label="Cari kegiatan">
        </form>
      @endif

      <div class="grid-3 reveal-stagger">
        @forelse ($daftarKegiatan as $k)
          <a href="#modalKegiatan{{ $k->id_kegiatan_mahasiswa }}" class="card news-card reveal" data-modal-open="modalKegiatan{{ $k->id_kegiatan_mahasiswa }}" onclick="event.preventDefault();">
            @if ($k->foto_url)
              <div class="news-photo" style="background-image:url('{{ $k->foto_url }}');"><span class="news-cat">{{ $k->kategori }}</span></div>
            @else
              <div class="news-photo ikon-saja"><i class="fa-solid {{ $k->ikon }}"></i><span class="news-cat">{{ $k->kategori }}</span></div>
            @endif
            <div class="news-body">
              <div class="news-date"><i class="fa-regular fa-calendar"></i> {{ $k->tanggal->translatedFormat('d F Y') }}@if ($k->lokasi) &middot; <i class="fa-solid fa-location-dot"></i> {{ $k->lokasi }}@endif</div>
              <h3>{{ $k->judul }}</h3>
              @if ($k->cuplikan)<p style="font-size:.9rem;">{{ $k->cuplikan }}</p>@endif
            </div>
          </a>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>{{ $cari !== '' || $kategori ? 'Kegiatan yang dicari tidak ditemukan.' : 'Belum ada kegiatan mahasiswa yang dipublikasikan.' }}</p>
          </div>
        @endforelse
      </div>

      @if ($daftarKegiatan->hasPages())
        <div class="text-center" style="margin-top:36px; display:flex; gap:10px; justify-content:center; align-items:center; flex-wrap:wrap;">
          @if ($daftarKegiatan->previousPageUrl())<a class="btn btn-outline btn-sm" href="{{ $daftarKegiatan->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>@endif
          <span class="tabel-publik-info" style="margin:0;">Halaman {{ $daftarKegiatan->currentPage() }} dari {{ $daftarKegiatan->lastPage() }}</span>
          @if ($daftarKegiatan->nextPageUrl())<a class="btn btn-outline btn-sm" href="{{ $daftarKegiatan->nextPageUrl() }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>@endif
        </div>
      @endif
    </div>
  </section>

  {{-- Detail kegiatan (modal yang sama dengan detail Dosen) --}}
  @foreach ($daftarKegiatan as $k)
    <div class="modal-overlay" id="modalKegiatan{{ $k->id_kegiatan_mahasiswa }}">
      <div class="modal-box">
        <button class="modal-close" data-modal-close aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
        @if ($k->foto_url)
          <div style="aspect-ratio:16/9; border-radius:var(--radius-md); background:url('{{ $k->foto_url }}') center/cover; margin:8px 0 18px 0;"></div>
        @endif
        <span class="ta-field" style="display:inline-flex; margin-bottom:10px;"><i class="fa-solid {{ $k->ikon }}" style="margin-right:6px;"></i>{{ $k->kategori }}</span>
        <h3>{{ $k->judul }}</h3>
        <p style="font-size:.88rem; color:var(--grey-500); margin-bottom:14px;">
          <i class="fa-regular fa-calendar"></i> {{ $k->tanggal->translatedFormat('d F Y') }}
          @if ($k->lokasi) &middot; <i class="fa-solid fa-location-dot"></i> {{ $k->lokasi }}@endif
          @if ($k->penyelenggara) &middot; <i class="fa-solid fa-users"></i> {{ $k->penyelenggara }}@endif
        </p>
        @foreach ($k->paragraf as $p)
          <p>{{ $p }}</p>
        @endforeach
      </div>
    </div>
  @endforeach

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
