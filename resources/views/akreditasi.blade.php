<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Akreditasi | Program Studi Teknologi Informasi</title>
<meta name="description" content="Status, peringkat, dan masa berlaku akreditasi Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Akreditasi</h1>
      <p>Status, peringkat, dan masa berlaku akreditasi Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/profil') }}">Profil</a><span class="sep">/</span><span class="current">Akreditasi</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      @if ($akreditasi)
        <div class="akreditasi-hero reveal">
          <div class="akreditasi-seal">
            <b>{{ $akreditasi->peringkat }}</b>
            <span>{{ $akreditasi->status }}</span>
          </div>
          <div>
            <span class="eyebrow" style="background:rgba(255,255,255,.14); color:#fff;"><i class="fa-solid fa-certificate"></i> Sertifikat Akreditasi</span>
            <h2>Mutu Program Studi yang Terakreditasi</h2>
            <p>Peringkat akreditasi ditetapkan oleh lembaga akreditasi mandiri bidang informatika dan komputer, serta ditinjau secara berkala.</p>
            <dl>
              <div><dt>Lembaga Akreditasi</dt><dd>{{ $akreditasi->lembaga ?? '-' }}</dd></div>
              <div><dt>Nomor SK</dt><dd>{{ $akreditasi->nomor_sk ?? '-' }}</dd></div>
              <div><dt>Tahun Penetapan</dt><dd>{{ $akreditasi->tahun ?? '-' }}</dd></div>
              <div><dt>Masa Berlaku</dt><dd>{{ optional($akreditasi->tanggal_berakhir)->translatedFormat('d F Y') ?? '-' }}</dd></div>
            </dl>
            @if ($akreditasi->dokumen_url)
              <a href="{{ $akreditasi->dokumen_url }}" target="_blank" rel="noopener" class="btn btn-light btn-sm" style="margin-top:10px;">
                <i class="fa-solid fa-file-pdf"></i> Lihat Dokumen SK
              </a>
            @endif
          </div>
        </div>
      @else
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>Belum ada data akreditasi. Data akan tampil setelah Staff Prodi mengisinya.</p>
        </div>
      @endif

      <div class="grid-3 reveal-stagger" style="margin-top:40px;">
        <div class="card benefit-card reveal"><div class="benefit-icon"><i class="fa-solid fa-book-open"></i></div><h3>Kurikulum</h3><p>Ditinjau berkala bersama mitra industri teknologi.</p></div>
        <div class="card benefit-card reveal"><div class="benefit-icon"><i class="fa-solid fa-chalkboard-user"></i></div><h3>Dosen</h3><p>Seluruh dosen tetap berkualifikasi magister dan doktor.</p></div>
        <div class="card benefit-card reveal"><div class="benefit-icon"><i class="fa-solid fa-user-graduate"></i></div><h3>Lulusan</h3><p>Masa tunggu kerja rata-rata di bawah enam bulan.</p></div>
      </div>
    </div>
  </section>

  <section class="section-pad bg-grey">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-timeline"></i> Perjalanan</span>
        <h2>Riwayat Akreditasi Program Studi</h2>
      </div>
      <div class="timeline" id="akrTimeline">
        @forelse ($riwayat as $item)
          <div class="timeline-item reveal">
            <div class="timeline-year">{{ $item->tahun ?? '-' }}</div>
            <h4>Peringkat {{ $item->peringkat }}</h4>
            <p>{{ $item->lembaga ?? 'Lembaga akreditasi' }} &middot; SK {{ $item->nomor_sk ?? '-' }}</p>
          </div>
        @empty
          <div class="empty-public">
            <p>Belum ada riwayat akreditasi.</p>
          </div>
        @endforelse
      </div>
      <div class="kodex-tip" style="margin-top:30px;">
        <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
        <div><strong>Kata Kodex</strong><p>Yuk lihat akreditasi Prodi TI! Data ini dikelola Staff Prodi lewat dashboard.</p></div>
      </div>
    </div>
  </section>


  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
