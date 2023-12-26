-- Create database
CREATE DATABASE IF NOT EXISTS sellcars;

USE sellcars;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intnr VARCHAR(50) NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_persons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    mobile_phone VARCHAR(20),
    birth_date DATE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    company_name VARCHAR(100),
    country VARCHAR(50),
    city VARCHAR(50),
    zip VARCHAR(20),
    fax VARCHAR(20),
    phone VARCHAR(20),
    street VARCHAR(255),
    email VARCHAR(100),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- mock accounts
INSERT INTO users (first_name, last_name, email, password_hash) VALUES
    ('Farhad', 'Hassan', 'farhad@example.com', MD5('password123')),
    ('Ralf', 'Doe', 'ralf@example.com', MD5('password456')),
    ('Tony', 'Kim', 'tony@example.com', MD5('password789'));