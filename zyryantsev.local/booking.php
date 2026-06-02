<?php
require __DIR__ . '/bootstrap.php';
require_user();
$user = current_user();

$items = pdo()->query("SELECT * FROM items ORDER BY category, id")->fetchAll();
// группировка по категориям для <optgroup>
$grouped = [];
foreach ($items as $it)
  $grouped[$it['category']][] = $it;

$preItem = (int) ($_GET['item'] ?? 0);
$errors = [];
$val = ['item_id' => $preItem, 'event_date' => '', 'payment' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $val['item_id'] = (int) ($_POST['item_id'] ?? 0);
  $val['event_date'] = trim($_POST['event_date'] ?? '');
  $val['payment'] = trim($_POST['payment'] ?? '');

  $okItem = $val['item_id'] && in_array($val['item_id'], array_column($items, 'id'));
  if (!$okItem)
    $errors['item_id'] = 'Выберите ' . $CONFIG['item_word'] . ' из списка.';

  // Дата в формате ДД.ММ.ГГГГ -> Y-m-d
  $sqlDate = null;
  if (
    preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $val['event_date'], $m)
    && checkdate((int) $m[2], (int) $m[1], (int) $m[3])
  ) {
    $sqlDate = "{$m[3]}-{$m[2]}-{$m[1]}";
    if ($sqlDate < date('Y-m-d'))
      $errors['event_date'] = 'Дата не может быть в прошлом.';
  } else {
    $errors['event_date'] = 'Введите дату в формате ДД.ММ.ГГГГ.';
  }

  if (!in_array($val['payment'], $CONFIG['payments'], true)) {
    $errors['payment'] = 'Выберите способ оплаты.';
  }

  if (!$errors) {
    $st = pdo()->prepare("INSERT INTO requests (user_id, item_id, event_date, payment, status)
                              VALUES (?,?,?,?,?)");
    $st->execute([$user['id'], $val['item_id'], $sqlDate, $val['payment'], $CONFIG['statuses'][0]]);
    flash('success', 'Заявка создана и отправлена администратору на согласование.');
    redirect('cabinet.php');
  }
}

$PAGE_TITLE = 'Оформление заявки';
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="auth-card auth-card--solo reveal">
      <h1>Оформление заявки</h1>

    <form method="post" novalidate class="form" style="margin-top:20px">
      <div class="field <?= isset($errors['item_id']) ? 'has-error' : '' ?>">
        <label><?= e($CONFIG['item_label']) ?></label>
        <select name="item_id" class="select">
          <option value="">— выберите из списка —</option>
          <?php foreach ($grouped as $cat => $list): ?>
            <optgroup label="<?= e($cat) ?>">
              <?php foreach ($list as $it): ?>
                <option value="<?= (int) $it['id'] ?>" <?= $val['item_id'] == $it['id'] ? 'selected' : '' ?>>
                  <?= e($it['title']) ?> — <?= money((int) $it['price']) ?>     <?= e($CONFIG['price_unit']) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
        <small class="hint"><?= $errors['item_id'] ?? 'Доступно ' . count($items) . ' вариантов.' ?></small>
      </div>

      <div class="field-row">
        <div class="field <?= isset($errors['event_date']) ? 'has-error' : '' ?>">
          <label><?= e($CONFIG['event_label']) ?></label>
          <input type="text" name="event_date" value="<?= e($val['event_date']) ?>" placeholder="ДД.ММ.ГГГГ"
            inputmode="numeric" id="dateField" maxlength="10">
          <small class="hint"><?= $errors['event_date'] ?? 'Формат: ДД.ММ.ГГГГ' ?></small>
        </div>
        <div class="field <?= isset($errors['payment']) ? 'has-error' : '' ?>">
          <label>Способ оплаты</label>
          <select name="payment" class="select">
            <option value="">— выберите способ —</option>
            <?php foreach ($CONFIG['payments'] as $p): ?>
              <option value="<?= e($p) ?>" <?= $val['payment'] === $p ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="hint"><?= $errors['payment'] ?? 'Как вам удобнее оплатить.' ?></small>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg">Отправить заявку</button>
    </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>