<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Mahasiswa | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-mahasiswa">
  <div class="admin-shell">

  {{-- Sidebar --}}
  @include('partials.staff-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Data Mahasiswa</h1>
            <div class="subtitle">Cari, tinjau, dan kelola data mahasiswa Prodi TI.</div>
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
            <h2 style="margin:0;">Data Master Mahasiswa <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <form method="GET" action="{{ route('staff-mahasiswa') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
              <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari nama / NIM..."></div>
              <select id="filterAngkatan" name="angkatan" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
                <option value="">Semua angkatan</option>
                @foreach ($daftarAngkatan as $th)
                  <option value="{{ $th }}" @selected((string) $angkatan === (string) $th)>{{ $th }}</option>
                @endforeach
              </select>
              <label for="perPage" style="display:flex; align-items:center; gap:8px; font-size:.85rem; color:var(--grey-500); font-weight:500;">
                Tampilkan
                <select id="perPage" name="per_page" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
                  @foreach ($pilihanPerHalaman as $n)
                    <option value="{{ $n }}" @selected($perHalaman === $n)>{{ $n }}</option>
                  @endforeach
                </select>
                data
              </label>
            </form>
            <button class="btn btn-outline" data-modal-open="modalImporMahasiswa"><i class="fa-solid fa-file-import"></i> Impor CSV</button>
            {{-- Tombol Tambah --}}
            <button class="btn btn-primary" id="btnTambah" data-modal-open="modalTambahMahasiswa"><i class="fa-solid fa-plus"></i> Tambah Mahasiswa</button>
          </div>
        </div>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>No</th><th>Foto</th><th>NIM</th><th>Nama</th><th>Angkatan</th><th>Kelas</th><th>IPK</th><th>Organisasi</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarMahasiswa as $m)
                @php
                  $isiOrganisasi = [];
                  for ($i = 0; $i < $maksOrganisasi; $i++) {
                    $o = $m->organisasi->values()->get($i);
                    $isiOrganisasi['organisasi['.$i.'][nama_organisasi]'] = $o?->nama_organisasi;
                    $isiOrganisasi['organisasi['.$i.'][jabatan]'] = $o?->jabatan;
                  }
                @endphp
                <tr>
                  <td>{{ $daftarMahasiswa->firstItem() + $loop->index }}</td>
                  <td><div class="table-thumb" @if ($m->foto_url) style="background-image:url('{{ $m->foto_url }}')" @endif></div></td>
                  <td>{{ $m->nim }}</td>
                  <td><strong>{{ $m->nama }}</strong></td>
                  <td>{{ $m->angkatan ?? '-' }}</td>
                  <td>{{ $m->kelas ?? '-' }}</td>
                  <td>{{ $m->ipk !== null ? number_format($m->ipk, 2) : '-' }}</td>
                  <td>
                    @forelse ($m->organisasi as $o)
                      <div style="font-size:.82rem;">{{ $o->nama_organisasi }} <span style="color:var(--grey-500);">&middot; {{ $o->jabatan }}</span></div>
                    @empty
                      -
                    @endforelse
                  </td>
                  <td><span class="badge {{ $m->status_mahasiswa === 'aktif' ? 'badge-green' : (in_array($m->status_mahasiswa, ['do', 'nonaktif'], true) ? 'badge-red' : 'badge-grey') }}">{{ $m->label_status }}</span></td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Detail" data-modal-open="modalDetailMhs{{ $m->nim }}"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-outline btn-sm btn-icon" title="Edit"
                      data-modal-open="modalEditMahasiswa"
                      data-isi-form="formEditMahasiswa"
                      data-action="{{ route('staff-mahasiswa.update', $m) }}"
                      data-judul-modal="Edit Data Mahasiswa"
                      data-nilai="{{ json_encode([
                        'nim' => $m->nim,
                        'nama' => $m->nama,
                        'angkatan' => $m->angkatan,
                        'kelas' => $m->kelas,
                        'ipk' => $m->ipk,
                        'email' => $m->user?->email ?? $m->email,
                        'no_hp' => $m->no_hp,
                        'status_mahasiswa' => $m->status_mahasiswa,
                      ] + $isiOrganisasi) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('staff-mahasiswa.destroy', $m) }}" style="display:inline;"
                          data-konfirmasi="Hapus data mahasiswa {{ $m->nama }} ({{ $m->nim }})?" data-konfirmasi-judul="Hapus Mahasiswa?" data-konfirmasi-catatan="Akun login, prestasi, dan organisasi mahasiswa ini ikut terhapus dan tidak dapat dikembalikan.">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="10"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Belum ada data mahasiswa.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @php
          $halAwal = max(1, $daftarMahasiswa->currentPage() - 2);
          $halAkhir = min($daftarMahasiswa->lastPage(), $daftarMahasiswa->currentPage() + 2);
        @endphp
        <div class="toolbar" style="margin:18px 0 0 0;">
          <span class="form-hint" id="infoHal">
            @if ($daftarMahasiswa->total())
              Menampilkan {{ $daftarMahasiswa->firstItem() }}–{{ $daftarMahasiswa->lastItem() }} dari {{ $daftarMahasiswa->total() }} data
              &middot; Halaman {{ $daftarMahasiswa->currentPage() }} dari {{ $daftarMahasiswa->lastPage() }}
            @else
              Halaman 1 dari 1
            @endif
          </span>
          <nav class="pager" aria-label="Navigasi halaman data mahasiswa" style="display:flex; gap:8px; flex-wrap:wrap;">
            <a class="btn btn-outline btn-sm {{ $daftarMahasiswa->onFirstPage() ? 'disabled' : '' }}"
               href="{{ $daftarMahasiswa->previousPageUrl() ?? '#' }}" @if ($daftarMahasiswa->onFirstPage()) aria-disabled="true" @endif><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
            @if ($daftarMahasiswa->lastPage() > 1)
              @foreach ($daftarMahasiswa->getUrlRange($halAwal, $halAkhir) as $hal => $urlHal)
                <a class="btn btn-sm {{ $hal === $daftarMahasiswa->currentPage() ? 'btn-primary' : 'btn-outline' }}"
                   href="{{ $urlHal }}" @if ($hal === $daftarMahasiswa->currentPage()) aria-current="page" @endif>{{ $hal }}</a>
              @endforeach
            @endif
            <a class="btn btn-outline btn-sm {{ ! $daftarMahasiswa->hasMorePages() ? 'disabled' : '' }}"
               href="{{ $daftarMahasiswa->nextPageUrl() ?? '#' }}" @if (! $daftarMahasiswa->hasMorePages()) aria-disabled="true" @endif>Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
          </nav>
        </div>
      </div>

      {{-- Modal Detail Mahasiswa --}}
      @foreach ($daftarMahasiswa as $m)
        {{-- Modal Detail Mahasiswa --}}
        <div class="modal-overlay" id="modalDetailMhs{{ $m->nim }}">
          <div class="modal-box">
            <div class="modal-head"><h3>Detail Mahasiswa</h3>
              <button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div style="display:flex; gap:18px; align-items:center; margin-bottom:20px;">
                <div class="table-thumb" style="width:70px; height:70px; @if ($m->foto_url) background-image:url('{{ $m->foto_url }}') @endif"></div>
                <div>
                  <h3 style="margin:0;">{{ $m->nama }}</h3>
                  <p style="font-size:.85rem; color:var(--grey-500);">{{ $m->nim }} &middot; {{ $m->kelas ?? '-' }}</p>
                </div>
              </div>
              <dl class="kv">
                <dt>Angkatan</dt><dd>{{ $m->angkatan ?? '-' }}</dd>
                <dt>IPK</dt><dd>{{ $m->ipk !== null ? number_format($m->ipk, 2) : '-' }}</dd>
                <dt>Email</dt><dd>{{ $m->user?->email ?? $m->email ?? '-' }}</dd>
                <dt>Nomor Telepon</dt><dd>{{ $m->no_hp ?? '-' }}</dd>
                <dt>Status</dt><dd>{{ $m->label_status }}</dd>
              </dl>
              <p style="margin-top:18px; font-size:.85rem; color:var(--grey-500);">
                Jumlah prestasi: {{ $m->prestasi_count }} ({{ $m->prestasi_disetujui_count }} disetujui)
              </p>

              @php $nilai = $ranking->nilaiKriteria($m); @endphp
              <h4 style="margin:18px 0 10px 0;">Nilai Kriteria Penilaian</h4>
              <dl class="kv">
                <dt>Nilai Akademik (IPK)</dt><dd>{{ number_format($nilai['c1'], 2) }}</dd>
                <dt>Prestasi Akademik</dt><dd>{{ number_format($nilai['c2'], 0) }} poin</dd>
                <dt>Prestasi Non-Akademik</dt><dd>{{ number_format($nilai['c3'], 0) }} poin</dd>
                <dt>Keaktifan Organisasi</dt><dd>{{ number_format($nilai['c4'], 0) }} poin</dd>
              </dl>

              <h4 style="margin:18px 0 10px 0;">Organisasi</h4>
              @forelse ($m->organisasi as $o)
                <p style="font-size:.85rem; margin:0 0 6px 0;"><strong>{{ $o->nama_organisasi }}</strong> &middot; {{ $o->jabatan }} ({{ $o->poin }} poin)</p>
              @empty
                <p style="font-size:.85rem; color:var(--grey-500);">Belum ada data organisasi.</p>
              @endforelse
            </div>
            <div class="modal-foot"><button class="btn btn-outline" data-modal-close>Tutup</button></div>
          </div>
        </div>
      @endforeach

      {{-- Modal Tambah Mahasiswa --}}
      <div class="modal-overlay" id="modalTambahMahasiswa">
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-mahasiswa.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-head"><h3>Tambah Mahasiswa</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Nama Lengkap *</label><input name="nama" value="{{ old('nama') }}" required></div>
                <div class="form-group"><label>NIM *</label><input name="nim" value="{{ old('nim') }}" required></div>
                <div class="form-group"><label>Angkatan</label><input type="number" name="angkatan" value="{{ old('angkatan', date('Y')) }}"></div>
                <div class="form-group"><label>Kelas</label><input name="kelas" value="{{ old('kelas') }}"></div>
                <div class="form-group"><label>IPK (Nilai Akademik)</label><input type="number" step="0.01" min="0" max="4" name="ipk" value="{{ old('ipk') }}"></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" value="{{ old('email') }}" placeholder="NIM&#64;{{ \App\Models\User::domainEmail('mahasiswa') }}" required>
                  <div class="form-hint">Dipakai untuk login dan ditampilkan di profil.</div></div>
                <div class="form-group"><label>Nomor Telepon</label><input name="no_hp" inputmode="numeric" pattern="[0-9]{10,15}" title="Nomor telepon hanya angka (10-15 digit)" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}"></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status_mahasiswa" required>
                    @foreach (\App\Models\Mahasiswa::LABEL_STATUS as $kodeStatus => $labelStatus)
                      <option value="{{ $kodeStatus }}" @selected(old('status_mahasiswa', 'aktif') === $kodeStatus)>{{ $labelStatus }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group full"><label>Foto</label><input type="file" name="foto" accept="image/*">
                  <div class="form-hint">JPG/PNG/WEBP, maksimal 2 MB.</div></div>
                <div class="form-group full"><label>Keaktifan Organisasi</label>
                  <div class="form-hint" style="margin-top:0;">Isi nama organisasi dan jabatan; kosongkan baris yang tidak dipakai. Poin dihitung dari jabatan.</div></div>
                @for ($i = 0; $i < $maksOrganisasi; $i++)
                  <div class="form-group"><label>Nama Organisasi {{ $i + 1 }}</label>
                    <input name="organisasi[{{ $i }}][nama_organisasi]" value="{{ old('organisasi.'.$i.'.nama_organisasi') }}" placeholder="Contoh: HIMA TI"></div>
                  <div class="form-group"><label>Jabatan Organisasi {{ $i + 1 }}</label>
                    <select name="organisasi[{{ $i }}][jabatan]">
                      <option value="">-- Tidak ada --</option>
                      @foreach ($daftarJabatan as $jabatan => $poin)
                        <option value="{{ $jabatan }}" @selected(old('organisasi.'.$i.'.jabatan') === $jabatan)>{{ $jabatan }} ({{ $poin }} poin)</option>
                      @endforeach
                    </select></div>
                @endfor
                <div class="form-group"><label>Password *</label><input type="password" name="password" required>
                  <div class="form-hint">Minimal 8 karakter.</div></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Modal Edit Mahasiswa --}}
      <div class="modal-overlay" id="modalEditMahasiswa">
        <div class="modal-box">
          <form method="POST" action="#" id="formEditMahasiswa" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-head"><h3 data-modal-title>Edit Data Mahasiswa</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Nama Lengkap *</label><input name="nama" required></div>
                <div class="form-group"><label>NIM *</label><input name="nim" required></div>
                <div class="form-group"><label>Angkatan</label><input type="number" name="angkatan"></div>
                <div class="form-group"><label>Kelas</label><input name="kelas"></div>
                <div class="form-group"><label>IPK (Nilai Akademik)</label><input type="number" step="0.01" min="0" max="4" name="ipk"></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" placeholder="NIM&#64;{{ \App\Models\User::domainEmail('mahasiswa') }}" required>
                  <div class="form-hint">Dipakai untuk login dan ditampilkan di profil.</div></div>
                <div class="form-group"><label>Nomor Telepon</label><input name="no_hp" inputmode="numeric" pattern="[0-9]{10,15}" title="Nomor telepon hanya angka (10-15 digit)" placeholder="08xxxxxxxxxx"></div>
                <div class="form-group"><label>Status *</label>
                  <select name="status_mahasiswa" required>
                    @foreach (\App\Models\Mahasiswa::LABEL_STATUS as $kodeStatus => $labelStatus)
                      <option value="{{ $kodeStatus }}">{{ $labelStatus }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group full"><label>Ganti Foto</label><input type="file" name="foto" accept="image/*">
                  <div class="form-hint">Kosongkan bila tidak ingin mengganti foto.</div></div>
                <div class="form-group full"><label>Keaktifan Organisasi</label>
                  <div class="form-hint" style="margin-top:0;">Isi nama organisasi dan jabatan; kosongkan baris yang tidak dipakai. Poin dihitung dari jabatan.</div></div>
                @for ($i = 0; $i < $maksOrganisasi; $i++)
                  <div class="form-group"><label>Nama Organisasi {{ $i + 1 }}</label>
                    <input name="organisasi[{{ $i }}][nama_organisasi]" placeholder="Contoh: HIMA TI"></div>
                  <div class="form-group"><label>Jabatan Organisasi {{ $i + 1 }}</label>
                    <select name="organisasi[{{ $i }}][jabatan]">
                      <option value="">-- Tidak ada --</option>
                      @foreach ($daftarJabatan as $jabatan => $poin)
                        <option value="{{ $jabatan }}" >{{ $jabatan }} ({{ $poin }} poin)</option>
                      @endforeach
                    </select></div>
                @endfor
                <div class="form-group"><label>Password Baru</label><input type="password" name="password">
                  <div class="form-hint">Kosongkan bila tidak ingin mengganti password.</div></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Modal Impor CSV --}}
      @include('partials.impor-csv-modal', [
        'id' => 'modalImporMahasiswa',
        'judul' => 'Impor Data Mahasiswa (CSV)',
        'action' => route('staff-mahasiswa.impor'),
        'template' => route('staff-mahasiswa.template'),
        'kolom' => [
          'nim' => 'NIM (huruf/angka tanpa spasi)',
          'nama' => 'Nama lengkap mahasiswa',
          'email' => 'Email @'.config('auth.domain_email.mahasiswa', 'mhs.politala.ac.id').' (dipakai untuk login)',
          'angkatan' => 'Tahun angkatan, mis. 2023',
          'kelas' => 'Kelas, mis. TI-3B',
          'no_hp' => 'Nomor telepon, 10-15 digit angka',
          'ipk' => 'IPK 0.00 - 4.00 (koma atau titik)',
          'status_mahasiswa' => 'Aktif, Alumni, Cuti, Nonaktif, DO, DISPEN; kosong = Aktif',
          'password' => 'Password awal min. 8 karakter; kosong = NIM',
        ],
        'wajib' => ['nim', 'nama', 'email'],
        'kunci' => 'nim',
        'kunciLabel' => 'NIM',
        'alias' => ['nama_mahasiswa' => 'nama', 'nomor_hp' => 'no_hp', 'no_telepon' => 'no_hp', 'telepon' => 'no_hp', 'tahun_angkatan' => 'angkatan'],
        'petunjuk' => 'Akun login mahasiswa dibuat otomatis. Bila kolom <strong>password</strong> kosong, password awal = NIM (mahasiswa juga dapat masuk dengan Google). Foto dan Keaktifan Organisasi diisi lewat tombol Edit. Contoh baris: <code>2301301001,Nama Mahasiswa,2301301001@mhs.politala.ac.id,2023,TI-3B,081234567890,3.75,Aktif,</code>',
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
