<?php
// api/vehicles/delete.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/Database.php';
include_once '../../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();
$vehicle = new Vehicle($db);

$data = json_decode(file_get_contents("php://input"));
$vehicle->id = $data->id ?? null;

if (!$vehicle->id) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID is required.']);
    exit;
}

if ($vehicle->delete()) {
    echo json_encode(['success' => true, 'message' => 'Vehicle deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete vehicle.']);
}
?>
