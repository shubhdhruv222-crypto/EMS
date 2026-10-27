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

-- Support weekly off and leave attendance without changing existing records.
ALTER TABLE attendance MODIFY status ENUM('Present','Absent','Late','Half Day','Week Off','Leave') NOT NULL;
