<?php
// api/payments/verify-session.php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

if (empty($_GET['session_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Session ID is required."]);
    exit;
}

$sessionId = $_GET['session_id'];

include_once '../../config/database.php';
include_once '../../models/Booking.php';
include_once '../../includes/StripeHelper.php';

$stripeHelper = new StripeHelper();
$response = $stripeHelper->retrieveSession($sessionId);

if ($response['status'] !== 200 || empty($response['body']['id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid session.", "status" => "failed"]);
    exit;
}

$sessionData = $response['body'];
$paymentStatus = $sessionData['payment_status']; // 'paid', 'unpaid', 'no_payment_required'
$clientReferenceId = $sessionData['client_reference_id']; // This is our booking ID

if ($paymentStatus === 'paid' && !empty($clientReferenceId)) {
    $database = new Database();
    $db = $database->getConnection();
    $booking = new Booking($db);
    $booking->id = $clientReferenceId;

    if ($booking->read_single()) {
        if ($booking->payment_status !== 'paid') {
            $booking->payment_status = 'paid';
            $booking->stripe_session_id = $sessionId;
            $booking->status = 'confirmed';
            $booking->updatePaymentStatus();
        }
        
        http_response_code(200);
        echo json_encode([
            "message" => "Payment verified successfully.",
            "status" => "success",
            "booking_id" => $booking->id,
            "trx_id" => $sessionData['payment_intent'] ?? $sessionId,
            "amount" => $sessionData['amount_total'] / 100
        ]);
        exit;
    }
}

http_response_code(400);
echo json_encode(["message" => "Payment not completed or booking not found.", "status" => "failed"]);
?>
