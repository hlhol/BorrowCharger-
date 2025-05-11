<?php
//import  all required thing
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
        
        //require the view:
        require_once('Views/RentalUser/DashBoardR.phtml');
    } elseif ($_SESSION['user_role'] === 'Homeowner') {
        
        //require the view:
        require_once('Views/Homeowner/DashBoardH.phtml');
    } elseif ($_SESSION['user_role'] === 'Admin') {
        
        //charts data
        $userModel = new UserData($conn);
        $chargePointModel = new ChargePointData($conn);
        $bookingModel = new BookingData($conn);
        $totalUsers = $userModel->countAllUsers(); 
        $activeChargers = $chargePointModel->countAllChargePoints();
        $pendingBookings = $bookingModel->countPending();
        $homeownerUsers = $userModel->PendHomeowner(); 
          
        //right side data:
        $view->chargerStatusOverview  = $chargePointModel->getAvailabilityPercentages();
        
        
        //left side Data:
        $view->usageOverTime = $bookingModel->getNumBooking($_SESSION['username']);
        //require the view:
        require_once('Views/Admin/DashBoardA.phtml');
    }
} else {
    header('Location: login.php');
    exit;
};

