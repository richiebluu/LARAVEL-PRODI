/* Program Studi Teknologi Informasi */
document.addEventListener('DOMContentLoaded', function () {
  /* Navbar */
  var navbar = document.querySelector('.navbar');
  function handleNavScroll(){
    if(!navbar) return;
    if(window.scrollY > 40){ navbar.classList.add('is-solid'); }
    else if(!navbar.classList.contains('force-solid')){ navbar.classList.remove('is-solid'); }
  }
  window.addEventListener('scroll', handleNavScroll);
  handleNavScroll();

  /* Hamburger / Mobile Menu */
  var hamburger = document.querySelector('.hamburger');
  if(hamburger && navbar){
    hamburger.addEventListener('click', function(){
      navbar.classList.toggle('mobile-open');
    });
  }
  document.querySelectorAll('.nav-item').forEach(function(item){
    var link = item.querySelector('.nav-link');
    if(!link) return;
    var punyaDropdown = !!item.querySelector('.dropdown');
    var bukaTutup = function(){
      var buka = item.classList.toggle('open');
      if(link.hasAttribute('aria-expanded')) link.setAttribute('aria-expanded', buka ? 'true' : 'false');
      document.querySelectorAll('.nav-item.open').forEach(function(lain){
        if(lain !== item){
          lain.classList.remove('open');
          var l = lain.querySelector('.nav-link[aria-expanded]');
          if(l) l.setAttribute('aria-expanded', 'false');
        }
      });
    };
    link.addEventListener('click', function(e){
      if(punyaDropdown && (window.innerWidth <= 1100 || !link.getAttribute('href'))){
        e.preventDefault();
        bukaTutup();
      }
    });
    link.addEventListener('keydown', function(e){
      if(punyaDropdown && (e.key === 'Enter' || e.key === ' ')){
        e.preventDefault();
        bukaTutup();
      } else if(e.key === 'Escape'){
        item.classList.remove('open');
        if(link.hasAttribute('aria-expanded')) link.setAttribute('aria-expanded', 'false');
      }
    });
  });
  document.addEventListener('click', function(e){
    if(e.target.closest && e.target.closest('.nav-item')) return;
    document.querySelectorAll('.nav-item.open').forEach(function(item){
      item.classList.remove('open');
      var l = item.querySelector('.nav-link[aria-expanded]');
      if(l) l.setAttribute('aria-expanded', 'false');
    });
  });

  var activeGroup = document.body.getAttribute('data-nav');
  if(activeGroup){
    document.querySelectorAll('.nav-link[data-group="' + activeGroup + '"]').forEach(function(el){
      el.classList.add('active');
    });
  }

  /* Hero Slider */
  (function(){
    var heroSlider = document.querySelector('.hero-slider');
    var slides = document.querySelectorAll('.hero-slide');
    if(!heroSlider || !slides.length) return;

    var indicators = document.querySelectorAll('.hero-indicators button');
    var nextBtn = document.querySelector('.hero-next');
    var prevBtn = document.querySelector('.hero-prev');
    var current = 0;
    var timer = null;
    var userPaused = false;
    var AUTOPLAY_MS = 7000;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    heroSlider.style.setProperty('--hero-interval', AUTOPLAY_MS + 'ms');

    function showSlide(idx){
      slides.forEach(function(s,i){ s.classList.toggle('active', i===idx); });
      indicators.forEach(function(b,i){
        b.classList.toggle('active', i===idx);
        b.setAttribute('aria-selected', i===idx ? 'true' : 'false');
      });
      current = idx;
    }
    function nextSlide(){ showSlide((current+1) % slides.length); }
    function prevSlide(){ showSlide((current-1+slides.length) % slides.length); }

    function play(){
      if(userPaused || reduceMotion || document.hidden) return;
      clearInterval(timer);
      timer = setInterval(nextSlide, AUTOPLAY_MS);
    }
    function stop(){ clearInterval(timer); timer = null; }
    function restart(){ stop(); if(!userPaused) play(); }

    if(nextBtn) nextBtn.addEventListener('click', function(){ nextSlide(); restart(); });
    if(prevBtn) prevBtn.addEventListener('click', function(){ prevSlide(); restart(); });
    indicators.forEach(function(btn, i){
      btn.addEventListener('click', function(){ if(i !== current){ showSlide(i); } restart(); });
    });

    function pauseForInteraction(){ userPaused = true; heroSlider.classList.add('is-paused'); stop(); }
    function resumeInteraction(){ userPaused = false; heroSlider.classList.remove('is-paused'); play(); }

    heroSlider.addEventListener('mouseenter', pauseForInteraction);
    heroSlider.addEventListener('mouseleave', resumeInteraction);
    heroSlider.addEventListener('focusin', pauseForInteraction);
    heroSlider.addEventListener('focusout', function(e){
      if(!heroSlider.contains(e.relatedTarget)) resumeInteraction();
    });

    document.addEventListener('visibilitychange', function(){
      if(document.hidden){ stop(); } else { play(); }
    });

    heroSlider.addEventListener('keydown', function(e){
      if(e.key === 'ArrowRight'){ nextSlide(); restart(); }
      else if(e.key === 'ArrowLeft'){ prevSlide(); restart(); }
    });

    var touchStartX = null;
    heroSlider.addEventListener('touchstart', function(e){
      touchStartX = e.touches[0].clientX;
      stop();
    }, { passive:true });
    heroSlider.addEventListener('touchend', function(e){
      if(touchStartX === null) return;
      var dx = e.changedTouches[0].clientX - touchStartX;
      if(Math.abs(dx) > 40){ dx < 0 ? nextSlide() : prevSlide(); }
      touchStartX = null;
      restart();
    }, { passive:true });

    var glow = heroSlider.querySelector('.hero-cursor-glow');
    if(glow && finePointer && !reduceMotion){
      var targetX = 0, targetY = 0, curX = 0, curY = 0;
      var MAX_OFFSET = 14;
      function loop(){
        curX += (targetX - curX) * 0.08;
        curY += (targetY - curY) * 0.08;
        glow.style.transform = 'translate3d(' + curX.toFixed(1) + 'px,' + curY.toFixed(1) + 'px,0)';
        requestAnimationFrame(loop);
      }
      heroSlider.addEventListener('mousemove', function(e){
        var rect = heroSlider.getBoundingClientRect();
        targetX = ((e.clientX - rect.left) / rect.width - 0.5) * MAX_OFFSET * 2;
        targetY = ((e.clientY - rect.top) / rect.height - 0.5) * MAX_OFFSET * 2;
      });
      heroSlider.addEventListener('mouseleave', function(){ targetX = 0; targetY = 0; });
      requestAnimationFrame(loop);
    } else if(glow){
      glow.style.display = 'none';
    }

    showSlide(0);
    play();
  })();

  /* Scroll Reveal */
  var revealEls = document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window && revealEls.length){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add('in-view');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(function(el, i){
      el.style.setProperty('--i', i % 8);
      io.observe(el);
    });
  } else {
    revealEls.forEach(function(el){ el.classList.add('in-view'); });
  }

  /* Animated Counter */
  var counters = document.querySelectorAll('[data-counter]');
  if('IntersectionObserver' in window && counters.length){
    var counterIO = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          animateCounter(entry.target);
          counterIO.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    counters.forEach(function(el){ counterIO.observe(el); });
  }
  function animateCounter(el){
    var target = parseInt(el.getAttribute('data-counter'), 10) || 0;
    var duration = 1600;
    var startTime = null;
    function step(ts){
      if(!startTime) startTime = ts;
      var progress = Math.min((ts - startTime) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * target) + '+';
      if(progress < 1){ requestAnimationFrame(step); }
      else { el.textContent = target + '+'; }
    }
    requestAnimationFrame(step);
  }
  document.querySelectorAll('[data-counter-plain]').forEach(function(el){
    var target = parseInt(el.getAttribute('data-counter-plain'), 10) || 0;
    if('IntersectionObserver' in window){
      var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
          if(entry.isIntersecting){
            var startTime = null;
            function step(ts){
              if(!startTime) startTime = ts;
              var progress = Math.min((ts - startTime) / 1600, 1);
              el.textContent = Math.floor(progress * target);
              if(progress < 1) requestAnimationFrame(step);
              else el.textContent = target;
            }
            requestAnimationFrame(step);
            obs.unobserve(entry.target);
          }
        });
      }, {threshold:.4});
      obs.observe(el);
    }
  });

  /* Back To Top */
  var backToTop = document.querySelector('.back-to-top');
  if(backToTop){
    window.addEventListener('scroll', function(){
      backToTop.classList.toggle('show', window.scrollY > 500);
    });
    backToTop.addEventListener('click', function(){
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* Filter Buttons */
  document.querySelectorAll('.filter-bar').forEach(function(bar){
    var targetSelector = bar.getAttribute('data-target');
    bar.querySelectorAll('.filter-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        bar.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        var filter = btn.getAttribute('data-filter');
        var items = targetSelector ? document.querySelectorAll(targetSelector) : [];
        items.forEach(function(item){
          var cat = item.getAttribute('data-category') || '';
          item.style.display = (filter === 'all' || cat === filter) ? '' : 'none';
        });
      });
    });
  });

  /* Simple Search */
  document.querySelectorAll('[data-search-target]').forEach(function(searchInput){
    var searchTargets = document.querySelectorAll(searchInput.getAttribute('data-search-target'));
    var kosong = searchInput.getAttribute('data-search-empty') ? document.querySelector(searchInput.getAttribute('data-search-empty')) : null;
    var saring = function(){
      var q = searchInput.value.toLowerCase().trim();
      var tampil = 0;
      searchTargets.forEach(function(item){
        var cocok = item.textContent.toLowerCase().indexOf(q) !== -1;
        item.style.display = cocok ? '' : 'none';
        if (cocok) tampil++;
      });
      document.querySelectorAll('[data-search-group]').forEach(function(grup){
        var ada = Array.prototype.some.call(grup.querySelectorAll(searchInput.getAttribute('data-search-target')), function(el){ return el.style.display !== 'none'; });
        grup.style.display = ada ? '' : 'none';
      });
      if (kosong) kosong.classList.toggle('tampil', searchTargets.length > 0 && tampil === 0);
    };
    searchInput.addEventListener('input', saring);
    if (searchInput.value) saring();
  });

  /* Accordion */
  document.querySelectorAll('.accordion-head').forEach(function(head){
    head.addEventListener('click', function(){
      var item = head.parentElement;
      var wasOpen = item.classList.contains('open');
      item.parentElement.querySelectorAll('.accordion-item').forEach(function(i){ i.classList.remove('open'); });
      if(!wasOpen) item.classList.add('open');
    });
  });

  /* Modal */
  document.querySelectorAll('[data-modal-open]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var modal = document.getElementById(btn.getAttribute('data-modal-open'));
      if(modal) modal.classList.add('show');
    });
  });
  document.querySelectorAll('[data-modal-close]').forEach(function(btn){
    btn.addEventListener('click', function(){
      btn.closest('.modal-overlay').classList.remove('show');
    });
  });
  document.querySelectorAll('.modal-overlay').forEach(function(overlay){
    overlay.addEventListener('click', function(e){
      if(e.target === overlay) overlay.classList.remove('show');
    });
  });
  if(location.hash && location.hash.indexOf('#modalDosen') === 0){
    var modalHash = document.getElementById(location.hash.substring(1));
    if(modalHash && modalHash.classList.contains('modal-overlay')) modalHash.classList.add('show');
  }
});

