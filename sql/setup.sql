CREATE DATABASE IF NOT EXISTS cronohoras CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cronohoras;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pin VARCHAR(4) NOT NULL UNIQUE,
  role ENUM('employee', 'admin') NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255),
  locale ENUM('es','en') NOT NULL DEFAULT 'es',
  theme ENUM('light','dark') NOT NULL DEFAULT 'light',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATE NOT NULL,
  entry DATETIME NOT NULL,
  exit DATETIME DEFAULT NULL,
  total_hours DECIMAL(5,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO users (pin, role, first_name, last_name, email, locale, theme)
VALUES
  ('1111', 'employee', 'Ana', 'González', 'ana.gonzalez@empresa.com', 'es', 'light'),
  ('2222', 'employee', 'Luis', 'Martínez', 'luis.martinez@empresa.com', 'es', 'light'),
  ('9999', 'admin', 'Marta', 'Ríos', 'marta.rios@empresa.com', 'es', 'dark');
