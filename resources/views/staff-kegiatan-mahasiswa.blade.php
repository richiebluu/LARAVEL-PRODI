<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kegiatan Mahasiswa | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-kegiatan-mahasiswa">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Kegiatan Mahasiswa</h1>
            <div class="subtitle">Kegiatan mahasiswa Prodi TI yang tampil di menu Mahasiswa &gt; Kegiatan Mahasiswa.</div>
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

      {{-- REVISI 28-09-2026 tahap 2 ("REVISI BARU(1).docx"): CRUD Kegiatan Mahasiswa.
           Pola sama dengan Testimoni (foto + status). Tanpa impor CSV: tiap kegiatan
           berisi deskripsi dan foto dokumentasi yang diunggah satu per satu. --}}
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
            <h2 style="margin:0;">Data Kegiatan Mahasiswa <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-kegiatan-mahasiswa') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
              <select name="kategori" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
                <option value="">Semua kategori</option>
                @foreach ($daftarKategori as $k => $ikon)
                  <option value="{{ $k }}" @selected($kategori === $k)>{{ $k }}</option>
                @endforeach
              </select>
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari kegiatan / lokasi..."></div>
            </form>
            <a href="{{ route('kegiatan-mahasiswa') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahKegiatan"><i class="fa-solid fa-plus"></i> Tambah Kegiatan</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Foto</th><th>Kegiatan</th><th>Kategori</th><th>Tanggal</th><th>Lokasi</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarKegiatan as $k)
                <tr>
                  <td>
                    @if ($k->foto_url)
                      <div class="table-thumb" style="background-image:url('{{ $k->foto_url }}')"></div>
                    @else
                      <div class="table-thumb" style="display:flex; align-items:center; justify-content:center; background:rgba(23,105,170,.08); color:var(--blue-600); font-size:1.1rem;"><i class="fa-solid {{ $k->ikon }}"></i></div>
                    @endif
                  </td>
                  <td style="max-width:320px;"><strong>{{ $k->judul }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ $k->penyelenggara ?? \Illuminate\Support\Str::limit($k->deskripsi ?? '-', 70) }}</div></td>
                  <td><span class="badge badge-blue"><i class="fa-solid {{ $k->ikon }}"></i> {{ $k->kategori }}</span></td>
                  <td style="white-space:nowrap;">{{ $k->tanggal->translatedFormat('d M Y') }}</td>
                  <td>{{ $k->lokasi ?? '-' }}</td>
                  <td><span class="badge {{ $k->status === 'aktif' ? 'badge-green' : 'badge-grey' }}">{{ $k->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Detail" data-modal-open="modalDetailKegiatan{{ $k->id_kegiatan_mahasiswa }}"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditKegiatan"
                      data-isi-form="formEditKegiatan"
                      data-action="{{ route('staff-kegiatan-mahasiswa.update', $k) }}"
                      data-judul-modal="Edit Kegiatan Mahasiswa"
                      data-foto="{{ $k->foto_url }}"
                      data-nilai="{{ json_encode(['judul' => $k->judul, 'kategori' => $k->kategori, 'tanggal' => $k->tanggal->format('Y-m-d'), 'lokasi' => $k->lokasi, 'penyelenggara' => $k->penyelenggara, 'deskripsi' => $k->deskripsi, 'status' => $k->status]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-kegiatan-mahasiswa.destroy', $k) }}" style="display:inline;" data-konfirmasi="Hapus kegiatan {{ $k->judul }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="7"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>{{ $cari || $kategori ? 'Tidak ada kegiatan yang cocok dengan filter.' : 'Belum ada data kegiatan mahasiswa.' }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarKegiatan->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarKegiatan->currentPage() }} dari {{ $daftarKegiatan->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm {{ $daftarKegiatan->onFirstPage() ? 'disabled' : '' }}" href="{{ $daftarKegiatan->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm {{ $daftarKegiatan->hasMorePages() ? '' : 'disabled' }}" href="{{ $daftarKegiatan->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

      {{-- Detail (Lihat) --}}
      @foreach ($daftarKegiatan as $k)
        <div class="modal-overlay" id="modalDetailKegiatan{{ $k->id_kegiatan_mahasiswa }}">
          <div class="modal-box">
            <div class="modal-head"><h3>Detail Kegiatan Mahasiswa</h3>
              <button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              @if ($k->foto_url)
                <div style="height:220px; border-radius:var(--radius-md); background:url('{{ $k->foto_url }}') center/cover; margin-bottom:18px;"></div>
              @endif
              <h3 style="margin:0 0 4px 0;">{{ $k->judul }}</h3>
              <p style="font-size:.85rem; color:var(--grey-500); margin-bottom:16px;"><i class="fa-solid {{ $k->ikon }}"></i> {{ $k->kategori }}</p>
              <dl class="kv">
                <dt>Tanggal</dt><dd>{{ $k->tanggal->translatedFormat('d F Y') }}</dd>
                <dt>Lokasi</dt><dd>{{ $k->lokasi ?? '-' }}</dd>
                <dt>Penyelenggara</dt><dd>{{ $k->penyelenggara ?? '-' }}</dd>
                <dt>Deskripsi</dt><dd>@forelse ($k->paragraf as $p)<p style="margin:0 0 8px 0;">{{ $p }}</p>@empty - @endforelse</dd>
                <dt>Status</dt><dd><span class="badge {{ $k->status === 'aktif' ? 'badge-green' : 'badge-grey' }}">{{ $k->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></dd>
              </dl>
            </div>
            <div class="modal-foot"><button class="btn btn-outline" data-modal-close>Tutup</button></div>
          </div>
        </div>
      @endforeach

@foreach (['tambah' => null, 'edit' => 1] as $mode => $edit)
      <div class="modal-overlay" id="{{ $edit ? 'modalEditKegiatan' : 'modalTambahKegiatan' }}" @if (! $edit && $errors->any() && ! old('_method')) data-buka-otomatis @endif>
        <div class="modal-box">
          <form method="POST" action="{{ $edit ? '#' : route('staff-kegiatan-mahasiswa.store') }}" @if ($edit) id="formEditKegiatan" @endif enctype="multipart/form-data">
            @csrf
            @if ($edit) @method('PUT') @endif
            <div class="modal-head"><h3 data-modal-title>{{ $edit ? 'Edit Kegiatan Mahasiswa' : 'Tambah Kegiatan Mahasiswa' }}</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Nama Kegiatan *</label><input name="judul" maxlength="150" value="{{ $edit ? '' : old('judul') }}" placeholder="Contoh: Seminar Nasional Teknologi Informasi" required></div>
                <div class="form-group"><label>Kategori *</label>
                  <select name="kategori" required>
                    @foreach ($daftarKategori as $kt => $ikon)
                      <option value="{{ $kt }}" @if (! $edit) @selected(old('kategori') === $kt) @endif>{{ $kt }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group"><label>Tanggal Kegiatan *</label><input type="date" name="tanggal" value="{{ $edit ? '' : old('tanggal', now()->format('Y-m-d')) }}" required></div>
                <div class="form-group"><label>Lokasi</label><input name="lokasi" maxlength="150" value="{{ $edit ? '' : old('lokasi') }}" placeholder="Contoh: Aula Politala"></div>
                <div class="form-group"><label>Penyelenggara</label><input name="penyelenggara" maxlength="150" value="{{ $edit ? '' : old('penyelenggara') }}" placeholder="Contoh: HIMA Teknologi Informasi"></div>
                <div class="form-group full"><label>Deskripsi</label><textarea name="deskripsi" rows="5" maxlength="3000" placeholder="Ringkasan kegiatan. Pisahkan paragraf dengan baris baru.">{{ $edit ? '' : old('deskripsi') }}</textarea></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status" required>
                    <option value="aktif" @if (! $edit) @selected(old('status', 'aktif') === 'aktif') @endif>Aktif (tampil)</option>
                    <option value="nonaktif" @if (! $edit) @selected(old('status') === 'nonaktif') @endif>Nonaktif</option>
                  </select></div>
                <div class="form-group"><label>{{ $edit ? 'Ganti Foto Dokumentasi' : 'Foto Dokumentasi' }}</label>
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
