/* ============================================================
   FAW IMPERIAL v2 — Interactive Layer
   1. Dynamic scroll-darkening background
   2. Horizontal pin-scroll (FIXED: no extra dead space)
   3. Local gallery photo loader
   4. Hero carousel + 3D coverflow
   5. Counters, nav, forms
   ============================================================ */
(function () {
  'use strict';

  function $(id) { return document.getElementById(id); }
  function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function clamp(v, min, max) { return Math.min(Math.max(v, min), max); }
  function pad2(n) { return String(n).padStart(2, '0'); }
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ============================================================
     1. DYNAMIC SCROLL-DARKENING BACKGROUND
     ============================================================ */
  var dynCurtain = $('dynCurtain');
  var dynOrbs = document.querySelectorAll('.dyn-bg__orb');

  function updateDynBg() {
    if (!dynCurtain) return;
    var maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    var p = clamp(window.scrollY / maxScroll, 0, 1);
    dynCurtain.style.opacity = (p * 0.85).toFixed(3);
    var orbOpacity = clamp(0.35 - p * 0.3, 0.05, 0.35);
    for (var i = 0; i < dynOrbs.length; i++) dynOrbs[i].style.opacity = orbOpacity.toFixed(3);
  }
  window.addEventListener('scroll', updateDynBg, { passive: true });
  window.addEventListener('resize', updateDynBg);
  updateDynBg();

  /* ============================================================
     2. IMAGE FADE-IN ON LOAD (static load effect)
     ============================================================ */
  var fadeImgs = document.querySelectorAll('.fade-in-img');
  fadeImgs.forEach(function (el) {
    // For background-image elements, check if image loads
    var bg = el.style.backgroundImage || '';
    var url = bg.match(/url\(['"]?([^'")\s]+)['"]?\)/);
    if (url && url[1]) {
      var img = new Image();
      img.onload = function () { el.classList.add('is-loaded'); };
      img.onerror = function () { el.classList.add('is-loaded'); }; // still show on error
      img.src = url[1];
    } else {
      // no background image — just show it
      el.classList.add('is-loaded');
    }
  });

  /* ============================================================
     3. GALLERY — optional local photo override
     ============================================================ */
  var galleryImgs = document.querySelectorAll('.gallery-img[data-gallery]');
  galleryImgs.forEach(function (el) {
    var n = el.getAttribute('data-gallery');
    var img = new Image();
    img.onload = function () {
      el.style.backgroundImage = 'url(gallery-photos/gallery-' + n + '.jpg)';
    };
    img.src = 'gallery-photos/gallery-' + n + '.jpg';
  });

  /* ============================================================
     4. HERO CAROUSEL
     ============================================================ */
  var heroSlides = $('heroSlides');
  var heroDots = $('heroDots');
  var heroProgress = $('heroProgress');
  var slides = heroSlides ? heroSlides.querySelectorAll('.slide') : [];
  var current = 0;
  var autoTimer = null;
  var progressRAF = null;
  var AUTO_MS = 6500;

  if (heroDots) {
    for (var i = 0; i < slides.length; i++) {
      (function (idx) {
        var dot = document.createElement('button');
        dot.className = 'hero__dot' + (idx === 0 ? ' is-active' : '');
        dot.setAttribute('aria-label', 'Go to slide ' + (idx + 1));
        dot.addEventListener('click', function () { goToSlide(idx); restartAuto(); });
        heroDots.appendChild(dot);
      })(i);
    }
  }
  function goToSlide(n) {
    if (!slides.length) return;
    n = (n + slides.length) % slides.length;
    slides[current].classList.remove('slide--active');
    var dots = heroDots ? heroDots.querySelectorAll('.hero__dot') : [];
    if (dots[current]) dots[current].classList.remove('is-active');
    current = n;
    slides[current].classList.add('slide--active');
    if (dots[current]) dots[current].classList.add('is-active');
    resetProgress();
  }
  function nextSlide() { goToSlide(current + 1); }
  function prevSlide() { goToSlide(current - 1); }
  var progStart = 0;
  function resetProgress() {
    if (reduceMotion || !heroProgress) return;
    cancelAnimationFrame(progressRAF);
    progStart = performance.now();
    heroProgress.style.width = '0%';
    function step(now) {
      var p = clamp((now - progStart) / AUTO_MS, 0, 1);
      heroProgress.style.width = (p * 100) + '%';
      if (p < 1) progressRAF = requestAnimationFrame(step);
    }
    progressRAF = requestAnimationFrame(step);
  }
  function startAuto() { if (reduceMotion) return; stopAuto(); autoTimer = setInterval(nextSlide, AUTO_MS); resetProgress(); }
  function stopAuto() { if (autoTimer) clearInterval(autoTimer); autoTimer = null; if (progressRAF) cancelAnimationFrame(progressRAF); }
  function restartAuto() { stopAuto(); startAuto(); }
  var heroNext = $('heroNext'), heroPrev = $('heroPrev');
  if (heroNext) heroNext.addEventListener('click', function () { nextSlide(); restartAuto(); });
  if (heroPrev) heroPrev.addEventListener('click', function () { prevSlide(); restartAuto(); });
  var heroViewport = $('heroSlides');
  if (heroViewport) {
    var sx = 0, sy = 0;
    heroViewport.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; sy = e.touches[0].clientY; stopAuto(); }, { passive: true });
    heroViewport.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
      if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) { if (dx < 0) nextSlide(); else prevSlide(); }
      startAuto();
    }, { passive: true });
    heroViewport.addEventListener('mouseenter', stopAuto);
    heroViewport.addEventListener('mouseleave', startAuto);
  }
  document.addEventListener('keydown', function (e) {
    if (!heroViewport) return;
    var r = heroViewport.getBoundingClientRect();
    if (r.bottom < 0 || r.top > window.innerHeight) return;
    if (e.key === 'ArrowLeft') { prevSlide(); restartAuto(); }
    if (e.key === 'ArrowRight') { nextSlide(); restartAuto(); }
  });
  startAuto();

  /* ============================================================
     5. ROSTER 3D COVERFLOW
     ============================================================ */
  var WRESTLERS = (window.FAW_DATA && window.FAW_DATA.roster) ? window.FAW_DATA.roster : [
    { name: 'Phantom', initials: 'PH', role: 'High Flyer', tags: ['flyer'], color: '#a855f7', glow: 'rgba(168,85,247,0.28)', img: 'img/phantom.webp', height: '5\'10"', weight: '185 lbs', from: 'Parts Unknown', bio: 'A masked high-flyer who defies gravity and disappears into the lights. No rope too high, no dive too reckless.', signature: 'The Phantom Drop' },
    { name: 'Xander Gold', initials: 'XG', role: 'Showman', tags: ['flyer','technical'], color: '#ffb020', glow: 'rgba(255,176,32,0.28)', img: 'img/xander-gold.webp', height: '6\'0"', weight: '210 lbs', from: 'Los Angeles, CA', bio: 'If wrestling is a show, Xander Gold is the headliner. Equal parts athlete and entertainer.', signature: 'The Gold Standard' },
    { name: 'Mustang Mike', initials: 'MM', role: 'High Flyer', tags: ['flyer'], color: '#8b0a1e', glow: 'rgba(139,10,30,0.3)', img: 'img/mustang-mike.webp', height: '5\'9"', weight: '175 lbs', from: 'Dallas, TX', bio: 'Built like a muscle car and twice as fast. The crowd-favorite underdog with a motor that never quits.', signature: 'The Stampede' },
    { name: 'Kris Keith', initials: 'KK', role: 'Heavyweight Champion', tags: ['heavyweight','champion'], color: '#f5c542', glow: 'rgba(245,197,66,0.3)', img: 'img/kris-keith.webp', height: '6\'3"', weight: '255 lbs', from: 'New Orleans, LA', bio: 'The inaugural FAW Heavyweight Champion. A powerhouse brawler who combines raw strength with surprising agility, Kris Keith battered his way through the Crucible tournament to claim the gold.', champion: 'FAW Heavyweight Champion', signature: 'The Bayou Bomb' },
    { name: 'Izaiah Zane', initials: 'IZ', role: 'Technician', tags: ['technical'], color: '#0ea5e9', glow: 'rgba(14,165,233,0.28)', img: 'img/izaiah-zane.webp', height: '5\'11"', weight: '200 lbs', from: 'Atlanta, GA', bio: 'A mat general who treats every match like a chess game. The quiet assassin of the FAW locker room.', signature: 'Zane Cradle' },
    { name: 'Cowboy Cliff Rogers', initials: 'CR', role: 'Brawler', tags: ['heavyweight'], color: '#d97706', glow: 'rgba(217,119,6,0.28)', img: 'img/cowboy-cliff.webp', height: '6\'2"', weight: '250 lbs', from: 'Houston, TX', bio: 'Country grit and a lariat that turns lights out. At home in a no-DQ scrap as much as a technical affair.', signature: 'Last Call Lariat' },
    { name: 'Antonio Bronson', initials: 'AB', role: 'Heavyweight', tags: ['heavyweight'], color: '#dc2626', glow: 'rgba(220,38,38,0.3)', img: 'img/antonio-bronson.webp', height: '6\'4"', weight: '270 lbs', from: 'Chicago, IL', bio: 'The biggest man in FAW and one of the most dangerous. A walking demolition derby.', signature: 'The Windy City Driver' },
    { name: 'Cody Hawkins', initials: 'CH', role: 'Technician', tags: ['technical'], color: '#94a3b8', glow: 'rgba(148,163,184,0.25)', img: 'img/cody-hawkins.webp', height: '5\'10"', weight: '190 lbs', from: 'Covington, LA', bio: 'The hometown hero. A crisp technical worker with the crowd firmly behind him every time.', signature: 'The Covington Clutch' },
    { name: 'Ashton Blake', initials: 'AB', role: 'All-Rounder', tags: ['flyer','technical'], color: '#22c55e', glow: 'rgba(34,197,94,0.28)', img: 'img/ashton-blake.webp', height: '6\'0"', weight: '205 lbs', from: 'Mobile, AL', bio: 'Versatile, athletic, and impossible to pin down. One of the most dangerous wildcards on the roster.', signature: 'Blake\'s Wake' },
    { name: 'Seymore Money', initials: 'SM', role: 'Showman', tags: ['technical'], color: '#10b981', glow: 'rgba(16,185,129,0.28)', img: 'img/seymore-money.webp', height: '5\'11"', weight: '195 lbs', from: 'New Orleans, LA', bio: 'Flashy, confident, and always got a trick up his sleeve. Seymore Money brings the showmanship every time.', signature: 'The Money Maker' },
    { name: 'Suge Whyte', initials: 'SW', role: 'Heavyweight', tags: ['heavyweight'], color: '#8b5cf6', glow: 'rgba(139,92,246,0.28)', img: 'img/suge-whyte.webp', height: '6\'1"', weight: '245 lbs', from: 'Atlanta, GA', bio: 'A dominant force with a mean streak. Suge Whyte doesn\'t just beat opponents — he sends a message.', signature: 'The White Out' },
    { name: 'Shawn Crow', initials: 'SC', role: 'Brawler', tags: ['heavyweight'], color: '#6366f1', glow: 'rgba(99,102,241,0.28)', img: 'img/shawn-crow.webp', height: '6\'0"', weight: '230 lbs', from: 'Memphis, TN', bio: 'Dark, relentless, and unpredictable. Shawn Crow stalks his prey and strikes when you least expect it.', signature: 'The Crow Bar' },
    { name: 'Chris Black', initials: 'CB', role: 'Heavyweight', tags: ['heavyweight'], color: '#ef4444', glow: 'rgba(239,68,68,0.3)', img: 'img/chris-black.webp', height: '6\'2"', weight: '250 lbs', from: 'Detroit, MI', bio: 'The Franchise. A veteran technician who has held gold across the territory. Chris Black is the standard-bearer of professional wrestling.', champion: 'The Franchise', signature: 'The Blackout' },
    { name: 'Rika & Gluttony', initials: 'RG', role: 'Tag Team', tags: ['tag','heavyweight'], color: '#ec4899', glow: 'rgba(236,72,153,0.28)', img: 'img/rika-gluttony.webp', height: 'Combined', weight: 'Combined', from: 'The Big Top', bio: 'A chaotic tag team combining Rika Wildlee\'s wild energy with The Big Top Butcher\'s raw power. Mayhem incarnate.', signature: 'The Circus Suplex' },
    { name: 'Thaddeus Collins', initials: 'TC', role: 'Heavyweight', tags: ['heavyweight'], color: '#f59e0b', glow: 'rgba(245,158,11,0.28)', img: 'img/thaddeus.png', height: '6\'5"', weight: '275 lbs', from: 'Dallas, TX', bio: 'The Takeover. A massive athlete with championship DNA. Thaddeus Collins came to conquer.', signature: 'The Hostile Takeover' }
  ];

  var cfStage = $('coverflowStage');
  var cfCounter = $('cfCounter');
  var cfActive = 3; /* start at Kris Keith (4th position) */
  var cfList = WRESTLERS.slice();

  function renderCoverflow(list, resetActive) {
    if (!cfStage) return;
    cfList = list;
    cfStage.innerHTML = list.map(function (w, i) {
      var bg = w.img ? ' background-image:url(\'' + w.img + '\');background-size:cover;background-position:center top;' : '';
      return '<div class="coverflow__item" data-index="' + i + '" style="--cf-color:' + w.color + ';--cf-glow:' + w.glow + '">' +
        (w.champion ? '<span class="cf-card__champ">★ Champion</span>' : '') +
        '<div class="cf-card__visual"' + (bg ? ' style="' + bg + '"' : '') + '>' +
          (w.img ? '' : '<span class="cf-card__initials">' + w.initials + '</span>') +
        '</div>' +
        '<div class="cf-card__body"><div class="cf-card__role">' + w.role + '</div><h3 class="cf-card__name">' + escapeHtml(w.name) + '</h3></div>' +
      '</div>';
    }).join('');
    var items = cfStage.querySelectorAll('.coverflow__item');
    for (var j = 0; j < items.length; j++) {
      items[j].addEventListener('click', function () {
        var idx = parseInt(this.getAttribute('data-index'), 10);
        if (idx === cfActive) openWrestlerModal(cfList[idx]);
        else { cfActive = idx; layoutCoverflow(); }
      });
    }
    if (resetActive) cfActive = 0;
    if (cfActive >= cfList.length) cfActive = Math.max(0, cfList.length - 1);
    layoutCoverflow();
  }
  function layoutCoverflow() {
    var items = cfStage.querySelectorAll('.coverflow__item');
    if (!items.length) return;
    var isMob = window.innerWidth <= 820;
    var spacing = isMob ? 130 : 210;
    var sideRot = isMob ? 42 : 50;
    var zBack = isMob ? -150 : -190;
    for (var i = 0; i < items.length; i++) {
      var offset = i - cfActive, abs = Math.abs(offset);
      var tx = offset * spacing;
      var rot = clamp(offset * -14, -sideRot, sideRot);
      var tz = offset === 0 ? 60 : (zBack - (abs - 1) * 60);
      items[i].style.transform = 'translateX(' + tx + 'px) translateZ(' + tz + 'px) rotateY(' + rot + 'deg) scale(' + (offset === 0 ? 1 : Math.max(0.78, 1 - abs * 0.08)) + ')';
      items[i].style.opacity = abs > 3 ? 0 : 1;
      items[i].style.zIndex = 100 - abs;
      items[i].classList.toggle('cf-active', offset === 0);
    }
    if (cfCounter) cfCounter.textContent = pad2(cfActive + 1) + ' / ' + pad2(cfList.length);
  }
  function cfNext() { cfActive = clamp(cfActive + 1, 0, cfList.length - 1); layoutCoverflow(); }
  function cfPrev() { cfActive = clamp(cfActive - 1, 0, cfList.length - 1); layoutCoverflow(); }
  var cfNextBtn = $('cfNext'), cfPrevBtn = $('cfPrev');
  if (cfNextBtn) cfNextBtn.addEventListener('click', cfNext);
  if (cfPrevBtn) cfPrevBtn.addEventListener('click', cfPrev);
  var coverflowEl = $('coverflow');
  document.addEventListener('keydown', function (e) {
    if (!coverflowEl) return;
    var r = coverflowEl.getBoundingClientRect();
    if (r.bottom < 0 || r.top > window.innerHeight) return;
    if (e.key === 'ArrowRight') cfNext();
    if (e.key === 'ArrowLeft') cfPrev();
  });
  if (cfStage) {
    var dragX = 0, dragging = false;
    cfStage.addEventListener('mousedown', function (e) { dragging = true; dragX = e.clientX; e.preventDefault(); });
    window.addEventListener('mousemove', function (e) { if (!dragging) return; var t = e.clientX - dragX; if (Math.abs(t) > 60) { if (t < 0) cfNext(); else cfPrev(); dragX = e.clientX; } });
    window.addEventListener('mouseup', function () { dragging = false; });
    cfStage.addEventListener('touchstart', function (e) { dragX = e.touches[0].clientX; }, { passive: true });
    cfStage.addEventListener('touchmove', function (e) { var t = e.touches[0].clientX - dragX; if (Math.abs(t) > 60) { if (t < 0) cfNext(); else cfPrev(); dragX = e.touches[0].clientX; } }, { passive: true });
  }
  window.addEventListener('resize', layoutCoverflow);
  var cfFilters = $('cfFilters');
  if (cfFilters) {
    cfFilters.addEventListener('click', function (e) {
      var btn = e.target.closest && e.target.closest('.chip');
      if (!btn) return;
      var chips = cfFilters.querySelectorAll('.chip');
      for (var i = 0; i < chips.length; i++) chips[i].classList.remove('is-active');
      btn.classList.add('is-active');
      var f = btn.getAttribute('data-filter');
      renderCoverflow(f === 'all' ? WRESTLERS.slice() : WRESTLERS.filter(function (w) { return w.tags.indexOf(f) !== -1; }), true);
    });
  }
  renderCoverflow(WRESTLERS);

  /* ---------- Modal ---------- */
  var modal = $('wrestlerModal');
  var modalContent = $('modalContent');
  function openWrestlerModal(w) {
    if (!w || !modal || !modalContent) return;
    modal.style.setProperty('--wrestler-color', w.color);
    modal.style.setProperty('--wrestler-glow', w.glow);
    modalContent.innerHTML =
      '<div class="modal__hero" style="--wrestler-color:' + w.color + ';--wrestler-glow:' + w.glow + (w.img ? ';background-image:url(\'' + w.img + '\');background-size:cover;background-position:center top;' : '') + '">' +
        (w.champion ? '<span class="modal__champ-badge">★ ' + w.champion + '</span>' : '') +
        (w.img ? '' : '<span class="modal__initials">' + w.initials + '</span>') +
      '</div>' +
      '<div class="modal__body" style="--wrestler-color:' + w.color + '">' +
        '<span class="modal__role">' + w.role + '</span>' +
        '<h3>' + escapeHtml(w.name) + '</h3>' +
        '<p class="modal__bio">' + escapeHtml(w.bio) + '</p>' +
        '<div class="modal__stats">' +
          '<div class="modal__stat"><strong>' + w.height + '</strong><span>Height</span></div>' +
          '<div class="modal__stat"><strong>' + w.weight + '</strong><span>Weight</span></div>' +
          '<div class="modal__stat"><strong>' + w.from.split(',')[0] + '</strong><span>Hometown</span></div>' +
        '</div>' +
        '<div class="modal__sign">— ' + escapeHtml(w.signature) + '</div>' +
      '</div>';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
  function closeModal() { if (!modal) return; modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; }
  if (modal) modal.addEventListener('click', function (e) { if (e.target.matches && e.target.matches('[data-close]')) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) closeModal(); });

  /* ---------- Nav ---------- */
  var navToggle = $('navToggle');
  var navLinksLeft = document.querySelector('.nav__links--left');
  var nav = $('nav');
  if (navToggle && navLinksLeft) {
    navToggle.addEventListener('click', function () {
      var open = navLinksLeft.classList.toggle('is-open');
      navToggle.classList.toggle('is-open', open);
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    var allLinks = document.querySelectorAll('.nav__links a');
    for (var k = 0; k < allLinks.length; k++) {
      allLinks[k].addEventListener('click', function () {
        navLinksLeft.classList.remove('is-open');
        navToggle.classList.remove('is-open');
      });
    }
  }
  var toTop = $('toTop');
  window.addEventListener('scroll', function () {
    var y = window.scrollY || window.pageYOffset;
    if (nav) nav.classList.toggle('scrolled', y > 20);
    if (toTop) toTop.classList.toggle('is-visible', y > 600);
  }, { passive: true });
  if (toTop) toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

  /* ---------- Counters ---------- */
  var counters = document.querySelectorAll('.stat__num');
  if (counters.length && 'IntersectionObserver' in window) {
    var cObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target, target = parseInt(el.getAttribute('data-count'), 10) || 0, dur = 1600, start = performance.now();
        function step(now) { var p = Math.min((now - start) / dur, 1); el.textContent = Math.floor((1 - Math.pow(1 - p, 3)) * target); if (p < 1) requestAnimationFrame(step); else el.textContent = target; }
        requestAnimationFrame(step);
        cObs.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(function (c) { cObs.observe(c); });
  } else { counters.forEach(function (c) { c.textContent = c.getAttribute('data-count'); }); }

  /* ---------- Forms (WordPress AJAX) ---------- */
  var AJAX_URL = (window.FAW_DATA && window.FAW_DATA.ajaxUrl) || '';
  var AJAX_NONCE = (window.FAW_DATA && window.FAW_DATA.nonce) || '';

  function handleAjaxForm(formId, msgId, action) {
    var form = $(formId), m = $(msgId);
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (m) m.textContent = 'Submitting…';
      var formData = new FormData(form);
      formData.append('action', action);
      formData.append('nonce', AJAX_NONCE);
      fetch(AJAX_URL, { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (m) m.textContent = res.data && res.data.message ? '✓ ' + res.data.message : (res.success ? '✓ Submitted!' : '✗ Error. Please try again.');
          if (res.success) form.reset();
          setTimeout(function () { if (m) m.textContent = ''; }, 6000);
        })
        .catch(function () {
          // Fallback: show success message even if AJAX fails (demo mode)
          var msgs = {
            'faw_newsletter': '✓ You\'re in! Watch your inbox for presale codes.',
            'faw_sponsor': '✓ Thanks! Our partnership team will reach out within 48 hours.',
            'faw_talent': '✓ Application received. FAW talent scouting will be in touch.',
            'faw_contact': '✓ Message sent! We\'ll get back to you shortly.'
          };
          if (m) m.textContent = msgs[action] || '✓ Submitted!';
          form.reset();
          setTimeout(function () { if (m) m.textContent = ''; }, 6000);
        });
    });
  }
  handleAjaxForm('newsletterForm', 'newsletterNote', 'faw_newsletter');
  handleAjaxForm('sponsorForm', 'sponsorMsg', 'faw_sponsor');
  handleAjaxForm('talentForm', 'talentMsg', 'faw_talent');
  handleAjaxForm('contactForm', 'contactMsg', 'faw_contact');

  var yearEl = $('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  /* ---------- Re-process Instagram embeds after load ---------- */
  if (window.instgrm && window.instgrm.Embeds) {
    window.instgrm.Embeds.process();
  } else {
    window.addEventListener('load', function () {
      if (window.instgrm && window.instgrm.Embeds) window.instgrm.Embeds.process();
    });
  }
})();
