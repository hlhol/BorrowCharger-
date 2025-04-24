<?php
session_start(); 
$view = new stdClass();
$view->pageTitle = 'Mange page';


if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'Homeowner') {
          require_once('Views/Homeowner/Management.phtml');
}elseif ($_SESSION['user_role'] === 'Admin'){
        // need condition to see manage user or charge point 
    }
}else {
    header('Location: login.php');
    exit;
};

