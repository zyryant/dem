<?php
require __DIR__ . '/bootstrap.php';

if (current_user()) redirect('cabinet.php');
if (is_admin())     redirect('admin.php');

$error = '';
$loginVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginVal = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    // Администратор (логин/пароль из задания)
    if ($loginVal === $CONFIG['admin_login'] && $password === $CONFIG['admin_pass']) {
        $_SESSION['is_admin'] = true;
        flash('success', 'Вы вошли как администратор.');
        redirect('admin.php');
    }

    // Обычный пользователь
    $st = pdo()->prepare("SELECT * FROM users WHERE login = ?");
    $st->execute([$loginVal]);
    $user = $st->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        flash('success', 'С возвращением, ' . explode(' ', $user['fio'])[0] . '!');
        redirect('cabinet.php');
    }

    $error = 'Неверный логин или пароль. Проверьте данные и попробуйте снова.';
}

$PAGE_TITLE = 'Вход';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
  <div class="container">
    <div class="auth-card auth-card--solo reveal">
      <h1>Вход</h1>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" novalidate class="form">
        <div class="field">
          <label>Логин</label>
          <input type="text" name="login" value="<?= e($loginVal) ?>" placeholder="Ваш логин" autocomplete="username" autofocus>
        </div>
        <div class="field">
          <label>Пароль</label>
          <input type="password" name="password" placeholder="Ваш пароль" autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Войти</button>
      </form>

      <p class="auth-switch"><a href="register.php">Ещё не зарегистрированы? Регистрация</a></p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
