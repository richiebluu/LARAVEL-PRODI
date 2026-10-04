@php
  $alias = $alias ?? [];
  $gagalImpor = session('impor_gagal') && $errors->has('berkas');
@endphp
{{-- Modal --}}
<div class="modal-overlay" id="{{ $id }}" @if ($gagalImpor) data-buka-otomatis @endif>
  <div class="modal-box impor-box">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data"
          data-impor-csv
          data-kolom='@json(array_keys($kolom))'
          data-wajib='@json($wajib)'
          data-alias='@json($alias, JSON_FORCE_OBJECT)'
          data-kunci="{{ $kunci }}">
      @csrf
      <div class="modal-head"><h3>{{ $judul }}</h3>
        <button type="button" class="modal-close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
      <div class="modal-body">
        @if ($gagalImpor)
          <div class="alert-error" style="margin-bottom:16px;">
            <strong><i class="fa-solid fa-circle-exclamation"></i> Impor belum berhasil</strong>
            <ul>@foreach ($errors->get('berkas') as $pesan)<li>{{ $pesan }}</li>@endforeach</ul>
          </div>
        @endif

        @if (! empty($petunjuk))
          <p class="form-hint" style="margin-top:0;">{!! $petunjuk !!}</p>
        @endif

        <div class="impor-kolom" aria-label="Kolom CSV">
          @foreach ($kolom as $nama => $ket)
            <span class="badge {{ in_array($nama, $wajib, true) ? 'badge-blue' : 'badge-grey' }}" title="{{ $ket }}">{{ $nama }}{{ in_array($nama, $wajib, true) ? ' *' : '' }}</span>
          @endforeach
        </div>
        <p class="form-hint" style="margin:6px 0 16px 0;">Kolom bertanda * wajib diisi. Arahkan kursor ke nama kolom untuk melihat keterangan.</p>

        <div class="dropzone" data-file-input="{{ $id }}Berkas" data-file-info="{{ $id }}Info">
          <strong><i class="fa-solid fa-cloud-arrow-up"></i> Pilih atau seret file CSV ke sini</strong>Maksimal 2 MB &middot; pemisah koma (,) atau titik koma (;).
        </div>
        <input type="file" name="berkas" id="{{ $id }}Berkas" accept=".csv,text/csv" hidden>
        <div class="form-hint" id="{{ $id }}Info"></div>

        <div class="impor-pratinjau" data-impor-pratinjau hidden>
          <div class="impor-ringkasan" data-impor-ringkasan></div>
          <div class="table-wrap impor-tabel">
            {{-- Tabel --}}
            <table class="data-table"><thead data-impor-head></thead><tbody data-impor-body></tbody></table>
          </div>
        </div>

        <div class="form-group" style="margin-top:18px;">
          <label>Jika {{ $kunciLabel }} sudah terdaftar</label>
          <div class="impor-opsi">
            <label><input type="radio" name="duplikat" value="lewati" @checked(old('duplikat', 'lewati') === 'lewati')> Lewati <span class="form-hint">(data lama tidak diubah)</span></label>
            <label><input type="radio" name="duplikat" value="perbarui" @checked(old('duplikat') === 'perbarui')> Perbarui dengan data dari CSV</label>
          </div>
        </div>

        <a href="{{ $template }}" class="btn btn-outline btn-sm" style="margin-top:6px;"><i class="fa-solid fa-download"></i> Unduh Template CSV</a>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-modal-close>Batal</button>
        <button type="submit" class="btn btn-primary" data-impor-kirim><i class="fa-solid fa-file-import"></i> Impor Data</button>
      </div>
    </form>
  </div>
</div>
