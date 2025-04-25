<?php

require_once('Models/Database.php');

class HomeOwner {

private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }
    
public function GetID($userName) {
    $sql = "SELECT user_id FROM users WHERE username = :username";
    $stm = $this->conn->prepare($sql);
    $stm->bindParam(":username", $userName, PDO::PARAM_STR);
    $stm->execute();
    
    $result = $stm->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['user_id'] : null;
}
public function getMyPoint(int $ownerID) {
    $sqlVal = "SELECT role FROM users WHERE user_id = :user_id";
    $stmtVal = $this->conn->prepare($sqlVal);
    $stmtVal->bindParam(":user_id", $ownerID, PDO::PARAM_INT);
    $stmtVal->execute();
    $resultVal = $stmtVal->fetch(PDO::FETCH_ASSOC);

    if (!$resultVal) {
        return ['error' => 'User not found.'];
    }
    if ($resultVal['role'] !== 'Homeowner') {
        return ['error' => 'Access denied. User is not a homeowner.'];
    }

    $sqlCollect = "SELECT * FROM charge_points WHERE user_id = :user_id";
    $stmtCollect = $this->conn->prepare($sqlCollect);
    $stmtCollect->bindParam(":user_id", $ownerID, PDO::PARAM_INT);
    $stmtCollect->execute();
    $resultCollect = $stmtCollect->fetchAll(PDO::FETCH_ASSOC);
    
    return $resultCollect;
}

public function editPoint(int $pointID, int $userID, array $data) {
    $sqlVal = "SELECT role FROM users WHERE user_id = :user_id";
    $stmtVal = $this->conn->prepare($sqlVal);
    $stmtVal->bindParam(":user_id", $userID, PDO::PARAM_INT);
    $stmtVal->execute();
    $resultVal = $stmtVal->fetch(PDO::FETCH_ASSOC);

    if (!$resultVal) return ['error' => 'User not found.'];
    if ($resultVal['role'] !== 'Homeowner') return ['error' => 'Access denied.'];

    $required = ['address', 'postcode', 'latitude', 'longitude', 'price'];
    foreach ($required as $field) {
        if (empty($data[$field])) return ['error' => "Missing $field"];
    }
    $sqlFetch = "SELECT image_path FROM charge_points 
                 WHERE point_id = :point_id AND user_id = :user_id";
    $stmtFetch = $this->conn->prepare($sqlFetch);
    $stmtFetch->bindParam(":point_id", $pointID, PDO::PARAM_INT);
    $stmtFetch->bindParam(":user_id", $userID, PDO::PARAM_INT);
    $stmtFetch->execute();
    $existingData = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    if (!$existingData) return ['error' => 'Charge point not found.'];

    $sqlUpdate = "UPDATE charge_points 
                  SET address = :address, postcode = :postcode, latitude = :latitude,
                      longitude = :longitude, price = :price, availability = :availability,
                      image_path = :image_path 
                  WHERE point_id = :point_id AND user_id = :user_id";
    $stmtUpdate = $this->conn->prepare($sqlUpdate);
    
    $stmtUpdate->bindValue(":address", $data['address'], PDO::PARAM_STR);
    $stmtUpdate->bindValue(":postcode", $data['postcode'], PDO::PARAM_STR);
    $stmtUpdate->bindValue(":latitude", $data['latitude'], PDO::PARAM_STR);
    $stmtUpdate->bindValue(":longitude", $data['longitude'], PDO::PARAM_STR);
    $stmtUpdate->bindValue(":price", $data['price'], PDO::PARAM_STR);
    $stmtUpdate->bindValue(":availability", $data['availability'] ?? 'Available', PDO::PARAM_STR);
    $stmtUpdate->bindValue(":image_path", $data['image_path'] ?? $existingData['image_path'], PDO::PARAM_STR);
    $stmtUpdate->bindValue(":point_id", $pointID, PDO::PARAM_INT);
    $stmtUpdate->bindValue(":user_id", $userID, PDO::PARAM_INT);
    return $stmtUpdate->execute() 
        ? ['success' => 'Charge point updated!'] 
        : ['error' => 'Update failed: ' . implode(' ', $stmtUpdate->errorInfo())];
}

