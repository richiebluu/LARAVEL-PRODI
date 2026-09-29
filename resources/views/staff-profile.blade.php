<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-profile">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Profil Saya</h1>
            <div class="subtitle">Data diri Staff Prodi yang sedang login.</div>
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

      @if (! $staff)
        <div class="alert-error"><strong>Akun Anda belum terhubung ke data Staff Prodi.</strong> Jalankan <code>php artisan staff:buat</code> atau hubungi admin.</div>
      @endif

      <div class="profile-head">
        <div class="p-photo" id="pFoto" @if ($staff?->foto_url) style="background-image:url('{{ $staff->foto_url }}')" @endif></div>
        <div>
          <h2 id="pNama">{{ $staff->nama ?? auth()->user()->name }}</h2>
          <p id="pJabatan">{{ $staff->jabatan ?? 'Staff Prodi' }}</p>
        </div>
        <div class="spacer"></div>
        @if ($staff)
          <button class="btn btn-light" id="btnEdit" data-modal-open="modalEditProfilStaff"><i class="fa-solid fa-pen"></i> Edit Profil</button>
        @endif
      </div>

      <div class="panel">
        <div class="panel-head"><h2>Data Diri</h2>
          <span id="pStatus">{{-- ERD: STAFF_PRODI tidak memiliki kolom status; akun yang terhubung ke data staff berarti aktif. --}}<span class="badge {{ $staff ? 'badge-green' : 'badge-grey' }}">{{ $staff ? 'Aktif' : '-' }}</span></span></div>
        <dl class="kv">
          <div><dt>NIP</dt><dd id="pNip">{{ $staff->nip ?? '-' }}</dd></div>
          <div><dt>Nama Lengkap</dt><dd id="pNamaLengkap">{{ $staff->nama ?? '-' }}</dd></div>
          <div><dt>Jabatan</dt><dd id="pJabatanDetail">{{ $staff->jabatan ?? '-' }}</dd></div>
          <div><dt>Email</dt><dd id="pEmail">{{ auth()->user()->email ?? $staff->email ?? '-' }}</dd></div>
          <div><dt>Nomor Telepon</dt><dd id="pTelp">{{ $staff->no_hp ?? '-' }}</dd></div>
          <div><dt>Role</dt><dd id="pRole">Staff Prodi</dd></div>
        </dl>
      </div>

      @if ($staff)
      <div class="modal-overlay" id="modalEditProfilStaff">
        <div class="modal-box">
          <form method="POST" action="{{ route('staff-profile.simpan') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-head"><h3>Edit Profil Staff Prodi</h3>
              <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <div class="form-grid">
                <div class="form-group full"><label>Nama Lengkap &amp; Gelar *</label><input name="nama" value="{{ old('nama', $staff->nama) }}" required></div>
                <div class="form-group"><label>NIP</label><input value="{{ $staff->nip }}" class="input-terkunci" disabled readonly aria-readonly="true"></div>
                <div class="form-group"><label>Jabatan</label><input name="jabatan" value="{{ old('jabatan', $staff->jabatan) }}"></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" value="{{ old('email', auth()->user()->email ?? $staff->email) }}" required>
                  <div class="form-hint">Email ini juga dipakai untuk login.</div></div>
                <div class="form-group"><label>Nomor Telepon</label><input name="no_hp" inputmode="numeric" pattern="[0-9]{10,15}" title="Nomor telepon hanya angka (10-15 digit)" placeholder="08xxxxxxxxxx" value="{{ old('no_hp', $staff->no_hp) }}"></div>
                <div class="form-group full"><label>Ganti Foto</label><input type="file" name="foto" accept="image/*">
                  <div class="form-hint">JPG/PNG/WEBP, maksimal 2 MB. Kosongkan bila tidak ingin mengganti foto.</div></div>
              </div>
            </div>
            <div class="modal-foot">
              <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
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
