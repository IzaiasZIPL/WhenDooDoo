-- init.sql for MySQL / MariaDB
-- You can paste this directly into phpMyAdmin's SQL tab

CREATE DATABASE IF NOT EXISTS todo_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE todo_app;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS todos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(300) NOT NULL,
  notes TEXT DEFAULT NULL,
  is_done TINYINT(1) NOT NULL DEFAULT 0,
  due_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_todos_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_todos_user (user_id),
  INDEX idx_todos_user_done (user_id, is_done),
  INDEX idx_todos_user_due (user_id, due_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- sample data to test
INSERT INTO users (name, email) VALUES ('Carlos', 'carlos@example.com'), ('Ana', 'ana@example.com');

INSERT INTO todos (user_id, title, notes, is_done, due_at)
VALUES
  (1, 'Buy groceries', 'Milk, eggs, bread', 0, NULL),
  (1, 'Read book', 'Finish chapter 4', 0, '2025-11-15 18:00:00'),
  (2, 'Pay bills', NULL, 1, NULL);
