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
USE cronohoras;

-- Tabla de empresas
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sector VARCHAR(100),
    color VARCHAR(7) DEFAULT '#2563eb',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de departamentos
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Agregar columnas a users
ALTER TABLE users ADD COLUMN company_id INT DEFAULT NULL;
ALTER TABLE users ADD COLUMN department_id INT DEFAULT NULL;

-- Insertar las dos empresas
INSERT INTO companies (id, name, sector, color) VALUES
(1, 'Maersk', 'Logística', '#0057a8'),
(2, 'Nexus Tech', 'Tecnología', '#7c3aed');

-- Insertar departamentos Maersk
INSERT INTO departments (company_id, name) VALUES
(1, 'Operaciones'),
(1, 'Almacén'),
(1, 'Logística'),
(1, 'Transporte'),
(1, 'Administración');

-- Insertar departamentos Nexus Tech
INSERT INTO departments (company_id, name) VALUES
(2, 'Desarrollo'),
(2, 'QA'),
(2, 'Diseño'),
(2, 'Soporte Técnico'),
(2, 'Administración');

EXIT;