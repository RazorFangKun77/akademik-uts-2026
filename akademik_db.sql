-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: akademik_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `akademik_db`
--

/*!40000 DROP DATABASE IF EXISTS `akademik_db`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `akademik_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `akademik_db`;

--
-- Table structure for table `nilai`
--

DROP TABLE IF EXISTS `nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nilai` (
  `nim` char(10) NOT NULL,
  `nm_lengkap` varchar(100) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) NOT NULL,
  `nilai_huruf` char(1) NOT NULL,
  `predikat` varchar(100) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`nim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nilai`
--

LOCK TABLES `nilai` WRITE;
/*!40000 ALTER TABLE `nilai` DISABLE KEYS */;
INSERT INTO `nilai` VALUES ('3202416001','Andi Wijaya','https://randomuser.me/api/portraits/men/12.jpg',92.50,'A','Sangat Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416002','Bunga Lestari','https://randomuser.me/api/portraits/women/44.jpg',88.00,'A','Sangat Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416003','Candra Putra','https://randomuser.me/api/portraits/men/45.jpg',78.50,'B','Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416004','Dewi Salsabila','https://randomuser.me/api/portraits/women/68.jpg',81.00,'A','Sangat Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416005','Eko Prasetyo','https://randomuser.me/api/portraits/men/28.jpg',66.25,'C','Cukup Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416006','Fira Aulia','https://randomuser.me/api/portraits/women/82.jpg',59.00,'D','Kurang Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416007','Gilang Ramadhan','https://randomuser.me/api/portraits/men/24.jpg',95.00,'A','Sangat Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416008','Hana Permata','https://randomuser.me/api/portraits/women/14.jpg',72.00,'B','Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416009','Iman Satria','https://randomuser.me/api/portraits/men/64.jpg',63.50,'C','Cukup Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416010','Jasmine Putri','https://randomuser.me/api/portraits/women/91.jpg',49.00,'E','Tidak Lulus','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416011','Krisna Mahardika','https://randomuser.me/api/portraits/men/86.jpg',84.75,'A','Sangat Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416012','Lia Rahmawati','https://randomuser.me/api/portraits/women/39.jpg',70.50,'B','Memuaskan','2026-10-26 20:55:11','2026-10-26 20:55:11'),('3202416039','Rafi Achmad Arlino','https://randomuser.me/api/portraits/men/32.jpg',85.00,'A','Sangat Memuaskan','2026-10-15 07:20:15','2026-10-26 20:55:11'),('3202416065','Raffa Muhammad Shaddam','https://randomuser.me/api/portraits/men/75.jpg',98.00,'A','Sangat Memuaskan','2026-10-15 07:26:18','2026-10-26 20:55:11');
/*!40000 ALTER TABLE `nilai` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-27  3:55:43
