<?php
// api/auth/reset_password.php
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once 'auth_helpers.php';

function respond($status, $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$token = trim($data['token'] ?? '');
$password = (string)($data['password'] ?? '');

if (!$token || strlen($password) < 6) {
    respond(400, ["message" => "Please provide a valid reset link and a password with at least 6 characters."]);
}

$database = new Database();
$db = $database->getConnection();
ensure_auth_schema($db);

$tokenHash = hash('sha256', $token);
$stmt = $db->prepare("SELECT id, user_id FROM password_resets
                      WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW()
                      ORDER BY id DESC LIMIT 1");
$stmt->bindValue(':token_hash', $tokenHash);
$stmt->execute();
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    respond(400, ["message" => "This reset link is invalid or expired."]);
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$db->beginTransaction();
try {
    $updateUser = $db->prepare("UPDATE users SET password = :password WHERE id = :user_id");
    $updateUser->bindValue(':password', $hashedPassword);
    $updateUser->bindValue(':user_id', $reset['user_id']);
    $updateUser->execute();

    $updateReset = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id");
    $updateReset->bindValue(':id', $reset['id']);
    $updateReset->execute();

    $db->commit();
} catch (PDOException $e) {
    $db->rollBack();
    respond(500, ["message" => "Unable to reset password right now."]);
}

respond(200, ["message" => "Password reset successful. You can log in now."]);
?>
