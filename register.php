<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');

        if (strlen($name) < 3) {
            $error = 'Nama lengkap minimal 3 karakter.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } elseif (strlen($pass) < 6) {
            $error = 'Kata sandi minimal 6 karakter.';
        } else {
            $pdo = getPDO();
            $chk = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $error = 'Email sudah terdaftar.';
            } else {
                $parts = preg_split('/\s+/', $name);
                $initials = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? 'N', 0, 1));
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, avatar_initials) VALUES (?, ?, ?, ?, ?)');
                $ins->execute([$name, $email, $hash, 'user', $initials]);

                $_SESSION['user'] = [
                    'id' => (int)$pdo->lastInsertId(),
                    'name' => $name,
                    'email' => $email,
                    'role' => 'user',
                    'avatar_initials' => $initials,
                ];
                header('Location: index.php');
                exit;
            }
        }
    }
}

$currentPage = 'auth';
$pageTitle = 'Daftar Akun – AQUANEST';
require __DIR__ . '/includes/header.php';
?>

<section class="section-pad" style="background:var(--cream);">
  <div class="container" style="max-width:480px;">
    <div style="background:#fff;border:1px solid var(--line);border-radius:24px;padding:32px;">
      <h1 style="font-size:24px;margin-bottom:6px;">Daftar Akun Penjelajah</h1>
      <p style="font-size:14px;color:var(--muted);margin-bottom:20px;">Sudah punya akun? <a href="login.php" style="color:var(--brand);font-weight:600;">Masuk di sini</a></p>

      <?php if ($error): ?>
        <div class="alert-err"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php" style="display:grid;gap:14px;">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div>
          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Nama Lengkap</label>
          <input type="text" name="name" required class="input" style="width:100%;">
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Email</label>
          <input type="email" name="email" required class="input" style="width:100%;">
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Kata Sandi (min. 6 karakter)</label>
          <input type="password" name="password" required class="input" style="width:100%;">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Buat Akun</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
