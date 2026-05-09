<?php
// api/auth/forgot_password.php
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once 'auth_helpers.php';

function respond($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['phone_or_email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, ["message" => "Please enter the email address on your account."]);
}

$database = new Database();
$db = $database->getConnection();
ensure_auth_schema($db);

$user = find_user_by_email($db, $email);
$message = "If an account exists for that email, a reset link has been prepared.";

if (!$user) {
    respond(200, ["message" => $message]);
}

$token = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $token);

$stmt = $db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
$stmt->bindValue(':user_id', $user['id']);
$stmt->bindValue(':token_hash', $tokenHash);
$stmt->execute();

$resetLink = auth_base_url() . '/views/reset-password.php?token=' . urlencode($token);

respond(200, [
    "message" => $message,
    "reset_link" => $resetLink,
    "dev_note" => "Email sending is not configured, so this local project returns the reset link here."
]);
?>
