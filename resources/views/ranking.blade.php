<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ranking Mahasiswa | Program Studi Teknologi Informasi</title>
<meta name="description" content="Peringkat mahasiswa Teknologi Informasi berdasarkan empat kriteria penilaian.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="mahasiswa">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Ranking Mahasiswa</h1>
      <p>Peringkat mahasiswa Teknologi Informasi berdasarkan empat kriteria penilaian.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><a href="{{ url('/mahasiswa-berprestasi') }}">Mahasiswa Berprestasi</a><span class="sep">/</span><span class="current">Ranking</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-trophy"></i> Tiga Peringkat Teratas</span>
        <h2>Podium Mahasiswa Berprestasi</h2>
        <p>Nilai akhir dihitung dengan metode SAW dari empat kriteria: Nilai Akademik, Prestasi Akademik, Prestasi Non-Akademik, dan Keaktifan Organisasi.</p>
      </div>
      <div class="podium" id="rankPodium">
        @forelse ($podium as $i => $r)
          <div class="podium-item p{{ $i + 1 }} reveal">
            <div class="podium-medal">{{ ['🥇','🥈','🥉'][$i] ?? '🏅' }}</div>
            <div class="podium-photo" @if ($r['foto']) style="background-image:url('{{ \App\Support\Berkas::url($r['foto']) }}')" @endif></div>
            <h4>{{ $r['nama'] }}</h4>
            <div class="podium-meta">{{ $r['nim'] }} &middot; {{ $r['kelas'] ?? '-' }}</div>
            <div class="podium-score">{{ number_format($r['skor'], 2) }}<span>Nilai akhir &middot; IPK {{ number_format($r['nilai_akademik'], 2) }}</span></div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Ranking belum tersedia.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="section-pad bg-grey">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-scale-balanced"></i> Kriteria &amp; Bobot</span>
        <h2>Empat Kriteria Penilaian</h2>
      </div>
      <div class="tabel-publik-wrap reveal" style="margin-bottom:34px;">
        <table class="tabel-publik">
          <thead><tr><th>Kode</th><th>Kriteria</th><th class="kolom-opsional">Sumber Nilai</th><th>Bobot</th></tr></thead>
          <tbody>
            @foreach ($kriteria as $kode => $k)
              <tr>
                <td>{{ $kode }}</td>
                <td><strong>{{ $k['nama'] }}</strong></td>
                <td class="kolom-opsional">{{ $k['sumber'] }}</td>
                <td class="poin">{{ rtrim(rtrim(number_format((float) ($bobot->get($kode)->bobot ?? $k['bobot']) * 100, 2), '0'), '.') }}%</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if ($tahun)
        <p class="tabel-publik-info" style="margin:-20px 0 24px 0;">Ranking tahun {{ $tahun }}. Nilai akhir (V<sub>i</sub>) = &Sigma; bobot &times; nilai ternormalisasi.</p>
      @endif
      <form class="search-bar" method="GET" action="{{ route('ranking') }}">
        <input type="text" id="rankSearch" name="q" value="{{ $cari }}" placeholder="Cari nama atau NIM mahasiswa...">
      </form>
      <div class="rank-row head">
        <div>#</div><div>Mahasiswa</div>
        <div class="rank-col">Nilai Akademik (IPK)</div><div class="rank-col">Prestasi Akademik</div>
        <div class="rank-col">Prestasi Non-Akademik</div><div class="rank-col">Keaktifan Organisasi</div>
        <div>Nilai Akhir</div>
      </div>
      <div id="rankList">
        @forelse ($daftarRanking as $r)
          <div class="rank-row">
            <div class="rank-no">{{ $r['peringkat'] }}</div>
            <div class="rank-person">
              <div class="rank-photo" @if ($r['foto']) style="background-image:url('{{ \App\Support\Berkas::url($r['foto']) }}')" @endif></div>
              <div><strong>{{ $r['nama'] }}</strong><span>{{ $r['nim'] }} &middot; {{ $r['kelas'] ?? '-' }}</span></div>
            </div>
            <div class="rank-col">{{ number_format($r['nilai_akademik'], 2) }}</div>
            <div class="rank-col">{{ number_format($r['prestasi_akademik'], 0) }} poin</div>
            <div class="rank-col">{{ number_format($r['prestasi_non_akademik'], 0) }} poin</div>
            <div class="rank-col">{{ number_format($r['keaktifan_organisasi'], 0) }} poin</div>
            <div class="rank-score">{{ number_format($r['skor'], 2) }}</div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>
              @if (! $adaRanking)
                Ranking belum tersedia.
              @elseif ($cari !== '')
                Nama itu belum ada di daftar ranking.
              @else
                Belum ada data ranking lainnya.
              @endif
            </p>
          </div>
        @endforelse
      </div>
      <div class="kodex-tip" style="margin-top:30px;">
        <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
        <div><strong>Siapa yang jadi Top 1?</strong><p>Prestasi Akademik, Prestasi Non-Akademik yang sudah disetujui Staff Prodi, serta jabatan di organisasi ikut menambah poin mahasiswa.</p></div>
      </div>
    </div>
  </section>


  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
