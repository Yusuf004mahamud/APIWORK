-- Run this in your MySQL server to create the database and tables
CREATE DATABASE IF NOT EXISTS taskmanager_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE taskmanager_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  totp_secret VARCHAR(64) DEFAULT NULL,
  totp_enabled TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  date DATE NOT NULL,
  time_from TIME DEFAULT NULL,
  time_to TIME DEFAULT NULL,
  color VARCHAR(20) DEFAULT '#ff6a00',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
