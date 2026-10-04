<div class="form-grid baris-organisasi" data-baris>
  <div class="form-group"><label>Nama Organisasi *</label>
    <input name="organisasi[{{ $i }}][nama_organisasi]" value="{{ $baris['nama_organisasi'] ?? '' }}" maxlength="150" placeholder="Contoh: HIMA TI" required></div>
  <div class="form-group"><label>Jabatan Organisasi *</label>
    <select name="organisasi[{{ $i }}][jabatan]" required>
      <option value="">-- Pilih jabatan --</option>
      @foreach (\App\Models\Organisasi::daftarJabatan() as $jabatan => $poin)
        <option value="{{ $jabatan }}" @selected(($baris['jabatan'] ?? '') === $jabatan)>{{ $jabatan }} ({{ $poin }} poin)</option>
      @endforeach
    </select></div>
  <div class="form-group"><button type="button" class="btn btn-danger btn-sm btn-icon" data-hapus-baris title="Hapus baris" aria-label="Hapus baris"><i class="fa-solid fa-xmark"></i></button></div>
</div>
