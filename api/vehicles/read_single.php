<?php
// api/vehicles/read_single.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();

$vehicle = new Vehicle($db);
$vehicle->id = isset($_GET['id']) ? $_GET['id'] : die();

if($vehicle->read_single()) {
    $vehicle_arr = array(
        "id" =>  $vehicle->id,
        "name" => $vehicle->name,
        "brand" => $vehicle->brand,
        "model_year" => $vehicle->model_year,
        "category_name" => $vehicle->category_name,
        "location_name" => $vehicle->location_name,
        "seats" => $vehicle->seats,
        "transmission" => $vehicle->transmission,
        "fuel_type" => $vehicle->fuel_type,
        "daily_rate" => $vehicle->daily_rate,
        "image_url" => $vehicle->image_url,
        "status" => $vehicle->status
    );

    http_response_code(200);
    echo json_encode($vehicle_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Vehicle does not exist."));
}
?>
