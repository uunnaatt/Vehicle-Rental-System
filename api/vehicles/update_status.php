<?php
// api/vehicles/update_status.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();

$vehicle = new Vehicle($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->status)) {
    // We cannot use the standard model update since we just need to update status
    // Add raw query here for simplicity or extend Vehicle model
    $query = "UPDATE " . $vehicle->table_name . " SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $status = htmlspecialchars(strip_tags($data->status));
    $id = htmlspecialchars(strip_tags($data->id));
    
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("success" => true, "message" => "Vehicle status updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Unable to update status."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Incomplete data."));
}
?>
