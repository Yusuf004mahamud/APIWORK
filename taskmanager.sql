
-- Create the database
DROP DATABASE IF EXISTS taskmanager;
CREATE DATABASE taskmanager CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE taskmanager;

-- ============================
-- 1. USERS TABLE
-- ============================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(120),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- 2. ADMINS TABLE
-- ============================
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(120),
  role ENUM('super_admin','admin') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- 3. ACTIVITY CATEGORIES
-- ============================
CREATE TABLE activity_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT
) ENGINE=InnoDB;

-- ============================
-- 4. ACTIVITIES TABLE
-- ============================
CREATE TABLE activities (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  date DATE NOT NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  status ENUM('pending','in_progress','done','cancelled') DEFAULT 'pending',
  priority TINYINT DEFAULT 3,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES activity_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================
-- 5. NOTIFICATIONS TABLE
-- ============================
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  status ENUM('unread','read') DEFAULT 'unread',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================
-- 6. PASSWORD RESETS TABLE
-- ============================
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  reset_token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================
-- 7. ADMIN LOGS TABLE (FIXED)
-- ============================
CREATE TABLE admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  action VARCHAR(255) NOT NULL,
  description TEXT,
  log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================
-- 8. SAMPLE DATA
-- ============================
INSERT INTO users (username, email, password_hash, full_name) VALUES
('alice','alice@example.com','hash_alice','Alice Student'),
('bob','bob@example.com','hash_bob','Bob Student');

INSERT INTO admins (username, email, password_hash, full_name, role) VALUES
('admin1','admin1@example.com','hash_admin','Super Admin','super_admin');

INSERT INTO activity_categories (category_name, description) VALUES
('Study', 'Tasks related to academic work'),
('Exercise', 'Physical fitness activities'),
('Projects', 'Group or personal projects');

INSERT INTO activities (user_id, category_id, title, description, date, start_time, end_time, status, priority)
VALUES
(1, 1, 'Math revision', 'Revise algebra chapter 3', '2025-10-15', '09:00:00', '10:00:00', 'pending', 2),
(1, 3, 'Project meeting', 'Discuss progress with team', '2025-10-15', '13:00:00', '14:00:00', 'in_progress', 1),
(2, 2, 'Morning run', 'Jogging at the park', '2025-10-15', '06:30:00', '07:00:00', 'done', 2);

INSERT INTO notifications (user_id, message) VALUES
(1, 'Your project meeting starts in 30 minutes!'),
(2, 'Time for your daily run!');

-- ============================
-- 9. STORED PROCEDURES
-- ============================

DELIMITER $$

-- Add user
CREATE PROCEDURE sp_add_user(
  IN p_username VARCHAR(60),
  IN p_email VARCHAR(255),
  IN p_password_hash VARCHAR(255),
  IN p_full_name VARCHAR(120)
)
BEGIN
  INSERT INTO users (username, email, password_hash, full_name)
  VALUES (p_username, p_email, p_password_hash, p_full_name);
END $$

-- Add activity
CREATE PROCEDURE sp_add_activity(
  IN p_user_id INT,
  IN p_category_id INT,
  IN p_title VARCHAR(200),
  IN p_description TEXT,
  IN p_date DATE,
  IN p_start_time TIME,
  IN p_end_time TIME,
  IN p_priority TINYINT
)
BEGIN
  INSERT INTO activities (user_id, category_id, title, description, date, start_time, end_time, priority)
  VALUES (p_user_id, p_category_id, p_title, p_description, p_date, p_start_time, p_end_time, p_priority);
END $$

-- Change user password
CREATE PROCEDURE sp_change_user_password(
  IN p_user_id INT,
  IN p_new_password_hash VARCHAR(255)
)
BEGIN
  UPDATE users 
  SET password_hash = p_new_password_hash,
      updated_at = CURRENT_TIMESTAMP
  WHERE id = p_user_id;
END $$

-- Log admin action
CREATE PROCEDURE sp_log_action(
  IN p_admin_id INT,
  IN p_action VARCHAR(255),
  IN p_description TEXT
)
BEGIN
  INSERT INTO admin_logs (admin_id, action, description)
  VALUES (p_admin_id, p_action, p_description);
END $$

DELIMITER ;

-- ============================
-- 10. VIEW FOR USER ACTIVITIES
-- ============================
CREATE OR REPLACE VIEW user_activities_view AS
SELECT 
  u.id AS user_id,
  u.username,
  a.id AS activity_id,
  a.title,
  a.description,
  a.date,
  a.start_time,
  a.end_time,
  a.status,
  c.category_name,
  a.priority
FROM users u
JOIN activities a ON u.id = a.user_id
LEFT JOIN activity_categories c ON a.category_id = c.id;

-- ============================
-- 11. PRIVILEGE SETUP 
-- ============================
-- Create MySQL accounts with limited access
CREATE USER IF NOT EXISTS 'task_user'@'localhost' IDENTIFIED BY 'user123';
CREATE USER IF NOT EXISTS 'task_admin'@'localhost' IDENTIFIED BY 'admin123';

-- User permissions (can access only their tables)
GRANT SELECT, INSERT, UPDATE, DELETE ON taskmanager.users TO 'task_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON taskmanager.activities TO 'task_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON taskmanager.notifications TO 'task_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON taskmanager.password_resets TO 'task_user'@'localhost';

-- Admin permissions (no access to user activities)
GRANT SELECT, INSERT, UPDATE, DELETE ON taskmanager.admins TO 'task_admin'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON taskmanager.admin_logs TO 'task_admin'@'localhost';
GRANT EXECUTE ON PROCEDURE taskmanager.sp_log_action TO 'task_admin'@'localhost';

FLUSH PRIVILEGES;
