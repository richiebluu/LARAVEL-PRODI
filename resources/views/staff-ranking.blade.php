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

  {{-- Sidebar --}}
  @include('partials.staff-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Ranking Mahasiswa</h1>
            <div class="subtitle">Bobot kriteria dengan metode AHP, perangkingan dengan metode SAW.</div>
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

      {{-- Bobot Kriteria + Dasar Pembobotan --}}
      <div class="panel">
        <div class="panel-head">
          <h2>Bobot Kriteria &amp; Dasar Pembobotan</h2>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <span class="badge badge-grey"><i class="fa-solid fa-lock"></i> Bobot hasil AHP</span>
            <span class="badge {{ abs($totalBobot - 1) < 0.0001 ? 'badge-green' : 'badge-red' }}" id="totalBobot">Total bobot: {{ rtrim(rtrim(number_format($totalBobot * 100, 2), '0'), '.') }}%</span>
            <button type="button" class="btn btn-outline btn-sm" data-modal-open="modalDasarPembobotan"><i class="fa-solid fa-pen"></i> Ubah Dasar Pembobotan</button>
          </div>
        </div>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>Kode</th><th>Kriteria</th><th>Bobot</th><th>Dasar Pembobotan</th></tr></thead>
            <tbody>
              @foreach ($kriteria as $kode => $k)
                @php $b = $bobot->get($kode); @endphp
                <tr>
                  <td><strong>{{ $kode }}</strong></td>
                  <td style="min-width:170px;">
                    <strong>{{ $k['nama'] }}</strong>
                    <div style="font-size:.78rem; color:var(--grey-500);">Sumber: {{ $k['sumber'] }} &middot; {{ ucfirst($b->tipe_bobot ?? $k['tipe']) }}</div>
                  </td>
                  <td style="white-space:nowrap;">
                    <span class="badge badge-blue" style="font-size:.85rem;">{{ $b?->persen ?? '0' }}%</span>
                    <div style="font-size:.75rem; color:var(--grey-500); margin-top:4px;">AHP: {{ number_format($ahp['priority_vector'][$kode], 4) }}</div>
                  </td>
                  <td style="font-size:.85rem; line-height:1.6; min-width:280px;">
                    {{ $b?->dasar_pembobotan ?: ($k['dasar'] ?? '-') }}
                    <div style="font-size:.78rem; color:var(--grey-500); margin-top:6px;"><i class="fa-solid fa-scale-balanced"></i> {{ $dasarAhp[$kode] ?? '' }}</div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <p class="form-hint" style="margin-top:14px;">
          Bobot ditentukan dengan metode <strong>AHP</strong> (lihat panel Perhitungan Bobot AHP di bawah), dibulatkan 2 desimal,
          lalu disimpan pada tabel <code>ranking_bobot</code> dan dipakai metode <strong>SAW</strong> untuk perangkingan.
          Angka bobot <strong>tidak dapat diketik manual</strong>; yang dapat diubah hanya teks dasar pembobotan
          (kolom <code>dasar_pembobotan</code>) sebagai dokumentasi/laporan.
          Poin Prestasi Akademik dan Prestasi Non-Akademik dihitung dari prestasi yang <strong>sudah disetujui</strong>,
          Keaktifan Organisasi dari jabatan organisasi, dan Nilai Akademik dari IPK mahasiswa.
        </p>
      </div>

      {{-- Perhitungan Bobot AHP --}}
      <div class="panel">
        <div class="panel-head">
          <h2>Perhitungan Bobot AHP</h2>
          <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <span class="badge {{ $ahp['konsisten'] ? 'badge-green' : 'badge-red' }}">
              <i class="fa-solid {{ $ahp['konsisten'] ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
              CR = {{ number_format($ahp['cr'], 4) }} &mdash; {{ $ahp['konsisten'] ? 'Konsisten' : 'Tidak konsisten' }}
            </span>
            <span class="badge {{ $bobotSesuaiAhp ? 'badge-green' : 'badge-grey' }}">
              {{ $bobotSesuaiAhp ? 'Bobot tersimpan = hasil AHP' : 'Bobot tersimpan belum sama dengan AHP — tekan Hitung & Simpan Ranking' }}
            </span>
          </div>
        </div>

        <h4 style="margin:4px 0 10px 0;">1. Matriks Perbandingan Berpasangan (skala Saaty 1–9)</h4>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>Kriteria</th>@foreach ($ahp['kode'] as $kolom)<th>{{ $kolom }}</th>@endforeach</tr></thead>
            <tbody>
              @foreach ($ahp['kode'] as $baris)
                <tr>
                  <td><strong>{{ $baris }}</strong> &middot; {{ $kriteria[$baris]['nama'] }}</td>
                  @foreach ($ahp['kode'] as $kolom)
                    @php $nilai = $ahp['matriks'][$baris][$kolom]; @endphp
                    <td>{{ $nilai >= 1 ? rtrim(rtrim(number_format($nilai, 2), '0'), '.') : '1/'.round(1 / $nilai) }}</td>
                  @endforeach
                </tr>
              @endforeach
              <tr>
                <td><strong>2. Jumlah kolom</strong></td>
                @foreach ($ahp['kode'] as $kolom)<td><strong>{{ number_format($ahp['jumlah_kolom'][$kolom], 4) }}</strong></td>@endforeach
              </tr>
            </tbody>
          </table>
        </div>

        <h4 style="margin:22px 0 10px 0;">3–4. Normalisasi Matriks &amp; Priority Vector (Bobot)</h4>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>Kriteria</th>@foreach ($ahp['kode'] as $kolom)<th>{{ $kolom }}</th>@endforeach<th>Jumlah Baris</th><th>Priority Vector (w)</th><th>Bobot Dipakai</th></tr></thead>
            <tbody>
              @foreach ($ahp['kode'] as $baris)
                <tr>
                  <td><strong>{{ $baris }}</strong></td>
                  @foreach ($ahp['kode'] as $kolom)<td>{{ number_format($ahp['normalisasi'][$baris][$kolom], 4) }}</td>@endforeach
                  <td>{{ number_format($ahp['jumlah_baris_normalisasi'][$baris], 4) }}</td>
                  <td><strong>{{ number_format($ahp['priority_vector'][$baris], 4) }}</strong></td>
                  <td><span class="badge badge-blue">{{ number_format($ahp['bobot_dibulatkan'][$baris], 2) }}</span></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <h4 style="margin:22px 0 10px 0;">5–6. Weighted Sum Vector &amp; Consistency Vector</h4>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>Kriteria</th><th>Weighted Sum (A &times; w)</th><th>Priority Vector (w)</th><th>Consistency Vector (WSV / w)</th></tr></thead>
            <tbody>
              @foreach ($ahp['kode'] as $baris)
                <tr>
                  <td><strong>{{ $baris }}</strong></td>
                  <td>{{ number_format($ahp['weighted_sum'][$baris], 4) }}</td>
                  <td>{{ number_format($ahp['priority_vector'][$baris], 4) }}</td>
                  <td>{{ number_format($ahp['consistency_vector'][$baris], 4) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <h4 style="margin:22px 0 10px 0;">7–10. Uji Konsistensi</h4>
        <dl class="kv">
          <dt>&lambda;<sub>max</sub> = &Sigma;CV / n</dt><dd>{{ number_format($ahp['lambda_max'], 4) }}</dd>
          <dt>CI = (&lambda;<sub>max</sub> &minus; n) / (n &minus; 1)</dt><dd>{{ number_format($ahp['ci'], 4) }}</dd>
          <dt>RI (n = {{ $ahp['n'] }})</dt><dd>{{ number_format($ahp['ri'], 2) }}</dd>
          <dt>CR = CI / RI</dt><dd><strong>{{ number_format($ahp['cr'], 4) }}</strong></dd>
          <dt>Keputusan</dt><dd>{{ $ahp['konsisten'] ? 'Konsisten (CR ≤ '.$ahp['batas_cr'].'), bobot layak dipakai' : 'Tidak konsisten (CR > '.$ahp['batas_cr'].'), perbandingan harus diperbaiki' }}</dd>
        </dl>
        <p class="form-hint" style="margin-top:14px;">
          Kode perhitungan: <code>app/Http/Controllers/Staff/RankingController.php</code> &rarr; method <code>hitungAHP()</code>.
          Nilai perbandingan berpasangan: <code>config/saw.php</code> &rarr; <code>ahp.perbandingan</code>.
          Saat tombol <strong>Hitung &amp; Simpan Ranking</strong> ditekan, bobot hasil AHP disimpan ke <code>ranking_bobot</code>
          lalu dipakai perhitungan SAW di bawah.
        </p>
      </div>

      {{-- Modal Ubah Dasar Pembobotan --}}
      <div class="modal-overlay" id="modalDasarPembobotan" @if ($errors->has('dasar') || $errors->has('dasar.*')) data-buka-otomatis @endif>
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-ranking.dasar') }}">
            @csrf
            @method('PUT')
            <div class="modal-head"><h3>Ubah Dasar Pembobotan</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                @foreach ($kriteria as $kode => $k)
                  <div class="form-group full">
                    <label for="dasar{{ $kode }}">{{ $kode }} &middot; {{ $k['nama'] }} &mdash; Bobot {{ $bobot->get($kode)?->persen ?? '0' }}%</label>
                    <textarea id="dasar{{ $kode }}" name="dasar[{{ $kode }}]" rows="4" required minlength="20" maxlength="2000">{{ old('dasar.'.$kode, $bobot->get($kode)?->dasar_pembobotan ?: ($k['dasar'] ?? '')) }}</textarea>
                  </div>
                @endforeach
              </div>
              <p class="form-hint" style="margin-top:10px;">Angka bobot tidak berubah (hasil AHP). Teks ini menjelaskan alasan bobot untuk dokumentasi dan laporan.</p>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Dasar Pembobotan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Skema Poin --}}
      <div class="two-col">
        <div class="panel">
          <div class="panel-head"><h2>Skema Poin Tingkat Prestasi</h2></div>
          <div class="table-wrap">
            {{-- Tabel --}}
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
            {{-- Tabel --}}
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

      {{-- Hasil Perhitungan SAW --}}
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
                    data-konfirmasi="Kosongkan seluruh data ranking yang tersimpan?" data-konfirmasi-judul="Reset Data Ranking?" data-konfirmasi-tombol="Ya, Kosongkan" data-konfirmasi-catatan="Halaman ranking publik akan kosong sampai ranking dihitung ulang.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Reset</button>
              </form>
            @endif
            <a href="{{ url('/ranking') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
          </div>
        </div>
        <div class="table-wrap">
          {{-- Tabel --}}
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

      {{-- Matriks Normalisasi --}}
      <div class="panel">
        <div class="panel-head"><h2>Matriks Normalisasi (R)</h2></div>
        <div class="table-wrap">
          {{-- Tabel --}}
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

  {{-- Flash Message --}}
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