/* Search Panel */
var SITE_SEARCH_INDEX = [
  { title:"Beranda", desc:"Halaman utama website Program Studi Teknologi Informasi", url:"/", keywords:["beranda","home","utama"], icon:"fa-house" },
  { title:"Tentang Program Studi", desc:"Profil, statistik, akreditasi, struktur organisasi, dan dosen pengajar Prodi TI", url:"/profil", keywords:["profil","profil prodi","tentang","pengenalan"], icon:"fa-building-columns" },
  { title:"Visi & Misi", desc:"Visi dan misi Program Studi Teknologi Informasi (halaman Tentang)", url:"/profil#visi-misi", keywords:["visi","misi","visi misi"], icon:"fa-bullseye" },
  { title:"Akreditasi", desc:"Status, peringkat, dan masa berlaku akreditasi Prodi TI", url:"/akreditasi", keywords:["akreditasi","peringkat","lam infokom","sk"], icon:"fa-certificate" },
  { title:"Struktur Organisasi", desc:"Koordinator Program Studi, Koordinator Gugus, dan pengelola Prodi", url:"/struktur-organisasi", keywords:["struktur","organisasi","koordinator","kaprodi","gugus"], icon:"fa-sitemap" },
  { title:"Dosen Pengajar", desc:"Tenaga pengajar Program Studi Teknologi Informasi", url:"/dosen", keywords:["dosen","pengajar","lecturer","google scholar","publikasi"], icon:"fa-chalkboard-user" },
  { title:"Sarana & Prasarana", desc:"Laboratorium, ruang kuliah, dan fasilitas pendukung Program Studi Teknologi Informasi", url:"/sarana-prasarana", keywords:["sarana","prasarana","fasilitas","laboratorium","lab","ruang","gedung"], icon:"fa-flask" },
  { title:"Mata Kuliah", desc:"Daftar mata kuliah Program Studi (kode, nama, semester, SKS, jenis) sesuai SIPADU", url:"/mata-kuliah", keywords:["mata kuliah","matkul","kurikulum","sks","semester","sipadu"], icon:"fa-book-open" },
  { title:"Prospek Lulusan", desc:"Peluang karier digital bagi lulusan Teknologi Informasi", url:"/prospek-lulusan", keywords:["prospek","lulusan","karier","karir","prospek lulusan"], icon:"fa-briefcase" },
  { title:"Mahasiswa Berprestasi", desc:"Mahasiswa berprestasi Program Studi Teknologi Informasi", url:"/mahasiswa-berprestasi", keywords:["mahasiswa berprestasi","prestasi","berprestasi","juara","prestasi akademik","prestasi non-akademik","keaktifan organisasi","ipk","mahasiswa"], icon:"fa-medal" },
  { title:"Ranking Mahasiswa", desc:"Peringkat SAW: Nilai Akademik, Prestasi Akademik, Prestasi Non-Akademik, Keaktifan Organisasi", url:"/ranking", keywords:["ranking","peringkat","top 3","skor","saw","bobot","nilai akademik","prestasi akademik","prestasi non-akademik","keaktifan organisasi"], icon:"fa-ranking-star" },
  { title:"Testimoni Alumni", desc:"Cerita alumni Program Studi Teknologi Informasi", url:"/testimoni", keywords:["testimoni","alumni","cerita","perusahaan"], icon:"fa-comment-dots" },
  { title:"Lowongan Kerja", desc:"Informasi lowongan kerja dan magang", url:"/lowongan-pekerjaan", keywords:["lowongan","pekerjaan","kerja","magang","karier","loker"], icon:"fa-briefcase" },
  { title:"Berita", desc:"Informasi: berita Program Studi dan kegiatan mahasiswa (seminar, lomba, pengabdian, kunjungan industri)", url:"/berita", keywords:["berita","kegiatan","kegiatan mahasiswa","seminar","workshop","lomba","pengabdian","kunjungan","informasi","news"], icon:"fa-newspaper" },
  { title:"AKAMAWA", desc:"Informasi: Layanan Akademik dan Kemahasiswaan Politala", url:"/akamawa", keywords:["informasi","akamawa","layanan","beasiswa","dispensasi","legalisir"], icon:"fa-building-columns" },
  { title:"Kode Etik Mahasiswa", desc:"Informasi: dokumen Kode Etik Mahasiswa (PDF dibaca langsung di website)", url:"/kode-etik", keywords:["informasi","kode etik","etik","pdf","aturan","dokumen"], icon:"fa-book-open" },
  { title:"Pengumuman", desc:"Pengumuman untuk mahasiswa berprestasi (dikirim via email & dashboard)", url:"/pengumuman", keywords:["pengumuman","announcement"], icon:"fa-bullhorn" },
  { title:"Login Sistem", desc:"Masuk sebagai Mahasiswa atau Staff Prodi (termasuk Login dengan Google)", url:"/login", keywords:["login","masuk","dashboard","mahasiswa","staff","google"], icon:"fa-right-to-bracket" }
];

