-- Create database if not exists
CREATE DATABASE IF NOT EXISTS hris_db;
USE hris_db;

-- Add user roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    permissions JSON
);

-- Add audit log table
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add file versions table
CREATE TABLE IF NOT EXISTS file_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT,
    version INT,
    file_path VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES uploaded_files(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Add divisions table
CREATE TABLE IF NOT EXISTS divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code INT UNIQUE,
    name VARCHAR(255),
    parent_id INT NULL,
    FOREIGN KEY (parent_id) REFERENCES divisions(id)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    verification_token VARCHAR(64),
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'inactive'
);

CREATE TABLE IF NOT EXISTS plantilla_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('On-Process', 'On-Hold', 'Not Yet for Filling') NOT NULL,
    record_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    modified_by INT,
    FOREIGN KEY (modified_by) REFERENCES users(id)
);
