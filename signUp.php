<?php
session_start();
$view = new stdClass();
$view->pageTitle = 'Sign UP';

require_once('Models/signUp.php');

$username = $email = $password = $role = $name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['pass'];
    $role     = $_POST['role'];
    $name     = trim($_POST['name']);

    if (!empty($username) && !empty($email) && !empty($password) && !empty($role) && !empty($name)) {
        $user = new User();
        $result = $user->register($username, $email, $password, $role, $name);

        if ($result === true) {
            $_SESSION['login'] = true;
            $_SESSION['user_role'] = $role;

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
