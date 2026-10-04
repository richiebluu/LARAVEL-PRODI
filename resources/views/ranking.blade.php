<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Ranking Mahasiswa | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Peringkat mahasiswa Teknologi Informasi berdasarkan empat kriteria penilaian.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="mahasiswa">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('ranking') }}">
    <div class="container">
      <h1>{{ __('Ranking Mahasiswa') }}</h1>
      <p>{{ __('Peringkat mahasiswa Teknologi Informasi berdasarkan empat kriteria penilaian.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><a href="{{ url('/mahasiswa-berprestasi') }}">{{ __('Mahasiswa Berprestasi') }}</a><span class="sep">/</span><span class="current">{{ __('Ranking') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-trophy"></i> {{ __('Tiga Peringkat Teratas') }}</span>
        <h2>{{ __('Podium Mahasiswa Berprestasi') }}</h2>
        <p>{{ __('Nilai akhir dihitung dengan metode SAW dari empat kriteria: Nilai Akademik, Prestasi Akademik, Prestasi Non-Akademik, dan Keaktifan Organisasi.') }}</p>
      </div>
      <div class="podium" id="rankPodium">
        @forelse ($podium as $i => $r)
          <div class="podium-item p{{ $i + 1 }} reveal">
            <div class="podium-medal">{{ ['🥇','🥈','🥉'][$i] ?? '🏅' }}</div>
            <div class="podium-photo" @if ($r['foto']) style="background-image:url('{{ \App\Support\Berkas::url($r['foto']) }}')" @endif></div>
            <h4>{{ $r['nama'] }}</h4>
            <div class="podium-meta">{{ $r['nim'] }} &middot; {{ $r['kelas'] ?? '-' }}</div>
            <div class="podium-score">{{ number_format($r['skor'], 2) }}<span>{{ __('Nilai akhir') }} &middot; IPK {{ number_format($r['nilai_akademik'], 2) }}</span></div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>{{ __('Ranking belum tersedia.') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="section-pad bg-grey">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-scale-balanced"></i> {{ __('Kriteria & Bobot') }}</span>
        <h2>{{ __('Empat Kriteria Penilaian') }}</h2>
      </div>
      <div class="tabel-publik-wrap reveal" style="margin-bottom:34px;">
        {{-- Tabel --}}
        <table class="tabel-publik">
          <thead><tr><th>{{ __('Kode') }}</th><th>{{ __('Kriteria') }}</th><th class="kolom-opsional">{{ __('Sumber Nilai') }}</th><th>{{ __('Bobot') }}</th></tr></thead>
          <tbody>
            @foreach ($kriteria as $kode => $k)
              <tr>
                <td>{{ $kode }}</td>
                <td><strong>{{ __($k['nama']) }}</strong></td>
                <td class="kolom-opsional">{{ __($k['sumber']) }}</td>
                <td class="poin">{{ rtrim(rtrim(number_format((float) ($bobot->get($kode)->bobot ?? $k['bobot']) * 100, 2), '0'), '.') }}%</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if ($tahun)
        <p class="tabel-publik-info" style="margin:-20px 0 24px 0;">{{ __('Ranking tahun :tahun. Nilai akhir', ['tahun' => $tahun]) }} (V<sub>i</sub>) = {{ __('Σ bobot × nilai ternormalisasi.') }}</p>
      @endif
      <form class="search-bar" method="GET" action="{{ route('ranking') }}">
        <input type="text" id="rankSearch" name="q" value="{{ $cari }}" placeholder="{{ __('Cari nama atau NIM mahasiswa...') }}">
      </form>
      <div class="rank-row head">
        <div>#</div><div>{{ __('Mahasiswa') }}</div>
        <div class="rank-col">{{ __('Nilai Akademik (IPK)') }}</div><div class="rank-col">{{ __('Prestasi Akademik') }}</div>
        <div class="rank-col">{{ __('Prestasi Non-Akademik') }}</div><div class="rank-col">{{ __('Keaktifan Organisasi') }}</div>
        <div>{{ __('Nilai Akhir') }}</div>
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
            <div class="rank-col">{{ number_format($r['prestasi_akademik'], 0) }} {{ __('poin') }}</div>
            <div class="rank-col">{{ number_format($r['prestasi_non_akademik'], 0) }} {{ __('poin') }}</div>
            <div class="rank-col">{{ number_format($r['keaktifan_organisasi'], 0) }} {{ __('poin') }}</div>
            <div class="rank-score">{{ number_format($r['skor'], 2) }}</div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>
              @if (! $adaRanking)
                {{ __('Ranking belum tersedia.') }}
              @elseif ($cari !== '')
                {{ __('Nama itu belum ada di daftar ranking.') }}
              @else
                {{ __('Belum ada data ranking lainnya.') }}
              @endif
            </p>
          </div>
        @endforelse
      </div>
      <div class="kodex-tip" style="margin-top:30px;">
        <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
        <div><strong>{{ __('Siapa yang jadi Top 1?') }}</strong><p>{{ __('Prestasi Akademik, Prestasi Non-Akademik yang sudah disetujui Staff Prodi, serta jabatan di organisasi ikut menambah poin mahasiswa.') }}</p></div>
      </div>
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
