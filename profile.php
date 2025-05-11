<?php

session_start();
require_once 'Models/BookingData.php';
require_once 'Models/Database.php';

$view = new stdClass();
$view->pageTitle = 'Profile';
$database = new Database();
$conn = $database->connect();
$BookingData = new BookingData($conn);

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'User') {
        //for user details 
        $view->userDetail = $BookingData->getUserDetails($_SESSION['username']);
        $userId = $view->userDetail['user_id'];

        //for user history of booking
        $limit = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;
        $view->BookingReq = $BookingData->getAllBookingByUser($userId, $limit, $offset);
        $totalBookings = $BookingData->countBookingsByUser($userId);
        $view->currentPage = $page;
        $view->totalPages = ceil($totalBookings / $limit);

        require_once('Views/RentalUser/ProfileR.phtml');
    } elseif ($_SESSION['user_role'] === 'Homeowner') {
        require_once('Views/Homeowner/ProfileH.phtml');
    }
} else { // return if he come in not correct way to the login 
    header('Location: login.php');
    exit;
}
