-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: rcef
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
-- Table structure for table `account_balances`
--

DROP TABLE IF EXISTS `account_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `debit_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`period_id`),
  KEY `period_id` (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=461 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_balances`
--

LOCK TABLES `account_balances` WRITE;
/*!40000 ALTER TABLE `account_balances` DISABLE KEYS */;
INSERT INTO `account_balances` VALUES (1,10,2,2410700.66,0.00),(2,11,2,400580.54,0.00),(3,12,2,18668.77,5000.00),(4,13,2,0.00,0.00),(5,102,2,0.00,1203712.27),(6,103,2,660384.30,0.00),(8,106,2,36402000.00,4041200.00),(9,110,2,3474000.00,0.00),(10,51,2,0.00,8727863.00),(11,189,2,180000.00,0.00),(13,190,2,167524.00,0.00),(14,191,2,300000.00,0.00),(15,192,2,249714.00,0.00),(16,193,2,102381.00,0.00),(17,194,2,366667.00,0.00),(18,195,2,615238.00,0.00),(20,197,2,440952.00,0.00),(21,198,2,51150.00,0.00),(22,199,2,339762.00,0.00),(23,200,2,615381.00,0.00),(25,201,2,219762.00,0.00),(26,202,2,80429.00,0.00),(27,203,2,176095.00,0.00),(28,204,2,74333.00,0.00),(29,205,2,207619.00,0.00),(30,206,2,344762.00,0.00),(31,207,2,229048.00,0.00),(32,208,2,124762.00,0.00),(33,209,2,72143.00,0.00),(34,210,2,181905.00,0.00),(35,211,2,16426.00,0.00),(36,212,2,156952.00,0.00),(37,213,2,309524.00,0.00),(38,214,2,71429.00,0.00),(39,215,2,81524.00,0.00),(40,216,2,2952381.00,0.00),(42,52,2,0.00,40293278.00),(54,47,2,126000.00,19790950.00),(57,82,2,15000.00,5642800.00),(60,35,2,11038650.00,0.00),(63,23,2,3233840.00,2000.00),(74,25,2,1349032.00,0.00),(86,30,2,193900.00,0.00),(91,15,2,177900.00,0.00),(99,14,2,176650.00,0.00),(116,17,2,130000.00,0.00),(121,249,2,262250.00,0.00),(127,90,2,192000.00,0.00),(129,250,2,28250.00,0.00),(134,83,2,120000.00,0.00),(136,34,2,1100000.00,0.00),(145,26,2,200000.00,0.00),(154,251,2,204250.00,22500.00),(161,42,2,4569750.00,0.00),(169,24,2,1000.00,0.00),(173,67,2,195000.00,0.00),(183,66,2,50000.00,0.00),(184,46,2,15000.00,0.00),(198,18,2,56699.00,0.00),(206,65,2,374800.00,0.00),(214,21,2,55000.00,0.00),(219,31,2,108150.00,0.00),(230,19,2,405000.00,0.00),(234,20,2,27000.00,0.00),(253,16,2,269500.00,0.00),(259,22,2,25000.00,0.00),(267,41,2,170250.00,0.00),(283,38,2,70000.00,0.00),(295,48,2,0.00,80000.00),(306,36,2,487338.00,91703.00),(328,68,2,10100.00,0.00),(343,2,2,300000.00,0.00),(403,58,2,373000.00,0.00),(412,40,2,296000.00,0.00),(431,69,2,637500.00,0.00),(446,252,2,40000.00,0.00),(456,89,2,723000.00,0.00);
/*!40000 ALTER TABLE `account_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounting_periods`
--

DROP TABLE IF EXISTS `accounting_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) GENERATED ALWAYS AS (`start_date` <= curdate() and `end_date` >= curdate() and `is_closed` = 0) VIRTUAL,
  `created_by` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `closed_by` (`closed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_periods`
--

LOCK TABLES `accounting_periods` WRITE;
/*!40000 ALTER TABLE `accounting_periods` DISABLE KEYS */;
INSERT INTO `accounting_periods` VALUES (2,'FY2025','2025-01-01','2025-12-31',0,NULL,NULL,1,'1','2025-04-08 03:56:15'),(3,'FY 2024','2024-01-01','2024-12-31',0,NULL,NULL,0,'6','2025-04-30 16:16:59');
/*!40000 ALTER TABLE `accounting_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_workflows`
--

DROP TABLE IF EXISTS `approval_workflows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_workflows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `item_category_id` int(11) DEFAULT NULL,
  `amount_threshold` decimal(15,2) DEFAULT NULL,
  `approval_level` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `item_category_id` (`item_category_id`),
  KEY `approver_id` (`approver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_workflows`
--

LOCK TABLES `approval_workflows` WRITE;
/*!40000 ALTER TABLE `approval_workflows` DISABLE KEYS */;
/*!40000 ALTER TABLE `approval_workflows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  `sent_to` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_logs`
--

LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_settings`
--

DROP TABLE IF EXISTS `backup_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `frequency` enum('daily','weekly') DEFAULT 'daily',
  `time` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_settings`
--

LOCK TABLES `backup_settings` WRITE;
/*!40000 ALTER TABLE `backup_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_accounts`
--

DROP TABLE IF EXISTS `bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `opening_balance` decimal(15,2) NOT NULL,
  `current_balance` decimal(15,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_accounts`
--

LOCK TABLES `bank_accounts` WRITE;
/*!40000 ALTER TABLE `bank_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_statements`
--

DROP TABLE IF EXISTS `bank_statements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_statements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_account_id` int(11) NOT NULL,
  `statement_date` date NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `opening_balance` decimal(15,2) NOT NULL,
  `closing_balance` decimal(15,2) NOT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `imported_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_account_id` (`bank_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_statements`
--

LOCK TABLES `bank_statements` WRITE;
/*!40000 ALTER TABLE `bank_statements` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_statements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_transactions`
--

DROP TABLE IF EXISTS `bank_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `statement_id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance` decimal(15,2) NOT NULL,
  `status` enum('unreconciled','matched','reconciled') DEFAULT 'unreconciled',
  `system_transaction_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `statement_id` (`statement_id`),
  KEY `system_transaction_id` (`system_transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_transactions`
--

LOCK TABLES `bank_transactions` WRITE;
/*!40000 ALTER TABLE `bank_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrower`
--

DROP TABLE IF EXISTS `borrower`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `borrower` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `idnumber` varchar(200) NOT NULL,
  `province` varchar(200) DEFAULT NULL,
  `district` varchar(200) DEFAULT NULL,
  `sector` varchar(200) DEFAULT NULL,
  `cell` varchar(200) DEFAULT NULL,
  `village` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrower`
--

LOCK TABLES `borrower` WRITE;
/*!40000 ALTER TABLE `borrower` DISABLE KEYS */;
INSERT INTO `borrower` VALUES (1,'4200102R','Umuton','Pam','K','0785555555','procurement@gmail.com','2025-05-09 21:09:57','114444','sdf','df','ds','sd','sd'),(2,'4200202R','MUSABYIMANA ','Ange Rose','Kigali- Gasabo','0787295345','mus@gmail.com','2025-05-19 13:29:45','','','','','',''),(3,'4200302R','UWIMANA ','Clementine','Kigali Gasabo','078','uwic@gmail.com','2025-05-19 13:45:26','','','','','',''),(4,'4200402R','NIYONSABA','Valentine','Kigali- Gasabo','0785848395','niy@gmail.com','2025-05-19 13:51:45','','','','','',''),(5,'4200502R','MUKESHIMANA ','Angelique','Kigaki- Kicukiro','0781433107','mka@gmail.com','2025-05-19 13:55:33','','','','','',''),(6,'4200602R','MUJAWAMARIYA ','Innocente','Kigali- Kicukiro','078','mujainn@gmail.com','2025-05-19 13:57:54','','','','','',''),(7,'4200702R','BANKUNDIYE ','Donatha','Kigali- Kicukiro','07','bankudo@gmail.com','2025-05-19 14:04:18','','','','','',''),(8,'4200802R','MUKANDAYISABA ','Drocella','Kigali- Kicukiro','0785703760','mka@gmail.com','2025-05-19 14:05:29','','','','','',''),(9,'4200902R','NSEKANABO','Jean','Kigali- Gasabo','0788835740','mus@gmail.com','2025-05-19 14:06:57','','','','','',''),(10,'4201002R','MUKAMPANGAZA ','Drocella','North- Rulindo','0787782613','mka@gmail.com','2025-05-19 14:27:09','','','','','',''),(11,'4201102R','ZIRIMWABAGABO','Bonaventure','Kigali- Kicukiro','0722387857','mka@gmail.com','2025-05-19 14:28:27','','','','','',''),(12,'4201202R','KAMASHAZI ','Winny','Kigali- Gasabo','0788','mka@gmail.com','2025-05-19 14:31:46','','','','','',''),(13,'4201302R','UZAMUKUNDA ','Rose','Kigali- Kicukiro- Kabeza','0788634458','mka@gmail.com','2025-05-19 14:34:46','','','','','',''),(14,'4201402R','KANKUYO ','Annonciata','Kigaki- Gasabo','0787910953','mka@gmail.com','2025-05-19 14:51:04','','','','','',''),(15,'4201502R','MBABAZI ','Beatrice','Kigali- Kicukiro- Busanza','0788887443','mus@gmail.com','2025-05-19 14:52:22','','','','','',''),(16,'4201602R','MUKAGAKWAYA ','Peace','Kigali -Kicukiro- Kagarama','0784847366','mka@gmail.com','2025-05-19 14:57:32','','','','','',''),(17,'4201702R','MUKARUGAMBWA ','Veronique','Kigali-  Gasabo','0788','mka@gmail.com','2025-05-19 15:00:38','','','','','',''),(18,'4201802R','MUKARUGINA ','Renia','Kigali- Gasabo','0788','mka@gmail.com','2025-05-19 15:01:35','','','','','',''),(19,'4201902R','NYIRANEZA ','Esperance','Kigali- Gasabo','0788','mka@gmail.com','2025-05-19 15:02:52','','','','','',''),(20,'4202002R','MUKAMANA ','Odette','East - BUGESERA','0723966597','mka@gmail.com','2025-05-19 15:04:39','','','','','',''),(21,'4202102R','NYIRASHYIRAMBERE ','Faina','Kigali- Gasabo','0788','mka@gmail.com','2025-05-19 15:05:19','','','','','',''),(22,'4202202R','NIYIRORA ','Donathile','Kigali- Gasabo','0788','mka@gmail.com','2025-05-19 15:09:25','','','','','',''),(23,'4202302R','NIRAGIRE','Joselyne','Kigali- Gasabo','0782175899','mka@gmail.com','2025-05-19 15:10:24','','','','','',''),(24,'4202402R','NYIRARUKUNDO','Jeannette','Kigali- Gasabo','0788','mus@gmail.com','2025-05-19 15:12:09','','','','','',''),(25,'4202502R','MUKANKUSI','Liliane','Kigali- kicukiro','0786917150','mka@gmail.com','2025-05-19 15:14:18','','','','','',''),(26,'4202602R','UMULISA ','Jeannette','Kigali- gasabo','0788','mka@gmail.com','2025-05-19 15:16:10','','','','','',''),(27,'4202702R','MUKAYIRANGA','Alphonsine','Kigali- Gasabo','0788','mka@gmail.com','2025-05-19 15:16:54','','','','','',''),(28,'4202802R','UWAMAHORO','Janviere','Kigali- Gasabo','0788','mka@gmail.com','2025-05-19 15:17:27','','','','','',''),(29,'4202902R','GAKWANDI MUNEZERO','Eric','Kigali- Kicukiro','0783119910','mus@gmail.com','2025-05-19 15:18:31','','','','','','');
/*!40000 ALTER TABLE `borrower` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrowers`
--

DROP TABLE IF EXISTS `borrowers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `borrowers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `credit_score` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrowers`
--

LOCK TABLES `borrowers` WRITE;
/*!40000 ALTER TABLE `borrowers` DISABLE KEYS */;
/*!40000 ALTER TABLE `borrowers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart_of_accounts`
--

DROP TABLE IF EXISTS `chart_of_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('asset','liability','equity','revenue','expense','stock','finances','receivables','payables') NOT NULL,
  `parent_account` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_code` (`account_code`)
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_of_accounts`
--

LOCK TABLES `chart_of_accounts` WRITE;
/*!40000 ALTER TABLE `chart_of_accounts` DISABLE KEYS */;
INSERT INTO `chart_of_accounts` VALUES (1,'100101P','Donation for Polyclinic','revenue',76,1),(2,'100201P','Consultation fees from Polyclinic','revenue',76,1),(3,'100301P',' Medical acts','revenue',76,1),(4,'100401P','Sales of drug','revenue',76,1),(5,'100501P','Laboratory examinations','revenue',76,1),(6,'100601P','Medical Imaging','revenue',76,1),(7,'100102R','Donation for RCEF','revenue',76,1),(8,'100103W','Gain on Exchange','revenue',76,1),(9,'100203w','Other Revenues from church','revenue',76,1),(10,'200102R','Net Salary  for Staff','expense',83,1),(11,'200202R','Pension Contribution','expense',83,1),(12,'200302R','Maternity Contributions','expense',83,1),(13,'200104R','Mutuel Contributions','expense',83,1),(14,'210102R','Office Supplies ','expense',85,1),(15,'210202R','Office consumable','expense',85,1),(16,'210302R','Rent of Office premises','expense',85,1),(17,'210402R','Electricity','expense',85,1),(18,'210502R','Water','expense',85,1),(19,'210602R','Security services','expense',85,1),(20,'210702R','Cleaning and waste collection','expense',85,1),(21,'210802R','Internet ','expense',85,1),(22,'210902R','communication  fees','expense',85,1),(23,'230102R','National Transport ','expense',86,1),(24,'230202R','Other Transport','expense',86,1),(25,'230302R','Fuel','expense',86,1),(26,'230402R','Rent of vehicle and other transport means','expense',86,1),(27,'230502R','Rent of Machinery','expense',86,1),(28,'230602R',' international transport','expense',86,1),(29,'230702R','Mission fees','expense',86,1),(30,'240102R','Meals ','expense',87,1),(31,'240202R',' Drinks','expense',87,1),(32,'240302R','Accomodation','expense',87,1),(33,'240402R','Other Cost Related to guest reception','expense',87,1),(34,'250102R','Managerial consultancies work','expense',88,1),(35,'250202R','Construction Works','expense',88,1),(36,'250302R','Audit Fees','expense',88,1),(37,'211002R','Bank charges and Financial cost','expense',85,1),(38,'250402R','Translation','expense',88,1),(39,'260102R','Christmas Gift to Children','expense',89,1),(40,'260202R','Gift to others in Kind','expense',89,1),(41,'260302R','Donations and other support','expense',89,1),(42,'270102R','School fees for supported children','expense',91,1),(43,'270202R','CBHI Payment to supported children','expense',91,1),(46,'270302R','Other school related support to children','expense',91,1),(47,'310102R',' Frw 0001432374137  Rwanda Children Educational Foundation','finances',81,1),(48,'310202R','USD 0001432370883 Rwanda Children Educational Foundation','finances',81,1),(49,'310302R','Access Frw  7002190104271501 RCEF','finances',81,1),(50,'310402R','Access Frw 7002460204271503 RCEF Saving Account','finances',81,1),(51,'310502R','SACCO KIMIHURURA Frw 4755 Rwanda  Children Educational Foundation','finances',81,1),(52,'310602R',' Petty Cash for RCEF','finances',82,1),(53,'320102R','Stock in office in Kigali','stock',79,1),(54,'320101P','Stock  for the polyclinic','stock',79,1),(55,'510102R','Purchase of Office Chair','asset',93,1),(56,'510202R','Purchase of Office Tables','asset',93,1),(57,'510302R','Purchase of Computer Lap top and accessories','asset',93,1),(58,'510402R','Purchase of Computer Desktop and accessories','asset',93,1),(59,'510502R','Purchase of Printer','asset',93,1),(60,'510602R','Purchase of Other ICT equipement','asset',93,1),(62,'42001R2','MUSABYIMANA  Ange Rose','receivables',78,1),(63,'280102R','Maintenance of Building','expense',92,1),(64,'4200102R','UWIMANA  Clementine','receivables',78,1),(65,'280202R','Maintenance of Vehicle and spare parts','expense',92,1),(66,'280302R','MAintenance of ICT equipments and Spare parts','expense',92,1),(67,'280402R','Maintenance of Office equipments and spare parts','expense',92,1),(68,'280502R','Other Maintenance and spare parts','expense',92,1),(69,'510702R','Purchase of Other equipment','asset',93,1),(70,'6101','Adjustment on cash balances','finances',80,1),(71,'6102','Adjustment on Receivable balances','receivables',80,1),(72,'6103','Adjustment on payable balances','payables',80,1),(73,'6104','Adjustment on Asset balances','asset',0,1),(74,'6201','Regularization of revenue','revenue',76,1),(75,'6202','Regularization of expense','expense',0,1),(76,'100','REVENUE','revenue',0,1),(78,'42','Account Receivable','receivables',0,1),(79,'32','Stock','stock',0,1),(80,'61','Adjustment on opening Balances','expense',0,1),(81,'310','Bank account','finances',0,1),(82,'311','Petty Cash','finances',0,1),(83,'20','Salaries','expense',0,1),(85,'21','Office expenses','expense',0,1),(86,'23','Transport and Travel','expense',0,1),(87,'24','Meeting and Reception','expense',0,1),(88,'25','Contractual and professional works','expense',0,1),(89,'26','Gift and Donation in Kind','expense',0,1),(90,'260402R','Support to Vulnerable families','expense',89,1),(91,'27','School fees and related support','expense',0,1),(92,'28','Maintenance and spare parts','expense',0,1),(93,'51','Purchase of asset','asset',0,1),(94,'211202R','Exchange Loss','expense',85,1),(101,'41','Supplier control Account','liability',0,1),(102,'41100','Staff salaries','liability',101,1),(103,'200502R','PAYE Deducted','expense',83,1),(105,'600','Opening Balance','finances',0,1),(106,'280206R','construction Material','expense',92,1),(172,'41001','NGB INVESTORS LTD','liability',1,1),(173,'41002','UMUBYEYI ANGE DIDIER','liability',1,1),(174,'41003','SHARO HARDWARE COMPANY LTD','liability',1,1),(175,'41004','NONOX COMPANY','liability',1,1),(176,'41005','STEEPED  HARDWARE GROUP LTD','liability',1,1),(177,'41006','QUINCAILLERIE IKAZE IWACU','liability',1,1),(178,'41007','SUPERHARDWARE','liability',1,1),(179,'41008','NIECO LTD','liability',1,1),(180,'41009','ONLY JAM TRADING CO.LTD','liability',1,1),(181,'41010','GADY &JODY LTD(R)','liability',1,1),(182,'41011','HARD WOOD COMPANY LTD','liability',1,1),(183,'41012','BATO INVESTMENT GROUP LTD','liability',1,1),(184,'41013','TIMBERLINE COMPANY','liability',1,1),(185,'41014','AUTHENTIC GARAGE LTD','liability',1,1),(186,'41015','SHABA LTD','liability',1,1),(187,'41016','HAPPY CUSTOMER CARE HARDWARE LTD','liability',1,1),(188,'41017','INFINITE GLORY LTD','liability',1,1),(189,'4200202R','MUSABYIMANA  Ange Rose','receivables',78,1),(190,'4200302R','UWIMANA  Clementine','receivables',78,1),(191,'4200402R','NIYONSABA Valentine','receivables',78,1),(192,'4200502R','MUKESHIMANA  Angelique','receivables',78,1),(193,'4200602R','MUJAWAMARIYA  Innocente','receivables',78,1),(194,'4200702R','BANKUNDIYE  Donatha','receivables',78,1),(195,'4200802R','MUKANDAYISABA  Drocella','receivables',78,1),(196,'4200902R','NSEKANABO Jean','receivables',1,1),(197,'4201002R','MUKAMPANGAZA  Drocella','receivables',1,1),(198,'4201102R','ZIRIMWABAGABO Bonaventure','receivables',1,1),(199,'4201202R','KAMASHAZI  Winny','receivables',1,1),(200,'4201302R','UZAMUKUNDA  Rose','receivables',1,1),(201,'4201402R','KANKUYO  Annonciata','receivables',1,1),(202,'4201502R','MBABAZI  Beatrice','receivables',1,1),(203,'4201602R','MUKAGAKWAYA  Peace','receivables',1,1),(204,'4201702R','MUKARUGAMBWA  Veronique','receivables',1,1),(205,'4201802R','MUKARUGINA  Renia','receivables',1,1),(206,'4201902R','NYIRANEZA  Esperance','receivables',1,1),(207,'4202002R','MUKAMANA  Odette','receivables',1,1),(208,'4202102R','NYIRASHYIRAMBERE  Faina','receivables',1,1),(209,'4202202R','NIYIRORA  Donathile','receivables',1,1),(210,'4202302R','NIRAGIRE Joselyne','receivables',1,1),(211,'4202402R','NYIRARUKUNDO Jeannette','receivables',1,1),(212,'4202502R','MUKANKUSI Liliane','receivables',1,1),(213,'4202602R','UMULISA  Jeannette','receivables',1,1),(214,'4202702R','MUKAYIRANGA Alphonsine','receivables',1,1),(215,'4202802R','UWAMAHORO Janviere','receivables',1,1),(216,'4202902R','GAKWANDI MUNEZERO Eric','receivables',1,1),(217,'41018','SPERO LTD','liability',1,1),(218,'41019','LA GLOIRE CONFIANCE TRADING LTD','liability',1,1),(219,'41020','UMWAMI SUPPLY AND DESIGN COMPANY ','liability',1,1),(220,'41021','THE EMPIRE CO LTD','liability',1,1),(221,'41022','KN UMUCYO BUMBA LTD','liability',1,1),(222,'41023','SAT GENERATOR HARDWARE LTD','liability',1,1),(223,'41024','KIN GENERAL LTD','liability',1,1),(224,'41025','NICKYS FAIR HARDWARE LTD','liability',1,1),(225,'41026','PANATECH','liability',1,1),(226,'41027','UMUHIRE JEANNETTE','liability',1,1),(227,'41028','REG','liability',1,1),(228,'41029','JUVENAL HANYURWIMFURA','liability',1,1),(229,'41030','EZECHIEL NSEKANABO','liability',1,1),(230,'41031','GAKWANDI MUNEZERO ERIC','liability',1,1),(231,'41032','SECURITY GUARDS','liability',1,1),(232,'41033','ANDRE MUNYEMANA','liability',1,1),(233,'41034','JEAN BOSCO KIMENYI','liability',1,1),(234,'41035','RUHINJA FRANCOIS ','liability',1,1),(235,'41036','RUHINJA FRANCOIS ','liability',1,1),(236,'41037','HARERIMANA GREGOIRE','liability',1,1),(237,'41038','MASENGESHO FLAVIEN','liability',1,1),(238,'41039','DONATH MAHORO','liability',1,1),(239,'41040','NIYONKURU DESIRE','liability',1,1),(240,'41041','NIYONKURU DESIRE','liability',1,1),(241,'41042','EKS LTD','liability',1,1),(242,'41043','ONE FAMILY CONSTRUCTION LTD','liability',1,1),(243,'41044','KAREMERA APPOLINAIRE','liability',1,1),(244,'41045','KAYIRANGA EMMANUEL','liability',1,1),(245,'41046','EDMOND NKURIKIYIMANA','liability',1,1),(246,'41047','TWILINGIYIMANA JEAN BOSCO','liability',1,1),(247,'41048','RUBUMBA ANTOINE','liability',1,1),(248,'41049','WASAC','liability',1,1),(249,'250502R','Loading and Offloading ','expense',88,1),(250,'250602R','Legal fees','expense',88,1),(251,'280602R','Rent of construction Material','expense',92,1),(252,'211302R','Rent  of office  and meeting equipment','expense',85,1);
/*!40000 ALTER TABLE `chart_of_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `manager_id` (`manager_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Super Admin','Admin',NULL,NULL,1,'2025-04-08 13:55:04','2025-04-09 07:31:43'),(2,'IT Depertment','IT',NULL,NULL,1,'2025-04-09 07:28:28','2025-04-09 07:28:28');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donation_projects`
--

DROP TABLE IF EXISTS `donation_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donation_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `target_amount` decimal(15,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donation_projects`
--

LOCK TABLES `donation_projects` WRITE;
/*!40000 ALTER TABLE `donation_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `donation_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donation_receipt_templates`
--

DROP TABLE IF EXISTS `donation_receipt_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donation_receipt_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `template_html` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donation_receipt_templates`
--

LOCK TABLES `donation_receipt_templates` WRITE;
/*!40000 ALTER TABLE `donation_receipt_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `donation_receipt_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donations`
--

DROP TABLE IF EXISTS `donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donor_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'RWF',
  `donation_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `is_acknowledged` tinyint(1) DEFAULT 0,
  `receipt_number` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `recieptdoc` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `donor_id` (`donor_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donations`
--

LOCK TABLES `donations` WRITE;
/*!40000 ALTER TABLE `donations` DISABLE KEYS */;
/*!40000 ALTER TABLE `donations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donor_categories`
--

DROP TABLE IF EXISTS `donor_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donor_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donor_categories`
--

LOCK TABLES `donor_categories` WRITE;
/*!40000 ALTER TABLE `donor_categories` DISABLE KEYS */;
INSERT INTO `donor_categories` VALUES (1,'School fees',NULL),(2,'Projects',NULL);
/*!40000 ALTER TABLE `donor_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donors`
--

DROP TABLE IF EXISTS `donors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('individual','organization') NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donors`
--

LOCK TABLES `donors` WRITE;
/*!40000 ALTER TABLE `donors` DISABLE KEYS */;
/*!40000 ALTER TABLE `donors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_permissions`
--

DROP TABLE IF EXISTS `employee_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=933 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_permissions`
--

LOCK TABLES `employee_permissions` WRITE;
/*!40000 ALTER TABLE `employee_permissions` DISABLE KEYS */;
INSERT INTO `employee_permissions` VALUES (752,25,1),(753,25,2),(754,25,3),(755,25,42),(756,25,24),(757,25,25),(758,25,26),(759,25,27),(760,25,40),(761,25,41),(762,25,44),(763,25,20),(764,25,22),(765,25,23),(766,25,4),(767,25,5),(768,25,6),(769,25,7),(770,25,8),(771,25,9),(772,25,10),(773,25,11),(774,25,12),(775,25,13),(776,25,14),(777,25,15),(778,25,16),(779,25,17),(780,25,18),(781,25,19),(782,25,35),(783,25,36),(784,25,37),(785,25,38),(786,25,39),(787,25,28),(788,25,29),(789,25,30),(790,25,31),(791,25,33),(792,25,34),(793,25,47),(801,21,1),(802,21,2),(803,21,3),(804,21,42),(805,21,43),(806,21,24),(807,21,25),(808,21,26),(809,21,27),(810,21,40),(811,21,41),(812,21,44),(813,21,45),(814,21,46),(815,21,20),(816,21,22),(817,21,23),(818,21,4),(819,21,5),(820,21,6),(821,21,7),(822,21,8),(823,21,9),(824,21,10),(825,21,11),(826,21,12),(827,21,13),(828,21,14),(829,21,15),(830,21,16),(831,21,17),(832,21,18),(833,21,19),(834,21,35),(835,21,36),(836,21,37),(837,21,38),(838,21,39),(839,21,28),(840,21,29),(841,21,30),(842,21,31),(843,21,33),(844,21,34),(845,21,47),(846,23,1),(847,23,2),(848,23,43),(849,23,24),(850,23,25),(851,23,26),(852,23,27),(853,23,40),(854,23,41),(855,23,44),(856,23,45),(857,23,46),(858,23,4),(859,23,5),(860,23,6),(861,23,7),(862,23,8),(863,23,9),(864,23,12),(865,23,13),(866,23,14),(867,23,17),(868,23,18),(869,23,19),(870,23,35),(871,23,37),(872,23,38),(873,23,39),(874,23,28),(875,23,29),(876,23,30),(877,23,31),(878,23,33),(879,23,34),(880,23,47),(881,22,1),(882,22,2),(883,22,3),(884,22,4),(885,22,5),(886,22,6),(887,22,7),(888,22,8),(889,22,9),(890,22,12),(891,22,13),(892,22,14),(893,22,17),(894,22,18),(895,22,19),(896,22,35),(897,22,37),(898,22,38),(899,22,39),(900,22,28),(901,22,29),(902,22,30),(903,22,31),(904,22,33),(905,22,34),(906,22,47),(907,29,1),(908,29,2),(909,29,40),(910,29,41),(911,29,44),(912,29,45),(913,29,46),(914,29,4),(915,29,5),(916,29,6),(917,29,7),(918,29,8),(919,29,9),(920,29,12),(921,29,13),(922,29,14),(923,29,17),(924,29,18),(925,29,19),(926,29,28),(927,29,29),(928,29,30),(929,29,31),(930,29,33),(931,29,34),(932,29,47);
/*!40000 ALTER TABLE `employee_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_roles`
--

DROP TABLE IF EXISTS `employee_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=568 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_roles`
--

LOCK TABLES `employee_roles` WRITE;
/*!40000 ALTER TABLE `employee_roles` DISABLE KEYS */;
INSERT INTO `employee_roles` VALUES (1,12,3),(2,12,1),(3,12,8),(4,12,7),(5,12,2),(6,12,4),(7,12,5),(8,12,6),(27,24,3),(28,24,1),(29,24,8),(30,24,7),(31,24,2),(32,24,4),(33,24,5),(34,24,6),(122,7,3),(123,7,1),(124,7,8),(125,7,7),(126,7,2),(127,7,4),(128,7,5),(129,7,6),(130,20,3),(172,28,1),(173,31,8),(533,25,3),(534,25,1),(535,25,8),(536,25,7),(537,25,2),(538,25,4),(539,25,5),(540,25,6),(542,21,3),(543,21,1),(544,21,8),(545,21,7),(546,21,2),(547,21,4),(548,21,5),(549,21,6),(550,23,3),(551,23,8),(552,23,7),(553,23,4),(554,23,5),(555,23,6),(556,22,3),(557,22,1),(558,22,8),(559,22,7),(560,22,2),(561,22,4),(562,22,5),(563,22,6),(564,29,3),(565,29,7),(566,29,4),(567,29,6);
/*!40000 ALTER TABLE `employee_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (21,'NKURIKIYIMANA Edmond','nkuredi@yahoo.fr','0788359272','2025-04-14',728541.00,1,'2025-04-14 19:40:46'),(22,'UWIMANA Jeanne','jeanneuwimana06@gmail.com','0786584968','2025-04-14',383046.00,1,'2025-04-14 19:42:06'),(23,'GAHIZI Gloria','glorigah12@gmail.com','078387832','2025-04-25',320251.00,1,'2025-04-14 19:43:17'),(24,'RCEF','rcefrw2013@gmail.com','0787893208','2025-04-14',0.00,1,'2025-04-14 19:44:54'),(25,'Admin Testing account','panatech@gmail.com','0786074570','2025-04-14',500000.00,1,'2025-04-14 22:06:16'),(28,'pascal Twizerimana','pascal@panatechrwanda.com','78607470','2025-05-06',50000.00,1,'2025-05-06 19:44:39'),(29,'MUNEZERO Asita','asitamunezero1994@gmail.com','0787906816','2025-05-06',320251.00,1,'2025-05-06 19:47:58');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_payments`
--

DROP TABLE IF EXISTS `fee_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `term_id` (`term_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_payments`
--

LOCK TABLES `fee_payments` WRITE;
/*!40000 ALTER TABLE `fee_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `fee_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixed_assets`
--

DROP TABLE IF EXISTS `fixed_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixed_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `cost` decimal(15,2) DEFAULT NULL,
  `useful_life` int(11) DEFAULT NULL,
  `depreciation_method` enum('straight_line','reducing_balance') DEFAULT 'straight_line',
  `salvage_value` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixed_assets`
--

LOCK TABLES `fixed_assets` WRITE;
/*!40000 ALTER TABLE `fixed_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `fixed_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internal_requisition_items`
--

DROP TABLE IF EXISTS `internal_requisition_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internal_requisition_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_of_measure` varchar(20) NOT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('pending','approved','partially_fulfilled','fulfilled','rejected') NOT NULL DEFAULT 'pending',
  `fulfilled_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `requisition_id` (`requisition_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internal_requisition_items`
--

LOCK TABLES `internal_requisition_items` WRITE;
/*!40000 ALTER TABLE `internal_requisition_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `internal_requisition_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internal_requisitions`
--

DROP TABLE IF EXISTS `internal_requisitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internal_requisitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_number` varchar(20) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `needed_by_date` date NOT NULL,
  `purpose` text NOT NULL,
  `status` enum('draft','submitted','approved','partially_fulfilled','fulfilled','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `requester_id` (`requester_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internal_requisitions`
--

LOCK TABLES `internal_requisitions` WRITE;
/*!40000 ALTER TABLE `internal_requisitions` DISABLE KEYS */;
/*!40000 ALTER TABLE `internal_requisitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_adjustments`
--

DROP TABLE IF EXISTS `inventory_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_adjustments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adjustment_date` datetime NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_adjustments`
--

LOCK TABLES `inventory_adjustments` WRITE;
/*!40000 ALTER TABLE `inventory_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_categories`
--

DROP TABLE IF EXISTS `inventory_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_categories`
--

LOCK TABLES `inventory_categories` WRITE;
/*!40000 ALTER TABLE `inventory_categories` DISABLE KEYS */;
INSERT INTO `inventory_categories` VALUES (1,'Electronics',NULL),(2,'Office Supplies',NULL);
/*!40000 ALTER TABLE `inventory_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `unit_of_measure` varchar(20) DEFAULT NULL,
  `current_quantity` decimal(10,2) DEFAULT 0.00,
  `reorder_level` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `category_id` (`category_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_locations`
--

DROP TABLE IF EXISTS `inventory_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `location_type` enum('warehouse','shelf','bin','room','other') NOT NULL DEFAULT 'warehouse',
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_locations`
--

LOCK TABLES `inventory_locations` WRITE;
/*!40000 ALTER TABLE `inventory_locations` DISABLE KEYS */;
INSERT INTO `inventory_locations` VALUES (1,'Stock','1',NULL,NULL,'warehouse',NULL,1,'2025-04-08 09:46:38',NULL);
/*!40000 ALTER TABLE `inventory_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `inventory_movement_report`
--

DROP TABLE IF EXISTS `inventory_movement_report`;
/*!50001 DROP VIEW IF EXISTS `inventory_movement_report`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `inventory_movement_report` AS SELECT
 1 AS `id`,
  1 AS `date_time`,
  1 AS `item_id`,
  1 AS `item_code`,
  1 AS `name`,
  1 AS `movement_type`,
  1 AS `movement_type_name`,
  1 AS `quantity`,
  1 AS `unit_of_measure`,
  1 AS `cost_price`,
  1 AS `selling_price`,
  1 AS `user_name`,
  1 AS `reference_id`,
  1 AS `reference_type`,
  1 AS `notes`,
  1 AS `category_name`,
  1 AS `supplier_name`,
  1 AS `reference_display` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `inventory_movements`
--

DROP TABLE IF EXISTS `inventory_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `movement_type` enum('purchase','sale','adjustment','transfer_in','transfer_out','return') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID of related transaction (purchase, sale, adjustment, etc)',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'Type of related transaction',
  `date_time` datetime NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL COMMENT 'If tracking multiple locations',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_inventory_movements_item` (`item_id`),
  KEY `idx_inventory_movements_date` (`date_time`),
  KEY `idx_inventory_movements_reference` (`reference_id`,`reference_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_movements`
--

LOCK TABLES `inventory_movements` WRITE;
/*!40000 ALTER TABLE `inventory_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_documents`
--

DROP TABLE IF EXISTS `loan_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_documents`
--

LOCK TABLES `loan_documents` WRITE;
/*!40000 ALTER TABLE `loan_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_repayments`
--

DROP TABLE IF EXISTS `loan_repayments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan_repayments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `amount_due` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `is_paid` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_repayments`
--

LOCK TABLES `loan_repayments` WRITE;
/*!40000 ALTER TABLE `loan_repayments` DISABLE KEYS */;
INSERT INTO `loan_repayments` VALUES (1,1,'2025-06-21',36451.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(2,1,'2025-07-21',36451.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(3,1,'2025-08-21',36451.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(4,1,'2025-09-21',36451.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(5,1,'2025-10-21',36451.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(6,2,'2025-06-21',28329.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(7,2,'2025-07-21',28329.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(8,2,'2025-08-21',28329.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(9,2,'2025-09-21',28329.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(10,2,'2025-10-21',28329.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(11,2,'2025-11-21',28329.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(12,3,'2025-06-21',50731.69,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(13,3,'2025-07-21',50731.69,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(14,3,'2025-08-21',50731.69,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(15,3,'2025-09-21',50731.69,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(16,3,'2025-10-21',50731.69,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(17,3,'2025-11-21',50731.69,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(18,4,'2025-06-21',38000.41,25000.00,'2025-05-21',NULL,'cash','RCEF-20250521011021','partial',0),(19,4,'2025-07-21',38000.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(20,4,'2025-08-21',38000.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(21,4,'2025-09-21',38000.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(22,4,'2025-10-21',38000.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(23,4,'2025-11-21',38000.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(24,5,'2025-06-21',17313.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(25,5,'2025-07-21',17313.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(26,5,'2025-08-21',17313.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(27,5,'2025-09-21',17313.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(28,5,'2025-10-21',17313.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(29,5,'2025-11-21',17313.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(30,6,'2025-06-21',62005.46,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(31,6,'2025-07-21',62005.46,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(32,6,'2025-08-21',62005.46,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(33,6,'2025-09-21',62005.46,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(34,6,'2025-10-21',62005.46,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(35,6,'2025-11-21',62005.46,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(36,7,'2025-06-21',49926.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(37,7,'2025-07-21',49926.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(38,7,'2025-08-21',49926.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(39,7,'2025-09-21',49926.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(40,7,'2025-10-21',49926.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(41,7,'2025-11-21',49926.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(42,8,'2025-06-21',54113.81,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(43,8,'2025-07-21',54113.81,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(44,8,'2025-08-21',54113.81,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(45,8,'2025-09-21',54113.81,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(46,8,'2025-10-21',54113.81,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(47,8,'2025-11-21',54113.81,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(48,9,'2025-06-21',74567.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(49,9,'2025-07-21',74567.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(50,9,'2025-08-21',74567.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(51,9,'2025-09-21',74567.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(52,9,'2025-10-21',74567.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(53,9,'2025-11-21',74567.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(54,10,'2025-06-21',25734.95,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(55,10,'2025-07-21',25734.95,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(56,11,'2025-06-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(57,11,'2025-07-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(58,11,'2025-08-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(59,11,'2025-09-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(60,11,'2025-10-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(61,11,'2025-11-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(62,12,'2025-06-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(63,12,'2025-07-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(64,12,'2025-08-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(65,12,'2025-09-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(66,12,'2025-10-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(67,12,'2025-11-21',57455.67,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(68,13,'2025-06-21',33080.28,80000.00,'2025-05-29',NULL,'cash','RCEF-20250529014222','paid',1),(69,13,'2025-07-21',33080.28,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(70,13,'2025-08-21',33080.28,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(71,13,'2025-09-21',33080.28,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(72,13,'2025-10-21',33080.28,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(73,13,'2025-11-21',33080.28,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(74,14,'2025-06-21',37162.99,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(75,14,'2025-07-21',37162.99,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(76,14,'2025-08-21',37162.99,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(77,14,'2025-09-21',37162.99,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(78,14,'2025-10-21',37162.99,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(79,14,'2025-11-21',37162.99,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(80,15,'2025-06-21',13601.00,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(81,15,'2025-07-21',13601.00,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(82,15,'2025-08-21',13601.00,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(83,15,'2025-09-21',13601.00,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(84,15,'2025-10-21',13601.00,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(85,15,'2025-11-21',13601.00,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(86,16,'2025-06-21',29778.66,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(87,16,'2025-07-21',29778.66,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(88,16,'2025-08-21',29778.66,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(89,16,'2025-09-21',29778.66,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(90,16,'2025-10-21',29778.66,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(91,16,'2025-11-21',29778.66,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(92,17,'2025-06-21',24984.43,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(93,17,'2025-07-21',24984.43,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(94,17,'2025-08-21',24984.43,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(95,18,'2025-06-21',35109.54,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(96,18,'2025-07-21',35109.54,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(97,18,'2025-08-21',35109.54,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(98,18,'2025-09-21',35109.54,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(99,18,'2025-10-21',35109.54,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(100,18,'2025-11-21',35109.54,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(101,19,'2025-06-21',58301.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(102,19,'2025-07-21',58301.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(103,19,'2025-08-21',58301.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(104,19,'2025-09-21',58301.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(105,19,'2025-10-21',58301.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(106,19,'2025-11-21',58301.20,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(107,20,'2025-06-21',38733.31,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(108,20,'2025-07-21',38733.31,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(109,20,'2025-08-21',38733.31,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(110,20,'2025-09-21',38733.31,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(111,20,'2025-10-21',38733.31,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(112,20,'2025-11-21',38733.31,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(113,21,'2025-06-21',21097.96,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(114,21,'2025-07-21',21097.96,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(115,21,'2025-08-21',21097.96,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(116,21,'2025-09-21',21097.96,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(117,21,'2025-10-21',21097.96,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(118,21,'2025-11-21',21097.96,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(119,22,'2025-06-21',12199.79,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(120,22,'2025-07-21',12199.79,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(121,22,'2025-08-21',12199.79,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(122,22,'2025-09-21',12199.79,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(123,22,'2025-10-21',12199.79,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(124,22,'2025-11-21',12199.79,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(125,23,'2025-06-21',30761.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(126,23,'2025-07-21',30761.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(127,23,'2025-08-21',30761.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(128,23,'2025-09-21',30761.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(129,23,'2025-10-21',30761.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(130,23,'2025-11-21',30761.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(131,24,'2025-06-21',16494.44,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(132,25,'2025-06-21',26541.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(133,25,'2025-07-21',26541.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(134,25,'2025-08-21',26541.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(135,25,'2025-09-21',26541.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(136,25,'2025-10-21',26541.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(137,25,'2025-11-21',26541.47,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(138,26,'2025-06-21',52342.26,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(139,26,'2025-07-21',52342.26,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(140,26,'2025-08-21',52342.26,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(141,26,'2025-09-21',52342.26,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(142,26,'2025-10-21',52342.26,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(143,26,'2025-11-21',52342.26,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(144,27,'2025-06-21',12079.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(145,27,'2025-07-21',12079.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(146,27,'2025-08-21',12079.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(147,27,'2025-09-21',12079.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(148,27,'2025-10-21',12079.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(149,27,'2025-11-21',12079.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(150,28,'2025-06-21',13786.17,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(151,28,'2025-07-21',13786.17,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(152,28,'2025-08-21',13786.17,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(153,28,'2025-09-21',13786.17,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(154,28,'2025-10-21',13786.17,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(155,28,'2025-11-21',13786.17,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(156,29,'2025-06-21',499264.29,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(157,29,'2025-07-21',499264.29,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(158,29,'2025-08-21',499264.29,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(159,29,'2025-09-21',499264.29,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(160,29,'2025-10-21',499264.29,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(161,29,'2025-11-21',499264.29,0.00,NULL,NULL,NULL,NULL,'unpaid',0);
/*!40000 ALTER TABLE `loan_repayments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loans`
--

DROP TABLE IF EXISTS `loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrower_id` varchar(200) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `term_months` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `purpose` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` VALUES (1,'2',180000.00,5.00,5,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 09:22:24'),(2,'3',167524.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:04:29'),(3,'4',300000.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:12:39'),(4,'5',224714.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:13:27'),(5,'6',102381.00,5.00,6,'2025-05-21','approved','Loan disbursement',6,'2025-05-21 10:14:05'),(6,'7',366667.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:16:21'),(7,'8',295238.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:24:06'),(8,'8',320000.00,5.00,6,'2025-05-21','approved','Loan disbursement',6,'2025-05-21 10:26:09'),(9,'10',440952.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:26:45'),(10,'11',51150.00,5.00,2,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:27:49'),(11,'12',339762.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:28:25'),(12,'13',339762.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:30:51'),(13,'13',195619.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:31:37'),(14,'14',219762.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:32:29'),(15,'15',80429.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:33:50'),(16,'16',176095.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:42:23'),(17,'17',74333.00,5.00,3,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:43:14'),(18,'18',207619.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:43:51'),(19,'19',344762.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:44:37'),(20,'20',229048.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:45:23'),(21,'21',124762.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:46:12'),(22,'22',72143.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:47:01'),(23,'23',181905.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:47:39'),(24,'24',16426.00,5.00,1,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:48:26'),(25,'25',156952.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:49:20'),(26,'26',309524.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:50:03'),(27,'27',71429.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:50:46'),(28,'28',81524.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:51:32'),(29,'29',2952381.00,5.00,6,'2025-05-21','approved','Loan Disbursement',6,'2025-05-21 10:52:13');
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_types`
--

DROP TABLE IF EXISTS `payment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `is_cash` tinyint(1) DEFAULT 0,
  `requires_authorization` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_types`
--

LOCK TABLES `payment_types` WRITE;
/*!40000 ALTER TABLE `payment_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll`
--

DROP TABLE IF EXISTS `payroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL,
  `gross_salary` decimal(10,2) DEFAULT NULL,
  `total_reduction` decimal(10,2) DEFAULT NULL,
  `net_salary` decimal(10,2) DEFAULT NULL,
  `pay_date` date DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll`
--

LOCK TABLES `payroll` WRITE;
/*!40000 ALTER TABLE `payroll` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_allowance_types`
--

DROP TABLE IF EXISTS `payroll_allowance_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll_allowance_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `is_taxable` tinyint(1) DEFAULT 1,
  `is_percentage` tinyint(1) DEFAULT 0,
  `default_amount` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_allowance_types`
--

LOCK TABLES `payroll_allowance_types` WRITE;
/*!40000 ALTER TABLE `payroll_allowance_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_allowance_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_deduction_types`
--

DROP TABLE IF EXISTS `payroll_deduction_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll_deduction_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `is_pretax` tinyint(1) DEFAULT 0,
  `is_percentage` tinyint(1) DEFAULT 0,
  `default_amount` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_deduction_types`
--

LOCK TABLES `payroll_deduction_types` WRITE;
/*!40000 ALTER TABLE `payroll_deduction_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_deduction_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_items`
--

DROP TABLE IF EXISTS `payroll_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `allowances` decimal(15,2) DEFAULT 0.00,
  `deductions` decimal(15,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `net_pay` decimal(15,2) NOT NULL,
  `status` enum('draft','approved','paid') DEFAULT 'draft',
  `payment_date` date DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_id` (`payroll_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_items`
--

LOCK TABLES `payroll_items` WRITE;
/*!40000 ALTER TABLE `payroll_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_periods`
--

DROP TABLE IF EXISTS `payroll_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','processing','completed') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_periods`
--

LOCK TABLES `payroll_periods` WRITE;
/*!40000 ALTER TABLE `payroll_periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payrolls`
--

DROP TABLE IF EXISTS `payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `gross_salary` decimal(12,2) NOT NULL,
  `transport` decimal(12,2) DEFAULT 0.00,
  `emp_pension` decimal(12,2) DEFAULT NULL,
  `emp_rama` decimal(12,2) DEFAULT NULL,
  `emp_maternity` decimal(12,2) DEFAULT NULL,
  `emp_cbhi` decimal(12,2) DEFAULT NULL,
  `total_deductions` decimal(12,2) DEFAULT NULL,
  `employer_pension` decimal(12,2) DEFAULT NULL,
  `employer_occupational` decimal(12,2) DEFAULT NULL,
  `employer_rama` decimal(12,2) DEFAULT NULL,
  `employer_maternity` decimal(12,2) DEFAULT NULL,
  `employer_cbhi` decimal(12,2) DEFAULT NULL,
  `total_employer_contribution` decimal(12,2) DEFAULT NULL,
  `net_salary` decimal(12,2) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrolls`
--

LOCK TABLES `payrolls` WRITE;
/*!40000 ALTER TABLE `payrolls` DISABLE KEYS */;
INSERT INTO `payrolls` VALUES (1,21,728415.00,0.00,43704.90,182524.50,2185.25,0.00,228414.65,58273.20,14568.30,0.00,2185.25,0.00,75026.75,500000.36,'2025-05','2025-05-06 14:43:09'),(2,23,320251.00,0.00,19215.06,60075.30,960.75,0.00,80251.11,25620.08,6405.02,0.00,960.75,0.00,32985.85,239999.89,'2025-05','2025-05-28 13:36:36'),(3,22,383046.00,0.00,22982.76,78913.80,1149.14,0.00,103045.70,30643.68,7660.92,0.00,1149.14,0.00,39453.74,280000.30,'2025-05','2025-05-28 13:38:01');
/*!40000 ALTER TABLE `payrolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `period_closing_summaries`
--

DROP TABLE IF EXISTS `period_closing_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `period_closing_summaries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period_id` int(11) NOT NULL,
  `total_debits` decimal(15,2) NOT NULL,
  `total_credits` decimal(15,2) NOT NULL,
  `retained_earnings` decimal(15,2) NOT NULL,
  `closing_entries_description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `period_id` (`period_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `period_closing_summaries`
--

LOCK TABLES `period_closing_summaries` WRITE;
/*!40000 ALTER TABLE `period_closing_summaries` DISABLE KEYS */;
/*!40000 ALTER TABLE `period_closing_summaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (37,'Add Borrower'),(41,'Add Donation'),(40,'Add Donor'),(20,'Add Employee'),(4,'Add Items'),(35,'Add Loan'),(30,'Add Sponsor'),(28,'Add Students'),(17,'Add Supplier'),(36,'Approve Loan'),(10,'Approve Purchase Order'),(15,'Approve Requisitions'),(42,'Close Account Period'),(33,'Create Academic Term'),(3,'Create Accounting Periods'),(1,'Create Journal'),(23,'Create Payroll'),(24,'Create Project'),(25,'Create Project Activities'),(7,'Create Purchase Order'),(13,'Create Requisitions'),(34,'Create School fees'),(46,'Delete Donation'),(21,'Delete Employee'),(6,'Delete Item'),(27,'Delete Project Activities'),(9,'Delete Purchase Order'),(32,'Delete Sponsor'),(19,'Delete Supplier'),(38,'Edit Borrower'),(45,'Edit Donation'),(44,'Edit Donor'),(22,'Edit Employee'),(5,'Edit Item'),(43,'Edit Journal'),(26,'Edit Project Activities'),(8,'Edit Purchase Order'),(14,'Edit Requisitions'),(31,'Edit Sponsor'),(29,'Edit Student'),(18,'Edit Supplier'),(47,'Edit term'),(12,'Receive Purchase order'),(39,'Record Loan Payment'),(11,'Reject Purchase Order'),(16,'Reject Requisitions'),(2,'View Journal');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_activities`
--

DROP TABLE IF EXISTS `project_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `budgeted_amount` decimal(15,2) NOT NULL,
  `actual_expense` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_activities`
--

LOCK TABLES `project_activities` WRITE;
/*!40000 ALTER TABLE `project_activities` DISABLE KEYS */;
INSERT INTO `project_activities` VALUES (1,1,'Act 1',500000.00,0.00);
/*!40000 ALTER TABLE `project_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `budgeted_amount` decimal(15,2) NOT NULL,
  `revised_budget` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'Construction of polyclinic','Desc',1000.00,1000.00,'2025-06-03 15:05:09');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `price` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_id` (`purchase_order_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected','received') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_note` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receipts`
--

DROP TABLE IF EXISTS `receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `printed_at` datetime DEFAULT NULL,
  `printed_by` int(11) DEFAULT NULL,
  `template` varchar(50) DEFAULT 'default',
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receipts`
--

LOCK TABLES `receipts` WRITE;
/*!40000 ALTER TABLE `receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconciliation_logs`
--

DROP TABLE IF EXISTS `reconciliation_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reconciliation_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_account_id` int(11) NOT NULL,
  `reconciled_by` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reconciled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `bank_account_id` (`bank_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reconciliation_logs`
--

LOCK TABLES `reconciliation_logs` WRITE;
/*!40000 ALTER TABLE `reconciliation_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconciliation_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reductions`
--

DROP TABLE IF EXISTS `reductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reductions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `pension` decimal(10,2) DEFAULT NULL,
  `contributions` decimal(10,2) DEFAULT NULL,
  `others` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reductions`
--

LOCK TABLES `reductions` WRITE;
/*!40000 ALTER TABLE `reductions` DISABLE KEYS */;
/*!40000 ALTER TABLE `reductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_schedules`
--

DROP TABLE IF EXISTS `report_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_schedules` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `frequency` enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
  `recipients` text NOT NULL,
  `last_sent` datetime DEFAULT NULL,
  `next_send` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_schedules`
--

LOCK TABLES `report_schedules` WRITE;
/*!40000 ALTER TABLE `report_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requisition_approvals`
--

DROP TABLE IF EXISTS `requisition_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requisition_approvals` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `approval_level` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `requisition_id` (`requisition_id`),
  KEY `approver_id` (`approver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requisition_approvals`
--

LOCK TABLES `requisition_approvals` WRITE;
/*!40000 ALTER TABLE `requisition_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `requisition_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requisition_fulfillments`
--

DROP TABLE IF EXISTS `requisition_fulfillments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requisition_fulfillments` (
  `id` int(11) NOT NULL,
  `requisition_item_id` int(11) NOT NULL,
  `fulfilled_by` int(11) NOT NULL,
  `fulfillment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `quantity` decimal(10,2) NOT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `requisition_item_id` (`requisition_item_id`),
  KEY `fulfilled_by` (`fulfilled_by`),
  KEY `from_location_id` (`from_location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requisition_fulfillments`
--

LOCK TABLES `requisition_fulfillments` WRITE;
/*!40000 ALTER TABLE `requisition_fulfillments` DISABLE KEYS */;
/*!40000 ALTER TABLE `requisition_fulfillments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requisition_items`
--

DROP TABLE IF EXISTS `requisition_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requisition_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `requisition_id` (`requisition_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `requisition_items_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`),
  CONSTRAINT `requisition_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requisition_items`
--

LOCK TABLES `requisition_items` WRITE;
/*!40000 ALTER TABLE `requisition_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `requisition_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requisitions`
--

DROP TABLE IF EXISTS `requisitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requisitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_date` date DEFAULT NULL,
  `requested_by` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requisitions`
--

LOCK TABLES `requisitions` WRITE;
/*!40000 ALTER TABLE `requisitions` DISABLE KEYS */;
INSERT INTO `requisitions` VALUES (1,NULL,NULL,'For office','approved','2025-05-09 20:06:20');
/*!40000 ALTER TABLE `requisitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (3,2,20),(5,2,22),(4,2,23),(61,3,1),(63,3,2),(60,3,3),(59,3,42),(62,3,43),(43,4,4),(52,4,5),(49,4,6),(47,4,7),(53,4,8),(50,4,9),(45,4,10),(57,4,11),(56,4,12),(48,4,13),(54,4,14),(46,4,15),(58,4,16),(44,4,17),(55,4,18),(51,4,19),(27,5,35),(28,5,36),(26,5,37),(29,5,38),(30,5,39),(73,6,28),(77,6,29),(72,6,30),(76,6,31),(74,6,33),(75,6,34),(78,6,47),(68,7,40),(67,7,41),(71,7,44),(70,7,45),(69,7,46),(39,8,24),(40,8,25),(42,8,26),(41,8,27);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (3,'accountant'),(1,'Admin'),(8,'budgeting'),(7,'donations'),(2,'hr'),(4,'inventory'),(5,'loan'),(6,'school');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `sale_date` datetime NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` int(11) DEFAULT NULL,
  `payment_status` enum('pending','partial','paid') DEFAULT 'paid',
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `payment_method` (`payment_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_reports`
--

DROP TABLE IF EXISTS `saved_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saved_reports` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`parameters`)),
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_reports`
--

LOCK TABLES `saved_reports` WRITE;
/*!40000 ALTER TABLE `saved_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sponsors`
--

DROP TABLE IF EXISTS `sponsors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sponsors`
--

LOCK TABLES `sponsors` WRITE;
/*!40000 ALTER TABLE `sponsors` DISABLE KEYS */;
/*!40000 ALTER TABLE `sponsors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_items`
--

DROP TABLE IF EXISTS `stock_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_items`
--

LOCK TABLES `stock_items` WRITE;
/*!40000 ALTER TABLE `stock_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `quantity_in` decimal(10,2) DEFAULT 0.00,
  `quantity_out` decimal(10,2) DEFAULT 0.00,
  `reference_type` enum('requisition','purchase_order','manual_adjustment') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_documents`
--

DROP TABLE IF EXISTS `student_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `document_name` varchar(255) DEFAULT NULL,
  `filepath` varchar(200) NOT NULL,
  `filetype` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_documents`
--

LOCK TABLES `student_documents` WRITE;
/*!40000 ALTER TABLE `student_documents` DISABLE KEYS */;
INSERT INTO `student_documents` VALUES (1,8,'Picture','682dd0fe112fb.JPG','image/jpeg','2025-05-21 13:11:26');
/*!40000 ALTER TABLE `student_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_payments`
--

DROP TABLE IF EXISTS `student_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `payment_date` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_payments`
--

LOCK TABLES `student_payments` WRITE;
/*!40000 ALTER TABLE `student_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_report`
--

DROP TABLE IF EXISTS `student_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `term` int(11) NOT NULL,
  `marks` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_report`
--

LOCK TABLES `student_report` WRITE;
/*!40000 ALTER TABLE `student_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_sponsor`
--

DROP TABLE IF EXISTS `student_sponsor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_sponsor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `sponsor_id` (`sponsor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_sponsor`
--

LOCK TABLES `student_sponsor` WRITE;
/*!40000 ALTER TABLE `student_sponsor` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_sponsor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `school_name` varchar(200) NOT NULL,
  `fees_payment` varchar(200) NOT NULL,
  `bank_name` varchar(200) NOT NULL,
  `bank_account` varchar(200) NOT NULL,
  `father_name` varchar(200) NOT NULL,
  `mother_name` varchar(200) DEFAULT NULL,
  `guardian_name` varchar(200) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `grade` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'Kelen','Agaheta','Female','2011-01-01',NULL,'0786917150','Karugira Kicukiro','2025-05-06 20:35:57','Karugira Primary School','975','Umwalimu SACCO','900010776300','Niyigena Jerome','Mukankusi  Liliane','',1,'6'),(2,'INT','LTD','Female','0111-11-11',NULL,'111111111111111','KKKKKK','2025-05-13 16:57:31','HJJKKK','12','11222222222222222','11111111111111','KKKKK','JJJJJJJ','HHHHHHHHH',1,''),(3,'Sylvie','Ahirwe','Female','2015-01-01',NULL,'0788649613','Kimironko Gasabo','2025-05-13 16:58:11','GS Remera Catholique','1000','Umwalimu SACCO','900009832500','Kabahiga Germain','Nyiraguhirwa  Claire','',1,'4'),(4,'Sabrina ','Ashimwe Angel','Female','2014-01-01',NULL,'0782127575','Kigarama-Kicukiro','2025-05-13 18:34:51','Kinunga Primary School/Ecole Maternelle Kinunga','975','BPR','402119350810181','Tuyisenge Salim','Mudahogora  ','',1,'4'),(5,'Ambiance','Bwenge ','Female','2011-01-01',NULL,'0','','2025-05-13 19:01:02','Groupe Scolaire Gatunga','975','Umwalimu SACCO','0000113616','Bosenibo Albert','Bampire Jeannette','',1,'6'),(6,'Gadi','Byiringiro','Male','2008-01-01',NULL,'0788456649','','2025-05-13 19:43:44','ES Munzanga','85,000','BPR','514298722310142','Twahirwa Steven ','Muhorakeye Betty','',1,'9'),(7,'Dickson','Byishimo ','Male','2008-01-07',NULL,'0787127794','Kagugu Gasabo','2025-05-13 20:15:21','EPR/Ecole Secondaire Mutunda','75000','BK','000500008814081','Kamanda Den','Mukarugina Renia','',1,'8'),(8,' Angel','Cyiza  ','Female','2008-10-24',NULL,'0788424097','Kanombe Kicukiro','2025-05-13 20:38:38','GS Ste Bernadette Save','85000','Umwalimu SACCO','0000097960','Gaburanyi Ibrahim','Bamukunde Anna','',1,'10'),(9,'Lucky Niyitegeka','Cyomoro','Male','2011-01-01',NULL,'0788661925','Nyamirambo -Nyarugenge , Second Phone Number : 0785477023','2025-05-15 16:31:44','Karugira Primary School','975','Umwalimu SACCO','900010776300','Nsengiyumva Aaron','Mujawayezu Assoumpta','',1,'6'),(10,'Graine',' Ineza Cyusa ','Male','2006-01-01',NULL,'0781864196','Gisozi -Gasabo','2025-05-15 16:45:28','ES kirinda','85,000','BPR','514298759510184','Abirabura Jean Marie Vianney','Mukabalisa Louise','',1,'11'),(11,'Emmanuel','Dufitumukiza','Male','2004-01-01',NULL,'0788978066','Kanombe -Kicukiro','2025-05-15 19:50:33','Ecole Secondaire De Kigoma','85000','BK','100000676823','Hategekimana Louis','Mukawera Angelique','',1,'12'),(12,'Feza','Acquiline','Female','2006-01-01',NULL,'0789613546','Kagarama -Kicukiro','2025-05-15 19:57:53','GS Apapec Murambi','75,000','BK','000400004357454','Gendahayo Francois','Mukashyaka Esperance','',1,'12'),(13,'Patrick','Gisa','Male','2016-03-26',NULL,'0780511417','Gikondo -Kicukiro','2025-05-15 20:20:49','Karugira Primary School','24,500','Umwalimu Sacco','900096097000','Zirimwabagabo Bonaventure','Mukamurenzi Gloriose','',1,'3'),(14,'Chris Berbatove','Ganza ','Male','2010-01-01',NULL,'0784149703','Kimisagara-Nyarugenge, Second Phone Number : /0722387887','2025-05-15 20:31:16','GS EPA ST Michel','24500','Umwalimu SACCO','900096097000','Zirimwabagabo Bonaventure','Mukamurenzi Gloriose','',1,'8'),(15,'Niyobuhungiro','Gisubizo','Male','2010-01-01',NULL,'0786397525','Kinyinya -Kagugu','2025-05-15 20:51:28','Ecole Secondaire Murama','85,000','BK','100000239792','None','Mukagashugi Souzane','',1,'7'),(16,'Ganza Kelly','Musoni','Male','2009-11-29',NULL,'0785526003','Kicukiro-Kanombe','2025-05-16 14:44:40','College de Musanze','85,000','BK','100000239792','Muvuzankaya James','Mukagashugi Suzane','',1,'11'),(17,'Elissa','Gisubizo','Male','2014-07-10',NULL,'0789970803','','2025-05-16 15:16:07','Kinunga Primary School/ Ecole Maternelle Kinunga','975','BPR','402119350810181','Uwimana Vianney','Vuguziga Hilarie','',1,'5'),(18,'Clever','Gitego','Male','2015-09-13',NULL,'0785504950','Gatenga-Kicukiro','2025-05-16 15:50:38','GS ST Vincent Pallotti Gikondo','975','BPR','4021184790-10169','Simpunga Jean Berclamas','Nikuze Mediatrice ','',1,'3'),(19,'Jean Elysee','Habimana','Male','2010-01-01',NULL,'0788557640','Kabeza-Kicukiro','2025-05-16 16:30:22','College Indashyikirwa','85,000','Umwalimu Sacco','90006219400','Habimana J Bosco','Musabyimana Gertulde','',1,'8'),(20,'Audrey','Hagenimana','Female','2016-01-01',NULL,'0784721924','Kimisagara-Nyarugenge','2025-05-16 16:40:02','Groupe Scolaire Kimisagara','975','Umwalimu Sacco','900096100000','Hagenimana Fabien','Bankundiye Pascasie','',1,'4'),(21,'Kevin','Habinshuti','Male','2007-04-05',NULL,'0788557340','Bugesera','2025-05-16 16:47:15','Ecole Secondaire Muhororo','85,000','BK','002650773863301','Nsengiyumva Innocent','Mukamana Beatrice','',1,'8'),(22,'Justin ','Hakizimana','Male','2014-01-08',NULL,'0787295345','Nyarugenge','2025-05-19 08:12:50','EP Muganza/Prime DES Parents ','975','BK','0004500388000','Rutaburingoga Saidi','Musabyimana Ange Rose','',1,'5'),(23,'Hora Leila','Kamanzi','Female','2014-01-01',NULL,'0786179214','Muyombo- Rwamagana','2025-05-19 08:26:45','Ecole Secondaire Saint Trinite De Ruhango','85,000','BPR','443214133110189','Ndabazigiye Kamanzi Darius','Uwingabire Leoncie','',1,'7'),(24,'Nadine','Ihirwe','Female','2006-01-01',NULL,'0783044523','Gasagara-Gasabo','2025-05-19 08:39:46','Lycee De Ruhengeri APICUR','104,500','BK','100000241584','Uwikirora J paul','Mukarukundo Seraphine','',1,'10'),(25,'Kellia','Imananikuzwe','Female','2007-05-12',NULL,'0784736235','Kabeza- Kicukiro','2025-05-19 08:52:46','G.S Bigugu','85,000','BK','85,000','Habumugisha  J de Dieu','Mukakalisa Redempta','',1,'9'),(26,'Prince David ','Ishimwe Muneza','Male','2008-11-07',NULL,'0787020308','Kigarama- Gikondo','2025-05-19 09:33:31','Apejerwa/Lycee Ikirezi Et Emeru','134,000','UNGUKA Bank','20200463090012','Nshimiyimana Alex','Ingabire Peace','',1,'10'),(27,'Bruce','Imena','Male','2015-01-01',NULL,'0788607843','','2025-05-19 10:37:24','Groupe Scolaire Kimironko II PTA','975','COPEDU PLC','1007020126727','Harindintwali ','Mukaruyundo Edegine','',1,'2'),(28,'Deborah','Ineza Ngabo','Female','2010-01-10',NULL,'0788776436','Kanombe-  Kicukiro','2025-05-19 11:01:15','Ecole Secondaire De Runaba (ST Charles Lwanga)','85000','BK','000520005631934','Ngaboyisonga Felecien','Nzabamwita Stephanie','',1,'8'),(29,'Joanna','Ineza','Female','2017-06-17',NULL,'0788835740','Remera','2025-05-19 11:33:55','Ecole Sainte Agnes','40,000','BPR','401203677410133','Nsekanabo John','Wibabara Jennifer','',1,'3'),(30,'Pascali','Ineza Kaliza','Female','2012-01-01',NULL,'0788821880','Kicukiro','2025-05-19 11:42:56','G.S Shyogwe','85,000','BK','000560000026963','Mugabukuze Pascal','Nyiraneza Esperance','',1,'7'),(31,'Peace','Ineza','Female','2014-01-01',NULL,'0782110102','Kimihurura- Gasabo','2025-05-19 11:53:08','GS Rudakabukirwa','975','Umwalimu Sacco','9000015565900','Ndakaza Richard','Mukantwali Jacqueline','',1,'5'),(32,'Claudine','Ingabire','Female','2009-01-01',NULL,'0780744117','Kigarama-Kicukiro','2025-05-19 12:41:24','Ecole Secondaire Muhororo','75000','Umwalimu Sacco ','900007285500','Uwiringiyimana Faustin','Mukeshimana Alice','',1,'8'),(33,'Violette','Ingabire','Female','2010-01-01',NULL,'0782166097','Bugesera - Second phone number : 0784993555','2025-05-20 09:27:37','ES Kigoma','85,000','BK','100000676823','Munyaneza Jean','Uwamahoro Clementine','',1,'7'),(34,'Cynthia','Irakoze','Female','2007-06-26',NULL,'0782175899','Kanombe-Kicukiro','2025-05-20 13:57:07','GS Apapec Murambi','975','BK','000400004357454','Rukundo JMV','Niragire Joselyne','',1,'9'),(35,'Kevine','Irasubiza','Female','2012-11-16',NULL,'0786149677','Gasagara- Rusororo','2025-05-21 07:58:43','Groupe Scolaire Ruhanga','85,000','Umwalimu Sacco','0000098314','Mporebucya Boniface','Mukabutera Jeanette','',1,'7'),(36,'Esther','Irema Usanase','Female','2009-01-01',NULL,'0788235675','Kanombe-Kicukiro','2025-05-21 12:58:17','Groupe Scolaire Kabare','85000','BK','000570007221413','Ndagijimana Eric','None','',1,'9');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `supplier_contact_person` varchar(200) DEFAULT NULL,
  `account_number` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_id` (`tax_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'NGB INVESTORS LTD','FF','','','','122661712',1,'','41001'),(2,'UMUBYEYI ANGE DIDIER','FF','','','','999999999',1,'','41002'),(3,'SHARO HARDWARE COMPANY LTD','FF','','','','111516963',1,'','41003'),(5,'NONOX COMPANY','FF','','','','107771454',1,'','41004'),(6,'STEEPED  HARDWARE GROUP LTD','FF','','','','119361729',1,'','41005'),(7,'QUINCAILLERIE IKAZE IWACU','FF','','','','111050221',1,'','41006'),(20,'SUPERHARDWARE','FF','','','','111737396',1,'','41007'),(21,'NIECO LTD','FF','','','','107654416',1,'','41008'),(25,'ONLY JAM TRADING CO.LTD','FF','','','','128671689',1,'','41009'),(27,'GADY &JODY LTD(R)','FF','','','','103138464',1,'','41010'),(28,'HARD WOOD COMPANY LTD','FF','','','','129586565',1,'','41011'),(29,'BATO INVESTMENT GROUP LTD','FF','','','','107688903',1,'','41012'),(30,'TIMBERLINE COMPANY','FF','','','','127560025',1,'','41013'),(31,'AUTHENTIC GARAGE LTD','FF','','','','103551615',1,'','41014'),(32,'SHABA LTD','FF','','','','108138012',1,'','41015'),(33,'HAPPY CUSTOMER CARE HARDWARE LTD','FF','','','','108847518',1,'','41016'),(34,'INFINITE GLORY LTD','FF','','','','121260936',1,'','41017'),(50,'SPERO LTD','ff','','','','107177569',1,'','41018'),(51,'LA GLOIRE CONFIANCE TRADING LTD','ff','','','','102053598',1,'','41019'),(56,'UMWAMI SUPPLY AND DESIGN COMPANY ','ff','','','','121827169',1,'','41020'),(57,'THE EMPIRE CO LTD','ff','','','','118469824',1,'','41021'),(62,'KN UMUCYO BUMBA LTD','ff','','','','107943182',1,'','41022'),(63,'SAT GENERATOR HARDWARE LTD','ff','','','','119831457',1,'','41023'),(64,'KIN GENERAL LTD','ff','','','','103293329',1,'','41024'),(68,'NICKYS FAIR HARDWARE LTD','ff','','','','110005452',1,'','41025'),(72,'PANATECH','Pascal Twizerimana','panatechrwanda@gmail.com','0791957866','Kigali Rwanda','108675815',1,'','41026'),(74,'UMUHIRE JEANNETTE','ff','','','','999999923',1,'','41027'),(75,'REG','FF','','','','999999992',1,'','41028'),(80,'JUVENAL HANYURWIMFURA','FF','','','','999999932',1,'','41029'),(81,'EZECHIEL NSEKANABO','FF','','','','078888888',1,'','41030'),(82,'GAKWANDI MUNEZERO ERIC','FF','','','','12345789',1,'','41031'),(83,'SECURITY GUARDS','FF','','','','078456734',1,'','41032'),(84,'ANDRE MUNYEMANA','FF','','','','1234323434',1,'','41033'),(85,'JEAN BOSCO KIMENYI','FF','','','','123456785',1,'','41034'),(86,'RUHINJA FRANCOIS ','FF','','','','222222222',1,'','41035'),(87,'RUHINJA FRANCOIS ','FF','','','','3333333333',1,'','41036'),(88,'HARERIMANA GREGOIRE','FF','','','','1111111111',1,'','41037'),(89,'MASENGESHO FLAVIEN','FF','','','','555555555',1,'','41038'),(90,'DONATH MAHORO','FF','','','','44444444',1,'','41039'),(91,'NIYONKURU DESIRE','FF','','','','123456734',1,'','41040'),(92,'NIYONKURU DESIRE','FF','','','',' 2323232333',1,'','41041'),(93,'EKS LTD','FF','','','','85858585',1,'','41042'),(94,'ONE FAMILY CONSTRUCTION LTD','FF','','','','14251425',1,'','41043'),(95,'KAREMERA APPOLINAIRE','FF','','','','123423112',1,'','41044'),(96,'KAYIRANGA EMMANUEL','FF','','','','78543232',1,'','41045'),(97,'EDMOND NKURIKIYIMANA','FF','','','','123123123',1,'','41046'),(98,'TWILINGIYIMANA JEAN BOSCO','FF','','','','453456765',1,'','41047'),(99,'RUBUMBA ANTOINE','FF','','','','78934532',1,'','41048'),(100,'WASAC','FF','','','','23423454',1,'','41049');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_name_short` varchar(100) DEFAULT NULL,
  `system_name_full` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `backup_email` varchar(100) DEFAULT NULL,
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_username` varchar(100) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `backup_time` time DEFAULT NULL,
  `backup_frequency` enum('daily','weekly','monthly') DEFAULT NULL,
  `timezone` varchar(100) DEFAULT NULL,
  `date_format` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'RCEF','RWANDA CHILDREN EDUCATIONAL FOUNDATION ','uploads/1746799010_logo.png','rcfrw2013@gmail.com','+250787893208','P.O BOX 1787 Kigali Rwanda','nkurec2@gmail.com','smtp.gmail.com','panatechrwanda@gmail.com','akjbosytxtskvfaj','17:00:00','daily','Africa/Kigali','Y-m-d');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax_brackets`
--

DROP TABLE IF EXISTS `tax_brackets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tax_brackets` (
  `id` int(11) NOT NULL,
  `country` varchar(2) NOT NULL,
  `lower_bound` decimal(15,2) NOT NULL,
  `upper_bound` decimal(15,2) DEFAULT NULL,
  `rate` decimal(5,2) NOT NULL,
  `fixed_amount` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_brackets`
--

LOCK TABLES `tax_brackets` WRITE;
/*!40000 ALTER TABLE `tax_brackets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tax_brackets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax_rates`
--

DROP TABLE IF EXISTS `tax_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tax_rates` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `tax_code` varchar(20) NOT NULL,
  `is_compound` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_rates`
--

LOCK TABLES `tax_rates` WRITE;
/*!40000 ALTER TABLE `tax_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `tax_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terms`
--

DROP TABLE IF EXISTS `terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `term_name` varchar(50) DEFAULT NULL,
  `year` char(20) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terms`
--

LOCK TABLES `terms` WRITE;
/*!40000 ALTER TABLE `terms` DISABLE KEYS */;
INSERT INTO `terms` VALUES (1,'1st ','2025-2026','2025-01-01','2025-01-01','2025-05-21 13:02:07');
/*!40000 ALTER TABLE `terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_lines`
--

DROP TABLE IF EXISTS `transaction_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1296 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_lines`
--

LOCK TABLES `transaction_lines` WRITE;
/*!40000 ALTER TABLE `transaction_lines` DISABLE KEYS */;
INSERT INTO `transaction_lines` VALUES (1,1,10,500000.36,0.00),(2,1,11,101978.10,0.00),(3,1,103,182524.50,0.00),(4,1,12,4370.49,0.00),(5,1,13,0.00,0.00),(6,1,102,0.00,788873.45),(7,2,189,180000.00,0.00),(8,2,51,0.00,180000.00),(9,3,190,167524.00,0.00),(10,3,51,0.00,167524.00),(11,4,191,300000.00,0.00),(12,4,51,0.00,300000.00),(13,5,192,224714.00,0.00),(14,5,51,0.00,224714.00),(15,6,193,102381.00,0.00),(16,6,51,0.00,102381.00),(17,7,194,366667.00,0.00),(18,7,51,0.00,366667.00),(19,8,195,295238.00,0.00),(20,8,51,0.00,295238.00),(21,9,195,320000.00,0.00),(22,9,51,0.00,320000.00),(23,10,197,440952.00,0.00),(24,10,51,0.00,440952.00),(25,11,198,51150.00,0.00),(26,11,51,0.00,51150.00),(27,12,199,339762.00,0.00),(28,12,51,0.00,339762.00),(29,13,200,339762.00,0.00),(30,13,51,0.00,339762.00),(31,14,200,195619.00,0.00),(32,14,51,0.00,195619.00),(33,15,201,219762.00,0.00),(34,15,51,0.00,219762.00),(35,16,202,80429.00,0.00),(36,16,51,0.00,80429.00),(37,17,203,176095.00,0.00),(38,17,51,0.00,176095.00),(39,18,204,74333.00,0.00),(40,18,51,0.00,74333.00),(41,19,205,207619.00,0.00),(42,19,51,0.00,207619.00),(43,20,206,344762.00,0.00),(44,20,51,0.00,344762.00),(45,21,207,229048.00,0.00),(46,21,51,0.00,229048.00),(47,22,208,124762.00,0.00),(48,22,51,0.00,124762.00),(49,23,209,72143.00,0.00),(50,23,51,0.00,72143.00),(51,24,210,181905.00,0.00),(52,24,51,0.00,181905.00),(53,25,211,16426.00,0.00),(54,25,51,0.00,16426.00),(55,26,212,156952.00,0.00),(56,26,51,0.00,156952.00),(57,27,213,309524.00,0.00),(58,27,51,0.00,309524.00),(59,28,214,71429.00,0.00),(60,28,51,0.00,71429.00),(61,29,215,81524.00,0.00),(62,29,51,0.00,81524.00),(63,30,216,2952381.00,0.00),(64,30,51,0.00,2952381.00),(69,33,106,217000.00,0.00),(70,33,52,0.00,217000.00),(71,34,106,4314000.00,0.00),(72,34,52,0.00,4314000.00),(73,35,106,32000.00,0.00),(74,35,52,0.00,32000.00),(75,36,106,27500.00,0.00),(76,36,52,0.00,27500.00),(77,37,106,75000.00,0.00),(78,37,52,0.00,75000.00),(79,38,106,1102000.00,0.00),(80,38,52,0.00,1102000.00),(81,39,106,762000.00,0.00),(82,39,52,0.00,762000.00),(83,40,106,479000.00,0.00),(84,40,52,0.00,479000.00),(85,41,106,15000.00,0.00),(86,41,52,0.00,15000.00),(87,42,106,279000.00,0.00),(88,42,47,0.00,279000.00),(111,54,25,70000.00,0.00),(112,54,52,0.00,70000.00),(115,56,25,60000.00,0.00),(116,56,52,0.00,60000.00),(119,58,106,66000.00,0.00),(120,58,52,0.00,66000.00),(121,59,106,120000.00,0.00),(122,59,52,0.00,120000.00),(185,91,249,111000.00,0.00),(186,91,52,0.00,111000.00),(187,92,25,70000.00,0.00),(188,92,52,0.00,70000.00),(189,93,23,120000.00,0.00),(190,93,52,0.00,120000.00),(191,94,35,60000.00,0.00),(192,94,52,0.00,60000.00),(193,95,17,5000.00,0.00),(194,95,52,0.00,5000.00),(197,97,90,192000.00,0.00),(198,97,52,0.00,192000.00),(199,98,25,60000.00,0.00),(200,98,52,0.00,60000.00),(203,100,15,13000.00,0.00),(204,100,52,0.00,13000.00),(205,101,23,20000.00,0.00),(206,101,52,0.00,20000.00),(209,103,25,50000.00,0.00),(210,103,52,0.00,50000.00),(213,105,14,10000.00,0.00),(214,105,52,0.00,10000.00),(215,106,34,300000.00,0.00),(216,106,47,0.00,300000.00),(217,107,23,25000.00,0.00),(218,107,52,0.00,25000.00),(219,108,23,40250.00,0.00),(220,108,52,0.00,40250.00),(224,110,17,5000.00,0.00),(225,110,52,0.00,5000.00),(226,111,23,100000.00,0.00),(227,111,52,0.00,100000.00),(228,112,23,40000.00,0.00),(229,112,52,0.00,40000.00),(232,114,35,60000.00,0.00),(233,114,52,0.00,60000.00),(234,115,23,40000.00,0.00),(235,115,52,0.00,40000.00),(236,116,35,30000.00,0.00),(237,116,52,0.00,30000.00),(244,120,251,50000.00,0.00),(245,120,52,0.00,50000.00),(256,126,35,50000.00,0.00),(257,126,52,0.00,50000.00),(258,127,42,4569750.00,0.00),(259,127,47,0.00,4569750.00),(260,128,23,240000.00,0.00),(261,128,52,0.00,240000.00),(262,129,23,3000.00,0.00),(263,129,52,0.00,3000.00),(264,130,23,2000.00,0.00),(265,130,52,0.00,2000.00),(266,131,23,75000.00,0.00),(267,131,52,0.00,75000.00),(268,132,251,30000.00,0.00),(269,132,52,0.00,30000.00),(270,133,24,500.00,0.00),(271,133,52,0.00,500.00),(276,136,249,30000.00,0.00),(277,136,52,0.00,30000.00),(278,137,25,50000.00,0.00),(279,137,52,0.00,50000.00),(282,139,14,90000.00,0.00),(283,139,52,0.00,90000.00),(284,140,23,10000.00,0.00),(285,140,52,0.00,10000.00),(286,141,35,2000000.00,0.00),(287,141,47,0.00,2000000.00),(288,142,23,40000.00,0.00),(289,142,52,0.00,40000.00),(290,143,35,15000.00,0.00),(291,143,52,0.00,15000.00),(292,144,17,15000.00,0.00),(293,144,52,0.00,15000.00),(294,145,66,50000.00,0.00),(295,145,52,0.00,50000.00),(296,146,46,15000.00,0.00),(297,146,52,0.00,15000.00),(298,147,17,30000.00,0.00),(299,147,52,0.00,30000.00),(300,148,25,50000.00,0.00),(301,148,52,0.00,50000.00),(302,149,25,70000.00,0.00),(303,149,52,0.00,70000.00),(304,150,35,90000.00,0.00),(305,150,52,0.00,90000.00),(306,151,25,70000.00,0.00),(307,151,52,0.00,70000.00),(310,153,25,62397.00,0.00),(311,153,52,0.00,62397.00),(316,155,67,158000.00,0.00),(317,155,52,0.00,158000.00),(318,156,35,700000.00,0.00),(319,156,47,0.00,700000.00),(320,157,23,1000.00,0.00),(321,157,52,0.00,1000.00),(322,158,18,1000.00,0.00),(323,158,52,0.00,1000.00),(324,159,23,3000.00,0.00),(325,159,52,0.00,3000.00),(328,161,23,5000.00,0.00),(329,161,52,0.00,5000.00),(330,162,30,5000.00,0.00),(331,162,52,0.00,5000.00),(332,163,18,2000.00,0.00),(333,163,52,0.00,2000.00),(334,164,65,63000.00,0.00),(335,164,52,0.00,63000.00),(336,165,25,35000.00,0.00),(337,165,52,0.00,35000.00),(338,166,30,5000.00,0.00),(339,166,52,0.00,5000.00),(342,168,23,1000.00,0.00),(343,168,52,0.00,1000.00),(344,169,18,9700.00,0.00),(345,169,52,0.00,9700.00),(348,171,30,2000.00,0.00),(349,171,52,0.00,2000.00),(350,172,21,25000.00,0.00),(351,172,52,0.00,25000.00),(352,173,24,500.00,0.00),(353,173,52,0.00,500.00),(354,174,25,63066.00,0.00),(355,174,52,0.00,63066.00),(356,175,31,15000.00,0.00),(357,175,52,0.00,15000.00),(358,176,25,6800.00,0.00),(359,176,52,0.00,6800.00),(360,177,30,1500.00,0.00),(361,177,52,0.00,1500.00),(368,181,30,1400.00,0.00),(369,181,31,1600.00,0.00),(370,181,52,0.00,3000.00),(373,183,14,6500.00,0.00),(374,183,52,0.00,6500.00),(375,184,19,200000.00,0.00),(376,184,47,0.00,200000.00),(377,185,15,20000.00,0.00),(378,185,52,0.00,20000.00),(385,189,23,200000.00,0.00),(386,189,52,0.00,200000.00),(387,190,20,10000.00,0.00),(388,190,52,0.00,10000.00),(389,191,25,30000.00,0.00),(390,191,52,0.00,30000.00),(393,193,25,40000.00,0.00),(394,193,52,0.00,40000.00),(397,195,23,10000.00,0.00),(398,195,52,0.00,10000.00),(407,200,10,700200.00,0.00),(408,200,47,0.00,700200.00),(411,202,23,75000.00,0.00),(412,202,52,0.00,75000.00),(417,205,23,200000.00,0.00),(418,205,52,0.00,200000.00),(419,206,20,10000.00,0.00),(420,206,52,0.00,10000.00),(421,207,22,10000.00,0.00),(422,207,52,0.00,10000.00),(425,209,23,100000.00,0.00),(426,209,52,0.00,100000.00),(427,210,23,1000.00,0.00),(428,210,52,0.00,1000.00),(431,212,23,1200.00,0.00),(432,212,52,0.00,1200.00),(433,213,41,140000.00,0.00),(434,213,52,0.00,140000.00),(435,214,22,5000.00,0.00),(436,214,52,0.00,5000.00),(439,216,23,30000.00,0.00),(440,216,52,0.00,30000.00),(443,218,23,1500.00,0.00),(444,218,52,0.00,1500.00),(447,220,23,1200.00,0.00),(448,220,52,0.00,1200.00),(455,223,17,10000.00,0.00),(456,223,52,0.00,10000.00),(457,224,23,2000.00,0.00),(458,224,52,0.00,2000.00),(459,225,23,2000.00,0.00),(460,225,52,0.00,2000.00),(461,226,18,5500.00,0.00),(462,226,52,0.00,5500.00),(463,227,38,40000.00,0.00),(464,227,52,0.00,40000.00),(465,228,23,15000.00,0.00),(466,228,52,0.00,15000.00),(467,229,21,25000.00,0.00),(468,229,52,0.00,25000.00),(469,230,23,2500.00,0.00),(470,230,52,0.00,2500.00),(471,231,23,2000.00,0.00),(472,231,52,0.00,2000.00),(473,232,23,2000.00,0.00),(474,232,52,0.00,2000.00),(475,233,23,2000.00,0.00),(476,233,23,0.00,2000.00),(477,234,23,3500.00,0.00),(478,234,52,0.00,3500.00),(479,235,19,200000.00,0.00),(480,235,47,0.00,200000.00),(481,236,23,10000.00,0.00),(482,236,52,0.00,10000.00),(483,237,10,80000.00,0.00),(484,237,48,0.00,80000.00),(485,238,20,3500.00,0.00),(486,238,52,0.00,3500.00),(487,239,23,2000.00,0.00),(488,239,52,0.00,2000.00),(489,240,23,2000.00,0.00),(490,240,52,0.00,2000.00),(491,241,23,170000.00,0.00),(492,241,47,0.00,170000.00),(493,242,16,115000.00,0.00),(494,242,47,0.00,115000.00),(495,243,23,2000.00,0.00),(496,243,52,0.00,2000.00),(497,244,23,100000.00,0.00),(498,244,52,0.00,100000.00),(499,245,10,115000.00,0.00),(500,245,47,0.00,115000.00),(501,246,35,1000000.00,0.00),(502,246,47,0.00,1000000.00),(503,247,35,10000.00,0.00),(504,247,52,0.00,10000.00),(505,248,36,100000.00,0.00),(506,248,47,0.00,100000.00),(507,249,106,181000.00,0.00),(508,249,52,0.00,181000.00),(509,250,23,75000.00,0.00),(510,250,47,0.00,75000.00),(511,251,36,6918.00,0.00),(512,251,52,0.00,6918.00),(513,252,36,122884.00,0.00),(514,252,52,0.00,122884.00),(515,253,36,91703.00,0.00),(516,253,36,0.00,91703.00),(522,256,23,200000.00,0.00),(523,256,47,0.00,200000.00),(526,258,38,30000.00,0.00),(527,258,52,0.00,30000.00),(528,259,23,1100.00,0.00),(529,259,52,0.00,1100.00),(530,260,23,2000.00,0.00),(531,260,52,0.00,2000.00),(532,261,36,31173.00,0.00),(533,261,52,0.00,31173.00),(534,262,23,3200.00,0.00),(535,262,47,0.00,3200.00),(536,263,17,10000.00,0.00),(537,263,52,0.00,10000.00),(544,267,34,300000.00,0.00),(545,267,47,0.00,300000.00),(546,268,68,10100.00,0.00),(547,268,52,0.00,10100.00),(550,270,14,2000.00,0.00),(551,270,52,0.00,2000.00),(552,271,250,8000.00,0.00),(553,271,52,0.00,8000.00),(560,273,10,280000.30,0.00),(561,273,11,53626.44,0.00),(562,273,103,78913.80,0.00),(563,273,12,2298.28,0.00),(564,273,13,0.00,0.00),(565,273,102,0.00,414838.82),(566,274,23,20000.00,0.00),(567,274,52,0.00,20000.00),(568,275,23,2000.00,0.00),(569,275,52,0.00,2000.00),(620,301,103,127982.00,0.00),(621,301,12,4000.00,0.00),(622,301,11,93324.00,0.00),(623,301,52,0.00,225306.00),(664,322,10,155000.00,0.00),(665,322,47,0.00,155000.00),(760,370,23,4740.00,0.00),(761,370,52,0.00,4740.00),(818,390,23,3000.00,0.00),(819,390,52,0.00,3000.00),(822,318,200,0.00,80000.00),(823,318,51,80000.00,0.00),(824,31,192,0.00,25000.00),(825,31,51,25000.00,0.00),(828,104,10,120000.00,0.00),(829,104,47,0.00,120000.00),(830,69,106,25000.00,0.00),(831,69,52,0.00,25000.00),(832,397,11,0.00,0.00),(833,397,12,0.00,0.00),(836,43,106,575000.00,0.00),(837,43,52,0.00,575000.00),(840,272,10,239999.89,0.00),(841,272,11,44835.14,0.00),(842,272,103,60075.30,0.00),(843,272,12,1921.51,0.00),(844,272,13,0.00,0.00),(845,272,102,0.00,346831.84),(854,32,106,3474000.00,0.00),(855,32,52,0.00,3474000.00),(856,257,23,1100.00,0.00),(857,257,52,0.00,1100.00),(858,221,23,30250.00,0.00),(859,221,52,0.00,30250.00),(860,219,103,15000.00,0.00),(861,219,52,0.00,15000.00),(862,217,21,5000.00,0.00),(863,217,52,0.00,5000.00),(864,215,23,2000.00,0.00),(865,215,52,0.00,2000.00),(866,211,23,2000.00,0.00),(867,211,52,0.00,2000.00),(868,208,23,1000.00,0.00),(869,208,52,0.00,1000.00),(870,222,103,127982.00,0.00),(871,222,11,93324.00,0.00),(872,222,12,4000.00,0.00),(873,222,52,0.00,225306.00),(874,73,14,7000.00,0.00),(875,73,52,0.00,7000.00),(876,204,23,145000.00,0.00),(877,204,47,0.00,145000.00),(878,203,16,154500.00,0.00),(879,203,47,0.00,154500.00),(880,201,14,7000.00,0.00),(881,201,52,0.00,7000.00),(882,199,23,4000.00,0.00),(883,199,52,0.00,4000.00),(886,198,31,3500.00,0.00),(887,198,52,0.00,3500.00),(888,197,19,5000.00,0.00),(889,197,52,0.00,5000.00),(890,255,10,280500.00,0.00),(891,255,10,220000.00,0.00),(892,255,47,0.00,500500.00),(893,194,23,1500.00,0.00),(894,194,52,0.00,1500.00),(895,192,23,3500.00,0.00),(896,192,52,0.00,3500.00),(897,188,31,2000.00,0.00),(898,188,52,0.00,2000.00),(899,187,23,15000.00,0.00),(900,187,52,0.00,15000.00),(901,186,20,3500.00,0.00),(902,186,52,0.00,3500.00),(903,182,23,1000.00,0.00),(904,182,52,0.00,1000.00),(905,180,23,20000.00,0.00),(906,180,52,0.00,20000.00),(907,179,65,141800.00,0.00),(908,179,47,0.00,141800.00),(909,178,23,7000.00,0.00),(910,178,52,0.00,7000.00),(911,170,65,80000.00,0.00),(912,170,52,0.00,80000.00),(913,167,23,700.00,0.00),(914,167,52,0.00,700.00),(915,160,23,5000.00,0.00),(916,160,52,0.00,5000.00),(917,154,11,53328.00,0.00),(918,154,12,4000.00,0.00),(919,154,103,127982.00,0.00),(920,154,52,0.00,185310.00),(921,152,23,8000.00,0.00),(922,152,52,0.00,8000.00),(923,138,23,3000.00,0.00),(924,138,52,0.00,3000.00),(925,135,67,30000.00,0.00),(926,135,52,0.00,30000.00),(927,134,23,10000.00,0.00),(928,134,52,0.00,10000.00),(933,122,26,80000.00,0.00),(934,122,52,0.00,80000.00),(935,119,23,75000.00,0.00),(936,119,52,0.00,75000.00),(937,117,26,50000.00,0.00),(938,117,52,0.00,50000.00),(939,113,26,70000.00,0.00),(940,113,52,0.00,70000.00),(941,102,30,7000.00,0.00),(942,102,52,0.00,7000.00),(943,99,250,20250.00,0.00),(944,99,52,0.00,20250.00),(947,89,106,60000.00,0.00),(948,89,52,0.00,60000.00),(951,88,17,5000.00,0.00),(952,88,52,0.00,5000.00),(953,87,106,55500.00,0.00),(954,87,52,0.00,55500.00),(955,86,106,47000.00,0.00),(956,86,52,0.00,47000.00),(957,85,106,25000.00,0.00),(958,85,52,0.00,25000.00),(959,80,106,470600.00,0.00),(960,80,52,0.00,470600.00),(961,67,15,25000.00,0.00),(962,67,52,0.00,25000.00),(963,62,106,147000.00,0.00),(964,62,52,0.00,147000.00),(965,45,23,16000.00,0.00),(966,45,52,0.00,16000.00),(967,308,23,41000.00,0.00),(968,308,52,0.00,41000.00),(969,304,23,40000.00,0.00),(970,304,52,0.00,40000.00),(971,330,106,75000.00,0.00),(972,330,52,0.00,75000.00),(973,359,23,15000.00,0.00),(974,359,52,0.00,15000.00),(975,358,23,1500.00,0.00),(976,358,52,0.00,1500.00),(977,357,14,22000.00,0.00),(978,357,52,0.00,22000.00),(979,356,23,2100.00,0.00),(980,356,52,0.00,2100.00),(981,355,14,20250.00,0.00),(982,355,52,0.00,20250.00),(983,354,35,10000.00,0.00),(984,354,52,0.00,10000.00),(985,353,17,10000.00,0.00),(986,353,52,0.00,10000.00),(987,352,15,10000.00,0.00),(988,352,52,0.00,10000.00),(989,350,23,20000.00,0.00),(990,350,52,0.00,20000.00),(991,348,23,20000.00,0.00),(992,348,52,0.00,20000.00),(993,347,23,20000.00,0.00),(994,347,52,0.00,20000.00),(995,346,23,20000.00,0.00),(996,346,52,0.00,20000.00),(997,345,23,20000.00,0.00),(998,345,52,0.00,20000.00),(999,344,23,20000.00,0.00),(1000,344,52,0.00,20000.00),(1001,343,23,20000.00,0.00),(1002,343,52,0.00,20000.00),(1003,341,23,20000.00,0.00),(1004,341,52,0.00,20000.00),(1005,339,23,20000.00,0.00),(1006,339,52,0.00,20000.00),(1007,338,35,60000.00,0.00),(1008,338,52,0.00,60000.00),(1009,337,15,55500.00,0.00),(1010,337,52,0.00,55500.00),(1011,336,23,20000.00,0.00),(1012,336,52,0.00,20000.00),(1013,335,23,20000.00,0.00),(1014,335,52,0.00,20000.00),(1017,395,82,15000.00,0.00),(1018,395,52,0.00,15000.00),(1019,394,23,15000.00,0.00),(1020,394,52,0.00,15000.00),(1021,393,41,392500.00,0.00),(1022,393,52,0.00,392500.00),(1023,392,31,5000.00,0.00),(1024,392,52,0.00,5000.00),(1025,391,14,8400.00,0.00),(1026,391,52,0.00,8400.00),(1027,389,35,148250.00,0.00),(1028,389,52,0.00,148250.00),(1029,385,31,52500.00,0.00),(1030,385,52,0.00,52500.00),(1031,384,23,1500.00,0.00),(1032,384,52,0.00,1500.00),(1033,383,252,40000.00,0.00),(1034,383,52,0.00,40000.00),(1035,396,90,330500.00,0.00),(1036,396,52,0.00,330500.00),(1037,90,25,50000.00,0.00),(1038,90,52,0.00,50000.00),(1039,96,25,50000.00,0.00),(1040,96,52,0.00,50000.00),(1041,44,35,65000.00,0.00),(1042,44,52,0.00,65000.00),(1043,46,106,439500.00,0.00),(1044,46,52,0.00,439500.00),(1045,47,106,155000.00,0.00),(1046,47,52,0.00,155000.00),(1047,48,106,332000.00,0.00),(1048,48,52,0.00,332000.00),(1049,49,106,214000.00,0.00),(1050,49,52,0.00,214000.00),(1051,50,106,14000.00,0.00),(1052,50,52,0.00,14000.00),(1053,51,106,329000.00,0.00),(1054,51,52,0.00,329000.00),(1055,52,106,3216500.00,0.00),(1056,52,52,0.00,3216500.00),(1057,53,106,15000.00,0.00),(1058,53,52,0.00,15000.00),(1059,57,106,141800.00,0.00),(1060,57,52,0.00,141800.00),(1061,123,251,46000.00,0.00),(1062,123,52,0.00,46000.00),(1063,380,69,607000.00,0.00),(1064,380,52,0.00,607000.00),(1065,381,15,10000.00,0.00),(1066,381,52,0.00,10000.00),(1067,376,23,30000.00,0.00),(1068,376,52,0.00,30000.00),(1069,374,25,65000.00,0.00),(1070,374,52,0.00,65000.00),(1071,373,14,3500.00,0.00),(1072,373,52,0.00,3500.00),(1075,329,106,500000.00,0.00),(1076,329,47,0.00,500000.00),(1077,388,30,30500.00,0.00),(1078,388,52,0.00,30500.00),(1079,387,106,903200.00,0.00),(1080,387,52,0.00,903200.00),(1081,379,35,100000.00,0.00),(1082,379,47,0.00,100000.00),(1083,369,23,4000.00,0.00),(1084,369,52,0.00,4000.00),(1085,368,69,30500.00,0.00),(1086,368,52,0.00,30500.00),(1087,386,23,90000.00,0.00),(1088,386,52,0.00,90000.00),(1089,382,31,6000.00,0.00),(1090,382,52,0.00,6000.00),(1091,378,23,3000.00,0.00),(1092,378,52,0.00,3000.00),(1093,377,15,4400.00,0.00),(1094,377,52,0.00,4400.00),(1095,372,67,7000.00,0.00),(1096,372,52,0.00,7000.00),(1097,375,31,2300.00,0.00),(1098,375,52,0.00,2300.00),(1099,371,23,6000.00,0.00),(1100,371,52,0.00,6000.00),(1101,367,25,64100.00,0.00),(1102,367,52,0.00,64100.00),(1103,366,18,5000.00,0.00),(1104,366,52,0.00,5000.00),(1105,365,23,50000.00,0.00),(1106,365,52,0.00,50000.00),(1107,364,23,5000.00,0.00),(1108,364,52,0.00,5000.00),(1109,363,23,1000.00,0.00),(1110,363,52,0.00,1000.00),(1111,362,25,20000.00,0.00),(1112,362,52,0.00,20000.00),(1113,361,30,22500.00,0.00),(1114,361,251,0.00,22500.00),(1115,360,251,38250.00,0.00),(1116,360,52,0.00,38250.00),(1117,351,40,296000.00,0.00),(1118,351,52,0.00,296000.00),(1119,349,23,6000.00,0.00),(1120,349,52,0.00,6000.00),(1121,342,58,373000.00,0.00),(1122,342,52,0.00,373000.00),(1123,340,23,1000.00,0.00),(1124,340,52,0.00,1000.00),(1125,324,10,126000.00,0.00),(1126,324,47,0.00,126000.00),(1127,317,23,3000.00,0.00),(1128,317,52,0.00,3000.00),(1129,316,65,80000.00,0.00),(1130,316,52,0.00,80000.00),(1131,334,251,40000.00,0.00),(1132,334,52,0.00,40000.00),(1133,323,15,20000.00,0.00),(1134,323,52,0.00,20000.00),(1135,315,249,40250.00,0.00),(1136,315,52,0.00,40250.00),(1137,314,65,10000.00,0.00),(1138,314,52,0.00,10000.00),(1139,312,25,63500.00,0.00),(1140,312,52,0.00,63500.00),(1141,333,106,1500000.00,0.00),(1142,333,52,0.00,1500000.00),(1143,332,15,20000.00,0.00),(1144,332,52,0.00,20000.00),(1145,331,17,10000.00,0.00),(1146,331,52,0.00,10000.00),(1147,321,35,1500000.00,0.00),(1148,321,47,0.00,1500000.00),(1149,320,10,80000.00,0.00),(1150,320,47,0.00,80000.00),(1151,311,25,25000.00,0.00),(1152,311,52,0.00,25000.00),(1153,306,31,20250.00,0.00),(1154,306,52,0.00,20250.00),(1155,283,23,10000.00,0.00),(1156,283,52,0.00,10000.00),(1157,125,34,500000.00,0.00),(1158,125,47,0.00,500000.00),(1159,121,35,150000.00,0.00),(1160,121,47,0.00,150000.00),(1161,118,23,30000.00,0.00),(1162,118,52,0.00,30000.00),(1163,124,23,30000.00,0.00),(1164,124,52,0.00,30000.00),(1165,319,106,400000.00,0.00),(1166,319,52,0.00,400000.00),(1167,82,106,640000.00,0.00),(1168,82,52,0.00,640000.00),(1169,313,35,114400.00,0.00),(1170,313,52,0.00,114400.00),(1171,310,35,1000000.00,0.00),(1172,310,47,0.00,1000000.00),(1173,309,35,500000.00,0.00),(1174,309,47,0.00,500000.00),(1175,75,25,30000.00,0.00),(1176,75,52,0.00,30000.00),(1177,66,106,240000.00,0.00),(1178,66,52,0.00,240000.00),(1179,64,30,12000.00,0.00),(1180,64,52,0.00,12000.00),(1181,61,106,225000.00,0.00),(1182,61,52,0.00,225000.00),(1183,55,106,497000.00,0.00),(1184,55,52,0.00,497000.00),(1185,307,249,6000.00,0.00),(1186,307,52,0.00,6000.00),(1187,305,17,10000.00,0.00),(1188,305,52,0.00,10000.00),(1189,84,106,12000.00,0.00),(1190,84,52,0.00,12000.00),(1191,300,106,209500.00,0.00),(1192,300,52,0.00,209500.00),(1193,299,35,900000.00,0.00),(1194,299,47,0.00,900000.00),(1195,83,106,27200.00,0.00),(1196,83,52,0.00,27200.00),(1197,81,106,182000.00,0.00),(1198,81,52,0.00,182000.00),(1199,79,106,36000.00,0.00),(1200,79,52,0.00,36000.00),(1201,78,106,67200.00,0.00),(1202,78,52,0.00,67200.00),(1203,328,106,517600.00,0.00),(1204,328,52,0.00,517600.00),(1205,327,106,266000.00,0.00),(1206,327,52,0.00,266000.00),(1207,326,106,18400.00,0.00),(1208,326,52,0.00,18400.00),(1209,325,106,157500.00,0.00),(1210,325,52,0.00,157500.00),(1211,298,30,107000.00,0.00),(1212,298,52,0.00,107000.00),(1213,297,35,10000.00,0.00),(1214,297,47,0.00,10000.00),(1215,289,35,20000.00,0.00),(1216,289,52,0.00,20000.00),(1217,288,249,70000.00,0.00),(1218,288,52,0.00,70000.00),(1219,109,35,30000.00,0.00),(1220,109,23,10000.00,0.00),(1221,109,52,0.00,40000.00),(1222,77,106,1988600.00,0.00),(1223,77,52,0.00,1988600.00),(1224,76,106,517600.00,0.00),(1225,76,52,0.00,517600.00),(1226,74,106,266000.00,0.00),(1227,74,52,0.00,266000.00),(1228,72,106,18400.00,0.00),(1229,72,52,0.00,18400.00),(1230,71,106,157500.00,0.00),(1231,71,52,0.00,157500.00),(1232,296,25,65157.00,0.00),(1233,296,52,0.00,65157.00),(1234,70,106,48000.00,0.00),(1235,70,52,0.00,48000.00),(1238,287,17,10000.00,0.00),(1239,287,52,0.00,10000.00),(1240,295,25,69000.00,0.00),(1241,295,52,0.00,69000.00),(1242,284,2,0.00,0.00),(1243,284,47,0.00,0.00),(1244,303,23,2000.00,0.00),(1245,303,52,0.00,2000.00),(1246,302,41,30250.00,0.00),(1247,302,52,0.00,30250.00),(1248,286,76,84660.00,0.00),(1249,286,52,0.00,84660.00),(1250,282,17,10000.00,0.00),(1251,282,52,0.00,10000.00),(1252,281,35,200000.00,0.00),(1253,281,47,0.00,200000.00),(1254,280,35,50000.00,0.00),(1255,280,52,0.00,50000.00),(1256,279,35,100000.00,0.00),(1257,279,47,0.00,100000.00),(1258,196,18,33499.00,0.00),(1259,196,52,0.00,33499.00),(1260,68,106,1174500.00,0.00),(1261,68,52,0.00,1174500.00),(1262,65,106,1200000.00,0.00),(1263,65,52,0.00,1200000.00),(1264,285,35,66000.00,0.00),(1265,285,47,0.00,66000.00),(1266,278,23,40000.00,0.00),(1267,278,52,0.00,40000.00),(1268,294,23,90000.00,0.00),(1269,294,52,0.00,90000.00),(1270,293,23,1000.00,0.00),(1271,293,52,0.00,1000.00),(1272,292,25,60012.00,0.00),(1273,292,52,0.00,60012.00),(1274,291,23,1500.00,0.00),(1275,291,52,0.00,1500.00),(1276,277,35,2000000.00,0.00),(1277,277,47,0.00,2000000.00),(1278,276,23,30000.00,0.00),(1279,276,52,0.00,30000.00),(1280,269,23,30000.00,0.00),(1281,269,52,0.00,30000.00),(1282,266,23,30000.00,0.00),(1283,266,52,0.00,30000.00),(1284,265,249,5000.00,0.00),(1285,265,52,0.00,5000.00),(1286,290,22,10000.00,0.00),(1287,290,52,0.00,10000.00),(1288,63,106,1200000.00,0.00),(1289,63,52,0.00,1200000.00),(1290,264,23,30000.00,0.00),(1291,264,52,0.00,30000.00),(1292,254,36,50000.00,0.00),(1293,254,52,0.00,50000.00),(1294,60,106,5952400.00,0.00),(1295,60,52,0.00,5952400.00);
/*!40000 ALTER TABLE `transaction_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_taxes`
--

DROP TABLE IF EXISTS `transaction_taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_taxes` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `tax_rate_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `tax_rate_id` (`tax_rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_taxes`
--

LOCK TABLES `transaction_taxes` WRITE;
/*!40000 ALTER TABLE `transaction_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_date` date NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'RWF',
  `exchange_rate` decimal(10,4) DEFAULT 1.0000,
  `fiscal_year` varchar(9) DEFAULT NULL,
  `is_reconciled` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_type` enum('journal_entry','payment','receipt','invoice','bill','adjustment') NOT NULL DEFAULT 'journal_entry',
  `status` enum('draft','posted','voided') NOT NULL DEFAULT 'draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `period_id` int(11) DEFAULT NULL,
  `trx_type` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `period_id` (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=398 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,'2025-05-06','RCEF-20250506094309','Salaries payment',0.00,'RWF',1.0000,NULL,0,6,'2025-05-06 14:43:09','journal_entry','posted',6,'2025-05-06 10:43:10',2,''),(2,'2025-05-21','RCEF-20250521112508','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 09:25:08','journal_entry','posted',6,'2025-05-21 11:25:08',2,''),(3,'2025-05-21','RCEF-20250521125226','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:52:26','journal_entry','posted',6,'2025-05-21 12:52:26',2,''),(4,'2025-05-21','RCEF-20250521125245','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:52:45','journal_entry','posted',6,'2025-05-21 12:52:45',2,''),(5,'2025-05-21','RCEF-20250521125258','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:52:58','journal_entry','posted',6,'2025-05-21 12:52:58',2,''),(6,'2025-05-21','RCEF-20250521125315','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:53:15','journal_entry','posted',6,'2025-05-21 12:53:15',2,''),(7,'2025-05-21','RCEF-20250521125326','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:53:26','journal_entry','posted',6,'2025-05-21 12:53:26',2,''),(8,'2025-05-21','RCEF-20250521125337','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:53:37','journal_entry','posted',6,'2025-05-21 12:53:37',2,''),(9,'2025-05-21','RCEF-20250521125347','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:53:47','journal_entry','posted',6,'2025-05-21 12:53:47',2,''),(10,'2025-05-21','RCEF-20250521125400','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:54:00','journal_entry','posted',6,'2025-05-21 12:54:00',2,''),(11,'2025-05-21','RCEF-20250521125410','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:54:10','journal_entry','posted',6,'2025-05-21 12:54:10',2,''),(12,'2025-05-21','RCEF-20250521125436','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:54:36','journal_entry','posted',6,'2025-05-21 12:54:36',2,''),(13,'2025-05-21','RCEF-20250521125456','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:54:56','journal_entry','posted',6,'2025-05-21 12:54:56',2,''),(14,'2025-05-21','RCEF-20250521125510','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:55:10','journal_entry','posted',6,'2025-05-21 12:55:10',2,''),(15,'2025-05-21','RCEF-20250521125526','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:55:26','journal_entry','posted',6,'2025-05-21 12:55:26',2,''),(16,'2025-05-21','RCEF-20250521125541','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:55:41','journal_entry','posted',6,'2025-05-21 12:55:41',2,''),(17,'2025-05-21','RCEF-20250521125554','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:55:54','journal_entry','posted',6,'2025-05-21 12:55:54',2,''),(18,'2025-05-21','RCEF-20250521125606','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:56:06','journal_entry','posted',6,'2025-05-21 12:56:06',2,''),(19,'2025-05-21','RCEF-20250521125621','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:56:21','journal_entry','posted',6,'2025-05-21 12:56:21',2,''),(20,'2025-05-21','RCEF-20250521125635','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:56:35','journal_entry','posted',6,'2025-05-21 12:56:35',2,''),(21,'2025-05-21','RCEF-20250521125647','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:56:47','journal_entry','posted',6,'2025-05-21 12:56:47',2,''),(22,'2025-05-21','RCEF-20250521125700','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:57:00','journal_entry','posted',6,'2025-05-21 12:57:00',2,''),(23,'2025-05-21','RCEF-20250521125717','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:57:17','journal_entry','posted',6,'2025-05-21 12:57:17',2,''),(24,'2025-05-21','RCEF-20250521125835','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:58:35','journal_entry','posted',6,'2025-05-21 12:58:35',2,''),(25,'2025-05-21','RCEF-20250521125846','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:58:46','journal_entry','posted',6,'2025-05-21 12:58:46',2,''),(26,'2025-05-21','RCEF-20250521125856','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:58:56','journal_entry','posted',6,'2025-05-21 12:58:56',2,''),(27,'2025-05-21','RCEF-20250521125909','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 10:59:09','journal_entry','posted',6,'2025-05-21 12:59:09',2,''),(28,'2025-05-21','RCEF-20250521010228','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 11:02:28','journal_entry','posted',6,'2025-05-21 13:02:28',2,''),(29,'2025-05-21','RCEF-20250521010241','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 11:02:41','journal_entry','posted',6,'2025-05-21 13:02:41',2,''),(30,'2025-05-21','RCEF-20250521010254','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 11:02:54','journal_entry','posted',6,'2025-05-21 13:02:54',2,''),(31,'2025-05-21','RCEF-20250521011021','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,6,'2025-05-21 11:10:21','journal_entry','posted',6,'2025-05-21 13:10:21',2,''),(32,'2025-04-15','RCEF-20250522011651','Purchase of construction supplies ( invoice No 688)',0.00,'RWF',1.0000,NULL,0,10,'2025-05-22 12:26:30','journal_entry','posted',10,'2025-05-22 14:26:30',2,'goods'),(33,'2025-01-28','RCEF-20250522022714','Purchase of construction supplies ( invoice No 001) Imbaho',0.00,'RWF',1.0000,NULL,0,6,'2025-05-22 12:28:45','journal_entry','posted',6,'2025-05-22 14:28:45',2,''),(34,'2025-01-28','RCEF-20250522023026','Purchase of construction supplies ( invoice No 688) Nail; Gipsum',0.00,'RWF',1.0000,NULL,0,6,'2025-05-22 12:32:10','journal_entry','posted',6,'2025-05-22 14:32:10',2,''),(35,'2025-01-28','RCEF-20250522023210','Purchase of construction supplies ( invoice No 691) building line',0.00,'RWF',1.0000,NULL,0,6,'2025-05-22 12:50:55','journal_entry','posted',6,'2025-05-22 14:50:55',2,''),(36,'2025-01-28','RCEF-20250522025055','Purchase of construction supplies ( invoice No 266), niveau, Blade',0.00,'RWF',1.0000,NULL,0,6,'2025-05-22 12:52:50','journal_entry','posted',6,'2025-05-22 14:52:50',2,''),(37,'2025-01-28','RCEF-20250522025250','Purchase of construction supplies ( invoice No 658), Discs',0.00,'RWF',1.0000,NULL,0,6,'2025-05-22 12:54:14','journal_entry','posted',6,'2025-05-22 14:54:14',2,''),(38,'2025-02-04','RCEF-20250522025415','Purchase of construction supplies ( invoice No 17701) Boites Tuyeau.....',0.00,'RWF',1.0000,NULL,0,6,'2025-05-22 13:23:42','journal_entry','posted',6,'2025-05-22 15:23:42',2,''),(39,'2025-02-04','RCEF-20250522032342','Purchase of construction supplies ( invoice No 2103) furring &g stand',0.00,'RWF',1.0000,NULL,0,6,'2025-05-22 13:26:28','journal_entry','posted',6,'2025-05-22 15:26:28',2,''),(40,'2025-02-04','RCEF-20250522034025','Purchase of construction supplies ( invoice No 5919), ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-22 13:42:06','journal_entry','posted',6,'2025-05-22 15:42:06',2,''),(41,'2025-02-04','RCEF-20250204092822','SILCONE,CABLE(INVOICE=2598)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-23 07:33:19','journal_entry','posted',8,'2025-05-23 09:33:19',2,''),(42,'2025-02-05','RCEF-20250523093320','KUGURA IMBAHO( INVOICE NUMBER=002)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-23 07:36:17','journal_entry','posted',8,'2025-05-23 09:36:17',2,''),(43,'2025-02-06','RCEF-20250526100007','radiator,accessories(invoice number=2123)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:03:18','journal_entry','posted',8,'2025-05-26 10:03:18',2,'goods'),(44,'2025-02-06','RCEF-20250526100318','oxygen for hospital(invoice number=2128)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:06:46','journal_entry','posted',8,'2025-05-26 10:06:46',2,'service'),(45,'2025-02-12','RCEF-20250526100646','transport after meeting',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:09:39','journal_entry','posted',8,'2025-05-26 10:09:39',2,'service'),(46,'2025-02-11','RCEF-20250526100939','coixiale cable,toilets isolantes,boite double(invoice number=17781)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:15:16','journal_entry','posted',8,'2025-05-26 10:15:16',2,'goods'),(47,'2025-02-11','RCEF-20250526101517','imbaho(003)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:23:39','journal_entry','posted',8,'2025-05-26 10:23:39',2,'goods'),(48,'2025-02-12','RCEF-20250526102340','cornier,disc,baguette,antirouille(53)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:26:24','journal_entry','posted',8,'2025-05-26 10:26:24',2,'goods'),(49,'2025-02-12','RCEF-20250526102625','furring,c channel,scrows',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:28:10','journal_entry','posted',8,'2025-05-26 10:28:10',2,'goods'),(50,'2025-02-20','RCEF-20250526102811','pinusi(228)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:31:04','journal_entry','posted',8,'2025-05-26 10:31:04',2,'goods'),(51,'2025-02-20','RCEF-20250526103104','compundspaints,matt paints,indasa(2147)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:33:02','journal_entry','posted',8,'2025-05-26 10:33:02',2,'goods'),(52,'2025-02-24','RCEF-20250526103302','bato wall stucco,dolimite paint,gypsum compound(4251)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:36:53','journal_entry','posted',8,'2025-05-26 10:36:53',2,'goods'),(53,'2025-02-26','RCEF-20250526104303','imbaho(42740)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-26 08:48:36','journal_entry','posted',8,'2025-05-26 10:48:37',2,'goods'),(54,'2025-01-06','RCEF-20250527093700','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 07:42:00','journal_entry','posted',6,'2025-05-27 09:42:00',2,''),(55,'2025-03-28','RCEF-20250527084135','Purchase of  construction Material ( invoice no 3197)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 07:45:38','journal_entry','posted',6,'2025-05-27 09:45:38',2,'goods'),(56,'2025-01-08','RCEF-20250527094201','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 07:45:44','journal_entry','posted',6,'2025-05-27 09:45:44',2,''),(57,'2025-02-28','RCEF-20250527094709','plaquette,gaine,watertap,fitting(447)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 07:51:14','journal_entry','posted',8,'2025-05-27 09:51:14',2,'goods'),(58,'2025-03-03','RCEF-20250527095830','flexible,gaine,water tap,fitting(566)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:03:03','journal_entry','posted',8,'2025-05-27 10:03:03',2,''),(59,'2025-03-04','RCEF-20250527100304','projector(7474)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:05:04','journal_entry','posted',8,'2025-05-27 10:05:04',2,''),(60,'2025-03-12','RCEF-20250527100505','screws,sanding sealer,nails,chevilles(194)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:08:49','journal_entry','posted',8,'2025-05-27 10:08:49',2,'goods'),(61,'2025-03-28','RCEF-20250527100553','Purchase of FUN & ALUM Mousing ( invoice no 18322)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 08:11:01','journal_entry','posted',6,'2025-05-27 10:11:01',2,'goods'),(62,'2025-02-14','RCEF-20250527100946','screws,sanding sealer,nails,chevilles(194)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:11:26','journal_entry','posted',8,'2025-05-27 10:11:26',2,'goods'),(63,'2025-03-13','RCEF-20250527101127','cement(12453)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:13:56','journal_entry','posted',8,'2025-05-27 10:13:56',2,'goods'),(64,'2025-03-28','RCEF-20250527101101','purchase of Gloceries ( invoice No 314)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 08:14:09','journal_entry','posted',6,'2025-05-27 10:14:09',2,'goods'),(65,'2025-03-17','RCEF-20250527101356','cement(12462)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:15:26','journal_entry','posted',8,'2025-05-27 10:15:26',2,'service'),(66,'2025-03-28','RCEF-20250527101409','Purchase of construction Materials ( invoice No 14775)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 08:17:37','journal_entry','posted',6,'2025-05-27 10:17:37',2,'goods'),(67,'2025-02-07','RCEF-20250527095830','purchase of cartridge',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 08:17:55','journal_entry','posted',6,'2025-05-27 10:17:55',2,'goods'),(68,'2025-03-17','RCEF-20250527101527','pva primer(4274)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:19:28','journal_entry','posted',8,'2025-05-27 10:19:28',2,'goods'),(69,'2025-01-08','RCEF-20250527101929','roller(1090)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:36:46','journal_entry','posted',8,'2025-05-27 10:36:46',2,''),(70,'2025-03-20','RCEF-20250527103647','cell wall tile (40847)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:40:02','journal_entry','posted',8,'2025-05-27 10:40:02',2,'goods'),(71,'2025-03-22','RCEF-20250527104003','playwoods(2743)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:44:34','journal_entry','posted',8,'2025-05-27 10:44:34',2,'goods'),(72,'2025-03-22','RCEF-20250527104435','nails(853)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:46:09','journal_entry','posted',8,'2025-05-27 10:46:09',2,'goods'),(73,'2025-01-28','RCEF-20250527103545','purchase of  stappler ( invice No 507)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 08:46:12','journal_entry','posted',6,'2025-05-27 10:46:12',2,'goods'),(74,'2025-03-22','RCEF-20250527104610','pinus(1860)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:47:31','journal_entry','posted',8,'2025-05-27 10:47:31',2,'goods'),(75,'2025-03-28','RCEF-20250527104612','Purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 08:48:33','journal_entry','posted',6,'2025-05-27 10:48:33',2,'goods'),(76,'2025-03-22','RCEF-20250527104731','muvura(1688)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:48:50','journal_entry','posted',8,'2025-05-27 10:48:50',2,'goods'),(77,'2025-03-22','RCEF-20250527104850','glass,muse,polyster,cheville(3134)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:50:37','journal_entry','posted',8,'2025-05-27 10:50:37',2,'goods'),(78,'2025-03-24','RCEF-20250527105038','accessories(3144)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:52:07','journal_entry','posted',8,'2025-05-27 10:52:07',2,'goods'),(79,'2025-03-24','RCEF-20250527105208','amapata(3143)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:54:19','journal_entry','posted',8,'2025-05-27 10:54:19',2,'goods'),(80,'2025-03-20','RCEF-20250527104833','Purchase of construction supplies ( invoice No 3223)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 08:54:26','journal_entry','posted',6,'2025-05-27 10:54:26',2,'goods'),(81,'2025-03-24','RCEF-20250527105420','glass(3442)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:55:37','journal_entry','posted',8,'2025-05-27 10:55:37',2,'goods'),(82,'2025-03-31','RCEF-20250527105426','Purchase of construction supplies ( invoice No 18374)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 08:56:42','journal_entry','posted',6,'2025-05-27 10:56:42',2,'goods'),(83,'2025-03-24','RCEF-20250527105538','kugura ibikoresho(437)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:56:51','journal_entry','posted',8,'2025-05-27 10:56:51',2,'goods'),(84,'2025-03-25','RCEF-20250527105652','spensa(859)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:57:46','journal_entry','posted',8,'2025-05-27 10:57:46',2,'goods'),(85,'2025-02-14','RCEF-20250527105746','blackwood screw(864)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 08:58:54','journal_entry','posted',8,'2025-05-27 10:58:54',2,'goods'),(86,'2025-01-07','RCEF-20250527105854','glass wool(3166)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 09:00:49','journal_entry','posted',8,'2025-05-27 11:00:49',2,'goods'),(87,'2025-01-27','RCEF-20250527105642','purchase of construction material ( Invoice No 29552)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:01:20','journal_entry','posted',6,'2025-05-27 11:01:20',2,''),(88,'2025-01-17','RCEF-20250527110049','electricity bugesera(001)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 09:03:43','journal_entry','posted',8,'2025-05-27 11:03:43',2,'service'),(89,'2025-02-07','RCEF-20250527111114','purchase of construction supplies ( invoice No 2291)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:13:34','journal_entry','posted',6,'2025-05-27 11:13:34',2,'goods'),(90,'2025-01-13','RCEF-20250527103630','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:13:37','journal_entry','posted',6,'2025-05-27 11:13:37',2,'goods'),(91,'2025-01-28','RCEF-20250527110953','gupakurura (001)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 09:16:20','journal_entry','posted',8,'2025-05-27 11:16:20',2,''),(92,'2025-01-17','RCEF-20250527111504','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:18:51','journal_entry','posted',6,'2025-05-27 11:18:51',2,''),(93,'2025-01-28','RCEF-20250527111621','transport(001)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 09:18:52','journal_entry','posted',8,'2025-05-27 11:18:52',2,''),(94,'2025-01-29','RCEF-20250527111852','abakoze ahashyizwe ama tiyeau ya gaz()',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 09:22:55','journal_entry','posted',8,'2025-05-27 11:22:55',2,''),(95,'2025-01-30','RCEF-20250527112256','electricity bugesera(003)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 09:25:14','journal_entry','posted',8,'2025-05-27 11:25:14',2,''),(96,'2025-01-20','RCEF-20250527111852','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:26:49','journal_entry','posted',6,'2025-05-27 11:26:49',2,'goods'),(97,'2025-02-05','RCEF-20250527113307','Gushingura Uwayisaba Jeannette',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:34:52','journal_entry','posted',6,'2025-05-27 11:34:52',2,''),(98,'2025-01-28','RCEF-20250527114921','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:52:22','journal_entry','posted',6,'2025-05-27 11:52:22',2,''),(99,'2025-01-20','RCEF-20250527115046',' Lawyer on Sound proof complaints',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:52:32','journal_entry','posted',6,'2025-05-27 11:52:32',2,'service'),(100,'2025-01-31','RCEF-20250527115223','purchase of office consumables',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:57:22','journal_entry','posted',6,'2025-05-27 11:57:22',2,''),(101,'2025-02-07','RCEF-20250527115232','Transport Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:58:48','journal_entry','posted',6,'2025-05-27 11:58:48',2,''),(102,'2025-01-17','RCEF-20250527115723','buying meal ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 09:59:25','journal_entry','posted',6,'2025-05-27 11:59:25',2,'service'),(103,'2025-02-04','RCEF-20250527115925','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:02:18','journal_entry','posted',6,'2025-05-27 12:02:18',2,''),(104,'2025-01-30','RCEF-20250527114644','security guards payments',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:04:05','journal_entry','posted',8,'2025-05-27 12:04:05',2,''),(105,'2025-02-07','RCEF-20250527120218','purchase of office supplies',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:05:28','journal_entry','posted',6,'2025-05-27 12:05:28',2,''),(106,'2025-01-30','RCEF-20250527120405','gakwandi munezero eric payment',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:06:22','journal_entry','posted',8,'2025-05-27 12:06:22',2,''),(107,'2025-01-30','RCEF-20250527120622','andre munyemana',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:07:29','journal_entry','posted',8,'2025-05-27 12:07:29',2,''),(108,'2025-02-04','RCEF-20250527120729','jean bosco kimenyi',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:08:27','journal_entry','posted',8,'2025-05-27 12:08:27',2,''),(109,'2025-03-22','RCEF-20250527115848',' Payment Valentin',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:08:37','journal_entry','posted',6,'2025-05-27 12:08:37',2,'service'),(110,'2025-02-04','RCEF-20250527120920','electricity bugesera',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:10:30','journal_entry','posted',8,'2025-05-27 12:10:30',2,''),(111,'2025-02-05','RCEF-20250527121031','transport to bugera',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:13:23','journal_entry','posted',8,'2025-05-27 12:13:23',2,''),(112,'2025-02-11','RCEF-20250527121323','Harerimana gregoire(001)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:15:28','journal_entry','posted',8,'2025-05-27 12:15:28',2,''),(113,'2025-01-08','RCEF-20250527120837','Transport y Ibikoresho ( Munyemana Andree)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:17:20','journal_entry','posted',6,'2025-05-27 12:17:20',2,''),(114,'2025-02-11','RCEF-20250527121528','gucukura imyobo ya gaz',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:19:19','journal_entry','posted',8,'2025-05-27 12:19:19',2,''),(115,'2025-02-11','RCEF-20250527121919','donath mahoro/kujyana ibikoresho(002)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:21:51','journal_entry','posted',8,'2025-05-27 12:21:51',2,''),(116,'2025-02-14','RCEF-20250527122151','ismael ntawiha/verbal contract',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:23:33','journal_entry','posted',8,'2025-05-27 12:23:34',2,''),(117,'2025-01-08','RCEF-20250527121720','Transport y Ibikoresho ( Jean Bosco Kimenyi)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:25:16','journal_entry','posted',6,'2025-05-27 12:25:16',2,'service'),(118,'2025-03-28','RCEF-20250527123218','Transport Ntaganda Pierre ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:33:58','journal_entry','posted',6,'2025-05-27 12:33:58',2,'service'),(119,'2025-02-07','RCEF-20250527123358','transport Ntirenganya Jean Pierre',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:35:13','journal_entry','posted',6,'2025-05-27 12:35:13',2,'service'),(120,'2025-02-14','RCEF-20250527122849','manirakiza ismael',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 10:35:57','journal_entry','posted',8,'2025-05-27 12:35:57',2,''),(121,'2025-03-28','RCEF-20250527123513','Kubaka Sound proof ( invoice No 00001197)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:46:29','journal_entry','posted',6,'2025-05-27 12:46:30',2,'service'),(122,'2025-01-08','RCEF-20250527124630','transport of materials ( Jean Bosco Kimenyi )',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:51:45','journal_entry','posted',6,'2025-05-27 12:51:45',2,'service'),(123,'2025-03-15','RCEF-20250527125145','rent Ibikwa ( Karemera)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:54:58','journal_entry','posted',6,'2025-05-27 12:54:58',2,'service'),(124,'2025-04-01','RCEF-20250527125458','Transport for signature WCR',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:57:29','journal_entry','posted',6,'2025-05-27 12:57:29',2,'service'),(125,'2025-04-01','RCEF-20250527125729','Sound proof construction Supervision',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 10:59:14','journal_entry','posted',6,'2025-05-27 12:59:14',2,'service'),(126,'2025-02-14','RCEF-20250527123557','manirakiza ismael',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 11:53:31','journal_entry','posted',8,'2025-05-27 13:53:31',2,''),(127,'2025-01-03','RCEF-20250527015259',' School fees payment Q 2 2024-2025',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 11:54:42','journal_entry','posted',6,'2025-05-27 13:54:42',2,''),(128,'2025-02-21','RCEF-20250527015332','after meeting transport ',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:04:12','journal_entry','posted',8,'2025-05-27 14:04:12',2,''),(129,'2025-01-03','RCEF-20250527015442','Transport to Town ( Gloria)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:06:44','journal_entry','posted',6,'2025-05-27 14:06:44',2,''),(130,'2025-01-03','RCEF-20250527020644','Transport to Bugesera/ Jean Paul',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:07:58','journal_entry','posted',6,'2025-05-27 14:07:58',2,''),(131,'2025-01-03','RCEF-20250527020758','Transport for overtime ( RCEF Staff)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:09:51','journal_entry','posted',6,'2025-05-27 14:09:51',2,''),(132,'2025-02-21','RCEF-20250527020412','ismael ntawiha ()',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:12:00','journal_entry','posted',8,'2025-05-27 14:12:00',2,''),(133,'2025-01-08','RCEF-20250527020951','Transport Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:13:29','journal_entry','posted',6,'2025-05-27 14:13:29',2,''),(134,'2025-03-27','RCEF-20250527021329','Transport ( Peace)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:14:52','journal_entry','posted',6,'2025-05-27 14:14:52',2,'service'),(135,'2025-02-19','RCEF-20250527021452','Maintenance of  Office equipment ( akabati)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:16:43','journal_entry','posted',6,'2025-05-27 14:16:43',2,'service'),(136,'2025-02-24','RCEF-20250527021201','juvenal hanyura imfura()',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:19:12','journal_entry','posted',8,'2025-05-27 14:19:12',2,''),(137,'2025-02-07','RCEF-20250527120528','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:21:45','journal_entry','posted',6,'2025-05-27 14:21:45',2,''),(138,'2025-02-10','RCEF-20250527021643','Transport / jean paul',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:24:44','journal_entry','posted',6,'2025-05-27 14:24:44',2,'service'),(139,'2025-01-11','RCEF-20250527022146','buying office supplies ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:29:18','journal_entry','posted',6,'2025-05-27 14:29:18',2,''),(140,'2025-02-24','RCEF-20250527021913','manirakiza ismael',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:30:21','journal_entry','posted',8,'2025-05-27 14:30:21',2,''),(141,'2025-02-25','RCEF-20250527023021','karemera appolinare',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:31:44','journal_entry','posted',8,'2025-05-27 14:31:45',2,''),(142,'2025-02-26','RCEF-20250527023145','munyemana andre',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:33:12','journal_entry','posted',8,'2025-05-27 14:33:12',2,''),(143,'2025-02-28','RCEF-20250527023313','munyemana andre',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:34:14','journal_entry','posted',8,'2025-05-27 14:34:14',2,''),(144,'2025-02-28','RCEF-20250527023415','umuhire jeannette',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:35:59','journal_entry','posted',8,'2025-05-27 14:35:59',2,''),(145,'2025-02-11','RCEF-20250527022919','computer repairing',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:44:26','journal_entry','posted',6,'2025-05-27 14:44:26',2,''),(146,'2025-01-10','RCEF-20250527022444',' Support to Graduate Jean Paul',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:44:41','journal_entry','posted',6,'2025-05-27 14:44:41',2,''),(147,'2025-02-28','RCEF-20250527023601','umuhire jeannette(245)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:45:59','journal_entry','posted',8,'2025-05-27 14:45:59',2,''),(148,'2025-02-12','RCEF-20250527024426','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:46:48','journal_entry','posted',6,'2025-05-27 14:46:48',2,''),(149,'2025-02-13','RCEF-20250527024649','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:48:24','journal_entry','posted',6,'2025-05-27 14:48:24',2,''),(150,'2025-02-28','RCEF-20250527024600','umuhire jeannette(241)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:48:25','journal_entry','posted',8,'2025-05-27 14:48:25',2,''),(151,'2025-02-18','RCEF-20250527024825','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:49:42','journal_entry','posted',6,'2025-05-27 14:49:42',2,''),(152,'2025-02-13','RCEF-20250527024441','Transport to Bugesera/ Jean Paul',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:49:59','journal_entry','posted',6,'2025-05-27 14:49:59',2,'service'),(153,'2025-02-19','RCEF-20250527024942','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:51:45','journal_entry','posted',6,'2025-05-27 14:51:45',2,''),(154,'2025-01-10','RCEF-20250527024959','Tax Payment of December 2024',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:58:46','journal_entry','posted',6,'2025-05-27 14:58:46',2,''),(155,'2025-02-19','RCEF-20250527025145','repairing office door',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 12:59:01','journal_entry','posted',6,'2025-05-27 14:59:01',2,''),(156,'2025-03-03','RCEF-20250527024826','twilingiyimana jean bosco(1178)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 12:59:11','journal_entry','posted',8,'2025-05-27 14:59:11',2,''),(157,'2025-01-14','RCEF-20250527025846','Transport to Bank',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:00:29','journal_entry','posted',6,'2025-05-27 15:00:29',2,''),(158,'2025-02-21','RCEF-20250527025901','buying water',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:01:11','journal_entry','posted',6,'2025-05-27 15:01:11',2,''),(159,'2025-03-03','RCEF-20250527025911','juvenal hanyurwimfura(247)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 13:02:14','journal_entry','posted',8,'2025-05-27 15:02:14',2,''),(160,'2025-03-05','RCEF-20250527030029','Transport RCEF Staff',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:02:44','journal_entry','posted',6,'2025-05-27 15:02:44',2,'service'),(161,'2025-03-03','RCEF-20250527030215','juvenal hanurwimfura(248)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-27 13:03:14','journal_entry','posted',8,'2025-05-27 15:03:14',2,''),(162,'2025-02-24','RCEF-20250527030111','buying drinks and food',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:03:34','journal_entry','posted',6,'2025-05-27 15:03:34',2,''),(163,'2025-02-24','RCEF-20250527031002','buying water office (invoice Nº 425)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:16:49','journal_entry','posted',6,'2025-05-27 15:16:49',2,''),(164,'2025-01-17','RCEF-20250527031241','Vehicle Maintenance (RAF 641 )',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:18:49','journal_entry','posted',6,'2025-05-27 15:18:49',2,''),(165,'2025-02-24','RCEF-20250527031649','purchase of fuel invoice Nº 13214',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:19:20','journal_entry','posted',6,'2025-05-27 15:19:20',2,''),(166,'2025-02-25','RCEF-20250527031920','buying drinks and food',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:30:33','journal_entry','posted',6,'2025-05-27 15:30:33',2,''),(167,'2025-03-28','RCEF-20250527033725','Transport to Bank',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:38:29','journal_entry','posted',6,'2025-05-27 15:38:29',2,'service'),(168,'2025-01-17','RCEF-20250527033829','Transport to Bank/Jeanne',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:43:42','journal_entry','posted',6,'2025-05-27 15:43:42',2,''),(169,'2025-01-17','RCEF-20250527034342','Water for Bugesera site',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:45:17','journal_entry','posted',6,'2025-05-27 15:45:17',2,''),(170,'2025-02-07','RCEF-20250527034518','Car maintenance',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:46:53','journal_entry','posted',6,'2025-05-27 15:46:53',2,'service'),(171,'2025-02-25','RCEF-20250527033033','buying water office (invoice Nº 2286)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:47:03','journal_entry','posted',6,'2025-05-27 15:47:03',2,''),(172,'2025-01-20','RCEF-20250527034804','Internet fees ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:49:16','journal_entry','posted',6,'2025-05-27 15:49:16',2,''),(173,'2025-02-26','RCEF-20250527034703','transport ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:50:46','journal_entry','posted',6,'2025-05-27 15:50:46',2,''),(174,'2025-02-27','RCEF-20250527035046','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:53:06','journal_entry','posted',6,'2025-05-27 15:53:06',2,''),(175,'2025-01-21','RCEF-20250527034916','Purchase of  office water',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 13:56:03','journal_entry','posted',6,'2025-05-27 15:56:03',2,''),(176,'2025-02-27','RCEF-20250527035306','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:02:10','journal_entry','posted',6,'2025-05-27 16:02:10',2,''),(177,'2025-02-27','RCEF-20250527040210','buying meal',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:04:12','journal_entry','posted',6,'2025-05-27 16:04:12',2,''),(178,'2025-03-06','RCEF-20250527035603','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:08:45','journal_entry','posted',6,'2025-05-27 16:08:45',2,'service'),(179,'2025-02-19','RCEF-20250527040412','buying car spare parts invoice Nº 447',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:08:57','journal_entry','posted',6,'2025-05-27 16:08:57',2,'goods'),(180,'2025-03-27','RCEF-20250527040845','Transport to bank/ Bugesera',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:12:16','journal_entry','posted',6,'2025-05-27 16:12:16',2,'service'),(181,'2025-03-03','RCEF-20250527040857','buying drinks and food',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:12:53','journal_entry','posted',6,'2025-05-27 16:12:53',2,''),(182,'2025-03-27','RCEF-20250527041217','Transport Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:15:16','journal_entry','posted',6,'2025-05-27 16:15:16',2,'service'),(183,'2025-03-05','RCEF-20250527041253','buying office supplies',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:16:58','journal_entry','posted',6,'2025-05-27 16:16:58',2,''),(184,'2025-01-27','RCEF-20250527041516','Payment  of security services',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:19:02','journal_entry','posted',6,'2025-05-27 16:19:02',2,''),(185,'2025-03-06','RCEF-20250527041658','buying office consumables invoice Nº 7632',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:19:50','journal_entry','posted',6,'2025-05-27 16:19:50',2,''),(186,'2025-03-27','RCEF-20250527041902','Cleaning services',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:20:51','journal_entry','posted',6,'2025-05-27 16:20:51',2,'service'),(187,'2025-03-27','RCEF-20250527042051','Transport of staff Jean paul/Gloria and Jeanne',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:28:15','journal_entry','posted',6,'2025-05-27 16:28:15',2,'service'),(188,'2025-03-27','RCEF-20250527042815','Water in Meeting',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:29:03','journal_entry','posted',6,'2025-05-27 16:29:03',2,'service'),(189,'2025-01-28','RCEF-20250527042903','Transport after the meeting',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:30:25','journal_entry','posted',6,'2025-05-27 16:30:25',2,''),(190,'2025-03-06','RCEF-20250527041950','office cleaning fees',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:30:34','journal_entry','posted',6,'2025-05-27 16:30:34',2,''),(191,'2025-03-07','RCEF-20250527043035','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:34:03','journal_entry','posted',6,'2025-05-27 16:34:03',2,''),(192,'2025-03-27','RCEF-20250527043025','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:36:37','journal_entry','posted',6,'2025-05-27 16:36:38',2,'service'),(193,'2025-03-11','RCEF-20250527043403','purchase of fuel invoice Nº 437790',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:37:42','journal_entry','posted',6,'2025-05-27 16:37:42',2,''),(194,'2025-02-28','RCEF-20250527043638','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:39:28','journal_entry','posted',6,'2025-05-27 16:39:28',2,'service'),(195,'2025-01-28','RCEF-20250527043928','Transport/ Karemera',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:41:31','journal_entry','posted',6,'2025-05-27 16:41:31',2,''),(196,'2025-03-17','RCEF-20250527043742','payment the bill of wasac bugesera',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:42:18','journal_entry','posted',6,'2025-05-27 16:42:18',2,'service'),(197,'2025-02-28','RCEF-20250527044131','Security payment',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:46:17','journal_entry','posted',6,'2025-05-27 16:46:17',2,'service'),(198,'2025-03-20','RCEF-20250527044218','buying drinks ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:48:14','journal_entry','posted',6,'2025-05-27 16:48:14',2,'goods'),(199,'2025-03-01','RCEF-20250527044618','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:49:54','journal_entry','posted',6,'2025-05-27 16:49:54',2,'service'),(200,'2025-01-30','RCEF-20250527044954','Salary January 2025 Gloria, Jeanne, and Jean paul',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:52:16','journal_entry','posted',6,'2025-05-27 16:52:16',2,''),(201,'2025-03-13','RCEF-20250527044814','buying office supplies',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:52:33','journal_entry','posted',6,'2025-05-27 16:52:33',2,'goods'),(202,'2025-01-30','RCEF-20250527045332','Transport Jeanne',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:56:17','journal_entry','posted',6,'2025-05-27 16:56:17',2,''),(203,'2025-04-24','RCEF-20250527045617','Office rent',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 14:57:53','journal_entry','posted',6,'2025-05-27 16:57:53',2,'service'),(204,'2025-04-28','RCEF-20250527045753','Transport  Bonaventure',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 15:03:04','journal_entry','posted',6,'2025-05-27 17:03:04',2,'service'),(205,'2025-01-30','RCEF-20250527050304','Transport Juvenal',0.00,'RWF',1.0000,NULL,0,6,'2025-05-27 15:04:16','journal_entry','posted',6,'2025-05-27 17:04:16',2,''),(206,'2025-01-31','RCEF-20250528082052','Cleaning  fees (  MUKANDAYISABA Drocella)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 06:52:14','journal_entry','posted',6,'2025-05-28 08:52:14',2,''),(207,'2025-01-31','RCEF-20250528085215','communication February 2025',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 06:54:58','journal_entry','posted',6,'2025-05-28 08:54:58',2,''),(208,'2025-03-26','RCEF-20250528085458','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 06:59:21','journal_entry','posted',6,'2025-05-28 08:59:21',2,'service'),(209,'2025-01-31','RCEF-20250528085921','Transport  Jean Paul',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:01:23','journal_entry','posted',6,'2025-05-28 09:01:23',2,''),(210,'2025-02-05','RCEF-20250528090123','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:10:57','journal_entry','posted',6,'2025-05-28 09:10:57',2,''),(211,'2025-02-28','RCEF-20250528091057','Transport to bank/ Jeanne',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:12:58','journal_entry','posted',6,'2025-05-28 09:12:58',2,''),(212,'2025-02-07','RCEF-20250528091258','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:13:50','journal_entry','posted',6,'2025-05-28 09:13:50',2,''),(213,'2025-02-10','RCEF-20250528091351','Gift to Regis Musanganya',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:15:24','journal_entry','posted',6,'2025-05-28 09:15:24',2,''),(214,'2025-02-11','RCEF-20250528092718','Purchase of airtime',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:28:32','journal_entry','posted',6,'2025-05-28 09:28:32',2,''),(215,'2025-02-20','RCEF-20250528092832','Transport to bank /Jeanne',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:32:01','journal_entry','posted',6,'2025-05-28 09:32:01',2,'service'),(216,'2025-02-13','RCEF-20250528091524','Transport Karemera',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:47:39','journal_entry','posted',6,'2025-05-28 09:47:39',2,''),(217,'2025-03-12','RCEF-20250528093202',' Purchase of internet',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 07:59:28','journal_entry','posted',6,'2025-05-28 09:59:28',2,'service'),(218,'2025-02-13','RCEF-20250528095928','Transport to Kanombe/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:01:19','journal_entry','posted',6,'2025-05-28 10:01:19',2,''),(219,'2025-02-14','RCEF-20250528100119','Tax charges',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:04:03','journal_entry','posted',6,'2025-05-28 10:04:03',2,''),(220,'2025-02-14','RCEF-20250528100448','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:05:45','journal_entry','posted',6,'2025-05-28 10:05:45',2,''),(221,'2025-03-28','RCEF-20250528100546','Transport Ismael',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:11:14','journal_entry','posted',6,'2025-05-28 10:11:14',2,'service'),(222,'2025-03-13','RCEF-20250528101114','Payment taxes deducted',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:19:19','journal_entry','posted',6,'2025-05-28 10:19:19',2,''),(223,'2025-02-14','RCEF-20250528101920',' Electricity for office   feb 2025',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:21:39','journal_entry','posted',6,'2025-05-28 10:21:39',2,''),(224,'2025-02-19','RCEF-20250528102139','Transport to Kanombe / Drocella',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:27:40','journal_entry','posted',6,'2025-05-28 10:27:40',2,''),(225,'2025-02-19','RCEF-20250528102740','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:40:42','journal_entry','posted',6,'2025-05-28 10:40:42',2,''),(226,'2025-02-19','RCEF-20250528104043','Payment of Water invoice',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:46:17','journal_entry','posted',6,'2025-05-28 10:46:17',2,''),(227,'2025-02-19','RCEF-20250528104618','Translation fees ( Eugene)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:48:18','journal_entry','posted',6,'2025-05-28 10:48:18',2,''),(228,'2025-02-20','RCEF-20250528104818','Transport Bihozagara Fidele',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 08:52:36','journal_entry','posted',6,'2025-05-28 10:52:36',2,''),(229,'2025-02-20','RCEF-20250528094739','payment of internet fees',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 09:50:52','journal_entry','posted',6,'2025-05-28 11:50:52',2,''),(230,'2025-02-21','RCEF-20250528115052','Transport to Bank/Jeanne',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 09:58:54','journal_entry','posted',6,'2025-05-28 11:58:54',2,''),(231,'2025-02-21','RCEF-20250528115854','Transport Bonaventure',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:05:58','journal_entry','posted',6,'2025-05-28 12:05:58',2,''),(232,'2025-02-21','RCEF-20250528120558','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:07:44','journal_entry','posted',6,'2025-05-28 12:07:44',2,''),(233,'2025-02-21','RCEF-20250528120744','Transport Bonaventure',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:19:24','journal_entry','posted',6,'2025-05-28 12:19:24',2,''),(234,'2025-02-21','RCEF-20250528121924','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:20:29','journal_entry','posted',6,'2025-05-28 12:20:29',2,''),(235,'2025-02-26','RCEF-20250528122029','payment security sevice',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:22:49','journal_entry','posted',6,'2025-05-28 12:22:49',2,''),(236,'2025-02-24','RCEF-20250528122249','Transport for annual  Declaration',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:24:29','journal_entry','posted',6,'2025-05-28 12:24:29',2,''),(237,'2025-03-03','RCEF-20250528121933','muhire jeannette',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 10:34:08','journal_entry','posted',8,'2025-05-28 12:34:08',2,''),(238,'2025-02-26','RCEF-20250528122429','Payment of Cleaning Companies',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:36:09','journal_entry','posted',6,'2025-05-28 12:36:09',2,''),(239,'2025-02-27','RCEF-20250528123609','Transport Bonaventure',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:43:31','journal_entry','posted',6,'2025-05-28 12:43:31',2,''),(240,'2025-02-27','RCEF-20250528124331','Transport to petro station/ Boneventure',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:44:57','journal_entry','posted',6,'2025-05-28 12:44:57',2,''),(241,'2025-02-28','RCEF-20250528124854','Transport Bonaventure',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:49:45','journal_entry','posted',6,'2025-05-28 12:49:45',2,''),(242,'2025-02-28','RCEF-20250528124946','Office rent',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 10:51:29','journal_entry','posted',6,'2025-05-28 12:51:29',2,''),(243,'2025-03-04','RCEF-20250528123409','munyemana andre(275)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 11:27:06','journal_entry','posted',8,'2025-05-28 13:27:06',2,''),(244,'2025-03-04','RCEF-20250528012707','Ruhinja Francois(1180)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 11:29:00','journal_entry','posted',8,'2025-05-28 13:29:00',2,''),(245,'2025-03-04','RCEF-20250528012900','security guards payments',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 11:31:08','journal_entry','posted',8,'2025-05-28 13:31:08',2,''),(246,'2025-03-05','RCEF-20250528013108','Harerimana gregoire',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 11:33:17','journal_entry','posted',8,'2025-05-28 13:33:17',2,''),(247,'2025-03-06','RCEF-20250528013318','umuhire jeannette(001)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 11:34:30','journal_entry','posted',8,'2025-05-28 13:34:30',2,''),(248,'2025-03-07','RCEF-20250528013431','deogratius edward kamanda',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 11:36:27','journal_entry','posted',8,'2025-05-28 13:36:27',2,''),(249,'2025-03-07','RCEF-20250528013628','Ruhinja Francois',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 11:38:06','journal_entry','posted',8,'2025-05-28 13:38:06',2,''),(250,'2025-02-28','RCEF-20250528125129','Transport Jeanne',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 12:08:22','journal_entry','posted',6,'2025-05-28 14:08:22',2,''),(251,'2025-03-07','RCEF-20250528021129','rwanda revenue authority',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 12:14:37','journal_entry','posted',8,'2025-05-28 14:14:37',2,''),(252,'2025-03-11','RCEF-20250528021438','rwanda revenue authority',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 12:15:48','journal_entry','posted',8,'2025-05-28 14:15:48',2,''),(253,'2025-03-11','RCEF-20250528021549','rwanda revenue authority',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 12:16:55','journal_entry','posted',8,'2025-05-28 14:16:55',2,''),(254,'2025-03-12','RCEF-20250528021656','rwanda revenue authority',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 12:18:37','journal_entry','posted',8,'2025-05-28 14:18:37',2,'service'),(255,'2025-02-28','RCEF-20250528020822',' Staff Salaries February 2025',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 12:30:34','journal_entry','posted',6,'2025-05-28 14:30:34',2,''),(256,'2025-03-03','RCEF-20250528023733','Transport Juvenal',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 12:45:18','journal_entry','posted',6,'2025-05-28 14:45:18',2,''),(257,'2025-04-28','RCEF-20250528024533','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 12:46:40','journal_entry','posted',6,'2025-05-28 14:46:40',2,'service'),(258,'2025-03-03','RCEF-20250528024700','Translation fees ( Eugene)',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 12:48:06','journal_entry','posted',6,'2025-05-28 14:48:06',2,''),(259,'2025-03-04','RCEF-20250528024807','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 12:55:12','journal_entry','posted',6,'2025-05-28 14:55:12',2,''),(260,'2025-03-04','RCEF-20250528025512','Transport Munyemana',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:03:36','journal_entry','posted',6,'2025-05-28 15:03:36',2,''),(261,'2025-03-11','RCEF-20250528021837','rwanda revenue authority',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 13:05:14','journal_entry','posted',8,'2025-05-28 15:05:14',2,''),(262,'2025-03-04','RCEF-20250528030336','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:05:54','journal_entry','posted',6,'2025-05-28 15:05:54',2,''),(263,'2025-03-04','RCEF-20250528030554','Electricity march 2025',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:10:46','journal_entry','posted',6,'2025-05-28 15:10:46',2,''),(264,'2025-03-12','RCEF-20250528030514','munyemana andre',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 13:11:49','journal_entry','posted',8,'2025-05-28 15:11:49',2,'service'),(265,'2025-03-14','RCEF-20250528031149','Paymet offloading of materials',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 13:14:51','journal_entry','posted',8,'2025-05-28 15:14:51',2,'service'),(266,'2025-03-14','RCEF-20250528031452','eric gakwandi',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 13:17:18','journal_entry','posted',8,'2025-05-28 15:17:18',2,'service'),(267,'2025-03-04','RCEF-20250528031046','PAyment Jean pierre ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:17:54','journal_entry','posted',6,'2025-05-28 15:17:54',2,''),(268,'2025-03-06','RCEF-20250528031754','Kwishyura uwakoze   ibigega',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:19:11','journal_entry','posted',6,'2025-05-28 15:19:11',2,''),(269,'2025-03-14','RCEF-20250528031719','jean pierre ntirenganya',0.00,'RWF',1.0000,NULL,0,8,'2025-05-28 13:22:49','journal_entry','posted',8,'2025-05-28 15:22:49',2,'service'),(270,'2025-03-06','RCEF-20250528031912','Transport cartouche',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:27:44','journal_entry','posted',6,'2025-05-28 15:27:44',2,''),(271,'2025-03-06','RCEF-20250528032744','Notification of documents',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:28:55','journal_entry','posted',6,'2025-05-28 15:28:55',2,''),(272,'2025-05-28','RCEF-20250528033636','Salaries payment',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:36:36','journal_entry','draft',NULL,NULL,NULL,''),(273,'2025-05-28','RCEF-20250528033801','Salaries payment',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:38:01','journal_entry','posted',6,'2025-05-28 15:38:01',2,''),(274,'2025-03-10','RCEF-20250528033910','Transport Uwizeyimana theoneste',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:44:52','journal_entry','posted',6,'2025-05-28 15:44:52',2,''),(275,'2025-03-10','RCEF-20250528034452','Transport Bonaventure',0.00,'RWF',1.0000,NULL,0,6,'2025-05-28 13:46:54','journal_entry','posted',6,'2025-05-28 15:46:54',2,''),(276,'2025-03-14','RCEF-20250528032249','jean pierre ntirenganya',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:17:27','journal_entry','posted',8,'2025-05-29 10:17:27',2,'service'),(277,'2025-03-14','RCEF-20250529101729','karemera appolinare',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:18:40','journal_entry','posted',8,'2025-05-29 10:18:40',2,'service'),(278,'2025-03-16','RCEF-20250529101841','irakoze cynthia',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:20:01','journal_entry','posted',8,'2025-05-29 10:20:01',2,'service'),(279,'2025-03-17','RCEF-20250529102002','furere uzise jean claude',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:21:20','journal_entry','posted',8,'2025-05-29 10:21:20',2,'service'),(280,'2025-03-17','RCEF-20250529102122','furere uzise jean claude',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:22:54','journal_entry','posted',8,'2025-05-29 10:22:54',2,'service'),(281,'2025-03-17','RCEF-20250529102255','hakizimana cyprien',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:24:42','journal_entry','posted',8,'2025-05-29 10:24:42',2,'service'),(282,'2025-03-17','RCEF-20250529102443','Purchase of electricity for Bugesera site',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:25:59','journal_entry','posted',8,'2025-05-29 10:25:59',2,'service'),(283,'2025-04-01','RCEF-20250529102600','irakoze cynthia',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:27:17','journal_entry','posted',8,'2025-05-29 10:27:17',2,'service'),(284,'2025-03-18','RCEF-20250529102718','gakwandi munezero eric ',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:37:00','journal_entry','posted',8,'2025-05-29 10:37:00',2,'service'),(285,'2025-03-16','RCEF-20250529103700','Ruhinja Francois',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:38:52','journal_entry','posted',8,'2025-05-29 10:38:52',2,'service'),(286,'2025-03-17','RCEF-20250529103852','pivot yard logistics',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:40:51','journal_entry','posted',8,'2025-05-29 10:40:51',2,'service'),(287,'2025-03-20','RCEF-20250529104051','Purchase of electricity for Bugesera site',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:42:09','journal_entry','posted',8,'2025-05-29 10:42:09',2,'service'),(288,'2025-03-22','RCEF-20250529104209','andre munyemana',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:43:42','journal_entry','posted',8,'2025-05-29 10:43:42',2,'service'),(289,'2025-03-22','RCEF-20250529104343','valentin dusengimana(gukora soundproof)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 08:45:16','journal_entry','posted',8,'2025-05-29 10:45:16',2,'service'),(290,'2025-03-13','RCEF-20250529111211',' Purchase of communication March 2025',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:21:29','journal_entry','posted',6,'2025-05-29 11:21:29',2,'service'),(291,'2025-03-14','RCEF-20250529112130','Transport to Bank',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:24:17','journal_entry','posted',6,'2025-05-29 11:24:17',2,'service'),(292,'2025-03-14','RCEF-20250529112417','Purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:25:23','journal_entry','posted',6,'2025-05-29 11:25:23',2,'goods'),(293,'2025-03-14','RCEF-20250529112524','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:28:56','journal_entry','posted',6,'2025-05-29 11:28:56',2,'service'),(294,'2025-03-14','RCEF-20250529112857','Transport  ntaganga, Jean Pierre and Eric ',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:30:47','journal_entry','posted',6,'2025-05-29 11:30:47',2,'service'),(295,'2025-03-18','RCEF-20250529113247','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:40:35','journal_entry','posted',6,'2025-05-29 11:40:35',2,'goods'),(296,'2025-03-20','RCEF-20250529114036','purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:42:58','journal_entry','posted',6,'2025-05-29 11:42:58',2,'goods'),(297,'2025-03-22','RCEF-20250529104517','valentin dusengimana(gukora soundproof)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 09:48:09','journal_entry','posted',8,'2025-05-29 11:48:09',2,'service'),(298,'2025-03-22','RCEF-20250529114259','buying meal',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:49:17','journal_entry','posted',6,'2025-05-29 11:49:17',2,'service'),(299,'2025-03-24','RCEF-20250529114810','umuhire jeannette',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 09:49:29','journal_entry','posted',8,'2025-05-29 11:49:29',2,'service'),(300,'2025-03-24','RCEF-20250529114917','purchasing construction materials',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:52:14','journal_entry','posted',6,'2025-05-29 11:52:14',2,'goods'),(301,'2025-03-14','RCEF-20250529115411','Payment of taxes Feb',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:55:45','journal_entry','posted',6,'2025-05-29 11:55:45',2,''),(302,'2025-03-17','RCEF-20250529115545',' support Kwivuza to a church member',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 09:57:19','journal_entry','posted',6,'2025-05-29 11:57:19',2,'service'),(303,'2025-03-17','RCEF-20250529115719','Transport to Bank/Jeanne',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 10:01:14','journal_entry','posted',6,'2025-05-29 12:01:14',2,'service'),(304,'2025-03-24','RCEF-20250529114930','munyemana andre',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 10:09:43','journal_entry','posted',8,'2025-05-29 12:09:43',2,'service'),(305,'2025-03-25','RCEF-20250529120944','umuhire jeannette',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 10:10:46','journal_entry','posted',8,'2025-05-29 12:10:46',2,'service'),(306,'2025-04-01','RCEF-20250529122823',' water for the meeting in Bugesera',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 10:31:16','journal_entry','posted',6,'2025-05-29 12:31:16',2,'goods'),(307,'2025-03-25','RCEF-20250529121046','umuhire jeannette',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 10:53:05','journal_entry','posted',8,'2025-05-29 12:53:05',2,'service'),(308,'2025-02-28','RCEF-20250529125306','andre munyemana',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 10:55:09','journal_entry','posted',8,'2025-05-29 12:55:09',2,'service'),(309,'2025-03-28','RCEF-20250529125510','eks ltd',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 10:59:13','journal_entry','posted',8,'2025-05-29 12:59:13',2,'service'),(310,'2025-03-28','RCEF-20250529125913','one family constuction ltd',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 11:01:38','journal_entry','posted',8,'2025-05-29 13:01:38',2,'service'),(311,'2025-04-01','RCEF-20250529123116',' purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 11:09:10','journal_entry','posted',6,'2025-05-29 13:09:10',2,'goods'),(312,'2025-04-02','RCEF-20250529010910','Purchase of fuel',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 11:09:51','journal_entry','posted',6,'2025-05-29 13:09:51',2,'goods'),(313,'2025-03-28','RCEF-20250529010139','niyonkuru desire',0.00,'RWF',1.0000,NULL,0,8,'2025-05-29 11:11:24','journal_entry','posted',8,'2025-05-29 13:11:24',2,'service'),(314,'2025-04-02','RCEF-20250529010951','Guhomesha ipine',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 11:19:06','journal_entry','posted',6,'2025-05-29 13:19:06',2,'service'),(315,'2025-04-02','RCEF-20250529012158','Guhemba abimuye Stock',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 11:22:56','journal_entry','posted',6,'2025-05-29 13:22:56',2,'service'),(316,'2025-04-03','RCEF-20250529120114','Gukora  Handle',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 11:27:03','journal_entry','posted',6,'2025-05-29 13:27:03',2,'service'),(317,'2025-04-03','RCEF-20250529012703','Transport Munyemana',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 11:38:25','journal_entry','posted',6,'2025-05-29 13:38:25',2,'service'),(318,'2025-05-29','RCEF-20250529014222','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,6,'2025-05-29 11:42:22','journal_entry','posted',6,'2025-05-29 13:42:22',2,''),(319,'2025-03-31','RCEF-20250530082126','jean bosco kimenyi',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 06:55:47','journal_entry','posted',8,'2025-05-30 08:55:47',2,'goods'),(320,'2025-04-01','RCEF-20250530085548','umuhire jeannette',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 06:59:16','journal_entry','posted',8,'2025-05-30 08:59:16',2,''),(321,'2025-04-01','RCEF-20250530085917','kayiranga emmanuel',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:01:54','journal_entry','posted',8,'2025-05-30 09:01:54',2,'service'),(322,'2025-04-01','RCEF-20250530090155','abazamu ',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:12:54','journal_entry','posted',8,'2025-05-30 09:12:54',2,''),(323,'2025-04-02','RCEF-20250530091255','umuhire jeannette',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:14:02','journal_entry','posted',8,'2025-05-30 09:14:02',2,'goods'),(324,'2025-04-03','RCEF-20250530091403','kaneza leonie',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:15:17','journal_entry','posted',8,'2025-05-30 09:15:17',2,''),(325,'2025-03-22','RCEF-20250530091518','playwoods tz',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:17:31','journal_entry','posted',8,'2025-05-30 09:17:31',2,'goods'),(326,'2025-03-22','RCEF-20250530091732','nails',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:18:53','journal_entry','posted',8,'2025-05-30 09:18:53',2,'goods'),(327,'2025-03-22','RCEF-20250530091854','pinus',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:20:33','journal_entry','posted',8,'2025-05-30 09:20:33',2,'goods'),(328,'2025-03-22','RCEF-20250530092034','muvura',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:22:25','journal_entry','posted',8,'2025-05-30 09:22:25',2,'goods'),(329,'2025-04-11','RCEF-20250530092226','umucanga',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:28:21','journal_entry','posted',8,'2025-05-30 09:28:22',2,'goods'),(330,'2025-04-30','RCEF-20250530092822','siphon de sol',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:29:40','journal_entry','posted',8,'2025-05-30 09:29:40',2,'goods'),(331,'2025-04-01','RCEF-20250530092941','electricity',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:31:05','journal_entry','posted',8,'2025-05-30 09:31:05',2,'service'),(332,'2025-04-01','RCEF-20250530093106','drinks',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:33:18','journal_entry','posted',8,'2025-05-30 09:33:18',2,'goods'),(333,'2025-04-01','RCEF-20250530093319','imicanga(hoho)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 07:35:48','journal_entry','posted',8,'2025-05-30 09:35:48',2,'goods'),(334,'2025-04-02','RCEF-20250530093549','umuhire jeannette(kwimura stoke)',0.00,'RWF',1.0000,NULL,0,8,'2025-05-30 08:18:52','journal_entry','posted',8,'2025-05-30 10:18:52',2,'service'),(335,'2025-04-17','RCEF-20250602104156','furere uzise jean claude',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 08:53:08','journal_entry','posted',8,'2025-06-02 10:53:08',2,'service'),(336,'2025-04-17','RCEF-20250602105310','Antoine  rubumba',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 08:54:24','journal_entry','posted',8,'2025-06-02 10:54:24',2,'service'),(337,'2025-04-17','RCEF-20250602105425','niyitanga jean baptiste',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 08:55:33','journal_entry','posted',8,'2025-06-02 10:55:33',2,'goods'),(338,'2025-04-17','RCEF-20250602105534','gusimbuza ibirahure/church/furere uzise payment',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 08:57:37','journal_entry','posted',8,'2025-06-02 10:57:37',2,'service'),(339,'2025-04-17','RCEF-20250602105738','jonas',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 08:58:33','journal_entry','posted',8,'2025-06-02 10:58:33',2,'service'),(340,'2025-04-03','RCEF-20250602104654','Transport to Bank/ Bonaventure',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 08:58:34','journal_entry','posted',6,'2025-06-02 10:58:34',2,'service'),(341,'2025-04-17','RCEF-20250602105834','karemera appolinare',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 08:59:36','journal_entry','posted',8,'2025-06-02 10:59:36',2,'service'),(342,'2025-04-03','RCEF-20250602105834','purchase of ICT Equipments',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 08:59:53','journal_entry','posted',6,'2025-06-02 10:59:53',2,'goods'),(343,'2025-04-17','RCEF-20250602105937','jean pierre ntirenganya',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:00:26','journal_entry','posted',8,'2025-06-02 11:00:26',2,'service'),(344,'2025-04-17','RCEF-20250602110028','gakwandi munezero eric ',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:01:18','journal_entry','posted',8,'2025-06-02 11:01:18',2,'service'),(345,'2025-04-17','RCEF-20250602110119','edmond nkurikiyimana',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:02:27','journal_entry','posted',8,'2025-06-02 11:02:27',2,'service'),(346,'2025-04-17','RCEF-20250602110228','gahizi gloria',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:03:18','journal_entry','posted',8,'2025-06-02 11:03:18',2,'service'),(347,'2025-04-17','RCEF-20250602110319','asita munezero',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:04:13','journal_entry','posted',8,'2025-06-02 11:04:13',2,'service'),(348,'2025-04-17','RCEF-20250602110414','jeanne uwimana',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:05:13','journal_entry','posted',8,'2025-06-02 11:05:13',2,'service'),(349,'2025-04-03','RCEF-20250602110537','Transport Edmond and steven',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:06:29','journal_entry','posted',6,'2025-06-02 11:06:29',2,'service'),(350,'2025-04-17','RCEF-20250602110514','zirimwabagabo bonaventure',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:06:34','journal_entry','posted',8,'2025-06-02 11:06:34',2,'service'),(351,'2025-04-03','RCEF-20250602110629','craft gifts',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:07:35','journal_entry','posted',6,'2025-06-02 11:07:35',2,'goods'),(352,'2025-04-17','RCEF-20250602110635','printing presentations documents',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:07:55','journal_entry','posted',8,'2025-06-02 11:07:55',2,'service'),(353,'2025-04-17','RCEF-20250602110756','electricity/office',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:09:02','journal_entry','posted',8,'2025-06-02 11:09:02',2,'service'),(354,'2025-04-24','RCEF-20250602110915','GUKORA UMURIRO IBUGESERA',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:10:34','journal_entry','posted',8,'2025-06-02 11:10:34',2,'service'),(355,'2025-04-25','RCEF-20250602111035','CARTOUCHE',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:11:31','journal_entry','posted',8,'2025-06-02 11:11:31',2,'goods'),(356,'2025-04-25','RCEF-20250602111131','TRANSPORT/CARTOUCHE',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:12:57','journal_entry','posted',8,'2025-06-02 11:12:57',2,'service'),(357,'2025-04-25','RCEF-20250602111257','VOUCHERS',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:14:21','journal_entry','posted',8,'2025-06-02 11:14:21',2,'goods'),(358,'2025-04-25','RCEF-20250602111422','TRANSPORT/VOUCHERS',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:15:42','journal_entry','posted',8,'2025-06-02 11:15:42',2,'service'),(359,'2025-04-29','RCEF-20250602111543','SIGNING CHEQUES',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:16:55','journal_entry','posted',8,'2025-06-02 11:16:55',2,'service'),(360,'2025-04-03','RCEF-20250602110735','gukoesha ibikwa',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:25:34','journal_entry','posted',6,'2025-06-02 11:25:34',2,'service'),(361,'2025-04-04','RCEF-20250602112534','purchase of Gloceries ( invoice No 314)',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:27:12','journal_entry','posted',6,'2025-06-02 11:27:12',2,'goods'),(362,'2025-04-04','RCEF-20250602112712','fuel /eric',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:28:04','journal_entry','posted',6,'2025-06-02 11:28:04',2,'goods'),(363,'2025-04-04','RCEF-20250602112804','Transport to bank/ Gloria',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:33:27','journal_entry','posted',6,'2025-06-02 11:33:27',2,'service'),(364,'2025-04-04','RCEF-20250602113327','transport josue',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:36:00','journal_entry','posted',6,'2025-06-02 11:36:00',2,'service'),(365,'2025-04-04','RCEF-20250602113601','transport steven',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:37:13','journal_entry','posted',6,'2025-06-02 11:37:13',2,'service'),(366,'2025-04-04','RCEF-20250602113713','Water for Bugesera site',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:38:14','journal_entry','posted',6,'2025-06-02 11:38:14',2,'goods'),(367,'2025-04-08','RCEF-20250602113814','fuel /BONAVENTURE',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:46:50','journal_entry','posted',6,'2025-06-02 11:46:50',2,'goods'),(368,'2025-04-10','RCEF-20250602111655','CLEANING MATERIALS/CHURCH',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:47:46','journal_entry','posted',8,'2025-06-02 11:47:46',2,'goods'),(369,'2025-04-10','RCEF-20250602114748','NOTARY/PRINTING WCR DOCUMENTS',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:51:31','journal_entry','posted',8,'2025-06-02 11:51:31',2,'service'),(370,'2025-04-10','RCEF-20250602115131','PAYING TAXES+CHARGES',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:52:44','journal_entry','posted',8,'2025-06-02 11:52:44',2,''),(371,'2025-04-08','RCEF-20250602114904','Transport Uwizeyimana theoneste',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:54:05','journal_entry','posted',6,'2025-06-02 11:54:05',2,'service'),(372,'2025-04-08','RCEF-20250602115405','repair stamp',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:55:40','journal_entry','posted',6,'2025-06-02 11:55:40',2,'service'),(373,'2025-04-11','RCEF-20250602115245','PAPER TOWEL',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:57:02','journal_entry','posted',8,'2025-06-02 11:57:02',2,'goods'),(374,'2025-04-11','RCEF-20250602115703','FUEL',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:58:03','journal_entry','posted',8,'2025-06-02 11:58:03',2,'goods'),(375,'2025-04-08','RCEF-20250602115540','kugura amazi/ office',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 09:59:35','journal_entry','posted',6,'2025-06-02 11:59:35',2,'goods'),(376,'2025-04-11','RCEF-20250602115804','RUBAMBANA JOSUE',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 09:59:49','journal_entry','posted',8,'2025-06-02 11:59:49',2,'service'),(377,'2025-04-09','RCEF-20250602115935','printing WCR Document',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 10:05:27','journal_entry','posted',6,'2025-06-02 12:05:27',2,'service'),(378,'2025-04-09','RCEF-20250602120527','transport  Muyemana',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 10:06:33','journal_entry','posted',6,'2025-06-02 12:06:33',2,'service'),(379,'2025-04-10','RCEF-20250602120633','payment of sound proof',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 10:08:38','journal_entry','posted',6,'2025-06-02 12:08:38',2,'service'),(380,'2025-04-14','RCEF-20250602115950','kugura ibikoresho/CHURCH',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 10:08:38','journal_entry','posted',8,'2025-06-02 12:08:38',2,'goods'),(381,'2025-04-14','RCEF-20250602120840','PRINTING AND PHOTOCOPIES  OF WCR DOCUMENTS',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 10:12:06','journal_entry','posted',8,'2025-06-02 12:12:06',2,'service'),(382,'2025-04-09','RCEF-20250602121737','kugura amazi/ office',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 10:18:48','journal_entry','posted',6,'2025-06-02 12:18:48',2,'goods'),(383,'2025-04-14','RCEF-20250602121744','RENTING OF CHAIRS',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 10:19:10','journal_entry','posted',8,'2025-06-02 12:19:10',2,'service'),(384,'2025-04-14','RCEF-20250602121911','UKINIWABO JEAN MARIE VIANNEY/COFFE',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 10:21:55','journal_entry','posted',8,'2025-06-02 12:21:55',2,'service'),(385,'2025-04-14','RCEF-20250602122156','FANTA/KIDS MEETING',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 10:23:04','journal_entry','posted',8,'2025-06-02 12:23:04',2,'goods'),(386,'2025-04-09','RCEF-20250602121848','transport after meeting ( Josue,Ismael,Karemera, Edmond, Asita, Eric, Gloria)',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 10:24:59','journal_entry','posted',6,'2025-06-02 12:24:59',2,'service'),(387,'2025-04-10','RCEF-20250602122459','Purchase of  construction Material ',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 10:31:11','journal_entry','posted',6,'2025-06-02 12:31:11',2,'goods'),(388,'2025-04-10','RCEF-20250602123111','purchase of Gloceries ',0.00,'RWF',1.0000,NULL,0,6,'2025-06-02 10:33:07','journal_entry','posted',6,'2025-06-02 12:33:07',2,'goods'),(389,'2025-04-14','RCEF-20250602122305','GUHEMBA ABASIZE AMARANGI/POLYCLINIC',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 10:40:29','journal_entry','posted',8,'2025-06-02 12:40:29',2,'service'),(390,'2025-02-11','RCEF-20250602124031','munyemana andre',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 10:42:25','journal_entry','posted',8,'2025-06-02 12:42:25',2,'service'),(391,'2025-04-15','RCEF-20250602124226','PAPER CLIP AND LREAM OF PAPER',0.00,'RWF',1.0000,NULL,0,8,'2025-06-02 10:45:04','journal_entry','posted',8,'2025-06-02 12:45:04',2,'goods'),(392,'2025-04-15','RCEF-20250602124505','BONBOM FOR KIDS  ',0.00,'RWF',1.0000,NULL,0,8,'2025-06-03 08:44:43','journal_entry','posted',8,'2025-06-03 10:44:43',2,'goods'),(393,'2025-04-15','RCEF-20250603104444','purchasing of food for beneficiaries',0.00,'RWF',1.0000,NULL,0,8,'2025-06-03 08:46:34','journal_entry','posted',8,'2025-06-03 10:46:34',2,'goods'),(394,'2025-04-16','RCEF-20250603104635','transport for leonie',0.00,'RWF',1.0000,NULL,0,8,'2025-06-03 08:47:40','journal_entry','posted',8,'2025-06-03 10:47:40',2,'service'),(395,'2025-04-16','RCEF-20250603104741','imiti/jeannine',0.00,'RWF',1.0000,NULL,0,8,'2025-06-03 08:53:25','journal_entry','posted',8,'2025-06-03 10:53:25',2,'goods'),(396,'2025-04-16','RCEF-20250603105326','additional amount for purchasing food for beneficiaries',0.00,'RWF',1.0000,NULL,0,8,'2025-06-03 08:57:31','journal_entry','posted',8,'2025-06-03 10:57:31',2,'goods'),(397,'2025-06-03','RCEF-20250603115953','Donation',0.00,'RWF',1.0000,NULL,0,10,'2025-06-03 10:00:09','journal_entry','posted',10,'2025-06-03 12:00:09',2,'');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` varchar(200) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (6,'nkuredi@yahoo.fr','$2y$10$0W/uLLaaHNEz6HM0MIYUT.JNN8d8sQ4Qc.jiQj8CPf6pas2o7/FTi','nkuredi@yahoo.fr',NULL,'user','2025-06-09 10:35:12','2025-04-14 19:40:46',1),(7,'jeanneuwimana06@gmail.com','$2y$10$s/CTkTw9PmtTtVMt7n9MkeGw1pAV3xDWA1oxpdrkW6E8xrRVmcfgS','jeanneuwimana06@gmail.com',NULL,'user','2025-04-30 07:57:33','2025-04-14 19:42:06',1),(8,'glorigah12@gmail.com','$2y$10$G3uLBsuogdotroceAnBbI.JdUw3DGqwafctnQTbOntdQ7LUjzCsVq','glorigah12@gmail.com',NULL,'user','2025-06-05 10:13:07','2025-04-14 19:43:17',1),(9,'rcefrw2013@gmail.com','$2y$10$cWXn8goTQ8iHUGEUNY77n.vcA.H234Y/Eh.PWXbfM30yBZNdDeT3u','rcefrw2013@gmail.com',NULL,'user',NULL,'2025-04-14 19:44:54',1),(10,'panatech@gmail.com','$2y$10$CETqkGBBOgCW/.nfNZ5mTechSd9baquYcJARZJ8VR22ND.L9NuX1i','panatech@gmail.com',NULL,'user','2025-06-03 11:03:10','2025-04-14 22:06:16',1),(11,'pascal@panatechrwanda.com','$2y$10$uRFRK4MqmKgngK5PEj4A8OwPg/B7fjkbjIBJNZVLpTpHvAtlr1Lwe','pascal@panatechrwanda.com',NULL,'user',NULL,'2025-05-06 19:44:39',1),(12,'asitamunezero1994@gmail.com','$2y$10$bh6XvuTrSyU.h8s.Z7CDUOnNACdTxo42/lb1ekERQrXPy9AxH2u7q','asitamunezero1994@gmail.com',NULL,'user','2025-05-26 11:12:17','2025-05-06 19:47:58',1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `inventory_movement_report`
--

/*!50001 DROP VIEW IF EXISTS `inventory_movement_report`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `inventory_movement_report` AS select `m`.`id` AS `id`,`m`.`date_time` AS `date_time`,`i`.`id` AS `item_id`,`i`.`item_code` AS `item_code`,`i`.`name` AS `name`,`m`.`movement_type` AS `movement_type`,case when `m`.`movement_type` = 'purchase' then 'Purchase' when `m`.`movement_type` = 'sale' then 'Sale' when `m`.`movement_type` = 'adjustment' then 'Adjustment' when `m`.`movement_type` = 'transfer' then 'Transfer' when `m`.`movement_type` = 'return' then 'Return' when `m`.`movement_type` = 'beginning_balance' then 'Beginning Balance' end AS `movement_type_name`,`m`.`quantity` AS `quantity`,`i`.`unit_of_measure` AS `unit_of_measure`,`m`.`cost_price` AS `cost_price`,`m`.`selling_price` AS `selling_price`,concat(`u`.`full_name`,' ',`u`.`full_name`) AS `user_name`,`m`.`reference_id` AS `reference_id`,`m`.`reference_type` AS `reference_type`,`m`.`notes` AS `notes`,`c`.`name` AS `category_name`,`s`.`name` AS `supplier_name`,case when `m`.`reference_type` = 'purchase_order' then concat('PO-',`m`.`reference_id`) when `m`.`reference_type` = 'sale' then concat('SALE-',`m`.`reference_id`) when `m`.`reference_type` = 'adjustment' then concat('ADJ-',`m`.`reference_id`) else `m`.`reference_type` end AS `reference_display` from ((((`inventory_movements` `m` join `inventory_items` `i` on(`m`.`item_id` = `i`.`id`)) left join `users` `u` on(`m`.`created_by` = `u`.`id`)) left join `inventory_categories` `c` on(`i`.`category_id` = `c`.`id`)) left join `suppliers` `s` on(`i`.`supplier_id` = `s`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-10  8:59:37
