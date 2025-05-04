<?php

require_once 'Models/Database.php';
require_once 'Models/User.php';

class UserData {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function getById(int $userId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getIdByName(string $username): ?int {
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['user_id'] : null;
    }

    public function isHomeowner(int $userId): bool {
        $user = $this->getById($userId);
        return $user && $user['role'] === 'Homeowner';
    }

    public function countChargePoints(int $userId): int {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM charge_points WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
    
     public function countAllUsers(): int {
    $stmt = $this->conn->query("SELECT COUNT(*) FROM users");
    return (int)$stmt->fetchColumn();
    }

    public function fetchAll() {
        
        $stmt = $this->conn->query(
         "SELECT user_id, username, email, role, status FROM users"
         );
        $stmt->execute();
        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new User($row);
        }

        return $results;
    }
    
    
    
    
    public function approveUser($id) {
    $stmt = $this->conn->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
    $stmt->execute([$id]);
    }

    public function suspendUser($id) {
    $stmt = $this->conn->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?");
    $stmt->execute([$id]);
    }   

    public function deleteUser($id) {
    $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    }
    
  
    
}