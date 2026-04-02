<?php
// models/User.php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $full_name;
    public $phone_or_email;
    public $password;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register a new user
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " SET full_name=:full_name, phone_or_email=:phone_or_email, password=:password";

        $stmt = $this->conn->prepare($query);

        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone_or_email = htmlspecialchars(strip_tags($this->phone_or_email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone_or_email", $this->phone_or_email);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login a user
    public function login() {
        $query = "SELECT id, full_name, password, role FROM " . $this->table_name . " WHERE phone_or_email = :phone_or_email LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->phone_or_email = htmlspecialchars(strip_tags($this->phone_or_email));
        $stmt->bindParam(":phone_or_email", $this->phone_or_email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            $hashed_password = $row['password'];

            if(password_verify($this->password, $hashed_password)) {
                return true;
            }
        }
        return false;
    }

    // Check if user exists
    public function userExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE phone_or_email = :phone_or_email LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $this->phone_or_email = htmlspecialchars(strip_tags($this->phone_or_email));
        $stmt->bindParam(":phone_or_email", $this->phone_or_email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
?>
