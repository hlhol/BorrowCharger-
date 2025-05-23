<?php
session_start();
$view = new stdClass();
$view->pageTitle = 'Sign UP';

require_once('Models/signUp.php');

function isStrong($password) {
    return (bool) preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/', $password);
}


$username = $email = $password = $role = $name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['pass'];
    $role     = $_POST['role'];
    $name     = trim($_POST['name']);
    $status;
    if($role == "Homeowner"){
        $status = "Pending";
    } else {
         $status = "Active";   
    }
    

    if (!empty($username) && !empty($email) && !empty($password) && !empty($role) && !empty($name)) {
        if(!isStrong($password)){
                $view->message = "password must include uppercase,lowercase,num and special charcter";
                require_once('Views/signUp.phtml');
                exit;
        };
        
        $user = new User();
        $result = $user->register($username, $email, $password, $role, $name,  $status);

        if ($result === true) {
            $_SESSION['login'] = true;
            $_SESSION['user_role'] = $role;
            $_SESSION['username'] = $username;
            $_SESSION['AcStatus'] =  $status;
            header('Location: DashBoard.php');
            exit;
        } else {
            $view->message = $result;
        }
    } else {
        $view->message = "Please fill all the fields.";
    }
}

require_once('Views/signUp.phtml');
