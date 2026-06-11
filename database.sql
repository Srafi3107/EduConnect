CREATE DATABASE IF NOT EXISTS hometutor_db;
USE hometutor_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Tutor', 'Student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tutor_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255),
    class_level VARCHAR(100),
    location VARCHAR(255),
    experience VARCHAR(100),
    salary DECIMAL(10,2),
    description TEXT,
    availability ENUM('Available', 'Not Available') DEFAULT 'Available',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tutor_id INT NOT NULL,
    message TEXT,
    status ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert a default admin user (password is 'admin123')
INSERT INTO users (name, email, password, role) 
VALUES ('System Admin', 'admin@hometutor.com', '$2y$10$eE/.mXFv8K7dF4F8n.g5uOGE/9O.ZJkXQxM/r3eM7t7l1s8s7w0bO', 'Admin')
ON DUPLICATE KEY UPDATE id=id;
