<?php
// ============================================================
// includes/config.php — Database Connection & Global Settings
// ============================================================

define('SITE_NAME', 'The Elite Attorney');
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
define('SITE_URL', $protocol . '://' . $host);
define('UPLOAD_DIR', __DIR__ . '/../uploads/photos/');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lawyers_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;color:red;padding:20px;">Database Connection Failed: ' . $conn->connect_error . '</div>');
}
$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdmin()    { return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'; }
function isLawyer()   { return isset($_SESSION['lawyer_id']); }
function isCustomer() { return isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'customer'; }

function clean($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}

function redirect($url, $msg = '', $type = 'success') {
    if ($msg) { $_SESSION['flash_msg'] = $msg; $_SESSION['flash_type'] = $type; }
    header("Location: $url");
    exit;
}
?>
