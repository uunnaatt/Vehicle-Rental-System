<?php
// api/vehicles/update.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/Database.php';
include_once '../../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();
$vehicle = new Vehicle($db);

$vehicle->id = $_POST['id'] ?? null;

if (!$vehicle->id) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID is required.']);
    exit;
}

// Check if we need to update the image
$imgUrl = $_POST['existing_image_url'] ?? '';

if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../../assets/images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = time() . '_' . basename($_FILES["image_upload"]["name"]);
    $targetFilePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $targetFilePath)) {
        $imgUrl = '../assets/images/' . $fileName;
    }
} elseif (!empty($_POST['image_url'])) {
    $imgUrl = $_POST['image_url'];
}

$vehicle->name = $_POST['name'] ?? '';
$vehicle->brand = $_POST['brand'] ?? '';
$vehicle->model_year = $_POST['model_year'] ?? date('Y');
$vehicle->category_id = $_POST['category_id'] ?? null;
$vehicle->location_id = $_POST['location_id'] ?? null;
$vehicle->seats = $_POST['seats'] ?? 4;
$vehicle->transmission = $_POST['transmission'] ?? 'Manual';
$vehicle->fuel_type = $_POST['fuel_type'] ?? 'Petrol';
$vehicle->daily_rate = $_POST['daily_rate'] ?? 0;
$vehicle->status = $_POST['status'] ?? 'Available';
$vehicle->description = $_POST['description'] ?? '';
$vehicle->image_url = $imgUrl;

if ($vehicle->update()) {
    echo json_encode(['success' => true, 'message' => 'Vehicle updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update vehicle.']);
}
?>
