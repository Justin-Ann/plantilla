CREATE TABLE IF NOT EXISTS monthly_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    month_year VARCHAR(7) NOT NULL,
    uploaded_by INT,
    file_size INT,
    file_type VARCHAR(50),
    status ENUM('active', 'archived', 'deleted') DEFAULT 'active',
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
