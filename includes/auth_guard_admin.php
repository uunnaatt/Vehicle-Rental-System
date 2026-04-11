<?php
/**
 * auth_guard_admin.php — include at the TOP of every admin-only page.
 * Redirects to login if the user is not authenticated as admin.
 */
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    $loginUrl = $scheme . '://' . $host . '/VehicleRental/views/login.php';
    header('Location: ' . $loginUrl, true, 302);
    ob_end_clean();
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
