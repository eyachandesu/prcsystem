-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 20, 2026 at 03:33 AM
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
-- Database: `dts`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `document_number` varchar(50) NOT NULL,
  `document_type` varchar(100) DEFAULT 'General',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `current_status` enum('Draft','Submitted','Under Review','Approved','Rejected','Completed','Archived','Forwarded','Pending') DEFAULT 'Draft',
  `current_holder` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `document_number`, `document_type`, `title`, `description`, `file_path`, `uploaded_by`, `current_status`, `current_holder`, `created_at`, `updated_at`) VALUES
(1, 'PRC-2026-94AEB', 'General', 'Sample Doc', '', 'uploads/1771553872_6997c45094a29.docx', 1, 'Submitted', 4, '2026-02-20 02:17:52', '2026-02-20 02:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `document_logs`
--

CREATE TABLE `document_logs` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'The Uploader/Sender',
  `r_id` int(11) DEFAULT NULL COMMENT 'The Recipient/Receiver',
  `action` varchar(255) NOT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_logs`
--

INSERT INTO `document_logs` (`id`, `document_id`, `user_id`, `r_id`, `action`, `log_time`) VALUES
(1, 1, 1, NULL, 'Document Transferred', '2026-02-20 02:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `document_routes`
--

CREATE TABLE `document_routes` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `from_user` int(11) DEFAULT NULL,
  `to_user` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Forwarded','Received') DEFAULT 'Pending',
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_routes`
--

INSERT INTO `document_routes` (`id`, `document_id`, `from_user`, `to_user`, `remarks`, `status`, `action_date`) VALUES
(1, 1, 1, 4, 'Need response', 'Pending', '2026-02-20 02:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `perm_name` varchar(100) NOT NULL,
  `perm_key` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `perm_name`, `perm_key`) VALUES
(1, 'Create Documents', 'doc_create'),
(2, 'Edit Documents', 'doc_edit'),
(3, 'Delete Documents', 'doc_delete'),
(4, 'Transfer Documents', 'doc_transfer'),
(5, 'Receive Documents', 'doc_receive'),
(6, 'Manage Users', 'user_manage'),
(7, 'Manage Roles', 'role_manage');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `created_at`) VALUES
(1, 'System Administrator', 'Has full access to manage users, roles, and system configurations.', '2026-02-19 02:03:30'),
(2, 'Records Officer', 'Responsible for receiving, scanning, and initial routing of official documents.', '2026-02-19 02:03:30'),
(3, 'Department Head', 'Authorized to approve, reject, and review documents within their specific division.', '2026-02-19 02:03:30'),
(4, 'Regular Staff', 'Can create, track, and transfer documents but has no administrative access.', '2026-02-19 02:03:30');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role_id`, `department`, `status`, `created_at`) VALUES
(1, 'Jane', 'Doe', 'doejane@email.com', '$2y$10$ruvGi4w1k8TiSZS.BXft3uvTgzNw5hqZ1L/8HpbDOKIkLcnmoVpCu', 1, 'Legal Service', 'active', '2026-02-19 02:16:12'),
(2, 'John', 'Smith', 'johnsmith@email.com', '$2y$10$lXYYCoDywmFLI03o/MDgv.AG0RoVtsdnlEdsJ2odZm9J85eeCwFm6', 2, 'Licensure Office', 'active', '2026-02-19 02:03:35'),
(4, 'Juan', 'Dela Cruz', 'juan.delacruz@email.com', '$2y$10$FyoZ3eW1Pu1RKlFWLVGsI.di2yuRS3XL0HkZQXQH9Ghu4j09u7JZy', 1, 'Legal Service', 'active', '2026-02-20 01:24:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_number` (`document_number`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `current_holder` (`current_holder`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `document_logs`
--
ALTER TABLE `document_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_document` (`document_id`),
  ADD KEY `fk_log_uploader` (`user_id`),
  ADD KEY `fk_log_receiver` (`r_id`);

--
-- Indexes for table `document_routes`
--
ALTER TABLE `document_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `from_user` (`from_user`),
  ADD KEY `to_user` (`to_user`);

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
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `document_logs`
--
ALTER TABLE `document_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `document_routes`
--
ALTER TABLE `document_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`current_holder`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `document_logs`
--
ALTER TABLE `document_logs`
  ADD CONSTRAINT `fk_log_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_receiver` FOREIGN KEY (`r_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_log_uploader` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_routes`
--
ALTER TABLE `document_routes`
  ADD CONSTRAINT `document_routes_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_routes_ibfk_2` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `document_routes_ibfk_3` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
