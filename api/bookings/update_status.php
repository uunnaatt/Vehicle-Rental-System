<?php
// api/bookings/update_status.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && !empty($data->status)) {
    $valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    if (!in_array($data->status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid status."));
        exit;
    }

    $query = "UPDATE bookings SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $data->status);
    $stmt->bindParam(':id', $data->id);

    if($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("success" => true, "message" => "Status updated successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to update status."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Incomplete data."));
}
?>
