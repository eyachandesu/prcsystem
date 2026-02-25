-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 25, 2026 at 08:13 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prcsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `dept_code` varchar(20) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `dept_code`, `status`) VALUES
(1, 'Finance and Administrative Division', 'FAD', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` char(36) NOT NULL,
  `document_number` varchar(50) NOT NULL,
  `document_type` varchar(100) DEFAULT 'General',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` char(36) NOT NULL,
  `current_status` enum('Draft','Submitted','Under Review','Approved','Rejected','Completed','Archived','Forwarded','Pending') DEFAULT 'Draft',
  `current_holder` char(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_logs`
--

CREATE TABLE `document_logs` (
  `id` char(36) NOT NULL,
  `document_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL COMMENT 'The Uploader/Sender',
  `r_id` char(36) DEFAULT NULL COMMENT 'The Recipient/Receiver',
  `action` varchar(255) NOT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_routes`
--

CREATE TABLE `document_routes` (
  `id` char(36) NOT NULL,
  `document_id` char(36) NOT NULL,
  `from_user` char(36) DEFAULT NULL,
  `to_user` char(36) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Forwarded','Received') DEFAULT 'Pending',
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` char(36) NOT NULL,
  `perm_name` varchar(100) NOT NULL,
  `perm_key` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `perm_name`, `perm_key`) VALUES
('7ee3cf90-1215-11f1-b039-3c15c2de6b1a', 'Create Documents', 'doc_create'),
('7ee3d166-1215-11f1-b039-3c15c2de6b1a', 'Transfer Documents', 'doc_transfer');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` char(36) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `created_at`) VALUES
('550e8400-e29b-41d4-a716-446655440000', 'System Administrator', 'Full access', '2026-02-25 06:44:55'),
('550e8400-e29b-41d4-a716-446655440001', 'Records Officer', 'Routing access', '2026-02-25 06:44:55'),
('550e8400-e29b-41d4-a716-446655440002', 'Department Head', 'Review access', '2026-02-25 06:44:55'),
('550e8400-e29b-41d4-a716-446655440003', 'Regular Staff', 'Creation access', '2026-02-25 06:44:55');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` char(36) NOT NULL,
  `permission_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
('550e8400-e29b-41d4-a716-446655440000', '7ee3cf90-1215-11f1-b039-3c15c2de6b1a'),
('550e8400-e29b-41d4-a716-446655440000', '7ee3d166-1215-11f1-b039-3c15c2de6b1a'),
('550e8400-e29b-41d4-a716-446655440001', '7ee3cf90-1215-11f1-b039-3c15c2de6b1a'),
('550e8400-e29b-41d4-a716-446655440001', '7ee3d166-1215-11f1-b039-3c15c2de6b1a'),
('550e8400-e29b-41d4-a716-446655440002', '7ee3cf90-1215-11f1-b039-3c15c2de6b1a'),
('550e8400-e29b-41d4-a716-446655440002', '7ee3d166-1215-11f1-b039-3c15c2de6b1a'),
('550e8400-e29b-41d4-a716-446655440003', '7ee3cf90-1215-11f1-b039-3c15c2de6b1a'),
('550e8400-e29b-41d4-a716-446655440003', '7ee3d166-1215-11f1-b039-3c15c2de6b1a');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` char(36) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role_id`, `department`, `status`, `created_at`) VALUES
('1df95d57-f7dc-47ea-a6d4-7cdfc9645693', 'John', 'Smith', 'smithjohn@email.com', '$2y$10$B0BJHiFu7YLxnj7j6a5npOHiWKVmf3mee/uH0uTWRG792FV9gZGTW', '550e8400-e29b-41d4-a716-446655440002', 'Finance and Administrative Division', 'active', '2026-02-25 07:09:15'),
('f47ac10b-58cc-4372-a567-0e02b2c3d479', 'Jane', 'Doe', 'doejane@email.com', '$2y$10$ruvGi4w1k8TiSZS.BXft3uvTgzNw5hqZ1L/8HpbDOKIkLcnmoVpCu', '550e8400-e29b-41d4-a716-446655440000', 'Finance and Administrative Division', 'active', '2026-02-25 06:44:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_number` (`document_number`),
  ADD KEY `fk_doc_uploader` (`uploaded_by`),
  ADD KEY `fk_doc_holder` (`current_holder`);

--
-- Indexes for table `document_logs`
--
ALTER TABLE `document_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_document` (`document_id`),
  ADD KEY `fk_log_user` (`user_id`),
  ADD KEY `fk_log_recipient` (`r_id`);

--
-- Indexes for table `document_routes`
--
ALTER TABLE `document_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_route_document` (`document_id`),
  ADD KEY `fk_route_from` (`from_user`),
  ADD KEY `fk_route_to` (`to_user`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `perm_key` (`perm_key`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_rp_permission` (`permission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_doc_holder` FOREIGN KEY (`current_holder`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_doc_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_logs`
--
ALTER TABLE `document_logs`
  ADD CONSTRAINT `fk_log_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_recipient` FOREIGN KEY (`r_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_routes`
--
ALTER TABLE `document_routes`
  ADD CONSTRAINT `fk_route_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_route_from` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_route_to` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
