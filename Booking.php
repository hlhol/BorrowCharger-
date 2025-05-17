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
               stripos($point->getPostcode(), $search) !== false ||
               stripos((string)$point->getLatitude(), $search) !== false ||
               stripos((string)$point->getLongitude(), $search) !== false;
    });
}
//Contact Form with homeowner email logic
if (isset($_GET['view']) && $_GET['view'] === 'contactForm' && isset($_GET['point_id'])) {
    $pointId = (int)$_GET['point_id'];

    $database = new Database();
    $conn = $database->connect();
    $chargePoint = new ChargePointData($conn);

    $homeownerId = $chargePoint->getHomeownerIdByPointId($pointId);

    if (!$homeownerId) {
        die('Invalid charge point.');
    }

    $stmt = $conn->prepare("SELECT email FROM Users WHERE user_id = ?");
    $stmt->execute([$homeownerId]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);
    $view->homeownerEmail = $owner['email'] ?? 'notfound@example.com';
    $view->pointId = $pointId;

    require 'Views/RentalUser/ContactH.phtml';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    // Validate user session

    $userId = $_SESSION['user_id'];

    // Validate POST data
    if (!isset($_POST['point_id'], $_POST['bookingdate'], $_POST['starttime'], $_POST['endtime'], $_POST['totalPrice'])) {
        die('Incomplete booking data.');
    }

    $pointId = (int)$_POST['point_id'];
    $bookingDate = $_POST['bookingdate'];
    $startTime = $_POST['starttime'];
    $endTime = $_POST['endtime'];
    $totalPrice = (float)$_POST['totalPrice'];

    // Combine date and time
    $startDateTime = $bookingDate . ' ' . $startTime;
    $endDateTime = $bookingDate . ' ' . $endTime;

    // Validate time logic
    $start = new DateTime($startDateTime);
    $end = new DateTime($endDateTime);

    if ($end <= $start) {
        die('End time must be after start time.');
    }

    $duration = $start->diff($end)->h + ($start->diff($end)->i / 60);

    // Insert into DB
    $db = new Database();
    $conn = $db->connect();
    $bookingData = new BookingData($conn);

    $result = $bookingData->createBooking($userId, $pointId, [
        'startDateTime' => $startDateTime,
        'endDateTime' => $endDateTime,
        'durationHours' => $duration,
        'totalPrice' => $totalPrice
    ]);

    if ($result) {
        header("Location: Booking.php?success=1");
        exit();
    } else {
        header("Location: Booking.php?error=1");
        exit();
    }
}


// booking data
if (isset($_GET['action']) && $_GET['action'] === 'getBookedTimes' && isset($_GET['point_id'], $_GET['date'])) {
    // Prevent mixed content
    ob_clean();
    
    try {
        $pointId = (int)$_GET['point_id'];
        $date = $_GET['date'];
        
        $db = new Database();
        $conn = $db->connect();
        $bookingData = new BookingData($conn);
        
        $bookings = $bookingData->getBookedTimes($pointId, $date);
        
        if (!is_array($bookings)) {
            throw new Exception("Invalid bookings data received");
        }
        
        $bookedTimes = [];
        foreach ($bookings as $booking) {
            $start = $booking['start_time'];
            $end = $booking['end_time'];
            
            $current = $start;
            while ($current < $end) {
                $bookedTimes[] = $current;
                $currentTime = new DateTime($current);
                $currentTime->add(new DateInterval('PT30M'));
                $current = $currentTime->format('H:i');
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(array_unique($bookedTimes));
        exit();
        
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

require_once('Views/RentalUser/Booking.phtml');