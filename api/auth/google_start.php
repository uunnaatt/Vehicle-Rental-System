<?php
// api/auth/google_start.php
session_start();

$clientId = getenv('GOOGLE_CLIENT_ID');
$baseUrl = '';

include_once 'auth_helpers.php';
$baseUrl = auth_base_url();

if (!$clientId) {
    header('Location: ' . $baseUrl . '/views/login.php?auth_error=' . urlencode('Google login is not configured yet. Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.'));
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id' => $clientId,
    'redirect_uri' => $baseUrl . '/api/auth/google_callback.php',
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'prompt' => 'select_account'
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
?>
