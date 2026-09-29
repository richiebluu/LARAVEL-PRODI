<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prospek Lulusan | Program Studi Teknologi Informasi</title>
<meta name="description" content="Prospek karier lulusan Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Prospek Lulusan</h1>
      <p>Peluang karier digital bagi lulusan Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/profil') }}">Profil</a><span class="sep">/</span><span class="current">Prospek Lulusan</span></div>
    </div>
  </section>

  {{-- REVISI 28-09-2026: data dari Staff Prodi (menu Prospek Lulusan). Card memakai
       komponen ta-card yang sama dengan halaman Lowongan Kerja; ikon kategori dari dropdown. --}}
  <section class="section-pad">
    <div class="container">
      {{-- REVISI 28-09-2026 tahap 2: teks pengantar di samping card (layout tidak sepi);
           card tetap ta-card seperti halaman Lowongan Kerja. --}}
      <div class="konten-split">
        <div class="konten-split-teks reveal">
          <span class="eyebrow"><i class="fa-solid fa-briefcase"></i> Karier</span>
          <h2>Bidang Karier Lulusan Teknologi Informasi</h2>
          <p>Lulusan siap berkarier di berbagai bidang teknologi yang terus berkembang, dengan bekal pembelajaran berbasis project, praktikum laboratorium, dan kolaborasi dengan industri.</p>
          @if ($jumlahProspek)
            <div class="ringkas-list">
              <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-user-tie"></i></div><div><b>{{ $jumlahProspek }}</b><span>Profesi / prospek karier</span></div></div>
              <div class="ringkas-item"><div class="benefit-icon"><i class="fa-solid fa-layer-group"></i></div><div><b>{{ $daftarKategori->count() }}</b><span>Kategori bidang</span></div></div>
            </div>
          @endif
          <div class="hero-actions" style="margin-top:22px;">
            <a href="{{ route('lowongan-pekerjaan') }}" class="btn btn-outline btn-sm">Lihat Lowongan Kerja <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <div>
          @if ($daftarKategori->count() > 1)
            <div class="filter-bar" style="margin-bottom:24px;">
              <a class="filter-btn {{ ! $kategori ? 'active' : '' }}" href="{{ route('prospek-lulusan') }}">Semua</a>
              @foreach ($daftarKategori as $k)
                <a class="filter-btn {{ $kategori === $k ? 'active' : '' }}" href="{{ route('prospek-lulusan', ['kategori' => $k]) }}">{{ $k }}</a>
              @endforeach
            </div>
          @endif
          <div class="grid-2 reveal-stagger" id="bindProspek">
            @forelse ($daftarProspek as $p)
              <div class="card ta-card reveal">
                <span class="ta-field"><i class="fa-solid {{ $p->kelas_ikon }}" style="margin-right:6px;"></i>{{ $p->kategori }}</span>
                <h4>{{ $p->nama }}</h4>
                @if ($p->deskripsi)<p style="font-size:.88rem; margin:0;">{{ $p->deskripsi }}</p>@endif
              </div>
            @empty
              <div class="empty-public">
                <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
                <p>Data prospek lulusan belum diisi oleh Staff Prodi.</p>
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