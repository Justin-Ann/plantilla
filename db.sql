-- Create database
CREATE DATABASE IF NOT EXISTS plantilla_management;
USE plantilla_management;

-- Create tables
CREATE TABLE raw_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_no VARCHAR(50),
    plantilla_division VARCHAR(100),
    plantilla_sectiondefinition VARCHAR(100),
    equivalent_division VARCHAR(100),
    position_title VARCHAR(100),
    item_number VARCHAR(50),
    sg VARCHAR(20),
    date_vacated DATE,
    vacated_due_to TEXT,
    vacated_by VARCHAR(100),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_latest BOOLEAN DEFAULT TRUE
);

CREATE TABLE clean_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_no VARCHAR(50) UNIQUE,  -- Ensure plantilla_no is unique
    plantilla_division VARCHAR(100),
    plantilla_sectiondefinition VARCHAR(100),
    equivalent_division VARCHAR(100),
    position_title VARCHAR(100),
    item_number VARCHAR(50),
    sg VARCHAR(20),
    date_vacated DATE,
    vacated_due_to TEXT,
    vacated_by VARCHAR(100),
    remarks VARCHAR(50),
    date_published DATE,
    status VARCHAR(20),
    raw_data_id INT,
    FOREIGN KEY (raw_data_id) REFERENCES raw_data(id)
);

INSERT INTO clean_data (
    plantilla_no, plantilla_division, plantilla_sectiondefinition, 
    equivalent_division, position_title, item_number, sg, 
    date_vacated, vacated_due_to, vacated_by, remarks, status, raw_data_id
) 
SELECT 
    r.plantilla_no, r.plantilla_division, r.plantilla_sectiondefinition, 
    r.equivalent_division, r.position_title, r.item_number, r.sg, 
    r.date_vacated, r.vacated_due_to, r.vacated_by, 'On-process', 'On-process', r.id
FROM raw_data AS r
WHERE r.is_latest = TRUE 
AND r.id = (SELECT MAX(id) FROM raw_data WHERE plantilla_no = r.plantilla_no)
ON DUPLICATE KEY UPDATE 
    plantilla_division = r.plantilla_division,
    plantilla_sectiondefinition = r.plantilla_sectiondefinition,
    equivalent_division = r.equivalent_division,
    position_title = r.position_title,
    item_number = r.item_number,
    sg = r.sg,
    date_vacated = r.date_vacated,
    vacated_due_to = r.vacated_due_to,
    vacated_by = r.vacated_by,
    remarks = 'On-process',
    status = 'On-process',
    raw_data_id = r.id;

CREATE TABLE applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100),
    sex VARCHAR(10),
    position_title VARCHAR(100),
    techcode VARCHAR(50),
    date_of_birth DATE,
    date_last_promotion DATE,
    date_last_increment DATE,
    date_of_longevity DATE,
    appointment_status VARCHAR(20),
    plantilla_no VARCHAR(50),
    FOREIGN KEY (plantilla_no) REFERENCES clean_data(plantilla_no)  -- Now it references a unique column
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20)
);

-- Insert admin user
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$8OxDJT4rZQp0tOHXGIX0HeWJA9UR.YG7TAq3OPz1pLzz7KxVISB2a', 'admin');

SELECT * FROM clean_data WHERE plantilla_no = '826';

SELECT plantilla_no, COUNT(*) 
FROM raw_data 
WHERE is_latest = TRUE 
GROUP BY plantilla_no 
HAVING COUNT(*) > 1;

ALTER TABLE applicants MODIFY COLUMN plantilla_no VARCHAR(50) NULL;
ALTER TABLE applicants DROP FOREIGN KEY applicants_ibfk_1;

CREATE TABLE uploaded_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    original_filename VARCHAR(255),
    file_path VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    month_year VARCHAR(7),  -- Format: YYYY-MM
    status VARCHAR(20) DEFAULT 'active',
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Check if last_modified column exists and add it if it doesn't
ALTER TABLE uploaded_files 
ADD COLUMN last_modified TIMESTAMP NULL DEFAULT NULL;

-- Update existing records to set last_modified same as upload_date
UPDATE uploaded_files 
SET last_modified = COALESCE(last_modified, upload_date);

// Add search indexes
ALTER TABLE raw_data ADD FULLTEXT INDEX ft_fullname (fullname);
ALTER TABLE raw_data ADD FULLTEXT INDEX ft_search (plantilla_no, fullname, position_title);

-- Add indices for better performance
ALTER TABLE raw_data ADD INDEX idx_file_search (file_id, is_latest);

-- Add vacancy reason enum
ALTER TABLE raw_data 
MODIFY COLUMN vacated_due_to ENUM(
    'PROMOTION', 
    'COMPULSORY RETIREMENT', 
    'RESIGNATION', 
    'SWAPPING OF ITEM', 
    'TRANSFER'
) NULL;

-- Add appointment status enum
ALTER TABLE raw_data 
MODIFY COLUMN appointment_status ENUM('TEMPORARY', 'PERMANENT') DEFAULT 'PERMANENT';

-- Add constraints for numeric fields
ALTER TABLE raw_data 
ADD CONSTRAINT chk_sg CHECK (CAST(sg AS UNSIGNED) BETWEEN 1 AND 100),
ADD CONSTRAINT chk_step CHECK (CAST(step AS UNSIGNED) BETWEEN 1 AND 10),
ADD CONSTRAINT chk_level CHECK (CAST(level AS UNSIGNED) BETWEEN 1 AND 10);

-- Add currency formatting
ALTER TABLE raw_data 
MODIFY COLUMN monthly_salary DECIMAL(12,2);

-- Update clean_data status options
ALTER TABLE clean_data 
MODIFY COLUMN status ENUM('On-process', 'On-hold', 'Not yet for filling') 
DEFAULT 'On-process';

-- Add last_edited field to track modifications
ALTER TABLE raw_data 
ADD COLUMN last_edited TIMESTAMP NULL DEFAULT NULL;

-- Add file_id to raw_data table if not exists
ALTER TABLE raw_data 
ADD COLUMN file_id INT NULL,
ADD FOREIGN KEY (file_id) REFERENCES uploaded_files(id) ON DELETE CASCADE;

-- Add status tracking to uploaded_files
ALTER TABLE uploaded_files
ADD COLUMN processing_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
ADD COLUMN error_message TEXT NULL;

-- Update indexes for better performance
CREATE INDEX idx_file_latest ON raw_data(file_id, is_latest);
CREATE INDEX idx_month_status ON uploaded_files(month_year, status);
CREATE INDEX idx_raw_data_plantilla ON raw_data(plantilla_no);