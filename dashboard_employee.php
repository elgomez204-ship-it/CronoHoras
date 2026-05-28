<?php
require_once __DIR__ . '/common.php';
require_login();
$user = current_user();
if ($user['role'] !== 'employee') {
    header('Location: dashboard_admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['action']) && $_POST['action'] === 'clock_in') {
        $status = get_employee_status($user['id']);
        if ($status === 'inactive') {
            db()->prepare('INSERT INTO attendance (user_id, date, entry, `exit`, total_hours) VALUES (?, ?, ?, NULL, 0)')
                ->execute([$user['id'], date('Y-m-d'), date('Y-m-d H:i:s')]);
        }
    }
    if (!empty($_POST['action']) && $_POST['action'] === 'clock_out') {
        $status = get_employee_status($user['id']);
        if ($status === 'active') {
            $stmt = db()->prepare('SELECT id, entry FROM attendance WHERE user_id = ? AND `exit` IS NULL ORDER BY id DESC LIMIT 1');
            $stmt->execute([$user['id']]);
            $record = $stmt->fetch();
            if ($record) {
                $entry = strtotime($record['entry']);
                $exit = time();
                $total = round(($exit - $entry) / 3600, 2);
                db()->prepare('UPDATE attendance SET `exit` = ?, total_hours = ? WHERE id = ?')
                    ->execute([date('Y-m-d H:i:s', $exit), $total, $record['id']]);
            }
        }
    }
    if (!empty($_POST['theme']) || !empty($_POST['locale'])) {
        if (!empty($_POST['locale'])) {
            set_locale($_POST['locale']);
        }
        if (!empty($_POST['theme'])) {
            set_theme($_POST['theme']);
        }
    }
    header('Location: dashboard_employee.php');
    exit;
}

$summary = attendance_summary($user['id']);
$status = get_employee_status($user['id']);
$timeNow = date('H:i:s');
$stmt = db()->prepare('SELECT * FROM attendance WHERE user_id = ? ORDER BY entry DESC LIMIT 20');
$stmt->execute([$user['id']]);
$records = $stmt->fetchAll();

layout_header(tr('employeeDashboard'));
?>
<div class="min-h-screen bg-background px-4 py-10 text-foreground">
  <main class="mx-auto w-full max-w-6xl space-y-8">
    <section class="topbar-card p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
          <div class="grid h-16 w-16 place-items-center rounded-3xl bg-primary text-white shadow-xl">⏱️</div>
          <div>
            <p class="text-xs uppercase tracking-[0.32em] text-primary/80">CronoHoras &nbsp;·&nbsp; <?php echo tr('controlDeHoras'); ?></p>
            <h1 class="mt-2 text-3xl font-semibold"><?php echo tr('employeeDashboardTitle'); ?></h1>
            <p class="mt-1 text-sm text-muted-foreground"><?php echo tr('welcome'); ?>, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <span id="dashboard_time" class="topbar-pill"><?php echo htmlspecialchars($timeNow); ?></span>
          <a href="?locale=<?php echo current_locale() === 'es' ? 'en' : 'es'; ?>" class="topbar-pill"><?php echo strtoupper(current_locale() === 'es' ? 'EN' : 'ES'); ?></a>
          <a href="?theme=<?php echo current_theme() === 'light' ? 'dark' : 'light'; ?>" class="topbar-pill">🌓</a>
          <a href="logout.php" class="btn-secondary"><?php echo tr('logout'); ?></a>
        </div>
      </div>
    </section>

    <section class="page-panel p-6">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-2xl font-semibold"><?php echo tr('scheduleControl'); ?></h2>
          <p class="mt-2 text-sm text-muted-foreground"><?php echo $status === 'active' ? tr('inProgress') : tr('inactive'); ?></p>
        </div>
        <form method="post" class="flex flex-wrap gap-3 justify-start lg:justify-end">
          <?php if ($status === 'inactive'): ?>
            <input type="hidden" name="action" value="clock_in">
            <button type="submit" class="btn-primary"><?php echo tr('clockIn'); ?></button>
          <?php else: ?>
            <input type="hidden" name="action" value="clock_out">
            <button type="submit" class="btn-success"><?php echo tr('clockOut'); ?></button>
          <?php endif; ?>
        </form>
      </div>
    </section>

    <section class="page-panel p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-2xl font-semibold"><?php echo tr('attendanceHistoryTitle'); ?></h2>
        </div>
      </div>
      <div class="overflow-x-auto mt-6">
        <table class="table-clean">
          <thead>
            <tr>
              <th><?php echo tr('date'); ?></th>
              <th><?php echo tr('entryTime'); ?></th>
              <th><?php echo tr('exitTime'); ?></th>
              <th><?php echo tr('totalHours'); ?></th>
              <th><?php echo tr('status'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($records)): ?>
              <tr><td colspan="5"><?php echo tr('noRecords'); ?></td></tr>
            <?php else: ?>
              <?php foreach ($records as $record): ?>
                <tr>
                  <td><?php echo htmlspecialchars($record['date']); ?></td>
                  <td><?php echo htmlspecialchars(format_date($record['entry'])); ?></td>
                  <td><?php echo $record['exit'] ? htmlspecialchars(format_date($record['exit'])) : '-'; ?></td>
                  <td><?php echo format_hours((float)$record['total_hours']); ?></td>
                  <td><?php echo $record['exit'] ? tr('inactive') : tr('active'); ?></td>
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
