  <header class="navbar" id="navbar">
    <div class="container">
      <a href="{{ url('/') }}" class="brand">
        <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="{{ __('Logo Program Studi Teknologi Informasi') }}"></div>
        <div class="brand-text">
          <span class="b1">POLITALA</span>
          <span class="b2">{{ __('Program Studi Teknologi Informasi') }}</span>
        </div>
      </a>

      <nav class="nav-menu">
        <div class="nav-item"><a href="{{ url('/') }}" class="nav-link" data-group="beranda">{{ __('Beranda') }}</a></div>

        <div class="nav-item">
          <a class="nav-link nav-induk" data-group="profil" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">{{ __('Profil') }} <i class="fa-solid fa-chevron-down chev"></i></a>
          <div class="dropdown">
            <a href="{{ url('/profil') }}">{{ __('Tentang') }}</a>
            <a href="{{ url('/prospek-lulusan') }}">{{ __('Prospek Lulusan') }}</a>
            <a href="{{ url('/akreditasi') }}">{{ __('Akreditasi') }}</a>
            <a href="{{ url('/struktur-organisasi') }}">{{ __('Struktur Organisasi') }}</a>
            <a href="{{ url('/dosen') }}">{{ __('Dosen Pengajar') }}</a>
            <a href="{{ url('/mata-kuliah') }}">{{ __('Mata Kuliah') }}</a>
            <a href="{{ url('/sarana-prasarana') }}">{{ __('Sarana & Prasarana') }}</a>
          </div>
        </div>

        <div class="nav-item">
          <a class="nav-link nav-induk" data-group="mahasiswa" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">{{ __('Mahasiswa') }} <i class="fa-solid fa-chevron-down chev"></i></a>
          <div class="dropdown">
            <a href="{{ url('/mahasiswa-berprestasi') }}">{{ __('Mahasiswa Berprestasi') }}</a>
            <a href="{{ url('/ranking') }}">{{ __('Ranking Mahasiswa') }}</a>
          </div>
        </div>

        <div class="nav-item"><a href="{{ url('/testimoni') }}" class="nav-link" data-group="testimoni" title="{{ __('Testimoni Alumni') }}">{{ __('Testimoni') }}</a></div>

        <div class="nav-item"><a href="{{ url('/lowongan-pekerjaan') }}" class="nav-link" data-group="lowongan">{{ __('Lowongan Kerja') }}</a></div>

        <div class="nav-item">
          <a class="nav-link nav-induk" data-group="informasi" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">{{ __('Informasi') }} <i class="fa-solid fa-chevron-down chev"></i></a>
          <div class="dropdown">
            <a href="{{ url('/berita') }}">{{ __('Berita') }}</a>
            <a href="{{ url('/akamawa') }}">AKAMAWA</a>
            <a href="{{ url('/kode-etik') }}">{{ __('Kode Etik Mahasiswa') }}</a>
          </div>
        </div>

        <div class="nav-item nav-bahasa">
          <a href="{{ route('bahasa', 'id') }}" class="nav-link {{ app()->getLocale() === 'id' ? 'aktif' : '' }}" lang="id">ID</a>
          <a href="{{ route('bahasa', 'en') }}" class="nav-link {{ app()->getLocale() === 'en' ? 'aktif' : '' }}" lang="en">EN</a>
        </div>
      </nav>

      <div class="nav-actions">
        <button class="nav-icon-btn search-toggle" title="{{ __('Cari') }}" aria-label="{{ __('Cari') }}"><i class="fa-solid fa-magnifying-glass"></i></button>
        <span class="lang-switch">
          <a href="{{ route('bahasa', 'id') }}" class="{{ app()->getLocale() === 'id' ? 'aktif' : '' }}" title="Bahasa Indonesia" lang="id">ID</a> / <a href="{{ route('bahasa', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'aktif' : '' }}" title="English" lang="en">EN</a>
        </span>
        @auth
          <a href="{{ url('/' . auth()->user()->role . '-dashboard') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-gauge"></i> {{ __('Dashboard') }}</a>
        @else
          <a href="{{ route('login') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-right-to-bracket"></i> {{ __('Login') }}</a>
        @endauth
        <button class="hamburger" aria-label="{{ __('Menu') }}"><span></span><span></span><span></span></button>
      </div>

      <div class="search-panel" id="searchPanel">
        <div class="container search-panel-inner">
          <div class="search-input-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="{{ __('Cari informasi...') }}" autocomplete="off">
            <button class="search-close" id="searchClose" aria-label="{{ __('Tutup pencarian') }}"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <div class="search-results" id="searchResults"></div>
        </div>
      </div>
    </div>
  </header>
