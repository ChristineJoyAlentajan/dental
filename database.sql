-- Dental Scheduling Management System Database
-- Database Name: dental

CREATE DATABASE IF NOT EXISTS dental;
USE dental;

-- Users Table (For all roles: Patient, Dentist, Staff)
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'dentist', 'staff', 'admin') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    profile_image VARCHAR(255),
    qualification VARCHAR(255),
    specialization VARCHAR(255),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
);

-- Dentist Schedule Table
CREATE TABLE IF NOT EXISTS dentist_schedule (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    dentist_id INT NOT NULL,
    day_of_week VARCHAR(20) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_patients INT DEFAULT 10,
    is_available TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dentist_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_dentist_id (dentist_id)
);

-- Appointments Table
CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    dentist_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    appointment_end_time TIME,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no-show') DEFAULT 'pending',
    notes TEXT,
    reason_for_visit VARCHAR(255),
    is_reminder_sent TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (dentist_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_patient_id (patient_id),
    INDEX idx_dentist_id (dentist_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status)
);

-- Treatment History Table
CREATE TABLE IF NOT EXISTS treatment_history (
    treatment_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    dentist_id INT NOT NULL,
    treatment_type VARCHAR(100) NOT NULL,
    treatment_description TEXT,
    procedure_cost DECIMAL(10, 2),
    payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'unpaid',
    medication_prescribed TEXT,
    follow_up_required TINYINT DEFAULT 0,
    follow_up_date DATE,
    diagnosis TEXT,
    x_ray_images VARCHAR(500),
    notes TEXT,
    tooth_number VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (dentist_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_patient_id (patient_id),
    INDEX idx_dentist_id (dentist_id)
);

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('appointment', 'reminder', 'payment', 'treatment', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_id INT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
);

-- Payments/Billing Table
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    appointment_id INT,
    treatment_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'card', 'online', 'check') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (treatment_id) REFERENCES treatment_history(treatment_id) ON DELETE SET NULL,
    INDEX idx_patient_id (patient_id),
    INDEX idx_payment_status (payment_status)
);

-- Audit Log Table
CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Insert sample data
INSERT INTO users (username, email, password, role, first_name, last_name, phone, gender, is_active)
VALUES 
('admin', 'admin@dental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'User', '9800000000', 'Male', 1),
('dentist1', 'dentist1@dental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dentist', 'Dr. John', 'Smith', '9800000001', 'Male', 1),
('dentist2', 'dentist2@dental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dentist', 'Dr. Sarah', 'Johnson', '9800000002', 'Female', 1),
('patient1', 'patient1@dental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'Mike', 'Wilson', '9800000003', 'Male', 1),
('patient2', 'patient2@dental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'Emma', 'Davis', '9800000004', 'Female', 1),
('staff1', 'staff1@dental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Lisa', 'Brown', '9800000005', 'Female', 1);

-- Default password for all sample users: password

-- Insert sample dentist schedules
INSERT INTO dentist_schedule (dentist_id, day_of_week, start_time, end_time, max_patients)
VALUES 
(2, 'Monday', '09:00:00', '17:00:00', 10),
(2, 'Tuesday', '09:00:00', '17:00:00', 10),
(2, 'Wednesday', '09:00:00', '17:00:00', 10),
(2, 'Thursday', '09:00:00', '17:00:00', 10),
(2, 'Friday', '09:00:00', '16:00:00', 8),
(3, 'Monday', '10:00:00', '18:00:00', 10),
(3, 'Tuesday', '10:00:00', '18:00:00', 10),
(3, 'Wednesday', '10:00:00', '18:00:00', 10),
(3, 'Thursday', '10:00:00', '18:00:00', 10),
(3, 'Friday', '10:00:00', '17:00:00', 8);
