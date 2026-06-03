<?php
require __DIR__ . '/bootstrap.php';
require_user();
$user = current_user();

// Добавление отзыва (только если статус заявки изменён администратором)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'review') {
    $reqId  = (int) ($_POST['request_id'] ?? 0);
    $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');

    $st = pdo()->prepare("SELECT * FROM requests WHERE id = ? AND user_id = ?");
    $st->execute([$reqId, $user['id']]);
    $req = $st->fetch();

    if (!$req) {
        flash('error', 'Заявка не найдена.');
    } elseif ($req['status'] === $CONFIG['statuses'][0]) {
        flash('error', 'Оставить отзыв можно только после изменения статуса заявки администратором.');
    } elseif ($comment === '') {
        flash('error', 'Напишите текст отзыва.');
    } else {
        $ins = pdo()->prepare("INSERT INTO reviews (request_id, user_id, rating, comment)
                               VALUES (?,?,?,?)
                               ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)");
        $ins->execute([$reqId, $user['id'], $rating, $comment]);
        flash('success', 'Спасибо! Ваш отзыв сохранён.');
    }
    redirect('cabinet.php');
}

// История заявок пользователя + есть ли отзыв
$st = pdo()->prepare(
    "SELECT q.*, i.title, i.category, i.image,
            r.id AS review_id, r.rating, r.comment
       FROM requests q
       JOIN items i ON i.id = q.item_id
  LEFT JOIN reviews r ON r.request_id = q.id
      WHERE q.user_id = ?
   ORDER BY q.id DESC"
);
$st->execute([$user['id']]);
$requests = $st->fetchAll();

$statuses = $CONFIG['statuses'];

$PAGE_TITLE = 'Личный кабинет';
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">

    <!-- Dashboard: карточка пользователя + слайдер -->
    <div class="cabinet-dashboard reveal">

      <!-- Левая колонка: пользователь -->
      <div class="cabinet-user-card">
        <h2><?= e($user['fio']) ?></h2>
        <p class="muted-text"><?= e($user['email']) ?></p>
        <p class="muted-text"><?= e($user['phone']) ?></p>
        <div class="cabinet-user-stats">
          <div class="cabinet-user-stat">
            <span class="cabinet-user-stat-num"><?= count($requests) ?></span>
            <span class="cabinet-user-stat-label">Заявок</span>
          </div>
          <div class="cabinet-user-stat">
            <span class="cabinet-user-stat-num"><?= count(array_filter($requests, fn($r) => $r['status'] === $statuses[2])) ?></span>
            <span class="cabinet-user-stat-label">Завершено</span>
          </div>
        </div>
        <a href="booking.php" class="btn btn-primary btn-block"><?= e($CONFIG['cta']) ?></a>
      </div>

      <!-- Правая колонка: слайдер -->
      <div class="slider reveal" id="slider">
        <div class="slides">
          <div class="slide"><img src="static/img/slider/s1.jpg" alt="Слайд 1"><div class="slide-cap">Современные площадки</div></div>
          <div class="slide"><img src="static/img/slider/s2.jpg" alt="Слайд 2"><div class="slide-cap">Удобное оборудование</div></div>
          <div class="slide"><img src="static/img/slider/s3.jpg" alt="Слайд 3"><div class="slide-cap">Атмосфера для идей</div></div>
          <div class="slide"><img src="static/img/slider/s4.jpg" alt="Слайд 4"><div class="slide-cap">Всё для вашего события</div></div>
        </div>
        <button class="slider-btn prev" id="slidePrev" aria-label="Назад">‹</button>
        <button class="slider-btn next" id="slideNext" aria-label="Вперёд">›</button>
        <div class="slider-dots" id="slideDots"></div>
      </div>

    </div>

    <h2 class="block-title">Мои заявки</h2>

    <?php if (!$requests): ?>
      <div class="empty reveal">
        <div class="empty-icon"></div>
        <h3>Заявок пока нет</h3>
        <p>Запишитесь на первый курс прямо сейчас</p>
        <a href="booking.php" class="btn btn-primary"><?= e($CONFIG['cta']) ?></a>
      </div>
    <?php else: ?>
      <div class="req-list">
        <?php foreach ($requests as $q): ?>
          <article class="req-card reveal">
            <div class="req-img"><img src="static/img/<?= e($q['image']) ?>" alt="" loading="lazy"></div>
            <div class="req-info">
              <div class="req-top">
                <h3><?= e($q['title']) ?></h3>
                <span class="badge <?= status_class($q['status'], $statuses) ?>"><?= e($q['status']) ?></span>
              </div>
              <div class="req-meta">
                <div class="req-meta-item">
                  <span class="req-meta-label">Категория</span>
                  <span class="req-meta-val"><?= e($q['category']) ?></span>
                </div>
                <div class="req-meta-item">
                  <span class="req-meta-label">Дата начала</span>
                  <span class="req-meta-val"><?= date('d.m.Y', strtotime($q['event_date'])) ?></span>
                </div>
                <div class="req-meta-item">
                  <span class="req-meta-label">Оплата</span>
                  <span class="req-meta-val"><?= e($q['payment']) ?></span>
                </div>
                <div class="req-meta-item">
                  <span class="req-meta-label">Заявка</span>
                  <span class="req-meta-val">№<?= (int)$q['id'] ?></span>
                </div>
              </div>

              <?php if ($q['status'] === $statuses[0]): ?>
                <p class="review-locked">Отзыв станет доступен после изменения статуса администратором.</p>
              <?php elseif ($q['review_id']): ?>
                <div class="review-done">
                  <div class="stars"><?= str_repeat('★', (int)$q['rating']) . str_repeat('☆', 5 - (int)$q['rating']) ?></div>
                  <p>«<?= e($q['comment']) ?>»</p>
                </div>
              <?php else: ?>
                <button class="btn btn-outline btn-sm js-open-review"
                        data-id="<?= (int)$q['id'] ?>" data-title="<?= e($q['title']) ?>">Оставить отзыв</button>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Модальное окно отзыва -->
<div class="modal" id="reviewModal" hidden>
  <div class="modal-backdrop" data-close></div>
  <div class="modal-card reveal-now">
    <button class="modal-x" data-close aria-label="Закрыть">×</button>
    <h3>Отзыв о заявке</h3>
    <p class="muted-text" id="reviewItemTitle"></p>
    <form method="post" class="form">
      <input type="hidden" name="action" value="review">
      <input type="hidden" name="request_id" id="reviewReqId">
      <div class="field">
        <label>Оценка</label>
        <div class="rating-input" id="ratingInput">
          <?php for ($i = 5; $i >= 1; $i--): ?>
            <input type="radio" name="rating" id="r<?= $i ?>" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
            <label for="r<?= $i ?>">★</label>
          <?php endfor; ?>
        </div>
      </div>
      <div class="field">
        <label>Комментарий</label>
        <textarea name="comment" rows="4" placeholder="Поделитесь впечатлениями о площадке и сервисе…" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Отправить отзыв</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
