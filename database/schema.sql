-- CCS Sit-In Monitoring System Database Schema
-- Import this file in phpMyAdmin (XAMPP) after creating database

CREATE DATABASE IF NOT EXISTS ccs_sit_in_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ccs_sit_in_db;

-- Administrators
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    middle_name VARCHAR(80) DEFAULT NULL,
    last_name VARCHAR(80) NOT NULL,
    course VARCHAR(50) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default-avatar.png',
    remaining_sessions INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_students_search (id_number, first_name, last_name, course, year_level)
);

-- Laboratories
CREATE TABLE laboratories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab_name VARCHAR(100) NOT NULL UNIQUE,
    pc_count INT NOT NULL DEFAULT 50,
    is_active TINYINT(1) DEFAULT 1
);

-- Announcements
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Sit-In Reservations / Records
CREATE TABLE sit_in_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sit_in_no VARCHAR(30) NOT NULL UNIQUE,
    student_id INT NOT NULL,
    purpose VARCHAR(100) NOT NULL,
    laboratory_id INT NOT NULL,
    pc_number INT NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time_in TIME NOT NULL,
    time_in DATETIME DEFAULT NULL,
    time_out DATETIME DEFAULT NULL,
    duration_minutes INT DEFAULT NULL,
    status ENUM(
        'Reserved',
        'User Cancelled',
        'Rejected',
        'Approved',
        'On Going',
        'Completed'
    ) NOT NULL DEFAULT 'Reserved',
    booked_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_by INT DEFAULT NULL,
    is_walk_in TINYINT(1) DEFAULT 0,
    session_deducted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (laboratory_id) REFERENCES laboratories(id),
    FOREIGN KEY (approved_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_records_student (student_id),
    INDEX idx_records_status (status),
    INDEX idx_records_lab_pc (laboratory_id, pc_number, scheduled_date)
);

-- Feedback
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sit_in_record_id INT NOT NULL UNIQUE,
    student_id INT NOT NULL,
    laboratory_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sit_in_record_id) REFERENCES sit_in_records(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (laboratory_id) REFERENCES laboratories(id)
);

-- Seed default admin — run install.php to hash password admin123
INSERT INTO admins (username, password, full_name) VALUES
('admin', '$2y$10$placeholder_run_install_php', 'Administrator');

-- Default laboratories
INSERT INTO laboratories (lab_name, pc_count) VALUES
('Computer Lab 1', 50),
('Computer Lab 2', 50),
('Computer Lab 3', 50),
('Programming Lab', 50);

-- Sample announcement
INSERT INTO announcements (title, content, start_date, end_date) VALUES
('Welcome to CCS Sit-In Monitoring',
 'Please observe laboratory rules at all times. Reserve your slot early and arrive on time for your scheduled session.',
 CURDATE(),
 DATE_ADD(CURDATE(), INTERVAL 90 DAY));
