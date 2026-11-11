(function () {
  'use strict';

  var revealAll = function () {
    var els = document.querySelectorAll('[data-aos]');
    for (var i = 0; i < els.length; i++) { els[i].classList.add('aos-animate'); }
  };
  var hasIO = ('IntersectionObserver' in window);
  var supportsFetch = ('fetch' in window);

  /* ---------- Preloader ---------- */
  var preloader = document.getElementById('preloader');
  var hidePreloader = function () {
    if (preloader) preloader.classList.add('hidden');
  };
  window.addEventListener('load', hidePreloader);
  setTimeout(hidePreloader, 2200);

  /* ---------- Toast ---------- */
  var toastBox = document.getElementById('toastContainer');
  function toast(msg, type) {
    type = type || 'success';
    if (!toastBox) return;
    var el = document.createElement('div');
    el.className = 'toast toast-' + type;
    var icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
    el.innerHTML = '<i class="fa-solid ' + icon + '"></i><span>' + msg + '</span>';
    toastBox.appendChild(el);
    setTimeout(function () { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(function () { el.remove(); }, 320); }, 4000);
  }
  window.showToast = toast;

  /* ---------- Navbar ---------- */
  var navbar = document.getElementById('navbar');
  var backToTop = document.getElementById('backToTop');
  var navToggle = document.getElementById('navToggle');
  var navMenu = document.getElementById('navMenu');
  var navLinks = document.querySelectorAll('.nav-link');

  function onScroll() {
    var y = window.scrollY || 0;
    if (navbar) navbar.classList.toggle('scrolled', y > 30);
    if (backToTop) backToTop.classList.toggle('show', y > 600);
  }

  function scrollSpy() {
    var sections = ['home', 'about', 'portfolio', 'services', 'contact'];
    var current = 'home';
    for (var i = 0; i < sections.length; i++) {
      var el = document.getElementById(sections[i]);
      if (el && el.getBoundingClientRect().top <= 120) current = sections[i];
    }
    for (var j = 0; j < navLinks.length; j++) {
      navLinks[j].classList.toggle('active', navLinks[j].getAttribute('data-id') === current);
    }
  }

  window.addEventListener('scroll', function () { onScroll(); scrollSpy(); }, { passive: true });
  onScroll();

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('open');
      var icon = navToggle.querySelector('i');
      icon.className = navMenu.classList.contains('open') ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
    });
    for (var k = 0; k < navLinks.length; k++) {
      navLinks[k].addEventListener('click', function () {
        navMenu.classList.remove('open');
        var icon = navToggle.querySelector('i');
        icon.className = 'fa-solid fa-bars';
      });
    }
  }

  /* ---------- Typing effect ---------- */
  var typer = document.getElementById('typer');
  if (typer) {
    var words = [];
    try { words = JSON.parse(typer.getAttribute('data-words') || '[]'); } catch (e) { words = []; }
    if (words.length) {
      var wi = 0, ci = 0, deleting = false;
      (function type() {
        var word = words[wi] || '';
        typer.textContent = word.substring(0, ci);
        var delay = deleting ? 50 : 110;
        if (!deleting && ci === word.length) { delay = 1600; deleting = true; }
        else if (deleting && ci === 0) { deleting = false; wi = (wi + 1) % words.length; delay = 400; }
        ci += deleting ? -1 : 1;
        setTimeout(type, delay);
      })();
    }
  }

  /* ---------- Reveal on scroll (safe for old browsers) ---------- */
  try {
    if (hasIO) {
      var revealObs = new IntersectionObserver(function (entries) {
        for (var i = 0; i < entries.length; i++) {
          if (entries[i].isIntersecting) {
            entries[i].target.classList.add('aos-animate');
            revealObs.unobserve(entries[i].target);
          }
        }
      }, { threshold: 0.12 });
      var aosEls = document.querySelectorAll('[data-aos]');
      for (var a = 0; a < aosEls.length; a++) { revealObs.observe(aosEls[a]); }
    } else {
      revealAll();
    }
  } catch (e) {
    revealAll();
  }

  /* ---------- Counters ---------- */
  var counters = document.querySelectorAll('.counter');
  if (hasIO) {
    try {
      var counterObs = new IntersectionObserver(function (entries) {
        for (var i = 0; i < entries.length; i++) {
          if (!entries[i].isIntersecting) continue;
          var el = entries[i].target;
          var target = parseInt(el.getAttribute('data-target') || '0', 10);
          var dur = 1400;
          var start = performance.now();
          (function tick(now) {
            var p = Math.min((now - start) / dur, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(eased * target);
            if (p < 1) requestAnimationFrame(tick);
          })(start);
          counterObs.unobserve(el);
        }
      }, { threshold: 0.5 });
      for (var c = 0; c < counters.length; c++) { counterObs.observe(counters[c]); }
    } catch (e) {
      for (var c2 = 0; c2 < counters.length; c2++) {
        counters[c2].textContent = counters[c2].getAttribute('data-target') || '0';
      }
    }
  } else {
    for (var c3 = 0; c3 < counters.length; c3++) { counters[c3].textContent = counters[c3].getAttribute('data-target') || '0'; }
  }

  /* ---------- Skill bars ---------- */
  var skillEls = document.querySelectorAll('.skill');
  function fillSkill(skill) {
    var fill = skill.querySelector('.skill-fill');
    var pct = skill.querySelector('.skill-pct');
    var level = fill ? parseInt(fill.getAttribute('data-level') || '0', 10) : 0;
    if (pct) pct.textContent = level + '%';
    if (fill) fill.style.width = level + '%';
  }
  if (hasIO) {
    try {
      var skillObs = new IntersectionObserver(function (entries) {
        for (var i = 0; i < entries.length; i++) {
          if (!entries[i].isIntersecting) continue;
          fillSkill(entries[i].target);
          skillObs.unobserve(entries[i].target);
        }
      }, { threshold: 0.4 });
      for (var s = 0; s < skillEls.length; s++) { skillObs.observe(skillEls[s]); }
    } catch (e) {
      for (var s2 = 0; s2 < skillEls.length; s2++) { fillSkill(skillEls[s2]); }
    }
  } else {
    for (var s3 = 0; s3 < skillEls.length; s3++) { fillSkill(skillEls[s3]); }
  }

  /* ---------- Portfolio filter + search ---------- */
  var filterBtns = document.querySelectorAll('.filter-btn');
  var searchInput = document.getElementById('projectSearch');
  var cards = document.querySelectorAll('.project-card');
  var emptyState = document.getElementById('projectsEmpty');
  var currentFilter = 'all';

  function applyFilter() {
    var q = ((searchInput && searchInput.value) || '').trim().toLowerCase();
    var visible = 0;
    for (var i = 0; i < cards.length; i++) {
      var cat = cards[i].getAttribute('data-category') || '';
      var title = (cards[i].getAttribute('data-title') || '').toLowerCase();
      var matchCat = currentFilter === 'all' || cat === currentFilter;
      var matchQ = q === '' || title.indexOf(q) !== -1;
      var show = matchCat && matchQ;
      cards[i].classList.toggle('hiding', !show);
      if (show) visible++;
    }
    if (emptyState) emptyState.hidden = visible > 0;
  }

  for (var b = 0; b < filterBtns.length; b++) {
    filterBtns[b].addEventListener('click', function () {
      for (var i = 0; i < filterBtns.length; i++) { filterBtns[i].classList.remove('active'); }
      this.classList.add('active');
      currentFilter = this.getAttribute('data-filter');
      applyFilter();
    });
  }
  if (searchInput) searchInput.addEventListener('input', applyFilter);

  /* ---------- Project modal ---------- */
  var modal = document.getElementById('projectModal');
  var modalBody = document.getElementById('projectModalBody');

  function projectRequestUrl(title) {
    var subj = 'طلب مشروع: ' + title;
    var form = document.getElementById('contactForm');
    if (form) {
      var sub = document.getElementById('cSubject');
      if (sub) sub.value = subj;
      if (form.scrollIntoView) form.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return '#contact';
    }
    return '../تواصل/?subject=' + encodeURIComponent(subj);
  }

  function openModal(card) {
    if (!modal || !modalBody) return;
    var img = card.getAttribute('data-image');
    var hasImg = img && img.trim() !== '';
    modalBody.innerHTML =
      '<div class="modal-hero">' +
      (hasImg ? '<img src="' + img + '" alt="' + card.getAttribute('data-title') + '">' : '<div class="modal-noimg"><i class="fa-solid fa-diagram-project"></i></div>') +
      '</div>' +
      '<div class="modal-content">' +
      '<span class="modal-cat"><i class="fa-solid fa-folder"></i> ' + card.getAttribute('data-category-name') + '</span>' +
      '<h3 class="modal-title">' + card.getAttribute('data-title') + '</h3>' +
      '<p>' + card.getAttribute('data-description') + '</p>' +
      '<div class="modal-actions"><a class="btn btn-primary" href="' + projectRequestUrl(card.getAttribute('data-title')) + '"><i class="fa-solid fa-envelope-open-text"></i> اطلب هذا المشروع أو مشابهاً له</a></div>' +
      '</div>';
    modal.classList.add('open');
    document.body.classList.add('no-scroll');
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove('open');
    document.body.classList.remove('no-scroll');
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest && e.target.closest('[data-close]')) closeModal();
  });
  document.addEventListener('click', function (e) {
    var viewBtn = e.target.closest ? e.target.closest('.project-view') : null;
    if (!viewBtn) return;
    var card = viewBtn.closest('.project-card');
    if (!card) return;
    openModal(card);
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

  /* ---------- Prefill subject from ?subject= ---------- */
  (function () {
    try {
      var m = window.location.search.match(/[?&]subject=([^&]+)/);
      if (m) {
        var sub = document.getElementById('cSubject');
        if (sub) sub.value = decodeURIComponent(m[1].replace(/\+/g, ' '));
      }
    } catch (e) {}
  })();

  /* ---------- Order button prefill ---------- */
  document.addEventListener('click', function (e) {
    var orderBtn = e.target.closest ? e.target.closest('.order-btn') : null;
    if (!orderBtn) return;
    var service = orderBtn.getAttribute('data-service');
    var subject = document.getElementById('cSubject');
    if (subject && service) subject.value = 'طلب خدمة: ' + service;
  });

  /* ---------- Contact form ---------- */
  var cForm = document.getElementById('contactForm');
  if (cForm) {
    var submitBtn = document.getElementById('contactSubmit');
    var labels = {
      name: 'يرجى إدخال الاسم بشكل صحيح (حرفين على الأقل).',
      email: 'يرجى إدخال بريد إلكتروني صحيح.',
      subject: 'يرجى إدخال موضوع الرسالة (3 أحرف على الأقل).',
      message: 'الرسالة يجب أن تكون 10 أحرف على الأقل.',
    };

    function setError(field, msg) {
      var input = document.getElementById(field);
      if (!input) return;
      var err = input.parentElement.querySelector('.field-error');
      input.classList.toggle('input-error', !!msg);
      if (err) err.textContent = msg || '';
    }

    function validate() {
      var values = {
        name: cForm.elements.name.value.trim(),
        email: cForm.elements.email.value.trim(),
        subject: cForm.elements.subject.value.trim(),
        message: cForm.elements.message.value.trim(),
      };
      var ok = true;
      var rules = {
        name: function (v) { return v.length >= 2; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); },
        subject: function (v) { return v.length >= 3; },
        message: function (v) { return v.length >= 10; },
      };
      var keys = ['name', 'email', 'subject', 'message'];
      for (var i = 0; i < keys.length; i++) {
        var good = rules[keys[i]](values[keys[i]]);
        setError(keys[i], good ? '' : labels[keys[i]]);
        if (!good) ok = false;
      }
      return ok ? values : null;
    }

    cForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var values = validate();
      if (!values) return;

      var render = function (loading) {
        var label = submitBtn.querySelector('.btn-label');
        var spinner = submitBtn.querySelector('.btn-loading');
        if (!submitBtn) return;
        if (loading) {
          if (label) label.hidden = true;
          if (spinner) spinner.hidden = false;
          submitBtn.disabled = true;
        } else {
          if (label) label.hidden = false;
          if (spinner) spinner.hidden = true;
          submitBtn.disabled = false;
        }
      };

      if (!supportsFetch) {
        toast('متصفحك قديم ليدعم الإرسال التلقائي — أرسل الرسالة عبر واتساب.', 'error');
        return;
      }

      render(true);
      fetch('contact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(values),
      }).then(function (res) { return res.json(); }).then(function (data) {
        render(false);
        if (data.success) {
          toast(data.message, 'success');
          cForm.reset();
        } else if (data.errors) {
          var keys = Object.keys(data.errors);
          for (var i = 0; i < keys.length; i++) { setError(keys[i], data.errors[keys[i]]); }
          toast('يرجى تصحيح الأخطاء في النموذج.', 'error');
        } else {
          toast(data.message || 'حدث خطأ، حاول مرة أخرى.', 'error');
        }
      }).catch(function () {
        render(false);
        toast('تعذر الاتصال بالخادم، تحقق من اتصالك.', 'error');
      });
    });
  }

})();