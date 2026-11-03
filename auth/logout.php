<?php
// ============================================================
// auth/logout.php — Destroy Session & Redirect
// ============================================================
require_once '../includes/config.php';
session_unset();
session_destroy();
session_start();
$_SESSION['flash_msg']  = 'You have been logged out successfully.';
$_SESSION['flash_type'] = 'success';
header('Location: ' . SITE_URL . '/index.php');
exit;
?>
