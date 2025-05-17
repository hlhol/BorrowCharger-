<?php

require_once 'Models/Database.php';
require_once 'Models/ChargePointData.php';

class BookingData {
    private $conn;

    // Constructor receives a PDO connection object
    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }
    
    public function getNumBooking($username) {
        // validate the role
        $roleSql = "SELECT role FROM Users WHERE username = :username LIMIT 1";
        $roleStmt = $this->conn->prepare($roleSql);
        $roleStmt->bindParam(':username', $username, PDO::PARAM_STR);
        $roleStmt->execute();
        $user = $roleStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['role'] !== 'Admin') {
            return ['error' => 'Unauthorized: Only Admins can access booking statistics.'];
        }
        // Fetch top 3 charge points with the most bookings
        // get data
        $sql = "
            SELECT 
                charge_points.point_id, 
                charge_points.address,
                COUNT(bookings.booking_id) AS booking_count
            FROM 
                bookings
            JOIN 
                charge_points ON bookings.point_id = charge_points.point_id
            GROUP BY 
                charge_points.point_id
            ORDER BY 
                booking_count DESC
            LIMIT 3
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    
    public function getAllBookingByUser(int $userId, int $limit = 10, int $offset = 0): array {
        $sql = "SELECT * FROM Bookings WHERE user_id = :userId ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stm->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stm->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stm->execute();

        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countBookingsByUser(int $userId): int {
        $sql = "SELECT COUNT(*) FROM Bookings WHERE user_id = :userId";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stm->execute();

        return (int)$stm->fetchColumn();
    }

    
    public function getUserDetails(String $username) {
    
    $stm = $this->conn->prepare("SELECT * FROM Users WHERE username = :username AND role = :role");
    $stm->bindValue(':username', $username, PDO::PARAM_STR);
    $stm->bindValue(':role', 'User', PDO::PARAM_STR);  
    $stm->execute();

    return $stm->fetch(PDO::FETCH_ASSOC);
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


    //create new booking for a user by these parmeters 
public function createBooking($userId, $pointId, $data) {
    $sql = "INSERT INTO Bookings 
            (user_id, point_id, start_datetime, end_datetime, duration_hours, total_price) 
            VALUES 
            (:user_id, :point_id, :start_datetime, :end_datetime, :duration_hours, :total_price)";

    $stmt = $this->conn->prepare($sql);

   $success = $stmt->execute([
    ':user_id' => $userId,
    ':point_id' => $pointId,
    ':start_datetime' => $data['startDateTime'],
    ':end_datetime' => $data['endDateTime'],
    ':duration_hours' => $data['durationHours'],
    ':total_price' => $data['totalPrice']
]);

if (!$success) {
    error_log("Booking insert failed: " . print_r($stmt->errorInfo(), true));
}

return $success;
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
    
    
    // get from the database the booked times 
   public function getBookedTimes($pointId, $date) {
    $stmt = $this->conn->prepare("
        SELECT TIME_FORMAT(start_datetime, '%H:%i') as start_time, 
               TIME_FORMAT(end_datetime, '%H:%i') as end_time
        FROM Bookings
        WHERE point_id = ? 
        AND DATE(start_datetime) = ?
        AND status IN ('Approved', 'Pending')
    ");
    
    if (!$stmt->execute([$pointId, $date])) {
        error_log("Database error: " . implode(" ", $stmt->errorInfo()));
        return [];
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

   public function add30Minutes($time) {
    $date = new DateTime($time);
    $date->add(new DateInterval('PT30M'));
    return $date->format('H:i');
}
    

}
    