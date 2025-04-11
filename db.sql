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

CREATE TABLE monthly_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    file_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE applicant_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_no VARCHAR(50),
    full_name VARCHAR(255),
    last_name VARCHAR(100),
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    ext_name VARCHAR(20),
    mi VARCHAR(10),
    sex ENUM('Male', 'Female', 'Others'),
    position_title VARCHAR(255),
    item_number VARCHAR(50),
    tech_code VARCHAR(50),
    level VARCHAR(50),
    appointment_status ENUM('Temporary', 'Permanent'),
    sg INT,
    step INT,
    monthly_salary DECIMAL(12,2),
    date_of_birth DATE,
    date_orig_appt DATE,
    date_govt_srvc DATE,
    date_last_promotion DATE,
    date_last_increment DATE,
    date_of_longevity DATE,
    division_id INT,
    file_id INT,
    FOREIGN KEY (division_id) REFERENCES divisions(id),
    FOREIGN KEY (file_id) REFERENCES monthly_files(id)
);

CREATE TABLE division_definitions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    division_name VARCHAR(255) NOT NULL,
    division_code VARCHAR(10) NOT NULL,
    division_order INT NOT NULL
);

-- Insert all 43 divisions
INSERT INTO division_definitions (division_name, division_code, division_order) VALUES
('Office of the Administrator', 'OA', 1),
('Administrative Division', 'AD', 2),
('Human Resources Management and Development Section', 'HRMDS', 3),
('Records Management Section', 'RMS', 4),
('Procurement, Property and General Services Section', 'PPGSS', 5),
('Financial, Planning and Management Division', 'FPMD', 6),
('Accounting Section', 'AS', 7),
('Budget and Planning Section', 'BPS', 8),
('Management Services Section', 'MSS', 9),
('Engineering and Technical Services Division', 'ETSD', 10),
('Meteorological Equipment and Telecommunications Technology Services Section', 'METTSS', 11),
('Meteorological Guides and Standards Section', 'MGSS', 12),
('Mechanical, Electrical and Infrastructure Engineering Section', 'MEIES', 13),
('Weather Division', 'WD', 14),
('Weather Forecasting Section', 'WFS', 15),
('Meteorological Data and Information Exchange Section', 'MDIES', 16),
('Techniques Application and Meteorological Satellite Section', 'TAMSS', 17),
('Aeronautical Meteorological Satellite Section', 'AMSS', 18),
('Marine Meteorological Services Section', 'MMSS', 19),
('Hydro-Meteorological Division', 'HMD', 20),
('Hydrometeorological Data Applications Sections', 'HDAS', 21),
('Flood Forecasting and Warning Section', 'FFWS', 22),
('Hydrometeorological Telemetry Section', 'HTS', 23),
('Climatology and Agrometeorology Division', 'CAD', 24),
('Climate Monitoring and Prediction Section', 'CMPS', 25),
('Farm Weather Services Section', 'FWSS', 26),
('Impact Assessment and Applications Section', 'IAAS', 27),
('Climate and Agrometeorology Data Section', 'CADS', 28),
('Research and Development and Training Division', 'RDTD', 29),
('Astronomy and Space Sciences Section', 'ASSS', 30),
('Climate and Agrometeorology Research and Development Section', 'CARDS', 31),
('Hydrometeorology, Tropical Meteorology and Instrument Research and Development', 'HTMIRD', 32),
('Numerical Modeling Section', 'NMS', 33),
('Training and Public Information Section', 'TPIS', 34),
('Northern Luzon PAGASA Regional Services Division', 'NLPRSD', 35),
('Agno Flood Forecasting and Warning System', 'AFFWS', 36),
('Pampanga Flood Forecasting and Warning System', 'PFFWS', 37),
('Southern Luzon PAGASA Regional Services Division', 'SLPRSD', 38),
('Bicol Flood Forecasting and Warning System', 'BFFWS', 39),
('Visayas PAGASA Regional Services Division', 'VPRSD', 40),
('Northern Mindanao PAGASA Regional Services Division', 'NMPRSD', 41),
('Southern Mindanao PAGASA Regional Services Division', 'SMPRSD', 42),
('Field Stations', 'FS', 43);

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

-- Create status_tracking table
CREATE TABLE IF NOT EXISTS status_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_no VARCHAR(50),
    status ENUM('On-Process', 'On-Hold', 'Not Yet for Filing') NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Create file_history table
CREATE TABLE IF NOT EXISTS file_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT,
    action ENUM('Created', 'Edited', 'Deleted') NOT NULL,
    user_id INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES uploaded_files(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE password_resets (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Create indices for better performance
CREATE INDEX idx_applicants_fullname ON applicants(fullname);
CREATE INDEX idx_applicants_id_no ON applicants(id_no);

-- Insert default admin account
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Default password is 'password', change immediately after first login

SET SQL_SAFE_UPDATES = 1;
