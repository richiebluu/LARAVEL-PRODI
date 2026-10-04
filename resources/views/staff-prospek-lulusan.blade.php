<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prospek Lulusan | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-prospek-lulusan">
  <div class="admin-shell">

  {{-- Sidebar --}}
  @include('partials.staff-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Prospek Lulusan</h1>
            <div class="subtitle">Bidang karier lulusan yang tampil pada Profil &gt; Prospek Lulusan di website publik.</div>
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
            <h2 style="margin:0;">Data Prospek Lulusan <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-prospek-lulusan') }}">
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari prospek lulusan..."></div>
            </form>
            <a href="{{ route('prospek-lulusan') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
            <button class="btn btn-outline" data-modal-open="modalImporProspek"><i class="fa-solid fa-file-import"></i> Impor CSV</button>
            {{-- Tombol Tambah --}}
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahProspek"><i class="fa-solid fa-plus"></i> Tambah Prospek Lulusan</button>
          </div>
        </div>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>No</th><th>Ikon</th><th>Prospek Lulusan</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarProspek as $p)
                <tr>
                  <td>{{ $daftarProspek->firstItem() + $loop->index }}</td>
                  <td><div class="table-thumb" title="{{ $p->nama_ikon }}" style="display:flex; align-items:center; justify-content:center; background:rgba(23,105,170,.08); color:var(--blue-600); font-size:1.15rem;"><i class="{{ $p->kelas_ikon }}"></i></div></td>
                  <td><strong>{{ $p->nama }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ \Illuminate\Support\Str::limit($p->deskripsi ?? '-', 80) }}</div></td>
                  <td><span class="badge {{ $p->status === 'aktif' ? 'badge-green' : 'badge-grey' }}">{{ $p->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Detail" data-modal-open="modalDetailProspek{{ $p->id_prospek_lulusan }}"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditProspek"
                      data-isi-form="formEditProspek"
                      data-action="{{ route('staff-prospek-lulusan.update', $p) }}"
                      data-judul-modal="Edit Prospek Lulusan"
                      data-nilai="{{ json_encode(['nama' => $p->nama, 'ikon' => $p->ikon ?: 'fa-briefcase', 'deskripsi' => $p->deskripsi, 'status' => $p->status]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-prospek-lulusan.destroy', $p) }}" style="display:inline;" data-konfirmasi="Hapus prospek lulusan {{ $p->nama }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="5"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>{{ $cari ? 'Tidak ada prospek lulusan yang cocok dengan pencarian.' : 'Belum ada data prospek lulusan.' }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarProspek->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarProspek->currentPage() }} dari {{ $daftarProspek->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm" href="{{ $daftarProspek->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm" href="{{ $daftarProspek->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

      @foreach ($daftarProspek as $p)
        {{-- Modal Detail Prospek Lulusan --}}
        <div class="modal-overlay" id="modalDetailProspek{{ $p->id_prospek_lulusan }}">
          <div class="modal-box">
            <div class="modal-head"><h3>Detail Prospek Lulusan</h3>
              <button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div style="display:flex; gap:18px; align-items:center; margin-bottom:20px;">
                <div class="table-thumb" style="width:70px; height:70px; display:flex; align-items:center; justify-content:center; background:rgba(23,105,170,.08); color:var(--blue-600); font-size:1.8rem;"><i class="{{ $p->kelas_ikon }}"></i></div>
                <div><h3 style="margin:0;">{{ $p->nama }}</h3>
                  <p style="font-size:.85rem; color:var(--grey-500);">Prospek Lulusan</p></div>
              </div>
              <dl class="kv">
                <dt>Ikon</dt><dd><i class="{{ $p->kelas_ikon }}" style="color:var(--blue-600);"></i> {{ $p->nama_ikon }}</dd>
                <dt>Deskripsi Singkat</dt><dd>{{ $p->deskripsi ?? '-' }}</dd>
                <dt>Status</dt><dd><span class="badge {{ $p->status === 'aktif' ? 'badge-green' : 'badge-grey' }}">{{ $p->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></dd>
              </dl>
            </div>
            <div class="modal-foot"><button class="btn btn-outline" data-modal-close>Tutup</button></div>
          </div>
        </div>
      @endforeach

@foreach (['tambah' => null, 'edit' => 1] as $mode => $edit)
      {{-- Modal Edit --}}
      <div class="modal-overlay" id="{{ $edit ? 'modalEditProspek' : 'modalTambahProspek' }}" @if (! $edit && $errors->any() && ! old('_method') && ! session('impor_gagal')) data-buka-otomatis @endif>
        <div class="modal-box">
          <form method="POST" action="{{ $edit ? '#' : route('staff-prospek-lulusan.store') }}" @if ($edit) id="formEditProspek" @endif>
            @csrf
            @if ($edit) @method('PUT') @endif
            <div class="modal-head"><h3 data-modal-title>{{ $edit ? 'Edit Prospek Lulusan' : 'Tambah Prospek Lulusan' }}</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Nama Prospek Lulusan *</label><input name="nama" maxlength="150" value="{{ $edit ? '' : old('nama') }}" placeholder="Contoh: Web Developer" autocomplete="off" required></div>
                <div class="form-group full" data-pilih-ikon><label>Ikon *</label>
                  <input type="hidden" name="ikon" value="{{ $edit ? '' : old('ikon', 'fa-briefcase') }}" data-pratinjau-ikon>
                  <div class="ikon-terpilih">
                    <div class="table-thumb" data-ikon-preview style="display:flex; align-items:center; justify-content:center; background:rgba(23,105,170,.08); color:var(--blue-600); font-size:1.15rem;"><i class="fa-solid fa-briefcase"></i></div>
                    <div>
                      <strong data-ikon-nama>{{ $daftarIkon['fa-briefcase'] }}</strong>
                      <div class="form-hint" style="margin-top:2px;" data-ikon-deskripsi>{{ $deskripsiIkon['fa-briefcase'] }}</div>
                    </div>
                  </div>
                  <div class="ikon-saran" data-ikon-saran>
                    @foreach ($daftarIkon as $kelas => $label)
                      <button type="button" class="btn btn-outline btn-sm btn-icon" data-ikon="{{ $kelas }}" data-ikon-label="{{ $label }}" data-ikon-info="{{ $deskripsiIkon[$kelas] ?? '' }}" title="{{ $label }}" aria-label="{{ $label }}"><i class="fa-solid {{ $kelas }}"></i></button>
                    @endforeach
                  </div>
                  <div class="form-hint">Klik salah satu ikon. Nama dan penjelasan penggunaannya tampil di atas.</div></div>
                <div class="form-group full"><label>Deskripsi Singkat</label><textarea name="deskripsi" rows="3" maxlength="1000">{{ $edit ? '' : old('deskripsi') }}</textarea></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status" required>
                    <option value="aktif" @if (! $edit) @selected(old('status', 'aktif') === 'aktif') @endif>Aktif (tampil)</option>
                    <option value="nonaktif" @if (! $edit) @selected(old('status') === 'nonaktif') @endif>Nonaktif</option>
                  </select></div>
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

      {{-- Modal Impor CSV --}}
      @include('partials.impor-csv-modal', [
        'id' => 'modalImporProspek',
        'judul' => 'Impor Prospek Lulusan (CSV)',
        'action' => route('staff-prospek-lulusan.impor'),
        'template' => route('staff-prospek-lulusan.template'),
        'kolom' => [
          'nama' => 'Nama Prospek Lulusan, mis. Web Developer',
          'ikon' => 'Nama ikon, mis. Keamanan Siber atau Pengembangan Web (lihat pilihan ikon di form Tambah); kosong = Umum / Karier',
          'deskripsi' => 'Deskripsi singkat (opsional)',
          'status' => 'Aktif atau Nonaktif; kosong = Aktif',
        ],
        'wajib' => ['nama'],
        'kunci' => 'nama',
        'kunciLabel' => 'nama prospek',
        'alias' => ['nama_prospek' => 'nama', 'prospek_lulusan' => 'nama', 'nama_prospek_lulusan' => 'nama', 'ikon_kategori' => 'ikon'],
        'petunjuk' => 'Contoh baris: <code>Web Developer,Pengembangan Web,Membangun aplikasi web,Aktif</code>.',
      ])

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
