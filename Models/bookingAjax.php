<?php
header('Content-Type: application/json');
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ChargePointData.php';

try {
    $database = new Database();
    $conn = $database->connect(); 
    $chargePoint = new ChargePointData($conn);

    $filters = [
        'search' => $_GET['search'] ?? null,
        'availability' => $_GET['availability'] ?? null,
        'maxPrice' => isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : null
    ];

    $results = $chargePoint->fetchFiltered($filters);

    echo json_encode(array_map(function($item) {
        return [
            'id' => $item->getId(),
            'latitude' => $item->getLatitude(),
            'longitude' => $item->getLongitude(),
            'address' => $item->getAddress(),
            'price' => $item->getPrice(),
            'availability' => $item->getAvailability()
        ];
    }, $results));
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}