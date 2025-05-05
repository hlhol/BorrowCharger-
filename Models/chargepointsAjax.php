<?php
header('Content-Type: application/json');
require_once 'Models/Database.php';
require_once 'Models/ChargePointData.php'; 

try {
    $database = new Database();
    $conn = $database->connect(); 
    
    $chargePoint = new ChargePointData($conn);
    $chargePoints = $chargePoint->fetchAll();
    
    echo json_encode(array_map(function($cp) {
        return [
            'latitude' => $cp->getLatitude(),
            'longitude' => $cp->getLongitude(),
            'address' => $cp->getAddress(),
            'price' => $cp->getPrice(),
            'availability' => $cp->getAvailability()
        ];
}, $chargePoints));} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}