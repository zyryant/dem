<?php
global $CONFIG;
$t = $CONFIG['theme'];
$u = current_user();
$page = $PAGE_TITLE ?? $CONFIG['brand'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<meta name="theme-color" content="<?= e($t['primary']) ?>">
<title><?= e($page) ?> — <?= e($CONFIG['brand']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="<?= e($t['font_url']) ?>" rel="stylesheet">
<style>
:root{
  --primary: <?= $t['primary'] ?>;
  --primary-dark: <?= $t['primary_dark'] ?>;
  --dark: <?= $t['dark'] ?>;
  --muted: <?= $t['muted'] ?>;
  --light: <?= $t['light'] ?>;
  --white: <?= $t['white'] ?>;
  --bg: <?= $t['bg'] ?>;
  --font: <?= $t['font'] ?>;
  --gradient: <?= $t['gradient'] ?>;
}
</style>
<link rel="stylesheet" href="static/style.css">
</head>
<body>
<header class="site-header" id="siteHeader">
  <div class="container header-inner header-inner--tri">

    <!-- Логотип по центру -->
    <a href="index.php" class="logo logo--center">
      <span class="logo-icon"><?= $CONFIG['brand_icon'] ?></span>
      <span class="logo-text logo-text--plain"><?= e($CONFIG['brand']) ?></span>
    </a>

    <!-- Авторизация справа (десктоп) -->
    <div class="nav-auth nav-auth--desktop">
      <?php if (is_admin()): ?>
        <a href="admin.php" class="nav-cta">Панель администратора</a>
        <a href="logout.php" class="nav-ghost">Выйти</a>
      <?php elseif ($u): ?>
        <a href="cabinet.php">Личный кабинет</a>
        <a href="booking.php" class="nav-cta"><?= e($CONFIG['cta']) ?></a>
        <a href="logout.php" class="nav-ghost">Выйти</a>
      <?php else: ?>
        <a href="login.php">Войти</a>
        <a href="register.php" class="nav-cta">Регистрация</a>
      <?php endif; ?>
    </div>

    <!-- Десктопная навигация -->
    <nav class="main-nav main-nav--left main-nav--desktop">
      <a href="index.php#catalog">Все курсы</a>
      <a href="index.php#benefits">Наши преимущества</a>
      <a href="index.php#contacts">Контакты</a>
    </nav>

    <button class="burger" id="burger" aria-label="Меню"><span></span><span></span><span></span></button>
  </div>
</header>

<!-- Мобильный drawer (вне шапки — собственный stacking context) -->
<nav class="main-nav main-nav--drawer" id="mainNav">
  <button class="drawer-close" id="drawerClose" aria-label="Закрыть">×</button>
  <a href="index.php#catalog">Все курсы</a>
  <a href="index.php#benefits">Наши преимущества</a>
  <a href="index.php#contacts">Контакты</a>
  <div class="nav-mobile-auth">
    <?php if (is_admin()): ?>
      <a href="admin.php" class="nav-cta">Панель администратора</a>
      <a href="logout.php">Выйти</a>
    <?php elseif ($u): ?>
      <a href="cabinet.php">Личный кабинет</a>
      <a href="booking.php" class="nav-cta"><?= e($CONFIG['cta']) ?></a>
      <a href="logout.php">Выйти</a>
    <?php else: ?>
      <a href="login.php">Войти</a>
      <a href="register.php" class="nav-cta">Регистрация</a>
    <?php endif; ?>
  </div>
</nav>
<div class="nav-overlay" id="navOverlay"></div>

<div class="toast-stack" id="toastStack">
<?php foreach (get_flashes() as $f): ?>
  <div class="toast toast-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
</div>

<main class="page">
