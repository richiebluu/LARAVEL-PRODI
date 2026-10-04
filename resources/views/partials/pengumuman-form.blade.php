@php
  $penerimaAwal = $penerimaAwal ?? collect();
  $pakaiOld = $pakaiOld ?? false;
@endphp
<div class="form-grid">
  <div class="form-group full">
    <label>Penerima (Mahasiswa Berprestasi) *</label>
    <div class="penerima-picker" data-pilih-penerima
         data-url="{{ route('staff-pengumuman.cari-mahasiswa') }}"
         data-domain="{{ $domainEmail }}">
      <div class="penerima-box" data-penerima-box>
        @foreach ($penerimaAwal as $m)
          <span class="penerima-chip" data-nim="{{ $m->nim }}" title="{{ $m->email_kontak }}">
            <span class="penerima-avatar">{{ Str::upper(Str::substr($m->nama, 0, 1)) }}</span>
            <span class="penerima-teks"><b>{{ $m->nama }}</b><small>{{ $m->email_kontak }}</small></span>
            <input type="hidden" name="penerima[]" value="{{ $m->nim }}">
            <button type="button" class="penerima-hapus" aria-label="Hapus {{ $m->nama }}">&times;</button>
          </span>
        @endforeach
        <input type="text" class="penerima-input" data-penerima-input autocomplete="off"
               placeholder="Ketik email mahasiswa... (contoh: nama&#64;{{ $domainEmail }})"
               aria-label="Cari mahasiswa berdasarkan email">
      </div>
      <div class="penerima-saran" data-penerima-saran role="listbox" hidden></div>
      <div class="penerima-info" data-penerima-info>
        <span data-penerima-jumlah>{{ $penerimaAwal->count() }}</span> mahasiswa dipilih
      </div>
    </div>
    <div class="form-hint">
      Cari dengan email <strong>&#64;{{ $domainEmail }}</strong> (juga bisa nama atau NIM). Hanya mahasiswa berprestasi
      (memiliki prestasi yang sudah disetujui) yang dapat dipilih. Klik &times; untuk menghapus penerima.
      Saat status Terkirim, setiap penerima mendapat pengumuman di dashboard (menu Pengumuman) dan email (Gmail).
    </div>
  </div>

  <div class="form-group full">
    <label>Kategori Prestasi *</label>
    <select name="kategori" required>
      <option value="">-- Pilih kategori --</option>
      @foreach (\App\Models\Pengumuman::daftarKategori() as $k)
        <option value="{{ $k }}" @selected($pakaiOld && old('kategori') === $k)>{{ $k }}</option>
      @endforeach
    </select>
    <div class="form-hint">Kategori mengikuti jenis prestasi yang diraih mahasiswa (bukan hasil ranking).</div>
  </div>

  <div class="form-group full">
    <label>Terkait Prestasi (opsional)</label>
    <select name="prestasi_id" data-prestasi-penerima>
      <option value="">-- Tidak terkait prestasi --</option>
      @foreach ($daftarPrestasi as $p)
        <option value="{{ $p->id_prestasi }}" data-nim="{{ $p->nim }}" @selected($pakaiOld && (string) old('prestasi_id') === (string) $p->id_prestasi)>{{ $p->judul }} — {{ $p->mahasiswa->nama ?? '-' }} ({{ $p->kategori }}, {{ $p->tingkat }})</option>
      @endforeach
    </select>
    <div class="form-hint">Hanya prestasi milik mahasiswa penerima yang dapat dipilih. Kategori otomatis mengikuti prestasi yang dipilih.</div>
  </div>

  <div class="form-group full"><label>Judul *</label><input name="judul" required value="{{ $pakaiOld ? old('judul') : '' }}"></div>
  <div class="form-group full"><label>Isi Pengumuman *</label><textarea name="isi" rows="4" required>{{ $pakaiOld ? old('isi') : '' }}</textarea></div>

  <div class="form-group">
    <label>Status *</label>
    <select name="status" required>
      <option value="terkirim" @selected($pakaiOld && old('status') === 'terkirim')>Terkirim</option>
      <option value="draft" @selected($pakaiOld && old('status') === 'draft')>Draft</option>
    </select>
    <div class="form-hint">Draft belum terlihat oleh mahasiswa.</div>
  </div>
</div>
