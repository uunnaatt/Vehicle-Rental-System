<?php
// api/payments/get.php — Fetch payment details by transaction ID
session_start();
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Payment.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized."]);
    exit;
}

$txnId = $_GET['txn_id'] ?? '';
if (empty($txnId)) {
    http_response_code(400);
    echo json_encode(["message" => "Transaction ID is required."]);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$payment = new Payment($db);

$record = $payment->get_by_txn($txnId);

if ($record) {
    http_response_code(200);
    echo json_encode($record);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Payment record not found."]);
}
?>
