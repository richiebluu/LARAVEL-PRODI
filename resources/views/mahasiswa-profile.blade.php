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

  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="Logo Program Studi Teknologi Informasi"></div>
      <div>
        <span class="b1">POLITALA</span>
        <span class="b2">Dashboard Mahasiswa</span>
      </div>
    </div>
    <nav class="sidebar-menu">
      <div class="menu-label">MENU UTAMA</div>
      <a href="{{ url('/mahasiswa-dashboard') }}" class="side-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="{{ url('/mahasiswa-profile') }}" class="side-link"><i class="fa-solid fa-id-card"></i> Profil Saya</a>
      <a href="{{ url('/mahasiswa-prestasi') }}" class="side-link"><i class="fa-solid fa-trophy"></i> Prestasi Saya</a>
      <a href="{{ url('/mahasiswa-ajukan-prestasi') }}" class="side-link"><i class="fa-solid fa-plus"></i> Ajukan Prestasi</a>
      <a href="{{ url('/ranking') }}" class="side-link"><i class="fa-solid fa-ranking-star"></i> Ranking</a>
      <a href="{{ url('/mahasiswa-pengumuman') }}" class="side-link"><i class="fa-solid fa-bullhorn"></i> Pengumuman</a>
      <a href="{{ url('/mahasiswa-notifikasi') }}" class="side-link"><i class="fa-solid fa-bell"></i> Notifikasi</a>
      <div class="menu-label">WEBSITE PUBLIK</div>
      <a href="{{ url('/') }}" class="side-link"><i class="fa-solid fa-globe"></i> Lihat Website</a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" class="side-link logout" data-logout><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </aside>
    <div class="main-area">

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
            <button class="btn btn-outline btn-sm" data-modal-open="modalOrganisasi"><i class="fa-solid fa-pen"></i> Input Keaktifan Organisasi</button>
          @endif
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>No</th><th>Nama Organisasi</th><th>Jabatan</th><th>Poin Jabatan</th></tr></thead>
            <tbody>
              @forelse ($daftarOrganisasi as $o)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $o->nama_organisasi }}</td><td>{{ $o->jabatan }}</td><td>{{ $o->poin }} poin</td></tr>
              @empty
                <tr class="empty-row"><td colspan="4">Belum ada data organisasi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="form-grid" style="margin-top:16px;">
          <div class="form-group">
            <label for="poinC4">Poin Keaktifan Organisasi (C4)</label>
            {{-- Angka dihitung sistem dari jabatan organisasi; tidak dapat diubah manual. --}}
            <input type="text" id="poinC4" class="input-terkunci" value="{{ number_format($poinOrganisasi, 0) }} poin" disabled readonly aria-readonly="true">
          </div>
        </div>
        <p class="form-hint" style="margin-top:12px;">Isi nama organisasi dan jabatan melalui tombol <strong>Input Keaktifan Organisasi</strong>. Data langsung tersimpan setelah lolos validasi, lalu poinnya dihitung otomatis oleh sistem sebagai kriteria Keaktifan Organisasi (C4) pada perhitungan ranking SAW.</p>
      </div>

      {{-- REVISI ERD: panel "Riwayat Perubahan Data" dihapus karena tabel pengajuan_perubahan tidak terdapat pada ERD. --}}

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
        <div class="modal-overlay" id="modalOrganisasi">
          <div class="modal-box">
            <form method="POST" action="{{ route('mahasiswa-profile.organisasi') }}">
              @csrf
              <div class="modal-head"><h3>Input Keaktifan Organisasi</h3>
                <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
              <div class="modal-body">
                <div class="form-grid">
                  <div class="form-group full"><div class="form-hint" style="margin-top:0;">Isi nama organisasi dan jabatan (maksimal {{ $maksOrganisasi }} organisasi). Kosongkan baris yang tidak dipakai. Poin dihitung dari jabatan.</div></div>
                  @for ($i = 0; $i < $maksOrganisasi; $i++)
                    @php $o = $daftarOrganisasi->values()->get($i); @endphp
                    <div class="form-group"><label>Nama Organisasi {{ $i + 1 }}</label>
                      <input name="organisasi[{{ $i }}][nama_organisasi]" value="{{ old('organisasi.'.$i.'.nama_organisasi', $o?->nama_organisasi) }}" placeholder="Contoh: HIMA TI"></div>
                    <div class="form-group"><label>Jabatan Organisasi {{ $i + 1 }}</label>
                      <select name="organisasi[{{ $i }}][jabatan]">
                        <option value="">-- Tidak ada --</option>
                        @foreach ($daftarJabatan as $jabatan => $poin)
                          <option value="{{ $jabatan }}" @selected(old('organisasi.'.$i.'.jabatan', $o?->jabatan) === $jabatan)>{{ $jabatan }} ({{ $poin }} poin)</option>
                        @endforeach
                      </select></div>
                  @endfor
                  <div class="form-group full"><div class="form-hint">Data langsung tersimpan setelah lolos validasi.</div></div>
                </div>
              </div>
              <div class="modal-foot">
                <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Keaktifan Organisasi</button>
              </div>
            </form>
          </div>
        </div>
      @endif

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
