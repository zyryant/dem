/* ==========================================================================
   Вариант V6 «Glassmorphism» — лёгкий доп. интерактив.
   Не дублирует static/app.js (меню, reveal, счётчики, фильтр, слайдер,
   модалки, тосты уже там). Только эффекты стекла:
     1) Spotlight-отблеск на карточках при наведении (CSS-переменные --mx/--my).
     2) Мягкая параллакс-реакция фоновых орбов на курсор и скролл.
   IIFE, group-agnostic, guard везде, уважение prefers-reduced-motion.
   ========================================================================== */
(function () {
  'use strict';

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- 1. Spotlight на карточках/панелях --- */
  // Работает на любой группе: ищем typical glass-панели.
  var spotTargets = document.querySelectorAll('.card, .review, .step, .stat-card');
  if (spotTargets.length) {
    spotTargets.forEach(function (el) {
      el.addEventListener('pointermove', function (ev) {
        var r = el.getBoundingClientRect();
        if (!r.width || !r.height) return;
        var x = ((ev.clientX - r.left) / r.width) * 100;
        var y = ((ev.clientY - r.top) / r.height) * 100;
        el.style.setProperty('--mx', x.toFixed(2) + '%');
        el.style.setProperty('--my', y.toFixed(2) + '%');
      }, { passive: true });
      el.addEventListener('pointerleave', function () {
        el.style.removeProperty('--mx');
        el.style.removeProperty('--my');
      });
    });
  }

  if (reduce) return; // ниже — только движение, его при reduce не запускаем

  /* --- 2. Параллакс фоновых орбов на курсор + скролл --- */
  var heroBg = document.querySelector('.hero-bg');
  if (!heroBg && !document.body) return;

  var px = 0, py = 0, sy = 0;          // целевые смещения
  var cx = 0, cy = 0, cs = 0;          // текущие (сглаженные)
  var ticking = false;

  function apply() {
    // Орбы hero
    if (heroBg) {
      heroBg.style.setProperty('--orb-x', cx.toFixed(1) + 'px');
      heroBg.style.setProperty('--orb-y', (cy + cs).toFixed(1) + 'px');
      heroBg.style.transform = 'translate3d(' + cx.toFixed(1) + 'px,' + (cy + cs * 0.4).toFixed(1) + 'px,0)';
    }
    // Глобальные фоновые пятна через body — двигаем псевдоэлементы переменной
    document.body.style.setProperty('--bgshift', (cs * 0.15).toFixed(1) + 'px');
    ticking = false;
  }

  function loop() {
    cx += (px - cx) * 0.08;
    cy += (py - cy) * 0.08;
    cs += (sy - cs) * 0.08;
    apply();
    if (Math.abs(px - cx) > 0.2 || Math.abs(py - cy) > 0.2 || Math.abs(sy - cs) > 0.2) {
      requestAnimationFrame(loop);
    } else {
      running = false;
    }
  }

  var running = false;
  function kick() {
    if (!running) { running = true; requestAnimationFrame(loop); }
  }

  window.addEventListener('pointermove', function (ev) {
    var w = window.innerWidth || 1, h = window.innerHeight || 1;
    px = ((ev.clientX / w) - 0.5) * 28;   // максимум ±14px
    py = ((ev.clientY / h) - 0.5) * 28;
    kick();
  }, { passive: true });

  window.addEventListener('scroll', function () {
    sy = (window.scrollY || 0) * 0.06;     // лёгкий вертикальный дрейф
    kick();
  }, { passive: true });
})();
