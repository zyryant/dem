<?php
require __DIR__ . '/bootstrap.php';

$items = pdo()->query("SELECT * FROM items ORDER BY category, id")->fetchAll();

// Несколько последних отзывов для витрины
$reviews = pdo()->query(
    "SELECT r.rating, r.comment, u.fio, i.title
       FROM reviews r
       JOIN users u   ON u.id = r.user_id
       JOIN requests q ON q.id = r.request_id
       JOIN items i   ON i.id = q.item_id
      ORDER BY r.id DESC LIMIT 6"
)->fetchAll();

$totalRequests = (int) pdo()->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$totalUsers    = (int) pdo()->query("SELECT COUNT(*) FROM users")->fetchColumn();

$PAGE_TITLE = 'Главная';
require __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO: центрированный ===== -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="container">

    <div class="hero-center reveal">
      <h1><?= e($CONFIG['hero_title']) ?></h1>
      <span class="hero-badge"><?= e($CONFIG['tagline']) ?></span>
      <p><?= e($CONFIG['hero_text']) ?></p>
      <div class="hero-actions">
        <a href="<?= current_user() ? 'booking.php' : 'register.php' ?>" class="btn btn-primary btn-lg"><?= e($CONFIG['cta']) ?></a>
        <a href="#catalog" class="btn btn-ghost btn-lg">Все курсы</a>
      </div>
    </div>

    <!-- Полоса статистики -->
    <div class="hero-stats-strip reveal">
      <div class="hero-stat">
        <b data-count="<?= max(6, count($items)) ?>">0</b>
        <span><?= e($CONFIG['stat_items_label']) ?></span>
      </div>
      <div class="hero-stat">
        <b data-count="<?= max(560, $totalUsers) ?>">0</b>
        <span><?= e($CONFIG['stat_users_label']) ?></span>
      </div>
      <div class="hero-stat">
        <b data-count="<?= max(149, $totalRequests) ?>">0</b>
        <span><?= e($CONFIG['stat_requests_label']) ?></span>
      </div>
    </div>

  </div>
</section>

<!-- ===== ПРЕИМУЩЕСТВА ===== -->
<section class="section section-benefits" id="benefits">
  <div class="container">
    <div class="section-head">
      <h2>Почему выбирают нас</h2>
    </div>
    <div class="benefits-grid">
      <div class="benefit-card">
        <div class="benefit-num">01</div>
        <h3>Официальный документ</h3>
        <p>Удостоверение о повышении квалификации или диплом о переподготовке государственного образца, внесённый в федеральный реестр.</p>
      </div>
      <div class="benefit-card">
        <div class="benefit-num">02</div>
        <h3>Дистанционный формат</h3>
        <p>Учитесь из любой точки страны в удобное время — никаких командировок и отрыва от работы.</p>
      </div>
      <div class="benefit-card">
        <div class="benefit-num">03</div>
        <h3>Куратор на весь курс</h3>
        <p>Персональный куратор отвечает на вопросы, следит за прогрессом и помогает успешно завершить обучение.</p>
      </div>
      <div class="benefit-card">
        <div class="benefit-num">04</div>
        <h3>Гибкий график оплаты</h3>
        <p>QR-код, карта МИР или постоплата в офисе — выбирайте удобный способ без скрытых комиссий.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== КАТАЛОГ: боковая панель + список (было: chips + card grid) ===== -->
<section class="section" id="catalog">
  <div class="container">
    <div class="section-head reveal">
      <h2><?= e($CONFIG['catalog_title']) ?></h2>
    </div>

    <!-- Двухколонная раскладка: sidebar + список -->
    <div class="catalog-layout">

      <!-- Боковая панель с вертикальными фильтрами (было: горизонтальные chips над сеткой) -->
      <aside class="cat-sidebar" id="catFilter">
        <span class="sidebar-title">Категории</span>
        <div class="cat-chips-row">
          <button class="chip chip--vert active" data-cat="all">Все</button>
          <?php foreach ($CONFIG['categories'] as $cat): ?>
            <button class="chip chip--vert" data-cat="<?= e($cat) ?>"><?= e($cat) ?></button>
          <?php endforeach; ?>
        </div>
      </aside>

      <!-- Горизонтальный список карточек (было: 3-колонная card grid) -->
      <div class="cards-list" id="catalogGrid">
        <?php foreach ($items as $it): ?>
          <article class="card card--list reveal" data-cat="<?= e($it['category']) ?>">
            <div class="card-media card-media--sm">
              <img src="static/img/<?= e($it['image']) ?>" alt="<?= e($it['title']) ?>" loading="lazy">
            </div>
            <div class="card-body">
              <div class="card-title-row">
                <h3><?= e($it['title']) ?></h3>
                <span class="card-tag card-tag--inline"><?= e($it['category']) ?></span>
              </div>
              <p><?= e($it['description']) ?></p>
              <div class="card-list-footer">
                <span class="card-price"><?= money((int)$it['price']) ?> <small><?= e($CONFIG['price_unit']) ?></small></span>
                <a href="<?= current_user() ? 'booking.php?item=' . (int)$it['id'] : 'register.php' ?>" class="btn btn-outline btn-list-pick">Выбрать</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>


<!-- ===== ОТЗЫВЫ: горизонтальная прокрутка (было: grid) ===== -->
<?php if ($reviews): ?>
<section class="section" id="reviews">
  <div class="container">
    <div class="section-head reveal">
      <h2>Отзывы выпускников</h2>
    </div>
    <div class="reviews-wrap"><div class="reviews-track">
      <?php foreach ($reviews as $rv): ?>
        <figure class="review">
          <div class="review-top">
            <div class="review-quote">"</div>
            <div class="stars"><?= str_repeat('★', (int)$rv['rating']) . str_repeat('☆', 5 - (int)$rv['rating']) ?></div>
          </div>
          <div class="review-author">
            <div>
              <b><?= e($rv['fio']) ?></b>
              <span><?= e($rv['title']) ?></span>
            </div>
          </div>
          <blockquote><?= e($rv['comment']) ?></blockquote>
        </figure>
      <?php endforeach; ?>
    </div></div>
  </div>
</section>
<?php endif; ?>

<!-- ===== CTA: центрированный макет ===== -->
<section class="cta-band reveal">
  <div class="container cta-center">
    <h2><?= e($CONFIG['cta_title']) ?></h2>
    <p><?= e($CONFIG['cta_text']) ?></p>
    <div class="cta-cards">
      <div class="cta-card">Документ государственного образца</div>
      <div class="cta-card">Куратор на весь период обучения</div>
      <div class="cta-card">Обучение в удобное время онлайн</div>
    </div>
    <a href="<?= current_user() ? 'booking.php' : 'register.php' ?>" class="btn btn-light btn-lg"><?= e($CONFIG['cta']) ?></a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
