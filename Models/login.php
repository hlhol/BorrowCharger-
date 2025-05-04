<?php
require_once('Models/Database.php');

class Login {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function auth($identifier, $password) {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $query = "SELECT user_id, username, email, password, role FROM Users WHERE email = ?";
        } else {
            $query = "SELECT user_id, username, email, password, role FROM Users WHERE username = ?";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $identifier); 
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
