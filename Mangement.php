<?php
require_once 'Models/Database.php';
require_once 'Models/UserData.php';
require_once 'Models/BookingData.php';
require_once 'Models/ChargePointData.php';
require_once 'Models/MangementH.php';
require_once 'Models/cpModel.php';

session_start();
$view = new stdClass();
$view->pageTitle = 'Manage Page';

// Check if user has required session data
if (isset($_SESSION['user_role'])) {
    
    if ($_SESSION['user_role'] === 'Homeowner') {
        // Initialize services with database connection
        $db = new Database();
        $conn = $db->connect();
        
        // Create data access objects
        $userData = new UserData($conn);
        $chargePointData = new ChargePointData($conn);
        $bookingData = new BookingData($conn);
        
        // Create homeowner service
        $homeowner = new HomeOwner($userData, $chargePointData, $bookingData);
        
        $userID = $homeowner->GetID($_SESSION['username']);
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;
        
        // Load charge points data
        $result = $homeowner->getMyPoint($userID);
        if (isset($result['error'])) {
            $view->error = $result['error'];
        } else {
            $view->chargePoints = $result;
        }
        
        // Load paginated bookings
        $view->BookingReq = $homeowner->GetAllBooking($userID, $limit, $offset);
        $view->page = $page;
        $view->limit = $limit;

        // Handle POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Booking actions (accept/decline)
            if (isset($_POST['action'])) {
                $bookingID = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;
                $action = $_POST['action'];

                if ($bookingID) {
                    $success = false;
                    $actionVerb = '';
                    
                    switch ($action) {
                        case 'accept':
                            $success = $homeowner->acceptBooking($bookingID, $userID);
                            $actionVerb = 'accepted';
                            break;
                        case 'decline':
                            $success = $homeowner->declineBooking($bookingID, $userID);
                            $actionVerb = 'declined';
                            break;
                        default:
                            $_SESSION['error'] = "Invalid action.";
                            break;
                    }

                    if ($success) {
                        $_SESSION['success'] = "Booking $actionVerb successfully!";
                    } else {
                        $_SESSION['error'] = "Failed to $action booking or booking not found.";
                    }
                    header("Location: Mangement.php?page=$page");
                    exit;
                } else {
                    $_SESSION['error'] = "Invalid booking ID.";
                    header("Location: Mangement.php?page=$page");
                    exit;
                }
            }
            
            // Delete charge point
            if (isset($_POST['delete'])) {
                if (isset($_POST['point_id'])) {
                    $pointID = (int)$_POST['point_id'];
                    $response = $homeowner->deletePoint($pointID, $userID);
                    
                    if (isset($response['success'])) {
                        $view->success = $response['success'];
                        $view->chargePoints = $homeowner->getMyPoint($userID);
                    } else {
                        $view->error = $response['error'];
                    }
                } else {
                    $view->error = 'No charge point specified for deletion.';
                }
            }
            
            // Edit charge point - show form
            if (isset($_POST['edit'])) {
                $pointID = (int)$_POST['point_id'];
                $chargePoint = $homeowner->getChargePointById($pointID, $userID);
                if ($chargePoint) {
                    $view->chargePoint = $chargePoint;
                    require_once('Views/Homeowner/edit.phtml');
                    exit; 
                } else {
                    $view->error = 'Charge point not found.';
                }
            }

            // Edit charge point - process form
            if (isset($_POST['edit-f'])) {
                $pointID = (int)$_POST['point_id'];
                $formData = [
                    'address'      => trim($_POST['address']),
                    'postcode'     => trim($_POST['postcode']),
                    'latitude'     => (float)$_POST['latitude'],
                    'longitude'    => (float)$_POST['longitude'],
                    'price'        => (float)$_POST['price'],
                    'availability' => $_POST['availability'] ?? 'Available',
                    'image_path'   => $_POST['existing_image'] 
                ];

                if (!empty($_FILES['image']['name'])) {
                    $uploadResult = handleImageUpload($_FILES['image']);
                    if (isset($uploadResult['error'])) {
                        $view->error = $uploadResult['error'];
                    } else {
                        $formData['image_path'] = $uploadResult['path'];
                    }
                }

                $response = $homeowner->editPoint($pointID, $userID, $formData);

                if (isset($response['success'])) {
                    $_SESSION['success'] = $response['success'];
                    header('Location: Mangement.php'); 
                    exit;
                } else {
                    $view->error = $response['error'];
                    $chargePoint = $homeowner->getChargePointById($pointID, $userID);
                    $view->chargePoint = $chargePoint;
                    require_once('Views/Homeowner/edit.phtml');
                    exit;
                }
            }
            
            // Add new charge point
            if (isset($_POST['submit'])) {
    $formData = [
        'address' => trim($_POST['address']),
        'postcode' => trim($_POST['postcode']),
        'latitude' => (float) trim($_POST['latitude']), 
        'longitude' => (float) trim($_POST['longitude']), 
        'price' => (float) trim($_POST['price']), 
        'availability' => $_POST['availability'] ?? 'Available',
        'image_path' => null 
    ];

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
        finfo_close($fileInfo);
        
        // Validate file
        if (in_array($mimeType, $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('chargepoint_') . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $formData['image_path'] = $destination;
            } else {
                $view->error = "Failed to upload image.";
                return;
            }
        } else {
            $view->error = "Invalid file type or size (max 2MB allowed).";
            return;
        }
    }

    $response = $homeowner->addPoint($userID, $formData);
    if (isset($response['success'])) {
        $view->success = $response['success'];
        $view->chargePoints = $homeowner->getMyPoint($userID);
    } else {
        $view->error = $response['error'];
    }
        }
        
    }
        
        // Load the view
        require_once('Views/Homeowner/Management.phtml');
        
    } elseif ($_SESSION['user_role'] === 'Admin') {
        // Admin logic here

      
         // Initialize DB and model
        $db = new Database();
        $conn = $db->connect();
        $cpModel = new ChargePointData($conn);
        $view->chargePoints = $cpModel->fetchAll();
        
        
       // Handle user actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['id'])) {
        $pointID = (int)$_POST['id'];
        $action = $_POST['action'];

        switch ($action) {
            
            
             //case 'approve':
              //  $cpModel->addChargePoint($id);
                //$_SESSION['flash_message'] = "User added successfully.";
                //break;
            
            case 'edit':
                $chargePoint = $cpModel->getByIdForAdmin($pointID);
                if ($chargePoint) {
                    $view->chargePoint = $chargePoint;
                    require_once('Views/Admin/editChargePoint.phtml');
                    exit;
                } else {
                    $view->error = 'Charge point not found.';
                }
                break;

            case 'delete':
                $cpModel->deleteChargePointByAdmin($pointID);
                $_SESSION['flash_message'] = "Charge point deleted successfully.";
                break;
        }
    }
}


