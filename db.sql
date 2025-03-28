SET SQL_SAFE_UPDATES = 0;

-- Create database
CREATE DATABASE IF NOT EXISTS plantilla_management;
USE plantilla_management;

-- Create tables

-- First create users table since it's referenced by other tables
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20)
);

-- Insert admin user
INSERT IGNORE INTO users (username, password, role) 
VALUES ('admin', '$2y$10$8OxDJT4rZQp0tOHXGIX0HeWJA9UR.YG7TAq3OPz1pLzz7KxVISB2a', 'admin');

-- Then create uploaded_files table since it's referenced by raw_data
CREATE TABLE IF NOT EXISTS uploaded_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    original_filename VARCHAR(255),
    file_path VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    month_year VARCHAR(7),  -- Format: YYYY-MM
    status VARCHAR(20) DEFAULT 'active',
    user_id INT,
    processing_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    last_modified TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Then create raw_data table with all indexes and constraints at creation time
CREATE TABLE IF NOT EXISTS raw_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plantilla_no VARCHAR(50),
    file_id INT NULL,
    plantilla_division VARCHAR(100),
    plantilla_sectiondefinition VARCHAR(100),
    equivalent_division VARCHAR(100),
    position_title VARCHAR(100),
    item_number VARCHAR(50),
    sg VARCHAR(20),
    date_vacated DATE,
    vacated_due_to TEXT,
    vacated_by VARCHAR(100),
    fullname VARCHAR(100),
    last_name VARCHAR(50),
    first_name VARCHAR(50),
    middle_name VARCHAR(50),
    ext_name VARCHAR(20),
    mi VARCHAR(10),
    sex VARCHAR(10),
    techcode VARCHAR(50),
    level VARCHAR(20),
    step VARCHAR(10),
    monthly_salary DECIMAL(12,2),
    date_of_birth DATE,
    date_orig_appt DATE,
    date_govt_srvc DATE,
    date_last_promotion DATE,
    date_last_increment DATE,
    date_of_longevity DATE,
    id_no VARCHAR(50),
    appointment_status ENUM('TEMPORARY', 'PERMANENT') DEFAULT 'PERMANENT',
    last_edited TIMESTAMP NULL DEFAULT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_latest BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (file_id) REFERENCES uploaded_files(id) ON DELETE CASCADE,
    INDEX idx_is_latest (is_latest),
    INDEX idx_raw_data_plantilla (plantilla_no),
    FULLTEXT INDEX ft_fullname (fullname),
    FULLTEXT INDEX ft_search (plantilla_no, fullname, position_title)
) ENGINE=InnoDB;

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

ALTER TABLE applicants MODIFY COLUMN plantilla_no VARCHAR(50) NULL;
ALTER TABLE applicants DROP FOREIGN KEY applicants_ibfk_1;

