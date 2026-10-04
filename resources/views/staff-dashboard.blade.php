<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-dashboard">
  <div class="admin-shell">

  {{-- Sidebar --}}
  @include('partials.staff-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Dashboard</h1>
            <div class="subtitle">Ringkasan pengelolaan data Program Studi Teknologi Informasi.</div>
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
      <div class="astats-grid">
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-user-graduate"></i></div><div class="astat-num" id="statMahasiswa">{{ $ringkasan['mahasiswa'] }}</div><div class="astat-label">Total Mahasiswa</div></div>
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-chalkboard-user"></i></div><div class="astat-num" id="statDosen">{{ $ringkasan['dosen'] }}</div><div class="astat-label">Total Dosen</div></div>
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-star"></i></div><div class="astat-num" id="statPrestasi">{{ $ringkasan['prestasiDisetujui'] }}</div><div class="astat-label">Prestasi Disetujui</div></div>
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-hourglass-half"></i></div><div class="astat-num" id="statPengajuan">{{ $ringkasan['prestasiMenunggu'] }}</div><div class="astat-label">Prestasi Menunggu Verifikasi</div></div>
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-newspaper"></i></div><div class="astat-num" id="statBerita">{{ $ringkasan['beritaTerbit'] }}</div><div class="astat-label">Berita Terbit</div></div>
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-medal"></i></div><div class="astat-num" id="statBerprestasi">{{ $ringkasan['mahasiswaBerprestasi'] }}</div><div class="astat-label">Mahasiswa Berprestasi</div></div>
      </div>

        <div class="panel">
          <div class="panel-head"><h2>Aktivitas Terbaru</h2></div>
          <div class="activity-list" id="listAktivitas">
            @forelse ($aktivitas as $a)
              <div class="activity-item">
                <div class="activity-dot"><i class="fa-solid {{ $a['icon'] }}"></i></div>
                <div>
                  <div class="activity-text">{{ $a['text'] }}</div>
                  <div class="activity-time">{{ $a['waktu']->diffForHumans() }}</div>
                </div>
              </div>
            @empty
              <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p>Belum ada aktivitas.</p>
              </div>
            @endforelse
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Flash Message --}}
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
