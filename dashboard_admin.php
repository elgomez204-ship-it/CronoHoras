
<?php
require_once __DIR__ . '/common.php';
require_login();
$user = current_user();
if ($user['role'] !== 'admin') {
    header('Location: dashboard_employee.php');
    exit;
}

if (empty($_SESSION['company_id'])) {
    header('Location: company_select.php');
    exit;
}
$companyId = (int)$_SESSION['company_id'];
$companyName = $_SESSION['company_name'] ?? '';
$companyColor = $_SESSION['company_color'] ?? '#2563eb';
$adminStatus = get_employee_status($user['id']);

$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$dateFilter = $_GET['date'] ?? '';
$employeeFilter = $_GET['employee'] ?? '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['action']) && $_POST['action'] === 'admin_clock_in') {
        if (get_employee_status($user['id']) === 'inactive') {
            db()->prepare('INSERT INTO attendance (user_id, date, entry, `exit`, total_hours) VALUES (?, ?, ?, NULL, 0)')
                ->execute([$user['id'], date('Y-m-d'), date('Y-m-d H:i:s')]);
        }
        header('Location: dashboard_admin.php');
        exit;
    }
    if (!empty($_POST['action']) && $_POST['action'] === 'admin_clock_out') {
        $stmt = db()->prepare('SELECT id, entry FROM attendance WHERE user_id = ? AND `exit` IS NULL ORDER BY id DESC LIMIT 1');
        $stmt->execute([$user['id']]);
        $rec = $stmt->fetch();
        if ($rec) {
            $exit = time();
            $total = round(($exit - strtotime($rec['entry'])) / 3600, 2);
            db()->prepare('UPDATE attendance SET `exit` = ?, total_hours = ? WHERE id = ?')
                ->execute([date('Y-m-d H:i:s', $exit), $total, $rec['id']]);
        }
        header('Location: dashboard_admin.php');
        exit;
    }
    if (!empty($_POST['action']) && $_POST['action'] === 'clock_out_all') {
        $now = date('Y-m-d H:i:s');
        $activeStmt = db()->query('SELECT id, entry FROM attendance WHERE `exit` IS NULL');
        foreach ($activeStmt->fetchAll() as $rec) {
            $total = round((strtotime($now) - strtotime($rec['entry'])) / 3600, 2);
            db()->prepare('UPDATE attendance SET `exit` = ?, total_hours = ? WHERE id = ?')
                ->execute([$now, $total, $rec['id']]);
        }
        header('Location: dashboard_admin.php');
        exit;
    }
    if (!empty($_POST['action']) && $_POST['action'] === 'add_employee') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? null);
        $password = trim($_POST['password'] ?? '');
        if ($firstName && $lastName) {
            $pin = generate_pin();
            $passwordHash = $password ? password_hash($password, PASSWORD_BCRYPT) : null;
            $deptId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
            db()->prepare('INSERT INTO users (pin, role, first_name, last_name, email, password, locale, theme, company_id, department_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$pin, 'employee', $firstName, $lastName, $email, $passwordHash, current_locale(), current_theme(), $companyId, $deptId]);
            header('Location: dashboard_admin.php');
            exit;
        }
    }
    if (!empty($_POST['action']) && $_POST['action'] === 'update_employee' && !empty($_POST['user_id'])) {
        $id = (int)$_POST['user_id'];
        $password = trim($_POST['password'] ?? '');
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            db()->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, pin = ?, password = ? WHERE id = ?')
                ->execute([trim($_POST['first_name']), trim($_POST['last_name']), trim($_POST['email']), trim($_POST['pin']), $passwordHash, $id]);
        } else {
            db()->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, pin = ? WHERE id = ?')
                ->execute([trim($_POST['first_name']), trim($_POST['last_name']), trim($_POST['email']), trim($_POST['pin']), $id]);
        }
        header('Location: dashboard_admin.php');
        exit;
    }
    if (!empty($_POST['action']) && $_POST['action'] === 'delete_employee' && !empty($_POST['user_id'])) {
        $id = (int)$_POST['user_id'];
        db()->prepare('DELETE FROM attendance WHERE user_id = ?')->execute([$id]);
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        header('Location: dashboard_admin.php');
        exit;
    }
    if (!empty($_POST['theme']) || !empty($_POST['locale'])) {
        if (!empty($_POST['locale'])) {
            set_locale($_POST['locale']);
        }
        if (!empty($_POST['theme'])) {
            set_theme($_POST['theme']);
        }
        header('Location: dashboard_admin.php');
        exit;
    }
}
 
