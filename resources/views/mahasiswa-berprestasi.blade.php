<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mahasiswa Berprestasi | Program Studi Teknologi Informasi</title>
<meta name="description" content="Mahasiswa berprestasi Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="mahasiswa">

  @include('partials.public-navbar')

  {{-- REVISI DOSEN 01-10-2026: hero memakai foto GTI + overlay warna utama (lihat App\Support\HeroFoto). --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('mahasiswa-berprestasi') }}">
    <div class="container">
      <h1>Mahasiswa Berprestasi</h1>
      <p>Kebanggaan Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><span class="current">Mahasiswa Berprestasi</span></div>
    </div>
  </section>

  <!-- Revisi dosen 24-09-2026: tab "Semua" berbentuk TABEL, tab kriteria lainnya tetap CARD.
       Revisi 27-09-2026: angka ranking tidak ditampilkan di halaman ini (catatan no. 3).
       Alur data: Prestasi (disetujui Staff Prodi) -> Tingkat -> Poin -> Ranking. -->
  <section class="section-pad">
    <div class="container">
      <div class="filter-bar">
        <a class="filter-btn {{ ! $kategori ? 'active' : '' }}" href="{{ route('mahasiswa-berprestasi') }}">Semua</a>
        @foreach ($daftarKriteria as $kode => $namaKriteria)
          <a class="filter-btn {{ $kategori === $namaKriteria ? 'active' : '' }}" href="{{ route('mahasiswa-berprestasi', ['kategori' => $namaKriteria]) }}">{{ $kode === 'C1' ? $namaKriteria.' (IPK)' : $namaKriteria }}</a>
        @endforeach
      </div>

      @if (! $kategori)
      {{-- ===== TAB "SEMUA" -> TABEL (REVISI 27-09-2026: seperti tabel Data Dosen) =====
           Satu baris = prestasi TERBARU tiap mahasiswa; prestasi lain lewat "Lihat Prestasi Lainnya".
           Kolom poin & angka ranking dihapus (tersedia di halaman Ranking Mahasiswa). --}}
      @if ($daftarBerprestasi->isNotEmpty())
        <div class="search-bar">
          <input type="text" id="cariBerprestasi" placeholder="Cari nama, NIM, atau prestasi..." data-search-target="#berprestasiGrid tbody tr.baris-berprestasi" autocomplete="off">
        </div>
      @endif
      <div class="tabel-publik-wrap reveal" id="berprestasiGrid">
        <table class="tabel-publik">
          <thead><tr>
            <th>No</th><th>Nama Mahasiswa</th><th class="kolom-opsional">NIM</th>
            <th>Prestasi Terbaru</th><th class="kolom-opsional">Tingkat Prestasi</th><th class="kolom-opsional">Tahun</th><th>Aksi</th>
          </tr></thead>
          <tbody>
            @forelse ($daftarBerprestasi as $row)
              @php $m = $row['mahasiswa']; $p = $row['terbaru']; $lainnya = $row['prestasi']->count() - 1; @endphp
              <tr class="baris-berprestasi">
                <td>{{ $loop->iteration }}</td>
                <td>
                  <div class="rank-person">
                    <div class="rank-photo" @if ($m->foto_url) style="background-image:url('{{ $m->foto_url }}')" @endif></div>
                    <div><strong>{{ $m->nama }}</strong><span>Angkatan {{ $m->angkatan ?? '-' }}</span></div>
                  </div>
                </td>
                <td class="kolom-opsional">{{ $m->nim }}</td>
                <td>
                  <span class="label-kriteria {{ $p->kategori === \App\Models\Prestasi::KATEGORI_NON_AKADEMIK ? 'non' : '' }}">{{ $p->kategori }}</span>
                  <div style="margin-top:6px;">{{ $p->judul }}</div>
                  @if ($p->penyelenggara)<span class="sub">{{ $p->penyelenggara }}</span>@endif
                </td>
                <td class="kolom-opsional">{{ $p->tingkat ?? '-' }}</td>
                <td class="kolom-opsional">{{ $p->tanggal?->format('Y') ?? '-' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline" style="white-space:nowrap;" data-modal-open="modalPrestasi{{ $m->nim }}">
                    Lihat Prestasi Lainnya{{ $lainnya > 0 ? ' (+'.$lainnya.')' : '' }}
                  </button>
                </td>
              </tr>
            @empty
              <tr class="empty-row"><td colspan="7">
                <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" style="width:90px; margin:0 auto 12px auto; display:block;">
                Belum ada mahasiswa berprestasi.
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Modal "Lihat Prestasi Lainnya": seluruh prestasi disetujui, terbaru di atas. --}}
      <div id="prestasiModals">
        @foreach ($daftarBerprestasi as $row)
          @php $m = $row['mahasiswa']; @endphp
          <div class="modal-overlay" id="modalPrestasi{{ $m->nim }}">
            <div class="modal-box" style="max-width:720px;">
              <button class="modal-close" data-modal-close aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
              <div class="modal-photo" @if ($m->foto_url) style="background-image:url('{{ $m->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif></div>
              <h3>{{ $m->nama }}</h3>
              <p style="color:var(--blue-600); font-weight:600; margin-bottom:16px;">{{ $m->nim }} &middot; Angkatan {{ $m->angkatan ?? '-' }} &middot; {{ $row['prestasi']->count() }} prestasi</p>
              <div class="tabel-publik-wrap">
                <table class="tabel-publik">
                  <thead><tr><th>Prestasi</th><th>Tingkat</th><th>Tahun</th></tr></thead>
                  <tbody>
                    @foreach ($row['prestasi'] as $p)
                      <tr>
                        <td>
                          <span class="label-kriteria {{ $p->kategori === \App\Models\Prestasi::KATEGORI_NON_AKADEMIK ? 'non' : '' }}">{{ $p->kategori }}</span>
                          <div style="margin-top:6px;"><strong>{{ $p->judul }}</strong>@if ($loop->first) <span class="label-kriteria" style="margin-left:4px;">Terbaru</span>@endif</div>
                          @if ($p->penyelenggara)<span class="sub">{{ $p->penyelenggara }}</span>@endif
                        </td>
                        <td>{{ $p->tingkat ?? '-' }}</td>
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
      {{-- ===== TAB KRITERIA -> CARD (komponen person-card yang sudah ada) ===== --}}
      @php $kolom = strtolower($kodeKategori); @endphp
      <div class="grid-4 reveal-stagger" id="berprestasiGrid">
        @forelse ($daftarBerprestasi as $row)
          @php $m = $row['mahasiswa']; $nilai = $row['nilai'][$kolom]; @endphp
          <div class="card person-card reveal">
            <div class="person-photo" @if ($m->foto_url) style="background-image:url('{{ $m->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif>
              <span class="person-badge">{{ $kodeKategori === 'C1' ? 'IPK '.number_format($nilai, 2) : number_format($nilai, 0).' poin' }}</span>
            </div>
            <div class="person-body">
              <h4>{{ $m->nama }}</h4>
              <div class="person-meta">{{ $m->nim }} &middot; Angkatan {{ $m->angkatan ?? '-' }}</div>
              <div class="person-achieve">
                @if ($kodeKategori === 'C1')
                  Nilai Akademik (IPK): <strong>{{ number_format($nilai, 2) }}</strong>
                @elseif ($kodeKategori === 'C4')
                  @foreach ($m->organisasi as $o)
                    <div>{{ $o->nama_organisasi }} &middot; {{ $o->jabatan }}</div>
                  @endforeach
                @else
                  @foreach ($row['prestasi']->where('kategori', $kategori) as $p)
                    <div>{{ $p->judul }} <span style="color:var(--grey-500);">(Tingkat {{ $p->tingkat ?? '-' }})</span></div>
                  @endforeach
                @endif
              </div>
            </div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Belum ada mahasiswa berprestasi pada kategori {{ $kategori }}.</p>
          </div>
        @endforelse
      </div>
      @endif
      <p class="tabel-publik-info">
        <i class="fa-solid fa-circle-info" style="color:var(--blue-600);"></i>
        Hanya prestasi yang sudah disetujui Staff Prodi yang ditampilkan. Poin, skor, dan urutan peringkat
        (Nilai Akademik, Prestasi Akademik, Prestasi Non-Akademik, Keaktifan Organisasi) tersedia di halaman Ranking Mahasiswa.
      </p>
      <div class="text-center" style="margin-top:36px;">
        <a href="{{ url('/ranking') }}" class="btn btn-primary">Lihat Ranking Mahasiswa</a>
      </div>
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>