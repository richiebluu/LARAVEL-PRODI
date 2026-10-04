/* Kode Etik Mahasiswa */
(function () {
  'use strict';

  var PDFJS_VERSI = '3.11.174';
  var PDFJS_URL = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/' + PDFJS_VERSI + '/pdf.min.js';
  var PDFJS_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/' + PDFJS_VERSI + '/pdf.worker.min.js';
  var BATAS_TUNGGU = 12000;
  var RASIO_A4 = 1.4142;
  var ZOOM_MIN = 0.5, ZOOM_MAKS = 3, LANGKAH_ZOOM = 0.25;

  var reader = document.getElementById('kodetikReader');
  if (!reader) return;

  var q = function (sel) { return reader.querySelector(sel); };
  var qa = function (sel) { return Array.prototype.slice.call(reader.querySelectorAll(sel)); };

  var urlPdf = reader.getAttribute('data-pdf');
  var bar = q('.kodetik-bar');
  var stage = q('[data-kodetik-stage]');
  var wadah = q('[data-kodetik-halaman]');
  var inputHal = q('[data-kodetik-lompat]');
  var teksTotal = q('[data-kodetik-total]');
  var teksZoom = q('[data-kodetik-zoom]');
  var navbar = document.getElementById('navbar');

  var pdf = null;
  var total = 0;
  var ukuran = [];
  var lembar = [];
  var mode = 'lebar';
  var zoom = 1;
  var halAktif = 1;
  var versiRender = 0;
  var pengamat = null;
  var selesai = false;

  /* Toolbar menempel tepat di bawah navbar */
  function aturPosisiBar() {
    if (!bar || !navbar || document.fullscreenElement) return;
    var tinggi = navbar.getBoundingClientRect().height;
    bar.style.top = Math.max(0, Math.round(tinggi)) + 'px';
    qa('.kodetik-lembar').forEach(function (el) { el.style.scrollMarginTop = (tinggi + bar.offsetHeight + 16) + 'px'; });
  }
  window.addEventListener('scroll', aturPosisiBar, { passive: true });
  window.addEventListener('resize', aturPosisiBar);
  aturPosisiBar();

  /* Cadangan */
  function pakaiCadangan() {
    if (selesai) return;
    selesai = true;
    reader.classList.add('gagal', 'siap');
    var box = q('[data-kodetik-fallback]');
    if (!box) return;
    var frame = box.querySelector('iframe');
    box.hidden = false;
    wadah.innerHTML = '';
    var sesuaikan = function () {
      var lebar = stage.clientWidth - 48;
      frame.style.height = Math.max(Math.round(lebar * RASIO_A4), window.innerHeight * 0.8) + 'px';
    };
    if (frame && !frame.getAttribute('src')) frame.setAttribute('src', frame.getAttribute('data-src'));
    sesuaikan();
    window.addEventListener('resize', sesuaikan);
  }

  /* Muat PDF.js */
  function muatPdfJs() {
    return new Promise(function (resolve, reject) {
      if (window.pdfjsLib) return resolve(window.pdfjsLib);
      var s = document.createElement('script');
      s.src = PDFJS_URL;
      s.async = true;
      s.onload = function () {
        var coba = 0;
        (function cek() {
          if (window.pdfjsLib) return resolve(window.pdfjsLib);
          if (++coba > 40) return reject(new Error('pdfjsLib tidak tersedia'));
          setTimeout(cek, 75);
        })();
      };
      s.onerror = function () { reject(new Error('Gagal memuat PDF.js')); };
      document.head.appendChild(s);
    });
  }

  /* Ukuran lembar */
  function lebarArea() {
    var gaya = window.getComputedStyle(stage);
    return stage.clientWidth - parseFloat(gaya.paddingLeft) - parseFloat(gaya.paddingRight);
  }

  function tinggiLayarBaca() {
    var atas = (navbar ? navbar.getBoundingClientRect().height : 0) + (bar ? bar.offsetHeight : 0);
    if (document.fullscreenElement) atas = bar ? bar.offsetHeight : 0;
    return Math.max(320, window.innerHeight - atas - 40);
  }

  function lebarHalaman(i) {
    var u = ukuran[i] || ukuran[0];
    var penuh = lebarArea();
    if (mode === 'halaman') {
      return Math.min(penuh, tinggiLayarBaca() * (u.w / u.h));
    }
    return penuh * zoom;
  }

  function terapkanUkuran() {
    versiRender++;
    lembar.forEach(function (el, i) {
      var u = ukuran[i] || ukuran[0];
      var w = Math.floor(lebarHalaman(i));
      el.style.width = w + 'px';
      el.style.height = Math.floor(w * (u.h / u.w)) + 'px';
      el.classList.remove('terender');
      el.removeAttribute('data-versi');
    });
    if (teksZoom) {
      var persen = mode === 'halaman' ? Math.round(lebarHalaman(0) / lebarArea() * 100) : Math.round(zoom * 100);
      teksZoom.textContent = persen + '%';
    }
    qa('[data-kodetik="lebar"]').forEach(function (b) { b.classList.toggle('aktif', mode === 'lebar' && zoom === 1); });
    qa('[data-kodetik="halaman"]').forEach(function (b) { b.classList.toggle('aktif', mode === 'halaman'); });
    renderTerlihat();
  }

  /* Render halaman */
  var antrean = Promise.resolve();

  function render(i) {
    var el = lembar[i];
    if (!el || String(el.getAttribute('data-versi')) === String(versiRender) || el.getAttribute('data-proses') === String(versiRender)) return;
    var versi = versiRender;
    el.setAttribute('data-proses', String(versi));
    antrean = antrean.then(function () {
      if (versi !== versiRender) return;
      return pdf.getPage(i + 1).then(function (page) {
        if (versi !== versiRender) return;
        var lebarCss = parseFloat(el.style.width);
        var dasar = page.getViewport({ scale: 1 });
        var dpr = Math.min(window.devicePixelRatio || 1, 2.5);
        var vp = page.getViewport({ scale: (lebarCss / dasar.width) * dpr });
        var canvas = document.createElement('canvas');
        canvas.width = Math.floor(vp.width);
        canvas.height = Math.floor(vp.height);
        canvas.setAttribute('aria-label', 'Halaman ' + (i + 1));
        return page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise.then(function () {
          if (versi !== versiRender) return;
          var lama = el.querySelector('canvas');
          if (lama) el.removeChild(lama);
          el.insertBefore(canvas, el.firstChild);
          el.classList.add('terender');
          el.setAttribute('data-versi', String(versi));
        });
      });
    }).catch(function () {  });
  }

  function renderTerlihat() {
    var atas = window.innerHeight * -1, bawah = window.innerHeight * 2;
    lembar.forEach(function (el, i) {
      var r = el.getBoundingClientRect();
      if (r.bottom > atas && r.top < bawah) render(i);
    });
  }

  /* Halaman aktif & navigasi */
  function perbaruiHalAktif() {
    if (!lembar.length) return;
    var patokan = (bar ? bar.getBoundingClientRect().bottom : 0) + 40;
    var pilih = 1;
    for (var i = 0; i < lembar.length; i++) {
      if (lembar[i].getBoundingClientRect().top <= patokan) pilih = i + 1; else break;
    }
    halAktif = pilih;
    if (inputHal && document.activeElement !== inputHal) inputHal.value = halAktif;
    var prev = q('[data-kodetik="prev"]'), next = q('[data-kodetik="next"]');
    if (prev) prev.disabled = halAktif <= 1;
    if (next) next.disabled = halAktif >= total;
  }

  function keHalaman(n) {
    n = Math.max(1, Math.min(total, n | 0));
    var el = lembar[n - 1];
    if (!el) return;
    if (document.fullscreenElement) {
      stage.scrollTo({ top: el.offsetTop - 12, behavior: 'smooth' });
    } else {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    halAktif = n;
    if (inputHal) inputHal.value = n;
  }

  var tunda = null;
  function saatGulir() {
    if (tunda) return;
    tunda = window.requestAnimationFrame(function () {
      tunda = null;
      perbaruiHalAktif();
      renderTerlihat();
    });
  }

  /* Kontrol toolbar */
  reader.addEventListener('click', function (e) {
    var tombol = e.target.closest ? e.target.closest('[data-kodetik]') : null;
    if (!tombol || !pdf) return;
    var aksi = tombol.getAttribute('data-kodetik');
    if (aksi === 'prev') keHalaman(halAktif - 1);
    else if (aksi === 'next') keHalaman(halAktif + 1);
    else if (aksi === 'zoom-in' || aksi === 'zoom-out') {
      var dasar = mode === 'halaman' ? lebarHalaman(0) / lebarArea() : zoom;
      var hal = halAktif;
      mode = 'bebas';
      zoom = Math.max(ZOOM_MIN, Math.min(ZOOM_MAKS, Math.round((dasar + (aksi === 'zoom-in' ? LANGKAH_ZOOM : -LANGKAH_ZOOM)) * 4) / 4));
      terapkanUkuran();
      keHalaman(hal);
    } else if (aksi === 'lebar') {
      var h1 = halAktif; mode = 'lebar'; zoom = 1; terapkanUkuran(); keHalaman(h1);
    } else if (aksi === 'halaman') {
      var h2 = halAktif; mode = 'halaman'; zoom = 1; terapkanUkuran(); keHalaman(h2);
    } else if (aksi === 'layar') {
      layarPenuh();
    }
  });

  if (inputHal) {
    inputHal.addEventListener('change', function () { keHalaman(parseInt(inputHal.value, 10) || 1); });
    inputHal.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); keHalaman(parseInt(inputHal.value, 10) || 1); } });
  }

  document.addEventListener('keydown', function (e) {
    if (!pdf || /input|textarea|select/i.test((e.target && e.target.tagName) || '')) return;
    if (e.key === 'ArrowRight' || e.key === 'PageDown') { e.preventDefault(); keHalaman(halAktif + 1); }
    if (e.key === 'ArrowLeft' || e.key === 'PageUp') { e.preventDefault(); keHalaman(halAktif - 1); }
  });

  function layarPenuh() {
    var btn = q('[data-kodetik="layar"]');
    if (!document.fullscreenElement) {
      if (reader.requestFullscreen) reader.requestFullscreen().catch(function () {});
    } else if (document.exitFullscreen) {
      document.exitFullscreen();
    }
    if (btn) btn.blur();
  }
  document.addEventListener('fullscreenchange', function () {
    var aktif = document.fullscreenElement === reader;
    var ikon = q('[data-kodetik="layar"] i');
    if (ikon) ikon.className = 'fa-solid ' + (aktif ? 'fa-compress' : 'fa-expand');
    if (!aktif && bar) aturPosisiBar();
    var hal = halAktif;
    setTimeout(function () { terapkanUkuran(); keHalaman(hal); }, 120);
  });
  stage.addEventListener('scroll', saatGulir, { passive: true });
  window.addEventListener('scroll', saatGulir, { passive: true });

  var tundaUkuran = null;
  var lebarTerakhir = 0;
  window.addEventListener('resize', function () {
    clearTimeout(tundaUkuran);
    tundaUkuran = setTimeout(function () {
      if (!pdf || Math.abs(lebarArea() - lebarTerakhir) < 2 && mode !== 'halaman') return;
      lebarTerakhir = lebarArea();
      terapkanUkuran();
    }, 200);
  });

  /* Mulai */
  var batas = setTimeout(pakaiCadangan, BATAS_TUNGGU);

  muatPdfJs().then(function (lib) {
    lib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
    return lib.getDocument({ url: urlPdf }).promise;
  }).then(function (dok) {
    if (selesai) return;
    pdf = dok;
    total = dok.numPages;
    if (teksTotal) teksTotal.textContent = total;
    if (inputHal) inputHal.max = total;

    return dok.getPage(1).then(function (p1) {
      var v = p1.getViewport({ scale: 1 });
      for (var i = 0; i < total; i++) ukuran.push({ w: v.width, h: v.height });

      var frag = document.createDocumentFragment();
      for (var n = 1; n <= total; n++) {
        var el = document.createElement('div');
        el.className = 'kodetik-lembar';
        el.id = 'halaman-' + n;
        el.innerHTML = '<span class="kodetik-nomor">' + n + ' / ' + total + '</span>';
        frag.appendChild(el);
        lembar.push(el);
      }
      wadah.appendChild(frag);
      clearTimeout(batas);
      selesai = true;
      reader.classList.add('siap');
      lebarTerakhir = lebarArea();
      aturPosisiBar();
      terapkanUkuran();
      perbaruiHalAktif();

      var tugas = [];
      for (var k = 2; k <= total; k++) {
        (function (idx) {
          tugas.push(dok.getPage(idx).then(function (p) {
            var vv = p.getViewport({ scale: 1 });
            if (Math.abs(vv.width - ukuran[idx - 1].w) > 1 || Math.abs(vv.height - ukuran[idx - 1].h) > 1) {
              ukuran[idx - 1] = { w: vv.width, h: vv.height };
              return true;
            }
            return false;
          }));
        })(k);
      }
      return Promise.all(tugas).then(function (hasil) {
        if (hasil.some(Boolean)) terapkanUkuran();
      });
    });
  }).catch(function () {
    clearTimeout(batas);
    pakaiCadangan();
  });
})();
