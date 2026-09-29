<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Prodi | Program Studi Teknologi Informasi</title>
<meta name="description" content="Profil Program Studi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  @include('partials.public-navbar')

  <section class="page-hero">
    <div class="container">
      <h1>Profil Program Studi</h1>
      <p>Tentang, akreditasi, struktur organisasi, dan dosen pengajar Program Studi Teknologi Informasi.</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">Beranda</a><span class="sep">/</span><span class="current">Profil</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="grid-2" style="align-items:center;">
        <div class="reveal">
          <span class="eyebrow"><i class="fa-solid fa-circle-info"></i> Pengenalan</span>
          <h2>Membentuk Talenta Digital Masa Depan</h2>
          <p id="bindDeskripsi">{{ $prodi?->deskripsi ?: 'Profil Program Studi belum diisi oleh Staff Prodi.' }}</p>
          <p>Melalui pembelajaran berbasis project, mahasiswa dibekali kemampuan praktis yang relevan dengan kebutuhan industri teknologi saat ini.</p>
        </div>
        <div class="reveal text-center">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex" style="width:220px; margin:0 auto; animation:floaty 4.5s ease-in-out infinite;">
        </div>
      </div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      <div class="stats-grid" style="background:none;">
        <div class="card stat-card reveal" style="background:var(--grey-50); color:var(--navy-900); border-color:var(--grey-100);"><div class="stat-num" style="color:var(--blue-600);"><span id="statMahasiswa" data-counter-plain="{{ $jumlahMahasiswa }}">0</span></div><div class="stat-label" style="color:var(--grey-500);">MAHASISWA AKTIF</div></div>
        <div class="card stat-card reveal" style="background:var(--grey-50); color:var(--navy-900); border-color:var(--grey-100);"><div class="stat-num" style="color:var(--blue-600);"><span id="statDosen" data-counter-plain="{{ $jumlahDosen }}">0</span></div><div class="stat-label" style="color:var(--grey-500);">DOSEN TETAP</div></div>
        <div class="card stat-card reveal" style="background:var(--grey-50); color:var(--navy-900); border-color:var(--grey-100);"><div class="stat-num" style="color:var(--blue-600);"><span id="statAlumni" data-counter-plain="{{ $jumlahAlumni }}">0</span></div><div class="stat-label" style="color:var(--grey-500);">ALUMNI</div></div>
        <div class="card stat-card reveal" style="background:var(--grey-50); color:var(--navy-900); border-color:var(--grey-100);"><div class="stat-num" style="color:var(--blue-600);"><span id="statPrestasi" data-counter-plain="{{ $jumlahPrestasi }}">0</span></div><div class="stat-label" style="color:var(--grey-500);">PRESTASI DISETUJUI</div></div>
      </div>
    </div>
  </section>

  <!-- =================== VISI & MISI (REVISI 27-09-2026: bagian dari halaman Tentang) =================== -->
  <section class="section-pad" id="visi-misi">
    <div class="container">
      <div class="card reveal" style="padding:44px; text-align:center; max-width:820px; margin:0 auto 50px auto;">
        <span class="eyebrow"><i class="fa-solid fa-eye"></i> Visi</span>
        <h2 id="bindVisi">{{ $prodi?->visi ?: 'Visi Program Studi belum diisi oleh Staff Prodi.' }}</h2>
      </div>
      <div class="section-head"><span class="eyebrow"><i class="fa-solid fa-list-check"></i> Misi</span><h2>Misi Program Studi</h2></div>
      <div class="grid-2 reveal-stagger" id="bindMisi">
        @forelse ($prodi?->misi_list ?? [] as $i => $misi)
          <div class="card benefit-card reveal">
            <div class="benefit-icon">{{ $i + 1 }}</div>
            <p style="margin:0;">{{ $misi }}</p>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Misi Program Studi belum diisi oleh Staff Prodi.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="section-pad bg-soft">
    <div class="container">
      <div class="grid-3 reveal-stagger">
        <a href="{{ url('/struktur-organisasi') }}" class="card link-card reveal">
          <div class="benefit-icon"><i class="fa-solid fa-sitemap"></i></div>
          <h3>Struktur Organisasi</h3>
          <p>Koordinator Program Studi, Koordinator Gugus TEFA, dan pengelola Program Studi.</p>
          <span class="go">Selengkapnya <i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a href="{{ url('/prospek-lulusan') }}" class="card link-card reveal">
          <div class="benefit-icon"><i class="fa-solid fa-briefcase"></i></div>
          <h3>Prospek Lulusan</h3>
          <p>Peluang karier digital yang terbuka bagi lulusan Teknologi Informasi.</p>
          <span class="go">Selengkapnya <i class="fa-solid fa-arrow-right"></i></span>
        </a>
        <a href="{{ url('/akreditasi') }}" class="card link-card reveal">
          <div class="benefit-icon"><i class="fa-solid fa-certificate"></i></div>
          <h3>Akreditasi</h3>
          <p>Status, peringkat, dan masa berlaku akreditasi Program Studi.</p>
          <span class="go">Selengkapnya <i class="fa-solid fa-arrow-right"></i></span>
        </a>
      </div>
    </div>
  </section>

  {{-- ===== REVISI 26-09-2026: Akreditasi, Struktur Organisasi, dan Dosen Pengajar
       menjadi satu bagian Profil Program Studi (komponen visual yang sudah ada). ===== --}}

  <!-- =================== AKREDITASI (atribut Profil Prodi) =================== -->
  <section class="section-pad bg-grey" id="akreditasi">
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

  <!-- =================== STRUKTUR ORGANISASI =================== -->
  <section class="section-pad" id="struktur-organisasi">
    <div class="container">
      <div class="section-head align-left" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; max-width:100%;">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-sitemap"></i> Struktur Organisasi</span>
          <h2 style="margin-bottom:0;">Pengelola Program Studi</h2>
        </div>
        <a href="{{ url('/struktur-organisasi') }}" class="btn btn-outline">Lihat Struktur Organisasi</a>
      </div>
      <div class="grid-4 reveal-stagger">
        @forelse ($strukturOrganisasi as $s)
          <div class="card person-card reveal">
            <div class="person-photo" @if ($s->foto_url) style="background-image:url('{{ $s->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif></div>
            <div class="person-body">
              <h4>{{ $s->nama_pejabat }}</h4>
              <div class="person-meta">{{ $s->jabatan }}</div>
            </div>
          </div>
        @empty
          <div class="empty-public">
            <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
            <p>Struktur organisasi belum diisi oleh Staff Prodi.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <!-- =================== DOSEN PENGAJAR =================== -->
  <section class="section-pad bg-soft" id="dosen-pengajar">
    <div class="container">
      <div class="section-head align-left" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; max-width:100%;">
        <div>
          <span class="eyebrow"><i class="fa-solid fa-chalkboard-user"></i> Dosen Pengajar</span>
          <h2 style="margin-bottom:0;">Tenaga Pengajar Program Studi</h2>
        </div>
        <a href="{{ url('/dosen') }}" class="btn btn-outline">Lihat Semua Dosen Pengajar</a>
      </div>
      <div class="tabel-publik-wrap reveal">
        <table class="tabel-publik">
          <thead><tr>
            <th>No</th><th>Nama Dosen</th><th>Status</th>
            <th class="kolom-opsional">Pendidikan Terakhir</th><th>Aksi</th>
          </tr></thead>
          <tbody>
            @forelse ($daftarDosen as $dosen)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <div class="rank-person">
                    <div class="rank-photo" @if ($dosen->foto_url) style="background-image:url('{{ $dosen->foto_url }}')" @endif></div>
                    <div><strong>{{ $dosen->nama }}</strong><span>{{ $dosen->email ?? '-' }}</span></div>
                  </div>
                </td>
                <td><span class="label-kriteria status-{{ $dosen->status }}">{{ $dosen->label_status }}</span></td>
                <td class="kolom-opsional">{{ $dosen->pendidikan_terakhir ?? '-' }}</td>
                <td><a href="{{ url('/dosen') }}#modalDosen{{ $dosen->nuptk }}" class="btn btn-sm btn-outline" style="white-space:nowrap;">Lihat Detail</a></td>
              </tr>
            @empty
              <tr class="empty-row"><td colspan="5">Belum ada data dosen.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  @include('partials.public-footer')
  <button class="back-to-top" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>