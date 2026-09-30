<?php
// ==================================================
// config.php - Koneksi database & pengaturan dasar
// ==================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'e_aspirasi');
define('DB_USER', 'root'); // sesuaikan
define('DB_PASS', ''); // sesuaikan
define('APP_NAME', 'E-Aspirasi SMK MUHAMMADIYAH MAJENANG');
define('BASE_URL', '/aspirasi_arya'); // sesuaikan dengan lokasi folder project
// ---- Pengamanan session ----
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
ini_set('session.cookie_secure', '1');
}
if (session_status() === PHP_SESSION_NONE) {
session_start();
}
// ---- Koneksi PDO (prepared statement native) ----
$options = [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false,
];
try {
$pdo = new PDO(
'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
DB_USER, DB_PASS, $options
);
} catch (PDOException $e) {
// Jangan tampilkan detail error ke user di production
error_log('DB Error: ' . $e->getMessage());
die('Koneksi database gagal. Hubungi administrator.');
}