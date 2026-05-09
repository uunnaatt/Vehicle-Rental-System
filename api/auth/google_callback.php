<?php
// api/auth/google_callback.php
session_start();

include_once '../../config/database.php';
include_once 'auth_helpers.php';

$baseUrl = auth_base_url();
$clientId = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');

if (!$clientId || !$clientSecret) {
    header('Location: ' . $baseUrl . '/views/login.php?auth_error=' . urlencode('Google login is not configured yet.'));
    exit;
}

if (empty($_GET['state']) || empty($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $_GET['state'])) {
    header('Location: ' . $baseUrl . '/views/login.php?auth_error=' . urlencode('Google login could not be verified.'));
    exit;
}
unset($_SESSION['google_oauth_state']);

if (empty($_GET['code'])) {
    header('Location: ' . $baseUrl . '/views/login.php?auth_error=' . urlencode('Google did not return an authorization code.'));
    exit;
}

$tokenPayload = [
    'code' => $_GET['code'],
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $baseUrl . '/api/auth/google_callback.php',
    'grant_type' => 'authorization_code'
];

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => http_build_query($tokenPayload),
    CURLOPT_TIMEOUT => 20
]);
$rawToken = curl_exec($ch);
$tokenStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$token = json_decode($rawToken, true);
if ($tokenStatus < 200 || $tokenStatus >= 300 || empty($token['access_token'])) {
    header('Location: ' . $baseUrl . '/views/login.php?auth_error=' . urlencode('Google login failed while requesting tokens.'));
    exit;
}

$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token['access_token']],
    CURLOPT_TIMEOUT => 20
]);
$rawProfile = curl_exec($ch);
$profileStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$profile = json_decode($rawProfile, true);
if ($profileStatus < 200 || $profileStatus >= 300 || empty($profile['sub']) || empty($profile['email'])) {
    header('Location: ' . $baseUrl . '/views/login.php?auth_error=' . urlencode('Google profile could not be loaded.'));
    exit;
}

$database = new Database();
$db = $database->getConnection();
ensure_auth_schema($db);

$user = upsert_google_user($db, $profile['sub'], $profile['email'], $profile['name'] ?? $profile['email']);
start_user_session($user);

header('Location: ' . $baseUrl . '/views/dashboard.php');
exit;
?>
