<?php
// api/reviews/read.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database_file = '../../config/database.php';
include_once $database_file;

$database = new Database();
$db = $database->getConnection();

$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

if($vehicle_id) {
    $query = "SELECT r.id, r.rating, r.comment, r.created_at, u.full_name as reviewer_name
              FROM reviews r
              LEFT JOIN users u ON r.user_id = u.id
              WHERE r.vehicle_id = :vehicle_id
              ORDER BY r.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':vehicle_id', $vehicle_id);
} else {
    $query = "SELECT r.id, r.rating, r.comment, r.created_at, u.full_name as reviewer_name, v.name as vehicle_name
              FROM reviews r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN vehicles v ON r.vehicle_id = v.id
              ORDER BY r.created_at DESC";
    $stmt = $db->prepare($query);
}

$stmt->execute();
$num = $stmt->rowCount();

if($num > 0) {
    $reviews_arr = array();
    $reviews_arr["records"] = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        array_push($reviews_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($reviews_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No reviews found."));
}
?>
