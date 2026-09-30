<?php
require_once __DIR__ . '/../functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM reports WHERE id = ?');
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) {
http_response_code(404);
die('Laporan tidak ditemukan.');
}
// ---- Update status / tanggapan ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
verify_csrf();
$status_baru = $_POST['status'] ?? '';
$tanggapan_baru = trim($_POST['tanggapan'] ?? '');
$status_valid = ['Menunggu', 'Diproses', 'Selesai'];
if (!in_array($status_baru, $status_valid, true)) {
die('Status tidak valid.');
}
$pdo->beginTransaction();
$stmt = $pdo->prepare('UPDATE reports SET status = ?, tanggapan = ? WHERE id = ?');
$stmt->execute([$status_baru, $tanggapan_baru !== '' ? $tanggapan_baru : null, $id]);
// Audit trail
$log = $pdo->prepare(
'INSERT INTO audit_logs (report_id, admin_id, aksi, detail) VALUES (?, ?, ?, ?)'
);
$log->execute([$id, $_SESSION['admin_id'], 'Ubah status',
"Status: {$r['status']} -> $status_baru"]);
$pdo->commit();
set_flash('ok', 'Laporan berhasil diperbarui.');
header('Location: ' . BASE_URL . '/admin/dashboard.php');
exit;
}
// ---- Riwayat audit laporan ini ----
$logs = $pdo->prepare(
'SELECT a.*, ad.username FROM audit_logs a
JOIN admins ad ON ad.id = a.admin_id
WHERE a.report_id = ? ORDER BY a.created_at DESC'
);
$logs->execute([$id]);
$logs = $logs->fetchAll();
$badge = ['Menunggu'=>'warn','Diproses'=>'info','Selesai'=>'ok'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Laporan #<?= e($r['token']) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="topbar">
<div class="container">
<h1>Kelola Laporan</h1>
<nav><a href="<?= BASE_URL ?>/admin/dashboard.php">← Kembali ke Dashboard</a></nav>
</div>
</header>
<main class="container">
<section class="panel">
<div class="report-head">
<h2><?= e($r['judul']) ?></h2>
<span class="badge <?= $badge[$r['status']] ?>"><?= e($r['status']) ?></span>
</div>
<table class="detail">
<tr><th>Kode</th><td><code><?= e($r['token']) ?></code></td></tr>
<tr><th>Kategori</th><td><?= e($r['kategori']) ?></td></tr>
<tr><th>Pelapor</th><td><?= $r['is_anonim'] ? 'Anonim' : e($r['nama']) . ' (' . e($r['kelas']) .
')' ?></td></tr>
<tr><th>Dikirim</th><td><?= e($r['created_at']) ?></td></tr>
<tr><th>Isi</th><td><?= nl2br(e($r['isi'])) ?></td></tr>
</table>
</section>
<section class="panel">
<h2>Update Status & Tanggapan</h2>
<form method="POST" action="">
<?= csrf_field() ?>
<label for="status">Status</label>
<select id="status" name="status" required>
<?php foreach (['Menunggu','Diproses','Selesai'] as $s): ?>
<option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
<?php endforeach; ?>
</select>
<label for="tanggapan">Tanggapan untuk Pelapor <small>(opsional, terlihat di halaman
lacak)</small></label>
<textarea id="tanggapan" name="tanggapan" rows="5"
placeholder="Contoh: Keran sudah diperbaiki oleh tim sarana..."><?= e($r['tanggapan'])
?></textarea>
<button type="submit" class="btn primary">Simpan Perubahan</button>
</form>
</section>
<section class="panel">
<h3>Riwayat Perubahan</h3>
<?php if (!$logs): ?>
<p class="muted">Belum ada riwayat.</p>
<?php else: ?>
<ul class="log-list">
<?php foreach ($logs as $l): ?>
<li><code><?= e($l['created_at']) ?></code> — <?= e($l['username']) ?>: <?= e($l['detail'])
?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</section>
</main>
</body>
</html>