// Edit charge point - process form
if (isset($_POST['edit-f'])) {
    $pointID = (int)$_POST['point_id'];
    $formData = [
        'address'      => trim($_POST['address']),
        'postcode'     => trim($_POST['postcode']),
        'latitude'     => (float)$_POST['latitude'],
        'longitude'    => (float)$_POST['longitude'],
        'price'        => (float)$_POST['price'],
        'availability' => $_POST['availability'] ?? 'Available',
        'image_path'   => $_POST['existing_image'] 
    ];

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $uploadResult = handleImageUpload($_FILES['image']);
        if (isset($uploadResult['error'])) {
            $view->error = $uploadResult['error'];
            $view->chargePoint = $cpModel->getByIdForAdmin($pointID);
            require_once('Views/Admin/editChargePoint.phtml');
            exit;
        } else {
            $formData['image_path'] = $uploadResult['path'];
        }
    }

    // Update the charge point using Admin
    $updated = $cpModel->updateChargePointByAdmin(
        $pointID,
        $formData['address'],
        $formData['postcode'],
        $formData['latitude'],
        $formData['longitude'],
        $formData['price'],
        $formData['availability'],
        $formData['image_path']
    );

    if ($updated) {
        $_SESSION['success'] = "Charge point updated successfully.";
        header('Location: Mangement.php'); 
        exit;
    } else {
        $view->error = "Failed to update charge point.";
        $view->chargePoint = $cpModel->getByIdForAdmin($pointID);
        require_once('Views/Admin/editChargePoint.phtml');
        exit;
    }
}
    require_once('Views/Admin/ManageCharge.phtml'); 

    }

} else {
    header('Location: login.php');
    exit;
}

