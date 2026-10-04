<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mata Kuliah | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-mata-kuliah">
  <div class="admin-shell">

  {{-- Sidebar --}}
  @include('partials.staff-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Mata Kuliah</h1>
            <div class="subtitle">Daftar mata kuliah Program Studi, disesuaikan dengan data SIPADU.</div>
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
            <h2 style="margin:0;">Data Mata Kuliah <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data &middot; total {{ $totalSks }} SKS)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-mata-kuliah') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
              <select name="semester" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
                <option value="">Semua semester</option>
                @for ($i = 1; $i <= $semesterMaks; $i++)
                  <option value="{{ $i }}" @selected($semester === $i)>Semester {{ $i }}</option>
                @endfor
              </select>
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari kode / nama MK..."></div>
            </form>
            <a href="{{ route('mata-kuliah') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a>
            <button class="btn btn-outline" data-modal-open="modalImporMataKuliah"><i class="fa-solid fa-file-import"></i> Impor CSV</button>
            {{-- Tombol Tambah --}}
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahMk"><i class="fa-solid fa-plus"></i> Tambah Mata Kuliah</button>
          </div>
        </div>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>Semester</th><th>Kode MK</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Jenis</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarMataKuliah as $mk)
                <tr>
                  <td>{{ $mk->semester }}</td>
                  <td><strong>{{ $mk->kode_mata_kuliah }}</strong></td>
                  <td>{{ $mk->nama }}</td>
                  <td>{{ $mk->sks }}</td>
                  <td><span class="badge {{ $mk->jenis === 'Wajib' ? 'badge-blue' : 'badge-grey' }}">{{ $mk->jenis }}</span></td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditMk"
                      data-isi-form="formEditMk"
                      data-action="{{ route('staff-mata-kuliah.update', $mk) }}"
                      data-judul-modal="Edit Mata Kuliah"
                      data-nilai="{{ json_encode(['kode' => $mk->kode_mata_kuliah, 'nama' => $mk->nama, 'semester' => $mk->semester, 'sks' => $mk->sks, 'jenis' => $mk->jenis]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-mata-kuliah.destroy', $mk) }}" style="display:inline;" data-konfirmasi="Hapus mata kuliah {{ $mk->kode_mata_kuliah }} - {{ $mk->nama }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="6"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>{{ $cari || $semester ? 'Tidak ada mata kuliah yang cocok dengan filter.' : 'Belum ada data mata kuliah. Tambahkan satu per satu atau impor CSV dari data SIPADU.' }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarMataKuliah->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarMataKuliah->currentPage() }} dari {{ $daftarMataKuliah->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm" href="{{ $daftarMataKuliah->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm" href="{{ $daftarMataKuliah->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

@foreach (['tambah' => null, 'edit' => 1] as $mode => $edit)
      {{-- Modal Edit --}}
      <div class="modal-overlay" id="{{ $edit ? 'modalEditMk' : 'modalTambahMk' }}" @if (! $edit && $errors->any() && old('kode') !== null && ! old('_method') && ! session('impor_gagal')) data-buka-otomatis @endif>
        <div class="modal-box">
          <form method="POST" action="{{ $edit ? '#' : route('staff-mata-kuliah.store') }}" @if ($edit) id="formEditMk" @endif>
            @csrf
            @if ($edit) @method('PUT') @endif
            <div class="modal-head"><h3 data-modal-title>{{ $edit ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' }}</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group"><label>Kode Mata Kuliah *</label><input name="kode" maxlength="20" value="{{ $edit ? '' : old('kode') }}" placeholder="Sesuai SIPADU, contoh: TI101" pattern="[A-Za-z0-9.\-]+" title="Huruf, angka, titik, atau tanda hubung" required></div>
                <div class="form-group"><label>Jenis Mata Kuliah *</label>
                  <select name="jenis" required>
                    @foreach ($daftarJenis as $j)
                      <option value="{{ $j }}" @if (! $edit) @selected(old('jenis', 'Wajib') === $j) @endif>{{ $j }}</option>
                    @endforeach
                  </select></div>
                <div class="form-group full"><label>Nama Mata Kuliah *</label><input name="nama" maxlength="150" value="{{ $edit ? '' : old('nama') }}" required></div>
                <div class="form-group"><label>Semester *</label>
                  <select name="semester" required>
                    @for ($i = 1; $i <= $semesterMaks; $i++)
                      <option value="{{ $i }}" @if (! $edit) @selected((int) old('semester', 1) === $i) @endif>Semester {{ $i }}</option>
                    @endfor
                  </select></div>
                <div class="form-group"><label>SKS *</label><input type="number" name="sks" min="1" max="24" value="{{ $edit ? '' : old('sks') }}" required></div>
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
        'id' => 'modalImporMataKuliah',
        'judul' => 'Impor Mata Kuliah (CSV)',
        'action' => route('staff-mata-kuliah.impor'),
        'template' => route('staff-mata-kuliah.template'),
        'kolom' => [
          'kode' => 'Kode mata kuliah sesuai SIPADU, mis. TI101',
          'nama' => 'Nama mata kuliah',
          'semester' => 'Angka 1 - '.$semesterMaks,
          'sks' => 'Jumlah SKS (angka)',
          'jenis' => 'Wajib atau Pilihan',
        ],
        'wajib' => ['kode', 'nama', 'semester', 'sks', 'jenis'],
        'kunci' => 'kode',
        'kunciLabel' => 'kode mata kuliah',
        'alias' => ['kode_mk' => 'kode', 'kode_matakuliah' => 'kode', 'kode_mata_kuliah' => 'kode', 'nama_mk' => 'nama', 'nama_matakuliah' => 'nama', 'nama_mata_kuliah' => 'nama', 'jenis_mk' => 'jenis', 'jenis_mata_kuliah' => 'jenis', 'smt' => 'semester'],
        'petunjuk' => 'Salin data mata kuliah dari SIPADU ke Excel sesuai kolom di bawah, lalu <em>Simpan Sebagai &gt; CSV</em>. Contoh baris: <code>TI101,Algoritma dan Pemrograman,1,3,Wajib</code>.',
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
