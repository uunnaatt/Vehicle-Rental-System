<?php
// api/payments/all.php — Admin: fetch all payments
session_start();
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Payment.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(["message" => "Admin access required."]);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$payment = new Payment($db);

$stmt = $payment->get_all();
$rows = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = $row;
}

echo json_encode(["records" => $rows]);
?>
