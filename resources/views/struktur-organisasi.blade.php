<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struktur Organisasi | Program Studi Teknologi Informasi</title>
<meta name="description" content="Struktur organisasi Program Studi Teknologi Informasi: Koordinator Program Studi, Koordinator Gugus, dan pengelola lainnya.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Struktur Organisasi</h1>
      <p>Pengelola Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/profil') }}">Profil</a><span class="sep">/</span><span class="current">Struktur Organisasi</span></div>
    </div>
  </section>

  <!-- REVISI 26-09-2026: Struktur Organisasi bagian dari Profil Program Studi.
       Data dari tabel struktur_organisasi (dikelola Staff Prodi), pejabat dapat terhubung ke data dosen. -->
  <section class="section-pad">
    <div class="container">
      @if ($strukturOrganisasi->isNotEmpty())
        @php $pimpinan = $strukturOrganisasi->first(); $lainnya = $strukturOrganisasi->slice(1); @endphp
        <div class="section-head">
          <span class="eyebrow"><i class="fa-solid fa-sitemap"></i> Struktur Organisasi</span>
          <h2>{{ $prodi?->nama_prodi ?? 'Program Studi Teknologi Informasi' }}</h2>
        </div>
        <div class="reveal" style="max-width:280px; margin:0 auto 36px auto;">
          <div class="card person-card">
            <div class="person-photo" @if ($pimpinan->foto_url) style="background-image:url('{{ $pimpinan->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif>
              <span class="person-badge">{{ $pimpinan->jabatan }}</span>
            </div>
            <div class="person-body">
              <h4>{{ $pimpinan->nama_pejabat }}</h4>
              <div class="person-meta">{{ $pimpinan->jabatan }}</div>
              @if ($pimpinan->dosen)
                <a href="{{ url('/dosen') }}#modalDosen{{ $pimpinan->dosen->nuptk }}" class="btn btn-sm btn-outline" style="margin-top:6px;">Lihat Detail</a>
              @endif
            </div>
          </div>
        </div>
        <div class="grid-4 reveal-stagger">
          @foreach ($lainnya as $s)
            <div class="card person-card reveal">
              <div class="person-photo" @if ($s->foto_url) style="background-image:url('{{ $s->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif></div>
              <div class="person-body">
                <h4>{{ $s->nama_pejabat }}</h4>
                <div class="person-meta">{{ $s->jabatan }}</div>
                @if ($s->dosen)
                  <a href="{{ url('/dosen') }}#modalDosen{{ $s->dosen->nuptk }}" class="btn btn-sm btn-outline" style="margin-top:6px;">Lihat Detail</a>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>Struktur organisasi belum diisi oleh Staff Prodi.</p>
        </div>
      @endif
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
