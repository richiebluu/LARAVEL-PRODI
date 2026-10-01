/* ==========================================================================
   DASHBOARD UI SCRIPT — DOSEN / MAHASISWA / STAFF PRODI

   PENTING: file ini TIDAK lagi menyimpan atau membaca data aplikasi.
   Tidak ada localStorage, tidak ada TIDB, tidak ada array dummy.
   Seluruh data berasal dari database Laravel dan sudah dirender oleh Blade.

   Isi file ini murni interaksi tampilan:
   toast, modal, sidebar, tab, konfirmasi hapus, dan pencarian (submit form).
   ========================================================================== */

function $d(sel, root) { return (root || document).querySelector(sel); }
function $$d(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

/* ---------- TOAST ---------- */
function tiToast(pesan, tipe) {
  var wrap = $d('.toast-wrap');
  if (!wrap) {
    wrap = document.createElement('div');
    wrap.className = 'toast-wrap';
    document.body.appendChild(wrap);
  }
  var t = document.createElement('div');
  t.className = 'toast' + (tipe ? ' ' + tipe : '');
  t.innerHTML = '<i class="fa-solid ' + (tipe === 'bad' ? 'fa-circle-exclamation' : 'fa-circle-check') + '"></i><span></span>';
  t.querySelector('span').textContent = pesan;
  wrap.appendChild(t);
  setTimeout(function () { t.style.opacity = '0'; }, 3000);
  setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 3400);
}

/* ---------- MODAL ----------
   Modal sekarang berupa markup Blade yang sudah ada di halaman.
   Script hanya membuka/menutup, tidak membuat isi modal dari data JS. */
function tiBukaModal(id) {
  var m = document.getElementById(id);
  if (m) m.classList.add('show');
}
function tiTutupModal(el) {
  var m = el ? el.closest('.modal-overlay') : $d('.modal-overlay.show');
  if (m) m.classList.remove('show');
}

function tiInitModal() {
  $$d('[data-modal-open]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      tiBukaModal(btn.getAttribute('data-modal-open'));
    });
  });

  $$d('.modal-overlay').forEach(function (m) {
    m.addEventListener('click', function (e) {
      if (e.target === m || (e.target.closest && e.target.closest('[data-modal-close]'))) {
        m.classList.remove('show');
      }
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      $$d('.modal-overlay.show').forEach(function (m) { m.classList.remove('show'); });
    }
  });
}

/* ---------- ISI FORM EDIT DARI ATRIBUT data-* BARIS TABEL ----------
   Nilainya berasal dari Blade (hasil query database), bukan dari JS. */
function tiInitFormIsi() {
  $$d('[data-isi-form]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById(btn.getAttribute('data-isi-form'));
      if (!form) return;

      var action = btn.getAttribute('data-action');
      if (action) form.setAttribute('action', action);

      var nilai = {};
      try { nilai = JSON.parse(btn.getAttribute('data-nilai') || '{}'); } catch (e) { nilai = {}; }

      Object.keys(nilai).forEach(function (nama) {
        var input = form.querySelector('[name="' + nama + '"]');
        if (!input) return;
        input.value = nilai[nama] === null ? '' : nilai[nama];
      });

      // Pratinjau foto yang sudah tersimpan (REVISI 27-09-2026).
      var pratinjau = form.querySelector('[data-foto-preview]');
      if (pratinjau) {
        var foto = btn.getAttribute('data-foto');
        pratinjau.style.backgroundImage = foto ? "url('" + foto + "')" : '';
      }
      var inputFoto = form.querySelector('input[type="file"]');
      if (inputFoto) inputFoto.value = '';
      var hapusFoto = form.querySelector('[name="hapus_foto"]');
      if (hapusFoto) hapusFoto.checked = false;

      // Komponen khusus (mis. chip penerima pengumuman) mengisi dirinya dari data yang sama.
      form.dispatchEvent(new CustomEvent('ti:isi-form', { detail: nilai }));

      // Perbarui field bersyarat (data-tampil-jika) setelah form diisi.
      $$d('select', form).forEach(function (sel) { sel.dispatchEvent(new Event('change')); });

      var judul = btn.getAttribute('data-judul-modal');
      var kotak = form.closest('.modal-overlay');
      if (judul && kotak) {
        var h = kotak.querySelector('[data-modal-title]');
        if (h) h.textContent = judul;
      }
    });
  });
}

