<?php
session_start();
$view = new stdClass();
$view->pageTitle = 'Login';
require_once('Models/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['pass'];

    $login = new Login();
    $user = $login->auth($identifier, $password);

    if ($user) {
        $_SESSION['login'] = true;  
        $_SESSION['user_role'] = $user['role']; 
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['AcStatus'] = $user['status'];
        header('Location: dashboard.php'); 
        exit;
    } else {
        $view->error = "Invalid login credentials.";
    }
}

require_once('Views/login.phtml');
