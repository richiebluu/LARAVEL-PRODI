  {{-- Sidebar --}}
  <aside class="sidebar" id="staffSidebar">
    <div class="sidebar-brand">
      <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="Logo Program Studi Teknologi Informasi"></div>
      <div class="sidebar-brand-text">
        <span class="b1">POLITALA</span>
        <span class="b2">Dashboard Staff Prodi</span>
      </div>
    </div>
    <nav class="sidebar-menu">
      <div class="menu-label">MENU UTAMA</div>
      <a href="{{ url('/staff-dashboard') }}" class="side-link" title="Dashboard"><i class="fa-solid fa-gauge"></i> <span class="side-text">Dashboard</span></a>
      <a href="{{ url('/staff-profile') }}" class="side-link" title="Profil Saya"><i class="fa-solid fa-id-card"></i> <span class="side-text">Profil Saya</span></a>
      <div class="menu-label">DATA MASTER</div>
      <a href="{{ url('/staff-profil') }}" class="side-link" title="Profil Prodi"><i class="fa-solid fa-building-columns"></i> <span class="side-text">Profil Prodi</span></a>
      <a href="{{ url('/staff-struktur-organisasi') }}" class="side-link" title="Struktur Organisasi"><i class="fa-solid fa-sitemap"></i> <span class="side-text">Struktur Organisasi</span></a>
      <a href="{{ url('/staff-prospek-lulusan') }}" class="side-link" title="Prospek Lulusan"><i class="fa-solid fa-user-tie"></i> <span class="side-text">Prospek Lulusan</span></a>
      <a href="{{ url('/staff-mata-kuliah') }}" class="side-link" title="Mata Kuliah"><i class="fa-solid fa-book-open"></i> <span class="side-text">Mata Kuliah</span></a>
      <a href="{{ url('/staff-sarana-prasarana') }}" class="side-link" title="Sarana &amp; Prasarana"><i class="fa-solid fa-flask"></i> <span class="side-text">Sarana &amp; Prasarana</span></a>
      <a href="{{ url('/staff-akreditasi') }}" class="side-link" title="Akreditasi"><i class="fa-solid fa-certificate"></i> <span class="side-text">Akreditasi</span></a>
      <a href="{{ url('/staff-dosen') }}" class="side-link" title="Dosen"><i class="fa-solid fa-chalkboard-user"></i> <span class="side-text">Dosen</span></a>
      <a href="{{ url('/staff-mahasiswa') }}" class="side-link" title="Mahasiswa"><i class="fa-solid fa-user-graduate"></i> <span class="side-text">Mahasiswa</span></a>
      <div class="menu-label">MAHASISWA BERPRESTASI</div>
      <a href="{{ url('/staff-prestasi') }}" class="side-link" title="Prestasi"><i class="fa-solid fa-star"></i> <span class="side-text">Prestasi</span></a>
      <a href="{{ url('/staff-ranking') }}" class="side-link" title="Ranking"><i class="fa-solid fa-ranking-star"></i> <span class="side-text">Ranking</span></a>
      <a href="{{ url('/staff-pengumuman') }}" class="side-link" title="Pengumuman"><i class="fa-solid fa-bullhorn"></i> <span class="side-text">Pengumuman</span></a>
      <div class="menu-label">INFORMASI PUBLIK</div>
      <a href="{{ url('/staff-testimoni') }}" class="side-link" title="Testimoni"><i class="fa-solid fa-comment-dots"></i> <span class="side-text">Testimoni</span></a>
      <a href="{{ url('/staff-berita') }}" class="side-link" title="Berita"><i class="fa-solid fa-newspaper"></i> <span class="side-text">Berita</span></a>
      <a href="{{ url('/staff-lowongan') }}" class="side-link" title="Lowongan Kerja"><i class="fa-solid fa-briefcase"></i> <span class="side-text">Lowongan Kerja</span></a>
      <div class="menu-label">WEBSITE PUBLIK</div>
      <a href="{{ url('/') }}" class="side-link" title="Lihat Website"><i class="fa-solid fa-globe"></i> <span class="side-text">Lihat Website</span></a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" class="side-link logout" data-logout title="Logout"><i class="fa-solid fa-right-from-bracket"></i> <span class="side-text">Logout</span></a>
    </div>
  </aside>
  @include('partials.sidebar-susut', ['kunciSidebar' => 'tiSidebarStaff', 'idSidebar' => 'staffSidebar'])
