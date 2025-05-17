<?php
require_once 'Models/Database.php';
require_once 'Models/UserData.php';
require_once 'Models/BookingData.php';
require_once 'Models/ChargePointData.php';

session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->connect();

$userModel = new UserData($conn);
$chargePointModel = new ChargePointData($conn);
$bookingModel = new BookingData($conn);

$totalUsers = $userModel->countAllUsers();
$activeChargers = $chargePointModel->countAllChargePoints();
$pendingBookings = $bookingModel->countPending();
$homeownerUsers = $userModel->PendHomeowner();

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="system_report.txt"');

echo "=== System Report ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";
echo "Total Users: $totalUsers\n";
echo "Active Chargers: $activeChargers\n";
echo "Pending Bookings: $pendingBookings\n";
echo "Pending Homeowners: $homeownerUsers\n";
