<?php
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/BookingData.php';

header('Content-Type: application/json');

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['userId'], $input['data'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Initialize database connection
$db = new Database();
$conn = $db->connect();
$bookingData = new BookingData($conn);

// Create booking
$success = $bookingData->createBooking(
    (int)$input['userId'],
    (int)$input['data']['pointId'],
    [
        'startDateTime' => $input['data']['startDateTime'],
        'endDateTime' => $input['data']['endDateTime'],
        'durationHours' => (float)$input['data']['durationHours'],
        'totalPrice' => (float)$input['data']['totalPrice']
    ]
);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Booking created successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create booking']);
}