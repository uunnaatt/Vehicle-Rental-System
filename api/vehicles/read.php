<?php
// api/vehicles/read.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();

$vehicle = new Vehicle($db);

$search = isset($_GET['search']) ? $_GET['search'] : "";
$category = isset($_GET['category']) ? $_GET['category'] : "";

$stmt = $vehicle->read($search, $category);
$num = $stmt->rowCount();

if($num > 0) {
    $vehicles_arr = array();
    $vehicles_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        array_push($vehicles_arr["records"], $row);
    }

    http_response_code(200);
    echo json_encode($vehicles_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No vehicles found."));
}
?>
