<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Mahasiswa Berprestasi | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Mahasiswa berprestasi Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="mahasiswa">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('mahasiswa-berprestasi') }}">
    <div class="container">
      <h1>{{ __('Mahasiswa Berprestasi') }}</h1>
      <p>{{ __('Kebanggaan Program Studi Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><span class="current">{{ __('Mahasiswa Berprestasi') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="filter-bar">
        <a class="filter-btn {{ ! $kategori ? 'active' : '' }}" href="{{ route('mahasiswa-berprestasi') }}">{{ __('Semua') }}</a>
        @foreach ($daftarKriteria as $kode => $namaKriteria)
          <a class="filter-btn {{ $kategori === $namaKriteria ? 'active' : '' }}" href="{{ route('mahasiswa-berprestasi', ['kategori' => $namaKriteria]) }}">{{ $kode === 'C1' ? __($namaKriteria).' (IPK)' : __($namaKriteria) }}</a>
        @endforeach
      </div>

      @if (! $kategori)
      {{-- Tab Semua --}}
      @if ($daftarBerprestasi->isNotEmpty())
        <div class="search-bar">
          <input type="text" id="cariBerprestasi" placeholder="{{ __('Cari nama, NIM, atau prestasi...') }}" data-search-target="#berprestasiGrid tbody tr.baris-berprestasi" autocomplete="off">
        </div>
      @endif
      <div class="tabel-publik-wrap reveal" id="berprestasiGrid">
        {{-- Tabel --}}
        <table class="tabel-publik">
          <thead><tr>
            <th>{{ __('No') }}</th><th>{{ __('Nama Mahasiswa') }}</th><th class="kolom-opsional">NIM</th>
            <th>{{ __('Prestasi Terbaru') }}</th><th class="kolom-opsional">{{ __('Tingkat Prestasi') }}</th><th class="kolom-opsional">{{ __('Tahun') }}</th><th>{{ __('Aksi') }}</th>
          </tr></thead>
          <tbody>
            @forelse ($daftarBerprestasi as $row)
              @php $m = $row['mahasiswa']; $p = $row['terbaru']; $lainnya = $row['prestasi']->count() - 1; @endphp
              <tr class="baris-berprestasi">
                <td>{{ $loop->iteration }}</td>
                <td>
                  <div class="rank-person">
                    <div class="rank-photo" @if ($m->foto_url) style="background-image:url('{{ $m->foto_url }}')" @endif></div>
                    <div><strong>{{ $m->nama }}</strong><span>{{ __('Angkatan') }} {{ $m->angkatan ?? '-' }}</span></div>
                  </div>
                </td>
                <td class="kolom-opsional">{{ $m->nim }}</td>
                <td>
                  <span class="label-kriteria {{ $p->kategori === \App\Models\Prestasi::KATEGORI_NON_AKADEMIK ? 'non' : '' }}">{{ __($p->kategori) }}</span>
                  <div style="margin-top:6px;">{{ $p->judul }}</div>
                  @if ($p->penyelenggara)<span class="sub">{{ $p->penyelenggara }}</span>@endif
                </td>
                <td class="kolom-opsional">{{ __($p->tingkat ?? '-') }}</td>
                <td class="kolom-opsional">{{ $p->tanggal?->format('Y') ?? '-' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline" style="white-space:nowrap;" data-modal-open="modalPrestasi{{ $m->nim }}">
                    {{ __('Lihat Prestasi Lainnya') }}{{ $lainnya > 0 ? ' (+'.$lainnya.')' : '' }}
                  </button>
                </td>
              </tr>
            @empty
              <tr class="empty-row"><td colspan="7">
                <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" style="width:90px; margin:0 auto 12px auto; display:block;">
                {{ __('Belum ada mahasiswa berprestasi.') }}
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div id="prestasiModals">
        @foreach ($daftarBerprestasi as $row)
          @php $m = $row['mahasiswa']; @endphp
          {{-- Modal --}}
          <div class="modal-overlay" id="modalPrestasi{{ $m->nim }}">
            <div class="modal-box" style="max-width:720px;">
              <button class="modal-close" data-modal-close aria-label="{{ __('Tutup') }}"><i class="fa-solid fa-xmark"></i></button>
              <div class="modal-photo" @if ($m->foto_url) style="background-image:url('{{ $m->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif></div>
              <h3>{{ $m->nama }}</h3>
              <p style="color:var(--blue-600); font-weight:600; margin-bottom:16px;">{{ $m->nim }} &middot; {{ __('Angkatan') }} {{ $m->angkatan ?? '-' }} &middot; {{ $row['prestasi']->count() }} {{ __('prestasi') }}</p>
              <div class="tabel-publik-wrap">
                {{-- Tabel --}}
                <table class="tabel-publik">
                  <thead><tr><th>{{ __('Prestasi') }}</th><th>{{ __('Tingkat') }}</th><th>{{ __('Tahun') }}</th></tr></thead>
                  <tbody>
                    @foreach ($row['prestasi'] as $p)
                      <tr>
                        <td>
                          <span class="label-kriteria {{ $p->kategori === \App\Models\Prestasi::KATEGORI_NON_AKADEMIK ? 'non' : '' }}">{{ __($p->kategori) }}</span>
                          <div style="margin-top:6px;"><strong>{{ $p->judul }}</strong>@if ($loop->first) <span class="label-kriteria" style="margin-left:4px;">{{ __('Terbaru') }}</span>@endif</div>
                          @if ($p->penyelenggara)<span class="sub">{{ $p->penyelenggara }}</span>@endif
                        </td>
                        <td>{{ __($p->tingkat ?? '-') }}</td>
                        <td>{{ $p->tanggal?->format('Y') ?? '-' }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endforeach
      </div>
      @else
      {{-- Tab Kriteria --}}
      @php $kolom = strtolower($kodeKategori); @endphp
      <div class="grid-4 reveal-stagger" id="berprestasiGrid">
        @forelse ($daftarBerprestasi as $row)
          @php $m = $row['mahasiswa']; $nilai = $row['nilai'][$kolom]; @endphp
          <div class="card person-card reveal">
            <div class="person-photo" @if ($m->foto_url) style="background-image:url('{{ $m->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif>
              <span class="person-badge">{{ $kodeKategori === 'C1' ? 'IPK '.number_format($nilai, 2) : number_format($nilai, 0).' '.__('poin') }}</span>
            </div>
            <div class="person-body">
              <h4>{{ $m->nama }}</h4>
              <div class="person-meta">{{ $m->nim }} &middot; {{ __('Angkatan') }} {{ $m->angkatan ?? '-' }}</div>
              <div class="person-achieve">
                @if ($kodeKategori === 'C1')
                  {{ __('Nilai Akademik (IPK):') }} <strong>{{ number_format($nilai, 2) }}</strong>
                @else
                  @foreach ($row['prestasi']->where('kategori', $kategori) as $p)
                    <div>{{ $p->judul }} <span style="color:var(--grey-500);">({{ __('Tingkat') }} {{ __($p->tingkat ?? '-') }})</span></div>
                  @endforeach
                @endif
              </div>
            </div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>{{ __('Belum ada mahasiswa berprestasi pada kategori :kategori.', ['kategori' => __($kategori)]) }}</p>
          </div>
        @endforelse
      </div>
      @endif
      <p class="tabel-publik-info">
        <i class="fa-solid fa-circle-info" style="color:var(--blue-600);"></i>
        {{ __('Hanya prestasi yang sudah disetujui Staff Prodi yang ditampilkan. Poin, skor, dan urutan peringkat (Nilai Akademik, Prestasi Akademik, Prestasi Non-Akademik, Keaktifan Organisasi) tersedia di halaman Ranking Mahasiswa.') }}
      </p>
      <div class="text-center" style="margin-top:36px;">
        <a href="{{ url('/ranking') }}" class="btn btn-primary">{{ __('Lihat Ranking Mahasiswa') }}</a>
      </div>
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
