<?php
session_start();
session_write_close();
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Login required."]);
    exit;
}

include_once '../../config/database.php';

function getBaseCoords($locationName) {
    $coords = [
        'kathmandu' => ['lat' => 27.700769, 'lng' => 85.300140],
        'pokhara' => ['lat' => 28.209583, 'lng' => 83.985567],
        'chitwan' => ['lat' => 27.576939, 'lng' => 84.503008],
        'bhaktapur' => ['lat' => 27.671022, 'lng' => 85.429817],
        'lalitpur' => ['lat' => 27.664402, 'lng' => 85.318791],
        'biratnagar' => ['lat' => 26.452474, 'lng' => 87.271782]
    ];

    $normalized = strtolower((string)$locationName);
    foreach ($coords as $key => $point) {
        if (strpos($normalized, $key) !== false) {
            return $point;
        }
    }

    return ['lat' => 27.700769, 'lng' => 85.300140];
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT b.id, b.start_date, b.end_date, b.status, b.total_price,
                     v.id AS vehicle_id, v.name AS vehicle_name,
                     p.name AS pickup_location
              FROM bookings b
              LEFT JOIN vehicles v ON b.vehicle_id = v.id
              LEFT JOIN locations p ON b.pickup_location_id = p.id
              WHERE b.user_id = :user_id
                AND b.status IN ('pending', 'confirmed')
                AND b.end_date >= CURDATE()
              ORDER BY b.created_at DESC
              LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['message' => 'No active rental found.']);
        exit;
    }

    $base = getBaseCoords($row['pickup_location'] ?? '');
    $tick = floor(time() / 15);
    $seed = ((int)$row['id'] + $tick) % 20;
    $offset = ($seed - 10) * 0.0002;

    echo json_encode([
        'booking_id' => (int)$row['id'],
        'vehicle_id' => (int)$row['vehicle_id'],
        'vehicle_name' => $row['vehicle_name'],
        'booked_date' => $row['start_date'],
        'return_date' => $row['end_date'],
        'booking_status' => $row['status'],
        'payment_status' => $row['status'] === 'confirmed' ? 'paid' : 'unpaid',
        'location_label' => $row['pickup_location'] ?: 'Unknown location',
        'lat' => round($base['lat'] + $offset, 6),
        'lng' => round($base['lng'] - $offset, 6),
        'last_updated' => date('Y-m-d H:i:s')
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to load active tracking.']);
}
?>
