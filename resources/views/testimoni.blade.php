<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Testimoni Alumni | Program Studi Teknologi Informasi</title>
<meta name="description" content="Testimoni Alumni Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="testimoni">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Testimoni Alumni</h1>
      <p>Cerita alumni Program Studi Teknologi Informasi setelah lulus dan berkarier.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><span class="current">Testimoni Alumni</span></div>
    </div>
  </section>

  <!-- REVISI 27-09-2026: hanya Testimoni Alumni (ERD: nama, foto, isi, tahun kelulusan,
       nama perusahaan, jabatan). Komponen testi-card yang sudah ada tetap dipakai. -->
  <section class="section-pad">
    <div class="container">
      {{-- REVISI 28-09-2026 tahap 2: judul bagian + teks singkat agar halaman tidak hanya berisi card. --}}
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-comment-dots"></i> Kata Alumni</span>
        <h2>Cerita Alumni Teknologi Informasi</h2>
        <p>Pengalaman alumni setelah lulus dan berkarier di dunia kerja, sebagai gambaran perjalanan lulusan Program Studi Teknologi Informasi.</p>
      </div>
      <div class="grid-3 reveal-stagger">
        @forelse ($daftarTestimoni as $t)
          <div class="card testi-card reveal">
            <p class="testi-quote">&ldquo;{{ $t->isi }}&rdquo;</p>
            <div class="testi-person">
              <div class="testi-photo" @if ($t->foto_url) style="background-image:url('{{ $t->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif></div>
              <div>
                <strong style="display:block; color:var(--navy-900);">{{ $t->nama }}</strong>
                <span style="font-size:.82rem; color:var(--grey-500);">{{ $t->keterangan_alumni }}</span>
              </div>
            </div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Belum ada testimoni alumni.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