var SITE_SEARCH_EN = {
  "/": ["Home", "Main page of the Information Technology Study Program website", ["home", "main"]],
  "/profil": ["About the Study Program", "Profile, statistics, accreditation, organizational structure, and lecturers of the IT Program", ["profile", "about", "introduction"]],
  "/profil#visi-misi": ["Vision & Mission", "Vision and mission of the Information Technology Study Program (About page)", ["vision", "mission"]],
  "/akreditasi": ["Accreditation", "Accreditation status, rating, and validity period of the IT Program", ["accreditation", "rating", "decree"]],
  "/struktur-organisasi": ["Organizational Structure", "Study Program Coordinator, Unit Coordinator, and program management", ["structure", "organization", "coordinator"]],
  "/dosen": ["Lecturers", "Teaching staff of the Information Technology Study Program", ["lecturer", "teacher", "publication"]],
  "/sarana-prasarana": ["Facilities", "Laboratories, classrooms, and supporting facilities of the Information Technology Study Program", ["facilities", "laboratory", "room", "building"]],
  "/mata-kuliah": ["Courses", "List of Study Program courses (code, name, semester, credits, type)", ["course", "subject", "curriculum", "credit", "semester"]],
  "/prospek-lulusan": ["Graduate Prospects", "Digital career opportunities for Information Technology graduates", ["prospect", "graduate", "career", "job"]],
  "/mahasiswa-berprestasi": ["Outstanding Students", "Outstanding students of the Information Technology Study Program", ["outstanding", "achievement", "student", "award"]],
  "/ranking": ["Student Ranking", "SAW ranking: Academic Score, Academic Achievement, Non-Academic Achievement, Organizational Activity", ["ranking", "rank", "score", "weight"]],
  "/testimoni": ["Alumni Testimonials", "Stories from Information Technology alumni", ["testimonial", "alumni", "story", "company"]],
  "/lowongan-pekerjaan": ["Job Vacancies", "Job and internship vacancies", ["vacancy", "job", "work", "internship", "career"]],
  "/berita": ["News", "Information: Study Program news and student activities (seminars, competitions, community service, industry visits)", ["news", "activity", "seminar", "workshop", "competition"]],
  "/akamawa": ["AKAMAWA", "Information: Politala Academic and Student Affairs Services", ["information", "service", "scholarship"]],
  "/kode-etik": ["Student Code of Conduct", "Information: Student Code of Conduct document (PDF read directly on the website)", ["code of conduct", "ethics", "rules", "document"]],
  "/pengumuman": ["Announcements", "Announcements for outstanding students (sent via email & dashboard)", ["announcement", "notice"]],
  "/login": ["System Login", "Sign in as a Student or Program Staff (including Sign in with Google)", ["login", "sign in", "dashboard", "student", "staff"]]
};
var SITE_LANG_EN = document.documentElement.lang === 'en';
if (SITE_LANG_EN) {
  SITE_SEARCH_INDEX.forEach(function (item) {
    var t = SITE_SEARCH_EN[item.url];
    if (t) { item.title = t[0]; item.desc = t[1]; item.keywords = item.keywords.concat(t[2]); }
  });
}

