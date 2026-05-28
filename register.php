<?php
require_once __DIR__ . '/common.php';

$error = '';
$selected_role = $_POST['role'] ?? $_GET['role'] ?? 'employee';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $selected_role = $_POST['role'] ?? 'employee';

    if (!$firstName || !$lastName || !$email || !$password) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Ya existe una cuenta con este correo.';
        } else {
            $pin = generate_pin();
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = db()->prepare('INSERT INTO users (pin, role, first_name, last_name, email, password, locale, theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $pin,
                $selected_role === 'admin' ? 'admin' : 'employee',
                $firstName,
                $lastName,
                $email,
                $passwordHash,
                current_locale(),
                current_theme(),
            ]);

            $_SESSION['user_id'] = (int)db()->lastInsertId();
            redirect_to_dashboard();
        }
    }
}

layout_header(tr('registerTitle'));
?>
<div class="min-h-screen login-bg">
  <main class="mx-auto w-full max-w-md px-4 py-20">
    <section class="login-panel p-8">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center">
          <div class="brand-badge">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
              <rect x="6" y="6" width="12" height="12" rx="2" stroke="currentColor" stroke-width="0" fill="none" />
              <path d="M12 9v6M9 12h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            </svg>
          </div>
        </div>
        <h1 class="text-3xl font-semibold tracking-tight text-foreground">Crear Cuenta</h1>
        <p class="mt-3 text-sm text-muted-foreground lead">Regístrate para comenzar.</p>
      </div>

      <div class="grid gap-4">
        <div class="toggle-group">
          <a href="?role=admin" class="toggle-btn<?php echo $selected_role === 'admin' ? ' active' : ''; ?>">
            <span class="toggle-icon">🛡️</span>
            <span class="toggle-text">Administrador</span>
          </a>
          <a href="?role=employee" class="toggle-btn<?php echo $selected_role === 'employee' ? ' active' : ''; ?>">
            <span class="toggle-icon">👤</span>
            <span class="toggle-text">Empleado</span>
          </a>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="rounded-3xl border border-rose-200/70 bg-rose-50 p-4 text-sm text-rose-700"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form action="register.php" method="post" id="register_form" class="mt-6 grid gap-5">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($selected_role); ?>">
        <div>
          <label for="first_name" class="block text-sm font-semibold text-foreground">Nombre</label>
          <input id="first_name" type="text" name="first_name" autocomplete="given-name" class="input mt-2" placeholder="Nombre" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required />
        </div>
        <div>
          <label for="last_name" class="block text-sm font-semibold text-foreground">Apellido</label>
          <input id="last_name" type="text" name="last_name" autocomplete="family-name" class="input mt-2" placeholder="Apellido" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required />
        </div>
        <div>
          <label for="email" class="block text-sm font-semibold text-foreground">Correo electrónico</label>
          <input id="email" type="email" name="email" autocomplete="email" class="input mt-2" placeholder="usuario@empresa.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
        </div>
        <div>
          <label for="password" class="block text-sm font-semibold text-foreground">Contraseña</label>
          <input id="password" type="password" name="password" autocomplete="new-password" class="input mt-2" placeholder="••••••••" required />
        </div>

        <button type="submit" class="btn-primary w-full">Crear Cuenta</button>
      </form>

      <div class="mt-6 text-center text-sm text-slate-500">
        <?php echo tr('alreadyHaveAccount'); ?> <a href="index.php" class="link"><?php echo tr('loginTitle'); ?></a>
      </div>
    </section>
  </main>
</div>

<?php layout_footer();
