<?php

session_start();

$view = new stdClass();
$view->pageTitle = 'Dahsboard';

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'user') {
        
        require_once('Views/RentalUser/DashBoardR.phtml');
    } elseif ($_SESSION['user_role'] === 'Homeowner') {
        
        require_once('Views/Homeowner/DashBoardH.phtml');
    } elseif ($_SESSION['user_role'] === 'Admin') {
        
        require_once('Views/Admin/DashBoardA.phtml');
    }
} else {
    header('Location: login.php');
    exit;
};

