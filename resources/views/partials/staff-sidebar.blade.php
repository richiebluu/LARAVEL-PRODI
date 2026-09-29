  {{-- Sidebar Dashboard Staff Prodi (satu sumber untuk semua halaman Staff Prodi).
       REVISI 26-09-2026: menu dikelompokkan (Data Master, Mahasiswa Berprestasi, Informasi Publik);
       menu Verifikasi perubahan data dihapus. Tampilan/kelas CSS tidak diubah.
       REVISI 28-09-2026 tahap 2: + Sarana & Prasarana (Data Master), + Kegiatan Mahasiswa (Informasi Publik). --}}
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="Logo Program Studi Teknologi Informasi"></div>
      <div>
        <span class="b1">POLITALA</span>
        <span class="b2">Dashboard Staff Prodi</span>
      </div>
    </div>
    <nav class="sidebar-menu">
      <div class="menu-label">MENU UTAMA</div>
      <a href="{{ url('/staff-dashboard') }}" class="side-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="{{ url('/staff-profile') }}" class="side-link"><i class="fa-solid fa-id-card"></i> Profil Saya</a>
      <div class="menu-label">DATA MASTER</div>
      <a href="{{ url('/staff-profil') }}" class="side-link"><i class="fa-solid fa-building-columns"></i> Profil Prodi</a>
      <a href="{{ url('/staff-struktur-organisasi') }}" class="side-link"><i class="fa-solid fa-sitemap"></i> Struktur Organisasi</a>
      <a href="{{ url('/staff-prospek-lulusan') }}" class="side-link"><i class="fa-solid fa-user-tie"></i> Prospek Lulusan</a>
      <a href="{{ url('/staff-kurikulum') }}" class="side-link"><i class="fa-solid fa-book-open"></i> Kurikulum</a>
      <a href="{{ url('/staff-sarana-prasarana') }}" class="side-link"><i class="fa-solid fa-flask"></i> Sarana &amp; Prasarana</a>
      <a href="{{ url('/staff-akreditasi') }}" class="side-link"><i class="fa-solid fa-certificate"></i> Akreditasi</a>
      <a href="{{ url('/staff-dosen') }}" class="side-link"><i class="fa-solid fa-chalkboard-user"></i> Dosen</a>
      <a href="{{ url('/staff-mahasiswa') }}" class="side-link"><i class="fa-solid fa-user-graduate"></i> Mahasiswa</a>
      <div class="menu-label">MAHASISWA BERPRESTASI</div>
      <a href="{{ url('/staff-prestasi') }}" class="side-link"><i class="fa-solid fa-star"></i> Prestasi</a>
      <a href="{{ url('/staff-ranking') }}" class="side-link"><i class="fa-solid fa-ranking-star"></i> Ranking</a>
      <a href="{{ url('/staff-pengumuman') }}" class="side-link"><i class="fa-solid fa-bullhorn"></i> Pengumuman</a>
      <div class="menu-label">INFORMASI PUBLIK</div>
      <a href="{{ url('/staff-kegiatan-mahasiswa') }}" class="side-link"><i class="fa-solid fa-people-group"></i> Kegiatan Mahasiswa</a>
      <a href="{{ url('/staff-testimoni') }}" class="side-link"><i class="fa-solid fa-comment-dots"></i> Testimoni</a>
      <a href="{{ url('/staff-berita') }}" class="side-link"><i class="fa-solid fa-newspaper"></i> Berita</a>
      <a href="{{ url('/staff-lowongan') }}" class="side-link"><i class="fa-solid fa-briefcase"></i> Lowongan Kerja</a>
      <div class="menu-label">WEBSITE PUBLIK</div>
      <a href="{{ url('/') }}" class="side-link"><i class="fa-solid fa-globe"></i> Lihat Website</a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" class="side-link logout" data-logout><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </aside>
