<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beranda | Program Studi Teknologi Informasi</title>
<meta name="description" content="Website resmi Program Studi Teknologi Informasi sebagai media informasi dan profil Prodi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="beranda">

  @include('partials.public-navbar')

  <!-- =================== HERO SLIDER =================== -->
  <section class="hero-slider">
    <div class="hero-slide active" style="background-image:url('https://cdn.corenexis.com/f/YVG0IKiag9T.jpg')">
      <div class="container">
        <div class="hero-content">
          <span class="eyebrow"><i class="fa-solid fa-microchip"></i> Program Studi Teknologi Informasi</span>
          <h1>Build Technology.<br>Create Innovation.<br>Make an Impact.</h1>
          <p>Tempat berkembangnya talenta digital, inovator, dan pencipta teknologi masa depan.</p>
          <div class="hero-actions">
            <a href="{{ url('/profil') }}" class="btn btn-primary">Kenali Prodi TI</a>
            <a href="{{ url('/pengumuman') }}" class="btn btn-ghost-white">Lihat Pengumuman</a>
          </div>
        </div>
      </div>
    </div>
    <div class="hero-slide" style="background-image:url('https://cdn.corenexis.com/f/mxRMXHq0qkL.png')">
      <div class="container">
        <div class="hero-content">
          <span class="eyebrow"><i class="fa-solid fa-medal"></i> Prestasi Mahasiswa</span>
          <h1>Explore Student Achievement</h1>
          <p>Lihat capaian, prestasi, dan mahasiswa berprestasi Teknologi Informasi.</p>
          <div class="hero-actions">
            <a href="{{ url('/mahasiswa-berprestasi') }}" class="btn btn-primary">Lihat Mahasiswa Berprestasi</a>
          </div>
        </div>
      </div>
    </div>
    <div class="hero-slide" style="background-image:url('https://cdn.corenexis.com/f/vuQbJbgT58Y.jpg')">
      <div class="container">
        <div class="hero-content">
          <span class="eyebrow"><i class="fa-solid fa-graduation-cap"></i> Bergabunglah</span>
          <h1>Your Future Starts Here</h1>
          <p>Mulai perjalananmu di dunia teknologi bersama Program Studi Teknologi Informasi.</p>
          <div class="hero-actions">
            <a href="{{ url('/login') }}" class="btn btn-primary">Masuk Dashboard</a>
          </div>
        </div>
      </div>
    </div>

    <div class="hero-cursor-glow" aria-hidden="true"></div>

    <button class="hero-nav-btn hero-prev" aria-label="Slide sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
    <button class="hero-nav-btn hero-next" aria-label="Slide berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
    <div class="hero-indicators" role="tablist" aria-label="Navigasi slide">
      <button class="active" role="tab" aria-selected="true" aria-label="Slide 1"><span class="hero-indicator-fill"></span></button>
      <button role="tab" aria-selected="false" aria-label="Slide 2"><span class="hero-indicator-fill"></span></button>
      <button role="tab" aria-selected="false" aria-label="Slide 3"><span class="hero-indicator-fill"></span></button>
    </div>
  </section>

  <!-- =================== KODEX =================== -->
  <section class="kodex-section section-pad">
    <div class="container">
      <div class="kodex-grid">
        <div class="kodex-figure reveal">
          <div class="kodex-glow"></div>
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex - Maskot Program Studi Teknologi Informasi" style="position:relative; z-index:1;">
        </div>
        <div class="reveal">
          <div class="kodex-bubble">Hi...!! I'm Kodex</div>
          <h2>Teman Digital Teknologi Informasi</h2>
          <p>Kodex menemani setiap mahasiswa menjelajahi dunia teknologi — dari baris kode pertama hingga karya yang berdampak nyata bagi masyarakat.</p>
          <div class="kodex-slogan"><i class="fa-solid fa-rocket"></i> Code . Create . Impact</div>
        </div>
      </div>
    </div>
  </section>

  <!-- =================== CAPAIAN PRODI =================== -->
  <!-- Seluruh angka dihitung dari database (Mahasiswa, Dosen, Prestasi). -->
  <section class="stats-section section-pad">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow" style="background:rgba(255,255,255,.1); color:#7DD3FC;"><i class="fa-solid fa-chart-line"></i> Capaian Prodi</span>
        <h2 style="color:#fff;">Angka yang Berbicara</h2>
        <p style="color:rgba(255,255,255,.7);">Pertumbuhan Program Studi Teknologi Informasi dari tahun ke tahun.</p>
      </div>
      <div class="stats-grid reveal-stagger">
        <div class="stat-card reveal"><div class="stat-num"><span id="statMahasiswa" data-counter-plain="{{ $jumlahMahasiswa }}">0</span></div><div class="stat-label">MAHASISWA AKTIF</div></div>
        <div class="stat-card reveal"><div class="stat-num"><span id="statAlumni" data-counter-plain="{{ $jumlahAlumni }}">0</span></div><div class="stat-label">ALUMNI</div></div>
        <div class="stat-card reveal"><div class="stat-num"><span id="statPrestasi" data-counter-plain="{{ $jumlahPrestasi }}">0</span></div><div class="stat-label">PRESTASI DISETUJUI</div></div>
        <div class="stat-card reveal"><div class="stat-num"><span id="statDosen" data-counter-plain="{{ $jumlahDosen }}">0</span></div><div class="stat-label">DOSEN</div></div>
      </div>
    </div>
  </section>

  <!-- =================== AKREDITASI =================== -->
  <section class="section-pad bg-grey">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-shield-halved"></i> Akreditasi</span>
        <h2>Akreditasi Program Studi</h2>
      </div>
      @if ($akreditasi)
        <div class="akreditasi-card reveal">
          <div class="akreditasi-icon"><i class="fa-solid fa-certificate"></i></div>
          <div>
            <div class="akreditasi-badge"><i class="fa-solid fa-circle-check"></i> <span>{{ Str::upper($akreditasi->status) }}</span></div>
            <h3 style="margin-bottom:6px;">Peringkat {{ $akreditasi->peringkat }}</h3>
            <p style="margin:0;">{{ $akreditasi->lembaga ?? '-' }} &middot; SK {{ $akreditasi->nomor_sk ?? '-' }} &middot; berlaku sampai {{ optional($akreditasi->tanggal_berakhir)->translatedFormat('d F Y') ?? '-' }}</p>
          </div>
          <a href="{{ url('/akreditasi') }}" class="btn btn-outline">Lihat Informasi Akreditasi</a>
        </div>
      @else
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>Belum ada data akreditasi.</p>
        </div>
      @endif
    </div>
  </section>

  <!-- =================== PROMOSI PRODI =================== -->
  <section class="section-pad">
    <div class="container">
      <div class="grid-2" style="align-items:center;">
        <div class="reveal">
          <img src="https://picsum.photos/seed/promo-ti/900/700" alt="Mahasiswa Teknologi Informasi berkolaborasi" style="border-radius:26px; box-shadow:var(--shadow-lg);">
        </div>
        <div class="reveal">
          <span class="eyebrow"><i class="fa-solid fa-arrow-trend-up"></i> Bergabunglah Bersama Kami</span>
          <h2>Mulai Masa Depan Digitalmu Bersama Teknologi Informasi</h2>
          <p>Belajar teknologi terkini melalui praktik nyata, project kolaboratif, dan bimbingan dosen berpengalaman di bidang teknologi informasi.</p>
          <div class="hero-actions">
            <a href="{{ url('/profil') }}" class="btn btn-primary">Kenali Prodi TI</a>
            <a href="{{ url('/pengumuman') }}" class="btn btn-outline">Lihat Pengumuman</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- =================== BENEFIT =================== -->
  <section class="section-pad bg-soft">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-star"></i> Keunggulan</span>
        <h2>Kenapa Memilih Teknologi Informasi?</h2>
      </div>
      <div class="grid-4 reveal-stagger">
        <div class="card benefit-card reveal">
          <div class="benefit-icon"><i class="fa-solid fa-layer-group"></i></div>
          <h3>Pembelajaran Relevan</h3>
          <p>Kurikulum mengikuti perkembangan teknologi industri terkini.</p>
        </div>
        <div class="card benefit-card reveal">
          <div class="benefit-icon"><i class="fa-solid fa-diagram-project"></i></div>
          <h3>Project Based Learning</h3>
          <p>Belajar dengan mengerjakan project nyata sejak semester awal.</p>
        </div>
        <div class="card benefit-card reveal">
          <div class="benefit-icon"><i class="fa-solid fa-laptop-code"></i></div>
          <h3>Pengalaman Praktis</h3>
          <p>Praktikum dan lab intensif di setiap mata kuliah inti.</p>
        </div>
        <div class="card benefit-card reveal">
          <div class="benefit-icon"><i class="fa-solid fa-briefcase"></i></div>
          <h3>Peluang Karier Digital</h3>
          <p>Terhubung dengan industri teknologi melalui kerja sama dan kolaborasi Program Studi.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- =================== MAHASISWA BERPRESTASI =================== -->
  <!-- Diambil dari prestasi berstatus "disetujui" pada database. -->
  <section class="section-pad">
    <div class="container">
      <div class="section-head align-left" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; max-width:100%;">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-medal"></i> Mahasiswa Berprestasi</span>
          <h2 style="margin-bottom:0;">Kebanggaan Program Studi</h2>
        </div>
        <a href="{{ url('/mahasiswa-berprestasi') }}" class="btn btn-outline">Lihat Mahasiswa Berprestasi</a>
      </div>
      <div class="tabel-publik-wrap reveal" id="idxBerprestasiGrid">
        <table class="tabel-publik">
          <thead><tr><th>No</th><th>NIM</th><th>Nama</th><th>Prestasi</th><th class="kolom-opsional">Kategori</th><th>Tingkat Prestasi</th><th class="kolom-opsional">Tahun</th></tr></thead>
          <tbody>
            @forelse ($daftarBerprestasi as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->mahasiswa->nim }}</td>
                <td><strong>{{ $item->mahasiswa->nama }}</strong></td>
                <td>{{ $item->judul }}</td>
                <td class="kolom-opsional"><span class="label-kriteria {{ $item->kategori === \App\Models\Prestasi::KATEGORI_NON_AKADEMIK ? 'non' : '' }}">{{ $item->kategori }}</span></td>
                <td>{{ $item->tingkat ?? '-' }}</td>
                <td class="kolom-opsional">{{ $item->tanggal?->format('Y') ?? '-' }}</td>
              </tr>
            @empty
              <tr class="empty-row"><td colspan="7">Belum ada mahasiswa berprestasi.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- =================== DOSEN =================== -->
  <section class="section-pad bg-soft">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><i class="fa-solid fa-chalkboard-user"></i> Tenaga Pengajar</span>
        <h2>Kenali Dosen Kami</h2>
      </div>
      <div class="grid-4 reveal-stagger" id="idxDosenGrid">
        @forelse ($daftarDosen as $dosen)
          <div class="card person-card reveal">
            <div class="person-photo" @if ($dosen->foto_url) style="background-image:url('{{ $dosen->foto_url }}')" @endif></div>
            <div class="person-body">
              <h4>{{ $dosen->nama }}</h4>
              <div class="person-meta">{{ $dosen->pendidikan_terakhir ?? $dosen->label_status }}</div>
              <a href="{{ url('/dosen') }}#modalDosen{{ $dosen->nuptk }}" class="btn btn-sm btn-outline" style="margin-top:10px;">Lihat Profil</a>
            </div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Belum ada data dosen.</p>
          </div>
        @endforelse
      </div>
      <div class="text-center" style="margin-top:36px;">
        <a href="{{ url('/dosen') }}" class="btn btn-primary">Lihat Semua Dosen</a>
      </div>
    </div>
  </section>

  <!-- =================== SOCIAL MEDIA =================== -->
  <section class="section-pad bg-grey">
    <div class="container">
      <div class="social-grid">
        <div class="reveal">
          <span class="eyebrow"><i class="fa-solid fa-hashtag"></i> Media Sosial</span>
          <h2>Ikuti Aktivitas Kami</h2>
          <p>Pantau kegiatan, prestasi, dan momen keseharian Program Studi Teknologi Informasi melalui media sosial resmi kami.</p>
          <div class="social-list">
            <a href="#" class="social-item"><div class="social-icon"><i class="fa-brands fa-instagram"></i></div><div><strong>Instagram</strong><span>@ti.politala</span></div></a>
            <a href="#" class="social-item"><div class="social-icon"><i class="fa-brands fa-youtube"></i></div><div><strong>YouTube</strong><span>Teknologi Informasi Channel</span></div></a>
            <a href="#" class="social-item"><div class="social-icon"><i class="fa-brands fa-tiktok"></i></div><div><strong>TikTok</strong><span>@ti.politala</span></div></a>
          </div>
        </div>
        <div class="social-collage reveal">
          <div><img src="https://picsum.photos/seed/soc1/500/640" alt="Kegiatan mahasiswa"></div>
          <div><img src="https://picsum.photos/seed/soc2/500/500" alt="Kegiatan laboratorium"></div>
          <div><img src="https://picsum.photos/seed/soc3/500/500" alt="Kegiatan seminar"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- =================== RANKING MAHASISWA =================== -->
  <section class="section-pad bg-soft">
    <div class="container">
      <div class="section-head align-left" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; max-width:100%;">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-ranking-star"></i> Ranking Mahasiswa</span>
          <h2 style="margin-bottom:0;">Peringkat Berdasarkan Mahasiswa Berprestasi</h2>
        </div>
        <a href="{{ url('/ranking') }}" class="btn btn-outline">Lihat Ranking</a>
      </div>
      <div id="idxRankList" class="reveal">
        @if ($daftarRanking->isNotEmpty())
          <div class="rank-row head">
            <div>#</div><div>Mahasiswa</div>
            <div class="rank-col">Nilai Akademik (IPK)</div><div class="rank-col">Prestasi Akademik</div>
            <div class="rank-col">Prestasi Non-Akademik</div><div class="rank-col">Keaktifan Organisasi</div>
            <div>Nilai Akhir</div>
          </div>
        @endif
        @forelse ($daftarRanking as $r)
          <div class="rank-row">
            <div class="rank-no">{{ $r['peringkat'] }}</div>
            <div class="rank-person">
              <div class="rank-photo" @if ($r['foto']) style="background-image:url('{{ \App\Support\Berkas::url($r['foto']) }}')" @endif></div>
              <div><strong>{{ $r['nama'] }}</strong><span>{{ $r['nim'] }} &middot; {{ $r['kelas'] ?? '-' }}</span></div>
            </div>
            <div class="rank-col">{{ number_format($r['nilai_akademik'], 2) }}</div>
            <div class="rank-col">{{ number_format($r['prestasi_akademik'], 0) }} poin</div>
            <div class="rank-col">{{ number_format($r['prestasi_non_akademik'], 0) }} poin</div>
            <div class="rank-col">{{ number_format($r['keaktifan_organisasi'], 0) }} poin</div>
            <div class="rank-score">{{ number_format($r['skor'], 2) }}</div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Ranking belum tersedia.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <!-- =================== TESTIMONI (REVISI 26-09-2026) =================== -->
  <section class="section-pad">
    <div class="container">
      <div class="section-head align-left" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; max-width:100%;">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-comment-dots"></i> Testimoni Alumni</span>
          <h2 style="margin-bottom:0;">Kata Alumni</h2>
        </div>
        <a href="{{ url('/testimoni') }}" class="btn btn-outline">Lihat Testimoni Alumni</a>
      </div>
      <div class="grid-3 reveal-stagger">
        @forelse ($daftarTestimoni as $t)
          <div class="card testi-card reveal">
            <p class="testi-quote">&ldquo;{{ \Illuminate\Support\Str::limit($t->isi, 220) }}&rdquo;</p>
            <div class="testi-person">
              <div class="testi-photo" @if ($t->foto_url) style="background-image:url('{{ $t->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif></div>
              <div>
                <strong style="display:block; color:var(--navy-900);">{{ $t->nama }}</strong>
                <span style="font-size:.82rem; color:var(--grey-500);">{{ $t->keterangan_alumni }}</span>
              </div>
            </div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Belum ada testimoni.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <!-- =================== BERITA (REVISI 26-09-2026) =================== -->
  <section class="section-pad bg-soft">
    <div class="container">
      <div class="section-head align-left" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; max-width:100%;">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-newspaper"></i> Berita</span>
          <h2 style="margin-bottom:0;">Informasi Terbaru Program Studi</h2>
        </div>
        <a href="{{ url('/berita') }}" class="btn btn-outline">Lihat Semua Berita</a>
      </div>
      <div class="grid-3 reveal-stagger">
        @forelse ($daftarBerita as $b)
          <a href="{{ route('berita.show', $b) }}" class="card news-card reveal">
            <div class="news-photo" style="background-image:url('{{ $b->gambar_url ?? asset('images/Kodex.png') }}'); @if (! $b->gambar_url) background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50); @endif">
              @if ($b->kategori)<span class="news-cat">{{ $b->kategori }}</span>@endif
            </div>
            <div class="news-body">
              <div class="news-date"><i class="fa-regular fa-calendar"></i> {{ $b->tanggal->translatedFormat('d F Y') }}</div>
              <h3>{{ $b->judul }}</h3>
              <p style="font-size:.9rem;">{{ $b->cuplikan }}</p>
            </div>
          </a>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Belum ada berita.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <!-- =================== LOWONGAN PEKERJAAN (REVISI 26-09-2026) =================== -->
  <section class="section-pad">
    <div class="container">
      <div class="section-head align-left" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; max-width:100%;">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-briefcase"></i> Lowongan Kerja</span>
          <h2 style="margin-bottom:0;">Peluang Karier untuk Mahasiswa &amp; Alumni</h2>
        </div>
        <a href="{{ url('/lowongan-pekerjaan') }}" class="btn btn-outline">Lihat Semua Lowongan</a>
      </div>
      <div class="grid-3 reveal-stagger">
        @forelse ($daftarLowongan as $l)
          <div class="card ta-card reveal">
            <span class="ta-field">{{ $l->tipe ?? 'Lowongan' }}</span>
            <h4>{{ $l->posisi }}</h4>
            <div class="ta-meta"><i class="fa-solid fa-building"></i> {{ $l->perusahaan }}@if ($l->lokasi) &middot; <i class="fa-solid fa-location-dot"></i> {{ $l->lokasi }}@endif</div>
            @if ($l->deskripsi)<p style="font-size:.88rem; margin:0;">{{ \Illuminate\Support\Str::limit($l->deskripsi, 160) }}</p>@endif
            <div class="ta-meta"><i class="fa-regular fa-calendar"></i> Batas lamaran: {{ $l->batas_lamaran ? $l->batas_lamaran->translatedFormat('d F Y') : 'Tidak ditentukan' }}</div>
            <a href="{{ $l->link }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline" style="align-self:flex-start; margin-top:auto;">Lihat Lowongan <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Belum ada lowongan pekerjaan yang dibuka.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <!-- =================== PENGUMUMAN =================== -->
  <section class="section-pad">
    <div class="container">
      <div class="section-head align-left" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; max-width:100%;">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-bullhorn"></i> Pengumuman</span>
          <h2 style="margin-bottom:0;">Pengumuman Mahasiswa Berprestasi</h2>
        </div>
        <a href="{{ url('/pengumuman') }}" class="btn btn-outline">Selengkapnya</a>
      </div>
      <div class="card reveal" style="padding:32px;">
        <p style="margin:0;">Pengumuman pada Program Studi Teknologi Informasi bersifat pribadi: Staff Prodi mengirimkannya melalui email (Gmail) kepada mahasiswa berprestasi yang bersangkutan — bukan berdasarkan urutan ranking. Mahasiswa juga dapat login untuk melihat pengumuman miliknya di dashboard.</p>
      </div>
    </div>
  </section>

  <!-- =================== CTA PENDAFTARAN =================== -->
  <section class="section-pad">
    <div class="container">
      <div class="cta-section reveal">
        <div>
          <h2>Tertarik Menjadi Bagian dari Teknologi Informasi?</h2>
          <p>Daftarkan dirimu sekarang dan mulai perjalananmu membangun karier di dunia teknologi.</p>
        </div>
        <div class="cta-actions">
          <a href="{{ url('/profil') }}" class="btn btn-light">Kenali Prodi TI</a>
          <a href="{{ url('/login') }}" class="btn btn-ghost-white">Masuk Sistem Prodi</a>
        </div>
        <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" class="cta-kodex">
      </div>
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>