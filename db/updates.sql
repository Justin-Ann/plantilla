-- Add new columns to raw_data
ALTER TABLE raw_data ADD COLUMN IF NOT EXISTS monthly_salary_formatted VARCHAR(50);
ALTER TABLE raw_data ADD COLUMN IF NOT EXISTS division_id INT;

-- Add indices for better performance
CREATE INDEX idx_division ON raw_data(plantilla_division_definition);
CREATE INDEX idx_upload_date ON raw_data(upload_date);
CREATE INDEX idx_applicant_name ON applicants(fullname);
