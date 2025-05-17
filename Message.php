<?php
// Controller: MessageController.php
session_start();
require_once 'Models/Database.php';
require_once 'Models/MessageData.php';
require_once 'Models/UserData.php';


$database = new Database();
$view = new stdClass();
$view->pageTitle = 'Dahsboard';
$conn = $database->connect();
$messageModel = new MessageData($conn);
$usr = new UserData($conn);

$id = $usr->getIdByName(  $_SESSION['username']);


// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['point_id'])) {
    // Validate and sanitize input
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var($_POST['user_email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    $pointId = (int)($_POST['point_id'] ?? 0);

    // Validate all required fields
    if (!$email || empty($name) || empty($phone) || empty($message) || $pointId <= 0) {
        $_SESSION['flash_error'] = 'Please fill all fields correctly';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }

   // In your MessageController.php
try {
    $homeownerId = $messageModel->getHomeownerIdByPointId($pointId);
    
    if (!$homeownerId) {
        throw new Exception("No homeowner found for point ID: $pointId");
    }

    $senderId = $messageModel->getUserIdByEmail($email) ?? 0;
    $content = "From: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
    
    // Debug output
    error_log("Creating message: Sender=$senderId, Receiver=$homeownerId");
    
    if ($messageModel->createMessage($senderId, $homeownerId, $content)) {
        $_SESSION['flash_message'] = 'Message sent successfully!';
    } else {
        throw new Exception("Database returned false on message creation");
    }
} catch (PDOException $e) {
    error_log("PDO Exception: " . $e->getMessage());
    $_SESSION['flash_error'] = 'Database error occurred. Please try again.';
} catch (Exception $e) {
    error_log("General Exception: " . $e->getMessage());
    $_SESSION['flash_error'] = $e->getMessage();
}

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit();
}

// Viewing messages (for homeowners)
if ( (!isset($_GET['action']) || $_GET['action'] === 'messages')) { 
    $homeownerId = $id; 
    
    // Pagination setup
    $currentPage = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 10;
    $totalMessages = $messageModel->getTotalMessagesForHomeowner($homeownerId);
    $totalPages = max(1, ceil($totalMessages / $perPage));
    
    // Get messages for current page
    $messages = $messageModel->getMessagesForHomeowner($homeownerId, $currentPage, $perPage);
    
    // Get homeowner's charge points
    $stmt = $conn->prepare("SELECT point_id, address FROM charge_points WHERE user_id = ?");
    $stmt->execute([$homeownerId]);
    $chargePoints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare view data
    $view = new stdClass();
    $view->messages = $messages;
    $view->chargePoints = $chargePoints;
    $view->currentPage = $currentPage;
    $view->totalPages = $totalPages;

    // Load view
    require_once('Views/Homeowner/Messages.phtml');
    exit();
}