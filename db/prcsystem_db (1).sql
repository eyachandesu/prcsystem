-- ==========================================================
-- 1. DATABASE INITIALIZATION
-- ==========================================================
CREATE DATABASE IF NOT EXISTS `prcsystem_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `prcsystem_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================================
-- 2. INDEPENDENT TABLES (Lookup Tables)
-- ==========================================================

-- Table: department
DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `dept_id` int NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(45) NOT NULL,
  `dept_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `dept_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `department` (`dept_id`, `dept_name`) VALUES 
(1, 'Finance and Administrative Division'),
(2, 'Regulations'),
(3, 'Supply Office');

-- Table: doc_status
DROP TABLE IF EXISTS `doc_status`;
CREATE TABLE `doc_status` (
  `doc_status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) NOT NULL,
  `status_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`doc_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `doc_status` (`doc_status_id`, `status_name`) VALUES
(1, 'Pending/Uploaded'),
(2, 'Forwarded/Transferred'),
(3, 'Received/Action Taken'),
(4, 'Archived/Completed');

-- Table: document_type
DROP TABLE IF EXISTS `document_type`;
CREATE TABLE `document_type` (
  `doc_type_id` int NOT NULL AUTO_INCREMENT,
  `document_type_name` varchar(50) NOT NULL,
  `doc_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `doc_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`doc_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `document_type` (`doc_type_id`, `document_type_name`) VALUES 
(1, 'Board Resolution'), (2, 'Licensure Exam Application'), (3, 'Professional ID Renewal'),
(4, 'Formal Legal Complaint'), (5, 'Memorandum Circular'), (6, 'Certification of Good Standing'),
(7, 'Disbursement Voucher'), (8, 'Office Order'), (9, 'CPD Provider Accreditation'),
(10, 'Special Temporary Permit');

-- Table: user_role
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `user_role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) NOT NULL,
  `role_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `role_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `user_role` (`user_role_id`, `role_name`) VALUES (1, 'Admin'), (2, 'User');

-- Table: user_status
DROP TABLE IF EXISTS `user_status`;
CREATE TABLE `user_status` (
  `user_status_id` int NOT NULL AUTO_INCREMENT,
  `user_status_name` varchar(45) NOT NULL,
  `user_status_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_status_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `user_status` (`user_status_id`, `user_status_name`) VALUES (1, 'Active'), (2, 'Inactive');

-- Table: permissions
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `permission_id` int NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(45) NOT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ==========================================================
-- 3. USER MANAGEMENT TABLES
-- ==========================================================

-- Table: user
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` char(36) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role_id` int NOT NULL,
  `user_status_id` int NOT NULL DEFAULT 1,
  `user_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`user_role_id`) ON DELETE CASCADE,
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`user_status_id`) REFERENCES `user_status` (`user_status_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `user` (`user_id`, `username`, `password`, `user_role_id`, `user_status_id`) 
VALUES(
  'dac48d52-2a90-11f1-b096-088fc334d711', 'admin', '$2y$12$IjCI8/t6ild1YCRd2uvl8.68MgXut9QpyqAyQ9mW4t1l/cgZmF9jW', 1, 1);

-- Table: user_profile
DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE `user_profile` (
  `user_id` char(36) NOT NULL,
  `dept_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_first_name` varchar(45) NOT NULL,
  `user_last_name` varchar(45) NOT NULL,
  `user_middle_name` varchar(45) DEFAULT NULL,
  `user_prof` varchar(255) DEFAULT NULL,
  `user_birthdate` date NOT NULL,
  `user_profile_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_profile_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `user_profile`(`user_id`, `dept_id`, `email`, `user_first_name`, `user_middle_name`,`user_last_name`, `user_birthdate`)
VALUE('dac48d52-2a90-11f1-b096-088fc334d711', 1, 'email@gmail.com', 'Mickyl', 'Gaytana', 'Sumagang', '2003-06-06');

-- Table: user_log (Audit Trail for Logins)
DROP TABLE IF EXISTS `user_log`;
CREATE TABLE `user_log` (
  `user_log_id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `log_message` text,
  `log_level` enum('CREATE','UPDATE','DELETE','LOGIN','LOGOUT','ERROR') DEFAULT NULL,
  `log_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_log_id`),
  CONSTRAINT `user_log_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ==========================================================
-- 4. DOCUMENT TRACKING TABLES
-- ==========================================================

-- Table: document
DROP TABLE IF EXISTS `document`;
CREATE TABLE `document` (
  `doc_id` char(36) NOT NULL,
  `current_user_id` char(36) NOT NULL,
  `current_dept_id` int NOT NULL,
  `doc_status_id` int NOT NULL DEFAULT 1,
  `doc_type_id` int NOT NULL,
  `ref_no` varchar(50) DEFAULT NULL,
  `uploaded_by` char(36) NOT NULL,
  `applicant_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `doc_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `doc_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`doc_id`),
  CONSTRAINT `doc_fk_curr_user` FOREIGN KEY (`current_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `doc_fk_dept` FOREIGN KEY (`current_dept_id`) REFERENCES `department` (`dept_id`),
  CONSTRAINT `doc_fk_status` FOREIGN KEY (`doc_status_id`) REFERENCES `doc_status` (`doc_status_id`),
  CONSTRAINT `doc_fk_type` FOREIGN KEY (`doc_type_id`) REFERENCES `document_type` (`doc_type_id`),
  CONSTRAINT `doc_fk_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table: transaction_logs
DROP TABLE IF EXISTS `transaction_logs`;
CREATE TABLE `transaction_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `doc_id` char(36) NOT NULL,
  `doc_status_id` int NOT NULL,
  `current_dept_id` int NOT NULL,
  `target_dept_id` int NOT NULL,
  `processed_by` char(36) NOT NULL,
  `remarks` text,
  `log_timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  CONSTRAINT `trans_fk_doc` FOREIGN KEY (`doc_id`) REFERENCES `document` (`doc_id`) ON DELETE CASCADE,
  CONSTRAINT `trans_fk_status` FOREIGN KEY (`doc_status_id`) REFERENCES `doc_status` (`doc_status_id`),
  CONSTRAINT `trans_fk_dept_curr` FOREIGN KEY (`current_dept_id`) REFERENCES `department` (`dept_id`),
  CONSTRAINT `trans_fk_dept_targ` FOREIGN KEY (`target_dept_id`) REFERENCES `department` (`dept_id`),
  CONSTRAINT `trans_fk_user` FOREIGN KEY (`processed_by`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ==========================================================
-- 5. ACCESS CONTROL
-- ==========================================================

-- Table: role_permissions
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `user_role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_role_id`,`permission_id`),
  CONSTRAINT `rp_fk_role` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`user_role_id`) ON DELETE CASCADE,
  CONSTRAINT `rp_fk_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

SET FOREIGN_KEY_CHECKS = 1;