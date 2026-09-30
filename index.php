<?php
require_once __DIR__ . '/functions.php';
$sukses_token = null;
$error = null;
// ---- Statistik publik (ringkas) ----
$stat = $pdo->query("SELECT
COUNT(*) AS total,
SUM(status='Menunggu') AS menunggu,
SUM(status='Diproses') AS diproses,
SUM(status='Selesai') AS selesai
FROM reports")->fetch();
// ---- Proses form pengaduan ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
verify_csrf();
if (!check_rate_limit('kirim_laporan', 3, 600)) {
$error = 'Terlalu banyak pengiriman. Coba lagi dalam beberapa menit.';
} else {
$kategori = $_POST['kategori'] ?? '';
$judul = trim($_POST['judul'] ?? '');
$isi = trim($_POST['isi'] ?? '');
$anonim = !empty($_POST['anonim']);
$nama = $anonim ? null : trim($_POST['nama'] ?? '');
$kelas = $anonim ? null : trim($_POST['kelas'] ?? '');
$kategori_valid = ['Kritik', 'Saran', 'Fasilitas Rusak', 'Lainnya'];
if (!in_array($kategori, $kategori_valid, true)) {
$error = 'Kategori tidak valid.';
} elseif ($judul === '' || mb_strlen($judul) > 150) {
$error = 'Judul wajib diisi (maks. 150 karakter).';
} elseif ($isi === '' || mb_strlen($isi) > 3000) {
$error = 'Isi laporan wajib diisi (maks. 3000 karakter).';
} elseif (!$anonim && $nama === '') {
$error = 'Nama wajib diisi kecuali Anda memilih mode anonim.';
} else {
$token = generate_report_token($pdo);
$stmt = $pdo->prepare(
'INSERT INTO reports (token, kategori, judul, isi, nama, kelas, is_anonim)
VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$token, $kategori, $judul, $isi, $nama, $kelas, $anonim ? 1 : 0]);
$sukses_token = $token;
}
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="topbar">
<div class="container">
<h1>E-Aspirasi SMK MUHAMMADIYAH MAJENANG</h1>
<p class="subtitle">Sampaikan kritik, saran, atau laporan fasilitas — transparan & terpantau</p>
<nav>
<a href="<?= BASE_URL ?>/index.php" class="active">Buat Laporan</a>
<a href="<?= BASE_URL ?>/track.php">Lacak Status</a>
<a href="<?= BASE_URL ?>/admin/login.php">Login Admin</a>
</nav>
</div>
</header>
<main class="container">
<!-- Statistik ringkas publik -->
<section class="cards">
<div class="card"><span class="num"><?= (int)$stat['total'] ?></span><span class="lbl">Total
    Laporan</span></div>
<div class="card warn"><span class="num"><?= (int)$stat['menunggu'] ?></span><span
class="lbl">Menunggu</span></div>
<div class="card info"><span class="num"><?= (int)$stat['diproses'] ?></span><span
class="lbl">Diproses</span></div>
<div class="card ok"><span class="num"><?= (int)$stat['selesai'] ?></span><span
class="lbl">Selesai</span></div>
</section>
<?php if ($sukses_token): ?>
<section class="panel success-box">
<h2>n Laporan Terkirim!</h2>
<p>Simpan kode pelacakan berikut untuk memantau status laporan Anda:</p>
<div class="token-box"><?= e($sukses_token) ?></div>
<a class="btn" href="<?= BASE_URL ?>/track.php?token=<?= e($sukses_token) ?>">Lacak Sekarang</a>
</section>
<?php else: ?>
<section class="panel">
<h2>Form Pengaduan / Aspirasi</h2>
<?php if ($error): ?>
<div class="alert error"><?= e($error) ?></div>
<?php endif; ?>
<form method="POST" action="">
<?= csrf_field() ?>
<label for="kategori">Kategori</label>
<select id="kategori" name="kategori" required>
<option value="">-- Pilih kategori --</option>
<option>Kritik</option>
<option>Saran</option>
<option>Fasilitas Rusak</option>
<option>Lainnya</option>
</select>
<label for="judul">Judul Laporan</label>
<input type="text" id="judul" name="judul" maxlength="150" required
placeholder="Contoh: Keran air di toilet lantai 2 bocor">
<label for="isi">Isi Laporan</label>
<textarea id="isi" name="isi" rows="6" maxlength="3000" required
placeholder="Jelaskan secara detail..."></textarea>
<div class="anon-box">
<label class="checkbox">
<input type="checkbox" name="anonim" id="anonim" value="1">
<strong>Kirim sebagai Anonim</strong> — identitas Anda tidak akan tercatat
</label>
</div>
<div id="identitas">
<label for="nama">Nama</label>
<input type="text" id="nama" name="nama" maxlength="100" placeholder="Nama lengkap">
<label for="kelas">Kelas <small>(opsional)</small></label>
<input type="text" id="kelas" name="kelas" maxlength="20" placeholder="Contoh: XI IPA 1">
</div>
<button type="submit" class="btn primary">Kirim Laporan</button>
</form>
</section>
<?php endif; ?>
</main>
<footer class="footer">
<div class="container">© <?= date('Y') ?> <?= e(APP_NAME) ?> — Laporan Anda terpantau secara
transparan.</div>
</footer>
<script>
// Sembunyikan/tampilkan field identitas saat anonim dicentang
document.getElementById('anonim').addEventListener('change', function () {
document.getElementById('identitas').style.display = this.checked ? 'none' : '';
});
</script>
</body>
</html>