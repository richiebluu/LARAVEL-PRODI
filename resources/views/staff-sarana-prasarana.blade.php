<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarana &amp; Prasarana | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-sarana-prasarana">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Sarana &amp; Prasarana</h1>
            <div class="subtitle">Laboratorium, ruang, dan fasilitas Program Studi yang tampil di Profil &gt; Sarana &amp; Prasarana.</div>
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

      {{-- REVISI 28-09-2026 tahap 2 ("REVISI BARU(1).docx"): CRUD Sarana & Prasarana termasuk
           nama-nama Laboratorium Prodi TI. Komponen (panel, toolbar, data-table, modal, badge)
           sama dengan halaman Data Master lainnya. --}}
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
            <h2 style="margin:0;">Data Sarana &amp; Prasarana <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data &middot; {{ $jumlahLab }} laboratorium)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-sarana-prasarana') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
              <select name="jenis" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
                <option value="">Semua jenis</option>
                @foreach ($daftarJenis as $j => $ikon)
                  <option value="{{ $j }}" @selected($jenis === $j)>{{ $j }}</option>
                @endforeach
              </select>
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari nama / lokasi / fasilitas..."></div>
            </form>
            <a href="{{ route('sarana-prasarana') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
            <button class="btn btn-outline" data-modal-open="modalImporSarana"><i class="fa-solid fa-file-import"></i> Impor CSV</button>
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahSarana"><i class="fa-solid fa-plus"></i> Tambah Sarana</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>No</th><th>Foto</th><th>Nama &amp; Lokasi</th><th>Jenis</th><th>Kapasitas</th><th>Fasilitas</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarSarana as $s)
                <tr>
                  <td>{{ $daftarSarana->firstItem() + $loop->index }}</td>
                  <td>
                    @if ($s->foto_url)
                      <div class="table-thumb" style="background-image:url('{{ $s->foto_url }}')"></div>
                    @else
                      <div class="table-thumb" style="display:flex; align-items:center; justify-content:center; background:rgba(23,105,170,.08); color:var(--blue-600); font-size:1.1rem;"><i class="fa-solid {{ $s->ikon }}"></i></div>
                    @endif
                  </td>
                  <td><strong>{{ $s->nama }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ $s->lokasi ?? '-' }}</div></td>
                  <td><span class="badge {{ $s->jenis === 'Laboratorium' ? 'badge-blue' : 'badge-cyan' }}"><i class="fa-solid {{ $s->ikon }}"></i> {{ $s->jenis }}</span></td>
                  <td>{{ $s->kapasitas ? $s->kapasitas.' orang' : '-' }}</td>
                  <td>{{ count($s->daftar_fasilitas) ? count($s->daftar_fasilitas).' item' : '-' }}</td>
                  <td><span class="badge {{ $s->status === 'aktif' ? 'badge-green' : 'badge-grey' }}">{{ $s->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Detail" data-modal-open="modalDetailSarana{{ $s->id_sarana_prasarana }}"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditSarana"
                      data-isi-form="formEditSarana"
                      data-action="{{ route('staff-sarana-prasarana.update', $s) }}"
                      data-judul-modal="Edit Sarana &amp; Prasarana"
                      data-foto="{{ $s->foto_url }}"
                      data-nilai="{{ json_encode(['nama' => $s->nama, 'jenis' => $s->jenis, 'lokasi' => $s->lokasi, 'kapasitas' => $s->kapasitas, 'fasilitas' => $s->fasilitas, 'deskripsi' => $s->deskripsi, 'status' => $s->status]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-sarana-prasarana.destroy', $s) }}" style="display:inline;" data-konfirmasi="Hapus {{ $s->nama }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="8"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>{{ $cari || $jenis ? 'Tidak ada sarana & prasarana yang cocok dengan filter.' : 'Belum ada data sarana & prasarana. Tambahkan laboratorium dan ruang Prodi satu per satu atau impor CSV.' }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarSarana->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarSarana->currentPage() }} dari {{ $daftarSarana->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm {{ $daftarSarana->onFirstPage() ? 'disabled' : '' }}" href="{{ $daftarSarana->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm {{ $daftarSarana->hasMorePages() ? '' : 'disabled' }}" href="{{ $daftarSarana->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

      {{-- Detail (Lihat) --}}
      @foreach ($daftarSarana as $s)
        <div class="modal-overlay" id="modalDetailSarana{{ $s->id_sarana_prasarana }}">
          <div class="modal-box">
            <div class="modal-head"><h3>Detail Sarana &amp; Prasarana</h3>
              <button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              @if ($s->foto_url)
                <div style="height:200px; border-radius:var(--radius-md); background:url('{{ $s->foto_url }}') center/cover; margin-bottom:18px;"></div>
              @endif
              <h3 style="margin:0 0 4px 0;">{{ $s->nama }}</h3>
              <p style="font-size:.85rem; color:var(--grey-500); margin-bottom:16px;"><i class="fa-solid {{ $s->ikon }}"></i> {{ $s->jenis }}</p>
              <dl class="kv">
                <dt>Lokasi</dt><dd>{{ $s->lokasi ?? '-' }}</dd>
                <dt>Kapasitas</dt><dd>{{ $s->kapasitas ? $s->kapasitas.' orang' : '-' }}</dd>
                <dt>Fasilitas</dt><dd>@if (count($s->daftar_fasilitas))<ul style="margin:0; padding-left:18px;">@foreach ($s->daftar_fasilitas as $f)<li>{{ $f }}</li>@endforeach</ul>@else - @endif</dd>
                <dt>Deskripsi</dt><dd>{{ $s->deskripsi ?? '-' }}</dd>
                <dt>Status</dt><dd><span class="badge {{ $s->status === 'aktif' ? 'badge-green' : 'badge-grey' }}">{{ $s->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></dd>
              </dl>
            </div>
            <div class="modal-foot"><button class="btn btn-outline" data-modal-close>Tutup</button></div>
          </div>
        </div>
      @endforeach

@foreach (['tambah' => null, 'edit' => 1] as $mode => $edit)
      <div class="modal-overlay" id="{{ $edit ? 'modalEditSarana' : 'modalTambahSarana' }}" @if (! $edit && $errors->any() && ! old('_method') && ! session('impor_gagal')) data-buka-otomatis @endif>
        <div class="modal-box">
          <form method="POST" action="{{ $edit ? '#' : route('staff-sarana-prasarana.store') }}" @if ($edit) id="formEditSarana" @endif enctype="multipart/form-data">
            @csrf
            @if ($edit) @method('PUT') @endif
            <div class="modal-head"><h3 data-modal-title>{{ $edit ? 'Edit Sarana & Prasarana' : 'Tambah Sarana & Prasarana' }}</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Nama Sarana / Ruang *</label><input name="nama" maxlength="150" value="{{ $edit ? '' : old('nama') }}" placeholder="Contoh: Laboratorium Pemrograman" required></div>
                <div class="form-group"><label>Jenis *</label>
                  <select name="jenis" required>
                    @foreach ($daftarJenis as $j => $ikon)
                      <option value="{{ $j }}" @if (! $edit) @selected(old('jenis', 'Laboratorium') === $j) @endif>{{ $j }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group"><label>Lokasi</label><input name="lokasi" maxlength="150" value="{{ $edit ? '' : old('lokasi') }}" placeholder="Contoh: Gedung TI Lantai 2"></div>
                <div class="form-group"><label>Kapasitas (orang)</label><input type="number" name="kapasitas" min="1" max="5000" value="{{ $edit ? '' : old('kapasitas') }}" placeholder="Contoh: 30"></div>
                <div class="form-group full"><label>Fasilitas</label><textarea name="fasilitas" rows="4" maxlength="2000" placeholder="Satu baris satu fasilitas, contoh:&#10;30 unit PC&#10;Proyektor&#10;Pendingin ruangan (AC)">{{ $edit ? '' : old('fasilitas') }}</textarea>
                  <div class="form-hint">Tulis satu fasilitas per baris.</div></div>
                <div class="form-group full"><label>Deskripsi Singkat</label><textarea name="deskripsi" rows="3" maxlength="1000" placeholder="Contoh: Dipakai untuk praktikum pemrograman dasar dan pemrograman web.">{{ $edit ? '' : old('deskripsi') }}</textarea></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status" required>
                    <option value="aktif" @if (! $edit) @selected(old('status', 'aktif') === 'aktif') @endif>Aktif (tampil)</option>
                    <option value="nonaktif" @if (! $edit) @selected(old('status') === 'nonaktif') @endif>Nonaktif</option>
                  </select></div>
                <div class="form-group"><label>{{ $edit ? 'Ganti Foto' : 'Foto' }}</label>
                  <div style="display:flex; gap:12px; align-items:center;">
                    <div class="table-thumb" data-foto-preview style="background-color:var(--grey-100);"></div>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-preview-foto style="flex:1; min-width:0;">
                  </div>
                  <div class="form-hint">JPG/PNG/WEBP, maksimal 2 MB.{{ $edit ? ' Kosongkan bila tidak ingin mengganti foto.' : '' }}</div>
                  @if ($edit)
                    <label style="display:flex; gap:8px; align-items:center; font-weight:500; margin-top:8px;"><input type="checkbox" name="hapus_foto" value="1" style="width:auto;"> Hapus foto yang diunggah</label>
                  @endif
                </div>
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

      @include('partials.impor-csv-modal', [
        'id' => 'modalImporSarana',
        'judul' => 'Impor Sarana & Prasarana (CSV)',
        'action' => route('staff-sarana-prasarana.impor'),
        'template' => route('staff-sarana-prasarana.template'),
        'kolom' => [
          'nama' => 'Nama laboratorium/ruang/fasilitas',
          'jenis' => implode(', ', array_keys($daftarJenis)),
          'lokasi' => 'Lokasi, mis. Gedung TI Lantai 2 (opsional)',
          'kapasitas' => 'Kapasitas orang (angka, opsional)',
          'fasilitas' => 'Pisahkan tiap fasilitas dengan tanda | (opsional)',
          'deskripsi' => 'Deskripsi singkat (opsional)',
          'status' => 'Aktif atau Nonaktif; kosong = Aktif',
        ],
        'wajib' => ['nama', 'jenis'],
        'kunci' => 'nama',
        'kunciLabel' => 'nama sarana',
        'alias' => ['nama_sarana' => 'nama', 'nama_ruang' => 'nama', 'nama_laboratorium' => 'nama'],
        'petunjuk' => 'Contoh baris: <code>Laboratorium Pemrograman,Laboratorium,Gedung TI Lt. 2,30,30 unit PC|Proyektor|AC,,Aktif</code>. Foto ditambahkan lewat tombol Edit setelah impor.',
      ])

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
