<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kurikulum | Program Studi Teknologi Informasi</title>
<meta name="description" content="Kurikulum dan daftar mata kuliah Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Kurikulum</h1>
      <p>Daftar mata kuliah yang digunakan pada Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/profil') }}">Profil</a><span class="sep">/</span><span class="current">Kurikulum</span></div>
    </div>
  </section>

  {{-- REVISI 28-09-2026 ("REVISI BARU.docx"): Profil > Kurikulum. Data mata kuliah
       disesuaikan dengan SIPADU dan dikelola Staff Prodi (menu Kurikulum). --}}
  <section class="section-pad">
    <div class="container">
      <div class="section-head"><span class="eyebrow"><i class="fa-solid fa-book-open"></i> Kurikulum</span><h2>Mata Kuliah Program Studi</h2>
        @if ($jumlahMataKuliah)<p>{{ $jumlahMataKuliah }} mata kuliah &middot; {{ $totalSks }} SKS &middot; {{ $daftarSemester->count() }} semester</p>@endif</div>

      @if ($daftarSemester->isNotEmpty())
        <div class="filter-bar">
          <a class="filter-btn {{ ! $semester ? 'active' : '' }}" href="{{ route('kurikulum') }}">Semua</a>
          @foreach ($daftarSemester as $s)
            <a class="filter-btn {{ $semester === $s ? 'active' : '' }}" href="{{ route('kurikulum', ['semester' => $s]) }}">Semester {{ $s }}</a>
          @endforeach
        </div>
        <div class="search-bar">
          <input type="text" id="cariMataKuliah" placeholder="Cari kode atau nama mata kuliah..." data-search-target=".baris-mk" data-search-empty="#mkTidakDitemukan" autocomplete="off">
        </div>
      @endif

      @forelse ($perSemester as $nomor => $daftar)
        <div class="kurikulum-semester reveal" data-search-group>
          <h3 class="kurikulum-judul">Semester {{ $nomor }} <span>{{ $daftar->count() }} mata kuliah &middot; {{ $daftar->sum('sks') }} SKS</span></h3>
          <div class="tabel-publik-wrap">
            <table class="tabel-publik">
              <thead><tr>
                <th>No</th><th>Kode MK</th><th>Nama Mata Kuliah</th><th>SKS</th><th class="kolom-opsional">Jenis</th>
              </tr></thead>
              <tbody>
                @foreach ($daftar as $mk)
                  <tr class="baris-mk">
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $mk->kode }}</strong></td>
                    <td>{{ $mk->nama }}<span class="sub tampil-mobile">{{ $mk->jenis }}</span></td>
                    <td>{{ $mk->sks }}</td>
                    <td class="kolom-opsional"><span class="label-kriteria {{ $mk->jenis === 'Pilihan' ? 'non' : '' }}">{{ $mk->jenis }}</span></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @empty
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>Data kurikulum belum diisi oleh Staff Prodi.</p>
        </div>
      @endforelse

      <div class="empty-public cari-kosong" id="mkTidakDitemukan"><p>Tidak ada mata kuliah yang cocok dengan pencarian.</p></div>

      <p class="tabel-publik-info">
        <i class="fa-solid fa-circle-info" style="color:var(--blue-600);"></i>
        Data kurikulum disesuaikan dengan data mata kuliah yang terdapat di SIPADU.
      </p>
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
