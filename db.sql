SET SQL_SAFE_UPDATES = 0;

-- Drop existing tables
DROP TABLE IF EXISTS uploaded_files;
DROP TABLE IF EXISTS applicants;
DROP TABLE IF EXISTS users;

-- Create database
CREATE DATABASE IF NOT EXISTS plantilla_management;
USE plantilla_management;

-- Create users table for admin accounts
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add file_uploads tracking table
CREATE TABLE IF NOT EXISTS uploaded_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    month_year VARCHAR(7) NOT NULL,
    uploaded_by INT,
    modified_by INT NULL,
    last_modified TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('processing', 'completed', 'error') DEFAULT 'processing',
    error_message TEXT,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    FOREIGN KEY (modified_by) REFERENCES users(id)
);

-- Add index for month_year searching
CREATE INDEX idx_month_year ON uploaded_files(month_year);

-- Create applicants table with all required fields
CREATE TABLE applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_no VARCHAR(50),
    fullname VARCHAR(255),
    last_name VARCHAR(100),
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    extname VARCHAR(20),
    mi VARCHAR(10),
    sex ENUM('Male', 'Female', 'Others'),
    position_title VARCHAR(255),
    item_number VARCHAR(50),
    techcode VARCHAR(50),
    level INT,
    appointment_status ENUM('PERMANENT', 'TEMPORARY'),
    sg INT,
    step INT,
    monthly_salary DECIMAL(12,2),
    date_of_birth DATE,
    date_orig_appt DATE,
    date_govt_srvc DATE,
    date_last_promotion DATE,
    date_last_increment DATE,
    date_of_longevity DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create indices for better performance
CREATE INDEX idx_applicants_fullname ON applicants(fullname);
CREATE INDEX idx_applicants_id_no ON applicants(id_no);

-- Insert default admin account
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Default password is 'password', change immediately after first login

SET SQL_SAFE_UPDATES = 1;
