# CronoHoras PHP/MySQL

Esta aplicación es un prototipo de control de asistencias hecho con PHP, MySQL, HTML y CSS.

## Instalación

1. Copia la carpeta en el servidor web (por ejemplo, `htdocs` o `www`).
2. Crea la base de datos usando el script `sql/setup.sql`.
   - `mysql -u root -p < sql/setup.sql`
3. Ajusta las variables en `db.php` si tu servidor MySQL usa otra contraseña o usuario.
4. Abre `index.php` en el navegador.

## Usuarios de ejemplo

- Empleado 1: PIN `1111`
- Empleado 2: PIN `2222`
- Administrador: PIN `9999`

## Funcionalidades

- Login por PIN con cambio de idioma y modo visual.
- Dashboard empleado con control de Clock In / Clock Out.
- Resumen diario, semanal y mensual.
- Historial personal de asistencias.
- Dashboard administrador con lista de empleados en tiempo real.
- Filtro de empleados activos/inactivos y búsqueda por nombre o PIN.
- Gestión de empleados: agregar, editar y eliminar.
- Historial completo de asistencias con edición de horas.
- Preferencias de idioma y modo persistentes por sesión y guardadas en la base de datos.

## Archivos importantes

- `index.php` - Login y selección de idioma/tema.
- `dashboard_employee.php` - Dashboard para empleados.
- `dashboard_admin.php` - Dashboard para administradores.
- `attendance_edit.php` - Edición de registros de asistencia.
- `logout.php` - Cierre de sesión.
- `db.php` - Configuración de la base de datos.
- `style.css` - Estilos responsivos.