public function addPoint(int $ownerID, array $data) {
    // Validate role and existing charge point
    $sqlVal = "SELECT u.role, COUNT(c.point_id) AS existing_points 
               FROM users u 
               LEFT JOIN charge_points c ON u.user_id = c.user_id 
               WHERE u.user_id = :user_id 
               GROUP BY u.user_id";
    $stmtVal = $this->conn->prepare($sqlVal);
    $stmtVal->bindParam(":user_id", $ownerID, PDO::PARAM_INT);
    $stmtVal->execute();
    $resultVal = $stmtVal->fetch(PDO::FETCH_ASSOC);

    if (!$resultVal) return ['error' => 'User not found.'];
    if ($resultVal['role'] !== 'Homeowner') return ['error' => 'Only homeowners can add points.'];
    if ($resultVal['existing_points'] > 0) return ['error' => 'Maximum one charge point per homeowner.'];

    // Validate required fields
    $required = ['address', 'postcode', 'latitude', 'longitude', 'price'];
    foreach ($required as $field) {
        if (empty($data[$field])) return ['error' => "Missing $field"];
    }

    // Modified INSERT statement (removed description)
    $sqlInsert = "INSERT INTO charge_points 
                  (user_id, address, postcode, latitude, longitude, price, availability, image_path)
                  VALUES (:user_id, :address, :postcode, :latitude, :longitude, :price, :availability, :image_path)";
    
    $stmtInsert = $this->conn->prepare($sqlInsert);
    $availability = $data['availability'] ?? 'Available';
    
    // Use bindValue instead of bindParam for immediate values
    $stmtInsert->bindParam(":user_id", $ownerID, PDO::PARAM_INT);
    $stmtInsert->bindValue(":address", $data['address'], PDO::PARAM_STR);
    $stmtInsert->bindValue(":postcode", $data['postcode'], PDO::PARAM_STR);
    $stmtInsert->bindValue(":latitude", $data['latitude'], PDO::PARAM_STR);
    $stmtInsert->bindValue(":longitude", $data['longitude'], PDO::PARAM_STR);
    $stmtInsert->bindValue(":price", $data['price'], PDO::PARAM_STR);  // Using bindValue for immediate values
    $stmtInsert->bindValue(":availability", $availability, PDO::PARAM_STR);
    $stmtInsert->bindValue(":image_path", $data['image_path'] ?? null, PDO::PARAM_STR);  // Use bindValue for file path or null

    // Execute and return result
    return $stmtInsert->execute() 
        ? ['success' => 'Charge point added!'] 
        : ['error' => 'Add failed: ' . implode(' ', $this->conn->errorInfo())];
}



public function deletePoint(int $pointID, int $userID) {
    $sqlVal = "SELECT role FROM users WHERE user_id = :user_id";
    $stmtVal = $this->conn->prepare($sqlVal);
    $stmtVal->bindParam(":user_id", $userID, PDO::PARAM_INT);
    $stmtVal->execute();
    $resultVal = $stmtVal->fetch(PDO::FETCH_ASSOC);

    if (!$resultVal) {
        return ['error' => 'User not found.'];
    }
    if ($resultVal['role'] !== 'Homeowner') {
        return ['error' => 'Access denied. User is not a homeowner.'];
    }

    $sqlDel = "DELETE FROM charge_points WHERE point_id = :point_id AND user_id = :user_id";
    $stmtDel = $this->conn->prepare($sqlDel);
    $stmtDel->bindParam(":point_id", $pointID, PDO::PARAM_INT);
    $stmtDel->bindParam(":user_id", $userID, PDO::PARAM_INT);
    $stmtDel->execute();

    if ($stmtDel->rowCount() === 0) {
        return ['error' => 'Point not found or could not be deleted.'];
    }
    return ['success' => 'Point deleted successfully.'];
}

    
    public function getChargePointById($pointID, $userID) {
    $stmt = $this->conn->prepare("SELECT * FROM charge_points WHERE point_id = ? AND user_id = ?");
    $stmt->execute([$pointID, $userID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    
}