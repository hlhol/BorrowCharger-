<?php

require_once 'Models/Database.php';

class BookingData {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

public function getByOwner(int $ownerId, int $limit = 5, int $offset = 0): array {
    $stmt = $this->conn->prepare(
        "SELECT b.*, u.username AS booked_by, cp.address 
         FROM Bookings b
         INNER JOIN charge_points cp ON b.point_id = cp.point_id
         INNER JOIN Users u ON b.user_id = u.user_id
         WHERE cp.user_id = ?
         ORDER BY b.created_at DESC
         LIMIT ? OFFSET ?"
    );
    
    $stmt->bindValue(1, $ownerId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function updateStatus(int $bookingId, int $ownerId, string $status): bool {
        $stmt = $this->conn->prepare(
            "UPDATE Bookings b
             JOIN charge_points cp ON b.point_id = cp.point_id
             SET b.status = ?
             WHERE b.booking_id = ? 
             AND cp.user_id = ?
             AND b.status = 'Pending'"
        );
        return $stmt->execute([$status, $bookingId, $ownerId]);
    }
}