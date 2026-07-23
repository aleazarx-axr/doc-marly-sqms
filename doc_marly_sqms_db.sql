-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: sqms_db
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `auth_logs`
--

DROP TABLE IF EXISTS `auth_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `event_type` enum('login_success','login_failed','account_lockout','logout','suspicious_activity','password_setup') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_logs`
--

LOCK TABLES `auth_logs` WRITE;
/*!40000 ALTER TABLE `auth_logs` DISABLE KEYS */;
INSERT INTO `auth_logs` VALUES (1,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-15 05:44:44'),(2,NULL,'agdajhgs','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-15 05:44:48'),(3,NULL,'kajsdkajsdhkajsh','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-15 05:44:52'),(4,NULL,'kjasdnkajsnda','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-15 05:44:55'),(5,NULL,'kjaskdjakjasb','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-15 05:44:57'),(6,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 06:08:02'),(7,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 06:53:41'),(8,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','password_setup','2026-07-15 07:27:38'),(9,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-15 07:28:03'),(10,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 07:28:12'),(11,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-15 07:28:19'),(12,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 07:28:32'),(13,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-15 07:53:07'),(14,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 07:55:55'),(15,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-16 06:24:39'),(16,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-16 06:32:01'),(17,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-16 06:33:36'),(18,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-16 06:33:46'),(19,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-16 06:55:12'),(20,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-21 16:16:32'),(21,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-21 16:16:39'),(22,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-21 16:17:33'),(23,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-21 17:50:40'),(24,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 00:14:37'),(25,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 03:02:29'),(26,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 04:46:17'),(27,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 05:53:11'),(28,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 07:15:21'),(29,2,'staff1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-22 07:15:30'),(30,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 07:16:17'),(31,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 07:17:20'),(32,4,'aleazarjohnvillanueva','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-22 07:17:42'),(33,4,'aleazarjohnvillanueva','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-22 07:17:51'),(34,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 07:18:25'),(35,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 07:19:43'),(36,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 07:20:10'),(37,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 09:23:05'),(38,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 09:29:55'),(39,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 09:30:10'),(40,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 09:32:00'),(41,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 09:32:56'),(42,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 00:14:55'),(43,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 00:21:00'),(44,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 01:21:12'),(45,7,'michael','192.168.0.86','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','password_setup','2026-07-23 02:21:56'),(46,7,'michael','192.168.0.86','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 02:22:35'),(47,7,'michael','192.168.0.86','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 02:23:16');
/*!40000 ALTER TABLE `auth_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `counter_citizen_categories`
--

DROP TABLE IF EXISTS `counter_citizen_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `counter_citizen_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `counter_id` int NOT NULL,
  `citizen_category` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ccc_counter` (`counter_id`),
  CONSTRAINT `fk_ccc_counter` FOREIGN KEY (`counter_id`) REFERENCES `counters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counter_citizen_categories`
--

LOCK TABLES `counter_citizen_categories` WRITE;
/*!40000 ALTER TABLE `counter_citizen_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `counter_citizen_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `counter_services`
--

DROP TABLE IF EXISTS `counter_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `counter_services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `counter_id` int NOT NULL,
  `service_id` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_counter_service` (`counter_id`,`service_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `counter_services_ibfk_1` FOREIGN KEY (`counter_id`) REFERENCES `counters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `counter_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counter_services`
--

LOCK TABLES `counter_services` WRITE;
/*!40000 ALTER TABLE `counter_services` DISABLE KEYS */;
INSERT INTO `counter_services` VALUES (1,5,4,1,'2026-07-22 06:20:03'),(2,6,4,1,'2026-07-22 06:20:06'),(3,3,1,0,'2026-07-22 06:41:19'),(4,3,4,1,'2026-07-22 09:30:52');
/*!40000 ALTER TABLE `counter_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `counters`
--

DROP TABLE IF EXISTS `counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `counters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `counter_type` enum('General','Dedicated','Priority') DEFAULT 'General',
  `staff_id` int DEFAULT NULL,
  `overflow_general` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_archived` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counters`
--

LOCK TABLES `counters` WRITE;
/*!40000 ALTER TABLE `counters` DISABLE KEYS */;
INSERT INTO `counters` VALUES (1,'Window 1','General',NULL,0,'2026-07-10 14:44:38',1,'2026-07-22 06:24:56',1),(2,'Window 2','General',NULL,0,'2026-07-10 14:44:52',1,'2026-07-22 06:25:02',1),(3,'Window 3','General',5,0,'2026-07-13 06:06:50',1,'2026-07-22 09:30:52',0),(4,'Window 1','General',2,0,'2026-07-22 06:20:01',1,'2026-07-22 06:24:58',1),(5,'Window 1','General',2,0,'2026-07-22 06:20:03',1,'2026-07-22 06:24:59',1),(6,'Window 1','General',2,0,'2026-07-22 06:20:05',1,'2026-07-22 06:25:00',1),(7,'Window 1','General',NULL,0,'2026-07-22 06:25:11',1,'2026-07-22 06:41:14',1);
/*!40000 ALTER TABLE `counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue_sessions`
--

DROP TABLE IF EXISTS `queue_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int DEFAULT NULL COMMENT 'NULL for office (all services), specific ID for offsite (single service)',
  `status` enum('active','closed') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `queue_sessions_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue_sessions`
--

LOCK TABLES `queue_sessions` WRITE;
/*!40000 ALTER TABLE `queue_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requirements`
--

DROP TABLE IF EXISTS `requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `requirements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_archived` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requirements`
--

LOCK TABLES `requirements` WRITE;
/*!40000 ALTER TABLE `requirements` DISABLE KEYS */;
INSERT INTO `requirements` VALUES (1,'Barangay Indigency','2026-07-10 12:24:12',0),(2,'Valid ID','2026-07-10 12:24:12',0),(3,'Medical Certificate','2026-07-10 12:24:12',0),(4,'Death Certificate','2026-07-10 12:24:12',0),(6,'Request Letter','2026-07-10 12:27:54',0);
/*!40000 ALTER TABLE `requirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_steps`
--

DROP TABLE IF EXISTS `service_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_steps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `step_order` int NOT NULL,
  `step_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `service_steps_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_steps`
--

LOCK TABLES `service_steps` WRITE;
/*!40000 ALTER TABLE `service_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `requirements` text NOT NULL,
  `prefix` varchar(10) DEFAULT NULL,
  `starting_number` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `is_archived` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,NULL,'Financial Assistance',NULL,'Medical Certificate, Request Letter',NULL,1,'2026-07-10 08:21:13','2026-07-22 06:11:08',1,1),(2,NULL,'Financial Assistance',NULL,'ajsaid, asdasd',NULL,1,'2026-07-13 05:41:12','2026-07-22 06:11:06',1,1),(3,NULL,'awdadwa',NULL,'',NULL,1,'2026-07-13 06:49:07','2026-07-22 06:11:09',1,1),(4,'MEDICAL_ASSISTANCE','Medical Assistance','','Birth Certificate\r\nValid ID\r\nBarangay Indigency','',1,'2026-07-22 06:02:07','2026-07-22 06:02:14',1,0);
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('smtp_from_email','no-reply@docmarly.com','Sender Email Address'),('smtp_from_name','Doc Marly SQMS','Sender Name'),('smtp_host','smtp.gmail.com','SMTP Server Host'),('smtp_pass','','SMTP App Password'),('smtp_port','587','SMTP Server Port'),('smtp_user','testuser98234@gmail.com','SMTP Username (Gmail Address)');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `service_id` int NOT NULL,
  `counter_id` int DEFAULT NULL,
  `citizen_category` varchar(50) DEFAULT NULL,
  `status` enum('waiting','called','serving','done','no-show','transferred') DEFAULT 'waiting',
  `issued_at` timestamp NULL DEFAULT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_t_service` (`service_id`),
  KEY `fk_t_counter` (`counter_id`),
  CONSTRAINT `fk_t_counter` FOREIGN KEY (`counter_id`) REFERENCES `counters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_t_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user',
  `status` enum('active','inactive','archived') NOT NULL DEFAULT 'active',
  `failed_attempts` int DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `setup_token` varchar(64) DEFAULT NULL,
  `token_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'admin','testuser98234@gmail.com','$2y$12$0l39qtCqwVCQ2F0pnyQPLe1MYFlkJn5zkxHqhUtxaCv5EybWNEizG','admin','active',0,NULL,NULL,NULL,'2026-07-10 07:25:22','091738','2026-07-23 02:24:06'),(2,NULL,'staff1',NULL,'$2y$12$eQzDEDJNqqGjQvSv1qGzE.1Z8KW0VxUlKGMLTw5bDXliIcwWR/XwW','staff','archived',1,NULL,NULL,NULL,'2026-07-10 07:56:09',NULL,NULL),(3,NULL,'staff2',NULL,'$2y$12$XED6e/PuF3.3QRa0Tc837.Xxyrdzw/Zy3ZOlt0Idbhy9Hidhn8JjW','staff','archived',0,NULL,NULL,NULL,'2026-07-13 00:44:18',NULL,NULL),(4,NULL,'aleazarjohnvillanueva','aleazarjohnvillanueva@gmail.com','$2y$12$.5XoTsX0FT1wrPWsbZyCZeO3vbcAo/2aD8tbHOxC4qtj/.y8z3V22','service_staff','active',2,NULL,NULL,NULL,'2026-07-15 07:19:24',NULL,NULL),(5,NULL,'michaelmartinez','kiraelse9@gmail.com','$2y$12$LwevcAxrBP2YhD1jKOl1lujZAwd5T24T7GzcVfGap7U2yi/RCB3bO','service_staff','active',0,NULL,NULL,NULL,'2026-07-15 07:26:57',NULL,NULL),(6,'michael martinez','michaelmartinez1','aleazarjohnvilanueva@gmail.com','$2y$12$qp8l9BReUOq45F2c4PdqU.GAQLQX2Wenph2ciVRBJuwN3i/3NgI6y','staff','archived',0,NULL,'cd957cae9b24dc169e4aa4f0a844033692ed97c35c98dc38fe2b959f0de7722d','2026-07-15 23:51:23','2026-07-15 07:51:23',NULL,NULL),(7,'Michael','michael','michaelmrtnz10@gmail.com','$2y$12$umTR1pcSJpQXLn85Az88EeCIdwft0dAQKXUWjQLagsgzhn0E33rCK','service_staff','active',0,NULL,NULL,NULL,'2026-07-23 01:23:07',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-23 10:35:50
