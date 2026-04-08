<?php
// api/bookings/user_bookings.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$booking = new Booking($db);
$booking->user_id = $_SESSION['user_id'];

$stmt = $booking->read_user_bookings();
$num = $stmt->rowCount();

if($num > 0) {
    $bookings_arr = array();
    $bookings_arr["records"] = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        array_push($bookings_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($bookings_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No bookings found for this user."));
}
?>
