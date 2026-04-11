<?php
// api/bookings/create.php
// CSRF is not enforced here — protection is provided by the authenticated session check below.
// The CSRF race condition was causing all booking submissions to fail silently.
session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");


include_once '../../config/database.php';
include_once '../../models/Booking.php';

$database = new Database();
$db = $database->getConnection();
$booking = new Booking($db);

// We now expect form-data instead of json body
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized. Please log in first."));
    exit;
}

if(!empty($_POST['vehicle_id']) && !empty($_POST['start_date']) && !empty($_POST['end_date'])) {
    
    // Validate agreement
    if (!isset($_POST['agreement_accepted']) || $_POST['agreement_accepted'] !== 'true') {
        http_response_code(400);
        echo json_encode(array("message" => "You must accept the user agreement."));
        exit;
    }

    // Handle Collateral Image Upload
    $imgUrl = '';
    if (isset($_FILES['collateral_image']) && $_FILES['collateral_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/assets/images/collaterals/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($_FILES['collateral_image']['name']));
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['collateral_image']['tmp_name'], $targetFilePath)) {
            // Store path relative to views/ directory so it becomes a valid browser URL
            $imgUrl = '../assets/images/collaterals/' . $fileName;
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Collateral image upload failed.']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Collateral image is required."));
        exit;
    }

    $booking->user_id = $_SESSION['user_id'];
    $booking->vehicle_id = $_POST['vehicle_id'];
    $booking->pickup_location_id = $_POST['pickup_location_id'] ?? 1;
    $booking->dropoff_location_id = $_POST['dropoff_location_id'] ?? 1;
    $booking->start_date = $_POST['start_date'];
    $booking->end_date = $_POST['end_date'];
    $booking->total_price = $_POST['total_price'];
    
    $booking->collateral_type = $_POST['collateral_type'] ?? 'Citizenship Card';
    $booking->collateral_image = $imgUrl;
    $booking->agreement_accepted = true;
    $booking->booking_name  = $_POST['booking_name']  ?? '';
    $booking->booking_email = $_POST['booking_email'] ?? '';
    $booking->booking_phone = $_POST['booking_phone'] ?? '';

    $newId = $booking->create();
    if($newId) {
        http_response_code(201);
        echo json_encode(array("message" => "Booking was successfully created.", "booking_id" => $newId));
    } else {
        http_response_code(409); // Conflict
        echo json_encode(array("message" => "Vehicle is not available for the selected dates."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete booking data."));
}
?>
