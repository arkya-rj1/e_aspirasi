<?php
require_once __DIR__ . '/../functions.php';
require_admin();
$error = null; $sukses = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
verify_csrf();
$lama = $_POST['password_lama'] ?? '';
$baru = $_POST['password_baru'] ?? '';
$ulang = $_POST['password_ulang'] ?? '';
$stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
$stmt->execute([$_SESSION['admin_id']]);
$hash = $stmt->fetchColumn();
if (!password_verify($lama, $hash)) {
$error = 'Password lama salah.';
} elseif (strlen($baru) < 8) {
$error = 'Password baru minimal 8 karakter.';
} elseif ($baru !== $ulang) {
$error = 'Konfirmasi password tidak cocok.';
} else {
$stmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
$stmt->execute([password_hash($baru, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
$sukses = 'Password berhasil diganti.';
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ganti Password</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<main class="container" style="max-width:480px; margin-top:8vh;">
<section class="panel">
<h2>n Ganti Password</h2>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if ($sukses): ?><div class="alert success"><?= e($sukses) ?></div><?php endif; ?>
<form method="POST" action="">
<?= csrf_field() ?>
<label>Password Lama</label>
<input type="password" name="password_lama" required autocomplete="current-password">
<label>Password Baru (min. 8 karakter)</label>
<input type="password" name="password_baru" required autocomplete="new-password">
<label>Ulangi Password Baru</label>
<input type="password" name="password_ulang" required autocomplete="new-password">
<button type="submit" class="btn primary" style="width:100%">Simpan</button>
</form>
<p class="muted" style="margin-top:15px;"><a href="<?= BASE_URL ?>/admin/dashboard.php">←
Kembali</a></p>
</section>
</main>
</body>
</html>