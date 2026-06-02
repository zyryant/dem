<?php
/**
 * Ядро приложения: сессия, подключение к БД (PDO), автоустановка схемы,
 * наполнение каталога и вспомогательные функции.
 * Подключается в начале каждой страницы.
 */

session_start();
mb_internal_encoding('UTF-8');

$CONFIG = require __DIR__ . '/config.php';

/* -------------------------------------------------------------------------
 *  Подключение к базе данных
 * ---------------------------------------------------------------------- */
function pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $cfg = require __DIR__ . '/db_config.php';
    $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        die('<h1 style="font-family:sans-serif">Не удалось подключиться к базе данных</h1>'
            . '<p style="font-family:sans-serif">Проверьте файл <b>db_config.php</b> и убедитесь, что база данных создана в phpMyAdmin.</p>'
            . '<pre style="font-family:monospace;color:#b00">' . htmlspecialchars($e->getMessage()) . '</pre>');
    }
    return $pdo;
}

/* -------------------------------------------------------------------------
 *  Автоматическая установка структуры БД и наполнение каталога
 * ---------------------------------------------------------------------- */
function install_database(array $config): void
{
    $db = pdo();

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        login       VARCHAR(50)  NOT NULL UNIQUE,
        password    VARCHAR(255) NOT NULL,
        fio         VARCHAR(150) NOT NULL,
        phone       VARCHAR(30)  NOT NULL,
        email       VARCHAR(120) NOT NULL,
        birthdate   DATE NULL,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS items (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        category    VARCHAR(60)  NOT NULL,
        title       VARCHAR(150) NOT NULL,
        description TEXT,
        image       VARCHAR(255),
        capacity    INT DEFAULT 0,
        price       INT DEFAULT 0,
        meta        VARCHAR(30) DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS requests (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NOT NULL,
        item_id     INT NOT NULL,
        event_date  DATE NOT NULL,
        payment     VARCHAR(80) NOT NULL,
        status      VARCHAR(60) NOT NULL,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_req_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_req_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS reviews (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        request_id  INT NOT NULL UNIQUE,
        user_id     INT NOT NULL,
        rating      TINYINT NOT NULL DEFAULT 5,
        comment     TEXT NOT NULL,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_rev_req  FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
        CONSTRAINT fk_rev_user FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Синхронизируем каталог с config.php
    $dbIds = $db->query("SELECT id FROM items ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $configItems = $config['items'];

    if (count($dbIds) === 0) {
        // Первый запуск — вставляем все
        $stmt = $db->prepare("INSERT INTO items (category, title, description, image, capacity, price, meta)
                              VALUES (?,?,?,?,?,?,?)");
        foreach ($configItems as $it) {
            $stmt->execute($it);
        }
    } elseif (count($dbIds) === count($configItems)) {
        // Обновляем название, описание, категорию, цену по порядку
        $stmt = $db->prepare("UPDATE items SET category=?, title=?, description=?, image=?, capacity=?, price=?, meta=? WHERE id=?");
        foreach ($configItems as $i => $it) {
            $stmt->execute(array_merge($it, [$dbIds[$i]]));
        }
    }
}

/* -------------------------------------------------------------------------
 *  Вспомогательные функции
 * ---------------------------------------------------------------------- */
function e(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

function redirect(string $url): never { header("Location: $url"); exit; }

function flash(string $type, string $msg): void { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; }

function get_flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) return null;
    static $u = null;
    if ($u === null) {
        $stmt = pdo()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $u = $stmt->fetch() ?: null;
    }
    return $u;
}

function is_admin(): bool { return !empty($_SESSION['is_admin']); }

function require_user(): void { if (!current_user()) redirect('login.php'); }

function require_admin(): void { if (!is_admin()) redirect('login.php'); }

function money(int $v): string { return number_format($v, 0, '.', ' ') . ' ₽'; }

/** Бейдж статуса -> css-модификатор по индексу статуса */
function status_class(string $status, array $statuses): string
{
    $i = array_search($status, $statuses, true);
    return 'st-' . ($i === false ? 0 : $i);
}

// Запускаем автоустановку при каждом обращении (CREATE IF NOT EXISTS — безопасно)
install_database($CONFIG);
