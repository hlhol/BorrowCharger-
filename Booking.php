<?php
require_once 'Models/Database.php';
require_once 'Models/UserData.php';
require_once 'Models/BookingData.php';
require_once 'Models/ChargePointData.php';
require_once 'Models/MangementH.php';
require_once 'Models/cpModel.php';

session_start();
$view = new stdClass();
$view->pageTitle = 'Booking';
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'User') {
$db = new Database();
        $conn = $db->connect();
        $cpModel = new ChargePointData($conn);
        $bookingData = new BookingData($conn);
        $view->chargePoints = $cpModel->fetchAll();
        
        
}
          
  }

// Conditional view logic
if (isset($_GET['view']) && $_GET['view'] === 'contact') {
    require_once('Views/RentalUser/ContactH.phtml');
    exit; // Stop further execution so Booking view doesn’t load
}


// Get filters from query string
$location = $_GET['location'] ?? '';
$priceRange = $_GET['price'] ?? '';
$availability = $_GET['availability'] ?? '';
$search = $_GET['search'] ?? '';

// Apply filters
$filteredPoints = $view->chargePoints ?? []; // Use the chargePoints from view


if ($isAjax) {
    require('Views/RentalUser/BookingCards.phtml');
    exit;
}

// Apply location filter
if (!empty($location)) {
    $filteredPoints = array_filter($filteredPoints, function($point) use ($location) {
        return stripos($point->getAddress(), $location) !== false;
    });
}

// Apply price sorting - using $priceRange instead of $price
if ($priceRange === 'Low_to_High') {
    usort($filteredPoints, function($a, $b) {
        return $a->getPrice() <=> $b->getPrice();
    });
} elseif ($priceRange === 'High_to_Low') {
    usort($filteredPoints, function($a, $b) {
        return $b->getPrice() <=> $a->getPrice();
    });
}

// Apply search filter
if (!empty($search)) {
    $filteredPoints = array_filter($filteredPoints, function($point) use ($search) {
        return stripos($point->getAddress(), $search) !== false;
    });
}

// Update the view with filtered points
$view->chargePoints = $filteredPoints;

require_once('Views/RentalUser/Booking.phtml');