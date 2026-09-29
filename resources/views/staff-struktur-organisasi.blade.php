<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struktur Organisasi | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-struktur-organisasi">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Struktur Organisasi</h1>
            <div class="subtitle">Data Master struktur organisasi yang tampil pada Profil Program Studi.</div>
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
            <h2 style="margin:0;">Struktur Organisasi <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $daftarStruktur->count() }}</span> jabatan)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <a href="{{ url('/struktur-organisasi') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahStruktur"><i class="fa-solid fa-plus"></i> Tambah Jabatan</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>No</th><th>Foto</th><th>Jabatan</th><th>Nama Pejabat</th><th>Sumber Data</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarStruktur as $s)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><div class="table-thumb" @if ($s->foto_url) style="background-image:url('{{ $s->foto_url }}')" @else style="background-color:var(--grey-100);" @endif></div></td>
                  <td><strong>{{ $s->jabatan }}</strong></td>
                  <td>{{ $s->nama_pejabat }}</td>
                  <td>@if ($s->dosen)<span class="badge badge-blue">Data Dosen</span>@else<span class="badge badge-grey">Nama manual</span>@endif</td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditStruktur"
                      data-isi-form="formEditStruktur"
                      data-action="{{ route('staff-struktur-organisasi.update', $s) }}"
                      data-judul-modal="Edit Jabatan"
                      data-foto="{{ $s->foto_url }}"
                      data-nilai="{{ json_encode(['jabatan' => $s->jabatan, 'dosen_id' => $s->dosen_id, 'nama' => $s->nama]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-struktur-organisasi.destroy', $s) }}" style="display:inline;" data-konfirmasi="Hapus jabatan {{ $s->jabatan }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="6"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Belum ada data struktur organisasi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <datalist id="saranJabatan">
        @foreach ($saranJabatan as $j)
          <option value="{{ $j }}">
        @endforeach
      </datalist>

      <div class="modal-overlay" id="modalTambahStruktur">
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-struktur-organisasi.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-head"><h3>Tambah Jabatan</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Jabatan *</label><input name="jabatan" list="saranJabatan" value="{{ old('jabatan') }}" placeholder="Contoh: Koordinator Program Studi" required>
                  <div class="form-hint">Pilih saran atau ketik jabatan lain (mis. Koordinator Gugus ...).</div></div>
                <div class="form-group full"><label>Pejabat dari Data Dosen</label>
                  <select name="dosen_id">
                    <option value="">-- Bukan dosen / isi nama manual --</option>
                    @foreach ($daftarDosen as $dsn)
                      <option value="{{ $dsn->nuptk }}" @selected(old('dosen_id') == $dsn->nuptk)>{{ $dsn->nama }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group"><label>Nama Pejabat</label><input name="nama" value="{{ old('nama') }}">
                  <div class="form-hint">Wajib bila pejabat tidak dipilih dari data dosen.</div></div>
                <div class="form-group full"><label>Foto Pejabat</label>
                  <div style="display:flex; gap:12px; align-items:center;">
                    <div class="table-thumb" data-foto-preview style="background-color:var(--grey-100);"></div>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-preview-foto style="flex:1; min-width:0;">
                  </div>
                  <div class="form-hint">JPG/PNG/WEBP, maksimal 2 MB. Bila kosong, foto diambil dari data dosen (jika dipilih).</div></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <div class="modal-overlay" id="modalEditStruktur">
        <div class="modal-box">
          <form method="POST" action="#" id="formEditStruktur" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-head"><h3 data-modal-title>Edit Jabatan</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Jabatan *</label><input name="jabatan" list="saranJabatan" placeholder="Contoh: Koordinator Program Studi" required>
                  <div class="form-hint">Pilih saran atau ketik jabatan lain (mis. Koordinator Gugus ...).</div></div>
                <div class="form-group full"><label>Pejabat dari Data Dosen</label>
                  <select name="dosen_id">
                    <option value="">-- Bukan dosen / isi nama manual --</option>
                    @foreach ($daftarDosen as $dsn)
                      <option value="{{ $dsn->nuptk }}">{{ $dsn->nama }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group"><label>Nama Pejabat</label><input name="nama">
                  <div class="form-hint">Wajib bila pejabat tidak dipilih dari data dosen.</div></div>
                <div class="form-group full"><label>Ganti Foto Pejabat</label>
                  <div style="display:flex; gap:12px; align-items:center;">
                    <div class="table-thumb" data-foto-preview style="background-color:var(--grey-100);"></div>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-preview-foto style="flex:1; min-width:0;">
                  </div>
                  <label style="display:flex; gap:8px; align-items:center; font-weight:500; margin-top:8px;"><input type="checkbox" name="hapus_foto" value="1" style="width:auto;"> Hapus foto yang diunggah</label>
                  <div class="form-hint">JPG/PNG/WEBP, maksimal 2 MB. Kosongkan bila tidak ingin mengganti foto.</div></div>
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
