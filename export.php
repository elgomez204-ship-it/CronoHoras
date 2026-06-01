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
$companyName = $_SESSION['company_name'] ?? 'empresa';

$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$employeeFilter = trim($_GET['employee'] ?? '');

if (!empty($_GET['download'])) {
    $query = 'SELECT u.first_name, u.last_name, u.pin, d.name as department,
                     at.date, at.entry, at.`exit`, at.total_hours
              FROM attendance at
              JOIN users u ON at.user_id = u.id
              LEFT JOIN departments d ON u.department_id = d.id
              WHERE u.company_id = ?
              AND at.date BETWEEN ? AND ?';
    $params = [$companyId, $dateFrom, $dateTo];

    if ($employeeFilter) {
        $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.pin LIKE ?)';
        $params[] = "%$employeeFilter%";
        $params[] = "%$employeeFilter%";
        $params[] = "%$employeeFilter%";
    }
    $query .= ' ORDER BY at.date DESC, u.last_name';

    $stmt = db()->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    $filename = 'asistencias_' . $companyName . '_' . $dateFrom . '_' . $dateTo . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
    fputcsv($out, ['Nombre', 'Apellido', 'PIN', 'Departamento', 'Fecha', 'Hora Entrada', 'Hora Salida', 'Total Horas']);
    foreach ($records as $r) {
        fputcsv($out, [
            $r['first_name'],
            $r['last_name'],
            $r['pin'],
            $r['department'] ?? '-',
            $r['date'],
            $r['entry'] ? date('H:i', strtotime($r['entry'])) : '-',
            $r['exit'] ? date('H:i', strtotime($r['exit'])) : '-',
            number_format((float)$r['total_hours'], 2)
        ]);
    }
    fclose($out);
    exit;
}

// Preview data
$query = 'SELECT u.first_name, u.last_name, u.pin, d.name as department,
                 at.date, at.entry, at.`exit`, at.total_hours
          FROM attendance at
          JOIN users u ON at.user_id = u.id
          LEFT JOIN departments d ON u.department_id = d.id
          WHERE u.company_id = ?
          AND at.date BETWEEN ? AND ?';
$params = [$companyId, $dateFrom, $dateTo];
if ($employeeFilter) {
    $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.pin LIKE ?)';
    $params[] = "%$employeeFilter%";
    $params[] = "%$employeeFilter%";
    $params[] = "%$employeeFilter%";
}
$query .= ' ORDER BY at.date DESC, u.last_name';
$stmt = db()->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();

layout_header('Exportar Asistencias');
?>
<div class="min-h-screen bg-background px-4 py-10 text-foreground">
  <main class="mx-auto w-full max-w-5xl space-y-6">

    <div class="topbar-card p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="brand-badge" style="background:#2563eb;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M12 7v6l4 2" stroke="white" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
          </div>
          <div>
            <p class="text-xs uppercase tracking-widest text-blue-400">CronoHoras · <?php echo htmlspecialchars($companyName); ?></p>
            <h1 class="text-2xl font-bold">Exportar Asistencias</h1>
          </div>
        </div>
        <a href="dashboard_admin.php" class="btn-secondary">← Volver</a>
      </div>
    </div>

    <div class="page-panel p-6">
      <h2 class="text-lg font-semibold mb-4">Filtros</h2>
      <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-1">Fecha desde</label>
          <input type="date" name="date_from" class="input" value="<?php echo htmlspecialchars($dateFrom); ?>">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Fecha hasta</label>
          <input type="date" name="date_to" class="input" value="<?php echo htmlspecialchars($dateTo); ?>">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Empleado (opcional)</label>
          <input type="text" name="employee" class="input" placeholder="Nombre o PIN" value="<?php echo htmlspecialchars($employeeFilter); ?>">
        </div>
        <div class="flex items-end gap-2">
          <button type="submit" class="btn-primary flex-1">Filtrar</button>
        </div>
      </form>
    </div>

    <div class="page-panel p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-lg font-semibold">Vista previa</h2>
          <p class="text-sm text-muted-foreground"><?php echo count($records); ?> registros encontrados</p>
        </div>
        <?php if (!empty($records)): ?>
          <a href="?date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>&employee=<?php echo urlencode($employeeFilter); ?>&download=1"
             class="btn-primary">
            ⬇ Descargar CSV
          </a>
        <?php endif; ?>
      </div>

      <div class="overflow-x-auto">
        <table class="table-clean">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Departamento</th>
              <th>Fecha</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Total Horas</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($records)): ?>
              <tr><td colspan="6" class="text-center py-4">No hay registros para este período</td></tr>
            <?php else: ?>
              <?php foreach ($records as $r): ?>
                <tr>
                  <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name'] . ' (' . $r['pin'] . ')'); ?></td>
                  <td><?php echo htmlspecialchars($r['department'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($r['date']); ?></td>
                  <td><?php echo $r['entry'] ? date('H:i', strtotime($r['entry'])) : '-'; ?></td>
                  <td><?php echo $r['exit'] ? date('H:i', strtotime($r['exit'])) : '-'; ?></td>
                  <td><?php echo number_format((float)$r['total_hours'], 2); ?>h</td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>
<?php layout_footer(); ?>
