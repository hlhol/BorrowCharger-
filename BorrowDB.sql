DROP DATABASE IF EXISTS BorowChager;
CREATE DATABASE BorowChager;
USE BorowChager;

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    fname VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    role ENUM('User', 'Homeowner', 'Admin') NOT NULL,
    status ENUM('Active', 'Suspended', 'Pending') DEFAULT 'Pending',
    email VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE charge_points (
    point_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address TEXT NOT NULL,
    postcode VARCHAR(20) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    price DECIMAL(5,2) NOT NULL,
    availability ENUM('Available', 'Unavailable') DEFAULT 'Available',
    image_path VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    point_id INT NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    status ENUM('Pending', 'Approved', 'Declined') DEFAULT 'Pending',
    duration_hours DECIMAL(5,2),
    total_price DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (point_id) REFERENCES charge_points(point_id) ON DELETE CASCADE
);

CREATE TABLE Messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    booking_id INT,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id) ON DELETE SET NULL
);

INSERT INTO Users (username, fname, password, role, status, email)
VALUES (
    'admin@admin.com',
    'User Lee Griffiths',
    '$2y$10$u4ZcRskyKrGBD9poIqGddet8KF.ScbANDOV6gLuMHi3YsP5pxrdh.',
    'Admin',
    'Active',
    'admin@admin.com'
);

INSERT INTO Users (username, fname, password, role, status, email)
VALUES (
    'lee@lee.com',
    'Lee Griffiths',
    '$2y$10$u4ZcRskyKrGBD9poIqGddet8KF.ScbANDOV6gLuMHi3YsP5pxrdh.', 
    'Homeowner',
    'Active',
    'lee@lee.com'
);

INSERT INTO Users (username, fname, password, role, status, email)
VALUES (
    'user@user.com',
    'User Lee Griffiths',
    '$2y$10$u4ZcRskyKrGBD9poIqGddet8KF.ScbANDOV6gLuMHi3YsP5pxrdh.',
    'User',
    'Active',
    'user@user.com'
);


INSERT INTO charge_points (user_id, address, postcode, latitude, longitude, price, availability)
VALUES (
    2, 
    '5 The Crescent, Salford, M5 4WT',
    'M5 4WT',
    53.483710,
    -2.270110,
    0.25, -- Price per kWh
    'Available'
);

INSERT INTO Bookings (user_id, point_id, start_datetime, end_datetime, status, duration_hours, total_price)
VALUES (
    3,                 -- user_id (the User)
    1,                 -- point_id (the charging point)
    '2025-04-26 10:00:00', -- start_datetime
    '2025-04-26 12:00:00', -- end_datetime
    'Pending',         -- initial status
    2.00,              -- duration in hours
    0.50               -- total price (2 hours × £0.25/hour)
);