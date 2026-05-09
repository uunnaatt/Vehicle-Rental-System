<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

include_once '../../config/database.php';
include_once 'favorite_helpers.php';

$data = json_decode(file_get_contents("php://input"), true);
$vehicleId = (int)($data['vehicle_id'] ?? 0);

if (!$vehicleId) {
    http_response_code(400);
    echo json_encode(["message" => "Vehicle ID is required."]);
    exit;
}

$database = new Database();
$db = $database->getConnection();
ensure_favorites_schema($db);

$check = $db->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND vehicle_id = :vehicle_id LIMIT 1");
$check->bindValue(':user_id', $_SESSION['user_id']);
$check->bindValue(':vehicle_id', $vehicleId);
$check->execute();
$existing = $check->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $delete = $db->prepare("DELETE FROM favorites WHERE id = :id");
    $delete->bindValue(':id', $existing['id']);
    $delete->execute();
    echo json_encode(["favorited" => false, "message" => "Removed from favorites."]);
    exit;
}

$insert = $db->prepare("INSERT INTO favorites (user_id, vehicle_id) VALUES (:user_id, :vehicle_id)");
$insert->bindValue(':user_id', $_SESSION['user_id']);
$insert->bindValue(':vehicle_id', $vehicleId);
$insert->execute();

echo json_encode(["favorited" => true, "message" => "Added to favorites."]);
?>
