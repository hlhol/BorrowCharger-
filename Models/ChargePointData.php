<?php

require_once 'Models/Database.php';

class ChargePointData {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function getByOwner(int $ownerId): array {
        $stmt = $this->conn->prepare("SELECT * FROM charge_points WHERE user_id = ?");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $pointId, int $ownerId): ?array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM charge_points WHERE point_id = ? AND user_id = ?"
        );
        $stmt->execute([$pointId, $ownerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

public function create(int $ownerId, array $data): bool {
    try {
        $stmt = $this->conn->prepare(
            "INSERT INTO charge_points 
            (user_id, address, postcode, latitude, longitude, price, availability, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        return $stmt->execute([
            $ownerId,
            $data['address'],
            $data['postcode'],
            $data['latitude'],
            $data['longitude'],
            $data['price'],
            $data['availability'] ?? 'Available',
            $data['image_path'] ?? null
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

    public function update(int $pointId, int $ownerId, array $data): bool {
        $stmt = $this->conn->prepare(
            "UPDATE charge_points SET
            address = ?, postcode = ?, latitude = ?, longitude = ?,
            price = ?, availability = ?, image_path = ?
            WHERE point_id = ? AND user_id = ?"
        );
        
        return $stmt->execute([
            $data['address'],
            $data['postcode'],
            $data['latitude'],
            $data['longitude'],
            $data['price'],
            $data['availability'] ?? 'Available',
            $data['image_path'] ?? null,
            $pointId,
            $ownerId
        ]);
    }

    public function delete(int $pointId, int $ownerId): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM charge_points WHERE point_id = ? AND user_id = ?"
        );
        return $stmt->execute([$pointId, $ownerId]);
    }
}