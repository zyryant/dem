/* ==========================================================================
   Интерактив портала: меню, анимации, счётчики, фильтр каталога,
   слайдер, модальные окна, маска даты, тосты, кнопка «наверх».
   ========================================================================== */
(function () {
  'use strict';

  /* --- Шапка: тень при прокрутке --- */
  const header = document.getElementById('siteHeader');
  const onScroll = () => {
    if (header) header.classList.toggle('scrolled', window.scrollY > 10);
    const top = document.getElementById('toTop');
    if (top) top.classList.toggle('show', window.scrollY > 400);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* --- Бургер-меню --- */
  const burger = document.getElementById('burger');
  const nav = document.getElementById('mainNav');
  if (burger && nav) {
    burger.addEventListener('click', () => {
      burger.classList.toggle('open');
      nav.classList.toggle('open');
    });
    nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
      burger.classList.remove('open');
      nav.classList.remove('open');
    }));
  }

  /* --- Кнопка «наверх» --- */
  const toTop = document.getElementById('toTop');
  if (toTop) toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  /* --- Появление элементов при прокрутке --- */
  const reveal = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { threshold: 0.12 });
    reveal.forEach(el => io.observe(el));
  } else {
    reveal.forEach(el => el.classList.add('in'));
  }

  /* --- Счётчики: анимация появления + счёт от 0 --- */
  const counters = document.querySelectorAll('[data-count]');
  counters.forEach(el => {
    el.style.opacity = '0';
    el.textContent = '0';
  });
  if ('IntersectionObserver' in window) {
    const cio = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (!en.isIntersecting) return;
        const el = en.target;
        const target = +el.dataset.count;
        const duration = 1200;
        const start = performance.now();
        const tick = (now) => {
          const p = Math.min((now - start) / duration, 1);
          const ease = 1 - Math.pow(1 - p, 3);
          el.style.opacity = ease;
          el.textContent = Math.round(ease * target).toLocaleString('ru-RU');
          if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
        cio.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(el => cio.observe(el));
  } else {
    counters.forEach(el => {
      el.style.opacity = '1';
      el.textContent = (+el.dataset.count).toLocaleString('ru-RU');
    });
  }

  /* --- Фильтр каталога по категориям --- */
  const chips = document.querySelectorAll('#catFilter .chip');
  const cards = document.querySelectorAll('#catalogGrid .card');
  chips.forEach(chip => chip.addEventListener('click', () => {
    chips.forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    const cat = chip.dataset.cat;
    cards.forEach(card => {
      const show = cat === 'all' || card.dataset.cat === cat;
      card.classList.toggle('hide', !show);
    });
  }));

  /* --- Слайдер: авто-переключение каждые 3 секунды, кнопки, точки --- */
  const slider = document.getElementById('slider');
  if (slider) {
    const track = slider.querySelector('.slides');
    const slides = slider.querySelectorAll('.slide');
    const dotsBox = document.getElementById('slideDots');
    let idx = 0, timer = null;
    const total = slides.length;

    slides.forEach((_, i) => {
      const d = document.createElement('button');
      d.className = 'dot' + (i === 0 ? ' active' : '');
      d.addEventListener('click', () => { go(i); restart(); });
      dotsBox.appendChild(d);
    });
    const dots = dotsBox.querySelectorAll('.dot');

    slides[0].classList.add('active');
    const go = (n) => {
      slides[idx].classList.remove('active');
      idx = (n + total) % total;
      slides[idx].classList.add('active');
      dots.forEach((d, i) => d.classList.toggle('active', i === idx));
    };
    const next = () => go(idx + 1);
    const prev = () => go(idx - 1);
    const start = () => { timer = setInterval(next, 3000); };
    const restart = () => { clearInterval(timer); start(); };

    document.getElementById('slideNext').addEventListener('click', () => { next(); restart(); });
    document.getElementById('slidePrev').addEventListener('click', () => { prev(); restart(); });
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', start);
    start();
  }

  /* --- Маска для даты ДД.ММ.ГГГГ --- */
  const dateField = document.getElementById('dateField');
  if (dateField) {
    dateField.addEventListener('input', (e) => {
      let v = e.target.value.replace(/\D/g, '').slice(0, 8);
      let out = v;
      if (v.length > 4) out = v.slice(0, 2) + '.' + v.slice(2, 4) + '.' + v.slice(4);
      else if (v.length > 2) out = v.slice(0, 2) + '.' + v.slice(2);
      e.target.value = out;
    });
  }

  /* --- Универсальные модальные окна --- */
  const openModal = (m) => { if (m) { m.hidden = false; document.body.style.overflow = 'hidden'; } };
  const closeModal = (m) => { if (m) { m.hidden = true; document.body.style.overflow = ''; } };
  document.querySelectorAll('[data-close]').forEach(el => {
    el.addEventListener('click', () => closeModal(el.closest('.modal')));
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') document.querySelectorAll('.modal:not([hidden])').forEach(closeModal);
  });

  // Модалка отзыва (личный кабинет)
  const reviewModal = document.getElementById('reviewModal');
  document.querySelectorAll('.js-open-review').forEach(btn => btn.addEventListener('click', () => {
    document.getElementById('reviewReqId').value = btn.dataset.id;
    document.getElementById('reviewItemTitle').textContent = btn.dataset.title;
    openModal(reviewModal);
  }));

  // Модалка статуса (админка)
  const statusModal = document.getElementById('statusModal');
  document.querySelectorAll('.js-open-status').forEach(btn => btn.addEventListener('click', () => {
    document.getElementById('statusReqId').value = btn.dataset.id;
    document.getElementById('statusInfo').textContent =
      `Заявка #${btn.dataset.id} · ${btn.dataset.who} · ${btn.dataset.title}`;
    statusModal.querySelectorAll('input[name="status"]').forEach(r => {
      r.checked = (r.value === btn.dataset.status);
    });
    openModal(statusModal);
  }));

  /* --- Тосты: авто-скрытие --- */
  document.querySelectorAll('.toast').forEach((t, i) => {
    setTimeout(() => t.classList.add('show'), 60 * i);
    setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 400); }, 4200 + 60 * i);
  });
})();
