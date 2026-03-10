-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 06, 2026 at 02:34 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4
USE `prcsystem_db`;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `prcsystem_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `prcsystem_db`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `department`;
CREATE TABLE department(
  `dept_id` INT AUTO_INCREMENT PRIMARY KEY,
  `dept_name` VARCHAR(45) NOT NULL,
  `dept_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `dept_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB; 

INSERT INTO `department` (`dept_id`, `dept_name`, `dept_created_at`, `dept_updated_at`) VALUES
(1, 'Finance and Administrative Division', '2026-03-03 14:55:58', '2026-03-03 14:55:58'),
(2, 'Regulations', '2026-03-03 14:55:58', '2026-03-03 14:55:58'),
(3, 'Supply Office', '2026-03-03 15:48:35', '2026-03-03 15:48:35');

DROP TABLE IF EXISTS `document_type`;
CREATE TABLE document_type (
    `doc_type_id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_type_name` VARCHAR(50) NOT NULL,
    `doc_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `doc_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB; 

INSERT INTO `document_type` (`doc_type_id`, `document_type_name`, `doc_created_at`, `doc_updated_at`) VALUES
(1, 'Board Resolution', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(2, 'Licensure Exam Application', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(3, 'Professional ID Renewal', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(4, 'Formal Legal Complaint', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(5, 'Memorandum Circular', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(6, 'Certification of Good Standing', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(7, 'Disbursement Voucher', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(8, 'Office Order', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(9, 'CPD Provider Accreditation', '2026-03-04 15:08:04', '2026-03-04 15:08:04'),
(10, 'Special Temporary Permit', '2026-03-04 15:08:04', '2026-03-04 15:08:04');

DROP TABLE IF EXISTS `doc_status`;
CREATE TABLE doc_status (
  `doc_status_id` INT AUTO_INCREMENT PRIMARY KEY,
  `status_name` VARCHAR(50) NOT NULL,
  `status_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB; 

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE user_role(
  `user_role_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(45) NOT NULL,
  `role_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `role_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB; 

INSERT INTO `user_role` (`user_role_id`, `role_name`, `role_created_at`, `role_updated_at`) VALUES
(1, 'System Administrator', '2026-03-04 13:36:41', '2026-03-04 13:36:41');

DROP TABLE IF EXISTS `user_status`;
CREATE TABLE user_status(
  `user_status_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_status_name` VARCHAR(45) NOT NULL,
  `user_status_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `user_status_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `user_status` (`user_status_id`, `user_status_name`, `user_status_created_at`, `user_status_updated_at`) VALUES
(1, 'Active', '2026-03-04 13:38:52', '2026-03-04 13:38:52'),
(2, 'Inactive', '2026-03-04 13:38:52', '2026-03-04 13:38:52');

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE permissions(
  `permission_id` INT AUTO_INCREMENT PRIMARY KEY,
  `permission_name` VARCHAR(45) NOT NULL,
  `permission_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `permission_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB; 



DROP TABLE IF EXISTS `user`;
CREATE TABLE user(
  `user_id` CHAR(36) PRIMARY KEY,
  `username` VARCHAR(45) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `user_role_id` INT NOT NULL,
  `user_status_id` INT NOT NULL,
  `user_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `user_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_role_id`) REFERENCES `user_role`(`user_role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`user_status_id`) REFERENCES `user_status`(`user_status_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB; 

INSERT INTO `user` (`user_id`, `username`, `password`, `user_role_id`, `user_status_id`, `user_created_at`, `user_updated_at`) VALUES
('36526fd4-2fbe-4942-b923-a0af724ee7a8', 'DoeJohn', '$2y$10$PMEbltHQFSWVIgHdYsXrVO5TSL6IbzexjBgjLlqfNPj146D4/oIH6', 1, 1, '2026-03-04 13:40:16', '2026-03-04 13:40:16'),
('41fad608-18fc-11f1-b547-3c15c2de6b1a', 'admin_jon', '$2y$10$YourHashedPasswordHere', 1, 1, '2026-03-06 09:31:54', '2026-03-06 09:31:54');


DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE user_profile(
  `user_id` CHAR(36) PRIMARY KEY,
  `dept_id` INT NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `user_first_name` VARCHAR(45) NOT NULL,
  `user_middle_name` VARCHAR(45),
  `user_last_name` VARCHAR(45) NOT NULL,
  `user_birthdate` DATE NOT NULL,
  `prof_pic_path` VARCHAR(255),
  `user_profile_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `user_profile_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`dept_id`) REFERENCES `department`(`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX (`dept_id`),
  INDEX (`user_last_name`)
) ENGINE=InnoDB; 

INSERT INTO `user_profile` (`user_id`, `dept_id`, `email`, `user_first_name`, `user_middle_name`, `user_last_name`, `user_birthdate`, `prof_pic_path`, `user_profile_created_at`, `user_profile_updated_at`) VALUES
('36526fd4-2fbe-4942-b923-a0af724ee7a8', 1, 'doejohn@gmail.com', 'John', NULL, 'Doe', '2000-01-20', NULL, '2026-03-04 13:40:16', '2026-03-04 13:40:16');


DROP TABLE IF EXISTS `document`;
CREATE TABLE document (
    `doc_id` CHAR(36) PRIMARY KEY,
    `current_user_id` CHAR(36) NOT NULL,
    `current_dept_id` INT NOT NULL,
    `doc_status_id` INT NOT NULL,
    `doc_type_id` INT NOT NULL,
    `ref_no` VARCHAR(50),
    `uploaded_by` CHAR(36) NOT NULL,
    `applicant_name` VARCHAR(255),
    `applicant_license_no` VARCHAR(50),
    `file_path` VARCHAR(255),
    `doc_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `doc_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`current_user_id`) REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`current_dept_id`) REFERENCES `department`(`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`doc_status_id`) REFERENCES `doc_status`(`doc_status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`doc_type_id`) REFERENCES `document_type`(`doc_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (`current_user_id`),
    INDEX (`current_dept_id`),
    INDEX (`doc_status_id`)
) ENGINE=InnoDB; 

DROP TABLE IF EXISTS `transaction_logs`;
CREATE TABLE transaction_logs (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `doc_id` CHAR(36) NOT NULL,
    `doc_status_id` INT NOT NULL,
    `current_dept_id` INT NOT NULL,
    `target_dept_id` INT NOT NULL,
    `processed_by` CHAR(36) NOT NULL,
    `remarks` TEXT,
    `log_timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`doc_id`) REFERENCES `document`(`doc_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`doc_status_id`) REFERENCES `doc_status`(`doc_status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`current_dept_id`) REFERENCES `department`(`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`target_dept_id`) REFERENCES `department`(`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`processed_by`) REFERENCES `user`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (`doc_id`),
    INDEX (`target_dept_id`),
    INDEX (`processed_by`)
) ENGINE=InnoDB; 

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE role_permissions(
  `user_role_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `permission_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `permission_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_role_id`, `permission_id`),
  FOREIGN KEY (`user_role_id`) REFERENCES `user_role`(`user_role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB; 

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;