document.addEventListener('DOMContentLoaded', function () {
  var searchToggle = document.querySelector('.search-toggle');
  var searchPanel = document.getElementById('searchPanel');
  var searchInput = document.getElementById('searchInput');
  var searchClose = document.getElementById('searchClose');
  var searchResults = document.getElementById('searchResults');

  if (!searchToggle || !searchPanel || !searchInput || !searchResults) { return; }

  function openSearch(){
    searchPanel.classList.add('show');
    renderSearchResults('');
    setTimeout(function(){ searchInput.focus(); }, 250);
  }
  function closeSearch(){
    searchPanel.classList.remove('show');
    searchInput.value = '';
  }
  function toggleSearch(){
    if(searchPanel.classList.contains('show')){ closeSearch(); } else { openSearch(); }
  }

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, function(c){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
    });
  }

  function searchSite(query){
    var q = query.trim().toLowerCase();
    if(!q) return null;
    return SITE_SEARCH_INDEX.filter(function(item){
      if(item.title.toLowerCase().indexOf(q) !== -1) return true;
      if(item.desc.toLowerCase().indexOf(q) !== -1) return true;
      return item.keywords.some(function(k){ return k.indexOf(q) !== -1; });
    });
  }

  function renderSearchResults(query){
    var q = query.trim();
    if(!q){
      searchResults.innerHTML =
        '<div class="search-hint"><i class="fa-solid fa-magnifying-glass"></i>' + (SITE_LANG_EN ? 'Type a keyword such as "lecturer", "achievement", or "project" to search IT Program information.' : 'Ketik kata kunci seperti "dosen", "prestasi", atau "project" untuk mencari informasi Prodi TI.') + '</div>';
      return;
    }
    var results = searchSite(q);
    if(!results || results.length === 0){
      searchResults.innerHTML =
        '<div class="search-empty"><i class="fa-solid fa-folder-open"></i>' + (SITE_LANG_EN ? 'No results found.' : 'Tidak ada hasil yang ditemukan.') + '</div>';
      return;
    }
    var html = '<div class="search-results-label">' + results.length + (SITE_LANG_EN ? ' RESULTS FOUND' : ' HASIL DITEMUKAN') + '</div>';
    results.forEach(function(item){
      html += '<a class="search-result-item" href="' + item.url + '">' +
        '<div class="search-result-icon"><i class="fa-solid ' + item.icon + '"></i></div>' +
        '<div class="search-result-text"><div class="search-result-title">' + escapeHtml(item.title) + '</div>' +
        '<div class="search-result-desc">' + escapeHtml(item.desc) + '</div></div>' +
        '<i class="fa-solid fa-arrow-right search-result-arrow"></i>' +
        '</a>';
    });
    searchResults.innerHTML = html;
  }

  searchToggle.addEventListener('click', function(e){
    e.stopPropagation();
    toggleSearch();
  });
  if(searchClose){
    searchClose.addEventListener('click', function(e){
      e.stopPropagation();
      closeSearch();
    });
  }
  searchInput.addEventListener('input', function(){
    renderSearchResults(searchInput.value);
  });
  searchPanel.addEventListener('click', function(e){ e.stopPropagation(); });

  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape' && searchPanel.classList.contains('show')){
      closeSearch();
    }
  });
  document.addEventListener('click', function(e){
    if(searchPanel.classList.contains('show') && !searchPanel.contains(e.target) && e.target !== searchToggle){
      closeSearch();
    }
  });
});
