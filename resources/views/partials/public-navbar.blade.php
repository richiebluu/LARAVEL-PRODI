  {{-- Navbar publik (satu sumber untuk semua halaman publik).
       REVISI 26-09-2026 & 27-09-2026: struktur menu disesuaikan, tampilan/kelas CSS tidak diubah.
       REVISI 28-09-2026 tahap 2: label menu ditulis Capital Each Word (tidak lagi huruf kapital semua);
       + Profil > Sarana & Prasarana, + Mahasiswa > Kegiatan Mahasiswa (urutan ketiga). --}}
  <header class="navbar" id="navbar">
    <div class="container">
      <a href="{{ url('/') }}" class="brand">
        <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="Logo Program Studi Teknologi Informasi"></div>
        <div class="brand-text">
          <span class="b1">POLITALA</span>
          <span class="b2">Program Studi Teknologi Informasi</span>
        </div>
      </a>

      <nav class="nav-menu">
        <div class="nav-item"><a href="{{ url('/') }}" class="nav-link" data-group="beranda">Beranda</a></div>

        <div class="nav-item">
          <a href="{{ url('/profil') }}" class="nav-link" data-group="profil">Profil <i class="fa-solid fa-chevron-down chev"></i></a>
          <div class="dropdown">
            <a href="{{ url('/profil') }}">Tentang</a>
            <a href="{{ url('/prospek-lulusan') }}">Prospek Lulusan</a>
            <a href="{{ url('/akreditasi') }}">Akreditasi</a>
            <a href="{{ url('/struktur-organisasi') }}">Struktur Organisasi</a>
            <a href="{{ url('/dosen') }}">Dosen Pengajar</a>
            <a href="{{ url('/kurikulum') }}">Kurikulum</a>
            <a href="{{ url('/sarana-prasarana') }}">Sarana &amp; Prasarana</a>
          </div>
        </div>

        <div class="nav-item">
          <a href="{{ url('/mahasiswa-berprestasi') }}" class="nav-link" data-group="mahasiswa">Mahasiswa <i class="fa-solid fa-chevron-down chev"></i></a>
          <div class="dropdown">
            <a href="{{ url('/mahasiswa-berprestasi') }}">Mahasiswa Berprestasi</a>
            <a href="{{ url('/ranking') }}">Ranking Mahasiswa</a>
            <a href="{{ url('/kegiatan-mahasiswa') }}">Kegiatan Mahasiswa</a>
          </div>
        </div>

        {{-- REVISI 27-09-2026: Testimoni tanpa dropdown, langsung ke halaman Testimoni Alumni. --}}
        <div class="nav-item"><a href="{{ url('/testimoni') }}" class="nav-link" data-group="testimoni" title="Testimoni Alumni">Testimoni</a></div>

        <div class="nav-item"><a href="{{ url('/lowongan-pekerjaan') }}" class="nav-link" data-group="lowongan">Lowongan Kerja</a></div>

        {{-- REVISI 27-09-2026: "Layanan" menjadi "Informasi"; Berita urutan pertama. --}}
        <div class="nav-item">
          <a href="{{ url('/berita') }}" class="nav-link" data-group="informasi">Informasi <i class="fa-solid fa-chevron-down chev"></i></a>
          <div class="dropdown">
            <a href="{{ url('/berita') }}">Berita</a>
            <a href="{{ url('/akamawa') }}">AKAMAWA</a>
            <a href="{{ url('/kode-etik') }}">Kode Etik Mahasiswa</a>
          </div>
        </div>
      </nav>

      <div class="nav-actions">
        <button class="nav-icon-btn search-toggle" title="Cari" aria-label="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        <span class="lang-switch">ID / EN</span>
        @auth
          <a href="{{ url('/' . auth()->user()->role . '-dashboard') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
        @endauth
        <button class="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
      </div>

      <!-- SEARCH PANEL -->
      <div class="search-panel" id="searchPanel">
        <div class="container search-panel-inner">
          <div class="search-input-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari informasi..." autocomplete="off">
            <button class="search-close" id="searchClose" aria-label="Tutup pencarian"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <div class="search-results" id="searchResults"></div>
        </div>
      </div>
    </div>
  </header>
