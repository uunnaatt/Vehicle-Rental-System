<?php
// models/Vehicle.php
class Vehicle {
    private $conn;
    private $table_name = "vehicles";

    public $id;
    public $name;
    public $brand;
    public $model_year;
    public $category_id;
    public $category_name;
    public $location_id;
    public $location_name;
    public $seats;
    public $transmission;
    public $fuel_type;
    public $daily_rate;
    public $image_url;
    public $status;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all vehicles
    public function read($search = "", $category = "") {
        $query = "SELECT v.id, v.name, v.brand, v.model_year, v.seats, v.transmission, v.fuel_type, v.daily_rate, v.image_url, v.status, v.description,
                         c.name as category_name, l.name as location_name 
                  FROM " . $this->table_name . " v
                  LEFT JOIN categories c ON v.category_id = c.id
                  LEFT JOIN locations l ON v.location_id = l.id
                  WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (v.name LIKE :search OR v.brand LIKE :search)";
        }
        if (!empty($category)) {
            $query .= " AND c.name = :category";
        }
        $query .= " ORDER BY v.created_at DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(':search', $search_param);
        }
        if (!empty($category)) {
            $stmt->bindParam(':category', $category);
        }

        $stmt->execute();
        return $stmt;
    }

    // Get single vehicle
    public function read_single() {
        $query = "SELECT v.id, v.name, v.brand, v.model_year, v.seats, v.transmission, v.fuel_type, v.daily_rate, v.image_url, v.status, v.description,
                         c.name as category_name, l.name as location_name 
                  FROM " . $this->table_name . " v
                  LEFT JOIN categories c ON v.category_id = c.id
                  LEFT JOIN locations l ON v.location_id = l.id 
                  WHERE v.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->name = $row['name'];
            $this->brand = $row['brand'];
            $this->model_year = $row['model_year'];
            $this->seats = $row['seats'];
            $this->transmission = $row['transmission'];
            $this->fuel_type = $row['fuel_type'];
            $this->daily_rate = $row['daily_rate'];
            $this->image_url = $row['image_url'];
            $this->status = $row['status'];
            $this->category_name = $row['category_name'];
            $this->location_name = $row['location_name'];
            return true;
        }
        return false;
    }

    // Get statistics
    public function get_stats() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Create Vehicle
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET name=:name, brand=:brand, model_year=:model_year, category_id=:category_id,
                      location_id=:location_id, seats=:seats, transmission=:transmission,
                      fuel_type=:fuel_type, daily_rate=:daily_rate, image_url=:image_url,
                      status=:status, description=:description";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':brand', $this->brand);
        $stmt->bindParam(':model_year', $this->model_year);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':location_id', $this->location_id);
        $stmt->bindParam(':seats', $this->seats);
        $stmt->bindParam(':transmission', $this->transmission);
        $stmt->bindParam(':fuel_type', $this->fuel_type);
        $stmt->bindParam(':daily_rate', $this->daily_rate);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':description', $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update Vehicle
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET name=:name, brand=:brand, model_year=:model_year, category_id=:category_id,
                      location_id=:location_id, seats=:seats, transmission=:transmission,
                      fuel_type=:fuel_type, daily_rate=:daily_rate, image_url=:image_url,
                      status=:status, description=:description
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->brand = htmlspecialchars(strip_tags($this->brand));

        // bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':brand', $this->brand);
        $stmt->bindParam(':model_year', $this->model_year);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':location_id', $this->location_id);
        $stmt->bindParam(':seats', $this->seats);
        $stmt->bindParam(':transmission', $this->transmission);
        $stmt->bindParam(':fuel_type', $this->fuel_type);
        $stmt->bindParam(':daily_rate', $this->daily_rate);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete Vehicle
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
