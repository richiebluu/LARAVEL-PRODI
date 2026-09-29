  {{-- Footer publik (satu sumber untuk semua halaman publik). Tampilan tidak diubah. --}}
  <footer class="footer">
    <div class="container">
      <div class="footer-top">
        <div class="footer-brand">
          <div class="brand">
            <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="Logo Program Studi Teknologi Informasi"></div>
            <div class="brand-text">
              <span class="b1" style="color:#fff;">POLITALA</span>
              <span class="b2" style="color:rgba(255,255,255,.55);">Program Studi Teknologi Informasi</span>
            </div>
          </div>
          <p>Media informasi dan profil resmi Program Studi Teknologi Informasi — membangun talenta digital masa depan.</p>
          <div class="footer-social">
            <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
          </div>
        </div>
        <div>
          <h5>Profil</h5>
          <ul class="footer-links">
            <li><a href="{{ url('/profil') }}">Tentang</a></li>
            <li><a href="{{ url('/prospek-lulusan') }}">Prospek Lulusan</a></li>
            <li><a href="{{ url('/akreditasi') }}">Akreditasi</a></li>
            <li><a href="{{ url('/struktur-organisasi') }}">Struktur Organisasi</a></li>
            <li><a href="{{ url('/dosen') }}">Dosen Pengajar</a></li>
            <li><a href="{{ url('/kurikulum') }}">Kurikulum</a></li>
            <li><a href="{{ url('/sarana-prasarana') }}">Sarana &amp; Prasarana</a></li>
          </ul>
        </div>
        <div>
          <h5>Mahasiswa</h5>
          <ul class="footer-links">
            <li><a href="{{ url('/mahasiswa-berprestasi') }}">Mahasiswa Berprestasi</a></li>
            <li><a href="{{ url('/ranking') }}">Ranking Mahasiswa</a></li>
            <li><a href="{{ url('/kegiatan-mahasiswa') }}">Kegiatan Mahasiswa</a></li>
            <li><a href="{{ url('/testimoni') }}">Testimoni Alumni</a></li>
            <li><a href="{{ url('/lowongan-pekerjaan') }}">Lowongan Kerja</a></li>
          </ul>
        </div>
        <div>
          <h5>Informasi</h5>
          <ul class="footer-links">
            <li><a href="{{ url('/berita') }}">Berita</a></li>
            <li><a href="{{ url('/akamawa') }}">AKAMAWA</a></li>
            <li><a href="{{ url('/kode-etik') }}">Kode Etik Mahasiswa</a></li>
          </ul>
        </div>
        <div>
          <h5>Akses</h5>
          <ul class="footer-links">
            <li><a href="{{ url('/pengumuman') }}">Pengumuman</a></li>
            <li><a href="{{ url('/login') }}">Login Sistem</a></li>
          </ul>
        </div>
      </div>
      </div>
    <div class="footer-bottom">
        <span>&copy; 2026 Program Studi Teknologi Informasi. Seluruh hak cipta dilindungi.</span>
        <span>Dibuat dengan <i class="fa-solid fa-heart" style="color:#38BDF8;"></i> oleh Tim PBL Teknologi Informasi</span>
      </div>
    </div>
  </footer>