$query = "SELECT u.*, a.`exit` IS NULL AS active, a.entry AS current_entry, a.`exit` AS current_exit, a.total_hours AS current_total_hours
          FROM users u
          LEFT JOIN attendance a ON a.user_id = u.id AND a.id = (
            SELECT id FROM attendance WHERE user_id = u.id ORDER BY id DESC LIMIT 1
          )
          WHERE u.role = 'employee' AND u.company_id = " . (int)$companyId;
$params = [];
 
if ($filter === 'active') {
    $query .= ' AND a.`exit` IS NULL';
}
if ($filter === 'inactive') {
    $query .= ' AND (a.`exit` IS NOT NULL OR a.`exit` IS NULL AND a.id IS NULL)';
}
if ($search !== '') {
    $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.pin LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$query .= ' ORDER BY u.first_name, u.last_name';
$stmt = db()->prepare($query);
$stmt->execute($params);
$employees = $stmt->fetchAll();
 
$attendanceQuery = 'SELECT at.*, u.first_name, u.last_name, u.pin FROM attendance at JOIN users u ON at.user_id = u.id WHERE 1=1';
$attendanceParams = [];
if ($dateFilter) {
    $attendanceQuery .= ' AND at.date = ?';
    $attendanceParams[] = $dateFilter;
}
if ($employeeFilter) {
    $attendanceQuery .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.pin LIKE ?)';
    $attendanceParams[] = "%$employeeFilter%";
    $attendanceParams[] = "%$employeeFilter%";
    $attendanceParams[] = "%$employeeFilter%";
}
$attendanceQuery .= ' ORDER BY at.entry DESC LIMIT 40';
$attendanceStmt = db()->prepare($attendanceQuery);
$attendanceStmt->execute($attendanceParams);
$attendanceRecords = $attendanceStmt->fetchAll();
 
$today = date('Y-m-d');
$totalHoursTodayStmt = db()->prepare('SELECT COALESCE(SUM(total_hours), 0) AS total FROM attendance WHERE date = ?');
$totalHoursTodayStmt->execute([$today]);
$totalHoursToday = (float)$totalHoursTodayStmt->fetchColumn();
$employeeCount = count($employees);
$activeEmployees = 0;
foreach ($employees as $employee) {
    if ($employee['active']) {
        $activeEmployees++;
    }
}
$alertsCount = 0;
$timeNow = date('H:i:s');
 
