<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya | Dashboard Mahasiswa</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="mahasiswa-profile">
  <div class="admin-shell">

  {{-- Sidebar --}}
  @include('partials.mahasiswa-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Profil Saya</h1>
            <div class="subtitle">Perbarui data diri dan Keaktifan Organisasi Anda.</div>
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
        <div class="alert-error"><strong><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</strong></div>
      @endif

      <div class="profile-head">
        <div class="p-photo" id="pFoto" @if ($mahasiswa?->foto_url) style="background-image:url('{{ $mahasiswa->foto_url }}')" @endif></div>
        <div>
          <h2 id="pNama">{{ $mahasiswa->nama ?? auth()->user()->name }}</h2>
          <p id="pMeta">{{ $mahasiswa ? $mahasiswa->nim.' · '.($mahasiswa->kelas ?? '-') : '-' }}</p>
        </div>
        <div class="spacer"></div>
        <button class="btn btn-light" id="btnEdit" data-modal-open="modalEditProfil"><i class="fa-solid fa-pen"></i> Edit Profil</button>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Data Mahasiswa</h2>
          <span id="pStatus"><span class="badge badge-green">{{ $mahasiswa?->label_status ?? '-' }}</span></span></div>
        <dl class="kv">
          <div><dt>NIM</dt><dd id="pNim">{{ $mahasiswa->nim ?? '-' }}</dd></div>
          <div><dt>Angkatan</dt><dd id="pAngkatan">{{ $mahasiswa->angkatan ?? '-' }}</dd></div>
          <div><dt>Kelas</dt><dd id="pKelas">{{ $mahasiswa->kelas ?? '-' }}</dd></div>
          <div><dt>Nilai Akademik (IPK)</dt><dd id="pIpk">{{ $mahasiswa?->ipk !== null ? number_format($mahasiswa->ipk, 2) : '-' }}</dd></div>
          <div><dt>Email</dt><dd id="pEmail">{{ auth()->user()->email ?? $mahasiswa->email ?? '-' }}</dd></div>
          <div><dt>Nomor Telepon</dt><dd id="pTelp">{{ $mahasiswa->no_hp ?? '-' }}</dd></div>
        </dl>
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Keaktifan Organisasi</h2>
          @if ($mahasiswa)
            <button class="btn btn-outline btn-sm" data-modal-open="modalOrganisasi"><i class="fa-solid fa-plus"></i> Tambah Keaktifan Organisasi</button>
          @endif
        </div>
        <div class="table-wrap">
          {{-- Tabel --}}
          <table class="data-table">
            <thead><tr><th>No</th><th>Nama Organisasi</th><th>Jabatan</th><th>Poin Jabatan</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody>
              @forelse ($daftarOrganisasi as $o)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $o->nama_organisasi }}</td><td>{{ $o->jabatan }}</td><td>{{ $o->poin }} poin</td>
                  <td class="actions" style="justify-content:flex-end;">
                    <button type="button" class="btn btn-outline btn-sm btn-icon" title="Edit" aria-label="Edit {{ $o->nama_organisasi }}"
                      data-modal-open="modalEditOrganisasi"
                      data-isi-form="formEditOrganisasi"
                      data-action="{{ route('mahasiswa-profile.organisasi.update', $o) }}"
                      data-nilai="{{ json_encode(['nama_organisasi' => $o->nama_organisasi, 'jabatan' => $o->jabatan]) }}"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('mahasiswa-profile.organisasi.destroy', $o) }}" style="display:inline;"
                          data-konfirmasi="Hapus organisasi {{ $o->nama_organisasi }} ({{ $o->jabatan }})?"
                          data-konfirmasi-judul="Hapus Organisasi?">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus" aria-label="Hapus {{ $o->nama_organisasi }}"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td></tr>
              @empty
                <tr class="empty-row"><td colspan="5">Belum ada data organisasi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="form-grid" style="margin-top:16px;">
          <div class="form-group">
            <label for="poinC4">Poin Keaktifan Organisasi (C4)</label>
            <input type="text" id="poinC4" class="input-terkunci" value="{{ number_format($poinOrganisasi, 0) }} poin" disabled readonly aria-readonly="true">
          </div>
        </div>
        <p class="form-hint" style="margin-top:12px;">Tambahkan organisasi melalui tombol <strong>Tambah Keaktifan Organisasi</strong> (boleh lebih dari satu sekaligus); ubah atau hapus lewat ikon pada setiap baris. Data langsung tersimpan setelah lolos validasi, lalu poinnya dihitung otomatis oleh sistem sebagai kriteria Keaktifan Organisasi (C4) pada perhitungan ranking SAW.</p>
      </div>

      {{-- Modal Edit Data Diri --}}
      <div class="modal-overlay" id="modalEditProfil">
        <div class="modal-box">
          <form method="POST" action="{{ route('mahasiswa-profile.simpan') }}">
            @csrf
            <div class="modal-head"><h3>Edit Data Diri</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group"><label>Email *</label><input type="email" name="email" required value="{{ old('email', auth()->user()->email ?? $mahasiswa?->email) }}">
                  <div class="form-hint">Email ini juga dipakai untuk login.</div></div>
                <div class="form-group"><label>Nomor Telepon</label><input name="no_hp" inputmode="numeric" pattern="[0-9]{10,15}" title="Nomor telepon hanya angka (10-15 digit)" placeholder="08xxxxxxxxxx" value="{{ old('no_hp', $mahasiswa?->no_hp) }}"></div>
                <div class="form-group"><label>Kelas</label><input name="kelas" value="{{ old('kelas', $mahasiswa?->kelas) }}"></div>
                <div class="form-group full"><div class="form-hint">Data langsung tersimpan setelah lolos validasi: email wajib &#64;{{ \App\Models\User::domainEmail('mahasiswa') }}, nomor telepon hanya angka.</div></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>

      @if ($mahasiswa)
        {{-- Modal Edit Keaktifan Organisasi --}}
        <div class="modal-overlay" id="modalEditOrganisasi">
          <div class="modal-box">
            <form method="POST" action="#" id="formEditOrganisasi">
              @csrf
              @method('PUT')
              <div class="modal-head"><h3>Edit Keaktifan Organisasi</h3>
                <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
              <div class="modal-body">
                <div class="form-grid">
                  <div class="form-group"><label>Nama Organisasi *</label><input name="nama_organisasi" maxlength="150" required></div>
                  <div class="form-group"><label>Jabatan Organisasi *</label>
                    <select name="jabatan" required>
                      @foreach ($daftarJabatan as $jabatan => $poin)
                        <option value="{{ $jabatan }}">{{ $jabatan }} ({{ $poin }} poin)</option>
                      @endforeach
                    </select></div>
                  <div class="form-group full"><div class="form-hint">Data langsung tersimpan setelah lolos validasi; poin dihitung dari jabatan.</div></div>
                </div>
              </div>
              <div class="modal-foot">
                <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>

        {{-- Modal Tambah Keaktifan Organisasi --}}
        <div class="modal-overlay" id="modalOrganisasi" @if ($errors->has('organisasi') || $errors->has('organisasi.*')) data-buka-otomatis @endif>
          <div class="modal-box">
            <form method="POST" action="{{ route('mahasiswa-profile.organisasi') }}">
              @csrf
              <div class="modal-head"><h3>Tambah Keaktifan Organisasi</h3>
                <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
              <div class="modal-body">
                <div class="form-hint" style="margin:0 0 14px 0;">Isi nama organisasi dan jabatan. Klik <strong>Tambah Baris</strong> bila mengikuti lebih dari satu organisasi. Poin dihitung dari jabatan.</div>
                @php $barisLama = old('organisasi', [['nama_organisasi' => '', 'jabatan' => '']]); @endphp
                <div data-baris-dinamis>
                  @foreach (array_values($barisLama) as $i => $baris)
                    @include('partials.baris-organisasi', ['i' => $i, 'baris' => $baris])
                  @endforeach
                  <template data-baris-template>
                    @include('partials.baris-organisasi', ['i' => '__i__', 'baris' => ['nama_organisasi' => '', 'jabatan' => '']])
                  </template>
                </div>
                <button type="button" class="btn btn-outline btn-sm" data-tambah-baris style="margin-top:4px;"><i class="fa-solid fa-plus"></i> Tambah Baris</button>
                <div class="form-hint" style="margin-top:12px;">Data langsung tersimpan setelah lolos validasi.</div>
              </div>
              <div class="modal-foot">
                <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Keaktifan Organisasi</button>
              </div>
            </form>
          </div>
        </div>
        </div>
      @endif

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
