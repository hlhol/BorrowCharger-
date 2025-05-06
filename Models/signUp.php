<?php
require_once('Models/Database.php');

class User
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function register($username, $email, $password, $role, $fname,  $status)
    {
        $checkUsernameSql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($checkUsernameSql);
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            return "username already exists!";
        }

        $checkEmailSql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($checkEmailSql);
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            return "email already exists!";
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertSql = "INSERT INTO users (username, fname, email, password, role, status)
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($insertSql);

        if ($stmt->execute([$username, $fname, $email, $hashedPassword, $role, $status])) {
            return true;
        } else {
            return "registration failed. please try again.";
        }
    }
}
