-- MySQL dump 10.13  Distrib 9.2.0, for macos15.2 (arm64)
--
-- Host: localhost    Database: crossword
-- ------------------------------------------------------
-- Server version	9.2.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `progress_easy` int NOT NULL DEFAULT '0',
  `progress_intermediate` int NOT NULL DEFAULT '0',
  `progress_hard` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'nirbhay','nirbhay200317@gmail.com','$2y$12$B7/myIKMbGX67q85O5LGq.JVusj4y9Nasw5er2gtJSa4aMrkWRI.O',1,0,0),(2,'hari','hari1234@gmail.com','$2y$12$5V88zq.VIu2Xw1tockaSDuHnd35by5jmNuwN2FFSWndWmiAH7dC3y',0,0,0),(3,'kuldeep','kd123@gmail.com','$2y$12$oOrh4c1UmtgEr7REmxEc8u8Jb4kACisFukto8Mnd01B7gmRPCPhOi',0,0,0),(4,'Kapil Kumar','kp12345@gmail.com','$2y$12$ToL6Jqj44i5aLdp6a5LxAupmnDAmWjbqSbZ3peP3E1bNpF6eZE0pa',0,0,0),(5,'Ryan','ryan123@gmail.com','$2y$12$sT6RvfF366xDes1wkwAKO.kgqud9dMH5TXcU5xtmKITF0u9632sju',0,0,0),(6,'Mayuri','mayuri123@gmail.com','$2y$12$6j5oAcC1.0jkZfv8XpdFFOsgRCDr8g5Hc1KSJRmeyt5SrN72.CAcC',3,1,0),(7,'Ajay','ajay123@gmail.com','$2y$12$71wyVIWF8lqSCZnOpWyI0OHz4wpSJjU2chIkjmwRiSlzsG1MtjXwm',3,0,0),(8,'Jai','jai419@gmail.com','$2y$12$lBaH4yp30vjltmSLNkDDsONC83/pwM2hIb84Ar/OM62ynLMZfMdaK',0,0,0),(9,'M','m123@gmail.com','$2y$12$E0sS8joF1Mo//VCiCYmCaeQfPwx3XzmfBvPA2/IOh/hwMYAleg4P6',0,0,0),(10,'H','h123@gmail.com','$2y$12$jxAu3uZZDSgSIL7xLSgJceQZPiLFbUcw2Nmeujp7w3N43XyaK0jCS',0,0,0),(11,'Harsh','harsh123@gmail.com','$2y$12$Foxk6vWO7gBEoQhq1CT21eVVqzRdibeDaq01iUuiksE/7e1t/xIvW',0,0,0),(12,'Chirag','chirag543@gmail.com','$2y$12$00Wz7viPY0aukE/T5HNAwuYaw4utU7JZg.cJzm3cXv8o1uQJSi8CK',0,0,0),(13,'koi','koi908@gmail.com','$2y$12$JVLHScr9GVlmp6wq8MOSaeh58AsAOPHbGZXWAmHbace7LTwm61Gxu',0,0,0),(14,'KD','kd12345@gmail.com','$2y$12$cxmgLI8y827dkiSQTrYxr.q6xnkgHdcLwT18qwNIad2KVVI7XznMq',0,0,0),(15,'KK','kk123@gmail.com','$2y$12$P7icgHvmlahY5ke0PyTHZuusANggiR37gIZncubbcYASO.DMdFkx2',0,0,0),(16,'BB','bb123@gmail.com','$2y$12$iUcjopYc84GwoOr2l3R9qumBqqqj58M7IlY0tA54y.aXKS8hwAyoy',0,0,0),(17,'MF','mf234@gmail.com','$2y$12$KEMF3.l/emM25Pxmf01uzeYqtnOwpcWpZSkuAyLnKHT5lPbpWSksa',0,0,0),(18,'lol','lol123@gmail.com','$2y$12$90tHD.4tChdyVNPRmIp5Ve5BrO1yaFP5OczBX8TtyKHxkoYlQh7dm',2,0,0);
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

-- Dump completed on 2025-06-09 20:32:30