/* ---------- KONFIRMASI HAPUS ----------
   Form hapus tetap form HTML biasa (method DELETE) menuju Laravel. */
function tiInitKonfirmasi() {
  $$d('[data-konfirmasi]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (form.dataset.sudahKonfirmasi === '1') return;
      e.preventDefault();

      var pesan = form.getAttribute('data-konfirmasi') || 'Yakin ingin melanjutkan?';
      if (window.confirm(pesan)) {
        form.dataset.sudahKonfirmasi = '1';
        form.submit();
      }
    });
  });
}

/* ---------- KERANGKA DASHBOARD ---------- */
function tiInitShell() {
  var page = document.body.getAttribute('data-page');
  $$d('.side-link').forEach(function (a) {
    var p = a.pathname || '';
    if (page && (p === '/' + page || p.endsWith('/' + page))) a.classList.add('active');
  });

  var toggle = $d('.sidebar-toggle');
  var sidebar = $d('.sidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', function () { sidebar.classList.toggle('open'); });
  }

  $$d('[data-logout]').forEach(function (b) {
    b.addEventListener('click', function (e) {
      e.preventDefault();
      if (window.confirm('Keluar dari dashboard?')) {
        var form = document.getElementById('formLogout');
        if (form) form.submit();
      }
    });
  });
}

/* ---------- TAB ---------- */
function tiInitTabs() {
  $$d('.tab-bar[data-tabs]').forEach(function (bar) {
    $$d('.tab-btn', bar).forEach(function (btn) {
      btn.addEventListener('click', function () {
        $$d('.tab-btn', bar).forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var id = btn.getAttribute('data-tab');
        $$d('.tab-panel').forEach(function (p) {
          if (p.getAttribute('data-scope') === bar.getAttribute('data-tabs')) {
            p.classList.toggle('active', p.getAttribute('data-panel') === id);
          }
        });
      });
    });
  });
}

/* ---------- FILTER & SEARCH -> submit form ke Laravel ---------- */
function tiInitFilter() {
  $$d('[data-auto-submit]').forEach(function (el) {
    el.addEventListener('change', function () {
      if (el.form) el.form.submit();
    });
  });
}

/* ---------- FIELD BERSYARAT (REVISI 28-09-2026) ----------
   <div data-tampil-jika="status=pendidikan"> hanya tampil bila select[name=status]
   pada form yang sama bernilai "pendidikan". */
function tiInitTampilJika() {
  $$d('[data-tampil-jika]').forEach(function (el) {
    var aturan = el.getAttribute('data-tampil-jika').split('=');
    var form = el.closest('form');
    var sumber = form ? form.querySelector('[name="' + aturan[0] + '"]') : null;
    if (!sumber) return;
    var perbarui = function () { el.style.display = sumber.value === aturan[1] ? '' : 'none'; };
    sumber.addEventListener('change', perbarui);
    perbarui();
  });
}

/* ---------- PRATINJAU IKON DARI DROPDOWN (REVISI 28-09-2026) ----------
   <select data-pratinjau-ikon> mengganti ikon pada [data-ikon-preview] di form yang sama. */
function tiInitPratinjauIkon() {
  $$d('select[data-pratinjau-ikon]').forEach(function (sel) {
    var form = sel.closest('form');
    var kotak = form ? form.querySelector('[data-ikon-preview] i') : null;
    if (!kotak) return;
    var perbarui = function () { kotak.className = 'fa-solid ' + (sel.value || 'fa-briefcase'); };
    sel.addEventListener('change', perbarui);
    perbarui();
  });
}

/* ---------- PRATINJAU FOTO SEBELUM DIUNGGAH (REVISI 27-09-2026) ----------
   Hanya tampilan; berkas tetap divalidasi Laravel (jpg/png/webp, maks 2 MB). */
