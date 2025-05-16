<?php
session_start();
require_once 'Models/Database.php';
require_once 'Models/UserData.php';
require_once 'Models/BookingData.php';
require_once 'Models/ChargePointData.php';
require_once 'Models/MangementH.php';
require_once 'Models/cpModel.php';

$view = new stdClass();
$view->pageTitle = 'Booking';

//AJAX filtering section (used by searchFilter.js)
if (isset($_GET['ajax']) && $_GET['ajax'] === 'filter') {
    $database = new Database();
    $conn = $database->connect();
    $chargePoint = new ChargePointData($conn);

    $filters = [
        'search' => $_GET['search'] ?? null,
        'availability' => $_GET['availability'] ?? null,
        'maxPrice' => isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : null,
        'location' => $_GET['location'] ?? null
    ];

    $filteredPoints = $chargePoint->fetchFiltered($filters);

    $view->chargePoints = $filteredPoints;
    require 'Views/RentalUser/BookingCards.phtml';
    exit(); //Stop further execution for AJAX
}

//Role check and data fetch
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'User') {
    $db = new Database();
    $conn = $db->connect();
    $cpModel = new ChargePointData($conn);
    $bookingData = new BookingData($conn);
    $view->chargePoints = $cpModel->fetchAll();

    //Pass prices to JavaScript
    $pricesData = [];
    foreach ($view->chargePoints as $point) {
        $pricesData[$point->getId()] = $point->getPrice();
    }
    echo "<script>const chargePointPrices = " . json_encode($pricesData) . ";</script>";
}

//Optional view switching
if (isset($_GET['view']) && $_GET['view'] === 'contact') {
    require_once('Views/RentalUser/ContactH.phtml');
    exit();
}
if (isset($_GET['view']) && $_GET['view'] === 'back') {
    require_once('Views/RentalUser/Booking.phtml');
    exit();
}
$filteredPoints = $view->chargePoints ?? [];

$availability = $_GET['availability'] ?? '';
$search = $_GET['search'] ?? '';
$price_range = $_GET['price'] ?? '';

//Availability filter
if (!empty($availability)) {
    $filteredPoints = array_filter($filteredPoints, function ($point) use ($availability) {
        return $point->getAvailability() === $availability;
    });
}

//Price range filter (expects something like "0-500")
if (!empty($price_range) && str_contains($price_range, '-')) {
    list($min_price, $max_price) = explode('-', $price_range);
    $filteredPoints = array_filter($filteredPoints, function ($point) use ($min_price, $max_price) {
        $price = $point->getPrice();
        return $price >= $min_price && $price <= $max_price;
    });
}

//Search filter (address + postcode)
if (!empty($search)) {
    $filteredPoints = array_filter($filteredPoints, function ($point) use ($search) {
        return stripos($point->getAddress(), $search) !== false ||
               stripos($point->getPostcode(), $search) !== false;
    });
}

require_once('Views/RentalUser/Booking.phtml');
