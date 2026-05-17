<?php
// api/payments/create-checkout-session.php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Read raw POST data (from fetch API JSON body)
$data = json_decode(file_get_contents("php://input"));

if (empty($data->booking_id)) {
    http_response_code(400);
    echo json_encode(["message" => "Booking ID is required."]);
    exit;
}

$bookingId = $data->booking_id;

include_once '../../config/database.php';
include_once '../../models/Booking.php';
include_once '../../includes/StripeHelper.php';

$database = new Database();
$db = $database->getConnection();
$booking = new Booking($db);
$booking->id = $bookingId;

if (!$booking->read_single()) {
    http_response_code(404);
    echo json_encode(["message" => "Booking not found."]);
    exit;
}

// Calculate total with service fee
$amount = floatval($booking->total_price);
$serviceFee = round($amount * 0.1);
$totalAmount = $amount + $serviceFee;

// Stripe amount is in cents
$stripeAmount = intval($totalAmount * 100);

// Application base URL (for success/cancel redirects)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
// Assuming Vehicle-Rental-System is the base path
$baseUrl = $protocol . "://" . $host . "/Vehicle-Rental-System";

$successUrl = $baseUrl . "/views/payment-success.php?session_id={CHECKOUT_SESSION_ID}&car=" . urlencode(isset($data->car_slug) ? $data->car_slug : 'car');
$cancelUrl = $baseUrl . "/views/payment-confirm.php?vehicle_id=" . $booking->vehicle_id . "&cancel=true";

$stripeHelper = new StripeHelper();
$response = $stripeHelper->createCheckoutSession(
    $stripeAmount,
    'usd', // Using USD as it is universally supported in Stripe Sandbox
    "Booking: " . ($booking->vehicle_name ?? "Vehicle Rental"),
    $successUrl,
    $cancelUrl,
    $booking->id
);

if ($response['status'] === 200 && isset($response['body']['url'])) {
    http_response_code(200);
    echo json_encode([
        "checkout_url" => $response['body']['url']
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to create checkout session.",
        "error" => $response['body']
    ]);
}
?>
