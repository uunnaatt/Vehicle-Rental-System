<?php
// models/Location.php
class Location {
    private $conn;
    private $table_name = "locations";

    public $id;
    public $name;
    public $address;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read locations
    public function read() {
        $query = "SELECT id, name, address FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