layout_header(tr('adminDashboard'));
?>
<div class="min-h-screen bg-background px-4 py-10 text-foreground">
  <main class="mx-auto w-full max-w-7xl space-y-8">
    <section class="topbar-card p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
          <div class="grid h-16 w-16 place-items-center rounded-3xl bg-primary text-white shadow-xl">⏱️</div>
          <div>
            <p class="text-xs uppercase tracking-[0.32em] text-primary/80">Control de Horas</p>
            <h1 class="mt-2 text-3xl font-semibold"><?php echo tr('adminPanel'); ?></h1>
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <span id="dashboard_time" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($timeNow); ?></span>
          <span class="rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
          <a href="?locale=<?php echo current_locale() === 'es' ? 'en' : 'es'; ?>" class="topbar-pill"><?php echo strtoupper(current_locale() === 'es' ? 'EN' : 'ES'); ?></a>
          <a href="?theme=<?php echo current_theme() === 'light' ? 'dark' : 'light'; ?>" class="topbar-pill">🌓</a>
          <a href="company_select.php" class="topbar-pill">🏢 <?php echo htmlspecialchars($companyName); ?></a>
          <a href="logout.php" class="btn-secondary"><?php echo tr('logout'); ?></a>
        </div>
      </div>
    </section>
 
    <section class="grid gap-4 xl:grid-cols-[1.6fr,1fr]">
      <div class="grid gap-4 md:grid-cols-3">
        <div class="metric-card">
          <h3>Total de Horas</h3>
          <p class="metric-value"><?php echo number_format($totalHoursToday, 1, '.', ''); ?>h</p>
        </div>
        <div class="metric-card">
          <h3>Empleados activos</h3>
          <p class="metric-value"><?php echo htmlspecialchars($activeEmployees . '/' . $employeeCount); ?></p>
        </div>
        <div class="metric-card">
          <h3>Alertas</h3>
          <p class="metric-value"><?php echo $alertsCount; ?></p>
        </div>
      </div>
      <div class="status-card p-6">
        <div class="card-header">
          <div>
            <p class="text-sm font-semibold text-slate-500"><?php echo tr('mySchedule'); ?></p>
            <p class="mt-2 text-lg font-semibold text-slate-900"><?php echo $adminStatus === 'active' ? tr('inProgress') . ' • ' . htmlspecialchars($timeNow) : tr('inactive') . ' • ' . htmlspecialchars($timeNow); ?></p>
          </div>
          <form method="post">
              <input type="hidden" name="action" value="clock_out_all">
              <?php if ($adminStatus === 'inactive'): ?>
              <input type="hidden" name="action" value="admin_clock_in">
              <button type="submit" class="btn-primary"><?php echo tr('clockIn'); ?></button>
              <?php else: ?>
              <input type="hidden" name="action" value="admin_clock_out">
              <button type="submit" class="btn-success"><?php echo tr('clockOut'); ?></button>
              <?php endif; ?>
            </form>
        </div>
      </div>
    </section>
 
    <section class="page-panel p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold"><?php echo tr('totalHoursToday'); ?></h2>
        </div>
        <a href="export.php" class="btn-secondary"><?php echo tr('export'); ?></a>
      </div>
      <div class="chart-panel mt-6"></div>
    </section>
 
    <section class="page-panel p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-2xl font-semibold"><?php echo tr('employeeManagementTitle'); ?></h2>
        </div>
        <a href="#employee-form" class="btn-primary"><?php echo tr('addEmployee'); ?></a>
      </div>
      <div class="overflow-x-auto mt-6">
        <table class="table-clean">
          <thead>
            <tr>
              <th>NOMBRE</th>
              <th>ROL</th>
              <th>PIN DE 6 DÍGITOS</th>
              <th>DEPARTAMENTO</th>
              <th>ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($employees)): ?>
              <tr><td colspan="4"><?php echo tr('noRecords'); ?></td></tr>
            <?php else: ?>
              <?php foreach ($employees as $employee): ?>
                <tr>
                  <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                  <td><span class="pill<?php echo $employee['role'] === 'admin' ? ' pill-active' : ''; ?>"><?php echo htmlspecialchars($employee['role'] === 'admin' ? tr('admin') : tr('employee')); ?></span></td>
                  <td><?php echo htmlspecialchars($employee['pin'] ?? '-'); ?></td>
                  <td><?php
                $dname = '';
                if (!empty($employee['department_id'])) {
                    $ds = db()->prepare('SELECT name FROM departments WHERE id = ?');
                    $ds->execute([$employee['department_id']]);
                    $dname = $ds->fetchColumn() ?: '-';
                }
                echo htmlspecialchars($dname ?: '-');
              ?></td>
                  <td class="flex gap-2">
                    <a class="btn-secondary" href="dashboard_admin.php?edit_id=<?php echo $employee['id']; ?>#employee-form">✏️</a>
                    <form method="post" class="inline" onsubmit="return confirm('<?php echo tr('confirmDelete'); ?>');">
                      <input type="hidden" name="action" value="delete_employee">
                      <input type="hidden" name="user_id" value="<?php echo $employee['id']; ?>">
                      <button type="submit" class="btn-secondary">🗑️</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
 
    <section id="employee-form" class="page-panel p-8">
      <h2 class="text-2xl font-semibold"><?php echo tr('employeeManagement'); ?></h2>
      <?php $editUser = null; if (!empty($_GET['edit_id'])): ?>
        <?php
          $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND role = "employee"');
          $stmt->execute([(int)$_GET['edit_id']]);
          $editUser = $stmt->fetch();
        ?>
      <?php endif; ?>
      <form method="post" class="grid gap-6 mt-6">
        <input type="hidden" name="action" value="<?php echo $editUser ? 'update_employee' : 'add_employee'; ?>">
        <?php if ($editUser): ?>
          <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
        <?php endif; ?>
        <div class="grid gap-4 lg:grid-cols-2">
          <div>
            <label class="block text-sm font-semibold text-foreground"><?php echo tr('name'); ?></label>
            <input type="text" name="first_name" class="input mt-2" value="<?php echo htmlspecialchars($editUser['first_name'] ?? ''); ?>" required>
          </div>
          <div>
            <label class="block text-sm font-semibold text-foreground"><?php echo tr('lastName'); ?></label>
            <input type="text" name="last_name" class="input mt-2" value="<?php echo htmlspecialchars($editUser['last_name'] ?? ''); ?>" required>
          </div>
          <div>
            <label class="block text-sm font-semibold text-foreground"><?php echo tr('email'); ?></label>
            <input type="email" name="email" class="input mt-2" value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>">
          </div>
          <div>
            <label class="block text-sm font-semibold text-foreground"><?php echo tr('password'); ?></label>
            <input type="password" name="password" class="input mt-2" placeholder="<?php echo tr('passwordPlaceholder'); ?>">
          </div>
          <div>
            <label class="block text-sm font-semibold text-foreground">Departamento</label>
            <select name="department_id" class="input mt-2">
              <option value="">Sin departamento</option>
              <?php
              $deptStmt = db()->prepare('SELECT id, name FROM departments WHERE company_id = ? ORDER BY name');
              $deptStmt->execute([$companyId]);
              foreach ($deptStmt->fetchAll() as $dept):
              ?>
              <option value="<?php echo $dept['id']; ?>" <?php echo (isset($editUser) && $editUser && $editUser['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($dept['name']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-foreground"><?php echo tr('generatedPin'); ?></label>
            <input type="text" name="pin" class="input mt-2" value="<?php echo htmlspecialchars($editUser['pin'] ?? generate_pin()); ?>" readonly>
          </div>
        </div>
        <div class="flex flex-wrap gap-3 justify-end">
          <?php if ($editUser): ?>
            <a href="dashboard_admin.php" class="btn-secondary"><?php echo tr('cancel'); ?></a>
          <?php endif; ?>
          <button type="submit" class="btn-primary"><?php echo $editUser ? tr('saveChanges') : tr('save'); ?></button>
        </div>
      </form>
    </section>
 
    <section id="attendance-history" class="page-panel p-8">
      <h2 class="text-2xl font-semibold"><?php echo tr('attendanceHistoryTitle'); ?></h2>
      <div class="overflow-x-auto mt-6">
        <table class="table-clean">
          <thead>
            <tr>
              <th><?php echo tr('date'); ?></th>
              <th><?php echo tr('name'); ?></th>
              <th><?php echo tr('entryTime'); ?></th>
              <th><?php echo tr('exitTime'); ?></th>
              <th><?php echo tr('totalHours'); ?></th>
              <th><?php echo tr('status'); ?></th>
              <th><?php echo tr('edit'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($attendanceRecords)): ?>
              <tr><td colspan="7"><?php echo tr('noRecords'); ?></td></tr>
            <?php else: ?>
              <?php foreach ($attendanceRecords as $record): ?>
                <tr>
                  <td><?php echo htmlspecialchars($record['date']); ?></td>
                  <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name'] . ' (' . $record['pin'] . ')'); ?></td>
                  <td><?php echo htmlspecialchars(format_date($record['entry'])); ?></td>
                  <td><?php echo $record['exit'] ? htmlspecialchars(format_date($record['exit'])) : '-'; ?></td>
                  <td><?php echo format_hours((float)$record['total_hours']); ?></td>
                  <td><?php echo $record['exit'] ? tr('inactive') : tr('active'); ?></td>
                  <td><a class="btn-secondary" href="attendance_edit.php?id=<?php echo $record['id']; ?>"><?php echo tr('editRecord'); ?></a></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>
 
<?php layout_footer();
 
