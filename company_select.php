<?php
require_once __DIR__ . '/common.php';
require_login();
$user = current_user();
if ($user['role'] !== 'admin') {
    header('Location: dashboard_employee.php');
    exit;
}

if (!empty($_POST['company_id'])) {
    $cid = (int)$_POST['company_id'];
    $stmt = db()->prepare('SELECT id, name, sector, color FROM companies WHERE id = ?');
    $stmt->execute([$cid]);
    $company = $stmt->fetch();
    if ($company) {
        $_SESSION['company_id'] = $company['id'];
        $_SESSION['company_name'] = $company['name'];
        $_SESSION['company_color'] = $company['color'];
        $_SESSION['company_sector'] = $company['sector'];
        header('Location: dashboard_admin.php');
        exit;
    }
}

$companies = db()->query('SELECT * FROM companies ORDER BY id')->fetchAll();
layout_header('Seleccionar Empresa');
?>
<div class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 100%);">
  <main class="w-full max-w-3xl px-4">
    <div class="text-center mb-10">
      <div class="brand-badge mx-auto mb-4" style="width:64px;height:64px;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <p class="text-xs font-bold uppercase tracking-widest text-blue-400 mb-1">CronoHoras</p>
      <h1 class="text-3xl font-bold text-slate-800">Selecciona una empresa</h1>
      <p class="mt-2 text-slate-500">Bienvenido, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>. ¿Con cuál empresa trabajas hoy?</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($companies as $company): ?>
        <form method="post">
          <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
          <button type="submit" class="w-full text-left company-card" style="--company-color: <?php echo htmlspecialchars($company['color']); ?>">
            <div class="company-icon" style="background: <?php echo htmlspecialchars($company['color']); ?>;">
              <?php if ($company['id'] == 1): ?>
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
                  <path d="M9 22V12h6v10" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
              <?php else: ?>
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="2" y="3" width="20" height="14" rx="2" stroke="white" stroke-width="1.5"/>
                  <path d="M8 21h8M12 17v4" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
              <?php endif; ?>
            </div>
            <div class="mt-4">
              <h2 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($company['name']); ?></h2>
              <p class="text-sm text-slate-500 mt-1"><?php echo htmlspecialchars($company['sector']); ?></p>
            </div>
            <div class="company-arrow" style="color: <?php echo htmlspecialchars($company['color']); ?>;">→</div>
          </button>
        </form>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-8">
      <a href="logout.php" class="text-sm text-slate-400 hover:text-slate-600 font-medium">Cerrar sesión</a>
    </div>
  </main>
</div>

<style>
.company-card {
  position: relative;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 1.25rem;
  padding: 2rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 12px rgba(15,23,42,0.06);
}
.company-card:hover {
  border-color: var(--company-color);
  box-shadow: 0 8px 30px rgba(15,23,42,0.12);
  transform: translateY(-3px);
}
.company-icon {
  width: 72px;
  height: 72px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.company-arrow {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  font-size: 1.5rem;
  font-weight: bold;
  opacity: 0;
  transition: opacity 0.2s;
}
.company-card:hover .company-arrow {
  opacity: 1;
}
</style>
<?php layout_footer(); ?>
