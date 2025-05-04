<?php

require_once 'Models/Database.php';
require_once 'Models/UserData.php';
require_once 'Models/BookingData.php';
require_once 'Models/ChargePointData.php';
require_once 'Models/MangementH.php';



session_start();
$view = new stdClass();
$view->pageTitle = 'Dahsboard';


$db = new Database();
        $conn = $db->connect();
        

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'User') {
        
        require_once('Views/RentalUser/DashBoardR.phtml');
    } elseif ($_SESSION['user_role'] === 'Homeowner') {
        
        require_once('Views/Homeowner/DashBoardH.phtml');
    } elseif ($_SESSION['user_role'] === 'Admin') {
        

        
        $userModel = new UserData($conn);
        $chargePointModel = new ChargePointData($conn);
        $bookingModel = new BookingData($conn);
    
        $totalUsers = $userModel->countAllUsers(); 
        $activeChargers = $chargePointModel->countAllChargePoints();
        $pendingBookings = $bookingModel->countPending();
        
        
        //$bookingStats = $bookingModel->getMonthlyBookingStats();
        //$months = array_column($bookingStats, 'month');
        //$counts = array_map('intval', array_column($bookingStats, 'total'));
        //$usageOverTime = json_encode(['labels' => $months, 'data' => $counts]);

        //$chargerStats = $chargePointModel->getAvailabilityStats();
        //$statuses = array_column($chargerStats, 'availability');
        //$statusCounts = array_map('intval', array_column($chargerStats, 'count'));
        //$chargerStatusOverview = json_encode(['labels' => $statuses, 'data' => $statusCounts]);

        //$view->usageOverTime = $usageOverTime;
        //$view->chargerStatusOverview = $chargerStatusOverview;

        
        require_once('Views/Admin/DashBoardA.phtml');
    }
} else {
    header('Location: login.php');
    exit;
};

