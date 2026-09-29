<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kode Etik Mahasiswa | Program Studi Teknologi Informasi</title>
<meta name="description" content="Dokumen Kode Etik Mahasiswa Program Studi Teknologi Informasi, dapat dibaca langsung dalam format PDF.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="informasi">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Kode Etik Mahasiswa</h1>
      <p>Dokumen Kode Etik Mahasiswa yang berlaku di Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><span>Informasi</span><span class="sep">/</span><span class="current">Kode Etik Mahasiswa</span></div>
    </div>
  </section>

  {{-- REVISI 28-09-2026 tahap 2 ("REVISI BARU(1).docx"): saat Kode Etik Mahasiswa dibuka, dokumen
       langsung tampil sebagai PDF di website dan setiap lembar dibuat FULL selebar area konten
       (sebelumnya tampilan buku dua halaman sehingga tiap lembar hanya setengah).
       Sumber data = PDF yang diunggah Staff Prodi (Data Master Profil Prodi). Halaman dirender
       oleh PDF.js (public/js/kode-etik.js); bila PDF.js gagal dimuat, otomatis memakai penampil
       PDF bawaan browser dengan tinggi mengikuti ukuran lembar PDF. --}}
  <section class="section-pad" style="padding-top:48px;">
    <div class="container">
      @if ($prodi?->kode_etik_url)
        <div class="kodetik-reader reveal" id="kodetikReader" data-pdf="{{ $prodi->kode_etik_url }}">
          <div class="kodetik-bar" role="toolbar" aria-label="Kontrol pembaca Kode Etik">
            <div class="kodetik-grup" data-kodetik-hanya-pdfjs>
              <button type="button" class="kodetik-alat" data-kodetik="prev" title="Halaman sebelumnya" aria-label="Halaman sebelumnya"><i class="fa-solid fa-chevron-up"></i></button>
              <label class="kodetik-hal">Hal. <input type="number" min="1" value="1" data-kodetik-lompat aria-label="Nomor halaman"> / <span data-kodetik-total>-</span></label>
              <button type="button" class="kodetik-alat" data-kodetik="next" title="Halaman berikutnya" aria-label="Halaman berikutnya"><i class="fa-solid fa-chevron-down"></i></button>
            </div>
            <div class="kodetik-grup" data-kodetik-hanya-pdfjs>
              <button type="button" class="kodetik-alat" data-kodetik="zoom-out" title="Perkecil" aria-label="Perkecil"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
              <span class="kodetik-zoom" data-kodetik-zoom>100%</span>
              <button type="button" class="kodetik-alat" data-kodetik="zoom-in" title="Perbesar" aria-label="Perbesar"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
              <button type="button" class="kodetik-alat aktif" data-kodetik="lebar" title="Lembar penuh selebar layar" aria-label="Lembar penuh selebar layar"><i class="fa-solid fa-arrows-left-right"></i><span>Lebar Penuh</span></button>
              <button type="button" class="kodetik-alat" data-kodetik="halaman" title="Satu lembar utuh terlihat di layar" aria-label="Satu lembar utuh"><i class="fa-regular fa-file"></i><span>Satu Halaman</span></button>
            </div>
            <div class="kodetik-grup">
              <button type="button" class="kodetik-alat" data-kodetik="layar" title="Layar penuh" aria-label="Layar penuh"><i class="fa-solid fa-expand"></i></button>
              <a class="kodetik-alat" href="{{ $prodi->kode_etik_url }}" target="_blank" rel="noopener" title="Buka di tab baru" aria-label="Buka di tab baru"><i class="fa-solid fa-up-right-from-square"></i></a>
              <a class="kodetik-alat" href="{{ $prodi->kode_etik_url }}" download title="Unduh PDF" aria-label="Unduh PDF"><i class="fa-solid fa-download"></i><span>Unduh</span></a>
            </div>
          </div>

          <div class="kodetik-stage" data-kodetik-stage>
            <div class="kodetik-loading" data-kodetik-loading>
              <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
              <p>Menyiapkan dokumen Kode Etik Mahasiswa...</p>
            </div>
            <div class="kodetik-halaman" data-kodetik-halaman></div>

            {{-- Cadangan: penampil PDF bawaan browser (dipakai bila PDF.js tidak dapat dimuat). --}}
            <div class="kodetik-fallback" data-kodetik-fallback hidden>
              <iframe data-src="{{ $prodi->kode_etik_url }}#view=FitH&amp;toolbar=1" title="Dokumen Kode Etik Mahasiswa"></iframe>
            </div>
          </div>

          <p class="form-hint kodetik-petunjuk" data-kodetik-hanya-pdfjs>Gulir untuk membaca seluruh halaman. Gunakan tombol <strong>Satu Halaman</strong> agar satu lembar PDF terlihat utuh di layar, atau tombol ← → / PgUp PgDn untuk berpindah halaman.</p>
        </div>
      @else
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>Dokumen Kode Etik Mahasiswa belum diunggah oleh Staff Prodi.</p>
        </div>
      @endif
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
@if ($prodi?->kode_etik_url)
<script src="{{ asset('js/kode-etik.js') }}"></script>
@endif
</body>
</html>
