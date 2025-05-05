<?php

class cpModel {
    private int $id;
    private string $address;
    private string $postcode;
    private float $latitude;  
    private float $longitude;
    private string $availability;
    private float $price;     
    private string $imagePath;

    public function __construct(array $row) {
        $this->id = $row['point_id'];
        $this->address = $row['address'];
        $this->postcode = $row['postcode'];
        $this->latitude = (float)$row['latitude'];    
        $this->longitude = (float)$row['longitude'];  
        $this->availability = $row['availability'];
        $this->price = (float)$row['price'];  
        $this->imagePath = $row['image_path'] ?? '';
    }

    public function getLatitude(): float {
        return $this->latitude;
    }
    public function getLongitude(): float {
        return $this->longitude;
    }
    public function getId(): int { return $this->id; }
    public function getAddress(): string { return $this->address; }
    public function getPostcode(): string { return $this->postcode; }
    public function getAvailability(): string { return $this->availability; }
    public function getPrice(): float { return $this->price; } 
    public function getImagePath(): string { return $this->imagePath; }
}