<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/db.php';

// Process locale/theme switches immediately so they take effect on current page
if (!empty($_GET['locale']) && in_array($_GET['locale'], ['es', 'en'], true)) {
    $_SESSION['locale'] = $_GET['locale'];
}
if (!empty($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'], true)) {
    $_SESSION['theme'] = $_GET['theme'];
}

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function current_locale(): string {
    if (!empty($_SESSION['locale'])) {
        return $_SESSION['locale'];
    }
    $user = current_user();
    return $user['locale'] ?? 'es';
}

function current_theme(): string {
    if (!empty($_SESSION['theme'])) {
        return $_SESSION['theme'];
    }
    $user = current_user();
    return $user['theme'] ?? 'light';
}

function set_locale(string $locale): void {
    $_SESSION['locale'] = $locale;
    $user = current_user();
    if ($user) {
        db()->prepare('UPDATE users SET locale = ? WHERE id = ?')->execute([$locale, $user['id']]);
    }
}

function set_theme(string $theme): void {
    $_SESSION['theme'] = $theme;
    $user = current_user();
    if ($user) {
        db()->prepare('UPDATE users SET theme = ? WHERE id = ?')->execute([$theme, $user['id']]);
    }
}

function tr(string $key): string {
    $translations = [
        'es' => [
            'loginTitle' => 'Inicio de sesión',
            'pinLabel' => 'PIN (4-6 dígitos)',
            'loginButton' => 'Iniciar sesión',
            'language' => 'Idioma',
            'theme' => 'Modo',
            'light' => 'Claro',
            'dark' => 'Oscuro',
            'invalidPin' => 'Credenciales incorrectas. Intenta de nuevo.',
            'invalidCredentials' => 'Credenciales incorrectas. Intenta de nuevo.',
            'missingCredentials' => 'Completa el correo y la contraseña.',
            'invalidPinFormat' => 'Ingresa un PIN de 4 a 6 dígitos.',
            'invalidDateRange' => 'Las horas de entrada y salida no son válidas.',
            'registerTitle' => 'Registro',
            'createAccount' => 'Crear cuenta',
            'alreadyHaveAccount' => '¿Ya tienes cuenta?',
            'employeeDashboard' => 'Dashboard Empleado',
            'adminDashboard' => 'Dashboard Administrador',
            'welcome' => 'Bienvenido',
            'logout' => 'Cerrar sesión',
            'status' => 'Estado',
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'clockIn' => 'Clock In',
            'clockOut' => 'Clock Out',
            'hoursToday' => 'Horas hoy',
            'hoursWeek' => 'Horas esta semana',
            'hoursMonth' => 'Horas este mes',
            'attendanceHistory' => 'Historial de asistencias',
            'date' => 'Fecha',
            'entryTime' => 'Hora de entrada',
            'exitTime' => 'Hora de salida',
            'totalHours' => 'Total horas',
            'employeeList' => 'Lista de empleados',
            'filter' => 'Filtro',
            'all' => 'Todos',
            'searchPlaceholder' => 'Buscar por nombre o PIN',
            'addEmployee' => 'Agregar empleado',
            'editEmployee' => 'Editar empleado',
            'deleteEmployee' => 'Eliminar empleado',
            'confirmDelete' => '¿Confirmas eliminar este empleado?',
            'name' => 'Nombre',
            'lastName' => 'Apellido',
            'email' => 'Correo',
            'password' => 'Contraseña',
            'passwordPlaceholder' => 'Dejar vacío para mantener',
            'generatedPin' => 'PIN automático',
            'save' => 'Guardar',
            'cancel' => 'Cancelar',
            'editRecord' => 'Editar horas',
            'noRecords' => 'No hay registros',
            'activeFilter' => 'Activos',
            'inactiveFilter' => 'Inactivos',
            'selectedEmployee' => 'Empleado',
            'employeeManagement' => 'Gestión de empleados',
            'realTimeView' => 'Vista en tiempo real',
            'showAll' => 'Mostrar todo',
            'today' => 'Hoy',
            'week' => 'Semana',
            'month' => 'Mes',
            'userProfile' => 'Perfil',
            'saveChanges' => 'Guardar cambios',
            'edit' => 'Editar',
            'statusActive' => 'Activo',
            'statusInactive' => 'Inactivo',
            'search' => 'Buscar',
            'other' => 'Otro',
            'selectRole' => 'Selecciona tu rol',
            'employee' => 'Empleado',
            'admin' => 'Administrador',
            'employeeDesc' => 'Control de asistencia',
            'adminDesc' => 'Gestión del sistema',
            'loginPin' => 'PIN de acceso',
            'loginEmail' => 'Correo y contraseña',
            'adminPanel' => 'Panel Administrativo',
            'employeeDashboardTitle' => 'Dashboard Empleado',
            'adminDashboardTitle' => 'Panel Administrativo',
            'scheduleControl' => 'Control de horario',
            'inProgress' => 'En curso',
            'attendanceHistoryTitle' => 'Historial de asistencias',
            'totalHoursToday' => 'Total de Horas — Hoy',
            'activeEmployees' => 'Empleados activos',
            'alerts' => 'Alertas',
            'registerExit' => 'Registrar Salida',
            'addEmployee' => 'Agregar Empleado',
            'employeeManagementTitle' => 'Gestión de Empleados',
            'export' => 'Exportar',
            'controlDeHoras' => 'Control de Horas',
            'mySchedule' => 'Mi horario',
        ],
        'en' => [
            'loginTitle' => 'Login',
            'pinLabel' => 'PIN (4-6 digits)',
            'loginButton' => 'Sign in',
            'language' => 'Language',
            'theme' => 'Theme',
            'light' => 'Light',
            'dark' => 'Dark',
            'invalidPin' => 'Incorrect credentials. Try again.',
            'invalidCredentials' => 'Incorrect credentials. Try again.',
            'missingCredentials' => 'Please enter email and password.',
            'invalidPinFormat' => 'Enter a 4-6 digit PIN.',
            'invalidDateRange' => 'Entry and exit times are not valid.',
            'registerTitle' => 'Register',
            'createAccount' => 'Create account',
            'alreadyHaveAccount' => 'Already have an account?',
            'employeeDashboard' => 'Employee Dashboard',
            'adminDashboard' => 'Admin Dashboard',
            'welcome' => 'Welcome',
            'logout' => 'Logout',
            'status' => 'Status',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'clockIn' => 'Clock In',
            'clockOut' => 'Clock Out',
            'hoursToday' => 'Hours Today',
            'hoursWeek' => 'Hours This Week',
            'hoursMonth' => 'Hours This Month',
            'attendanceHistory' => 'Attendance History',
            'date' => 'Date',
            'entryTime' => 'Entry Time',
            'exitTime' => 'Exit Time',
            'totalHours' => 'Total Hours',
            'employeeList' => 'Employee List',
            'filter' => 'Filter',
            'all' => 'All',
            'searchPlaceholder' => 'Search by name or PIN',
            'addEmployee' => 'Add Employee',
            'editEmployee' => 'Edit Employee',
            'deleteEmployee' => 'Delete Employee',
            'confirmDelete' => 'Confirm delete this employee?',
            'name' => 'Name',
            'lastName' => 'Last Name',
            'email' => 'Email',
            'password' => 'Password',
            'passwordPlaceholder' => 'Leave blank to keep current',
            'generatedPin' => 'Auto PIN',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'editRecord' => 'Edit hours',
            'noRecords' => 'No records',
            'activeFilter' => 'Active',
            'inactiveFilter' => 'Inactive',
            'selectedEmployee' => 'Employee',
            'employeeManagement' => 'Employee management',
            'realTimeView' => 'Real-time view',
            'showAll' => 'Show all',
            'today' => 'Today',
            'week' => 'Week',
            'month' => 'Month',
            'userProfile' => 'Profile',
            'saveChanges' => 'Save changes',
            'edit' => 'Edit',
            'statusActive' => 'Active',
            'statusInactive' => 'Inactive',
            'search' => 'Search',
            'other' => 'Other',
            'selectRole' => 'Select your role',
            'employee' => 'Employee',
            'admin' => 'Administrator',
            'employeeDesc' => 'Attendance tracking',
            'adminDesc' => 'System management',
            'loginPin' => 'PIN access',
            'loginEmail' => 'Email and password',
            'adminPanel' => 'Admin Panel',
            'employeeDashboardTitle' => 'Employee Dashboard',
            'adminDashboardTitle' => 'Admin Panel',
            'scheduleControl' => 'Schedule Control',
            'inProgress' => 'In progress',
            'attendanceHistoryTitle' => 'Attendance History',
            'totalHoursToday' => 'Total Hours — Today',
            'activeEmployees' => 'Active employees',
            'alerts' => 'Alerts',
            'registerExit' => 'Register Exit',
            'addEmployee' => 'Add Employee',
            'employeeManagementTitle' => 'Employee Management',
            'export' => 'Export',
            'controlDeHoras' => 'Hours Control',
            'mySchedule' => 'My schedule',
        ],
    ];

    $locale = current_locale();
    return $translations[$locale][$key] ?? $translations['es'][$key] ?? $key;
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function redirect_to_dashboard(): void {
    $user = current_user();
    if (!$user) {
        header('Location: index.php');
        exit;
    }
    if ($user['role'] === 'admin') {
        header('Location: dashboard_admin.php');
    } else {
        header('Location: dashboard_employee.php');
    }
    exit;
}

function format_date(string $value): string {
    return date('Y-m-d H:i', strtotime($value));
}

function format_hours(float $value): string {
    return number_format($value, 2, '.', ',');
}

function generate_pin(): string {
    $pdo = db();
    do {
        $pin = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('SELECT id FROM users WHERE pin = ?');
        $stmt->execute([$pin]);
    } while ($stmt->fetch());
    return $pin;
}

function ensure_default_accounts(): void {
    $pdo = db();
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    if ((int)$stmt->fetchColumn() === 0) {
        $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
        $pdo->prepare('INSERT INTO users (pin, role, first_name, last_name, email, password, locale, theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute(['123456', 'admin', 'Marta', 'Ríos', 'marta.rios@empresa.com', $passwordHash, 'es', 'light']);
        $pdo->prepare('INSERT INTO users (pin, role, first_name, last_name, email, password, locale, theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute(['111111', 'employee', 'Ana', 'González', 'ana.gonzalez@empresa.com', $passwordHash, 'es', 'light']);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['juan@empresa.com']);
    if (!$stmt->fetch()) {
        $passwordHash = password_hash('123456789', PASSWORD_BCRYPT);
        $pdo->prepare('INSERT INTO users (pin, role, first_name, last_name, email, password, locale, theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute(['000000', 'admin', 'Juan', 'Empresa', 'juan@empresa.com', $passwordHash, 'es', 'light']);
    }

    $fillStmt = $pdo->query('SELECT id, pin FROM users WHERE password IS NULL OR password = ""');
    foreach ($fillStmt->fetchAll() as $missingUser) {
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([password_hash($missingUser['pin'], PASSWORD_BCRYPT), $missingUser['id']]);
    }
}

function is_admin(): bool {
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function get_employee_status(int $userId): string {
    $stmt = db()->prepare('SELECT `exit` FROM attendance WHERE user_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row && $row['exit'] === null ? 'active' : 'inactive';
}

function total_hours_for_period(int $userId, string $start, string $end): float {
    $stmt = db()->prepare('SELECT COALESCE(SUM(total_hours), 0) AS total FROM attendance WHERE user_id = ? AND entry BETWEEN ? AND ?');
    $stmt->execute([$userId, $start, $end]);
    return (float)$stmt->fetchColumn();
}

function attendance_summary(int $userId): array {
    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $monthStart = date('Y-m-01');
    return [
        'today' => total_hours_for_period($userId, "$today 00:00:00", "$today 23:59:59"),
        'week' => total_hours_for_period($userId, "$weekStart 00:00:00", date('Y-m-d H:i:s')), 
        'month' => total_hours_for_period($userId, "$monthStart 00:00:00", date('Y-m-d H:i:s')),
    ];
}

function layout_header(string $title): void {
    $theme = current_theme();
    $cssPath = 'style.css';
    $version = time();
    $html  = '<!DOCTYPE html>';
    $html .= '<html lang="' . htmlspecialchars(current_locale()) . '">';
    $html .= '<head>';
    $html .= '<meta charset="UTF-8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $html .= '<title>' . htmlspecialchars($title) . '</title>';
    $html .= '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">';
    $html .= '<script src="https://cdn.tailwindcss.com"></script>';
    $html .= '<link rel="stylesheet" href="' . htmlspecialchars($cssPath . '?v=' . $version) . '">';
    $html .= '</head>';
    $html .= '<body class="' . htmlspecialchars($theme) . '">';
    echo $html;
}

function layout_footer(): void {
    echo '</body></html>';
}