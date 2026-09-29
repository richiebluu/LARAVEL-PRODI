<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Berita | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-berita">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Berita</h1>
            <div class="subtitle">Kelola berita dan kegiatan terbaru Program Studi untuk halaman publik.</div>
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
            <h2 style="margin:0;">Data Berita <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-berita') }}">
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari judul berita..."></div>
            </form>
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahBerita"><i class="fa-solid fa-plus"></i> Tambah Berita</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Gambar</th><th>Judul</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarBerita as $b)
                <tr>
                  <td><div class="table-thumb" @if ($b->gambar_url) style="background-image:url('{{ $b->gambar_url }}')" @endif></div></td>
                  <td><strong>{{ $b->judul }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ \Illuminate\Support\Str::limit($b->cuplikan, 80) }}</div></td>
                  <td>{{ $b->kategori ?? '-' }}</td>
                  <td>{{ $b->tanggal->translatedFormat('d F Y') }}</td>
                  <td><span class="badge {{ $b->status === 'terbit' ? 'badge-green' : 'badge-grey' }}">{{ \App\Models\Berita::LABEL_STATUS[$b->status] ?? $b->status }}</span></td>
                  <td class="actions">
                    @if ($b->status === 'terbit')
                      <a href="{{ route('berita.show', $b) }}" target="_blank" class="btn btn-outline btn-sm btn-icon" title="Lihat di website"><i class="fa-solid fa-eye"></i></a>
                    @endif
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditBerita"
                      data-isi-form="formEditBerita"
                      data-action="{{ route('staff-berita.update', ['berita' => $b->id_berita]) }}"
                      data-judul-modal="Edit Berita"
                      data-nilai="{{ json_encode(['judul' => $b->judul, 'kategori' => $b->kategori, 'tanggal' => $b->tanggal->format('Y-m-d'), 'ringkasan' => $b->ringkasan, 'isi' => $b->isi, 'status' => $b->status]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-berita.destroy', ['berita' => $b->id_berita]) }}" style="display:inline;" data-konfirmasi="Hapus berita {{ $b->judul }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="6"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Belum ada berita.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarBerita->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarBerita->currentPage() }} dari {{ $daftarBerita->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm" href="{{ $daftarBerita->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm" href="{{ $daftarBerita->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

      <div class="modal-overlay" id="modalTambahBerita">
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-berita.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-head"><h3>Tambah Berita</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Judul Berita *</label><input name="judul" value="{{ old('judul') }}" required></div>
                <div class="form-group"><label>Kategori</label><input name="kategori" value="{{ old('kategori') }}" placeholder="Contoh: Kegiatan, Akademik, Prestasi"></div>
                <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required></div>
                <div class="form-group full"><label>Ringkasan</label><textarea name="ringkasan" rows="2" maxlength="300">{{ old('ringkasan') }}</textarea>
                  <div class="form-hint">Tampil pada kartu berita (maks. 300 karakter). Kosongkan untuk memakai awal isi berita.</div></div>
                <div class="form-group full"><label>Isi Berita *</label><textarea name="isi" rows="7" required>{{ old('isi') }}</textarea>
                  <div class="form-hint">Pisahkan paragraf dengan baris baru.</div></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status" required>
                    <option value="terbit" @selected(old('status') === 'terbit')>Terbit</option>
                    <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                  </select></div>
                <div class="form-group"><label>Gambar</label><input type="file" name="gambar" accept="image/*"></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <div class="modal-overlay" id="modalEditBerita">
        <div class="modal-box">
          <form method="POST" action="#" id="formEditBerita" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-head"><h3 data-modal-title>Edit Berita</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Judul Berita *</label><input name="judul" required></div>
                <div class="form-group"><label>Kategori</label><input name="kategori" placeholder="Contoh: Kegiatan, Akademik, Prestasi"></div>
                <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal" required></div>
                <div class="form-group full"><label>Ringkasan</label><textarea name="ringkasan" rows="2" maxlength="300"></textarea>
                  <div class="form-hint">Tampil pada kartu berita (maks. 300 karakter). Kosongkan untuk memakai awal isi berita.</div></div>
                <div class="form-group full"><label>Isi Berita *</label><textarea name="isi" rows="7" required></textarea>
                  <div class="form-hint">Pisahkan paragraf dengan baris baru.</div></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status" required>
                    <option value="terbit">Terbit</option>
                    <option value="draft">Draft</option>
                  </select></div>
                <div class="form-group"><label>Ganti Gambar</label><input type="file" name="gambar" accept="image/*"></div>
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
