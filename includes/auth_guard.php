<?php
/**
 * auth_guard.php — include at the TOP of every user-protected page.
 * Redirects to login if the user is not authenticated.
 */
ob_start(); // Buffer output so header() always works (output_buffering=0 fix)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    // Build an absolute URL so the redirect works regardless of relative path issues
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    // Walk up from /views/ to find the app root — assumes files are in /views/
    $loginUrl = $scheme . '://' . $host . '/VehicleRental/views/login.php';
    header('Location: ' . $loginUrl, true, 302);
    ob_end_clean();
    exit;
}

// Generate CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
