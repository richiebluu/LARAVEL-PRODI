<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Prestasi | Dashboard Staff Prodi</title>
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-page="staff-prestasi">
  <div class="admin-shell">

  @include('partials.staff-sidebar')
    <div class="main-area">

      <header class="topbar">
        <div style="display:flex; align-items:center;">
          <button class="sidebar-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
          <div>
            <h1>Data Prestasi</h1>
            <div class="subtitle">Tinjau bukti, kategori, dan tingkat prestasi untuk perhitungan poin.</div>
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
      @if ($errors->any())
        <div class="alert-error">
          <strong><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</strong>
        </div>
      @endif

      <div class="panel">
        <div class="toolbar">
          <div>
            <h2 style="margin:0;">Data Prestasi <span style="color:var(--grey-500); font-weight:500; font-size:.85rem;">(<span id="crudCount">{{ $jumlah }}</span> data)</span></h2>
          </div>
          <form method="GET" action="{{ route('staff-prestasi') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="crudSearch" name="q" value="{{ $cari }}" placeholder="Cari prestasi / mahasiswa..."></div>
            <select id="filterStatus" name="status" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
              <option value="">Semua prestasi</option>
              <option value="menunggu" @selected($status === 'menunggu')>Menunggu Verifikasi</option>
              <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
            </select>
            <select name="kategori" data-auto-submit style="padding:10px 14px; border-radius:10px; border:1.5px solid var(--grey-300); font-size:.85rem;">
              <option value="">Semua kategori</option>
              @foreach (\App\Models\Prestasi::KATEGORI as $k)
                <option value="{{ $k }}" @selected($kategori === $k)>{{ $k }}</option>
              @endforeach
            </select>
          </form>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Mahasiswa</th><th>Prestasi</th><th>Kategori</th><th>Tingkat Prestasi</th><th>Poin</th><th>Tanggal</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody id="crudTableBody">
              @forelse ($daftarPrestasi as $p)

                <tr>
                  <td><strong>{{ $p->mahasiswa->nama ?? '-' }}</strong><div style="font-size:.78rem; color:var(--grey-500);">{{ $p->mahasiswa->nim ?? '' }}</div></td>
                  <td>{{ $p->judul }}</td>
                  <td><span class="badge badge-blue">{{ $p->kategori }}</span></td>
                  <td>{{ $p->tingkat ?? '-' }}</td>
                  {{-- Label "Approved/Disetujui" dihapus sesuai revisi dosen. Prestasi yang sudah
                       diverifikasi cukup ditampilkan poinnya; hanya status menunggu/ditolak yang diberi label. --}}
                  <td>
                    <span class="badge badge-blue" style="white-space:nowrap;">{{ $p->poin }} poin</span>
                    @if ($p->status === 'menunggu')
                      <span class="badge badge-cyan">Menunggu Verifikasi</span>
                    @elseif ($p->status === 'ditolak')
                      <span class="badge badge-red">Ditolak</span>
                    @endif
                  </td>
                  <td>{{ optional($p->tanggal)->translatedFormat('d F Y') ?? '-' }}</td>
                  <td class="actions">
                    <button class="btn btn-outline btn-sm btn-icon" title="Lihat detail" data-modal-open="modalPrestasi{{ $p->id_prestasi }}"><i class="fa-solid fa-eye"></i></button>
                    @if ($p->status !== 'disetujui')
                      <form method="POST" action="{{ route('staff-prestasi.verifikasi', $p) }}" style="display:inline;"
                            data-konfirmasi="Setujui prestasi ini? Poinnya akan dihitung pada ranking dan tampil di halaman publik.">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="disetujui">
                        <button type="submit" class="btn btn-primary btn-sm">Setujui</button>
                      </form>
                    @endif
                    @if ($p->status !== 'ditolak')
                      <button class="btn btn-danger btn-sm" data-modal-open="modalTolak{{ $p->id_prestasi }}">Tolak</button>
                    @endif
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="7"><i class="fa-solid fa-inbox" style="font-size:1.4rem; display:block; margin-bottom:10px; color:var(--grey-300);"></i>Belum ada data prestasi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($daftarPrestasi->hasPages())
          <div class="toolbar" style="margin:18px 0 0 0;">
            <span class="form-hint">Halaman {{ $daftarPrestasi->currentPage() }} dari {{ $daftarPrestasi->lastPage() }}</span>
            <div style="display:flex; gap:8px;">
              <a class="btn btn-outline btn-sm" href="{{ $daftarPrestasi->previousPageUrl() ?? '#' }}"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</a>
              <a class="btn btn-outline btn-sm" href="{{ $daftarPrestasi->nextPageUrl() ?? '#' }}">Berikutnya <i class="fa-solid fa-chevron-right"></i></a>
            </div>
          </div>
        @endif
      </div>

      @foreach ($daftarPrestasi as $p)
        <div class="modal-overlay" id="modalPrestasi{{ $p->id_prestasi }}">
          <div class="modal-box">
            <div class="modal-head"><h3>Detail Pengajuan Prestasi</h3>
              <button class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
            <div class="modal-body">
              <dl class="kv">
                <dt>Mahasiswa</dt><dd>{{ $p->mahasiswa->nama ?? '-' }}</dd>
                <dt>NIM</dt><dd>{{ $p->mahasiswa->nim ?? '-' }}</dd>
                <dt>Prestasi</dt><dd>{{ $p->judul }}</dd>
                <dt>Kategori</dt><dd>{{ $p->kategori }}</dd>
                <dt>Tingkat Prestasi</dt><dd>{{ $p->tingkat ?? '-' }}</dd>
                <dt>Poin</dt><dd>{{ $p->poin }} poin</dd>
                <dt>Penyelenggara</dt><dd>{{ $p->penyelenggara ?? '-' }}</dd>
                <dt>Tanggal</dt><dd>{{ optional($p->tanggal)->translatedFormat('d F Y') ?? '-' }}</dd>
                <dt>Diajukan</dt><dd>{{ $p->created_at?->translatedFormat('d F Y H:i') }}</dd>
              </dl>
              <p style="font-size:.86rem; margin-top:16px;">{{ $p->deskripsi ?? '-' }}</p>
              @if ($p->catatan)
                <p style="font-size:.82rem; color:#DC2626; margin-top:10px;"><i class="fa-solid fa-circle-info"></i> Catatan: {{ $p->catatan }}</p>
              @endif
              @if ($p->dokumen_url)
                <a class="dropzone" style="margin-top:14px; display:block; text-decoration:none;" href="{{ $p->dokumen_url }}" target="_blank" rel="noopener">
                  <strong><i class="fa-solid fa-paperclip"></i> Lihat berkas bukti</strong>Berkas diunggah mahasiswa dan tersimpan di server.
                </a>
              @else
                <div class="dropzone" style="margin-top:14px;"><strong><i class="fa-solid fa-paperclip"></i> Tidak ada berkas bukti</strong>Mahasiswa tidak melampirkan dokumen.</div>
              @endif
            </div>
            <div class="modal-foot">
              <form method="POST" action="{{ route('staff-prestasi.destroy', $p) }}" data-konfirmasi="Hapus prestasi ini secara permanen?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
              </form>
              <button class="btn btn-outline" data-modal-close>Tutup</button>
            </div>
          </div>
        </div>

        <div class="modal-overlay" id="modalTolak{{ $p->id_prestasi }}">
          <div class="modal-box">
            <form method="POST" action="{{ route('staff-prestasi.verifikasi', $p) }}">
              @csrf
              @method('PUT')
              <input type="hidden" name="status" value="ditolak">
              <div class="modal-head"><h3>Tolak pengajuan ini?</h3>
                <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
              <div class="modal-body">
                <div class="form-group">
                  <label>Alasan penolakan</label>
                  <textarea name="catatan" rows="3" placeholder="Contoh: bukti sertifikat tidak terbaca"></textarea>
                  <div class="form-hint">Alasan akan terlihat oleh mahasiswa pada halaman Prestasi Saya.</div>
                </div>
              </div>
              <div class="modal-foot">
                <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-xmark"></i> Tolak Pengajuan</button>
              </div>
            </form>
          </div>
        </div>
      @endforeach

      </div>
    </div>
  </div>

  <!-- Flash message dari session Laravel (ditampilkan sebagai toast) -->
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
