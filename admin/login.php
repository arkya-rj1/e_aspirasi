<?php
require_once __DIR__ . '/../functions.php';
// Sudah login? langsung ke dashboard
if (!empty($_SESSION['admin_id'])) {
header('Location: ' . BASE_URL . '/admin/dashboard.php');
exit;
}
$error = null;
// ---- Rate limiting login (5x gagal -> kunci 10 menit) ----
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? ['count' => 0, 'time' => 0];
if (time() - $_SESSION['login_attempts']['time'] > 600) {
$_SESSION['login_attempts'] = ['count' => 0, 'time' => 0];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
verify_csrf();
if ($_SESSION['login_attempts']['count'] >= 5) {
$error = 'Terlalu banyak percobaan gagal. Coba lagi 10 menit lagi.';
} else {
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
$stmt->execute([$username]);
$admin = $stmt->fetch();
if ($admin && password_verify($password, $admin['password_hash'])) {
session_regenerate_id(true); // cegah session fixation
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_user'] = $admin['username'];
$_SESSION['login_attempts'] = ['count' => 0, 'time' => 0];
header('Location: ' . BASE_URL . '/admin/dashboard.php');
exit;
} else {
$_SESSION['login_attempts']['count']++;
$_SESSION['login_attempts']['time'] = time();
$error = 'Username atau password salah.';
}
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<main class="container" style="max-width:420px; margin-top:8vh;">
<section class="panel">
<h2>n Login Admin</h2>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="POST" action="">
<?= csrf_field() ?>
<label for="username">Username</label>
<input type="text" id="username" name="username" required autocomplete="username">
<label for="password">Password</label>
<input type="password" id="password" name="password" required autocomplete="current-password">
<button type="submit" class="btn primary" style="width:100%">Masuk</button>
</form>
<p class="muted" style="margin-top:15px;"><a href="<?= BASE_URL ?>/index.php">← Kembali ke halaman
utama</a></p>
</section>
</main>
</body>
</html>