<?php
include_once __DIR__ . '/config/database.php';
$database = new Database();
$db = $database->getConnection();

$queries = [
    "ALTER TABLE bookings ADD COLUMN collateral_type VARCHAR(50) DEFAULT NULL;",
    "ALTER TABLE bookings ADD COLUMN collateral_image VARCHAR(255) DEFAULT NULL;",
    "ALTER TABLE bookings ADD COLUMN agreement_accepted BOOLEAN DEFAULT 0;",
    "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT 0
    );"
];

foreach ($queries as $query) {
    try {
        $db->exec($query);
        echo "Successfully ran SQL.<br>";
    } catch(PDOException $e) {
        echo "Error or already exists: " . $e->getMessage() . "<br>";
    }
}
?>
