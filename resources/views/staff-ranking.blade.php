<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ranking Mahasiswa | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-ranking">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Ranking Mahasiswa</h1>
            <div class="subtitle">Perhitungan metode SAW dengan empat kriteria dan bobot tetap.</div>
          </div>
        </div>
        <div class="admin-profile">
          <div class="admin-avatar"><i class="fa-solid fa-user"></i></div>
          <div>
            <div class="name">{{ auth()->user()->name }}</div>
            <div class="role">{{ auth()->user()->label_role }}</div>
          </div>
        </div>
      </header>

      <div class="content">
      @if ($errors->any())
        <div class="alert-error"><strong><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</strong></div>
      @endif

      {{-- ===== BOBOT KRITERIA (TETAP / READ-ONLY sesuai revisi dosen) ===== --}}
      <div class="panel">
        <div class="panel-head">
          <h2>Bobot Kriteria</h2>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <span class="badge badge-grey"><i class="fa-solid fa-lock"></i> Bobot tetap</span>
            <span class="badge {{ abs($totalBobot - 1) < 0.0001 ? 'badge-green' : 'badge-red' }}" id="totalBobot">Total bobot: {{ rtrim(rtrim(number_format($totalBobot * 100, 2), '0'), '.') }}%</span>
          </div>
        </div>
        <div class="form-grid">
          @foreach ($kriteria as $kode => $k)
            <div class="form-group">
              <label for="bobot{{ $kode }}">{{ $k['nama'] }} ({{ $kode }}) &mdash; Bobot (%)</label>
              <input type="text" id="bobot{{ $kode }}" class="input-terkunci"
                     value="{{ rtrim(rtrim(number_format((float) ($bobot->get($kode)->bobot ?? 0) * 100, 2), '0'), '.') }}"
                     disabled readonly aria-readonly="true">
              <div class="form-hint">Sumber nilai: {{ $k['sumber'] }} &middot; Tipe: {{ ucfirst($bobot->get($kode)->tipe_bobot ?? $k['tipe']) }}</div>
            </div>
          @endforeach
        </div>
        <p class="form-hint" style="margin-top:14px;">
          Bobot ditetapkan sesuai acuan perhitungan SAW Program Studi dan tersimpan pada tabel <code>ranking_bobot</code>.
          Bobot hanya ditampilkan dan <strong>tidak dapat diubah</strong> melalui website.
          Poin Prestasi Akademik dan Prestasi Non-Akademik dihitung dari prestasi yang <strong>sudah disetujui</strong>,
          Keaktifan Organisasi dari jabatan organisasi, dan Nilai Akademik dari IPK mahasiswa.
        </p>
      </div>

      {{-- ===== SKEMA POIN (acuan sheet "Skema Skor") ===== --}}
      <div class="two-col">
        <div class="panel">
          <div class="panel-head"><h2>Skema Poin Tingkat Prestasi</h2></div>
          <div class="table-wrap">
            <table class="data-table">
              <thead><tr><th>Tingkat Prestasi</th><th>Prestasi Akademik (C2)</th><th>Prestasi Non-Akademik (C3)</th></tr></thead>
              <tbody>
                @foreach (config('saw.tingkat') as $t)
                  <tr>
                    <td><strong>{{ $t }}</strong></td>
                    <td>{{ config('saw.prestasi_akademik.skor.'.$t) }} poin</td>
                    <td>{{ config('saw.prestasi_non_akademik.skor.'.$t) }} poin</td>
                  </tr>
                @endforeach
                <tr>
                  <td>Bonus per prestasi tambahan</td>
                  <td>+{{ config('saw.prestasi_akademik.bonus_per_tambahan') }} (maks. {{ config('saw.prestasi_akademik.maks_tambahan') }} prestasi)</td>
                  <td>+{{ config('saw.prestasi_non_akademik.bonus_per_tambahan') }} (maks. {{ config('saw.prestasi_non_akademik.maks_tambahan') }} prestasi)</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="form-hint" style="margin-top:12px;">Poin = poin tingkat tertinggi + bonus prestasi tambahan, maksimal 100.</p>
        </div>
        <div class="panel">
          <div class="panel-head"><h2>Skema Poin Jabatan Organisasi</h2></div>
          <div class="table-wrap">
            <table class="data-table">
              <thead><tr><th>Jabatan</th><th>Poin</th></tr></thead>
              <tbody>
                @foreach (config('saw.organisasi.jabatan') as $jabatan => $poin)
                  <tr><td>{{ $jabatan }}</td><td>{{ $poin }} poin</td></tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <p class="form-hint" style="margin-top:12px;">Keaktifan Organisasi = poin jabatan tertinggi + {{ (int) (config('saw.organisasi.bonus_organisasi_kedua') * 100) }}% poin jabatan tertinggi kedua (dibulatkan), maksimal 100.</p>
        </div>
      </div>

      {{-- ===== HASIL PERHITUNGAN SAW ===== --}}
      <div class="panel">
        <div class="panel-head">
          <h2>Hasil Perhitungan Ranking (SAW)</h2>
          <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <form method="POST" action="{{ route('staff-ranking.generate') }}" style="display:inline;">
              @csrf
              <input type="hidden" name="tahun" value="{{ $tahun }}">
              <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-calculator"></i> Hitung &amp; Simpan Ranking {{ $tahun }}</button>
            </form>
            @if ($rankingTersimpan > 0)
              <form method="POST" action="{{ route('staff-ranking.reset') }}" style="display:inline;"
                    data-konfirmasi="Kosongkan seluruh data ranking?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Reset</button>
              </form>
            @endif
            <a href="{{ url('/ranking') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr>
              <th>Ranking</th><th>Mahasiswa</th>
              <th>Nilai Akademik (IPK)</th><th>Prestasi Akademik</th><th>Prestasi Non-Akademik</th><th>Keaktifan Organisasi</th>
              <th>Nilai Akhir (Vi)</th>
            </tr></thead>
            <tbody id="crudTableBody">
              @forelse ($perhitungan as $r)
                <tr>
                  <td><span class="rank-no">{{ $r['peringkat'] }}</span></td>
                  <td><strong>{{ $r['nama'] }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ $r['nim'] }} &middot; {{ $r['kelas'] ?? '-' }}</div></td>
                  <td>{{ number_format($r['nilai_akademik'], 2) }}</td>
                  <td>{{ number_format($r['prestasi_akademik'], 0) }} poin</td>
                  <td>{{ number_format($r['prestasi_non_akademik'], 0) }} poin</td>
                  <td>{{ number_format($r['keaktifan_organisasi'], 0) }} poin</td>
                  <td><span class="badge badge-blue">{{ number_format($r['skor'], 2) }}</span></td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="7"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Ranking belum tersedia. Tambahkan mahasiswa aktif terlebih dahulu.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <p class="form-hint" style="margin-top:14px;">
          Tabel di atas adalah pratinjau perhitungan langsung dari database.
          Halaman ranking publik baru menampilkan data setelah tombol
          <strong>Hitung &amp; Simpan Ranking</strong> ditekan
          (tersimpan pada tabel <code>ranking</code>).
          Data ranking tersimpan saat ini: <strong>{{ $rankingTersimpan }}</strong> baris{{ $tahunTersimpan ? ' (tahun terakhir '.$tahunTersimpan.')' : '' }}.
        </p>
      </div>

      {{-- ===== MATRIKS NORMALISASI ===== --}}
      <div class="panel">
        <div class="panel-head"><h2>Matriks Normalisasi (R)</h2></div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr>
              <th>Ranking</th><th>Mahasiswa</th>
              @foreach ($kriteria as $kode => $k)
                <th>R{{ substr($kode, 1) }} &middot; {{ $k['nama'] }} &times; {{ rtrim(rtrim(number_format((float) ($bobot->get($kode)->bobot ?? 0), 2), '0'), '.') }}</th>
              @endforeach
              <th>Nilai Akhir (Vi)</th>
            </tr></thead>
            <tbody>
              @forelse ($perhitungan as $r)
                <tr>
                  <td><span class="rank-no">{{ $r['peringkat'] }}</span></td>
                  <td><strong>{{ $r['nama'] }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ $r['nim'] }}</div></td>
                  <td>{{ number_format($r['r1'], 4) }}</td>
                  <td>{{ number_format($r['r2'], 4) }}</td>
                  <td>{{ number_format($r['r3'], 4) }}</td>
                  <td>{{ number_format($r['r4'], 4) }}</td>
                  <td><strong>{{ number_format($r['skor'], 2) }}</strong></td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="7">Belum ada data untuk dinormalisasi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <p class="form-hint" style="margin-top:14px;">
          Normalisasi kriteria benefit: R<sub>ij</sub> = X<sub>ij</sub> / max(X<sub>j</sub>).
          Nilai akhir: V<sub>i</sub> = &Sigma; W<sub>j</sub> &times; R<sub>ij</sub>. Peringkat diurutkan dari V<sub>i</sub> terbesar.
        </p>
      </div>

      </div>
    </div>
  </div>

  <!-- Flash message dari session Laravel (ditampilkan sebagai toast) -->
  @if (session('success'))
    <div data-flash="{{ session('success') }}" data-flash-tipe="ok" hidden></div>
  @endif
  @if ($errors->any())
    <div data-flash="{{ $errors->first() }}" data-flash-tipe="bad" hidden></div>
  @endif

  <form id="formLogout" method="POST" action="{{ route('logout') }}" class="d-none" style="display:none;">
    @csrf
  </form>
<script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
