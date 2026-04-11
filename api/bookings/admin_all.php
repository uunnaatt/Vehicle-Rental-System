<?php
// api/bookings/admin_all.php
session_start();
session_write_close(); // read-only — release lock immediately
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Admin access required."]);
    exit;
}

include_once '../../config/database.php';
include_once '../../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$stmt = $booking->read_all();
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
    echo json_encode(array("message" => "No bookings found."));
}
?>
