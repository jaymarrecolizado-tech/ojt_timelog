-- OJT Time Log Management System - MySQL Schema
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS ojt_timelog
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ojt_timelog;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'guard', 'admin', 'super_admin') NOT NULL DEFAULT 'student',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    email_verified BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_is_active (is_active)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: students
-- ============================================
CREATE TABLE students (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL UNIQUE,
    student_id_no VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10),
    department VARCHAR(150) NOT NULL,
    program VARCHAR(150) NOT NULL,
    company VARCHAR(200),
    company_address VARCHAR(300),
    supervisor_name VARCHAR(200),
    ojt_start DATE,
    ojt_end DATE,
    required_hours DECIMAL(7,2) NOT NULL DEFAULT 500.00,
    contact_no VARCHAR(20),
    status ENUM('pending', 'active', 'completed', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_students_user_id (user_id),
    INDEX idx_students_status (status),
    INDEX idx_students_department (department),
    INDEX idx_students_student_id_no (student_id_no)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: locations (QR stations/gates)
-- ============================================
CREATE TABLE locations (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(100) NOT NULL,
    description VARCHAR(300),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    radius_meters INT DEFAULT 100,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    secret_key VARCHAR(128) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_locations_is_active (is_active)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: time_logs
-- ============================================
CREATE TABLE time_logs (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    student_id CHAR(36) NOT NULL,
    log_type ENUM('IN', 'OUT') NOT NULL,
    log_category ENUM('AM', 'PM') NOT NULL,
    timestamp DATETIME NOT NULL,
    date DATE NOT NULL,
    qr_token_hash VARCHAR(64),
    location_id CHAR(36),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    device_info VARCHAR(500),
    ip_address VARCHAR(45),
    is_manual BOOLEAN NOT NULL DEFAULT FALSE,
    is_flagged BOOLEAN NOT NULL DEFAULT FALSE,
    flag_reason VARCHAR(200),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    INDEX idx_time_logs_student_date (student_id, date),
    INDEX idx_time_logs_date (date),
    INDEX idx_time_logs_timestamp (timestamp),
    UNIQUE KEY idx_unique_log_entry (student_id, date, log_type, log_category)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: log_overrides (audit trail)
-- ============================================
CREATE TABLE log_overrides (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    time_log_id CHAR(36),
    student_id CHAR(36) NOT NULL,
    admin_id CHAR(36) NOT NULL,
    action ENUM('CREATE', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    reason TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (time_log_id) REFERENCES time_logs(id) ON DELETE SET NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_overrides_student (student_id),
    INDEX idx_overrides_admin (admin_id)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: holidays
-- ============================================
CREATE TABLE holidays (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    date DATE NOT NULL,
    name VARCHAR(200) NOT NULL,
    type ENUM('regular', 'special', 'company') DEFAULT 'regular',
    is_recurring BOOLEAN NOT NULL DEFAULT FALSE,
    created_by CHAR(36),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_holidays_date (date)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: system_settings
-- ============================================
CREATE TABLE system_settings (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    data_type ENUM('string', 'number', 'boolean', 'json') NOT NULL DEFAULT 'string',
    description VARCHAR(300),
    updated_by CHAR(36),
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default settings
INSERT INTO system_settings (setting_key, setting_value, data_type, description) VALUES
('qr_rotation_seconds', '30', 'number', 'QR code rotation interval in seconds'),
('max_scans_per_day', '4', 'number', 'Maximum scans allowed per student per day'),
('grace_period_minutes', '15', 'number', 'Minutes after schedule start to not be marked late'),
('schedule_am_start', '08:00', 'string', 'AM schedule start time'),
('schedule_am_end', '12:00', 'string', 'AM schedule end time'),
('schedule_pm_start', '13:00', 'string', 'PM schedule start time'),
('schedule_pm_end', '17:00', 'string', 'PM schedule end time'),
('geolocation_required', 'false', 'boolean', 'Whether GPS validation is required'),
('geolocation_max_distance', '200', 'number', 'Max allowed distance from site in meters'),
('scan_debounce_seconds', '60', 'number', 'Minimum seconds between scans for same student');

-- ============================================
-- TABLE: activity_logs (system audit)
-- ============================================
CREATE TABLE activity_logs (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36),
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id CHAR(36),
    details JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_logs_user (user_id),
    INDEX idx_activity_logs_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: refresh_tokens
-- ============================================
CREATE TABLE refresh_tokens (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    revoked BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_refresh_tokens_user (user_id),
    INDEX idx_refresh_tokens_hash (token_hash)
) ENGINE=InnoDB;

-- ============================================
-- TABLE: password_reset_tokens
-- ============================================
CREATE TABLE password_reset_tokens (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_reset_tokens_user (user_id),
    INDEX idx_password_reset_tokens_hash (token_hash)
) ENGINE=InnoDB;

-- ============================================
-- Insert default location
-- ============================================
INSERT INTO locations (id, name, description, secret_key) VALUES
(UUID(), 'Main Gate', 'Primary entrance/exit point', 'default-secret-key-change-me');

-- ============================================
-- Create default super admin (password: Admin@123)
-- ============================================
INSERT INTO users (id, email, password_hash, role, is_active, email_verified) VALUES
(UUID(), 'admin@ojt-tlms.test', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4wqO.1LPMuLqLkzq', 'super_admin', TRUE, TRUE);
