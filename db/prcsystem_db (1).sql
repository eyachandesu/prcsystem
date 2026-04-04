-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: prcsystem_db
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department` (
  `dept_id` int NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(45) NOT NULL,
  `dept_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `dept_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department`
--

LOCK TABLES `department` WRITE;
/*!40000 ALTER TABLE `department` DISABLE KEYS */;
INSERT INTO `department` VALUES (1,'Finance and Administrative Division','2026-03-28 19:55:44','2026-03-28 19:55:44'),(2,'Regulations','2026-03-28 19:55:44','2026-03-28 19:55:44'),(3,'Supply Office','2026-03-28 19:55:44','2026-03-28 19:55:44');
/*!40000 ALTER TABLE `department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doc_status`
--

DROP TABLE IF EXISTS `doc_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doc_status` (
  `doc_status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) NOT NULL,
  `status_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`doc_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_status`
--

LOCK TABLES `doc_status` WRITE;
/*!40000 ALTER TABLE `doc_status` DISABLE KEYS */;
INSERT INTO `doc_status` VALUES (1,'Pending/Uploaded','2026-03-28 19:55:44','2026-03-28 19:55:44'),(2,'Forwarded/Transferred','2026-03-28 19:55:44','2026-03-28 19:55:44'),(3,'Received/Action Taken','2026-03-28 19:55:44','2026-03-28 19:55:44'),(4,'Archived/Completed','2026-03-28 19:55:44','2026-03-28 19:55:44');
/*!40000 ALTER TABLE `doc_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document`
--

DROP TABLE IF EXISTS `document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document` (
  `doc_id` char(36) NOT NULL,
  `current_user_id` char(36) NOT NULL,
  `current_dept_id` int NOT NULL,
  `doc_status_id` int NOT NULL DEFAULT '1',
  `doc_name` varchar(255) NOT NULL,
  `doc_description` text,
  `uploaded_by` char(36) NOT NULL,
  `doc_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `doc_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`doc_id`),
  KEY `doc_fk_curr_user` (`current_user_id`),
  KEY `doc_fk_dept` (`current_dept_id`),
  KEY `doc_fk_status` (`doc_status_id`),
  KEY `doc_fk_uploader` (`uploaded_by`),
  CONSTRAINT `doc_fk_curr_user` FOREIGN KEY (`current_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `doc_fk_dept` FOREIGN KEY (`current_dept_id`) REFERENCES `department` (`dept_id`),
  CONSTRAINT `doc_fk_status` FOREIGN KEY (`doc_status_id`) REFERENCES `doc_status` (`doc_status_id`),
  CONSTRAINT `doc_fk_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document`
--

LOCK TABLES `document` WRITE;
/*!40000 ALTER TABLE `document` DISABLE KEYS */;
INSERT INTO `document` VALUES ('4fc0db02-6a61-464f-ac5e-f22b8989d044','dac48d52-2a90-11f1-b096-088fc334d711',1,1,'test','testing lang','dac48d52-2a90-11f1-b096-088fc334d711','2026-04-04 12:04:10','2026-04-04 12:04:10'),('51cc6e43-8bea-411e-b59a-a9120f935914','5d383d70-2049-4a41-8ea5-9888180f20e6',3,1,'Test 2','testing lang 2','5d383d70-2049-4a41-8ea5-9888180f20e6','2026-04-04 20:24:17','2026-04-04 20:24:17'),('83e4c794-dd26-44e9-be67-3c97bde11984','dac48d52-2a90-11f1-b096-088fc334d711',1,1,'Document','Basta Document yan','dac48d52-2a90-11f1-b096-088fc334d711','2026-04-04 20:23:18','2026-04-04 20:23:18'),('ad8ab5f4-8ee3-446e-965b-e427a0e4f86e','c2d03138-8f57-4d02-8fa5-7b4f14928bce',3,1,'test 3','dapat kita lahat ng taga supply office toh','c2d03138-8f57-4d02-8fa5-7b4f14928bce','2026-04-04 21:05:47','2026-04-04 21:05:47'),('f4db3878-586b-41ec-9764-db5b0d5c1b8c','493866f4-e71f-4b4a-8569-998942b02681',3,1,'test','dapat kita ni admin at ni ricky toh','493866f4-e71f-4b4a-8569-998942b02681','2026-04-04 21:04:58','2026-04-04 21:04:58');
/*!40000 ALTER TABLE `document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_logs`
--

DROP TABLE IF EXISTS `transaction_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  KEY `trans_fk_doc` (`doc_id`),
  KEY `trans_fk_status` (`doc_status_id`),
  KEY `trans_fk_dept_curr` (`current_dept_id`),
  KEY `trans_fk_dept_targ` (`target_dept_id`),
  KEY `trans_fk_user` (`processed_by`),
  CONSTRAINT `trans_fk_dept_curr` FOREIGN KEY (`current_dept_id`) REFERENCES `department` (`dept_id`),
  CONSTRAINT `trans_fk_dept_targ` FOREIGN KEY (`target_dept_id`) REFERENCES `department` (`dept_id`),
  CONSTRAINT `trans_fk_doc` FOREIGN KEY (`doc_id`) REFERENCES `document` (`doc_id`) ON DELETE CASCADE,
  CONSTRAINT `trans_fk_status` FOREIGN KEY (`doc_status_id`) REFERENCES `doc_status` (`doc_status_id`),
  CONSTRAINT `trans_fk_user` FOREIGN KEY (`processed_by`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_logs`
--

LOCK TABLES `transaction_logs` WRITE;
/*!40000 ALTER TABLE `transaction_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` char(36) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role_id` int NOT NULL,
  `user_status_id` int NOT NULL DEFAULT '1',
  `user_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `user_ibfk_1` (`user_role_id`),
  KEY `user_ibfk_2` (`user_status_id`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`user_role_id`) ON DELETE CASCADE,
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`user_status_id`) REFERENCES `user_status` (`user_status_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('3603a40c-d20f-4677-8227-660f9963ce4d','doctest','$2y$12$y2D26r5hiWVzl0mykMiE/eYJzjG4kCgw9fqw6pMFN.pwlu65/r71e',2,1,'2026-04-04 21:09:24','2026-04-04 21:11:28'),('493866f4-e71f-4b4a-8569-998942b02681','trecy','$2y$12$g/JUZmuetI05Dpqzm9FbKe2VOdU3JJ0vO6y8uONluBscLbTglv/Cy',2,2,'2026-04-03 21:24:01','2026-04-04 21:05:17'),('5d383d70-2049-4a41-8ea5-9888180f20e6','ricky','$2y$12$2c19hQFuhkq.NX5J.137hOtv0nbKAfk8TrS6kFFhaJSJymnjOtvBG',2,2,'2026-03-29 15:03:20','2026-04-04 21:11:22'),('92ff329f-432a-40f3-9e7a-b2fa51d43d36','test','$2y$12$B/IvT0CgINWFEoW7ODrPTebE0aZl9daQmCN1M1Nw8Q9b5JebZVnaK',1,2,'2026-04-03 21:23:26','2026-04-04 21:09:33'),('c2d03138-8f57-4d02-8fa5-7b4f14928bce','user','$2y$12$xezE9WDnfPcZjywMSSv9uemsiXzjfJNN4Piw.MI/SWAYEEfxmfpWW',2,2,'2026-03-29 15:11:36','2026-04-04 21:06:40'),('dac48d52-2a90-11f1-b096-088fc334d711','admin','$2y$12$IjCI8/t6ild1YCRd2uvl8.68MgXut9QpyqAyQ9mW4t1l/cgZmF9jW',1,2,'2026-03-28 19:55:45','2026-04-04 21:10:57'),('ec159736-311b-41f6-8588-9b63057a85a2','eya','$2y$12$LnGK4RPfFbgT.DJ9lGy6aOLKcC0BPFx./w.g2Srg5xeVxbYLV2B4W',1,1,'2026-03-28 20:13:57','2026-04-03 22:20:24');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_log`
--

DROP TABLE IF EXISTS `user_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_log` (
  `user_log_id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `log_message` text,
  `log_level` enum('CREATE','UPDATE','DELETE','LOGIN','LOGOUT','ERROR') DEFAULT NULL,
  `log_created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_log_id`),
  KEY `user_log_fk` (`user_id`),
  CONSTRAINT `user_log_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_log`
--

LOCK TABLES `user_log` WRITE;
/*!40000 ALTER TABLE `user_log` DISABLE KEYS */;
INSERT INTO `user_log` VALUES ('04310ea8-7032-41aa-bbf1-649b50eebd57','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-03-29 08:50:51'),('04aa8e11-3bab-49a8-aad3-aa9f91a8e09f','5d383d70-2049-4a41-8ea5-9888180f20e6','Ricky Logged In','LOGIN','2026-03-29 07:10:52'),('0567b7d3-168e-44f9-81e1-c11e9a38f4d4','493866f4-e71f-4b4a-8569-998942b02681','trecy Logged Out','LOGOUT','2026-04-04 13:05:17'),('05d0574a-880f-4f78-99cd-7c1d87ceb6e9','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-04 04:02:54'),('06e4e004-dd6d-4f94-8e41-88ddb6c7d9c8','c2d03138-8f57-4d02-8fa5-7b4f14928bce','user Logged Out','LOGOUT','2026-04-04 13:06:40'),('0a80ac6b-14bb-4f3f-b28a-bb50f77fb0ec','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-03-29 08:50:06'),('0ab2c8af-d1e2-4d74-bd2d-1aba94e43ff0','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-03 14:09:42'),('0b2e4d0e-b899-4d07-a5fb-5dc9d6ecb505','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-04 12:23:27'),('0d28035f-aa58-4c5e-b7c2-5c358b452d4d','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-28 12:32:14'),('0e5ff675-64da-4b0c-a0de-ce7eb1bbf4c0','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged Out','LOGOUT','2026-04-04 12:24:46'),('10e55459-03e0-45f5-99c4-8012d9d5d972','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-04 13:10:28'),('18782df3-4470-44b8-b319-be4b5f7bad47','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-03 13:53:30'),('18c93660-2f55-4c4c-95d1-1948469d7367','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-28 12:35:20'),('19a345e1-12f4-4b35-b0df-a6c9c6f66147','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-29 07:02:23'),('1d1c4e5a-654d-48fd-a368-b4c4be0bf0a1','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-28 12:36:37'),('2160cf5e-a4dd-4848-ab64-c729117dda45','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-28 12:25:31'),('236e8389-9588-4a87-936e-3eb6e1ce1f34','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-04 12:24:50'),('2bb9f82d-4fd0-4191-9d15-c5db3236fa8c','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-29 08:42:33'),('2c12be99-b31e-4eb7-9a62-d4f42396bfb0','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-04 04:03:00'),('3573a9e3-4b41-417a-b862-b568d899d3c8','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-04 12:06:11'),('39ae3d26-d36f-46c3-97ed-a09e9c562e9f','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged In','LOGIN','2026-03-29 08:53:06'),('3e7d19e1-4f1f-40be-8c2f-375fe786e749','92ff329f-432a-40f3-9e7a-b2fa51d43d36','test Logged Out','LOGOUT','2026-04-04 13:09:33'),('3e84a741-3414-49ee-b0a6-68450044c3da','92ff329f-432a-40f3-9e7a-b2fa51d43d36','test Logged In successfully.','LOGIN','2026-04-04 13:06:44'),('41ee5f86-9159-43ac-bc78-d5b5501a485c','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged Out','LOGOUT','2026-04-04 13:03:13'),('474b6d87-affe-469b-956a-f7bd03418e9f','5d383d70-2049-4a41-8ea5-9888180f20e6','Ricky Logged Out','LOGOUT','2026-03-29 07:11:47'),('484d9e5c-46b1-494a-842e-b7abedf3916e','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-03 14:02:08'),('4e00fced-6ee3-40e4-a2f3-ba2aadc76d1b','3603a40c-d20f-4677-8227-660f9963ce4d','doctest Logged Out','LOGOUT','2026-04-04 13:10:24'),('50654756-400c-49cb-82da-370c07e3b13a','c2d03138-8f57-4d02-8fa5-7b4f14928bce','user Logged In successfully.','LOGIN','2026-04-04 13:05:21'),('5b95a0ee-c3fd-493a-afdd-923f2bcc96f1','ec159736-311b-41f6-8588-9b63057a85a2','Eya Logged Out','LOGOUT','2026-03-29 07:40:44'),('5bc6a96b-5332-4c73-808a-1206a8a923e6','3603a40c-d20f-4677-8227-660f9963ce4d','doctest Logged In successfully.','LOGIN','2026-04-04 13:09:39'),('5bcfac4d-cfba-45fa-b31c-9219d6d601cf','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged In successfully.','LOGIN','2026-04-03 13:53:39'),('5e3a3ae3-afe3-4f5f-a79b-81a1339bcf94','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged Out','LOGOUT','2026-03-29 08:52:45'),('5ec444ad-f8f5-492e-a15b-2d48451e4409','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged Out','LOGOUT','2026-04-04 12:57:21'),('6025a557-2627-417d-a5c1-300d15587efc','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged Out','LOGOUT','2026-04-03 13:58:41'),('62c16289-f897-4116-a98e-a3fc501914aa','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-03-28 12:20:52'),('6bb125e4-978f-411e-ac5f-ec9fc80e0788','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-28 12:52:06'),('6e34828a-9494-4bb7-b338-fa03821ee31d','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-04 04:01:33'),('6f8027a1-6478-4738-b3ca-8a24966109c8','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged In successfully.','LOGIN','2026-04-04 12:57:29'),('740c6198-b2f3-42f6-83c8-73eb22491d11','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged In successfully.','LOGIN','2026-04-04 12:23:42'),('74aa5f0e-87a3-40c5-ade5-c87449ca1a19','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-04 10:44:06'),('812584ba-0f8b-4a4c-b8ca-8eb16d5b28b9','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged In successfully.','LOGIN','2026-04-03 14:19:37'),('8373f619-4262-4b45-96f9-2e22cffaf021','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged Out','LOGOUT','2026-03-29 08:50:16'),('88af7722-53f1-491a-9dc1-cab580f0f809','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged Out','LOGOUT','2026-04-03 13:53:58'),('89998e8c-fb97-4b55-ab35-dc658848a2ba','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-03-29 07:06:02'),('96ac0702-b03d-44cb-9bf7-1ebad4fc847f','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged In successfully.','LOGIN','2026-04-04 13:11:07'),('98c567a6-c82e-4f3e-a56c-70490a5471e3','ec159736-311b-41f6-8588-9b63057a85a2','Eya Logged In','LOGIN','2026-03-29 07:19:58'),('9b40c922-cd41-40ca-b8fc-2c2044a6f0a8','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-03 13:58:45'),('9e6824dd-c007-4680-b33b-5967318f879c','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-29 06:57:26'),('9fe32db8-3163-43a7-9f31-e61466e30b10','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged Out','LOGOUT','2026-03-29 08:51:01'),('a531d1ae-7692-4718-ad38-ec91bfd65a77','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged In','LOGIN','2026-03-29 08:51:45'),('a5bcce07-7cf8-44a6-8b69-daa5c13771f5','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-03 13:21:29'),('a6111ddf-774a-4ff1-91f0-86f43fa06f21','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-28 12:42:59'),('a76372f1-6f7b-468e-b0fb-9d09cf192c6d','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-03 14:09:35'),('ad6ba950-2d00-4b06-9f37-1d76258a93a8','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-04 12:50:26'),('b1bafe70-6ec6-46e5-bc2f-99ae13b12fd6','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-29 06:46:56'),('b375b5a6-82e9-42f4-b530-f6e033a139ea','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged In successfully.','LOGIN','2026-04-03 13:54:08'),('b38fbbe4-06f2-4fa4-b237-c769de60d4ad','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged Out','LOGOUT','2026-04-04 13:11:22'),('bae4a916-9af4-48fe-9e04-b1f3ec906315','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged Out','LOGOUT','2026-03-29 08:53:10'),('c009e85b-0f69-498d-a2ff-ab2dd9436da8','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged In successfully.','LOGIN','2026-04-03 14:20:24'),('c1d94a2e-607f-4b33-a6f8-d2dd7f751866','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-04 13:03:21'),('c8e4997f-1efe-45c8-a479-c144fa3f8662','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-29 07:41:19'),('c943e0a9-5f2a-4ac8-ab74-d91463be4a3e','493866f4-e71f-4b4a-8569-998942b02681','trecy Logged In successfully.','LOGIN','2026-04-04 13:04:32'),('cd9e07f4-517b-4191-8717-d8f5e014452f','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-04 13:04:28'),('ce16796a-5546-4a0d-8701-d3cdd2ca822c','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-03 14:19:26'),('d68dfd96-8f9f-4765-9bc9-153b80458238','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-29 08:50:19'),('d75285b4-792d-4b97-8661-63895ef8bf0a','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In successfully.','LOGIN','2026-04-04 04:01:37'),('d866c6e1-9b22-4a45-ba4e-759cde37f8b7','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-03-28 12:32:09'),('de1aaac9-6dee-4a3f-b58c-5f6bd2e8ae91','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-28 12:54:15'),('e03a7888-1eaa-48b1-be79-43e2729824ee','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-03-29 06:57:56'),('e1b7d079-28c0-47ea-a6d3-5f440047b1b0','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged In','LOGIN','2026-03-29 06:53:52'),('e5a3df8d-ce24-4b83-98f0-631ca9645d8e','3603a40c-d20f-4677-8227-660f9963ce4d','doctest Logged In successfully.','LOGIN','2026-04-04 13:11:28'),('e636271c-b5c0-4858-8653-9517af715297','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged In','LOGIN','2026-03-29 08:50:13'),('ee8f98bb-e407-4b7b-a6ab-f7e5c146b9f8','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged Out','LOGOUT','2026-04-03 14:20:20'),('eed7ccb6-bda7-4e51-a420-23b62b87be31','ec159736-311b-41f6-8588-9b63057a85a2','eya Logged In','LOGIN','2026-03-29 08:50:55'),('f499cb0e-4ba2-43af-8cc2-c012e19e6313','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-03 14:02:04'),('f4abc5d2-c132-4409-ba8d-036aa82d585f','5d383d70-2049-4a41-8ea5-9888180f20e6','ricky Logged In successfully.','LOGIN','2026-04-04 12:50:35'),('fb1b17f0-6fac-45eb-919e-4f6eeba793b6','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-03-29 06:57:21'),('fbf482b3-b8cd-42ea-af84-992151c8961a','dac48d52-2a90-11f1-b096-088fc334d711','admin Logged Out','LOGOUT','2026-04-04 13:10:57');
/*!40000 ALTER TABLE `user_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profile`
--

DROP TABLE IF EXISTS `user_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  KEY `user_profile_ibfk_2` (`dept_id`),
  CONSTRAINT `user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_profile_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profile`
--

LOCK TABLES `user_profile` WRITE;
/*!40000 ALTER TABLE `user_profile` DISABLE KEYS */;
INSERT INTO `user_profile` VALUES ('3603a40c-d20f-4677-8227-660f9963ce4d',1,'testing@gmail.com','name','last','middle','default.png','2002-02-22','2026-04-04 21:09:24','2026-04-04 21:09:24'),('493866f4-e71f-4b4a-8569-998942b02681',3,'test2@gmail.com','Trecy','Rosales','','69cfbf71bb3f5.jpg','2003-02-22','2026-04-03 21:24:01','2026-04-03 22:57:26'),('5d383d70-2049-4a41-8ea5-9888180f20e6',3,'ricky@gmail.com','Ricky','Dela Cruz','','default.png','2003-03-21','2026-03-29 15:03:20','2026-03-29 15:03:20'),('92ff329f-432a-40f3-9e7a-b2fa51d43d36',1,'test@gmail.com','test','test','','default.png','2000-12-05','2026-04-03 21:23:26','2026-04-03 21:23:26'),('c2d03138-8f57-4d02-8fa5-7b4f14928bce',3,'user@gmail.com','User','Huli','Gitna','default.png','2000-02-14','2026-03-29 15:11:36','2026-03-29 15:11:36'),('dac48d52-2a90-11f1-b096-088fc334d711',1,'email@gmail.com','Mickyl','Sumagang','Gaytana',NULL,'2003-06-06','2026-03-28 19:55:45','2026-03-28 19:55:45'),('ec159736-311b-41f6-8588-9b63057a85a2',2,'eya@gmail.com','Eya Nichole','Barcena','Dela Cruz','69c7c6059b9d3.jpg','2000-02-12','2026-03-28 20:13:57','2026-04-03 22:56:17');
/*!40000 ALTER TABLE `user_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_role` (
  `user_role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) NOT NULL,
  `role_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `role_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role`
--

LOCK TABLES `user_role` WRITE;
/*!40000 ALTER TABLE `user_role` DISABLE KEYS */;
INSERT INTO `user_role` VALUES (1,'Admin','2026-03-28 19:55:44','2026-03-28 19:55:44'),(2,'User','2026-03-28 19:55:44','2026-03-28 19:55:44');
/*!40000 ALTER TABLE `user_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_status`
--

DROP TABLE IF EXISTS `user_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_status` (
  `user_status_id` int NOT NULL AUTO_INCREMENT,
  `user_status_name` varchar(45) NOT NULL,
  `user_status_created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_status_updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_status`
--

LOCK TABLES `user_status` WRITE;
/*!40000 ALTER TABLE `user_status` DISABLE KEYS */;
INSERT INTO `user_status` VALUES (1,'Active','2026-03-28 19:55:44','2026-03-28 19:55:44'),(2,'Inactive','2026-03-28 19:55:44','2026-03-28 19:55:44');
/*!40000 ALTER TABLE `user_status` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-04 21:24:28
