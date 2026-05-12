<?php
header("Content-Type: application/json; charset=UTF-8");

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

include_once '../../config/database.php';
include_once 'favorite_helpers.php';

$database = new Database();
$db = $database->getConnection();
ensure_favorites_schema($db);

$query = "SELECT v.id, v.name, v.brand, v.model_year, v.seats, v.transmission, v.fuel_type,
                 v.daily_rate, v.image_url, v.status, v.description,
                 c.name as category_name, l.name as location_name, f.created_at as favorited_at
          FROM favorites f
          INNER JOIN vehicles v ON f.vehicle_id = v.id
          LEFT JOIN categories c ON v.category_id = c.id
          LEFT JOIN locations l ON v.location_id = l.id
          WHERE f.user_id = :user_id
          ORDER BY f.created_at DESC";

$stmt = $db->prepare($query);
$stmt->bindValue(':user_id', $_SESSION['user_id']);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($records) {
    echo json_encode(["records" => $records]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No favorite vehicles found."]);
}
?>
