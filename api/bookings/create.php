<?php
// api/bookings/create.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';
include_once '../../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->user_id) && !empty($data->vehicle_id) && !empty($data->pickup_location_id) && 
   !empty($data->dropoff_location_id) && !empty($data->start_date) && !empty($data->end_date) && 
   !empty($data->total_price)) {

    $booking->user_id = $data->user_id;
    $booking->vehicle_id = $data->vehicle_id;
    $booking->pickup_location_id = $data->pickup_location_id;
    $booking->dropoff_location_id = $data->dropoff_location_id;
    $booking->start_date = $data->start_date;
    $booking->end_date = $data->end_date;
    $booking->total_price = $data->total_price;

    if($booking->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Booking was successfully created."));
    } else {
        http_response_code(409); // Conflict
        echo json_encode(array("message" => "Vehicle is not available for the selected dates (Double-booking prevented)."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete booking data."));
}
?>