-- Check if last_modified column exists and add it if it doesn't
SET @dbname = 'plantilla_management';
SET @tablename = 'uploaded_files';
SET @columnname = 'last_modified';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (COLUMN_NAME = @columnname)
      AND (TABLE_NAME = @tablename)
      AND (TABLE_SCHEMA = @dbname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TIMESTAMP NULL DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing records to set last_modified same as upload_date
UPDATE uploaded_files 
SET last_modified = COALESCE(last_modified, upload_date)
WHERE id > 0;

-- Check and add columns to raw_data table
SET @dbname = 'plantilla_management';
SET @tablename = 'raw_data';

-- Function to add column if it doesn't exist
DELIMITER //
CREATE PROCEDURE add_column_if_not_exists(
    IN p_table VARCHAR(100),
    IN p_column VARCHAR(100),
    IN p_definition TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = p_table
        AND COLUMN_NAME = p_column
    ) THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table, ' ADD COLUMN ', p_column, ' ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- Add columns using the procedure
CALL add_column_if_not_exists('raw_data', 'fullname', 'VARCHAR(100) AFTER vacated_by');
CALL add_column_if_not_exists('raw_data', 'last_name', 'VARCHAR(50)');
CALL add_column_if_not_exists('raw_data', 'first_name', 'VARCHAR(50)');
CALL add_column_if_not_exists('raw_data', 'middle_name', 'VARCHAR(50)');
CALL add_column_if_not_exists('raw_data', 'ext_name', 'VARCHAR(20)');
CALL add_column_if_not_exists('raw_data', 'mi', 'VARCHAR(10)');
CALL add_column_if_not_exists('raw_data', 'sex', 'VARCHAR(10)');
CALL add_column_if_not_exists('raw_data', 'techcode', 'VARCHAR(50)');
CALL add_column_if_not_exists('raw_data', 'level', 'VARCHAR(20)');
CALL add_column_if_not_exists('raw_data', 'step', 'VARCHAR(10)');
CALL add_column_if_not_exists('raw_data', 'monthly_salary', 'DECIMAL(12,2)');
CALL add_column_if_not_exists('raw_data', 'date_of_birth', 'DATE');
CALL add_column_if_not_exists('raw_data', 'date_orig_appt', 'DATE');
CALL add_column_if_not_exists('raw_data', 'date_govt_srvc', 'DATE');
CALL add_column_if_not_exists('raw_data', 'date_last_promotion', 'DATE');
CALL add_column_if_not_exists('raw_data', 'date_last_increment', 'DATE');
CALL add_column_if_not_exists('raw_data', 'date_of_longevity', 'DATE');
CALL add_column_if_not_exists('raw_data', 'id_no', 'VARCHAR(50)');

-- Clean up - drop the procedure
DROP PROCEDURE IF EXISTS add_column_if_not_exists;

-- Drop any existing indexes using proper MySQL syntax
DELIMITER //
CREATE PROCEDURE drop_index_if_exists(
    IN idx_name VARCHAR(64),
    IN tbl_name VARCHAR(64)
) 
BEGIN 
    IF ((SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE table_schema = DATABASE()
        AND table_name = tbl_name
        AND index_name = idx_name) > 0) THEN
        SET @sqlstmt = CONCAT('DROP INDEX ', idx_name, ' ON ', tbl_name);
        PREPARE st FROM @sqlstmt;
        EXECUTE st;
        DEALLOCATE PREPARE st;
    END IF;
END//
DELIMITER ;

-- Drop existing indexes safely
CALL drop_index_if_exists('ft_fullname', 'raw_data');
CALL drop_index_if_exists('ft_search', 'raw_data');
CALL drop_index_if_exists('idx_file_search', 'raw_data');
CALL drop_index_if_exists('idx_raw_data_plantilla', 'raw_data');
CALL drop_index_if_exists('idx_month_status', 'uploaded_files');

-- Clean up procedure
DROP PROCEDURE IF EXISTS drop_index_if_exists;

-- Create indexes (only once)
ALTER TABLE raw_data ADD FULLTEXT INDEX ft_fullname (fullname);
ALTER TABLE raw_data ADD FULLTEXT INDEX ft_search (plantilla_no, fullname, position_title);
ALTER TABLE raw_data ADD INDEX idx_file_search (file_id, is_latest);
ALTER TABLE raw_data ADD INDEX idx_raw_data_plantilla (plantilla_no);
ALTER TABLE uploaded_files ADD INDEX idx_month_status (month_year, status);

-- Create a procedure to safely add indexes
DELIMITER //
CREATE PROCEDURE add_index_if_not_exists(
    IN p_table VARCHAR(64),
    IN p_index VARCHAR(64),
    IN p_columns VARCHAR(64)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE table_schema = DATABASE()
        AND table_name = p_table
        AND index_name = p_index
    ) THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table, ' ADD INDEX ', p_index, ' (', p_columns, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- Add indexes using the procedure
CALL add_index_if_not_exists('raw_data', 'idx_file_search', 'file_id, is_latest');
CALL add_index_if_not_exists('raw_data', 'idx_raw_data_plantilla', 'plantilla_no');
CALL add_index_if_not_exists('uploaded_files', 'idx_month_status', 'month_year, status');

-- Clean up
DROP PROCEDURE IF EXISTS add_index_if_not_exists;

-- Add constraints for numeric fields safely using procedure
DELIMITER //
CREATE PROCEDURE add_constraint_if_not_exists(
    IN p_table VARCHAR(64),
    IN p_constraint VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = p_table
        AND CONSTRAINT_NAME = p_constraint
    ) THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table, 
            ' ADD CONSTRAINT ', p_constraint, 
            ' CHECK ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- Add constraints using procedure
CALL add_constraint_if_not_exists('raw_data', 'chk_sg', '(CAST(sg AS UNSIGNED) BETWEEN 1 AND 100)');
CALL add_constraint_if_not_exists('raw_data', 'chk_step', '(CAST(step AS UNSIGNED) BETWEEN 1 AND 10)');
CALL add_constraint_if_not_exists('raw_data', 'chk_level', '(CAST(level AS UNSIGNED) BETWEEN 1 AND 10)');

-- Clean up
DROP PROCEDURE IF EXISTS add_constraint_if_not_exists;

-- Add currency formatting if not exists
ALTER TABLE raw_data 
MODIFY COLUMN monthly_salary DECIMAL(12,2);

-- Update clean_data status options if not exists
ALTER TABLE clean_data 
MODIFY COLUMN status ENUM('On-process', 'On-hold', 'Not yet for filling') 
DEFAULT 'On-process';

-- Create procedure for adding timestamp columns safely
DELIMITER //
CREATE PROCEDURE add_timestamp_if_not_exists(
    IN p_table VARCHAR(64),
    IN p_column VARCHAR(64)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = p_table
        AND COLUMN_NAME = p_column
    ) THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table, ' ADD COLUMN ', p_column, ' TIMESTAMP NULL DEFAULT NULL');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- Add timestamp columns safely
