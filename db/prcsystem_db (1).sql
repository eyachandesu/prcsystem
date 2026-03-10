-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 06, 2026 at 02:34 AM
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
-- Database: `prcsystem_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(45) NOT NULL,
  `dept_created_at` datetime DEFAULT current_timestamp(),
  `dept_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`dept_id`, `dept_name`, `dept_created_at`, `dept_updated_at`) VALUES
(1, 'Finance and Administrative Division', '2026-03-03 14:55:58', '2026-03-03 14:55:58'),
(2, 'Regulations', '2026-03-03 14:55:58', '2026-03-03 14:55:58'),
(3, 'Supply Office', '2026-03-03 15:48:35', '2026-03-03 15:48:35');

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

CREATE TABLE `document` (
  `doc_id` char(36) NOT NULL,
  `current_user_id` char(36) NOT NULL,
  `current_dept_id` int(11) NOT NULL,
  `doc_status_id` int(11) NOT NULL,
  `doc_type_id` int(11) NOT NULL,
  `ref_no` varchar(50) DEFAULT NULL,
  `uploaded_by` char(36) NOT NULL,
  `applicant_name` varchar(255) DEFAULT NULL,
  `applicant_license_no` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `doc_created_at` datetime DEFAULT current_timestamp(),
  `doc_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_type`
--

CREATE TABLE `document_type` (
  `doc_type_id` int(11) NOT NULL,
  `document_type_name` varchar(50) NOT NULL,
  `doc_created_at` datetime DEFAULT current_timestamp(),
  `doc_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_type`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `doc_status`
--

CREATE TABLE `doc_status` (
  `doc_status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `status_created_at` datetime DEFAULT current_timestamp(),
  `status_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(45) NOT NULL,
  `permission_created_at` datetime DEFAULT current_timestamp(),
  `permission_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `user_role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `permission_created_at` datetime DEFAULT current_timestamp(),
  `permission_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_logs`
--

CREATE TABLE `transaction_logs` (
  `log_id` int(11) NOT NULL,
  `doc_id` char(36) NOT NULL,
  `doc_status_id` int(11) NOT NULL,
  `current_dept_id` int(11) NOT NULL,
  `target_dept_id` int(11) NOT NULL,
  `processed_by` char(36) NOT NULL,
  `remarks` text DEFAULT NULL,
  `log_timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` char(36) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role_id` int(11) NOT NULL,
  `user_status_id` int(11) NOT NULL,
  `user_created_at` datetime DEFAULT current_timestamp(),
  `user_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `user_role_id`, `user_status_id`, `user_created_at`, `user_updated_at`) VALUES
('36526fd4-2fbe-4942-b923-a0af724ee7a8', 'DoeJohn', '$2y$10$PMEbltHQFSWVIgHdYsXrVO5TSL6IbzexjBgjLlqfNPj146D4/oIH6', 1, 1, '2026-03-04 13:40:16', '2026-03-04 13:40:16'),
('41fad608-18fc-11f1-b547-3c15c2de6b1a', 'admin_jon', '$2y$10$YourHashedPasswordHere', 1, 1, '2026-03-06 09:31:54', '2026-03-06 09:31:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_profile`
--

CREATE TABLE `user_profile` (
  `user_id` char(36) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_first_name` varchar(45) NOT NULL,
  `user_middle_name` varchar(45) DEFAULT NULL,
  `user_last_name` varchar(45) NOT NULL,
  `user_birthdate` date NOT NULL,
  `prof_pic_path` varchar(255) DEFAULT NULL,
  `user_profile_created_at` datetime DEFAULT current_timestamp(),
  `user_profile_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profile`
--

INSERT INTO `user_profile` (`user_id`, `dept_id`, `email`, `user_first_name`, `user_middle_name`, `user_last_name`, `user_birthdate`, `prof_pic_path`, `user_profile_created_at`, `user_profile_updated_at`) VALUES
('36526fd4-2fbe-4942-b923-a0af724ee7a8', 1, 'doejohn@gmail.com', 'John', NULL, 'Doe', '2000-01-20', NULL, '2026-03-04 13:40:16', '2026-03-04 13:40:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
  `user_role_id` int(11) NOT NULL,
  `role_name` varchar(45) NOT NULL,
  `role_created_at` datetime DEFAULT current_timestamp(),
  `role_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_role_id`, `role_name`, `role_created_at`, `role_updated_at`) VALUES
(1, 'System Administrator', '2026-03-04 13:36:41', '2026-03-04 13:36:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

CREATE TABLE `user_status` (
  `user_status_id` int(11) NOT NULL,
  `user_status_name` varchar(45) NOT NULL,
  `user_status_created_at` datetime DEFAULT current_timestamp(),
  `user_status_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_status`
--

INSERT INTO `user_status` (`user_status_id`, `user_status_name`, `user_status_created_at`, `user_status_updated_at`) VALUES
(1, 'Active', '2026-03-04 13:38:52', '2026-03-04 13:38:52'),
(2, 'Inactive', '2026-03-04 13:38:52', '2026-03-04 13:38:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`dept_id`);

--
-- Indexes for table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `doc_type_id` (`doc_type_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `current_user_id` (`current_user_id`),
  ADD KEY `current_dept_id` (`current_dept_id`),
  ADD KEY `doc_status_id` (`doc_status_id`);

--
-- Indexes for table `document_type`
--
ALTER TABLE `document_type`
  ADD PRIMARY KEY (`doc_type_id`);

--
-- Indexes for table `doc_status`
--
ALTER TABLE `doc_status`
  ADD PRIMARY KEY (`doc_status_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`user_role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `doc_status_id` (`doc_status_id`),
  ADD KEY `current_dept_id` (`current_dept_id`),
  ADD KEY `doc_id` (`doc_id`),
  ADD KEY `target_dept_id` (`target_dept_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `user_role_id` (`user_role_id`),
  ADD KEY `user_status_id` (`user_status_id`);

--
-- Indexes for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `user_last_name` (`user_last_name`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`user_role_id`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`user_status_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `document_type`
--
ALTER TABLE `document_type`
  MODIFY `doc_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doc_status`
--
ALTER TABLE `doc_status`
  MODIFY `doc_status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_role`
--
ALTER TABLE `user_role`
  MODIFY `user_role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_status`
--
ALTER TABLE `user_status`
  MODIFY `user_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`current_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_2` FOREIGN KEY (`current_dept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_3` FOREIGN KEY (`doc_status_id`) REFERENCES `doc_status` (`doc_status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_4` FOREIGN KEY (`doc_type_id`) REFERENCES `document_type` (`doc_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_5` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`user_role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD CONSTRAINT `transaction_logs_ibfk_1` FOREIGN KEY (`doc_id`) REFERENCES `document` (`doc_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_logs_ibfk_2` FOREIGN KEY (`doc_status_id`) REFERENCES `doc_status` (`doc_status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_logs_ibfk_3` FOREIGN KEY (`current_dept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_logs_ibfk_4` FOREIGN KEY (`target_dept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_logs_ibfk_5` FOREIGN KEY (`processed_by`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`user_role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`user_status_id`) REFERENCES `user_status` (`user_status_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_profile_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
