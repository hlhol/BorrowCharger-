<?php
class MessageData {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function createMessage(int $senderId, int $receiverId, string $content): bool {
    try {
        $stmt = $this->conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, created_at, is_read) 
                                   VALUES (:sender_id, :receiver_id, :content, NOW(), 0)");
        
        $result = $stmt->execute([
            ':sender_id' => $senderId,
            ':receiver_id' => $receiverId,
            ':content' => $content
        ]);
        
        if (!$result) {
            $error = $stmt->errorInfo();
            error_log("Database error: " . print_r($error, true));
            throw new Exception("Database execute failed: " . $error[2]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("PDO Exception in createMessage: " . $e->getMessage());
        throw $e;
    }
  }

    public function getHomeownerIdByPointId(int $pointId): ?int {
        $stmt = $this->conn->prepare("SELECT u.user_id FROM Users u JOIN charge_points cp ON u.user_id = cp.user_id WHERE cp.point_id = ? LIMIT 1");
        $stmt->execute([$pointId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['user_id'] : null;
    }

    public function getUserIdByEmail(string $email): ?int {
        $stmt = $this->conn->prepare("SELECT user_id FROM Users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['user_id'] : null;
    }

  public function getMessagesForHomeowner(int $homeownerId, int $page = 1, int $perPage = 10): array {
    $offset = ($page - 1) * $perPage;

    $stmt = $this->conn->prepare("
        SELECT 
            m.message_id,
            m.content,
            m.created_at,
            m.is_read,
            cp.address AS point_address,
            m.sender_id,
            m.receiver_id,
            u.email AS sender_email,
            cp.point_id
        FROM Messages m
        LEFT JOIN Users u ON m.sender_id = u.user_id
        LEFT JOIN charge_points cp ON m.receiver_id = cp.user_id
        WHERE m.receiver_id = ?
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bindValue(1, $homeownerId, PDO::PARAM_INT);
    $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



    public function getTotalMessagesForHomeowner(int $homeownerId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM Messages WHERE receiver_id = ?");
        $stmt->execute([$homeownerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
    
       public function deleteMessage(int $messageId, int $homeownerId): bool {
        $stmt = $this->conn->prepare("DELETE FROM Messages WHERE message_id = ? AND receiver_id = ?");
        return $stmt->execute([$messageId, $homeownerId]);
    }
    
}