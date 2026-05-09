<?php
header("Content-Type: application/json; charset=UTF-8");

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(200);
    echo json_encode(["favorited" => false]);
    exit;
}

include_once '../../config/database.php';
include_once 'favorite_helpers.php';

$vehicleId = (int)($_GET['vehicle_id'] ?? 0);
if (!$vehicleId) {
    http_response_code(400);
    echo json_encode(["message" => "Vehicle ID is required."]);
    exit;
}

$database = new Database();
$db = $database->getConnection();
ensure_favorites_schema($db);

$stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND vehicle_id = :vehicle_id LIMIT 1");
$stmt->bindValue(':user_id', $_SESSION['user_id']);
$stmt->bindValue(':vehicle_id', $vehicleId);
$stmt->execute();

echo json_encode(["favorited" => (bool)$stmt->fetch(PDO::FETCH_ASSOC)]);
?>
