<?php
// api/payments/process.php — Dummy Payment Processor
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';
include_once '../../models/Payment.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Please log in."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$required = ['booking_id', 'card_name', 'card_number', 'card_expiry', 'card_cvv', 'amount'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required field: $field"]);
        exit;
    }
}

// Validate card number (16 digits after stripping spaces)
$cardNumber = preg_replace('/\s/', '', $data['card_number']);
if (!preg_match('/^\d{16}$/', $cardNumber)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid card number. Must be 16 digits."]);
    exit;
}

// Validate expiry MM/YY
if (!preg_match('/^\d{2}\/\d{2}$/', $data['card_expiry'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid expiry date format. Use MM/YY."]);
    exit;
}

// Validate CVV (3 digits)
if (!preg_match('/^\d{3}$/', $data['card_cvv'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid CVV. Must be 3 digits."]);
    exit;
}

// Validate expiry is not in the past
list($expMonth, $expYear) = explode('/', $data['card_expiry']);
$expYear = '20' . $expYear;
if ((int)$expYear < (int)date('Y') || ((int)$expYear === (int)date('Y') && (int)$expMonth < (int)date('m'))) {
    http_response_code(400);
    echo json_encode(["message" => "Card has expired. Please use a valid card."]);
    exit;
}

// Connect to DB
$database = new Database();
$db = $database->getConnection();
$payment = new Payment($db);

// Verify booking belongs to this user
$stmt = $db->prepare("SELECT id, status FROM bookings WHERE id = :id AND user_id = :uid LIMIT 1");
$stmt->bindParam(':id', $data['booking_id']);
$stmt->bindParam(':uid', $_SESSION['user_id']);
$stmt->execute();
$bookingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bookingRecord) {
    http_response_code(400);
    echo json_encode(["message" => "Booking not found or does not belong to the user."]);
    exit;
}

// Calculate amounts
$amount     = floatval($data['amount']);
$serviceFee = round($amount * 0.10, 2);   // 10% service fee
$total      = $amount + $serviceFee;

// Generate unique transaction ID
$txnId = 'TXN-' . strtoupper(bin2hex(random_bytes(8)));

// Card last 4
$card_last4 = substr($cardNumber, -4);

// ---- SIMULATE PAYMENT (95% success, 5% failure for realism) ----
$paymentSuccess = (rand(1, 100) <= 95);
// Uncomment line below to ALWAYS succeed:
// $paymentSuccess = true;

$status = $paymentSuccess ? 'success' : 'failed';

// Save payment record
$payment->booking_id      = intval($data['booking_id']);
$payment->user_id         = $_SESSION['user_id'];
$payment->amount          = $amount;
$payment->service_fee     = $serviceFee;
$payment->total_amount    = $total;
$payment->transaction_id  = $txnId;
$payment->payment_method  = 'card';
$payment->card_last4      = $card_last4;
$payment->status          = $status;

$payment->create();

// If payment succeeded, update booking status
if ($paymentSuccess) {
    $stmt = $db->prepare("UPDATE bookings SET payment_status='paid', status='confirmed' WHERE id=:id AND user_id=:uid");
    $stmt->bindParam(':id',  $data['booking_id']);
    $stmt->bindParam(':uid', $_SESSION['user_id']);
    $stmt->execute();

    http_response_code(200);
    echo json_encode([
        "success"        => true,
        "transaction_id" => $txnId,
        "amount"         => $amount,
        "service_fee"    => $serviceFee,
        "total_amount"   => $total,
        "card_last4"     => $card_last4,
        "message"        => "Payment successful!"
    ]);
} else {
    http_response_code(402);
    echo json_encode([
        "success" => false,
        "message" => "Payment declined. Please check your card details and try again."
    ]);
}
?>