CALL add_timestamp_if_not_exists('raw_data', 'last_edited');

-- Clean up
DROP PROCEDURE IF EXISTS add_timestamp_if_not_exists;

-- Add file_id to raw_data table if not exists
ALTER TABLE raw_data 
ADD COLUMN IF NOT EXISTS file_id INT NULL,
ADD CONSTRAINT IF NOT EXISTS fk_file_id 
FOREIGN KEY (file_id) REFERENCES uploaded_files(id) ON DELETE CASCADE;

-- Add status tracking to uploaded_files if not exists
ALTER TABLE uploaded_files
ADD COLUMN IF NOT EXISTS processing_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS error_message TEXT NULL;

-- Update raw_data table to include all required columns
ALTER TABLE raw_data 
ADD COLUMN fullname VARCHAR(100) AFTER vacated_by,
ADD COLUMN last_name VARCHAR(50),
ADD COLUMN first_name VARCHAR(50),
ADD COLUMN middle_name VARCHAR(50),
ADD COLUMN ext_name VARCHAR(20),
ADD COLUMN mi VARCHAR(10),
ADD COLUMN sex VARCHAR(10),
ADD COLUMN techcode VARCHAR(50),
ADD COLUMN level VARCHAR(20),
ADD COLUMN step VARCHAR(10),
ADD COLUMN monthly_salary DECIMAL(12,2),
ADD COLUMN date_of_birth DATE,
ADD COLUMN date_orig_appt DATE,
ADD COLUMN date_govt_srvc DATE,
ADD COLUMN date_last_promotion DATE,
ADD COLUMN date_last_increment DATE,
ADD COLUMN date_of_longevity DATE,
ADD COLUMN id_no VARCHAR(50);

-- Clean up
DROP PROCEDURE IF EXISTS add_timestamp_column_if_not_exists;

-- Update raw_data table with proper column names and constraints
ALTER TABLE raw_data 
MODIFY COLUMN sex ENUM('Male', 'Female', 'Others') NULL,
MODIFY COLUMN appointment_status ENUM('Temporary', 'Permanent') DEFAULT 'Permanent',
MODIFY COLUMN sg INT CHECK (sg BETWEEN 1 AND 100),
MODIFY COLUMN step INT CHECK (step BETWEEN 1 AND 10),
MODIFY COLUMN level INT CHECK (level BETWEEN 1 AND 10),
MODIFY COLUMN monthly_salary DECIMAL(12,2),
MODIFY COLUMN vacated_due_to ENUM(
    'PROMOTION',
    'COMPULSORY RETIREMENT',
    'RESIGNATION',
    'SWAPPING OF ITEM',
    'TRANSFER'
) NULL;

-- Add indices for searching
ALTER TABLE raw_data 
ADD FULLTEXT INDEX ft_filename_search (plantilla_no, fullname, position_title);

-- Add last_modified tracking
ALTER TABLE raw_data
ADD COLUMN last_modified TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- Re-enable safe updates
SET SQL_SAFE_UPDATES = 1;