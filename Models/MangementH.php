<?php

require_once('Models/Database.php');

class HomeOwner {

private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
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


public function addPoint(int $ownerID, array $data) {
    // Validate role and existing charge point (separate validation statement)
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

    $required = ['address', 'postcode', 'latitude', 'longitude', 'price'];
    foreach ($required as $field) {
        if (empty($data[$field])) return ['error' => "Missing $field"];
    }

    $sqlInsert = "INSERT INTO charge_points 
                  (user_id, address, postcode, latitude, longitude, price, availability, image_path, description)
                  VALUES (:user_id, :address, :postcode, :latitude, :longitude, :price, :availability, :image_path, :description)";
    $stmtInsert = $this->conn->prepare($sqlInsert);
    $availability = $data['availability'] ?? 'Available';
    $stmtInsert->bindParam(":user_id", $ownerID, PDO::PARAM_INT);
    $stmtInsert->bindParam(":address", $data['address'], PDO::PARAM_STR);
    $stmtInsert->bindParam(":postcode", $data['postcode'], PDO::PARAM_STR);
    $stmtInsert->bindParam(":latitude", $data['latitude'], PDO::PARAM_STR);
    $stmtInsert->bindParam(":longitude", $data['longitude'], PDO::PARAM_STR);
    $stmtInsert->bindParam(":price", $data['price'], PDO::PARAM_STR);
    $stmtInsert->bindParam(":availability", $availability, PDO::PARAM_STR);
    $stmtInsert->bindParam(":image_path", $data['image_path'] ?? null, PDO::PARAM_STR);
    $stmtInsert->bindParam(":description", $data['description'] ?? null, PDO::PARAM_STR);

    return $stmtInsert->execute() 
        ? ['success' => 'Charge point added!'] 
        : ['error' => 'Add failed: ' . $this->conn->errorInfo()[2]];
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

    
    

    
}

    
