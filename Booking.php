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
        
          // Prepare prices data for JavaScript
        $pricesData = [];
        foreach ($view->chargePoints as $point) {
            $pricesData[$point->getId()] = $point->getPrice();
        }
        
        // Pass prices to JavaScript
        echo "<script>const chargePointPrices = " . json_encode($pricesData) . ";</script>";
  
}
}
   
// Apply filters
$filteredPoints = $view->chargePoints ?? []; // Use the chargePoints from view

// Conditional view logic
if (isset($_GET['view']) && $_GET['view'] === 'contact') {
    require_once('Views/RentalUser/ContactH.phtml');
    exit; // Stop further execution so Booking view doesn’t load
}

if (isset($_GET['view']) && $_GET['view'] === 'back') {
    require_once('Views/RentalUser/Booking.phtml');
    exit; // Stop further execution so Booking view doesn’t load
}
// Get filters from query string
$location = $_GET['location'] ?? '';
$priceRange = $_GET['price'] ?? '';
$availability = $_GET['availability'] ?? '';
$search = $_GET['search'] ?? '';



if ($isAjax) {
    require('Views/RentalUser/BookingCards.phtml');
    exit;
}

// Apply availability filter
if (!empty($availability)) {
    $filteredPoints = array_filter($filteredPoints, function($point) use ($availability) {
        return $point->getAvailability() === $availability;
    });
}

// Apply price range filter
$price_range = $_GET['price_range'] ?? '';
if (!empty($price_range)) {
    list($min_price, $max_price) = explode('-', $price_range);
    $filteredPoints = array_filter($filteredPoints, function($point) use ($min_price, $max_price) {
        $price = $point->getPrice();
        return $price >= $min_price && $price <= $max_price;
    });
}

// Apply search filter
if (!empty($search)) {
    $filteredPoints = array_filter($filteredPoints, function($point) use ($search) {
        return stripos($point->getAddress(), $search) !== false;
    });
}

// Apply general search filter (searches both address and postcode)
if (!empty($search)) {
    $filteredPoints = array_filter($filteredPoints, function($point) use ($search) {
        return stripos($point->getAddress(), $search) !== false || 
               stripos($point->getPostcode(), $search) !== false;
    });
}

require_once('Views/RentalUser/Booking.phtml');