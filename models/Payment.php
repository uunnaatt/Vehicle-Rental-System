<?php
// models/Payment.php
class Payment {
    private $conn;
    private $table = "payments";

    public $booking_id;
    public $user_id;
    public $amount;
    public $service_fee;
    public $total_amount;
    public $transaction_id;
    public $payment_method = 'card';
    public $card_last4;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a payment record
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  SET booking_id=:booking_id, user_id=:user_id,
                      amount=:amount, service_fee=:service_fee, total_amount=:total_amount,
                      transaction_id=:transaction_id, payment_method=:payment_method,
                      card_last4=:card_last4, status=:status";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':booking_id',      $this->booking_id);
        $stmt->bindParam(':user_id',         $this->user_id);
        $stmt->bindParam(':amount',          $this->amount);
        $stmt->bindParam(':service_fee',     $this->service_fee);
        $stmt->bindParam(':total_amount',    $this->total_amount);
        $stmt->bindParam(':transaction_id',  $this->transaction_id);
        $stmt->bindParam(':payment_method',  $this->payment_method);
        $stmt->bindParam(':card_last4',      $this->card_last4);
        $stmt->bindParam(':status',          $this->status);
        return $stmt->execute();
    }

    // Get a payment by transaction_id
    public function get_by_txn($txn_id) {
        $query = "SELECT p.*, b.start_date, b.end_date, v.name as vehicle_name,
                         u.full_name as customer_name
                  FROM " . $this->table . " p
                  LEFT JOIN bookings b ON p.booking_id = b.id
                  LEFT JOIN vehicles v ON b.vehicle_id = v.id
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.transaction_id = :txn_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':txn_id', $txn_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all payments (admin)
    public function get_all() {
        $query = "SELECT p.*, b.start_date, b.end_date,
                         v.name as vehicle_name,
                         u.full_name as customer_name
                  FROM " . $this->table . " p
                  LEFT JOIN bookings b ON p.booking_id = b.id
                  LEFT JOIN vehicles v ON b.vehicle_id = v.id
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.paid_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get payment stats
    public function get_stats() {
        $query = "SELECT COUNT(*) as total_payments,
                         SUM(CASE WHEN status='success' THEN total_amount ELSE 0 END) as total_collected
                  FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
