<?php
include_once __DIR__ . '/config/database.php';
$database = new Database();
$db = $database->getConnection();

$queries = [
    "ALTER TABLE bookings ADD COLUMN collateral_type VARCHAR(50) DEFAULT NULL;",
    "ALTER TABLE bookings ADD COLUMN collateral_image VARCHAR(255) DEFAULT NULL;",
    "ALTER TABLE bookings ADD COLUMN agreement_accepted BOOLEAN DEFAULT 0;",
    // New columns to store booking form contact info
    "ALTER TABLE bookings ADD COLUMN booking_name VARCHAR(100) DEFAULT NULL;",
    "ALTER TABLE bookings ADD COLUMN booking_email VARCHAR(100) DEFAULT NULL;",
    "ALTER TABLE bookings ADD COLUMN booking_phone VARCHAR(30) DEFAULT NULL;",
    "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT 0
    );",
    "ALTER TABLE users ADD COLUMN google_id VARCHAR(120) DEFAULT NULL;",
    "ALTER TABLE users ADD UNIQUE INDEX idx_users_google_id (google_id);",
    "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_password_resets_token_hash (token_hash),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );",
    "CREATE TABLE IF NOT EXISTS favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        vehicle_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_vehicle (user_id, vehicle_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
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
