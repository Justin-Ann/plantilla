CREATE DATABASE IF NOT EXISTS plantilla_db;
USE plantilla_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    verification_expires TIMESTAMP,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE divisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL
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

CREATE TABLE status_tracking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    record_id INT,
    status ENUM('On-Process', 'On-Hold', 'Not Yet for Filling') NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_id) REFERENCES applicant_records(id)
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
