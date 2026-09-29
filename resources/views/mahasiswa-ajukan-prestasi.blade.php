<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ajukan Prestasi | Dashboard Mahasiswa</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="mahasiswa-ajukan-prestasi">
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
            <h1>Ajukan Prestasi</h1>
            <div class="subtitle">Isi data prestasi dan unggah bukti untuk diverifikasi Staff Prodi.</div>
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

      @if (session('success'))
        <div class="notif-item unread" id="boxStatus" style="display:flex;">
          <div class="notif-ico"><i class="fa-solid fa-paper-plane"></i></div>
          <div style="flex:1;"><strong>{{ session('success') }}</strong>
          <p>Pantau prosesnya pada halaman <a href="{{ url('/mahasiswa-prestasi') }}" style="color:var(--blue-600); font-weight:600;">Prestasi Saya</a>.</p></div>
        </div>
      @endif

      <div class="two-col">
        <div class="panel">
          <div class="panel-head"><h2>Formulir Pengajuan Prestasi</h2></div>
          <form id="formPrestasi" method="POST" action="{{ route('mahasiswa-ajukan-prestasi.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
              <div class="form-group full"><label>Nama Prestasi *</label>
                <input name="judul" value="{{ old('judul') }}" placeholder="Contoh: Juara 1 Hackathon Nasional" required></div>
              <div class="form-group"><label>Kategori Prestasi *</label>
                <select name="kategori" required>
                  @foreach (\App\Models\Prestasi::KATEGORI as $k)
                    <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                  @endforeach
                </select></div>
              <div class="form-group"><label>Tingkat Prestasi *</label>
                <select name="tingkat" required>
                  @foreach (\App\Models\Prestasi::daftarTingkat() as $t)
                    <option value="{{ $t }}" @selected(old('tingkat') === $t)>{{ $t }}</option>
                  @endforeach
                </select></div>
              <div class="form-group"><label>Tanggal Prestasi *</label>
                <input type="date" name="tanggal" value="{{ old('tanggal') }}" max="{{ date('Y-m-d') }}" required></div>
              <div class="form-group"><label>Penyelenggara</label>
                <input name="penyelenggara" value="{{ old('penyelenggara') }}" placeholder="Nama penyelenggara"></div>
              <div class="form-group full"><label>Deskripsi</label>
                <textarea name="deskripsi" rows="3" placeholder="Ceritakan singkat lomba dan capaianmu">{{ old('deskripsi') }}</textarea></div>
              <div class="form-group full">
                <label>Upload Bukti</label>
                <div class="dropzone" id="dropzone" data-file-input="fileBukti" data-file-info="namaFile">
                  <strong><i class="fa-solid fa-cloud-arrow-up"></i> Pilih berkas sertifikat</strong>PDF atau gambar, maksimal 4 MB. Berkas diunggah ke server.
                </div>
                <input type="file" name="dokumen" id="fileBukti" accept=".pdf,image/*" hidden>
                <div class="form-hint" id="namaFile"></div>
              </div>
            </div>
            <div style="margin-top:20px;">
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan</button>
            </div>
          </form>
        </div>

        <div class="panel">
          <div class="panel-head"><h2>Alur Verifikasi</h2></div>
          <div class="timeline">
            <div class="timeline-item"><div class="timeline-year">1</div><h4>Mahasiswa mengajukan</h4><p>Data dan bukti dikirim lewat formulir ini dan langsung tersimpan di database.</p></div>
            <div class="timeline-item"><div class="timeline-year">2</div><h4>Status menunggu</h4><p>Pengajuan masuk ke antrean verifikasi Staff Prodi.</p></div>
            <div class="timeline-item"><div class="timeline-year">3</div><h4>Staff menyetujui atau menolak</h4><p>Kamu menerima notifikasi beserta alasannya bila ditolak.</p></div>
            <div class="timeline-item"><div class="timeline-year">4</div><h4>Poin masuk ranking</h4><p>Prestasi yang disetujui tampil di halaman Mahasiswa Berprestasi dan poinnya dihitung pada ranking.</p></div>
          </div>

          <div class="panel-head" style="margin-top:24px;"><h2>Poin Tingkat Prestasi</h2></div>
          <div class="table-wrap">
            <table class="data-table">
              <thead><tr><th>Tingkat Prestasi</th><th>Prestasi Akademik</th><th>Prestasi Non-Akademik</th></tr></thead>
              <tbody>
                @foreach (\App\Models\Prestasi::daftarTingkat() as $t)
                  <tr><td>{{ $t }}</td><td>{{ config('saw.prestasi_akademik.skor.'.$t) }} poin</td><td>{{ config('saw.prestasi_non_akademik.skor.'.$t) }} poin</td></tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <p class="form-hint" style="margin-top:10px;">Prestasi tambahan pada kategori yang sama memberi bonus +{{ config('saw.prestasi_akademik.bonus_per_tambahan') }} (Prestasi Akademik) atau +{{ config('saw.prestasi_non_akademik.bonus_per_tambahan') }} (Prestasi Non-Akademik), maksimal 2 prestasi tambahan.</p>
        </div>
      </div>

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
