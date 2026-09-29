<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Akreditasi | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-akreditasi">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Akreditasi</h1>
            <div class="subtitle">Perubahan langsung terlihat pada halaman akreditasi publik.</div>
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
        <div class="alert-error">
          <strong><i class="fa-solid fa-circle-exclamation"></i> Periksa kembali isian Anda:</strong>
          <ul>@foreach ($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach</ul>
        </div>
      @endif

      @php $terbaru = $daftarAkreditasi->first(); @endphp

      {{-- REVISI 28-09-2026 tahap 2 ("REVISI BARU(1).docx"): tabel/list data akreditasi dipindah ke ATAS,
           form tambah + pratinjau badge di bawahnya. Komponen & CRUD tidak berubah. --}}
      <div class="panel">
        <div class="panel-head"><h2>Riwayat Akreditasi <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">({{ $daftarAkreditasi->count() }} data)</span></h2>
          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ url('/akreditasi') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
            <a href="#tambahAkreditasi" class="btn btn-primary btn-sm" data-fokus="#formAkreditasi [name=peringkat]"><i class="fa-solid fa-plus"></i> Tambah Akreditasi</a>
          </div></div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Peringkat</th><th>Lembaga</th><th>Nomor SK</th><th>Mulai</th><th>Berakhir</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody>
              @forelse ($daftarAkreditasi as $a)
                <tr>
                  <td><strong>{{ $a->peringkat }}</strong></td>
                  <td>{{ $a->lembaga ?? '-' }}</td>
                  <td>{{ $a->nomor_sk ?? '-' }}</td>
                  <td>{{ optional($a->tanggal_mulai)->translatedFormat('d M Y') ?? '-' }}</td>
                  <td>{{ optional($a->tanggal_berakhir)->translatedFormat('d M Y') ?? '-' }}</td>
                  <td><span class="badge {{ $a->status === 'Terakreditasi' ? 'badge-green' : 'badge-grey' }}">{{ $a->status }}</span></td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditAkreditasi"
                      data-isi-form="formEditAkreditasi"
                      data-action="{{ route('staff-akreditasi.update', $a) }}"
                      data-nilai="{{ json_encode([
                        'peringkat' => $a->peringkat,
                        'lembaga' => $a->lembaga,
                        'nomor_sk' => $a->nomor_sk,
                        'tanggal_mulai' => optional($a->tanggal_mulai)->format('Y-m-d'),
                        'tanggal_berakhir' => optional($a->tanggal_berakhir)->format('Y-m-d'),
                      ]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-akreditasi.destroy', $a) }}" style="display:inline;"
                          data-konfirmasi="Hapus data akreditasi ini?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="7"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Belum ada data akreditasi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>


      <div class="two-col">
        <div class="panel" id="tambahAkreditasi">
          <div class="panel-head"><h2>Tambah Data Akreditasi</h2></div>
          <form id="formAkreditasi" method="POST" action="{{ route('staff-akreditasi.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
              <div class="form-group"><label>Peringkat *</label><input name="peringkat" value="{{ old('peringkat') }}" placeholder="Contoh: Baik Sekali" required></div>
              <div class="form-group"><label>Lembaga Akreditasi</label><input name="lembaga" value="{{ old('lembaga') }}" placeholder="Contoh: LAM INFOKOM"></div>
              <div class="form-group full"><label>Nomor SK</label><input name="nomor_sk" value="{{ old('nomor_sk') }}"></div>
              <div class="form-group"><label>Tanggal Mulai</label><input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"></div>
              <div class="form-group"><label>Tanggal Berakhir</label><input type="date" name="tanggal_berakhir" value="{{ old('tanggal_berakhir') }}"></div>
              <div class="form-group full"><label>Dokumen SK</label><input type="file" name="dokumen" accept=".pdf,image/*">
                <div class="form-hint">PDF atau gambar, maksimal 4 MB.</div></div>
            </div>
            <div style="margin-top:20px;">
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Akreditasi</button>
            </div>
            <p class="form-hint" style="margin-top:12px;">
              Status akreditasi (Terakreditasi / Masa Berlaku Berakhir) dihitung otomatis dari tanggal berakhir,
              dan tahun penetapan diambil dari tanggal mulai — keduanya tidak perlu diisi manual.
            </p>
          </form>
        </div>

        <div class="panel">
          <div class="panel-head"><h2>Pratinjau Badge</h2></div>
          <div class="akreditasi-seal" style="background:var(--grad-primary); color:#fff; border-color:rgba(255,255,255,.4);">
            <b id="prevPeringkat">{{ $terbaru->peringkat ?? '-' }}</b>
            <span id="prevStatus">{{ $terbaru->status ?? 'Belum ada data' }}</span>
          </div>
          <p class="form-hint" style="text-align:center; margin-top:18px;">Badge ini yang tampil pada beranda dan halaman akreditasi.</p>
        </div>
      </div>

      <div class="modal-overlay" id="modalEditAkreditasi">
        <div class="modal-box">
          <form method="POST" action="#" id="formEditAkreditasi" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-head"><h3 data-modal-title>Edit Data Akreditasi</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group"><label>Peringkat *</label><input name="peringkat" required></div>
                <div class="form-group"><label>Lembaga Akreditasi</label><input name="lembaga"></div>
                <div class="form-group full"><label>Nomor SK</label><input name="nomor_sk"></div>
                <div class="form-group"><label>Tanggal Mulai</label><input type="date" name="tanggal_mulai"></div>
                <div class="form-group"><label>Tanggal Berakhir</label><input type="date" name="tanggal_berakhir"></div>
                <div class="form-group full"><label>Ganti Dokumen SK</label><input type="file" name="dokumen" accept=".pdf,image/*"></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
            </div>
          </form>
        </div>
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
