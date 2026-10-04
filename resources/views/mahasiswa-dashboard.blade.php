<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Dashboard Mahasiswa</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="mahasiswa-dashboard">
  <div class="admin-shell">

  {{-- Sidebar --}}
  @include('partials.mahasiswa-sidebar')
    <div class="main-area">

      {{-- Header --}}
      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Dashboard</h1>
            <div class="subtitle">Ringkasan nilai kriteria, prestasi, dan pengumuman.</div>
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
      @if (! $mahasiswa)
        <div class="alert-error"><strong>Akun Anda belum terhubung ke data mahasiswa.</strong> Hubungi Staff Prodi.</div>
      @endif

      <div class="profile-head">
        <div class="p-photo" id="pFoto" @if ($mahasiswa?->foto_url) style="background-image:url('{{ $mahasiswa->foto_url }}')" @endif></div>
        <div>
          <h2 id="pNama">{{ $mahasiswa->nama ?? auth()->user()->name }}</h2>
          <p id="pMeta">{{ $mahasiswa ? $mahasiswa->nim.' · '.($mahasiswa->kelas ?? '-').' · Angkatan '.($mahasiswa->angkatan ?? '-') : '-' }}</p>
        </div>
      </div>

      <div class="astats-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-graduation-cap"></i></div><div class="astat-num" id="statIpk">{{ $mahasiswa?->ipk !== null ? number_format($mahasiswa->ipk, 2) : '-' }}</div><div class="astat-label">Nilai Akademik (IPK)</div></div>
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-trophy"></i></div><div class="astat-num" id="statPrestasi">{{ $jumlahDisetujui }}</div><div class="astat-label">Prestasi Disetujui</div></div>
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-ranking-star"></i></div><div class="astat-num" id="statRank">{{ $peringkat ? '#'.$peringkat : '-' }}</div><div class="astat-label">Peringkat Saat Ini</div></div>
        <div class="astat-card"><div class="astat-icon"><i class="fa-solid fa-hourglass-half"></i></div><div class="astat-num" id="statPending">{{ $jumlahMenunggu }}</div><div class="astat-label">Menunggu Verifikasi</div></div>
      </div>

      <div class="kodex-tip">
        <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
        <div><strong>Kodex di sini!</strong><p>Prestasi kamu tampil di halaman publik setelah disetujui Staff Prodi dan poinnya ikut dihitung pada ranking.</p></div>
      </div>

      @if ($nilaiKriteria)
        <div class="panel">
          <div class="panel-head"><h2>Nilai Kriteria Penilaian</h2><a href="{{ url('/ranking') }}" class="btn btn-outline btn-sm">Lihat Ranking</a></div>
          <div class="table-wrap">
            {{-- Tabel --}}
            <table class="data-table">
              <thead><tr><th>Kriteria</th><th>Sumber Data</th><th>Nilai / Poin</th><th>Bobot</th></tr></thead>
              <tbody>
                @foreach (config('saw.kriteria') as $kode => $k)
                  @php $kolom = strtolower($kode); @endphp
                  <tr>
                    <td><strong>{{ $k['nama'] }}</strong></td>
                    <td>
                      {{ $k['sumber'] }}
                      @if ($kode === 'C4' && $daftarOrganisasi->isNotEmpty())
                        <div style="font-size:.78rem; color:var(--grey-500);">{{ $daftarOrganisasi->map(fn ($o) => $o->nama_organisasi.' ('.$o->jabatan.')')->implode(', ') }}</div>
                      @endif
                    </td>
                    <td>{{ $kode === 'C1' ? number_format($nilaiKriteria[$kolom], 2) : number_format($nilaiKriteria[$kolom], 0).' poin' }}</td>
                    <td>{{ (int) round($k['bobot'] * 100) }}%</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <p class="form-hint" style="margin-top:12px;">Poin prestasi hanya dihitung dari prestasi yang sudah disetujui Staff Prodi. Peringkat resmi mengikuti perhitungan ranking terakhir.</p>
        </div>
      @endif
      <div class="two-col">
        <div class="panel">
          <div class="panel-head"><h2>Prestasi Terbaru</h2><a href="{{ url('/mahasiswa-prestasi') }}" class="btn btn-outline btn-sm">Lihat Semua</a></div>
          <div id="listPrestasi">
            @forelse ($prestasiTerbaru as $p)
              @php $warna = ['menunggu'=>'badge-cyan','disetujui'=>'badge-green','ditolak'=>'badge-red'][$p->status] ?? 'badge-grey'; @endphp
              <div class="notif-item">
                <div class="notif-ico"><i class="fa-solid fa-trophy"></i></div>
                <div style="flex:1;"><strong>{{ $p->judul }}</strong>
                  <p>{{ $p->kategori }} &middot; Tingkat {{ $p->tingkat ?? '-' }} &middot; {{ $p->poin }} poin</p></div>
                <span class="badge {{ $warna }}">{{ $p->label_status }}</span>
              </div>
            @empty
              <div class="empty-state"><i class="fa-solid fa-trophy"></i><p>Belum ada prestasi. Ajukan prestasi pertamamu.</p></div>
            @endforelse
          </div>
        </div>
        <div class="panel">
          <div class="panel-head"><h2>Pengumuman Terbaru @if ($jumlahBelumDibaca)<span class="badge badge-cyan" style="vertical-align:middle;">{{ $jumlahBelumDibaca }} baru</span>@endif</h2><a href="{{ url('/mahasiswa-pengumuman') }}" class="btn btn-outline btn-sm">Lihat Semua</a></div>
          <div id="listPengumuman">
            @forelse ($daftarPengumuman as $item)
              <div class="notif-item {{ $item->belum_dibaca ? 'unread' : '' }}">
                <div class="notif-ico"><i class="fa-solid {{ $item->status === \App\Models\Pengumuman::STATUS_NOTIFIKASI ? 'fa-circle-info' : 'fa-bullhorn' }}"></i></div>
                <div style="flex:1;">
                  <strong>{{ $item->judul }}</strong>
                  <p>{{ \Illuminate\Support\Str::limit($item->status === \App\Models\Pengumuman::STATUS_NOTIFIKASI ? ($item->notifikasi ?? $item->isi) : $item->isi, 110) }}</p>
                  <span class="notif-time">{{ ($item->tanggal_dikirim ?? $item->created_at)?->diffForHumans() }}</span>
                </div>
              </div>
            @empty
              <div class="empty-state"><i class="fa-solid fa-bullhorn"></i><p>Belum ada pengumuman untuk Anda.</p></div>
            @endforelse
          </div>
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