function tiInitPratinjauFoto() {
  $$d('input[type="file"][data-preview-foto]').forEach(function (input) {
    input.addEventListener('change', function () {
      var wadah = input.closest('.form-group');
      var kotak = wadah ? wadah.querySelector('[data-foto-preview]') : null;
      var file = input.files && input.files[0];
      if (!kotak || !file) return;
      if (file.size > 2 * 1024 * 1024) {
        if (window.tiToast) window.tiToast('Ukuran foto melebihi 2 MB.', 'bad');
      }
      var reader = new FileReader();
      reader.onload = function (e) { kotak.style.backgroundImage = "url('" + e.target.result + "')"; };
      reader.readAsDataURL(file);
    });
  });
}

/* ---------- UPLOAD (dropzone) ---------- */
function tiInitDropzone() {
  $$d('.dropzone[data-file-input]').forEach(function (drop) {
    var input = document.getElementById(drop.getAttribute('data-file-input'));
    if (!input) return;

    drop.addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function () {
      var info = document.getElementById(drop.getAttribute('data-file-info'));
      if (info && input.files[0]) info.textContent = 'Berkas dipilih: ' + input.files[0].name;
    });
  });
}

/* ---------- SERET & LEPAS FILE KE DROPZONE (REVISI 28-09-2026 tahap 2) ---------- */
function tiInitDropzoneSeret() {
  $$d('.dropzone[data-file-input]').forEach(function (drop) {
    var input = document.getElementById(drop.getAttribute('data-file-input'));
    if (!input) return;
    ['dragenter', 'dragover'].forEach(function (ev) {
      drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('seret'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('seret'); });
    });
    drop.addEventListener('drop', function (e) {
      if (!e.dataTransfer || !e.dataTransfer.files.length) return;
      try {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
      } catch (err) { /* browser lama: pilih file lewat klik */ }
    });
  });
}

/* ---------- IMPOR CSV: PRATINJAU SEBELUM DIUNGGAH (REVISI 28-09-2026 tahap 2) ----------
   File dibaca di browser hanya untuk pratinjau & pemeriksaan awal (judul kolom, sel wajib
   yang kosong, data ganda). Validasi final tetap di Laravel (App\Http\Controllers\Concerns\MengimporCsv). */
