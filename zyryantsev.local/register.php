<?php
require __DIR__ . '/bootstrap.php';

if (current_user()) redirect('cabinet.php');

$errors = [];
$old = ['login' => '', 'fio' => '', 'phone' => '', 'email' => '', 'birthdate' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) $old[$k] = trim($_POST[$k] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Логин: латиница + цифры, минимум 6 символов
    if (!preg_match('/^[A-Za-z0-9]{6,}$/', $old['login'])) {
        $errors['login'] = 'Логин: только латинские буквы и цифры, минимум 6 символов.';
    }
    if (mb_strlen($password) < 8) {
        $errors['password'] = 'Пароль должен содержать минимум 8 символов.';
    }
    if ($password !== $password2) {
        $errors['password2'] = 'Пароли не совпадают.';
    }
    if (mb_strlen($old['fio']) < 3) {
        $errors['fio'] = 'Укажите ФИО полностью.';
    }
    if (!preg_match('/^[\d\s\-\+\(\)]{6,}$/', $old['phone'])) {
        $errors['phone'] = 'Укажите корректный номер телефона.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Укажите корректный e-mail.';
    }
    if (!empty($CONFIG['has_birthdate']) && $old['birthdate'] === '') {
        $errors['birthdate'] = 'Укажите дату рождения.';
    }

    // Уникальность логина
    if (empty($errors['login'])) {
        $st = pdo()->prepare("SELECT 1 FROM users WHERE login = ?");
        $st->execute([$old['login']]);
        if ($st->fetch()) $errors['login'] = 'Такой логин уже занят, выберите другой.';
    }

    if (!$errors) {
        $st = pdo()->prepare("INSERT INTO users (login, password, fio, phone, email, birthdate)
                              VALUES (?,?,?,?,?,?)");
        $st->execute([
            $old['login'],
            password_hash($password, PASSWORD_DEFAULT),
            $old['fio'], $old['phone'], $old['email'],
            !empty($CONFIG['has_birthdate']) ? $old['birthdate'] : null,
        ]);
        $_SESSION['user_id'] = (int) pdo()->lastInsertId();
        flash('success', 'Регистрация прошла успешно. Добро пожаловать!');
        redirect('cabinet.php');
    }
}

$PAGE_TITLE = 'Регистрация';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
  <div class="container">
    <div class="auth-card auth-card--solo reveal">
      <h1>Регистрация</h1>
      <form method="post" novalidate class="form">
        <div class="field <?= isset($errors['login']) ? 'has-error' : '' ?>">
          <label>Логин</label>
          <input type="text" name="login" value="<?= e($old['login']) ?>" placeholder="Dmitry2000" autocomplete="username">
          <small class="hint"><?= $errors['login'] ?? 'Латиница и цифры, минимум 6 символов.' ?></small>
        </div>

        <div class="field-row">
          <div class="field <?= isset($errors['password']) ? 'has-error' : '' ?>">
            <label>Пароль</label>
            <input type="password" name="password" placeholder="минимум 8 символов" autocomplete="new-password">
            <small class="hint"><?= $errors['password'] ?? 'Минимум 8 символов.' ?></small>
          </div>
          <div class="field <?= isset($errors['password2']) ? 'has-error' : '' ?>">
            <label>Повтор пароля</label>
            <input type="password" name="password2" placeholder="повторите пароль" autocomplete="new-password">
            <small class="hint"><?= $errors['password2'] ?? 'Повторите пароль.' ?></small>
          </div>
        </div>

        <div class="field <?= isset($errors['fio']) ? 'has-error' : '' ?>">
          <label>ФИО</label>
          <input type="text" name="fio" value="<?= e($old['fio']) ?>" placeholder="Дмитрий Дмитриев Дмитриевич">
          <small class="hint"><?= $errors['fio'] ?? 'Фамилия, имя и отчество.' ?></small>
        </div>

        <?php if (!empty($CONFIG['has_birthdate'])): ?>
        <div class="field <?= isset($errors['birthdate']) ? 'has-error' : '' ?>">
          <label>Дата рождения</label>
          <input type="date" name="birthdate" value="<?= e($old['birthdate']) ?>">
          <small class="hint"><?= $errors['birthdate'] ?? 'Укажите дату рождения.' ?></small>
        </div>
        <?php endif; ?>

        <div class="field-row">
          <div class="field <?= isset($errors['phone']) ? 'has-error' : '' ?>">
            <label>Телефон</label>
            <input type="tel" name="phone" value="<?= e($old['phone']) ?>" placeholder="+7 (___) ___-__-__">
            <small class="hint"><?= $errors['phone'] ?? 'Контактный номер.' ?></small>
          </div>
          <div class="field <?= isset($errors['email']) ? 'has-error' : '' ?>">
            <label>E-mail</label>
            <input type="email" name="email" value="<?= e($old['email']) ?>" placeholder="example@mail.ru">
            <small class="hint"><?= $errors['email'] ?? 'Электронная почта.' ?></small>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">Зарегистрироваться</button>
      </form>
      <p class="auth-switch"><a href="login.php">Уже есть аккаунт? Войти</a></p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
