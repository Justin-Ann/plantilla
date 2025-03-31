SET SQL_SAFE_UPDATES = 0;

-- First drop tables in reverse order of dependencies
DROP TABLE IF EXISTS clean_data;
DROP TABLE IF EXISTS raw_data;
DROP TABLE IF EXISTS applicants;
DROP TABLE IF EXISTS uploaded_files;
DROP TABLE IF EXISTS divisions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS users;

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS plantilla_management;
USE plantilla_management;

-- Create users table first (no dependencies)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255),
    role VARCHAR(20),
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    verification_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME
);

-- Insert admin user with email
INSERT INTO users (username, email, password, role, email_verified) 
VALUES (
    'admin', 
    'admin@pagasa.gov.ph', 
    '$2y$10$8OxDJT4rZQp0tOHXGIX0HeWJA9UR.YG7TAq3OPz1pLzz7KxVISB2a', 
    'admin',
    1
) AS new_user
ON DUPLICATE KEY UPDATE 
    username = new_user.username,
    email = new_user.email;

-- Create divisions table (no dependencies)
CREATE TABLE IF NOT EXISTS divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    division_code INT NOT NULL UNIQUE,
    division_name VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    FOREIGN KEY (parent_id) REFERENCES divisions(id)
);

-- Insert all divisions
TRUNCATE TABLE divisions;
INSERT INTO divisions (division_code, division_name) VALUES
(1, 'Office of the Administrator'),
(2, 'Administrative Division'),
(3, 'Human Resources Management and Development Section'),
(4, 'Records Management Section'),
(5, 'Procurement, Property and General Services Section'),
(6, 'Financial, Planning and Management Division'),
(7, 'Accounting Section'),
(8, 'Budget and Planning Section'),
(9, 'Management Services Section'),
(10, 'Engineering and Technical Services Division'),
(11, 'Meteorological Equipment and Telecommunications Technology Services Section'),
(12, 'Meteorological Guides and Standards Section'),
(13, 'Mechanical, Electrical and Infrastructure Engineering Section'),
(14, 'Weather Division'),
(15, 'Weather Forecasting Section'),
(16, 'Meteorological Data and Information Exchange Section'),
(17, 'Techniques Application and Meteorological Satellite Section'),
(18, 'Aeronautical Meteorological Satellite Section'),
(19, 'Marine Meteorological Services Section'),
(20, 'Hydro-Meteorological Division'),
(21, 'Hydrometeorological Data Applications Sections'),
(22, 'Flood Forecasting and Warning Section'),
(23, 'Hydrometeorological Telemetry Section'),
(24, 'Climatology and Agrometeorology Division'),
(25, 'Climate Monitoring and Prediction Section'),
(26, 'Farm Weather Services Section'),
(27, 'Impact Assessment and Applications Section'),
(28, 'Climate and Agrometeorology Data Section'),
(29, 'Research and Development and Training Division'),
(30, 'Astronomy and Space Sciences Section'),
(31, 'Climate and Agrometeorology Research and Development Section'),
(32, 'Hydrometeorology, Tropical Meteorology and Instrument Research and Development'),
(33, 'Numerical Modeling Section'),
(34, 'Training and Public Information Section'),
(35, 'Northern Luzon PAGASA Regional Services Division'),
(36, 'Agno Flood Forecasting and Warning System'),
(37, 'Pampanga Flood Forecasting and Warning System'),
(38, 'Southern Luzon PAGASA Regional Services Division'),
(39, 'Bicol Flood Forecasting and Warning System'),
(40, 'Visayas PAGASA Regional Services Division'),
(41, 'Northern Mindanao PAGASA Regional Services Division'),
(42, 'Southern Mindanao PAGASA Regional Services Division'),
(43, 'Field Stations');

-- Create uploaded_files table (depends on users)
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

-- Create raw_data table (depends on uploaded_files and divisions)
CREATE TABLE IF NOT EXISTS raw_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_no VARCHAR(50),
    file_id INT NULL,
    plantilla_division VARCHAR(100),
    plantilla_section VARCHAR(100) COMMENT 'PLANTILLASECTION/STATION',
    plantilla_division_definition VARCHAR(100) COMMENT 'PLANTILLA DIVISIONDEFINITION',
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
    techcode VARCHAR(50) COMMENT 'Technical Code',
    level INT CHECK (level BETWEEN 1 AND 10),
    step INT CHECK (step BETWEEN 1 AND 10),
    monthly_salary DECIMAL(12,2) COMMENT 'Salary in PHP',
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
    division_id INT,
    FOREIGN KEY (file_id) REFERENCES uploaded_files(id) ON DELETE CASCADE,
    FOREIGN KEY (division_id) REFERENCES divisions(id),
    INDEX idx_is_latest (is_latest),
    INDEX idx_raw_data_plantilla (plantilla_no),
    FULLTEXT INDEX ft_fullname (fullname),
    FULLTEXT INDEX ft_search (plantilla_no, fullname, position_title)
) ENGINE=InnoDB;

-- Create clean_data table (depends on raw_data)
CREATE TABLE IF NOT EXISTS clean_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_no VARCHAR(50) UNIQUE,
    plantilla_division VARCHAR(100),
    plantilla_section VARCHAR(100) COMMENT 'PLANTILLASECTION/STATION',
    plantilla_division_definition VARCHAR(100),
    equivalent_division VARCHAR(100),
    position_title VARCHAR(100),
    item_number VARCHAR(50),
    sg VARCHAR(20),
    date_vacated DATE,
    vacated_due_to VARCHAR(50),
    vacated_by VARCHAR(100),
    remarks VARCHAR(50),
    date_published DATE,
    status ENUM('On-process', 'On-hold', 'Not yet for filling') DEFAULT 'On-process',
    raw_data_id INT,
    FOREIGN KEY (raw_data_id) REFERENCES raw_data(id)
);

-- Create applicants table (independent)
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

-- Create roles table (no dependencies)
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO roles (name, description, permissions) VALUES
('admin', 'Full system access', '{"all": true}'),
('editor', 'Can edit records', '{"read": true, "write": true, "delete": false}'),
('viewer', 'Read-only access', '{"read": true, "write": false, "delete": false}')
ON DUPLICATE KEY UPDATE name=name;

-- Create audit_log table (depends on users)
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
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
