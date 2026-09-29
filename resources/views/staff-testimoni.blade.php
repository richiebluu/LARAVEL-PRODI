<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Testimoni | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-testimoni">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Testimoni</h1>
            <div class="subtitle">Testimoni Alumni yang tampil pada halaman Testimoni di website publik.</div>
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
            <h2 style="margin:0;">Testimoni Alumni <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-testimoni') }}">
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari nama / perusahaan..."></div>
            </form>
            <a href="{{ url('/testimoni') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahTestimoni"><i class="fa-solid fa-plus"></i> Tambah Testimoni</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Foto</th><th>Nama Alumni</th><th>Tahun Kelulusan</th><th>Perusahaan &amp; Jabatan</th><th>Testimoni</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarTestimoni as $t)
                <tr>
                  <td><div class="table-thumb" @if ($t->foto_url) style="background-image:url('{{ $t->foto_url }}')" @endif></div></td>
                  <td><strong>{{ $t->nama }}</strong></td>
                  <td>{{ $t->tahun_kelulusan ?? '-' }}</td>
                  <td>{{ $t->nama_perusahaan ?? '-' }}<div style="font-size:.78rem; color:var(--grey-500);">{{ $t->jabatan ?? '-' }}</div></td>
                  <td style="max-width:320px;">{{ \Illuminate\Support\Str::limit($t->isi, 90) }}</td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditTestimoni"
                      data-isi-form="formEditTestimoni"
                      data-action="{{ route('staff-testimoni.update', $t) }}"
                      data-judul-modal="Edit Testimoni Alumni"
                      data-foto="{{ $t->foto_url }}"
                      data-nilai="{{ json_encode(['nama' => $t->nama, 'tahun_kelulusan' => $t->tahun_kelulusan, 'nama_perusahaan' => $t->nama_perusahaan, 'jabatan' => $t->jabatan, 'isi' => $t->isi]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-testimoni.destroy', $t) }}" style="display:inline;" data-konfirmasi="Hapus testimoni {{ $t->nama }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="6"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>{{ $cari ? 'Tidak ada testimoni yang cocok dengan pencarian.' : 'Belum ada testimoni alumni.' }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarTestimoni->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarTestimoni->currentPage() }} dari {{ $daftarTestimoni->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm" href="{{ $daftarTestimoni->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm" href="{{ $daftarTestimoni->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

@foreach (['tambah' => null, 'edit' => 1] as $mode => $edit)
      <div class="modal-overlay" id="{{ $edit ? 'modalEditTestimoni' : 'modalTambahTestimoni' }}">
        <div class="modal-box">
          <form method="POST" action="{{ $edit ? '#' : route('staff-testimoni.store') }}" @if ($edit) id="formEditTestimoni" @endif enctype="multipart/form-data">
            @csrf
            @if ($edit) @method('PUT') @endif
            <div class="modal-head"><h3 data-modal-title>{{ $edit ? 'Edit Testimoni Alumni' : 'Tambah Testimoni Alumni' }}</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group"><label>Nama Alumni *</label><input name="nama" maxlength="150" value="{{ $edit ? '' : old('nama') }}" required></div>
                <div class="form-group"><label>Tahun Kelulusan</label><input type="number" name="tahun_kelulusan" min="2000" max="{{ now()->year + 1 }}" placeholder="Contoh: 2023" value="{{ $edit ? '' : old('tahun_kelulusan') }}"></div>
                <div class="form-group"><label>Nama Perusahaan</label><input name="nama_perusahaan" maxlength="150" placeholder="Contoh: PT Teknologi Nusantara" value="{{ $edit ? '' : old('nama_perusahaan') }}"></div>
                <div class="form-group"><label>Jabatan</label><input name="jabatan" maxlength="150" placeholder="Contoh: Web Developer" value="{{ $edit ? '' : old('jabatan') }}"></div>
                <div class="form-group full"><label>Isi Testimoni *</label><textarea name="isi" rows="4" maxlength="1000" required>{{ $edit ? '' : old('isi') }}</textarea></div>
                <div class="form-group"><label>{{ $edit ? 'Ganti Foto' : 'Foto' }}</label>
                  <div style="display:flex; gap:12px; align-items:center;">
                    <div class="table-thumb" data-foto-preview style="background-color:var(--grey-100);"></div>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-preview-foto style="flex:1; min-width:0;">
                  </div>
                  <div class="form-hint">JPG/PNG/WEBP, maksimal 2 MB.{{ $edit ? ' Kosongkan bila tidak ingin mengganti foto.' : '' }}</div></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ $edit ? 'Simpan Perubahan' : 'Simpan' }}</button>
            </div>
          </form>
        </div>
      </div>
@endforeach

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
