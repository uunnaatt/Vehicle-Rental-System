<?php
// api/reviews/create.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->vehicle_id) && !empty($data->rating)) {
    $query = "INSERT INTO reviews (user_id, vehicle_id, rating, comment) 
              VALUES (:user_id, :vehicle_id, :rating, :comment)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':vehicle_id', $data->vehicle_id);
    $stmt->bindParam(':rating', $data->rating);
    $comment = !empty($data->comment) ? htmlspecialchars(strip_tags($data->comment)) : '';
    $stmt->bindParam(':comment', $comment);

    if($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Review submitted successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Failed to submit review."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required fields: user_id, vehicle_id, rating."));
}
?>
