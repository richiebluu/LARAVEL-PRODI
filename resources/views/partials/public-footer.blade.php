  {{-- Footer --}}
  <footer class="footer">
    <div class="container">
      <div class="footer-top">
        <div class="footer-brand">
          <div class="brand">
            <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="{{ __('Logo Program Studi Teknologi Informasi') }}"></div>
            <div class="brand-text">
              <span class="b1" style="color:#fff;">POLITALA</span>
              <span class="b2" style="color:rgba(255,255,255,.55);">{{ __('Program Studi Teknologi Informasi') }}</span>
            </div>
          </div>
          <p>{{ __('Media informasi dan profil resmi Program Studi Teknologi Informasi — membangun talenta digital masa depan.') }}</p>
          <div class="footer-social">
            @foreach (\App\Models\ProgramStudi::mediaSosialWebsite() as $sosmed)
              @if (filled($sosmed['url']))
                <a href="{{ $sosmed['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $sosmed['label'] }} Prodi TI Politala" title="{{ $sosmed['label'] }} Prodi TI Politala"><i class="{{ $sosmed['ikon'] }}"></i></a>
              @else
                <a class="belum-diatur" role="link" aria-disabled="true" aria-label="{{ $sosmed['label'] }} (tautan belum diatur)" title="{{ $sosmed['label'] }} — tautan belum diatur"><i class="{{ $sosmed['ikon'] }}"></i></a>
              @endif
            @endforeach
          </div>
        </div>
        <div>
          <h5>{{ __('Profil') }}</h5>
          <ul class="footer-links">
            <li><a href="{{ url('/profil') }}">{{ __('Tentang') }}</a></li>
            <li><a href="{{ url('/prospek-lulusan') }}">{{ __('Prospek Lulusan') }}</a></li>
            <li><a href="{{ url('/akreditasi') }}">{{ __('Akreditasi') }}</a></li>
            <li><a href="{{ url('/struktur-organisasi') }}">{{ __('Struktur Organisasi') }}</a></li>
            <li><a href="{{ url('/dosen') }}">{{ __('Dosen Pengajar') }}</a></li>
            <li><a href="{{ url('/mata-kuliah') }}">{{ __('Mata Kuliah') }}</a></li>
            <li><a href="{{ url('/sarana-prasarana') }}">{{ __('Sarana & Prasarana') }}</a></li>
          </ul>
        </div>
        <div>
          <h5>{{ __('Mahasiswa') }}</h5>
          <ul class="footer-links">
            <li><a href="{{ url('/mahasiswa-berprestasi') }}">{{ __('Mahasiswa Berprestasi') }}</a></li>
            <li><a href="{{ url('/ranking') }}">{{ __('Ranking Mahasiswa') }}</a></li>
            <li><a href="{{ url('/testimoni') }}">{{ __('Testimoni Alumni') }}</a></li>
            <li><a href="{{ url('/lowongan-pekerjaan') }}">{{ __('Lowongan Kerja') }}</a></li>
          </ul>
        </div>
        <div>
          <h5>{{ __('Informasi') }}</h5>
          <ul class="footer-links">
            <li><a href="{{ url('/berita') }}">{{ __('Berita') }}</a></li>
            <li><a href="{{ url('/akamawa') }}">AKAMAWA</a></li>
            <li><a href="{{ url('/kode-etik') }}">{{ __('Kode Etik Mahasiswa') }}</a></li>
          </ul>
        </div>
        <div>
          <h5>{{ __('Akses') }}</h5>
          <ul class="footer-links">
            <li><a href="{{ url('/pengumuman') }}">{{ __('Pengumuman') }}</a></li>
            <li><a href="{{ url('/login') }}">{{ __('Login Sistem') }}</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>{{ __('© 2026 Program Studi Teknologi Informasi. Seluruh hak cipta dilindungi.') }}</span>
        <span>{{ __('Dibuat dengan') }} <i class="fa-solid fa-heart" style="color:#38BDF8;"></i> {{ __('oleh Tim PBL Teknologi Informasi') }}</span>
      </div>
    </div>
  </footer>
