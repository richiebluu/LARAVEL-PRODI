<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Prodi | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-profil">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Profil Prodi</h1>
            <div class="subtitle">Perubahan langsung terlihat pada halaman profil publik.</div>
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
        <div class="panel-head"><h2>Data Profil Program Studi</h2>
          <a href="{{ url('/profil') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-globe"></i> Lihat Halaman Publik</a></div>
        <form id="formProfil" method="POST" action="{{ route('staff-profil.simpan') }}" enctype="multipart/form-data">
          @csrf
          <div class="form-grid">
            <div class="form-group full"><label>Nama Program Studi *</label>
              <input name="nama_prodi" value="{{ old('nama_prodi', $prodi?->nama_prodi ?? 'Program Studi Teknologi Informasi') }}" required></div>
            <div class="form-group full"><label>Deskripsi Singkat</label>
              <textarea name="deskripsi" rows="2">{{ old('deskripsi', $prodi?->deskripsi) }}</textarea></div>
            <div class="form-group full"><label>Visi</label>
              <textarea name="visi" rows="3">{{ old('visi', $prodi?->visi) }}</textarea></div>
            <div class="form-group full"><label>Misi (satu baris satu poin)</label>
              <textarea name="misi" rows="4">{{ old('misi', $prodi?->misi) }}</textarea></div>
            {{-- REVISI 28-09-2026: Prospek Lulusan dikelola di halaman tersendiri. --}}
            <div class="form-group full"><div class="form-hint"><i class="fa-solid fa-circle-info"></i> Prospek Lulusan sekarang dikelola di menu <a href="{{ route('staff-prospek-lulusan') }}" style="color:var(--blue-600); font-weight:600;">Prospek Lulusan</a>.</div></div>
            <div class="form-group"><label>Jumlah Alumni</label>
              <input type="number" min="0" name="jumlah_alumni" value="{{ old('jumlah_alumni', $prodi?->jumlah_alumni ?? 0) }}">
              <div class="form-hint">Dipakai pada Capaian Prodi bila belum ada mahasiswa berstatus alumni.</div></div>
            <div class="form-group"><label>Jumlah Dosen (catatan resmi)</label>
              <input type="number" min="0" name="jumlah_dosen" value="{{ old('jumlah_dosen', $prodi?->jumlah_dosen ?? 0) }}">
              <div class="form-hint">Angka DOSEN pada halaman publik tetap dihitung dari tabel dosen.</div></div>

            {{-- REVISI 27-09-2026: "Layanan" menjadi "Informasi" (AKAMAWA + Kode Etik Mahasiswa). --}}
            <div class="form-group full"><h3 style="margin:10px 0 0 0; font-size:1rem;">Informasi</h3>
              <div class="form-hint">Ditampilkan pada menu Informasi (AKAMAWA dan Kode Etik Mahasiswa) di website publik.</div></div>
            <div class="form-group"><label>Link AKAMAWA</label>
              <input type="url" name="link_akamawa" value="{{ old('link_akamawa', $prodi?->link_akamawa) }}" placeholder="{{ \App\Models\ProgramStudi::LINK_AKAMAWA_BAWAAN }}">
              <div class="form-hint">Kosongkan untuk memakai {{ \App\Models\ProgramStudi::LINK_AKAMAWA_BAWAAN }}</div></div>
            <div class="form-group"><label>PDF Kode Etik Mahasiswa</label>
              <input type="file" name="kode_etik" accept="application/pdf">
              <div class="form-hint">Format PDF, maksimal 10 MB. @if ($prodi?->kode_etik_url)<a href="{{ $prodi->kode_etik_url }}" target="_blank" rel="noopener" style="color:var(--blue-600);">Lihat PDF saat ini</a>@else Belum ada PDF yang diunggah. @endif</div></div>
            @if ($prodi?->kode_etik)
              <div class="form-group"><label>Hapus PDF Kode Etik Mahasiswa?</label>
                <select name="hapus_kode_etik">
                  <option value="0">Tidak</option>
                  <option value="1">Ya, hapus PDF saat ini</option>
                </select></div>
            @endif
          </div>
          <div style="margin-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Profil Prodi</button>
          </div>
        </form>
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