function tiNormalJudul(j) {
  return String(j || '').trim().replace(/^["']|["']$/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
}

function tiParseCsv(teks) {
  teks = teks.replace(/^﻿/, '');
  var barisPertama = teks.split(/\r\n|\r|\n/)[0] || '';
  var pemisah = (barisPertama.split(';').length > barisPertama.split(',').length) ? ';' : ',';
  var hasil = [], baris = [], sel = '', kutip = false, nomor = 1, nomorBaris = [];
  for (var i = 0; i < teks.length; i++) {
    var c = teks[i];
    if (kutip) {
      if (c === '"' && teks[i + 1] === '"') { sel += '"'; i++; }
      else if (c === '"') { kutip = false; }
      else { sel += c; if (c === '\n') nomor++; }
    } else if (c === '"') { kutip = true; }
    else if (c === pemisah) { baris.push(sel); sel = ''; }
    else if (c === '\r' || c === '\n') {
      if (c === '\r' && teks[i + 1] === '\n') i++;
      baris.push(sel); hasil.push(baris); nomorBaris.push(nomor); nomor++; baris = []; sel = '';
    } else { sel += c; }
  }
  if (sel !== '' || baris.length) { baris.push(sel); hasil.push(baris); nomorBaris.push(nomor); }
  return { baris: hasil, nomor: nomorBaris };
}

function tiInitImporCsv() {
  $$d('form[data-impor-csv]').forEach(function (form) {
    var input = form.querySelector('input[type="file"][name="berkas"]');
    var kotak = form.querySelector('[data-impor-pratinjau]');
    var ringkas = form.querySelector('[data-impor-ringkasan]');
    var thead = form.querySelector('[data-impor-head]');
    var tbody = form.querySelector('[data-impor-body]');
    var kirim = form.querySelector('[data-impor-kirim]');
    if (!input || !kotak) return;

    var baca = function (atr, bawaan) { try { return JSON.parse(form.getAttribute(atr) || ''); } catch (e) { return bawaan; } };
    var kolom = baca('data-kolom', []);
    var wajib = baca('data-wajib', []);
    var alias = baca('data-alias', {});
    var kunci = form.getAttribute('data-kunci');
    var siap = false;

    var aturKirim = function () { if (kirim) kirim.disabled = !siap; };
    aturKirim();

    var tampil = function (html, tipe) {
      kotak.hidden = false;
      ringkas.className = 'impor-ringkasan ' + (tipe || '');
      ringkas.innerHTML = html;
    };
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; };

    input.addEventListener('change', function () {
      siap = false; aturKirim();
      thead.innerHTML = ''; tbody.innerHTML = '';
      var file = input.files && input.files[0];
      if (!file) { kotak.hidden = true; return; }

      if (!/\.(csv|txt)$/i.test(file.name)) {
        tampil('<i class="fa-solid fa-circle-exclamation"></i> File <strong>' + esc(file.name) + '</strong> bukan CSV. Dari Excel pilih <em>Simpan Sebagai &gt; CSV</em>.', 'bad');
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        tampil('<i class="fa-solid fa-circle-exclamation"></i> Ukuran file melebihi 2 MB.', 'bad');
        return;
      }

      var reader = new FileReader();
      reader.onload = function (e) {
        var csv = tiParseCsv(String(e.target.result || ''));
        var semua = csv.baris;
        if (!semua.length) { tampil('<i class="fa-solid fa-circle-exclamation"></i> File kosong.', 'bad'); return; }

        var judul = semua[0].map(function (j) { var n = tiNormalJudul(j); return alias[n] || n; });
        var hilang = wajib.filter(function (k) { return judul.indexOf(k) === -1; });
        if (hilang.length) {
          tampil('<i class="fa-solid fa-circle-exclamation"></i> Judul kolom tidak sesuai template. Kolom wajib tidak ditemukan: <strong>' + esc(hilang.join(', ')) + '</strong>. Unduh template lalu salin data ke kolom yang sesuai.', 'bad');
          return;
        }

        var data = [], masalah = [], lihatKunci = {};
        for (var r = 1; r < semua.length; r++) {
          var sel = semua[r];
          if (!sel.join('').trim()) continue;
          var obj = {};
          kolom.forEach(function (k) { var p = judul.indexOf(k); obj[k] = p === -1 ? '' : String(sel[p] || '').trim(); });
          var no = csv.nomor[r];
          var kosong = wajib.filter(function (k) { return obj[k] === ''; });
          if (kosong.length) masalah.push('Baris ' + no + ': ' + kosong.join(', ') + ' kosong');
          if (kunci && obj[kunci]) {
            var kk = obj[kunci].toLowerCase();
            if (lihatKunci[kk]) masalah.push('Baris ' + no + ': ' + kunci + ' "' + obj[kunci] + '" ganda (baris ' + lihatKunci[kk] + ')');
            else lihatKunci[kk] = no;
          }
          obj._no = no;
          data.push(obj);
        }

        if (!data.length) { tampil('<i class="fa-solid fa-circle-exclamation"></i> File hanya berisi judul kolom, belum ada baris data.', 'bad'); return; }

        var kolomTampil = kolom.filter(function (k) { return judul.indexOf(k) !== -1; });
        thead.innerHTML = '<tr><th>Baris</th>' + kolomTampil.map(function (k) { return '<th>' + esc(k) + '</th>'; }).join('') + '</tr>';
        tbody.innerHTML = data.slice(0, 5).map(function (o) {
          return '<tr><td>' + o._no + '</td>' + kolomTampil.map(function (k) {
            return '<td' + (o[k] === '' && wajib.indexOf(k) !== -1 ? ' class="sel-kosong"' : '') + '>' + (o[k] === '' ? '-' : esc(o[k])) + '</td>';
          }).join('') + '</tr>';
        }).join('');

        var info = '<strong>' + data.length + '</strong> baris data terbaca' + (data.length > 5 ? ' (pratinjau 5 baris pertama)' : '') + '.';
        if (masalah.length) {
          tampil('<i class="fa-solid fa-triangle-exclamation"></i> ' + info + ' Ditemukan <strong>' + masalah.length + '</strong> baris yang perlu diperbaiki:<ul>' +
            masalah.slice(0, 5).map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') +
            (masalah.length > 5 ? '<li>... dan ' + (masalah.length - 5) + ' lainnya</li>' : '') + '</ul>', 'warn');
          siap = true; // tetap boleh dikirim: server menampilkan detail lengkap dan membatalkan impor bila salah
        } else {
          tampil('<i class="fa-solid fa-circle-check"></i> ' + info + ' Format kolom sesuai. Data akan divalidasi ulang saat diimpor.', 'ok');
          siap = true;
        }
        aturKirim();
      };
      reader.onerror = function () { tampil('<i class="fa-solid fa-circle-exclamation"></i> File tidak dapat dibaca.', 'bad'); };
      reader.readAsText(file);
    });

    form.addEventListener('submit', function (e) {
      if (!input.files || !input.files[0]) {
        e.preventDefault();
        tiToast('Pilih file CSV terlebih dahulu.', 'bad');
        return;
      }
      if (kirim) {
        kirim.disabled = true;
        kirim.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengimpor...';
      }
    });
  });
}

/* ---------- LOADING SAAT MENYIMPAN FORM (REVISI 28-09-2026 tahap 2) ----------
   Mencegah klik ganda pada tombol Simpan di modal tambah/edit. */
function tiInitLoadingSimpan() {
  $$d('.modal-overlay form[method="POST"]:not([data-impor-csv])').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (!btn || btn.disabled) return;
      setTimeout(function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
      }, 0);
    });
  });
}

