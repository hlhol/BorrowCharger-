<?php
header('Content-Type: application/json');
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/BookingData.php'; // <- Change to your Booking model

try {
    $database = new Database();
    $conn = $database->connect(); 
    $booking = new BookingData($conn); // Assuming you have this

    $filters = [
        'search' => $_GET['search'] ?? null,
        'availability' => $_GET['availability'] ?? null,
        'maxPrice' => isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : null
    ];

    $results = $booking->fetchFiltered($filters); // <- Similar to fetchFiltered()

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
