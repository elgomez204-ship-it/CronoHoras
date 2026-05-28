<?php
require_once __DIR__ . '/common.php';
require_login();
$user = current_user();
if ($user['role'] !== 'admin') {
    header('Location: dashboard_employee.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT at.*, u.first_name, u.last_name, u.pin FROM attendance at JOIN users u ON at.user_id = u.id WHERE at.id = ?');
$stmt->execute([$id]);
$record = $stmt->fetch();
if (!$record) {
    header('Location: dashboard_admin.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry = trim($_POST['entry'] ?? '');
    $exit = trim($_POST['exit'] ?? '');
    if ($entry && $exit && strtotime($entry) && strtotime($exit) && strtotime($exit) >= strtotime($entry)) {
        $total = round((strtotime($exit) - strtotime($entry)) / 3600, 2);
        db()->prepare('UPDATE attendance SET entry = ?, `exit` = ?, total_hours = ? WHERE id = ?')
            ->execute([$entry, $exit, $total, $id]);
        header('Location: dashboard_admin.php');
        exit;
    }
    $error = tr('invalidDateRange');
}

layout_header(tr('editRecord'));
?>
<div class="min-h-screen bg-background px-4 py-10 text-foreground">
  <main class="mx-auto w-full max-w-3xl px-4 py-10">
    <section class="page-panel p-8">
      <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-semibold tracking-tight"><?php echo tr('editRecord'); ?></h1>
          <p class="mt-2 text-sm text-muted-foreground"><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name'] . ' (' . $record['pin'] . ')'); ?></p>
        </div>
        <a class="btn-secondary" href="dashboard_admin.php"><?php echo tr('cancel'); ?></a>
      </div>

      <?php if ($error): ?>
        <div class="rounded-3xl border border-rose-200/70 bg-rose-50 p-4 text-sm text-rose-700"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" class="mt-6 grid gap-5">
        <div>
          <label class="block text-sm font-semibold text-foreground"><?php echo tr('entryTime'); ?></label>
          <input type="datetime-local" name="entry" class="input mt-2" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($record['entry']))); ?>" required>
        </div>
        <div>
          <label class="block text-sm font-semibold text-foreground"><?php echo tr('exitTime'); ?></label>
          <input type="datetime-local" name="exit" class="input mt-2" value="<?php echo $record['exit'] ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($record['exit']))) : ''; ?>">
        </div>
        <button type="submit" class="btn-primary w-full"><?php echo tr('saveChanges'); ?></button>
      </form>
    </section>
  </main>
</div>
<?php layout_footer();
