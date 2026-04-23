CREATE DATABASE IF NOT EXISTS csv_explorer_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE csv_explorer_db;

CREATE TABLE uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT,
    total_rows INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE analysis_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,
    total_rows INT DEFAULT 0,
    total_columns INT DEFAULT 0,
    numeric_columns TEXT,
    text_columns TEXT,
    stats JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(id)
);