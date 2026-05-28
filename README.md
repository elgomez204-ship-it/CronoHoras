# CronoHoras

Sistema web de control de asistencia y gestión de horas laborales en tiempo real, desarrollado en PHP con MySQL.

---

## Descripción

CronoHoras permite a empresas registrar y gestionar las horas de trabajo de sus empleados de forma sencilla. Los empleados fichан entrada y salida desde su propio panel, mientras que los administradores tienen una vista completa con historial, edición de registros y gestión del personal.

---

## Características principales

### Panel de Empleado
- Clock In / Clock Out con un solo clic
- Visualización del estado actual (En curso / Inactivo)
- Historial de asistencias con fecha, hora de entrada, hora de salida, total de horas y estado
- Acceso mediante correo + contraseña o PIN de 6 dígitos

### Panel de Administrador
- Clock In / Clock Out propio (el admin también puede registrar sus horas)
- Métricas en tiempo real: total de horas del día, empleados activos, alertas
- Gestión completa de empleados: crear, editar y eliminar
- PIN de acceso generado automáticamente al crear empleados
- Historial completo de asistencias de todos los empleados
- Edición manual de registros de entrada/salida
- Exportación de datos

### General
- Soporte bilingüe: Español e Inglés (cambio instantáneo)
- Modo claro y modo oscuro
- Reloj en tiempo real en el topbar
- Autenticación por correo/contraseña o PIN
- Registro de nuevas cuentas (admin o empleado)
- Zona horaria configurable (por defecto: Europe/Madrid)

---

## Tecnologías

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8+ |
| Base de datos | MySQL / MariaDB |
| Frontend | HTML, Tailwind CSS (CDN), CSS personalizado |
| Servidor | Apache (Ubuntu / XAMPP / WAMP) |
| Sesiones | PHP Sessions nativas |

---

## Estructura de archivos

```
CronoHoras/
├── index.php              # Página de login
├── register.php           # Registro de nuevas cuentas
├── logout.php             # Cierre de sesión
├── dashboard_admin.php    # Panel del administrador
├── dashboard_employee.php # Panel del empleado
├── attendance_edit.php    # Edición de registros de asistencia
├── common.php             # Funciones compartidas, traducciones, layout
├── db.php                 # Configuración y conexión a la base de datos
└── style.css              # Estilos personalizados
```

---

## Instalación

### Requisitos
- PHP 8.0 o superior
- MySQL 5.7 o superior / MariaDB 10.3+
- Apache con `mod_rewrite` habilitado
- Extensión PDO_MySQL habilitada en PHP

### Pasos

**1. Clonar o copiar los archivos**
```bash
sudo cp -r CronoHoras/ /var/www/html/CronoHoras
```

**2. Crear la base de datos**
```sql
CREATE DATABASE cronohoras CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**3. Crear las tablas**
```sql
USE cronohoras;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pin VARCHAR(6) UNIQUE,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255),
    locale VARCHAR(5) DEFAULT 'es',
    theme VARCHAR(10) DEFAULT 'light'
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    entry DATETIME NOT NULL,
    `exit` DATETIME DEFAULT NULL,
    total_hours DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**4. Configurar la base de datos**

Edita `db.php` con tus credenciales:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cronohoras');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

**5. Configurar la zona horaria**

En `common.php`, cambia la línea:
```php
date_default_timezone_set('Europe/Madrid');
```
Por tu zona horaria correspondiente. Consulta la lista completa en [php.net/timezones](https://www.php.net/manual/es/timezones.php).

**6. Dar permisos al directorio**
```bash
sudo chown -R www-data:www-data /var/www/html/CronoHoras
sudo chmod -R 755 /var/www/html/CronoHoras
```

**7. Acceder a la aplicación**
```
http://localhost/CronoHoras/
```



## Uso básico

### Como Empleado
1. Inicia sesión con tu correo/contraseña o tu PIN de 6 dígitos
2. Pulsa **Clock In** para registrar tu entrada
3. Pulsa **Clock Out** cuando termines tu jornada
4. Consulta tu historial de asistencias en la tabla inferior

### Como Administrador
1. Inicia sesión con tu cuenta de administrador
2. Desde el panel puedes:
   - Registrar tu propia entrada/salida con **Clock In / Clock Out**
   - Ver cuántos empleados están activos en tiempo real
   - Agregar nuevos empleados con el formulario (se genera un PIN automáticamente)
   - Editar o eliminar empleados existentes
   - Corregir registros de asistencia desde el historial
3. Cambia el idioma pulsando **ES / EN** en la barra superior
4. Cambia entre modo claro y oscuro con el botón **🌓**

---

## Seguridad

- Las contraseñas se almacenan con hash `bcrypt` (`PASSWORD_BCRYPT`)
- Las consultas SQL usan **PDO con prepared statements** para prevenir inyección SQL
- Las salidas HTML usan `htmlspecialchars()` para prevenir XSS
- Las sesiones PHP gestionan la autenticación
