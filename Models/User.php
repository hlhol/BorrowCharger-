<?php

class User {
    private int $id;
    private string $name;
    private string $email;
    private string $role;
    private string $status;

    public function __construct(array $row) {
        $this->id = $row['user_id'];
        $this->name = $row['username'];
        $this->email = $row['email'];
        $this->role = $row['role'];
        $this->status = $row['status'];
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function getStatus(): string { return $this->status; }
}

