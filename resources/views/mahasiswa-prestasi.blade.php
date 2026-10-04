<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prestasi Saya | Dashboard Mahasiswa</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="mahasiswa-prestasi">
  <div class="admin-shell">

  {{-- Sidebar --}}
  @include('partials.mahasiswa-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Prestasi Saya</h1>
            <div class="subtitle">Status setiap pengajuan prestasi kamu.</div>
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
      <div class="tab-bar">
        <a class="tab-btn {{ ! $status ? 'active' : '' }}" href="{{ route('mahasiswa-prestasi') }}">Semua</a>
        <a class="tab-btn {{ $status === 'menunggu' ? 'active' : '' }}" href="{{ route('mahasiswa-prestasi', ['status' => 'menunggu']) }}">Menunggu Verifikasi</a>
        <a class="tab-btn {{ $status === 'disetujui' ? 'active' : '' }}" href="{{ route('mahasiswa-prestasi', ['status' => 'disetujui']) }}">Disetujui</a>
        <a class="tab-btn {{ $status === 'ditolak' ? 'active' : '' }}" href="{{ route('mahasiswa-prestasi', ['status' => 'ditolak']) }}">Ditolak</a>
      </div>
      <div id="listPrestasi">
        @forelse ($daftarPrestasi as $p)
          @php $warna = ['menunggu'=>'badge-cyan','disetujui'=>'badge-green','ditolak'=>'badge-red'][$p->status] ?? 'badge-grey'; @endphp
          <div class="panel" style="padding:20px 22px; margin-bottom:14px;">
            <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">
              <div style="flex:1; min-width:240px;">
                <strong style="font-family:var(--font-display); color:var(--navy-900);">{{ $p->judul }}</strong>
                <p style="font-size:.85rem; color:var(--grey-500); margin:6px 0 0 0;">{{ $p->penyelenggara ?? '-' }} &middot; {{ optional($p->tanggal)->translatedFormat('d F Y') }}</p>
                <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                  <span class="badge badge-blue">{{ $p->kategori }}</span>
                  <span class="badge badge-cyan">Tingkat {{ $p->tingkat ?? '-' }}</span>
                  <span class="badge badge-grey">{{ $p->poin }} poin</span>
                  @if ($p->dokumen_url)
                    <a class="badge badge-grey" href="{{ $p->dokumen_url }}" target="_blank" rel="noopener"><i class="fa-solid fa-paperclip"></i> Lihat sertifikat</a>
                  @endif
                </div>
                @if ($p->status === 'ditolak' && $p->catatan)
                  <p style="font-size:.82rem; color:#DC2626; margin-top:10px;"><i class="fa-solid fa-circle-info"></i> Alasan penolakan: {{ $p->catatan }}</p>
                @endif
              </div>
              <span class="badge {{ $warna }}">{{ $p->label_status }}</span>
            </div>
          </div>
        @empty
          <div class="empty-state">
            <i class="fa-solid fa-trophy"></i>
            <p>Belum ada prestasi pada status ini.
              <a href="{{ route('mahasiswa-ajukan-prestasi') }}" style="color:var(--blue-600); font-weight:600;">Ajukan prestasi</a>
            </p>
          </div>
        @endforelse
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
