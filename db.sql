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