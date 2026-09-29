<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>{{ $pengumuman->judul }}</title></head>
<body style="margin:0; padding:24px; background:#F4F7FB; font-family:Arial, Helvetica, sans-serif; color:#0F172A;">
  <div style="max-width:600px; margin:0 auto; background:#fff; border-radius:16px; overflow:hidden; border:1px solid #E2E8F0;">
    <div style="background:#062B60; color:#fff; padding:20px 28px;">
      <strong style="font-size:15px;">POLITALA</strong><br>
      <span style="font-size:12px; opacity:.75;">Program Studi Teknologi Informasi</span>
    </div>
    <div style="padding:28px;">
      <p style="margin:0 0 6px 0; font-size:12px; color:#64748B;">Pengumuman Mahasiswa Berprestasi &middot; {{ $pengumuman->kategori ?? '-' }}</p>
      <h2 style="margin:0 0 18px 0; font-size:20px;">{{ $pengumuman->judul }}</h2>
      <p style="margin:0 0 14px 0;">Yth. {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }}),</p>
      <div style="font-size:14px; line-height:1.7;">{!! nl2br(e($pengumuman->isi)) !!}</div>
      @if ($pengumuman->prestasi)
        <p style="margin:18px 0 0 0; font-size:13px; color:#475569;">Prestasi terkait: <strong>{{ $pengumuman->prestasi->judul }}</strong></p>
      @endif
      <p style="margin:24px 0 0 0;">
        <a href="{{ url('/mahasiswa-pengumuman') }}" style="display:inline-block; background:#1769AA; color:#fff; text-decoration:none; padding:10px 20px; border-radius:10px; font-size:14px;">Lihat di Dashboard Mahasiswa</a>
      </p>
    </div>
    <div style="padding:16px 28px; font-size:11px; color:#94A3B8; border-top:1px solid #E2E8F0;">
      Email ini dikirim otomatis oleh sistem Program Studi Teknologi Informasi kepada mahasiswa berprestasi penerima pengumuman.
    </div>
  </div>
</body>
</html>
