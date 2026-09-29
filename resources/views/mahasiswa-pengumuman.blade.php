<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengumuman | Dashboard Mahasiswa</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="mahasiswa-pengumuman">
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
            <h1>Pengumuman</h1>
            <div class="subtitle">Informasi resmi yang ditujukan untuk mahasiswa.</div>
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
      <div class="tab-bar">
        <a class="tab-btn {{ ! $kategori ? 'active' : '' }}" href="{{ route('mahasiswa-pengumuman') }}">Semua</a>
        @foreach (\App\Models\Pengumuman::daftarKategori() as $k)
          <a class="tab-btn {{ $kategori === $k ? 'active' : '' }}" href="{{ route('mahasiswa-pengumuman', ['kategori' => $k]) }}">{{ $k }}</a>
        @endforeach
      </div>
      <div id="listPengumuman">
        @forelse ($daftarPengumuman as $item)
          @php $g = $item; @endphp
          <div class="panel" style="margin-bottom:14px;">
            <span class="badge badge-blue">{{ $g->label_kategori }}</span>
            <h3 style="margin:12px 0 6px 0; font-size:1.02rem;">{{ $g->judul }}</h3>
            <p style="font-size:.8rem; color:var(--grey-500);">
              {{ optional($g->tanggal_dikirim ?? $g->created_at)->translatedFormat('d F Y') }} &middot; Pengumuman pribadi untuk Anda
            </p>
            <p style="font-size:.88rem; margin-top:10px;">{{ $g->isi }}</p>
          </div>
        @empty
          <div class="empty-state"><i class="fa-solid fa-bullhorn"></i><p>Belum ada pengumuman untuk Anda.</p></div>
        @endforelse
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
