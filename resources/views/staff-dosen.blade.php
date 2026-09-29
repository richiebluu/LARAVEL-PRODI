<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Dosen | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-dosen">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Data Dosen</h1>
            <div class="subtitle">Data Master dosen pengajar untuk Profil Program Studi (dosen tidak memiliki akun login).</div>
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
            <h2 style="margin:0;">Data Master Dosen <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-dosen') }}">
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari nama / NUPTK..."></div>
            </form>
            <button class="btn btn-outline" data-modal-open="modalImporDosen"><i class="fa-solid fa-file-import"></i> Impor CSV</button>
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahDosen"><i class="fa-solid fa-plus"></i> Tambah Dosen</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Foto</th><th>Dosen</th><th>NUPTK</th><th>Status</th><th>Google Scholar</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarDosen as $d)
                <tr>
                  <td><div class="table-thumb" @if ($d->foto_url) style="background-image:url('{{ $d->foto_url }}')" @endif></div></td>
                  <td><strong>{{ $d->nama }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ $d->email ?? '-' }}</div></td>
                  <td>{{ $d->nuptk }}</td>
                  <td><span class="badge {{ $d->badge_status }}">{{ $d->label_status }}</span></td>
                  <td>
                    @if ($d->google_scholar)
                      <a href="{{ $d->google_scholar }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><i class="fa-solid fa-graduation-cap"></i> Lihat</a>
                    @else
                      -
                    @endif
                  </td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Detail" data-modal-open="modalDetailDsn{{ $d->nuptk }}"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditDosen"
                      data-isi-form="formEditDosen"
                      data-action="{{ route('staff-dosen.update', $d) }}"
                      data-judul-modal="Edit Data Dosen"
                      data-nilai="{{ json_encode([
                        'nuptk' => $d->nuptk,
                        'nama' => $d->nama,
                        'pendidikan_terakhir' => $d->pendidikan_terakhir,
                        'google_scholar' => $d->google_scholar,
                        'email' => $d->email,
                        'alamat' => $d->alamat,
                        'tanggal_lahir' => optional($d->tanggal_lahir)->format('Y-m-d'),
                        'status' => $d->status,
                      ]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-dosen.destroy', $d) }}" style="display:inline;"
                          data-konfirmasi="Hapus data dosen {{ $d->nama }}?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="6"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Belum ada data dosen.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarDosen->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarDosen->currentPage() }} dari {{ $daftarDosen->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm" href="{{ $daftarDosen->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm" href="{{ $daftarDosen->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

      @foreach ($daftarDosen as $d)
        <div class="modal-overlay" id="modalDetailDsn{{ $d->nuptk }}">
          <div class="modal-box">
            <div class="modal-head"><h3>Detail Dosen</h3>
              <button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div style="display:flex; gap:18px; align-items:center; margin-bottom:20px;">
                <div class="table-thumb" style="width:70px; height:70px; @if ($d->foto_url) background-image:url('{{ $d->foto_url }}') @endif"></div>
                <div><h3 style="margin:0;">{{ $d->nama }}</h3>
                  <p style="font-size:.85rem; color:var(--grey-500);">{{ $d->pendidikan_terakhir ?? '-' }}</p></div>
              </div>
              <dl class="kv">
                <dt>NUPTK</dt><dd>{{ $d->nuptk }}</dd>
                <dt>Pendidikan Terakhir</dt><dd>{{ $d->pendidikan_terakhir ?? '-' }}</dd>
                <dt>Publikasi Google Scholar</dt><dd>
                  @if ($d->google_scholar)
                    <a href="{{ $d->google_scholar }}" target="_blank" rel="noopener" style="color:var(--blue-600); word-break:break-all;">{{ $d->google_scholar }}</a>
                  @else
                    -
                  @endif
                </dd>
                <dt>Email</dt><dd>{{ $d->email ?? '-' }}</dd>
                <dt>Alamat</dt><dd>{{ $d->alamat ?? '-' }}</dd>
                <dt>Tanggal Lahir</dt><dd>{{ optional($d->tanggal_lahir)->translatedFormat('d F Y') ?? '-' }}</dd>
                <dt>Status</dt><dd><span class="badge {{ $d->badge_status }}">{{ $d->label_status }}</span></dd>
              </dl>
            </div>
            <div class="modal-foot"><button class="btn btn-outline" data-modal-close>Tutup</button></div>
          </div>
        </div>
      @endforeach

      <div class="modal-overlay" id="modalTambahDosen">
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-dosen.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-head"><h3>Tambah Dosen</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Nama Lengkap &amp; Gelar *</label><input name="nama" value="{{ old('nama') }}" required></div>
                <div class="form-group"><label>NUPTK *</label><input name="nuptk" value="{{ old('nuptk') }}" inputmode="numeric" pattern="[0-9]+" title="NUPTK hanya angka" required></div>
                <div class="form-group full"><label>Pendidikan Terakhir</label><input name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir') }}" placeholder="Contoh: S2 Teknik Informatika"></div>
                <div class="form-group full"><label>Link Google Scholar</label><input type="url" name="google_scholar" value="{{ old('google_scholar') }}" placeholder="https://scholar.google.com/citations?user=..."></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="nama&#64;politala.ac.id">
                  <div class="form-hint">Wajib domain &#64;politala.ac.id. Ditampilkan pada profil dosen.</div></div>
                <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"></div>
                <div class="form-group full"><label>Alamat</label><input name="alamat" value="{{ old('alamat') }}"></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status" required>
                    @foreach (\App\Models\Dosen::LABEL_STATUS as $kode => $label)
                      <option value="{{ $kode }}" @selected(old('status', 'aktif') === $kode)>{{ $label }}</option>
                    @endforeach
                  </select>
                  <div class="form-hint">Pendidikan = sedang studi lanjut (mis. S3).</div>
                </div>
                <div class="form-group"><label>Foto</label><input type="file" name="foto" accept="image/*"></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <div class="modal-overlay" id="modalEditDosen">
        <div class="modal-box">
          <form method="POST" action="#" id="formEditDosen" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-head"><h3 data-modal-title>Edit Data Dosen</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Nama Lengkap &amp; Gelar *</label><input name="nama" required></div>
                <div class="form-group"><label>NUPTK *</label><input name="nuptk" inputmode="numeric" pattern="[0-9]+" title="NUPTK hanya angka" required></div>
                <div class="form-group full"><label>Pendidikan Terakhir</label><input name="pendidikan_terakhir" placeholder="Contoh: S2 Teknik Informatika"></div>
                <div class="form-group full"><label>Link Google Scholar</label><input type="url" name="google_scholar" placeholder="https://scholar.google.com/citations?user=..."></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="nama&#64;politala.ac.id">
                  <div class="form-hint">Wajib domain &#64;politala.ac.id. Ditampilkan pada profil dosen.</div></div>
                <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir"></div>
                <div class="form-group full"><label>Alamat</label><input name="alamat"></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status" required>
                    @foreach (\App\Models\Dosen::LABEL_STATUS as $kode => $label)
                      <option value="{{ $kode }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <div class="form-hint">Pendidikan = sedang studi lanjut (mis. S3).</div>
                </div>
                <div class="form-group"><label>Ganti Foto</label><input type="file" name="foto" accept="image/*"></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- REVISI 28-09-2026 tahap 2: Impor CSV Data Master Dosen. --}}
      @include('partials.impor-csv-modal', [
        'id' => 'modalImporDosen',
        'judul' => 'Impor Data Dosen (CSV)',
        'action' => route('staff-dosen.impor'),
        'template' => route('staff-dosen.template'),
        'kolom' => [
          'nuptk' => 'NUPTK (angka)',
          'nama' => 'Nama lengkap beserta gelar',
          'pendidikan_terakhir' => 'mis. S2 Teknik Informatika (opsional)',
          'email' => 'Email @'.config('auth.domain_email.dosen', 'politala.ac.id').' (opsional)',
          'google_scholar' => 'URL profil Google Scholar (opsional)',
          'alamat' => 'Alamat (opsional)',
          'tanggal_lahir' => '1980-12-31 atau 31/12/1980 (opsional)',
          'status' => 'Aktif, Pendidikan, atau Nonaktif; kosong = Aktif',
        ],
        'wajib' => ['nuptk', 'nama'],
        'kunci' => 'nuptk',
        'kunciLabel' => 'NUPTK',
        'alias' => ['nama_dosen' => 'nama', 'pendidikan' => 'pendidikan_terakhir', 'scholar' => 'google_scholar'],
        'petunjuk' => 'Jabatan struktural tidak diimpor (dikelola di Struktur Organisasi). Foto diisi lewat tombol Edit. Contoh baris: <code>1234567890,Nama Dosen M.Kom,S2 Teknik Informatika,nama@politala.ac.id,,,,Aktif</code>',
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
