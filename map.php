<?php
session_start();
require_once 'Models/Database.php';
require_once 'Models/ChargePointData.php';
$view = new stdClass();
$view->pageTitle = 'map';
$database = new Database();
$conn = $database->connect(); 

$chargePoint = new ChargePointData($conn);
$chargePoints = $chargePoint->fetchAll();

// Pass data to view

if(  $_SESSION['login'] == true &&  $_SESSION['user_role'] == 'User' && $_SESSION['AcStatus'] == 'Active'){
    require_once 'Views/RentalUser/Map.phtml';
}else {
    header('Location: login.php');
    exit();
}

