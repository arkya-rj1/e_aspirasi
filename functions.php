<?php
// ==================================================
// functions.php - Helper keamanan & utilitas
// ==================================================
require_once __DIR__ . '/config.php';
// Escape output -> mencegah XSS
function e(?string $str): string {
return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
// ---- CSRF Protection ----
function csrf_token(): string {
if (empty($_SESSION['csrf_token'])) {
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
return $_SESSION['csrf_token'];
}
function csrf_field(): string {
return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
function verify_csrf(): void {
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
http_response_code(403);
die('Permintaan ditolak (CSRF token tidak valid). Muat ulang halaman.');
}
}
// ---- Kode unik pelacakan laporan ----
function generate_report_token(PDO $pdo): string {
do {
$token = strtoupper(rtrim(strtr(base64_encode(random_bytes(8)), '+/', 'XZ'), '='));
$stmt = $pdo->prepare('SELECT 1 FROM reports WHERE token = ?');
$stmt->execute([$token]);
} while ($stmt->fetchColumn());
return $token;
}
// ---- Rate limiting sederhana (anti spam form) ----
function check_rate_limit(string $key, int $max = 3, int $seconds = 300): bool {
$now = time();
$_SESSION['rl'][$key] = array_filter($_SESSION['rl'][$key] ?? [], fn($t) => $t > $now - $seconds);
if (count($_SESSION['rl'][$key]) >= $max) return false;
$_SESSION['rl'][$key][] = $now;
return true;
}
// ---- Flash message ----
function set_flash(string $type, string $msg): void {
$_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function get_flash(): ?array {
$f = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
return $f;
}
// ---- Cek login admin ----
function require_admin(): void {
if (empty($_SESSION['admin_id'])) {
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
}
}