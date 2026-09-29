<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Berita | Program Studi Teknologi Informasi</title>
<meta name="description" content="Berita dan kegiatan terbaru Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="informasi">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Berita</h1>
      <p>Kegiatan dan informasi terbaru Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><span class="current">Berita</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <form class="search-bar" method="GET" action="{{ route('berita') }}">
        <input type="text" name="q" value="{{ $cari }}" placeholder="Cari judul atau kategori berita...">
      </form>
      <div class="grid-3 reveal-stagger">
        @forelse ($daftarBerita as $b)
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
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>{{ $cari !== '' ? 'Berita tidak ditemukan.' : 'Belum ada berita.' }}</p>
          </div>
        @endforelse
      </div>
      @if ($daftarBerita->hasPages())
        <div class="text-center" style="margin-top:36px; display:flex; gap:10px; justify-content:center;">
          @if ($daftarBerita->previousPageUrl())<a class="btn btn-outline btn-sm" href="{{ $daftarBerita->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>@endif
          @if ($daftarBerita->nextPageUrl())<a class="btn btn-outline btn-sm" href="{{ $daftarBerita->nextPageUrl() }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>@endif
        </div>
      @endif
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
