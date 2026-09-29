<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dosen Pengajar | Program Studi Teknologi Informasi</title>
<meta name="description" content="Dosen Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Dosen Pengajar</h1>
      <p>Tenaga pengajar Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/profil') }}">Profil</a><span class="sep">/</span><span class="current">Dosen Pengajar</span></div>
    </div>
  </section>

  {{-- REVISI 24-09-2026: daftar dosen berbentuk TABEL + tombol "Lihat Detail".
       REVISI 27-09-2026: kolom keahlian dihapus (diganti Email @politala.ac.id).
       REVISI 28-09-2026: kolom Jabatan diganti Status (Aktif / Pendidikan / Nonaktif).
       Data diambil dari tabel `dosen` (dikelola Staff Prodi). --}}
  <section class="section-pad">
    <div class="container">
      {{-- REVISI 28-09-2026 tahap 2: teks pengantar + ringkasan status di samping (layout tidak sepi),
           lalu kolom pencarian tepat di atas tabel dosen. --}}
      <div class="intro-baris reveal">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-chalkboard-user"></i> Tenaga Pengajar</span>
          <h2>Dosen Program Studi Teknologi Informasi</h2>
          <p>Dosen pengajar yang membimbing perkuliahan, praktikum, dan project mahasiswa. Status menunjukkan kondisi dosen saat ini: <strong>Aktif</strong> mengajar, <strong>Pendidikan</strong> (sedang studi lanjut, mis. S3), atau <strong>Nonaktif</strong>. Pilih <em>Lihat Detail</em> untuk melihat profil dan publikasi Google Scholar.</p>
        </div>
        <div class="ringkas-grid">
          @foreach (['aktif' => 'fa-user-check', 'pendidikan' => 'fa-graduation-cap', 'nonaktif' => 'fa-user-clock'] as $kode => $ikon)
            <div class="ringkas-item">
              <div class="benefit-icon"><i class="fa-solid {{ $ikon }}"></i></div>
              <div><b>{{ $ringkasanStatus[$kode] ?? 0 }}</b><span>Dosen {{ \App\Models\Dosen::LABEL_STATUS[$kode] }}</span></div>
            </div>
          @endforeach
        </div>
      </div>

      <form class="search-bar" method="GET" action="{{ route('dosen') }}" role="search">
        <input type="text" name="q" value="{{ $cari }}" placeholder="Cari nama dosen, NUPTK, atau pendidikan terakhir..." autocomplete="off"
               data-search-target=".baris-dosen" data-search-empty="#dosenTidakDitemukan" aria-label="Cari dosen">
      </form>

      <div class="tabel-publik-wrap reveal" id="dosenGrid">
        <table class="tabel-publik">
          <thead><tr>
            <th>No</th><th>Nama Dosen</th><th class="kolom-opsional">NUPTK</th><th>Status</th>
            <th class="kolom-opsional">Email</th><th>Aksi</th>
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
                <td><span class="label-kriteria status-{{ $dosen->status }}">{{ $dosen->label_status }}</span></td>
                <td class="kolom-opsional">{{ $dosen->email ?? '-' }}</td>
                <td><button class="btn btn-sm btn-outline" style="white-space:nowrap;" data-modal-open="modalDosen{{ $dosen->nuptk }}">Lihat Detail</button></td>
              </tr>
            @empty
              <tr class="empty-row"><td colspan="6">
                <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" style="width:90px; margin:0 auto 12px auto; display:block;">
                {{ $cari !== '' ? 'Dosen dengan kata kunci "'.$cari.'" tidak ditemukan.' : 'Belum ada data dosen.' }}
              </td></tr>
            @endforelse
            <tr class="empty-row cari-kosong" id="dosenTidakDitemukan"><td colspan="6">Tidak ada dosen yang cocok dengan pencarian.</td></tr>
          </tbody>
        </table>
      </div>
      <p class="tabel-publik-info">
        <i class="fa-solid fa-circle-info" style="color:var(--blue-600);"></i>
        {{ $cari !== '' ? 'Menampilkan '.$daftarDosen->count().' dari '.$totalDosen.' dosen.' : 'Jabatan struktural dosen dapat dilihat pada halaman Struktur Organisasi.' }}
        @if ($cari !== '')<a href="{{ route('dosen') }}" style="color:var(--blue-600); font-weight:600;">Tampilkan semua</a>@endif
      </p>
    </div>
  </section>

  <div id="dosenModals">
    @foreach ($daftarDosen as $dosen)
      <div class="modal-overlay" id="modalDosen{{ $dosen->nuptk }}">
        <div class="modal-box">
          <button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button>
          <div class="modal-photo" @if ($dosen->foto_url) style="background-image:url('{{ $dosen->foto_url }}')" @endif></div>
          <h3>{{ $dosen->nama }}</h3>
          <p style="margin-bottom:16px;"><span class="label-kriteria status-{{ $dosen->status }}">{{ $dosen->label_status }}</span></p>
          <p><strong>NUPTK:</strong> {{ $dosen->nuptk }}</p>
          <p><strong>Pendidikan Terakhir:</strong> {{ $dosen->pendidikan_terakhir ?? '-' }}</p>
          <p><strong>Email:</strong> {{ $dosen->email ?? '-' }}</p>
          <p><strong>Alamat:</strong> {{ $dosen->alamat ?? '-' }}</p>
          <p><strong>Tanggal Lahir:</strong> {{ optional($dosen->tanggal_lahir)->translatedFormat('d F Y') ?? '-' }}</p>
          <p style="margin-bottom:6px;"><strong>Publikasi Google Scholar:</strong></p>
          @if ($dosen->google_scholar)
            <p><a href="{{ $dosen->google_scholar }}" target="_blank" rel="noopener" style="color:var(--blue-600); font-weight:600; word-break:break-all;"><i class="fa-solid fa-graduation-cap"></i> Lihat Publikasi di Google Scholar</a></p>
          @else
            <p style="font-size:.88rem; color:var(--grey-500);">Link Google Scholar belum diisi.</p>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>