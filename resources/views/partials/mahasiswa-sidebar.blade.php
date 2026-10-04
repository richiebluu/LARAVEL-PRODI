  {{-- Sidebar --}}
  <aside class="sidebar" id="mahasiswaSidebar">
    <div class="sidebar-brand">
      <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="Logo Program Studi Teknologi Informasi"></div>
      <div class="sidebar-brand-text">
        <span class="b1">POLITALA</span>
        <span class="b2">Dashboard Mahasiswa</span>
      </div>
    </div>
    <nav class="sidebar-menu">
      <div class="menu-label">MENU UTAMA</div>
      <a href="{{ url('/mahasiswa-dashboard') }}" class="side-link" title="Dashboard"><i class="fa-solid fa-gauge"></i> <span class="side-text">Dashboard</span></a>
      <a href="{{ url('/mahasiswa-profile') }}" class="side-link" title="Profil Saya"><i class="fa-solid fa-id-card"></i> <span class="side-text">Profil Saya</span></a>
      <a href="{{ url('/mahasiswa-prestasi') }}" class="side-link" title="Prestasi Saya"><i class="fa-solid fa-trophy"></i> <span class="side-text">Prestasi Saya</span></a>
      <a href="{{ url('/mahasiswa-ajukan-prestasi') }}" class="side-link" title="Ajukan Prestasi"><i class="fa-solid fa-plus"></i> <span class="side-text">Ajukan Prestasi</span></a>
      <a href="{{ url('/ranking') }}" class="side-link" title="Ranking"><i class="fa-solid fa-ranking-star"></i> <span class="side-text">Ranking</span></a>
      <a href="{{ url('/mahasiswa-pengumuman') }}" class="side-link" title="Pengumuman"><i class="fa-solid fa-bullhorn"></i> <span class="side-text">Pengumuman</span></a>
      <div class="menu-label">WEBSITE PUBLIK</div>
      <a href="{{ url('/') }}" class="side-link" title="Lihat Website"><i class="fa-solid fa-globe"></i> <span class="side-text">Lihat Website</span></a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" class="side-link logout" data-logout title="Logout"><i class="fa-solid fa-right-from-bracket"></i> <span class="side-text">Logout</span></a>
    </div>
  </aside>
  @include('partials.sidebar-susut', ['kunciSidebar' => 'tiSidebarMahasiswa', 'idSidebar' => 'mahasiswaSidebar'])
