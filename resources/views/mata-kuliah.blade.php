<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Mata Kuliah | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Daftar mata kuliah Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('mata-kuliah') }}">
    <div class="container">
      <h1>{{ __('Mata Kuliah') }}</h1>
      <p>{{ __('Daftar mata kuliah yang digunakan pada Program Studi Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><a href="{{ url('/profil') }}">{{ __('Profil') }}</a><span class="sep">/</span><span class="current">{{ __('Mata Kuliah') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="section-head"><span class="eyebrow"><i class="fa-solid fa-book-open"></i> {{ __('Mata Kuliah') }}</span><h2>{{ __('Mata Kuliah Program Studi') }}</h2>
        @if ($jumlahMataKuliah)<p>{{ $jumlahMataKuliah }} {{ __('mata kuliah') }} &middot; {{ $totalSks }} SKS &middot; {{ $daftarSemester->count() }} {{ __('semester') }}</p>@endif</div>

      @if ($daftarSemester->isNotEmpty())
        <div class="filter-bar">
          <a class="filter-btn {{ ! $semester ? 'active' : '' }}" href="{{ route('mata-kuliah') }}">{{ __('Semua') }}</a>
          @foreach ($daftarSemester as $s)
            <a class="filter-btn {{ $semester === $s ? 'active' : '' }}" href="{{ route('mata-kuliah', ['semester' => $s]) }}">{{ __('Semester') }} {{ $s }}</a>
          @endforeach
        </div>
        <div class="search-bar">
          <input type="text" id="cariMataKuliah" placeholder="{{ __('Cari kode atau nama mata kuliah...') }}" data-search-target=".baris-mk" data-search-empty="#mkTidakDitemukan" autocomplete="off">
        </div>
      @endif

      @forelse ($perSemester as $nomor => $daftar)
        <div class="kurikulum-semester reveal" data-search-group>
          <h3 class="kurikulum-judul">{{ __('Semester') }} {{ $nomor }} <span>{{ $daftar->count() }} {{ __('mata kuliah') }} &middot; {{ $daftar->sum('sks') }} SKS</span></h3>
          <div class="tabel-publik-wrap">
            {{-- Tabel --}}
            <table class="tabel-publik">
              <thead><tr>
                <th>{{ __('No') }}</th><th>{{ __('Kode MK') }}</th><th>{{ __('Nama Mata Kuliah') }}</th><th>SKS</th><th class="kolom-opsional">{{ __('Jenis') }}</th>
              </tr></thead>
              <tbody>
                @foreach ($daftar as $mk)
                  <tr class="baris-mk">
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $mk->kode_mata_kuliah }}</strong></td>
                    <td>{{ $mk->nama }}<span class="sub tampil-mobile">{{ $mk->jenis }}</span></td>
                    <td>{{ $mk->sks }}</td>
                    <td class="kolom-opsional"><span class="label-kriteria {{ $mk->jenis === 'Pilihan' ? 'non' : '' }}">{{ __($mk->jenis) }}</span></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @empty
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>{{ __('Data mata kuliah belum diisi oleh Staff Prodi.') }}</p>
        </div>
      @endforelse

      <div class="empty-public cari-kosong" id="mkTidakDitemukan"><p>{{ __('Tidak ada mata kuliah yang cocok dengan pencarian.') }}</p></div>

    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
