<?php

require_once 'Models/Database.php';

class BookingData {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }
    
    public function getAllBokkingByUser(int $BookrID){
        
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

    
    
    public function countPending(): int {
    $stmt = $this->conn->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
    }


    
    public function createBooking(
    int $userId,
    int $pointId,
    string $startDateTime,
    string $endDateTime,
    float $durationHours,
    float $totalPrice,
    string $status = 'Pending' ): bool {
    $stmt = $this->conn->prepare("
        INSERT INTO bookings 
        (user_id, point_id, start_datetime, end_datetime, duration_hours, total_price, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([
        $userId,
        $pointId,
        $startDateTime,
        $endDateTime,
        $durationHours,
        $totalPrice,
        $status
    ]);
}
    public function getMonthlyBookingStats(): array {
    $stmt = $this->conn->prepare("
        SELECT DATE_FORMAT(start_datetime, '%b') AS month, COUNT(*) AS total
        FROM bookings
        WHERE status = 'Approved'
        GROUP BY month
        ORDER BY MONTH(STR_TO_DATE(month, '%b'))
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}