{{-- Isian form pengumuman. Pilihan mahasiswa & prestasi berasal dari database.
     REVISI 24-09 & 26-09-2026: penerima = mahasiswa berprestasi (prestasi disetujui), bukan berdasarkan ranking. --}}
<div class="form-grid">
  <div class="form-group full">
    <label>Pilih Mahasiswa Berprestasi Penerima *</label>
    <select name="nim" required>
      <option value="">-- Pilih mahasiswa berprestasi --</option>
      @foreach ($daftarMahasiswa as $m)
        <option value="{{ $m->nim }}">{{ $m->nama }} - {{ $m->nim }}</option>
      @endforeach
    </select>
    <div class="form-hint">Daftar hanya berisi mahasiswa berprestasi (memiliki prestasi yang sudah disetujui Staff Prodi). Pengumuman hanya diterima oleh mahasiswa yang dipilih; saat status Terkirim, pengumuman juga dikirim ke email mahasiswa (Gmail).</div>
  </div>

  <div class="form-group full">
    <label>Kategori Prestasi *</label>
    <select name="kategori" required>
      <option value="">-- Pilih kategori --</option>
      @foreach (\App\Models\Pengumuman::daftarKategori() as $k)
        <option value="{{ $k }}">{{ $k }}</option>
      @endforeach
    </select>
    <div class="form-hint">Kategori mengikuti jenis prestasi yang diraih mahasiswa (bukan hasil ranking).</div>
  </div>

  <div class="form-group full">
    <label>Terkait Prestasi (opsional)</label>
    <select name="prestasi_id">
      <option value="">-- Tidak terkait prestasi --</option>
      @foreach ($daftarPrestasi as $p)
        <option value="{{ $p->id_prestasi }}">{{ $p->judul }} — {{ $p->mahasiswa->nama ?? '-' }} ({{ $p->kategori }}, {{ $p->tingkat }})</option>
      @endforeach
    </select>
    <div class="form-hint">Pilih prestasi milik mahasiswa penerima bila pengumuman berkaitan dengan prestasi tertentu. Kategori otomatis mengikuti prestasi yang dipilih.</div>
  </div>

  <div class="form-group full"><label>Judul *</label><input name="judul" required></div>
  <div class="form-group full"><label>Isi Pengumuman *</label><textarea name="isi" rows="4" required></textarea></div>

  <div class="form-group">
    <label>Status *</label>
    <select name="status" required>
      <option value="terkirim">Terkirim</option>
      <option value="draft">Draft</option>
    </select>
    <div class="form-hint">Draft belum terlihat oleh mahasiswa.</div>
  </div>
</div>
