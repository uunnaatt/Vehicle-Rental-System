<?php
// models/Booking.php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $user_id;
    public $vehicle_id;
    public $pickup_location_id;
    public $dropoff_location_id;
    public $start_date;
    public $end_date;
    public $total_price;
    public $status;
    public $collateral_type;
    public $collateral_image;
    public $agreement_accepted;
    public $payment_status;
    public $stripe_session_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Check if double-booking exists
    public function is_available() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE vehicle_id = :vehicle_id 
                  AND status != 'cancelled'
                  AND status != 'completed'
                  AND (start_date <= :end_date AND end_date >= :start_date)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vehicle_id', $this->vehicle_id);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return false; // Not available
        }
        return true; // Available
    }

    // Create booking
    public function create() {
        if(!$this->is_available()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, vehicle_id=:vehicle_id, pickup_location_id=:pickup_location_id, 
                      dropoff_location_id=:dropoff_location_id, start_date=:start_date, 
                      end_date=:end_date, total_price=:total_price, status=:status, 
                      collateral_type=:collateral_type, collateral_image=:collateral_image, 
                      agreement_accepted=:agreement_accepted, payment_status=:payment_status";

        $stmt = $this->conn->prepare($query);

        $this->status = "pending";
        $this->payment_status = "unpaid";

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':vehicle_id', $this->vehicle_id);
        $stmt->bindParam(':pickup_location_id', $this->pickup_location_id);
        $stmt->bindParam(':dropoff_location_id', $this->dropoff_location_id);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':total_price', $this->total_price);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':collateral_type', $this->collateral_type);
        $stmt->bindParam(':collateral_image', $this->collateral_image);
        $stmt->bindParam(':payment_status', $this->payment_status);
        
        $agreement = $this->agreement_accepted ? 1 : 0;
        $stmt->bindParam(':agreement_accepted', $agreement, PDO::PARAM_INT);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function read_single() {
        $query = "SELECT b.*, v.name as vehicle_name FROM " . $this->table_name . " b
                  LEFT JOIN vehicles v ON b.vehicle_id = v.id
                  WHERE b.id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->total_price = $row['total_price'];
            $this->payment_status = $row['payment_status'];
            $this->stripe_session_id = $row['stripe_session_id'];
            // add vehicle name as a dynamic property for convenience
            $this->vehicle_name = $row['vehicle_name'];
            return true;
        }
        return false;
    }

    public function updatePaymentStatus() {
        $query = "UPDATE " . $this->table_name . " SET payment_status = :payment_status, stripe_session_id = :stripe_session_id, status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':payment_status', $this->payment_status);
        $stmt->bindParam(':stripe_session_id', $this->stripe_session_id);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Read user bookings
    public function read_user_bookings() {
        $query = "SELECT b.id, b.start_date, b.end_date, b.total_price, b.status, 
                         v.name as vehicle_name, v.image_url as vehicle_image
                  FROM " . $this->table_name . " b
                  LEFT JOIN vehicles v ON b.vehicle_id = v.id
                  WHERE b.user_id = :user_id
                  ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    // Read all bookings
    public function read_all() {
        $query = "SELECT b.id, b.start_date, b.end_date, b.total_price, b.status, b.collateral_type, b.collateral_image, b.agreement_accepted,
                         u.full_name as user_name, v.name as vehicle_name
                  FROM " . $this->table_name . " b
                  LEFT JOIN users u ON b.user_id = u.id
                  LEFT JOIN vehicles v ON b.vehicle_id = v.id
                  ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function get_stats() {
        $query = "SELECT COUNT(*) as total_bookings, SUM(total_price) as total_revenue FROM " . $this->table_name . " WHERE status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
