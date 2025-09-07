CREATE DATABASE online_food_ordering_system; 
USE online_food_ordering_system;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL, 
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    contact_no VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    role ENUM('customer','admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE foods ( id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL, 
 description TEXT, 
 price DECIMAL(10,2) NOT NULL, 
 category VARCHAR(50), 
 image VARCHAR(100) DEFAULT 'default.jpg', 
 available TINYINT(1) DEFAULT 1, 
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP );