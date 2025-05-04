<?php
require_once 'Models/Database.php';
require_once 'Models/UserData.php';


session_start();
$view = new stdClass();
$view->pageTitle = 'Manage Page';

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'Admin') {
        
        // Initialize DB and model
        $db = new Database();
        $conn = $db->connect();
        $userData = new UserData($conn);
        
        $view->users = $userData->fetchAll();

        // Handle user actions (approve, suspend, delete)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && isset($_POST['id'])) {
                $userId = (int)$_POST['id'];
                $action = $_POST['action'];
                
                 switch ($action) {
            case 'approve':
                $userData->approveUser($id);
                $_SESSION['flash_message'] = "User approved successfully.";
                break;

            case 'suspend':
                $userData->suspendUser($id);
                $_SESSION['flash_message'] = "User suspended successfully.";
                break;

            case 'delete':
                $userData->deleteUser($id);
                $_SESSION['flash_message'] = "User deleted successfully.";
                break;
             }
                header('Location: ManageU.php');
                exit;
            }
        }

        

        

        //  Load the viewa
        require_once('Views/Admin/ManageUsers.phtml');
        
    } else {
        header('Location: login.php');
        exit;
    }
}
