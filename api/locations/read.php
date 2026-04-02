<?php
// api/locations/read.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Location.php';

$database = new Database();
$db = $database->getConnection();

$location = new Location($db);
$stmt = $location->read();
$num = $stmt->rowCount();

if($num > 0) {
    $loc_arr = array();
    $loc_arr["records"] = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        array_push($loc_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($loc_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No locations found."));
}
?>
