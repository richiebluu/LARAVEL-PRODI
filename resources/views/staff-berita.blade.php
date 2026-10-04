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

  {{-- Sidebar --}}
  @include('partials.staff-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Berita</h1>
            <div class="subtitle">Kelola Kegiatan Prodi dan Kegiatan Mahasiswa untuk halaman publik.</div>
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
            <form method="GET" action="{{ route('staff-berita') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
              <select name="jenis" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
                <option value="">Semua jenis</option>
                @foreach ($daftarJenis as $kode => $label)
                  <option value="{{ $kode }}" @selected($jenis === $kode)>{{ $label }}</option>
                @endforeach
              </select>
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari judul / kategori / lokasi..."></div>
            </form>
            <a href="{{ route('berita') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
            {{-- Tombol Tambah --}}
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahBerita"><i class="fa-solid fa-plus"></i> Tambah Berita</button>
          </div>
        </div>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>Gambar</th><th>Judul</th><th>Jenis</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarBerita as $b)
                <tr>
                  <td><div class="table-thumb" @if ($b->gambar_url) style="background-image:url('{{ $b->gambar_url }}')" @endif></div></td>
                  <td><strong>{{ $b->judul }}</strong>@if ($b->media_sosial) <a href="{{ $b->link_media_sosial }}" target="_blank" rel="noopener noreferrer" title="Postingan {{ $b->media_sosial['label'] }}" style="color:var(--blue-600);"><i class="{{ $b->media_sosial['ikon'] }}"></i></a>@endif<div style="font-size:.78rem; color:var(--grey-500);">{{ \Illuminate\Support\Str::limit($b->cuplikan, 80) }}</div></td>
                  <td><span class="badge {{ $b->is_kegiatan ? 'badge-cyan' : 'badge-blue' }}">{{ $b->label_jenis }}</span></td>
                  <td>{{ $b->kategori ?? '-' }}@if ($b->lokasi)<div style="font-size:.78rem; color:var(--grey-500);"><i class="fa-solid fa-location-dot"></i> {{ $b->lokasi }}</div>@endif</td>
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
                      data-nilai="{{ json_encode(['judul' => $b->judul, 'jenis' => $b->jenis ?? \App\Models\Berita::JENIS_BERITA, 'kategori' => $b->kategori, 'lokasi' => $b->lokasi, 'penyelenggara' => $b->penyelenggara, 'tanggal' => $b->tanggal->format('Y-m-d'), 'ringkasan' => $b->ringkasan, 'isi' => $b->isi, 'link_media_sosial' => $b->link_media_sosial, 'status' => $b->status]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-berita.destroy', ['berita' => $b->id_berita]) }}" style="display:inline;" data-konfirmasi="Hapus berita {{ $b->judul }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="7"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>{{ $cari || $jenis ? 'Tidak ada berita yang cocok dengan filter.' : 'Belum ada berita.' }}</td></tr>
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

      <datalist id="saranKategoriBerita">
        @foreach ($saranKategori as $k)
          <option value="{{ $k }}">
        @endforeach
      </datalist>

      {{-- Modal Tambah Berita --}}
      <div class="modal-overlay" id="modalTambahBerita" @if ($errors->any() && ! old('_method')) data-buka-otomatis @endif>
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-berita.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-head"><h3>Tambah Berita</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Judul Berita *</label><input name="judul" value="{{ old('judul') }}" required></div>
                <div class="form-group"><label>Jenis Berita *</label>
                  <select name="jenis" required>
                    @foreach ($daftarJenis as $kode => $label)
                      <option value="{{ $kode }}" @selected(old('jenis', $jenis ?? \App\Models\Berita::JENIS_BERITA) === $kode)>{{ $label }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group"><label>Kategori</label><input name="kategori" list="saranKategoriBerita" value="{{ old('kategori') }}" placeholder="Contoh: Akademik, Seminar & Workshop"></div>
                <div class="form-group" data-tampil-jika="jenis=kegiatan_mahasiswa"><label>Lokasi Kegiatan</label><input name="lokasi" maxlength="150" value="{{ old('lokasi') }}" placeholder="Contoh: Aula Politala"></div>
                <div class="form-group" data-tampil-jika="jenis=kegiatan_mahasiswa"><label>Penyelenggara</label><input name="penyelenggara" maxlength="150" value="{{ old('penyelenggara') }}" placeholder="Contoh: HIMA TI"></div>
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
                <div class="form-group full"><label>Link Media Sosial</label><input type="url" name="link_media_sosial" maxlength="500" value="{{ old('link_media_sosial') }}" placeholder="https://instagram.com/p/contoh">
                  <div class="form-hint">Opsional. Link postingan Instagram/Facebook/TikTok/YouTube yang membahas berita ini; tampil sebagai tombol di halaman berita.</div></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Modal Edit Berita --}}
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
                <div class="form-group"><label>Jenis Berita *</label>
                  <select name="jenis" required>
                    @foreach ($daftarJenis as $kode => $label)
                      <option value="{{ $kode }}">{{ $label }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group"><label>Kategori</label><input name="kategori" list="saranKategoriBerita" placeholder="Contoh: Akademik, Seminar & Workshop"></div>
                <div class="form-group" data-tampil-jika="jenis=kegiatan_mahasiswa"><label>Lokasi Kegiatan</label><input name="lokasi" maxlength="150" placeholder="Contoh: Aula Politala"></div>
                <div class="form-group" data-tampil-jika="jenis=kegiatan_mahasiswa"><label>Penyelenggara</label><input name="penyelenggara" maxlength="150" placeholder="Contoh: HIMA TI"></div>
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
                <div class="form-group full"><label>Link Media Sosial</label><input type="url" name="link_media_sosial" maxlength="500" placeholder="https://instagram.com/p/contoh">
                  <div class="form-hint">Opsional. Kosongkan bila berita tidak memiliki postingan media sosial.</div></div>
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
