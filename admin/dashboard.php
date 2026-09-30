<?php
require_once __DIR__ . '/../functions.php';
require_admin();
// ---- Statistik ----
$stat = $pdo->query("SELECT
COUNT(*) AS total,
SUM(status='Menunggu') AS menunggu,
SUM(status='Diproses') AS diproses,
SUM(status='Selesai') AS selesai
FROM reports")->fetch();
// ---- Filter status + pencarian ----
$filter = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM reports WHERE 1=1';
$params = [];
if (in_array($filter, ['Menunggu','Diproses','Selesai'], true)) {
$sql .= ' AND status = ?';
$params[] = $filter;
}
if ($q !== '') {
$sql .= ' AND (judul LIKE ? OR token LIKE ? OR nama LIKE ?)';
$like = "%$q%";
array_push($params, $like, $like, $like);
}
$sql .= ' ORDER BY created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="topbar">
<div class="container">
<h1>Dashboard Admin</h1>
<nav>
<span class="muted">Halo, <?= e($_SESSION['admin_user']) ?></span>
<a href="<?= BASE_URL ?>/admin/ganti_password.php">Ganti Password</a>
<a href="<?= BASE_URL ?>/admin/logout.php">Logout</a>
</nav>
</div>
</header>
<main class="container">
<?php if ($flash): ?>
<div class="alert <?= $flash['type'] === 'ok' ? 'success' : 'error' ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
<section class="cards">
<div class="card"><span class="num"><?= (int)$stat['total'] ?></span><span
class="lbl">Total</span></div>
<div class="card warn"><span class="num"><?= (int)$stat['menunggu'] ?></span><span
class="lbl">Menunggu</span></div>
<div class="card info"><span class="num"><?= (int)$stat['diproses'] ?></span><span
class="lbl">Diproses</span></div>
<div class="card ok"><span class="num"><?= (int)$stat['selesai'] ?></span><span
class="lbl">Selesai</span></div>
</section>
<section class="panel">
<form method="GET" action="" class="track-form">
<select name="status">
<option value="">Semua Status</option>
<?php foreach (['Menunggu','Diproses','Selesai'] as $s): ?>
<option value="<?= $s ?>" <?= $filter === $s ? 'selected' : '' ?>><?= $s ?></option>
<?php endforeach; ?>
</select>
<input type="text" name="q" placeholder="Cari judul / kode / nama..." value="<?= e($q) ?>">
<button type="submit" class="btn">Filter</button>
</form>
<table class="table">
<thead>
<tr>
<th>Kode</th><th>Judul</th><th>Kategori</th><th>Pelapor</th>
<th>Status</th><th>Tanggal</th><th></th>
</tr>
</thead>
<tbody>
<?php if (!$rows): ?>
<tr><td colspan="7" class="muted">Belum ada laporan.</td></tr>
<?php endif; ?>
<?php $badge = ['Menunggu'=>'warn','Diproses'=>'info','Selesai'=>'ok']; ?>
<?php foreach ($rows as $r): ?>
<tr>
<td><code><?= e($r['token']) ?></code></td>
<td><?= e($r['judul']) ?></td>
<td><?= e($r['kategori']) ?></td>
<td><?= $r['is_anonim'] ? '<em>Anonim</em>' : e($r['nama']) ?></td>
<td><span class="badge <?= $badge[$r['status']] ?>"><?= e($r['status']) ?></span></td>
<td><?= e($r['created_at']) ?></td>
<td><a class="btn small" href="<?= BASE_URL ?>/admin/detail.php?id=<?= (int)$r['id']
?>">Kelola</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</section>
</main>
</body>
</html>\
