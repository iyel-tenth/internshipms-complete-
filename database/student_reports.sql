-- Table to store student report uploads
CREATE TABLE IF NOT EXISTS student_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    doc_type VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at DATETIME NOT NULL,
    UNIQUE KEY unique_report (student_id, doc_type)
);
