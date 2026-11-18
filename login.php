<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');
        $stmt = getPDO()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar_initials' => $user['avatar_initials'],
            ];
            header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
            exit;
        }
        $error = 'Email atau kata sandi tidak sesuai.';
    }
}

$currentPage = 'auth';
$pageTitle = 'Masuk – AQUANEST';
require __DIR__ . '/includes/header.php';
?>

<section class="section-pad" style="background:var(--cream);">
  <div class="container" style="max-width:480px;">
    <div style="background:#fff;border:1px solid var(--line);border-radius:24px;padding:32px;">
      <h1 style="font-size:24px;margin-bottom:6px;">Masuk ke AQUANEST</h1>
      <p style="font-size:14px;color:var(--muted);margin-bottom:20px;">Belum punya akun? <a href="register.php" style="color:var(--brand);font-weight:600;">Daftar sekarang</a></p>

      <?php if ($error): ?>
        <div class="alert-err"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" style="display:grid;gap:14px;">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div>
          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Email</label>
          <input type="email" name="email" required class="input" style="width:100%;" placeholder="admin@aquanest.id">
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Kata Sandi</label>
          <input type="password" name="password" required class="input" style="width:100%;" placeholder="Kata sandi">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Masuk</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
