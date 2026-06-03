<?php global $CONFIG; ?>
</main>

<footer class="site-footer" id="contacts">

  <!-- Верхняя полоса: бренд по центру -->
  <div class="footer-brand-bar">
    <div class="container footer-brand-inner">
      <div class="logo logo--footer">
        <span class="logo-icon"><?= $CONFIG['brand_icon'] ?></span>
        <span class="logo-text"><?= e($CONFIG['brand']) ?></span>
      </div>
      <p class="footer-tagline"><?= e($CONFIG['tagline']) ?></p>
      <nav class="footer-nav-row">
        <a href="index.php#catalog">Все курсы</a>
        <a href="index.php#benefits">Наши преимущества</a>
        <a href="index.php#reviews">Отзывы</a>
        <a href="index.php#contacts">Контакты</a>
      </nav>
    </div>
  </div>

  <!-- Нижняя полоса: 3 блока в ряд -->
  <div class="footer-info-bar">
    <div class="container footer-info-row">
      <div class="footer-info-block">
        <span class="footer-info-label">Адрес</span>
        <span class="footer-info-value"><?= e($CONFIG['address']) ?></span>
      </div>
      <div class="footer-info-sep"></div>
      <div class="footer-info-block">
        <span class="footer-info-label">Телефон горячей линии</span>
        <a class="footer-info-value" href="tel:<?= preg_replace('/[^+\d]/', '', $CONFIG['phone']) ?>"><?= e($CONFIG['phone']) ?></a>
        <span class="footer-info-value" style="font-size:12px;opacity:.7">Если возникли вопросы или пожелания, позвоните нам. Ответим оперативно 
и подробно.</span>
      </div>
      <div class="footer-info-sep"></div>
      <div class="footer-info-block">
        <span class="footer-info-label">Варианты оплаты</span>
        <?php foreach ($CONFIG['payments'] as $p): ?>
          <span class="footer-info-value"><?= e($p) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="footer-bottom container">
    © <?= date('Y') ?> <?= e($CONFIG['brand']) ?>. Демонстрационный экзамен.
  </div>
</footer>

<button class="to-top" id="toTop" aria-label="Наверх">↑</button>

<script src="static/app.js"></script>
<script src="static/variant.js"></script>
</body>
</html>
