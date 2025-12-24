# Рабочая заготовка формы авторизации

Полная авторизации, реализована на php


для бд

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  nickname VARCHAR(32) NOT NULL,
  email VARCHAR(255) NOT NULL,

  password_hash VARCHAR(255) NOT NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  failed_logins INT UNSIGNED NOT NULL DEFAULT 0,
  last_failed_login TIMESTAMP NULL,

  UNIQUE KEY uniq_users_nickname (nickname),
  UNIQUE KEY uniq_users_email (email)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
