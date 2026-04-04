<?php
// models/Message.php
class Message {
    private $conn;
    private $table_name = "messages";

    public $id;
    public $sender_id;
    public $receiver_id;
    public $message;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function send() {
        $query = "INSERT INTO " . $this->table_name . " SET sender_id=:s, receiver_id=:r, message=:m";
        $stmt = $this->conn->prepare($query);
        $this->message = htmlspecialchars(strip_tags($this->message));
        $stmt->bindParam(':s', $this->sender_id);
        $stmt->bindParam(':r', $this->receiver_id);
        $stmt->bindParam(':m', $this->message);
        if ($stmt->execute()) return true;
        return false;
    }

    public function get_chat($contact_id, $admin_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (sender_id = :contact AND receiver_id = :admin) 
                     OR (sender_id = :admin AND receiver_id = :contact) 
                  ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':contact', $contact_id);
        $stmt->bindParam(':admin', $admin_id);
        $stmt->execute();
        return $stmt;
    }
    
    public function get_admin_inbox() {
        $query = "SELECT u.id, u.full_name, MAX(m.created_at) as last_msg_time 
                  FROM " . $this->table_name . " m 
                  JOIN users u ON m.sender_id = u.id OR m.receiver_id = u.id 
                  WHERE u.role = 'user' 
                  GROUP BY u.id 
                  ORDER BY last_msg_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
