-- ============================================================
--  Схема базы данных портала «Учусь.РФ»
--  СУБД: MySQL 8.4 / InnoDB / utf8mb4
--
--  ПРИМЕЧАНИЕ: приложение создаёт эти таблицы автоматически
--  при первом запуске (см. bootstrap.php → install_database()).
--  Этот файл приведён как справочная схема (Модуль 1, ER-диаграмма).
-- ============================================================

CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    login       VARCHAR(50)  NOT NULL UNIQUE,         -- латиница+цифры, >= 6
    password    VARCHAR(255) NOT NULL,                -- password_hash()
    fio         VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  NOT NULL,
    email       VARCHAR(120) NOT NULL,
    birthdate   DATE NULL,                            -- используется в варианте «Пассажирам.РФ»
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category    VARCHAR(60)  NOT NULL,                -- Повышение квалификации / Переподготовка / Охрана труда
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    image       VARCHAR(255),
    capacity    INT DEFAULT 0,
    price       INT DEFAULT 0,
    meta        VARCHAR(30) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE requests (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    item_id     INT NOT NULL,
    event_date  DATE NOT NULL,
    payment     VARCHAR(80) NOT NULL,                 -- способ оплаты
    status      VARCHAR(60) NOT NULL,                 -- Новая / Идёт обучение / Обучение завершено
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    request_id  INT NOT NULL UNIQUE,                  -- один отзыв на заявку
    user_id     INT NOT NULL,
    rating      TINYINT NOT NULL DEFAULT 5,
    comment     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Связи:
--   users (1) ──< (∞) requests >── (1) items
--   requests (1) ──── (1) reviews
--   users (1) ──< (∞) reviews
