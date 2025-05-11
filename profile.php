<?php

session_start();

$view = new stdClass();
$view->pageTitle = 'Profile';

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'User') {
        
        require_once('Views/RentalUser/ProfileR.phtml');
    } elseif ($_SESSION['user_role'] === 'Homeowner') {
        
        require_once('Views/Homeowner/ProfileH.phtml');
    }
} else {
    header('Location: login.php');
    exit;
};

