-- Import this into phpMyAdmin. Uses a separate database; never drops the old ems database.
CREATE DATABASE IF NOT EXISTS ems_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ems_portal;
CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 role ENUM('admin','employee') NOT NULL,
 employee_code VARCHAR(30) NULL UNIQUE,
 full_name VARCHAR(120) NOT NULL,
 email VARCHAR(180) NOT NULL UNIQUE,
 phone VARCHAR(30) NOT NULL DEFAULT '',
 department VARCHAR(100) NOT NULL DEFAULT '',
 designation VARCHAR(100) NOT NULL DEFAULT '',
 join_date DATE NULL,
 address TEXT NULL,
 username VARCHAR(80) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 session_version INT UNSIGNED NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS feedback (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 employee_id INT UNSIGNED NOT NULL,
 category VARCHAR(30) NOT NULL DEFAULT 'General',
 message TEXT NOT NULL,
 status ENUM('Pending','Reviewed','Resolved') NOT NULL DEFAULT 'Pending',
 reply TEXT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 CONSTRAINT fk_feedback_employee FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_feedback_status (status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS salaries (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 employee_id INT UNSIGNED NOT NULL,
 salary_month DATE NOT NULL,
 basic DECIMAL(10,2) NOT NULL,
 allowances DECIMAL(10,2) NOT NULL,
 deductions DECIMAL(10,2) NOT NULL,
 net_salary DECIMAL(10,2) NOT NULL,
 payment_status ENUM('Pending','Paid') NOT NULL DEFAULT 'Pending',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_employee_month (employee_id,salary_month),
 CONSTRAINT fk_salary_employee FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT ck_salary_amounts CHECK (basic >= 0 AND allowances >= 0 AND deductions >= 0 AND net_salary >= 0 AND net_salary = basic + allowances - deductions)
) ENGINE=InnoDB;


-- Safe additive upgrade: existing users, salaries and feedback are preserved.
USE ems_portal;
CREATE TABLE IF NOT EXISTS departments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL UNIQUE,
 description VARCHAR(1000) NOT NULL DEFAULT ''
) ENGINE=InnoDB;
INSERT IGNORE INTO departments (name) SELECT DISTINCT department FROM users WHERE department <> '';
CREATE TABLE IF NOT EXISTS attendance (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 employee_id INT UNSIGNED NOT NULL,
 attendance_date DATE NOT NULL,
 status ENUM('Present','Absent','Late','Half Day','Week Off','Leave') NOT NULL,
 check_in TIME NULL,
 check_out TIME NULL,
 UNIQUE KEY uq_attendance_day (employee_id,attendance_date),
 FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS leave_requests (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 employee_id INT UNSIGNED NOT NULL,
 leave_type ENUM('Casual','Sick','Annual','Other') NOT NULL,
 start_date DATE NOT NULL,
 end_date DATE NOT NULL,
 reason VARCHAR(2000) NOT NULL,
 status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
 admin_note VARCHAR(1000) NOT NULL DEFAULT '',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
 CHECK (end_date >= start_date)
) ENGINE=InnoDB;



-- Fresh installations contain only this administrator; existing records are preserved.
INSERT INTO users (role,full_name,email,username,password_hash) SELECT 'admin','Administrator','admin@gmail.com','admin@gmail.com','$2y$10$0Ik3qOWdbCr/uKMXu7NLWOLr2bw32Vs/Ctc4mAWai4qy0yj2G2ecK' WHERE NOT EXISTS (SELECT 1 FROM users);