<?php

class cpModel {
    private int $id;
    private string $address;
    private string $postcode;
    private string $availability;
    private string $price;
    private string $imagePath;

    public function __construct(array $row) {
        $this->id = $row['point_id'];
        $this->address = $row['address'];
        $this->postcode = $row['postcode'];
        $this->availability = $row['availability'];
        $this->price = $row['price'];
        $this->imagePath = !empty($row['image_path']) ? $row['image_path'] : '';
    }

    public function getId(): int { return $this->id; }
    public function getAddress(): string { return $this->address; }
    public function getPostcode(): string { return $this->postcode; }
    public function getAvailability(): string { return $this->availability; }
    public function getprice(): string { return $this->price; }
     public function getImagePath(): string { return $this->imagePath; } 
}

