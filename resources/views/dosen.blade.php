<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Dosen Pengajar | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Dosen Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('dosen') }}">
    <div class="container">
      <h1>{{ __('Dosen Pengajar') }}</h1>
      <p>{{ __('Tenaga pengajar Program Studi Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><a href="{{ url('/profil') }}">{{ __('Profil') }}</a><span class="sep">/</span><span class="current">{{ __('Dosen Pengajar') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="intro-baris reveal">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-chalkboard-user"></i> {{ __('Tenaga Pengajar') }}</span>
          <h2>{{ __('Dosen Program Studi Teknologi Informasi') }}</h2>
          <p>{{ __('Dosen pengajar yang membimbing perkuliahan, praktikum, dan project mahasiswa. Status menunjukkan kondisi dosen saat ini:') }} <strong>{{ __('Aktif') }}</strong> {{ __('mengajar,') }} <strong>{{ __('Pendidikan') }}</strong> {{ __('(sedang studi lanjut, mis. S3), atau') }} <strong>{{ __('Nonaktif') }}</strong>{{ __('. Pilih') }} <em>{{ __('Lihat Detail') }}</em> {{ __('untuk melihat profil dan publikasi Google Scholar.') }}</p>
        </div>
        <div class="ringkas-grid">
          @foreach (['aktif' => 'fa-user-check', 'pendidikan' => 'fa-graduation-cap', 'nonaktif' => 'fa-user-clock'] as $kode => $ikon)
            <div class="ringkas-item">
              <div class="benefit-icon"><i class="fa-solid {{ $ikon }}"></i></div>
              <div><b>{{ $ringkasanStatus[$kode] ?? 0 }}</b><span>{{ __('Dosen :status', ['status' => __(\App\Models\Dosen::LABEL_STATUS[$kode])]) }}</span></div>
            </div>
          @endforeach
        </div>
      </div>

      <form class="search-bar" method="GET" action="{{ route('dosen') }}" role="search">
        <input type="text" name="q" value="{{ $cari }}" placeholder="{{ __('Cari nama dosen, NUPTK, atau pendidikan terakhir...') }}" autocomplete="off"
               data-search-target=".baris-dosen" data-search-empty="#dosenTidakDitemukan" aria-label="{{ __('Cari dosen') }}">
      </form>

      <div class="tabel-publik-wrap reveal" id="dosenGrid">
        {{-- Tabel --}}
        <table class="tabel-publik">
          <thead><tr>
            <th>{{ __('No') }}</th><th>{{ __('Nama Dosen') }}</th><th class="kolom-opsional">NUPTK</th><th>{{ __('Status') }}</th>
            <th class="kolom-opsional">{{ __('Email') }}</th><th>{{ __('Aksi') }}</th>
          </tr></thead>
          <tbody>
            @forelse ($daftarDosen as $dosen)
              <tr class="baris-dosen">
                <td>{{ $loop->iteration }}</td>
                <td>
                  <div class="rank-person">
                    <div class="rank-photo" @if ($dosen->foto_url) style="background-image:url('{{ $dosen->foto_url }}')" @endif></div>
                    <div><strong>{{ $dosen->nama }}</strong><span>{{ $dosen->pendidikan_terakhir ?? '-' }}</span></div>
                  </div>
                </td>
                <td class="kolom-opsional">{{ $dosen->nuptk }}</td>
                <td><span class="label-kriteria status-{{ $dosen->status }}">{{ __($dosen->label_status) }}</span></td>
                <td class="kolom-opsional">{{ $dosen->email ?? '-' }}</td>
                <td><button class="btn btn-sm btn-outline" style="white-space:nowrap;" data-modal-open="modalDosen{{ $dosen->nuptk }}">{{ __('Lihat Detail') }}</button></td>
              </tr>
            @empty
              <tr class="empty-row"><td colspan="6">
                <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" style="width:90px; margin:0 auto 12px auto; display:block;">
                {{ $cari !== '' ? __('Dosen dengan kata kunci ":kata" tidak ditemukan.', ['kata' => $cari]) : __('Belum ada data dosen.') }}
              </td></tr>
            @endforelse
            <tr class="empty-row cari-kosong" id="dosenTidakDitemukan"><td colspan="6">{{ __('Tidak ada dosen yang cocok dengan pencarian.') }}</td></tr>
          </tbody>
        </table>
      </div>
      <p class="tabel-publik-info">
        <i class="fa-solid fa-circle-info" style="color:var(--blue-600);"></i>
        {{ $cari !== '' ? __('Menampilkan :jumlah dari :total dosen.', ['jumlah' => $daftarDosen->count(), 'total' => $totalDosen]) : __('Jabatan struktural dosen dapat dilihat pada halaman Struktur Organisasi.') }}
        @if ($cari !== '')<a href="{{ route('dosen') }}" style="color:var(--blue-600); font-weight:600;">{{ __('Tampilkan semua') }}</a>@endif
      </p>
    </div>
  </section>

  <div id="dosenModals">
    @foreach ($daftarDosen as $dosen)
      {{-- Modal --}}
      <div class="modal-overlay" id="modalDosen{{ $dosen->nuptk }}">
        <div class="modal-box">
          <button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button>
          <div class="modal-photo" @if ($dosen->foto_url) style="background-image:url('{{ $dosen->foto_url }}')" @endif></div>
          <h3>{{ $dosen->nama }}</h3>
          <p style="margin-bottom:16px;"><span class="label-kriteria status-{{ $dosen->status }}">{{ __($dosen->label_status) }}</span></p>
          <p><strong>{{ __('NUPTK:') }}</strong> {{ $dosen->nuptk }}</p>
          <p><strong>{{ __('Pendidikan Terakhir:') }}</strong> {{ $dosen->pendidikan_terakhir ?? '-' }}</p>
          <p><strong>{{ __('Email:') }}</strong> {{ $dosen->email ?? '-' }}</p>
          <p style="margin-bottom:6px;"><strong>{{ __('Publikasi Google Scholar:') }}</strong></p>
          @if ($dosen->google_scholar)
            <p><a href="{{ $dosen->google_scholar }}" target="_blank" rel="noopener" style="color:var(--blue-600); font-weight:600; word-break:break-all;"><i class="fa-solid fa-graduation-cap"></i> {{ __('Lihat Publikasi di Google Scholar') }}</a></p>
          @else
            <p style="font-size:.88rem; color:var(--grey-500);">{{ __('Link Google Scholar belum diisi.') }}</p>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
