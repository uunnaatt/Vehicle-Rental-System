<?php
// api/admin/stats.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Vehicle.php';
include_once '../../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$vehicle = new Vehicle($db);
$booking = new Booking($db);

$total_vehicles = $vehicle->get_stats();
$booking_stats = $booking->get_stats();

$stats = array(
    "total_vehicles" => $total_vehicles,
    "total_bookings" => $booking_stats['total_bookings'],
    "total_revenue" => $booking_stats['total_revenue'] ?? 0
);

http_response_code(200);
echo json_encode($stats);
?>
