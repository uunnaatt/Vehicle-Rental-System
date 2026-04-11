<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';
include_once '../../includes/auth_guard_admin.php'; // ensure only admin can read user info

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["message" => "Missing user ID."]);
    exit;
}

$query = "SELECT id, full_name, phone_or_email, role, created_at FROM users WHERE id = :id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    http_response_code(200);
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["message" => "User not found."]);
}
?>
