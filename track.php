<?php
require_once __DIR__ . '/functions.php';
$hasil = null;
$cari = trim($_GET['token'] ?? '');
if ($cari !== '') {
// Hanya cari berdasarkan token; prepared statement mencegah SQL injection
$stmt = $pdo->prepare('SELECT token, kategori, judul, isi, nama, kelas, is_anonim, status, tanggapan,
created_at
FROM reports WHERE token = ?');
$stmt->execute([$cari]);
$hasil = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lacak Status - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="topbar">
<div class="container">
<h1>n Lacak Laporan</h1>
<nav>
<a href="<?= BASE_URL ?>/index.php">Buat Laporan</a>
<a href="<?= BASE_URL ?>/track.php" class="active">Lacak Status</a>
<a href="<?= BASE_URL ?>/admin/login.php">Login Admin</a>
</nav>
</div>
</header>
<main class="container">
<section class="panel">
<h2>Masukkan Kode Pelacakan</h2>
<form method="GET" action="" class="track-form">
<input type="text" name="token" maxlength="12" required
placeholder="Contoh: XZ7K2PQ9AB"
value="<?= e($cari) ?>" style="text-transform:uppercase">
<button type="submit" class="btn primary">Lacak</button>
</form>
</section>
<?php if ($cari !== ''): ?>
<?php if ($hasil): ?>
<?php
$badge = ['Menunggu' => 'warn', 'Diproses' => 'info', 'Selesai' => 'ok'];
?>
<section class="panel">
<div class="report-head">
<h2><?= e($hasil['judul']) ?></h2>
<span class="badge <?= $badge[$hasil['status']] ?>"><?= e($hasil['status']) ?></span>
</div>
<table class="detail">
<tr><th>Kode</th><td><?= e($hasil['token']) ?></td></tr>
<tr><th>Kategori</th><td><?= e($hasil['kategori']) ?></td></tr>
<tr><th>Pelapor</th><td><?= $hasil['is_anonim'] ? 'Anonim' : e($hasil['nama']) ?></td></tr>
<tr><th>Dikirim</th><td><?= e($hasil['created_at']) ?></td></tr>
<tr><th>Isi</th><td><?= nl2br(e($hasil['isi'])) ?></td></tr>
</table>
<h3>Tanggapan</h3>
<?php if ($hasil['tanggapan']): ?>
<div class="response-box"><?= nl2br(e($hasil['tanggapan'])) ?></div>
<?php else: ?>
<p class="muted">Belum ada tanggapan dari admin.</p>
<?php endif; ?>
</section>
<?php else: ?>
<div class="alert error">Kode pelacakan tidak ditemukan. Periksa kembali kode Anda.</div>
<?php endif; ?>
<?php endif; ?>
</main>
<footer class="footer"><div class="container">© <?= date('Y') ?> <?= e(APP_NAME) ?></div></footer>
</body>
</html>