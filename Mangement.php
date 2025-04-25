<?php
session_start();
$view = new stdClass();
$view->pageTitle = 'Manage Page';

//  has required session data
if (isset($_SESSION['user_role'])) {
    
    if ($_SESSION['user_role'] === 'Homeowner') {
        require_once('Models/MangementH.php');
        $homeowner = new HomeOwner();
        
        $userID = $homeowner->GetID( $_SESSION['username']);
        $result = $homeowner->getMyPoint($userID);
        
        if (isset($result['error'])) {
            $view->error = $result['error'];
        } else {
            $view->chargePoints = $result;
        }
        
        //for delete
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
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
        
         
        
        //for edit
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit-f'])) {
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
        
        
        //now for add 
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
            $formData = [
                'address' => trim($_POST['address']),
                'postcode' => trim($_POST['postcode']),
                'latitude' => (float) trim($_POST['latitude']), 
                'longitude' => (float) trim($_POST['longitude']), 
                'price' => (float) trim($_POST['price']), 
                'availability' => $_POST['availability'] ?? 'Available',
                'image_path' => null 
            ];

            $response = $homeowner->addPoint($userID, $formData);
            if (isset($response['success'])) {
                $view->success = $response['success'];
                $view->chargePoints = $homeowner->getMyPoint($userID);
            } else {
                $view->error = $response['error'];
            }
        }

        
        
        require_once('Views/Homeowner/Management.phtml');
        
    } elseif ($_SESSION['user_role'] === 'Admin') {
        // Admin logic here
    }
} else {
    header('Location: login.php');
    exit;
}
