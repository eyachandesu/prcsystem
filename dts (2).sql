CREATE DATABASE IF NOT EXISTS prcsystem;
USE prcsystem;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------
CREATE TABLE `roles` (
  `id` CHAR(36) NOT NULL,
  `role_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `permissions`
-- --------------------------------------------------------
CREATE TABLE `permissions` (
  `id` CHAR(36) NOT NULL,
  `perm_name` VARCHAR(100) NOT NULL,
  `perm_key` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `perm_key` (`perm_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `role_permissions` (Junction Table)
-- --------------------------------------------------------
CREATE TABLE `role_permissions` (
  `role_id` CHAR(36) NOT NULL,
  `permission_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` CHAR(36) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` CHAR(36) NOT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `documents`
-- --------------------------------------------------------
CREATE TABLE `documents` (
  `id` CHAR(36) NOT NULL,
  `document_number` VARCHAR(50) NOT NULL,
  `document_type` VARCHAR(100) DEFAULT 'General',
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_by` CHAR(36) NOT NULL,
  `current_status` ENUM('Draft','Submitted','Under Review','Approved','Rejected','Completed','Archived','Forwarded','Pending') DEFAULT 'Draft',
  `current_holder` CHAR(36) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_number` (`document_number`),
  CONSTRAINT `fk_doc_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_doc_holder` FOREIGN KEY (`current_holder`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `document_logs`
-- --------------------------------------------------------
CREATE TABLE `document_logs` (
  `id` CHAR(36) NOT NULL,
  `document_id` CHAR(36) NOT NULL,
  `user_id` CHAR(36) NOT NULL COMMENT 'The Uploader/Sender',
  `r_id` CHAR(36) DEFAULT NULL COMMENT 'The Recipient/Receiver',
  `action` VARCHAR(255) NOT NULL,
  `log_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_log_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_recipient` FOREIGN KEY (`r_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `document_routes`
-- --------------------------------------------------------
CREATE TABLE `document_routes` (
  `id` CHAR(36) NOT NULL,
  `document_id` CHAR(36) NOT NULL,
  `from_user` CHAR(36) DEFAULT NULL,
  `to_user` CHAR(36) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `status` ENUM('Pending','Approved','Rejected','Forwarded','Received') DEFAULT 'Pending',
  `action_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_route_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_route_from` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_route_to` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping Sanitized Data (Example UUIDs)
-- --------------------------------------------------------

-- Insert Roles
INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES 
('550e8400-e29b-41d4-a716-446655440000', 'System Administrator', 'Full access'),
('550e8400-e29b-41d4-a716-446655440001', 'Records Officer', 'Routing access'),
('550e8400-e29b-41d4-a716-446655440002', 'Department Head', 'Review access'),
('550e8400-e29b-41d4-a716-446655440003', 'Regular Staff', 'Creation access');

-- Insert Permissions (Partial example)
INSERT INTO `permissions` (`id`, `perm_name`, `perm_key`) VALUES 
(UUID(), 'Create Documents', 'doc_create'),
(UUID(), 'Transfer Documents', 'doc_transfer');

-- Insert Users
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role_id`, `department`) VALUES 
('f47ac10b-58cc-4372-a567-0e02b2c3d479', 'Jane', 'Doe', 'doejane@email.com', '$2y$10$ruvGi4w1k8TiSZS.BXft3uvTgzNw5hqZ1L/8HpbDOKIkLcnmoVpCu', '550e8400-e29b-41d4-a716-446655440000', 'Legal Service');

COMMIT;