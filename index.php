<?php
require_once __DIR__ . '/common.php';

ensure_default_accounts();

$error = '';
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$pin = trim($_POST['pin'] ?? '');
$selected_role = $_POST['role'] ?? $_GET['role'] ?? 'employee';
$auth_method = $_POST['auth_method'] ?? $_GET['auth_method'] ?? 'email';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['authenticate'])) {
    $role = $_POST['role'] ?? '';

    if ($auth_method === 'pin') {
        if (!preg_match('/^\d{4,6}$/', $pin)) {
            $error = tr('invalidPinFormat');
        } else {
            $stmt = db()->prepare('SELECT id, role, locale, theme FROM users WHERE pin = ?');
            $stmt->execute([$pin]);
            $user = $stmt->fetch();
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['locale'] = $user['locale'] ?? 'es';
                $_SESSION['theme'] = $user['theme'] ?? 'light';
                redirect_to_dashboard();
            }
            $error = tr('invalidCredentials');
        }
    } elseif ($auth_method === 'email') {
        if (!$email || !$password) {
            $error = tr('missingCredentials');
        } else {
            $stmt = db()->prepare('SELECT id, role, locale, theme, password FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && $user['password'] && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['locale'] = $user['locale'] ?? 'es';
                $_SESSION['theme'] = $user['theme'] ?? 'light';
                redirect_to_dashboard();
            }
            $error = tr('invalidCredentials');
        }
    }
}

if (!empty($_GET['locale']) && in_array($_GET['locale'], ['es', 'en'], true)) {
    set_locale($_GET['locale']);
}
if (!empty($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'], true)) {
    set_theme($_GET['theme']);
}

layout_header(tr('loginTitle'));
?>
<div class="min-h-screen login-bg">
  <main class="mx-auto w-full max-w-md px-4 py-20">
    <section class="login-panel p-8">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center">
          <div class="brand-badge" style="background:#2563eb !important;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="0" fill="none" />
              <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            </svg>
          </div>
        </div>
        <p class="text-xs font-bold uppercase tracking-widest text-blue-400 mb-1">Control de Horas</p><h1 class="text-3xl font-semibold tracking-tight text-foreground">CronoHoras</h1>
        <p class="mt-3 text-sm text-muted-foreground lead">Gestión de tiempo en tiempo real.</p>
      </div>

      <div class="grid gap-4">
        <div class="toggle-group">
          <a href="?role=admin&auth_method=<?php echo htmlspecialchars($auth_method); ?>" class="toggle-btn<?php echo $selected_role === 'admin' ? ' active' : ''; ?>">
              <span class="toggle-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
                  <path d="M12 2l7 3v5c0 5.25-3.88 9.87-7 11-3.12-1.13-7-5.75-7-11V5l7-3z" fill="currentColor" />
                  <path d="M9.5 11.5l1.5 1.5 3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                </svg>
              </span>
              <span class="toggle-text"><?php echo tr('admin'); ?></span>
            </a>
            <a href="?role=employee&auth_method=<?php echo htmlspecialchars($auth_method); ?>" class="toggle-btn<?php echo $selected_role === 'employee' ? ' active' : ''; ?>">
              <span class="toggle-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
                  <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" fill="currentColor" />
                  <path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
              </span>
              <span class="toggle-text"><?php echo tr('employee'); ?></span>
            </a>
          </div>
          <div class="toggle-group">
            <a href="?role=<?php echo htmlspecialchars($selected_role); ?>&auth_method=email" class="toggle-btn<?php echo $auth_method === 'email' ? ' active' : ''; ?>">
              <span class="toggle-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
                  <path d="M4 7.5v9c0 .83.67 1.5 1.5 1.5h13c.83 0 1.5-.67 1.5-1.5v-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M4 7.5l8 5 8-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
              <span class="toggle-text"><?php echo tr('loginEmail'); ?></span>
            </a>
            <a href="?role=<?php echo htmlspecialchars($selected_role); ?>&auth_method=pin" class="toggle-btn<?php echo $auth_method === 'pin' ? ' active' : ''; ?>">
              <span class="toggle-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
                  <rect x="6" y="10" width="12" height="8" rx="2" stroke="currentColor" stroke-width="1.5" />
                  <path d="M9 10V8a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
              </span>
              <span class="toggle-text"><?php echo tr('loginPin'); ?></span>
            </a>
          </div>

      <form method="post" id="auth_form" class="mt-8 space-y-5">
        <input type="hidden" name="authenticate" value="1">
        <input type="hidden" name="role" id="form_role" value="<?php echo htmlspecialchars($selected_role); ?>">
        <input type="hidden" name="auth_method" id="form_auth_method" value="<?php echo htmlspecialchars($auth_method); ?>">

        <div id="email_section" class="auth-section space-y-4" style="display: <?php echo $auth_method === 'email' ? 'block' : 'none'; ?>;">
          <div class="space-y-2">
            <label for="email" class="block text-sm font-semibold text-foreground"><?php echo tr('email'); ?></label>
            <input id="email" type="email" name="email" class="input" placeholder="usuario@empresa.com" value="<?php echo htmlspecialchars($email); ?>" <?php echo $auth_method === 'email' ? 'required' : ''; ?> />
          </div>
          <div class="space-y-2">
            <label for="password" class="block text-sm font-semibold text-foreground"><?php echo tr('password'); ?></label>
            <input id="password" type="password" name="password" class="input" placeholder="••••••••" value="<?php echo htmlspecialchars($password); ?>" <?php echo $auth_method === 'email' ? 'required' : ''; ?> />
          </div>
        </div>

        <div id="pin_section" class="auth-section space-y-4" style="display: <?php echo $auth_method === 'pin' ? 'block' : 'none'; ?>;">
          <div class="space-y-2">
            <label for="pin" class="block text-sm font-semibold text-foreground"><?php echo tr('pinLabel'); ?></label>
            <input id="pin" type="text" name="pin" maxlength="6" class="input" autocomplete="off" pattern="\d{6}" placeholder="000000" value="<?php echo htmlspecialchars($pin); ?>" <?php echo $auth_method === 'pin' ? 'required' : ''; ?> />
          </div>
        </div>

        <?php if ($error): ?>
          <div class="rounded-3xl border border-rose-200/70 bg-rose-50 p-4 text-sm text-rose-700"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <button type="submit" class="btn-primary w-full"><?php echo tr('loginButton'); ?> →</button>
      </form>

      <div class="mt-6 text-center text-sm text-slate-500">
        ¿No tienes cuenta? <a href="register.php" style="color:#2563eb;font-weight:700;text-decoration:none;"><?php echo tr('createAccount'); ?></a>
      </div>
    </section>
  </main>
</div>

<?php layout_footer();