/* ---------- TOMBOL MENUJU FORM DI HALAMAN YANG SAMA (REVISI 28-09-2026 tahap 2) ----------
   <a href="#idPanel" data-fokus="selector input"> : gulir halus ke form lalu fokus ke input pertama. */
function tiInitFokus() {
  $$d('a[data-fokus]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var tujuan = document.querySelector(a.getAttribute('href'));
      if (!tujuan) return;
      e.preventDefault();
      tujuan.scrollIntoView({ behavior: 'smooth', block: 'start' });
      var input = document.querySelector(a.getAttribute('data-fokus'));
      setTimeout(function () { if (input) input.focus({ preventScroll: true }); }, 450);
    });
  });
}

/* ---------- PILIH BANYAK PENERIMA PENGUMUMAN (REVISI DOSEN 01-10-2026) ----------
   Konsep "bagikan" Google Drive: ketik email @mhs.politala.ac.id -> sistem mencari
   mahasiswa di DATABASE (GET /staff-pengumuman/cari-mahasiswa) -> pilih -> tampil
   sebagai chip [ Nama × ]. Setiap chip membawa <input hidden name="penerima[]">.
   Validasi final (domain email, mahasiswa berprestasi, tidak dobel) tetap di Laravel. */
function tiInitPilihPenerima() {
  $$d('[data-pilih-penerima]').forEach(function (wadah) {
    var form = wadah.closest('form');
    var kotak = wadah.querySelector('[data-penerima-box]');
    var input = wadah.querySelector('[data-penerima-input]');
    var saran = wadah.querySelector('[data-penerima-saran]');
    var jumlah = wadah.querySelector('[data-penerima-jumlah]');
    var url = wadah.getAttribute('data-url');
    var domain = wadah.getAttribute('data-domain') || '';
    var selectPrestasi = form ? form.querySelector('[data-prestasi-penerima]') : null;
    var hasil = [];
    var aktif = -1;
    var timer = null;
    var urutan = 0;

    var esc = function (t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; };
    var dipilih = function () {
      return $$d('input[name="penerima[]"]', kotak).map(function (i) { return i.value; });
    };

    // Opsi "Terkait Prestasi" hanya untuk prestasi milik penerima yang dipilih.
    var saringPrestasi = function () {
      if (!selectPrestasi) return;
      var nim = dipilih();
      $$d('option[data-nim]', selectPrestasi).forEach(function (o) {
        var cocok = nim.indexOf(o.getAttribute('data-nim')) !== -1;
        o.hidden = !cocok;
        o.disabled = !cocok;
      });
      var terpilih = selectPrestasi.options[selectPrestasi.selectedIndex];
      if (terpilih && terpilih.disabled) selectPrestasi.value = '';
    };

    var perbarui = function () {
      var n = dipilih().length;
      if (jumlah) jumlah.textContent = n;
      kotak.classList.toggle('kosong', n === 0);
      saringPrestasi();
    };

    var tutupSaran = function () { saran.hidden = true; saran.innerHTML = ''; hasil = []; aktif = -1; };

    var tambahChip = function (m) {
      if (!m || !m.nim || dipilih().indexOf(m.nim) !== -1) return; // tidak boleh dobel
      var chip = document.createElement('span');
      chip.className = 'penerima-chip';
      chip.setAttribute('data-nim', m.nim);
      chip.title = m.email || '';
      chip.innerHTML =
        '<span class="penerima-avatar">' + esc((m.nama || '?').charAt(0).toUpperCase()) + '</span>' +
        '<span class="penerima-teks"><b>' + esc(m.nama) + '</b><small>' + esc(m.email) + '</small></span>' +
        '<input type="hidden" name="penerima[]" value="' + esc(m.nim) + '">' +
        '<button type="button" class="penerima-hapus" aria-label="Hapus ' + esc(m.nama) + '">&times;</button>';
      kotak.insertBefore(chip, input);
      perbarui();
    };

    var kosongkan = function () {
      $$d('.penerima-chip', kotak).forEach(function (c) { c.parentNode.removeChild(c); });
      perbarui();
    };

    var tampilPesan = function (pesan, tipe) {
      saran.innerHTML = '<div class="penerima-pesan ' + (tipe || '') + '"><i class="fa-solid ' +
        (tipe === 'bad' ? 'fa-circle-exclamation' : 'fa-circle-info') + '"></i> ' + esc(pesan) + '</div>';
      saran.hidden = false;
    };

    var tandaiAktif = function () {
      $$d('.penerima-opsi', saran).forEach(function (o, i) { o.classList.toggle('aktif', i === aktif); });
    };

    var tampilHasil = function (data, pesan) {
      hasil = data || [];
      aktif = -1;
      if (!hasil.length) { tampilPesan(pesan || 'Mahasiswa tidak ditemukan.', 'bad'); return; }
      saran.innerHTML = hasil.map(function (m, i) {
        return '<button type="button" class="penerima-opsi' + (m.bisa_dipilih ? '' : ' nonaktif') + '" data-i="' + i + '"' +
          (m.bisa_dipilih ? '' : ' aria-disabled="true"') + ' role="option">' +
          '<span class="penerima-avatar">' + esc((m.nama || '?').charAt(0).toUpperCase()) + '</span>' +
          '<span class="penerima-teks"><b>' + esc(m.nama) + '</b><small>' + esc(m.email) + ' &middot; ' + esc(m.nim) + '</small>' +
          (m.bisa_dipilih ? '' : '<em>' + esc(m.alasan) + '</em>') + '</span></button>';
      }).join('');
      saran.hidden = false;
    };

    var pilih = function (i) {
      var m = hasil[i];
      if (!m) return;
      if (!m.bisa_dipilih) { tiToast(m.nama + ': ' + (m.alasan || 'tidak dapat dipilih'), 'bad'); return; }
      tambahChip(m);
      input.value = '';
      tutupSaran();
      input.focus();
    };

    var cari = function () {
      var q = input.value.trim();
      if (q.length < 2) { tutupSaran(); return; }
      if (q.indexOf('@') !== -1) {
        var dom = q.split('@')[1] || '';
        if (dom && domain.indexOf(dom.toLowerCase()) !== 0) {
          tampilPesan('Penerima hanya boleh email mahasiswa @' + domain + '.', 'bad');
          return;
        }
      }
      var param = new URLSearchParams();
      param.append('q', q);
      dipilih().forEach(function (n) { param.append('kecuali[]', n); });
      var nomor = ++urutan;
      tampilPesan('Mencari mahasiswa...', '');
      fetch(url + '?' + param.toString(), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      }).then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      }).then(function (json) {
        if (nomor !== urutan) return; // abaikan respons lama
        tampilHasil(json.data, json.pesan);
      }).catch(function () {
        if (nomor !== urutan) return;
        tampilPesan('Pencarian gagal. Muat ulang halaman lalu coba lagi.', 'bad');
      });
    };

    input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(cari, 250);
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' && hasil.length) { e.preventDefault(); aktif = (aktif + 1) % hasil.length; tandaiAktif(); }
      else if (e.key === 'ArrowUp' && hasil.length) { e.preventDefault(); aktif = (aktif - 1 + hasil.length) % hasil.length; tandaiAktif(); }
      else if (e.key === 'Enter') {
        e.preventDefault(); // Enter memilih mahasiswa, bukan mengirim form
        if (hasil.length) pilih(aktif >= 0 ? aktif : 0);
      }
      else if (e.key === 'Escape') { tutupSaran(); }
      else if (e.key === 'Backspace' && input.value === '') {
        var chip = $$d('.penerima-chip', kotak).pop();
        if (chip) { chip.parentNode.removeChild(chip); perbarui(); }
      }
    });

    saran.addEventListener('mousedown', function (e) { e.preventDefault(); }); // input tidak kehilangan fokus
    saran.addEventListener('click', function (e) {
      var opsi = e.target.closest('.penerima-opsi');
      if (opsi) pilih(parseInt(opsi.getAttribute('data-i'), 10));
    });

    kotak.addEventListener('click', function (e) {
      var hapus = e.target.closest('.penerima-hapus');
      if (hapus) {
        var chip = hapus.closest('.penerima-chip');
        if (chip) chip.parentNode.removeChild(chip);
        perbarui();
        return;
      }
      input.focus();
    });

    input.addEventListener('blur', function () { setTimeout(tutupSaran, 150); });

    if (form) {
      // Tombol Edit pada tabel: isi chip dari data penerima pengumuman tsb.
      form.addEventListener('ti:isi-form', function (e) {
        kosongkan();
        ((e.detail && e.detail.penerima) || []).forEach(tambahChip);
        input.value = '';
        tutupSaran();
      });

      form.addEventListener('submit', function (e) {
        if (!dipilih().length) {
          e.preventDefault();
          e.stopImmediatePropagation();
          tiToast('Pilih minimal satu mahasiswa penerima.', 'bad');
          input.focus();
        }
      }, true); // capture: berjalan sebelum efek loading tombol Simpan
    }

    perbarui();
  });
}

/* ---------- FLASH MESSAGE DARI LARAVEL ---------- */
function tiInitFlash() {
  var box = $d('[data-flash]');
  if (!box) return;
  var pesan = box.getAttribute('data-flash');
  var tipe = box.getAttribute('data-flash-tipe') || 'ok';
  if (pesan) tiToast(pesan, tipe);
}

document.addEventListener('DOMContentLoaded', function () {
  tiInitShell();
  tiInitTabs();
  tiInitModal();
  tiInitFormIsi();
  tiInitKonfirmasi();
  tiInitFilter();
  tiInitDropzone();
  tiInitPratinjauFoto();
  tiInitTampilJika();
  // Buka kembali form tambah bila validasi gagal (old input tetap terisi).
  $$d('.modal-overlay[data-buka-otomatis]').forEach(function (m) { m.classList.add('show'); });
  tiInitPratinjauIkon();
  tiInitDropzoneSeret();
  tiInitImporCsv();
  tiInitPilihPenerima();
  tiInitLoadingSimpan();
  tiInitFokus();
  tiInitFlash();
});
