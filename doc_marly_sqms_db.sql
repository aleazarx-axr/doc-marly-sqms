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
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_logs`
--

LOCK TABLES `auth_logs` WRITE;
/*!40000 ALTER TABLE `auth_logs` DISABLE KEYS */;
INSERT INTO `auth_logs` VALUES (1,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-15 05:44:44'),(2,NULL,'agdajhgs','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-15 05:44:48'),(3,NULL,'kajsdkajsdhkajsh','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-15 05:44:52'),(4,NULL,'kjasdnkajsnda','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-15 05:44:55'),(5,NULL,'kjaskdjakjasb','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-15 05:44:57'),(6,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 06:08:02'),(7,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 06:53:41'),(8,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','password_setup','2026-07-15 07:27:38'),(9,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-15 07:28:03'),(10,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 07:28:12'),(11,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-15 07:28:19'),(12,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 07:28:32'),(13,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-15 07:53:07'),(14,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-15 07:55:55'),(15,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-16 06:24:39'),(16,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-16 06:32:01'),(17,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-16 06:33:36'),(18,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-16 06:33:46'),(19,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-16 06:55:12'),(20,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-21 16:16:32'),(21,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-21 16:16:39'),(22,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-21 16:17:33'),(23,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-21 17:50:40'),(24,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 00:14:37'),(25,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 03:02:29'),(26,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 04:46:17'),(27,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 05:53:11'),(28,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 07:15:21'),(29,2,'staff1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-22 07:15:30'),(30,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 07:16:17'),(31,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 07:17:20'),(32,4,'aleazarjohnvillanueva','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-22 07:17:42'),(33,4,'aleazarjohnvillanueva','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-22 07:17:51'),(34,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 07:18:25'),(35,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 07:19:43'),(36,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 07:20:10'),(37,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 09:23:05'),(38,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 09:29:55'),(39,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 09:30:10'),(40,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-22 09:32:00'),(41,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-22 09:32:56'),(42,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 00:14:55'),(43,5,'michaelmartinez','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 00:21:00'),(44,1,'admin','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 01:21:12'),(45,7,'michael','192.168.0.86','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','password_setup','2026-07-23 02:21:56'),(46,7,'michael','192.168.0.86','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 02:22:35'),(47,7,'michael','192.168.0.86','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 02:23:16'),(48,7,'michael','192.168.0.86','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 02:42:58'),(49,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 02:46:45'),(50,4,'aleazarjohnvillanueva','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-23 02:47:11'),(51,4,'aleazarjohnvillanueva','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 02:48:22'),(52,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 02:50:00'),(53,5,'michaelmartinez','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 02:50:46'),(54,5,'michaelmartinez','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 03:11:05'),(55,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 03:11:31'),(56,4,'aleazarjohnvillanueva','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 03:36:41'),(57,7,'michael','192.168.0.133','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67','login_success','2026-07-23 05:04:20'),(58,7,'michael','192.168.0.133','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67','logout','2026-07-23 05:04:49'),(59,7,'michael','192.168.0.133','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67','login_failed','2026-07-23 05:19:32'),(60,7,'michael','192.168.0.133','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67','login_failed','2026-07-23 05:19:50'),(61,7,'michael','192.168.0.133','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67','login_failed','2026-07-23 05:20:10'),(62,7,'michael','192.168.0.133','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67','login_failed','2026-07-23 05:20:14'),(63,7,'michael','192.168.0.133','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36 Edg/92.0.902.67','login_success','2026-07-23 05:22:26'),(64,4,'aleazarjohnvillanueva','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-23 06:16:19'),(65,4,'aleazarjohnvillanueva','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:17:36'),(66,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:19:22'),(67,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 06:21:45'),(68,5,'michaelmartinez','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:22:10'),(69,5,'michaelmartinez','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 06:45:58'),(70,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:46:22'),(71,7,'michael','192.168.0.124','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:46:52'),(72,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 06:47:08'),(73,7,'michael','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-23 06:47:29'),(74,7,'michael','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:49:06'),(75,7,'michael','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 06:51:07'),(76,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:51:26'),(77,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 06:55:05'),(78,5,'michaelmartinez','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:55:29'),(79,5,'michaelmartinez','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 06:56:49'),(80,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 06:57:08'),(81,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 07:06:35'),(82,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 07:07:06'),(83,7,'michael','192.168.0.124','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 07:14:32'),(84,7,'michael','192.168.0.124','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 07:15:16'),(85,7,'michael','192.168.0.124','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 07:15:53'),(86,1,'admin','192.168.0.124','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 07:16:28'),(87,4,'aleazarjohnvillanueva','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 07:46:55'),(88,1,'admin','192.168.0.124','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 07:53:44'),(89,1,'admin','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 08:14:13'),(90,5,'michaelmartinez','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 08:16:09'),(91,8,'user2','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','password_setup','2026-07-23 08:20:02'),(92,8,'user2','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 08:21:06'),(93,8,'user2','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-23 08:37:27'),(94,8,'user2','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 08:37:54'),(95,8,'user2','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 09:44:02'),(96,5,'michaelmartinez','192.168.0.96','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-23 10:01:05'),(97,1,'admin','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-24 09:57:43'),(98,4,'aleazarjohnvillanueva','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-24 10:06:51'),(99,4,'aleazarjohnvillanueva','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-24 10:08:09'),(100,4,'aleazarjohnvillanueva','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-24 10:34:39'),(101,8,'user2','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-24 10:35:07'),(102,4,'aleazarjohnvillanueva','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-24 11:12:50'),(103,8,'user2','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','logout','2026-07-24 11:30:03'),(104,5,'michaelmartinez','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-24 11:31:11'),(105,4,'aleazarjohnvillanueva','192.168.254.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-24 12:18:33'),(106,1,'admin','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 05:06:44'),(107,1,'admin','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 08:29:20'),(108,1,'admin','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 09:21:46'),(109,1,'admin','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 10:14:20'),(110,1,'admin','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_failed','2026-07-26 11:40:17'),(111,1,'admin','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 11:40:51'),(112,1,'admin','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 12:49:51'),(113,8,'user2','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 13:19:52'),(114,4,'aleazarjohnvillanueva','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 13:49:08'),(115,4,'aleazarjohnvillanueva','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 14:23:30'),(116,8,'user2','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 14:23:39'),(117,1,'admin','192.168.1.14','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 14:33:13'),(118,4,'aleazarjohnvillanueva','192.168.1.14','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 14:33:42'),(119,8,'user2','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 14:34:12'),(120,5,'michaelmartinez','192.168.137.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 14:34:52'),(121,1,'admin','192.168.0.125','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','login_success','2026-07-26 23:29:57');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counter_citizen_categories`
--

