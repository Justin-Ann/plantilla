SET SQL_SAFE_UPDATES = 0;

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS plantilla_management;
USE plantilla_management;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20)
);

-- Insert admin user
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$8OxDJT4rZQp0tOHXGIX0HeWJA9UR.YG7TAq3OPz1pLzz7KxVISB2a', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Create uploaded_files table
CREATE TABLE IF NOT EXISTS uploaded_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    original_filename VARCHAR(255),
    file_path VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    month_year VARCHAR(7),
    status VARCHAR(20) DEFAULT 'active',
    user_id INT,
    processing_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    last_modified TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Add unique index to prevent duplicate records
ALTER TABLE uploaded_files 
ADD UNIQUE INDEX idx_month_file (month_year, original_filename);

-- Create raw_data table
CREATE TABLE IF NOT EXISTS raw_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_no VARCHAR(50),
    file_id INT NULL,
    plantilla_division VARCHAR(100),
    plantilla_section VARCHAR(100),
    plantilla_division_definition VARCHAR(100),
    equivalent_division VARCHAR(100),
    position_title VARCHAR(100),
    item_number VARCHAR(50),
    sg INT CHECK (sg BETWEEN 1 AND 100),
    date_vacated DATE,
    vacated_due_to ENUM('PROMOTION', 'COMPULSORY RETIREMENT', 'RESIGNATION', 'SWAPPING OF ITEM', 'TRANSFER') NULL,
    vacated_by VARCHAR(100),
    fullname VARCHAR(100),
    last_name VARCHAR(50),
    first_name VARCHAR(50),
    middle_name VARCHAR(50),
    ext_name VARCHAR(20),
    mi VARCHAR(10),
    sex ENUM('Male', 'Female', 'Others'),
    techcode VARCHAR(50),
    level INT CHECK (level BETWEEN 1 AND 10),
    step INT CHECK (step BETWEEN 1 AND 10),
    monthly_salary DECIMAL(12,2),
    monthly_salary_formatted VARCHAR(50),
    date_of_birth DATE,
    date_orig_appt DATE,
    date_govt_srvc DATE,
    date_last_promotion DATE,
    date_last_increment DATE,
    date_of_longevity DATE,
    id_no VARCHAR(50),
    appointment_status ENUM('Temporary', 'Permanent') DEFAULT 'Permanent',
    last_edited TIMESTAMP NULL DEFAULT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_latest BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (file_id) REFERENCES uploaded_files(id) ON DELETE CASCADE,
    INDEX idx_is_latest (is_latest),
    INDEX idx_raw_data_plantilla (plantilla_no),
    FULLTEXT INDEX ft_fullname (fullname),
    FULLTEXT INDEX ft_search (plantilla_no, fullname, position_title)
) ENGINE=InnoDB;

-- Create clean_data table
CREATE TABLE IF NOT EXISTS clean_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_no VARCHAR(50) UNIQUE,
    plantilla_division VARCHAR(100),
    plantilla_section VARCHAR(100),
    plantilla_division_definition VARCHAR(100),
    equivalent_division VARCHAR(100),
    position_title VARCHAR(100),
    item_number VARCHAR(50),
    sg VARCHAR(20),
    date_vacated DATE,
    vacated_due_to TEXT,
    vacated_by VARCHAR(100),
    remarks VARCHAR(50),
    date_published DATE,
    status ENUM('On-process', 'On-hold', 'Not yet for filling') DEFAULT 'On-process',
    raw_data_id INT,
    FOREIGN KEY (raw_data_id) REFERENCES raw_data(id)
);

-- Create applicants table
CREATE TABLE IF NOT EXISTS applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100),
    sex ENUM('Male', 'Female', 'Others'),
    position_title VARCHAR(100),
    techcode VARCHAR(50),
    date_of_birth DATE,
    date_last_promotion DATE,
    date_last_increment DATE,
    date_of_longevity DATE,
    appointment_status ENUM('Temporary', 'Permanent') DEFAULT 'Permanent',
    plantilla_no VARCHAR(50),
    INDEX idx_fullname (fullname),
    INDEX idx_plantilla (plantilla_no)
);

-- Create trigger for salary formatting
DELIMITER //
CREATE TRIGGER format_salary_before_insert 
BEFORE INSERT ON raw_data
FOR EACH ROW 
BEGIN
    SET NEW.monthly_salary_formatted = CONCAT('₱', FORMAT(NEW.monthly_salary, 2));
END//

CREATE TRIGGER format_salary_before_update
BEFORE UPDATE ON raw_data
FOR EACH ROW 
BEGIN
    IF NEW.monthly_salary != OLD.monthly_salary THEN
        SET NEW.monthly_salary_formatted = CONCAT('₱', FORMAT(NEW.monthly_salary, 2));
    END IF;
END//
DELIMITER ;

-- Add trigger to update clean_data status
DELIMITER //
CREATE TRIGGER update_clean_data_status
AFTER UPDATE ON raw_data
FOR EACH ROW
BEGIN
    UPDATE clean_data 
    SET status = 'On-process' 
    WHERE raw_data_id = NEW.id;
END//
DELIMITER ;

SET SQL_SAFE_UPDATES = 1;
