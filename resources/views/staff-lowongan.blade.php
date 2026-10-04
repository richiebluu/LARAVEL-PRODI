<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lowongan Kerja | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-lowongan">
  <div class="admin-shell">

  {{-- Sidebar --}}
  @include('partials.staff-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Lowongan Kerja</h1>
            <div class="subtitle">Informasi lowongan kerja/magang beserta tautan ke sumber eksternal.</div>
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
          <ul>
            @foreach ($errors->all() as $pesan)
              <li>{{ $pesan }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="panel">
        <div class="toolbar">
          <div>
            <h2 style="margin:0;">Data Lowongan Kerja <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-lowongan') }}">
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari posisi / perusahaan..."></div>
            </form>
            {{-- Tombol Tambah --}}
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahLowongan"><i class="fa-solid fa-plus"></i> Tambah Lowongan Kerja</button>
          </div>
        </div>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>Posisi</th><th>Perusahaan</th><th>Tipe</th><th>Batas Lamaran</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarLowongan as $l)
                <tr>
                  <td><strong>{{ $l->posisi }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ $l->lokasi ?? '-' }}</div></td>
                  <td>{{ $l->perusahaan }}</td>
                  <td>{{ $l->tipe ?? '-' }}</td>
                  <td>{{ $l->batas_lamaran ? $l->batas_lamaran->translatedFormat('d F Y') : '-' }}@if ($l->sudah_ditutup) <span class="badge badge-red">Ditutup</span>@endif</td>
                  <td class="actions">
                    <a href="{{ $l->link }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm btn-icon" title="Buka tautan"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditLowongan"
                      data-isi-form="formEditLowongan"
                      data-action="{{ route('staff-lowongan.update', $l) }}"
                      data-judul-modal="Edit Lowongan Kerja"
                      data-nilai="{{ json_encode(['posisi' => $l->posisi, 'perusahaan' => $l->perusahaan, 'lokasi' => $l->lokasi, 'tipe' => $l->tipe, 'link' => $l->link, 'deskripsi' => $l->deskripsi, 'batas_lamaran' => optional($l->batas_lamaran)->format('Y-m-d')]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-lowongan.destroy', $l) }}" style="display:inline;" data-konfirmasi="Hapus lowongan {{ $l->posisi }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="5"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Belum ada lowongan pekerjaan.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarLowongan->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarLowongan->currentPage() }} dari {{ $daftarLowongan->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm" href="{{ $daftarLowongan->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm" href="{{ $daftarLowongan->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

      {{-- Modal Tambah Lowongan Kerja --}}
      <div class="modal-overlay" id="modalTambahLowongan">
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-lowongan.store') }}">
            @csrf
            <div class="modal-head"><h3>Tambah Lowongan Kerja</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group"><label>Posisi *</label><input name="posisi" value="{{ old('posisi') }}" placeholder="Contoh: Junior Web Developer" required></div>
                <div class="form-group"><label>Perusahaan *</label><input name="perusahaan" value="{{ old('perusahaan') }}" required></div>
                <div class="form-group"><label>Lokasi</label><input name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Banjarmasin / Remote"></div>
                <div class="form-group"><label>Tipe</label>
                  <select name="tipe">
                    <option value="">-- Pilih tipe --</option>
                    @foreach ($daftarTipe as $t)
                      <option value="{{ $t }}" @selected(old('tipe') === $t)>{{ $t }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group full"><label>Link Lowongan (sumber eksternal) *</label><input type="url" name="link" value="{{ old('link') }}" placeholder="https://..." required>
                  <div class="form-hint">Pengunjung diarahkan ke tautan ini untuk melamar.</div></div>
                <div class="form-group full"><label>Deskripsi Singkat</label><textarea name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea></div>
                <div class="form-group"><label>Batas Lamaran</label><input type="date" name="batas_lamaran" value="{{ old('batas_lamaran') }}">
                  <div class="form-hint">Lowongan otomatis tidak tampil setelah tanggal ini.</div></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Modal Edit Lowongan Kerja --}}
      <div class="modal-overlay" id="modalEditLowongan">
        <div class="modal-box">
          <form method="POST" action="#" id="formEditLowongan">
            @csrf
            @method('PUT')
            <div class="modal-head"><h3 data-modal-title>Edit Lowongan Kerja</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group"><label>Posisi *</label><input name="posisi" placeholder="Contoh: Junior Web Developer" required></div>
                <div class="form-group"><label>Perusahaan *</label><input name="perusahaan" required></div>
                <div class="form-group"><label>Lokasi</label><input name="lokasi" placeholder="Contoh: Banjarmasin / Remote"></div>
                <div class="form-group"><label>Tipe</label>
                  <select name="tipe">
                    <option value="">-- Pilih tipe --</option>
                    @foreach ($daftarTipe as $t)
                      <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group full"><label>Link Lowongan (sumber eksternal) *</label><input type="url" name="link" placeholder="https://..." required>
                  <div class="form-hint">Pengunjung diarahkan ke tautan ini untuk melamar.</div></div>
                <div class="form-group full"><label>Deskripsi Singkat</label><textarea name="deskripsi" rows="3"></textarea></div>
                <div class="form-group"><label>Batas Lamaran</label><input type="date" name="batas_lamaran">
                  <div class="form-hint">Lowongan otomatis tidak tampil setelah tanggal ini.</div></div>
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
