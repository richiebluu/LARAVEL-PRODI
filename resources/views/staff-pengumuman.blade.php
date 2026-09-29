<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengumuman | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-pengumuman">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Pengumuman</h1>
            <div class="subtitle">Pengumuman pribadi untuk mahasiswa berprestasi, dikirim ke email (Gmail) dan dashboard mahasiswa.</div>
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

      <div class="panel">
        <div class="toolbar">
          <div>
            <h2 style="margin:0;">Daftar Pengumuman <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-pengumuman') }}">
              <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari judul..."></div>
                <select name="kategori" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
                  <option value="">Semua kategori prestasi</option>
                  @foreach (\App\Models\Pengumuman::daftarKategori() as $k)
                    <option value="{{ $k }}" @selected($kategori === $k)>{{ $k }}</option>
                  @endforeach
                </select>
              </div>
            </form>
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahPengumuman"><i class="fa-solid fa-plus"></i> Buat Pengumuman</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Judul</th><th>Kategori Prestasi</th><th>Tanggal</th><th>Mahasiswa Berprestasi</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarPengumuman as $g)
                @php $penerima = $g->mahasiswa; @endphp
                <tr>
                  <td><strong>{{ $g->judul }}</strong>
                    <div style="font-size:.78rem; color:var(--grey-500);">{{ Str::limit($g->isi, 60) }}</div></td>
                  <td><span class="badge badge-blue">{{ $g->label_kategori }}</span></td>
                  <td>{{ optional($g->tanggal_dikirim ?? $g->created_at)->translatedFormat('d F Y') }}</td>
                  <td>{{ $penerima ? $penerima->nama.' - '.$penerima->nim : '-' }}</td>
                  <td><span class="badge {{ $g->status === 'terkirim' ? 'badge-green' : 'badge-grey' }}">{{ $g->status === 'terkirim' ? 'Terkirim' : 'Draft' }}</span></td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditPengumuman"
                      data-isi-form="formEditPengumuman"
                      data-action="{{ route('staff-pengumuman.update', $g) }}"
                      data-nilai="{{ json_encode([
                        'nim' => $g->nim,
                        'prestasi_id' => $g->prestasi_id,
                        'kategori' => $g->kategori,
                        'judul' => $g->judul,
                        'isi' => $g->isi,
                        'status' => $g->status,
                      ]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-pengumuman.destroy', $g) }}" style="display:inline;"
                          data-konfirmasi="Hapus pengumuman &quot;{{ $g->judul }}&quot;?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="6"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Belum ada pengumuman.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <p class="form-hint" style="margin-top:14px;">
          Pengumuman ditujukan kepada <strong>mahasiswa berprestasi</strong> (memiliki prestasi yang sudah disetujui), bukan berdasarkan ranking.
          Pengumuman berstatus <strong>terkirim</strong> otomatis tampil pada dashboard mahasiswa yang dipilih
          (kolom <code>nim</code> pada tabel <code>pengumuman</code>) dan membuat notifikasi pribadi (kolom <code>notifikasi</code>).
          Pengumuman tidak pernah tampil di halaman publik.
        </p>
      </div>

      <div class="modal-overlay" id="modalTambahPengumuman">
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-pengumuman.store') }}">
            @csrf
            <div class="modal-head"><h3>Buat Pengumuman</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              @include('partials.pengumuman-form', ['daftarMahasiswa' => $daftarMahasiswa, 'daftarPrestasi' => $daftarPrestasi])
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <div class="modal-overlay" id="modalEditPengumuman">
        <div class="modal-box">
          <form method="POST" action="#" id="formEditPengumuman">
            @csrf
            @method('PUT')
            <div class="modal-head"><h3 data-modal-title>Edit Pengumuman</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              @include('partials.pengumuman-form', ['daftarMahasiswa' => $daftarMahasiswa, 'daftarPrestasi' => $daftarPrestasi])
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
  @if (session('email_gagal'))
    <div data-flash="{{ session('email_gagal') }}" data-flash-tipe="bad" hidden></div>
  @endif
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