LOCK TABLES `counter_citizen_categories` WRITE;
/*!40000 ALTER TABLE `counter_citizen_categories` DISABLE KEYS */;
INSERT INTO `counter_citizen_categories` VALUES (1,8,'Senior Citizen','2026-07-26 11:42:18'),(2,8,'PWD','2026-07-26 11:42:18'),(3,8,'Pregnant','2026-07-26 11:42:18');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counter_services`
--

LOCK TABLES `counter_services` WRITE;
/*!40000 ALTER TABLE `counter_services` DISABLE KEYS */;
INSERT INTO `counter_services` VALUES (3,3,1,0,'2026-07-22 06:41:19'),(4,3,4,1,'2026-07-22 09:30:52'),(5,2,4,1,'2026-07-23 03:34:19'),(6,2,6,1,'2026-07-23 07:34:03'),(7,1,7,1,'2026-07-23 09:32:35'),(8,8,4,1,'2026-07-26 11:42:18'),(9,8,6,1,'2026-07-26 11:42:18'),(10,8,7,1,'2026-07-26 11:42:18');
/*!40000 ALTER TABLE `counter_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `counter_staff`
--

DROP TABLE IF EXISTS `counter_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `counter_staff` (
  `counter_id` int NOT NULL,
  `staff_id` int NOT NULL,
  PRIMARY KEY (`counter_id`,`staff_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `counter_staff_ibfk_1` FOREIGN KEY (`counter_id`) REFERENCES `counters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `counter_staff_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counter_staff`
--

LOCK TABLES `counter_staff` WRITE;
/*!40000 ALTER TABLE `counter_staff` DISABLE KEYS */;
INSERT INTO `counter_staff` VALUES (1,5),(2,5),(1,7),(3,7),(3,8),(8,8);
/*!40000 ALTER TABLE `counter_staff` ENABLE KEYS */;
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
  `current_staff_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_current_staff` (`current_staff_id`),
  CONSTRAINT `fk_current_staff` FOREIGN KEY (`current_staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counters`
--

LOCK TABLES `counters` WRITE;
/*!40000 ALTER TABLE `counters` DISABLE KEYS */;
INSERT INTO `counters` VALUES (1,'Window 1','General',NULL,0,'2026-07-10 14:44:38',1,'2026-07-26 14:36:56',0,5),(2,'Window 2','General',5,0,'2026-07-10 14:44:52',1,'2026-07-26 14:36:56',0,NULL),(3,'Window 3','General',7,0,'2026-07-13 06:06:50',1,'2026-07-26 15:02:16',0,NULL),(8,'Window 4','Priority',NULL,1,'2026-07-26 11:42:18',1,'2026-07-26 15:02:16',0,8);
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,NULL,'Financial Assistance',NULL,'Medical Certificate, Request Letter',NULL,1,'2026-07-10 08:21:13','2026-07-22 06:11:08',1,1),(2,NULL,'Financial Assistance',NULL,'ajsaid, asdasd',NULL,1,'2026-07-13 05:41:12','2026-07-22 06:11:06',1,1),(3,NULL,'awdadwa',NULL,'',NULL,1,'2026-07-13 06:49:07','2026-07-22 06:11:09',1,1),(4,'MEDICAL_ASSISTANCE','Medical Assistance','','Birth Certificate\r\nValid ID\r\nBarangay Indigency','',1,'2026-07-22 06:02:07','2026-07-22 06:02:14',1,0),(6,'FINANCIAL_ASSISTANCE','Financial Assistance','','','cee2ac0b',1,'2026-07-23 07:33:50','2026-07-23 07:33:50',1,0),(7,'BURIAL_ASSISTANCE','Burial Assistance','','','90c60dc9',1,'2026-07-23 07:38:07','2026-07-23 07:38:07',1,0);
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
INSERT INTO `settings` VALUES ('smtp_from_email','','Sender Email Address'),('smtp_from_name','','Sender Name'),('smtp_host','','SMTP Server Host'),('smtp_pass','','SMTP App Password'),('smtp_port','','SMTP Server Port'),('smtp_user','','SMTP Username (Gmail Address)');
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
  `requirements_checked` text,
  `status` enum('waiting','called','serving','done','no-show','transferred') DEFAULT 'waiting',
  `issued_at` timestamp NULL DEFAULT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_t_counter` (`counter_id`),
  KEY `idx_status_service_issued` (`status`,`service_id`,`issued_at`),
  KEY `idx_service_created` (`service_id`,`created_at`),
  CONSTRAINT `fk_t_counter` FOREIGN KEY (`counter_id`) REFERENCES `counters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_t_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,'Rennier Indayo','MED-001',4,3,'Regular',NULL,'done',NULL,'2026-07-23 02:50:51','2026-07-23 02:50:54','2026-07-23 02:49:45'),(2,'VILLANUEVA, ALEAZAR JOHN RITO.','M-001',4,2,'Regular',NULL,'done',NULL,'2026-07-23 06:22:14','2026-07-23 06:22:21','2026-07-23 03:36:50'),(3,'WMSU Ipil','M-001',4,2,'Regular',NULL,'done',NULL,'2026-07-23 06:22:30','2026-07-23 06:22:32','2026-07-23 03:36:57'),(4,'','M-001',4,2,'Regular',NULL,'done',NULL,'2026-07-23 06:22:34','2026-07-23 06:22:35','2026-07-23 03:37:04'),(5,'Aleazar','M-002',4,2,'Regular',NULL,'no-show','2026-07-23 06:19:56','2026-07-23 06:22:37',NULL,'2026-07-23 06:19:56'),(6,'Aleazar','M-003',4,2,'Regular',NULL,'no-show','2026-07-23 06:19:56','2026-07-23 06:22:46','2026-07-23 06:28:00','2026-07-23 06:19:56'),(7,'Ryan Autida','M-004',4,2,'Regular',NULL,'done','2026-07-23 06:30:27','2026-07-23 06:30:38','2026-07-23 06:30:53','2026-07-23 06:30:27'),(8,'','M-005',4,2,'Regular',NULL,'done','2026-07-23 06:31:08','2026-07-23 06:31:11','2026-07-23 06:31:24','2026-07-23 06:31:08'),(9,'','M-006',4,2,'Regular',NULL,'no-show','2026-07-23 06:31:30','2026-07-23 06:31:57',NULL,'2026-07-23 06:31:30'),(10,'','M-007',4,2,'Regular',NULL,'done','2026-07-23 06:31:49','2026-07-23 06:32:16','2026-07-23 06:33:40','2026-07-23 06:31:49'),(11,'','M-008',4,2,'Regular',NULL,'done','2026-07-23 06:31:54','2026-07-23 06:33:44','2026-07-23 06:35:13','2026-07-23 06:31:54'),(12,'','M-009',4,2,'Regular',NULL,'done','2026-07-23 06:38:12','2026-07-23 06:38:34','2026-07-23 06:39:07','2026-07-23 06:38:12'),(13,'','M-010',4,2,'Regular',NULL,'done','2026-07-23 06:39:04','2026-07-23 06:39:11','2026-07-23 06:39:22','2026-07-23 06:39:04'),(14,'','M-011',4,2,'Regular',NULL,'done','2026-07-23 06:41:01','2026-07-23 06:41:08','2026-07-23 06:42:26','2026-07-23 06:41:01'),(15,'','M-012',4,2,'PWD',NULL,'done','2026-07-23 06:42:11','2026-07-23 06:42:33','2026-07-23 06:43:43','2026-07-23 06:42:11'),(16,'','M-013',4,2,'Regular',NULL,'no-show','2026-07-23 06:43:36','2026-07-23 06:43:46',NULL,'2026-07-23 06:43:36'),(17,'','M-014',4,2,'Regular',NULL,'done','2026-07-23 06:43:39','2026-07-23 06:44:06','2026-07-23 06:45:29','2026-07-23 06:43:39'),(18,'','M-015',4,2,'Regular',NULL,'done','2026-07-23 06:45:34','2026-07-23 06:45:41','2026-07-23 06:55:30','2026-07-23 06:45:34'),(19,'','M-016',4,3,'Regular',NULL,'done','2026-07-23 06:51:59','2026-07-23 06:52:06','2026-07-23 06:52:09','2026-07-23 06:51:59'),(20,'','B-001',7,1,'Regular',NULL,'done','2026-07-23 07:47:16','2026-07-24 11:31:41','2026-07-24 11:31:46','2026-07-23 07:47:16'),(21,'','F-001',6,2,'Regular',NULL,'done','2026-07-23 08:14:28','2026-07-23 08:17:00','2026-07-23 08:17:12','2026-07-23 08:14:28'),(22,'','M-017',4,2,'Regular',NULL,'done','2026-07-23 08:17:42','2026-07-23 08:17:52','2026-07-23 08:18:10','2026-07-23 08:17:42'),(23,'','M-018',4,2,'Regular',NULL,'done','2026-07-23 08:18:26','2026-07-23 08:38:32','2026-07-24 11:34:26','2026-07-23 08:18:26'),(24,'','F-002',6,2,'Regular',NULL,'done','2026-07-23 08:18:30','2026-07-24 11:34:41','2026-07-24 11:34:53','2026-07-23 08:18:30'),(25,'','B-001',7,1,'Senior Citizen',NULL,'done','2026-07-24 11:13:24','2026-07-24 11:32:02','2026-07-24 11:33:48','2026-07-24 11:13:24'),(26,'','F-001',6,2,'Regular',NULL,'done','2026-07-24 11:13:28','2026-07-24 11:34:56','2026-07-24 11:35:07','2026-07-24 11:13:28'),(27,'','M-001',4,3,'Regular',NULL,'done','2026-07-24 11:13:30','2026-07-24 11:13:44','2026-07-24 11:22:56','2026-07-24 11:13:30'),(28,'','F-002',6,2,'Regular',NULL,'done','2026-07-24 11:22:02','2026-07-24 11:35:10','2026-07-24 11:35:14','2026-07-24 11:22:02'),(29,'','F-003',6,2,'Regular',NULL,'done','2026-07-24 11:22:45','2026-07-24 11:35:17','2026-07-24 11:35:26','2026-07-24 11:22:45'),(30,'','B-002',7,1,'Regular',NULL,'done','2026-07-24 11:33:13','2026-07-24 11:34:08','2026-07-24 11:35:32','2026-07-24 11:33:13'),(31,'','F-004',6,2,'Regular',NULL,'done','2026-07-24 11:33:16','2026-07-24 11:35:48','2026-07-24 11:35:58','2026-07-24 11:33:16'),(32,'','M-002',4,3,'Regular',NULL,'done','2026-07-24 11:33:18','2026-07-26 13:31:11','2026-07-26 14:26:25','2026-07-24 11:33:18'),(33,'','B-003',7,1,'Regular',NULL,'done','2026-07-24 11:33:34','2026-07-24 11:35:35','2026-07-24 11:35:42','2026-07-24 11:33:34'),(34,'WMSU Ipil','F-001',6,8,'Regular',NULL,'done','2026-07-26 14:24:16','2026-07-26 15:02:29','2026-07-26 15:02:39','2026-07-26 14:24:16'),(35,'VILLANUEVA, ALEAZAR JOHN RITO.','B-001',7,1,'Regular','[]','done','2026-07-26 14:25:52','2026-07-26 14:35:23','2026-07-26 14:35:37','2026-07-26 14:25:52'),(36,'VILLANUEVA, ALEAZAR JOHN RITO.','F-002',6,8,'Regular',NULL,'done','2026-07-26 14:32:18','2026-07-26 15:32:51','2026-07-26 15:32:53','2026-07-26 14:32:18'),(37,'Roland Albar','B-002',7,1,'Senior Citizen',NULL,'called','2026-07-26 14:36:04','2026-07-26 14:53:58',NULL,'2026-07-26 14:36:04'),(38,'michael martinez','B-003',7,8,'Regular',NULL,'no-show','2026-07-26 14:36:24','2026-07-26 15:32:55',NULL,'2026-07-26 14:36:24'),(39,'','M-001',4,3,'Regular','[\"Birth Certificate\",\"Valid ID\",\"Barangay Indigency\"]','done','2026-07-26 14:53:06','2026-07-26 14:53:48','2026-07-26 15:00:18','2026-07-26 14:53:06');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'admin','testuser98234@gmail.com','$2y$12$0l39qtCqwVCQ2F0pnyQPLe1MYFlkJn5zkxHqhUtxaCv5EybWNEizG','admin','active',0,NULL,NULL,NULL,'2026-07-10 07:25:22',NULL,NULL),(2,NULL,'staff1',NULL,'$2y$12$eQzDEDJNqqGjQvSv1qGzE.1Z8KW0VxUlKGMLTw5bDXliIcwWR/XwW','staff','archived',1,NULL,NULL,NULL,'2026-07-10 07:56:09',NULL,NULL),(3,NULL,'staff2',NULL,'$2y$12$XED6e/PuF3.3QRa0Tc837.Xxyrdzw/Zy3ZOlt0Idbhy9Hidhn8JjW','staff','archived',0,NULL,NULL,NULL,'2026-07-13 00:44:18',NULL,NULL),(4,NULL,'aleazarjohnvillanueva','aleazarjohnvillanueva@gmail.com','$2y$12$DTMZV6Yr66rXCITYEsYTIuiKFqX7NgRpSz.RSickffbU7IJcg2VFS','information_staff','active',0,NULL,NULL,NULL,'2026-07-15 07:19:24',NULL,NULL),(5,NULL,'michaelmartinez','kiraelse9@gmail.com','$2y$12$LwevcAxrBP2YhD1jKOl1lujZAwd5T24T7GzcVfGap7U2yi/RCB3bO','service_staff','active',0,NULL,NULL,NULL,'2026-07-15 07:26:57',NULL,NULL),(6,'michael martinez','michaelmartinez1','aleazarjohnvilanueva@gmail.com','$2y$12$qp8l9BReUOq45F2c4PdqU.GAQLQX2Wenph2ciVRBJuwN3i/3NgI6y','staff','archived',0,NULL,'cd957cae9b24dc169e4aa4f0a844033692ed97c35c98dc38fe2b959f0de7722d','2026-07-15 23:51:23','2026-07-15 07:51:23',NULL,NULL),(7,NULL,'michael','michaelmrtnz10@gmail.com','$2y$12$umTR1pcSJpQXLn85Az88EeCIdwft0dAQKXUWjQLagsgzhn0E33rCK','service_staff','active',0,NULL,NULL,NULL,'2026-07-23 01:23:07',NULL,NULL),(8,'User 2','user2','u0822444@gmail.com','$2y$12$QfJm7i4cZXcXt2ZpQGSEjuT/LiIksdfuoFM0RjTiPpg2kQQsMGzCy','service_staff','active',0,NULL,NULL,NULL,'2026-07-23 08:19:23',NULL,NULL);
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

-- Dump completed on 2026-07-27  7:35:48
