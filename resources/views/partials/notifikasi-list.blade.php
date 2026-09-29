{{-- Daftar notifikasi. Sumber data (ERD): tabel pengumuman, kolom nim + notifikasi + dibaca_pada. --}}
@forelse ($daftar as $n)
  <div class="notif-item {{ $n->belum_dibaca ? 'unread' : '' }}">
    <div class="notif-ico"><i class="fa-solid fa-bell"></i></div>
    <div style="flex:1;">
      <strong>{{ $n->judul_notifikasi }}</strong>
      <p>{{ $n->notifikasi }}</p>
      <span class="notif-time">{{ ($n->tanggal_dikirim ?? $n->created_at)?->diffForHumans() }}</span>
    </div>
  </div>
@empty
  <div class="empty-state"><i class="fa-solid fa-bell-slash"></i><p>Belum ada notifikasi.</p></div>
@endforelse
