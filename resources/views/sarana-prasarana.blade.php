<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarana &amp; Prasarana | Program Studi Teknologi Informasi</title>
<meta name="description" content="Laboratorium, ruang kuliah, dan fasilitas pendukung Program Studi Teknologi Informasi Politala.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  @include('partials.public-navbar')

  {{-- REVISI DOSEN 01-10-2026: hero memakai foto GTI + overlay warna utama (lihat App\Support\HeroFoto). --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('sarana-prasarana', optional($daftarSarana->first(fn ($s) => filled($s->foto_url)))->foto_url) }}">
    <div class="container">
      <h1>Sarana &amp; Prasarana</h1>
      <p>Laboratorium dan fasilitas penunjang pembelajaran Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/profil') }}">Profil</a><span class="sep">/</span><span class="current">Sarana &amp; Prasarana</span></div>
    </div>
  </section>

  {{-- REVISI 28-09-2026 tahap 2 ("REVISI BARU(1).docx"): Profil > Sarana & Prasarana, termasuk
       nama-nama Laboratorium Prodi TI. Data dari tabel sarana_prasarana (dikelola Staff Prodi).
       Layout: teks pengantar di samping card (komponen .card .news-card yang sudah ada). --}}
  <section class="section-pad">
    <div class="container">
      <div class="konten-split">
        <div class="konten-split-teks reveal">
          <span class="eyebrow"><i class="fa-solid fa-flask"></i> Fasilitas</span>
          <h2>Mendukung Pembelajaran Berbasis Praktik</h2>
          <p>Perkuliahan dan praktikum Teknologi Informasi didukung laboratorium komputer serta ruang dan fasilitas penunjang yang dikelola Program Studi.</p>
          @if ($jumlahSarana)
            <div class="ringkas-list">
              <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-flask"></i></div><div><b>{{ $jumlahLab }}</b><span>Laboratorium</span></div></div>
              @if ($totalKapasitasLab)
                <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-users"></i></div><div><b>{{ $totalKapasitasLab }}</b><span>Kapasitas lab (orang)</span></div></div>
              @endif
              <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-building"></i></div><div><b>{{ $jumlahSarana }}</b><span>Seluruh sarana &amp; prasarana</span></div></div>
            </div>
          @endif
        </div>

        <div>
          @if ($daftarJenis->count() > 1)
            <div class="filter-bar" style="margin-bottom:24px;">
              <a class="filter-btn {{ ! $jenis ? 'active' : '' }}" href="{{ route('sarana-prasarana') }}">Semua</a>
              @foreach ($daftarJenis as $j)
                <a class="filter-btn {{ $jenis === $j ? 'active' : '' }}" href="{{ route('sarana-prasarana', ['jenis' => $j]) }}">{{ $j }}</a>
              @endforeach
            </div>
          @endif

          <div class="grid-2 reveal-stagger">
            @forelse ($daftarSarana as $s)
              <div class="card news-card tanpa-link reveal">
                @if ($s->foto_url)
                  <div class="news-photo" style="background-image:url('{{ $s->foto_url }}');"><span class="news-cat">{{ $s->jenis }}</span></div>
                @else
                  <div class="news-photo ikon-saja"><i class="fa-solid {{ $s->ikon }}"></i><span class="news-cat">{{ $s->jenis }}</span></div>
                @endif
                <div class="news-body">
                  <h3>{{ $s->nama }}</h3>
                  <div class="ta-meta">
                    @if ($s->lokasi)<span><i class="fa-solid fa-location-dot"></i> {{ $s->lokasi }}</span>@endif
                    @if ($s->kapasitas)<span><i class="fa-solid fa-users"></i> {{ $s->kapasitas }} orang</span>@endif
                  </div>
                  @if ($s->deskripsi)<p style="font-size:.9rem; margin:0;">{{ $s->deskripsi }}</p>@endif
                  @if (count($s->daftar_fasilitas))
                    <ul class="fasilitas-list" aria-label="Fasilitas">
                      @foreach ($s->daftar_fasilitas as $f)<li>{{ $f }}</li>@endforeach
                    </ul>
                  @endif
                </div>
              </div>
            @empty
              <div class="empty-public">
                <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
                <p>Data sarana &amp; prasarana belum diisi oleh Staff Prodi.</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
