<?php

require_once 'Models/UserData.php';
require_once 'Models/BookingData.php';
require_once 'Models/ChargePointData.php';

class HomeOwner {
    private $userData;
    private $chargePointData;
    private $bookingData;

    public function __construct(
        UserData $userData,
        ChargePointData $chargePointData,
        BookingData $bookingData
    ) {
        $this->userData = $userData;
        $this->chargePointData = $chargePointData;
        $this->bookingData = $bookingData;
    }

    public function GetID(string $username): ?int {
        return $this->userData->getIdByName($username);
    }
    
    public function getMyPoint(int $ownerID): array {
        if (!$this->userData->isHomeowner($ownerID)) {
            return ['error' => 'Access denied. User is not a homeowner or the status not active.'];
        }
        return $this->chargePointData->getByOwner($ownerID);
    }

    public function editPoint(int $pointID, int $userID, array $data): array {
        if (!$this->userData->isHomeowner($userID)) {
            return ['error' => 'Access denied. User is not a homeowner or the status not active.'];
        }

        $required = ['address', 'postcode', 'latitude', 'longitude', 'price'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['error' => "Missing $field"];
            }
        }

        $existingData = $this->chargePointData->getById($pointID, $userID);
        if (!$existingData) {
            return ['error' => 'Charge point not found.'];
        }
         
        
    
        if ($price > 50.0) {
            return ['error' => 'Maximum price is 50.000 BHD'];
        }

        $success = $this->chargePointData->update($pointID, $userID, $data);
        return $success 
            ? ['success' => 'Charge point updated!'] 
            : ['error' => 'Update failed'];
    }

    public function addPoint(int $ownerID, array $data): array {
        if (!$this->userData->isHomeowner($ownerID)) {
            return ['error' => 'Only homeowners can add points.'];
        }

        if ($this->userData->countChargePoints($ownerID) > 0) {
            return ['error' => 'Maximum one charge point per homeowner.'];
        }

        $required = ['address', 'postcode', 'latitude', 'longitude', 'price'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['error' => "Missing $field"];
            }
        }
        
       if ($price > 50.0) {
            return ['error' => 'Maximum price is 50.000 BHD'];
        }

        $success = $this->chargePointData->create($ownerID, $data);
        return $success 
            ? ['success' => 'Charge point added!'] 
            : ['error' => 'Add failed'];
    }

    public function deletePoint(int $pointID, int $userID): array {
        if (!$this->userData->isHomeowner($userID)) {
            return ['error' => 'Access denied.'];
        }

        $success = $this->chargePointData->delete($pointID, $userID);
        return $success 
            ? ['success' => 'Point deleted successfully.'] 
            : ['error' => 'Point not found or could not be deleted.'];
    }
    
    public function getChargePointById(int $pointID, int $userID): array {
        if (!$this->userData->isHomeowner($userID)) {
            return ['error' => 'Access denied. User is not a homeowner.'];
        }
        $point = $this->chargePointData->getById($pointID, $userID);
        return $point ?: ['error' => 'Charge point not found.'];
    }

    public function GetAllBooking(int $ownerID, int $limit = 5, int $offset = 0): array {
        if (!$this->userData->isHomeowner($ownerID)) {
            return ['error' => 'Access denied. User is not a homeowner.'];
        }
        return $this->bookingData->getByOwner($ownerID, $limit, $offset);
    }
    
    public function acceptBooking(int $bookingID, int $ownerID): bool {
        if (!$this->userData->isHomeowner($ownerID)) {
            return false;
        }
        return $this->bookingData->updateStatus($bookingID, $ownerID, 'Approved', $_SESSION['user_role']);
    }

    public function declineBooking(int $bookingID, int $ownerID): bool {
        if (!$this->userData->isHomeowner($ownerID)) {
            return false;
        }
        return $this->bookingData->updateStatus($bookingID, $ownerID, 'Declined', $_SESSION['user_role']);
    }
    
    
}