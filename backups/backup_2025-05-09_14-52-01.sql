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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_balances`
--

LOCK TABLES `account_balances` WRITE;
/*!40000 ALTER TABLE `account_balances` DISABLE KEYS */;
INSERT INTO `account_balances` VALUES (1,6,2,1000.00,100.00),(2,11,2,3000100.00,100.00),(4,13,2,50000.00,1100.00),(5,4,2,81170.82,0.00),(8,5,2,100.00,0.00),(11,1,2,0.00,3150028800.00),(12,47,2,3015000000.00,10230000.00),(14,10,2,5000000.00,0.00),(15,12,2,2000000.00,0.00),(17,48,2,150000000.00,15000000.00),(19,310502,2,0.00,1657776.00),(20,4200202,2,1500000.00,0.00),(22,0,2,3488800.00,60000.00),(26,98,2,1839003.20,0.00),(31,51,2,0.00,3213216.56),(34,64,2,5000.00,0.00),(36,42,2,180000.00,0.00),(46,3,2,265918.54,0.00),(48,7,2,150100000.00,0.00),(49,41,2,0.00,150100000.00),(52,99,2,1100000.00,0.00),(58,95,2,0.00,2000000.00),(59,96,2,0.00,1760000.00),(62,53,2,440000.00,0.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_periods`
--

LOCK TABLES `accounting_periods` WRITE;
/*!40000 ALTER TABLE `accounting_periods` DISABLE KEYS */;
INSERT INTO `accounting_periods` VALUES (2,'FY2025','2025-01-01','2025-12-31',0,NULL,NULL,1,'1','2025-04-08 03:56:15');
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrower`
--

LOCK TABLES `borrower` WRITE;
/*!40000 ALTER TABLE `borrower` DISABLE KEYS */;
INSERT INTO `borrower` VALUES (1,'42001R2','MUSABYIMANA ','Ange Rose','GASABO District','0788','mus@gmail.com','2025-04-23 11:50:54'),(2,'4200102R','UWIMANA ','Clementine','Gasabo','079','ucle@gmail.com','2025-04-23 12:01:25'),(3,'4200202R','pascal','Twizerimana','kigali Rwanda','11100000','pascal@panatechrwanda.com','2025-04-25 18:08:40'),(4,'4200302R','Panatech','ltd','kigali Rwanda','78607470','panatechrwanda@gmail.com','2025-04-30 09:24:02');
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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_of_accounts`
--

LOCK TABLES `chart_of_accounts` WRITE;
/*!40000 ALTER TABLE `chart_of_accounts` DISABLE KEYS */;
INSERT INTO `chart_of_accounts` VALUES (1,'100101P','Donation for Polyclinic','revenue',76,1),(2,'100201P','Consultation fees from Polyclinic','revenue',76,1),(3,'100301P',' Medical acts','revenue',76,1),(4,'100401P','Sales of drug','revenue',76,1),(5,'100501P','Laboratory examinations','revenue',76,1),(6,'100601P','Medical Imaging','revenue',76,1),(7,'100102R','Donation for RCEF','revenue',76,1),(8,'100103W','Gain on Exchange','revenue',76,1),(9,'100203w','Other Revenues from church','revenue',76,1),(10,'200102R','Net Salary  for Staff','expense',83,1),(11,'200202R','Pension Contribution','expense',83,1),(12,'200302R','Maternity Contributions','expense',83,1),(13,'200104R','Mutuel Contributions','expense',83,1),(14,'210102R','Office Supplies ','expense',85,1),(15,'210202R','Office consumable','expense',85,1),(16,'210302R','Rent of Office premises','expense',85,1),(17,'210402R','Electricity','expense',85,1),(18,'210502R','Water','expense',85,1),(19,'210602R','Security services','expense',85,1),(20,'210702R','Cleaning and waste collection','expense',85,1),(21,'210802R','Internet ','expense',85,1),(22,'210902R','communication  fees','expense',85,1),(23,'230102R','National Transport ','expense',86,1),(24,'230202R','Other Transport','expense',86,1),(25,'230302R','Fuel','expense',86,1),(26,'230402R','Rent of vehicle and other transport means','expense',86,1),(27,'230502R','Rent of Machinery','expense',86,1),(28,'230602R',' international transport','expense',86,1),(29,'230702R','Mission fees','expense',86,1),(30,'240102R','Meals ','expense',87,1),(31,'240202R',' Drinks','expense',87,1),(32,'240302R','Accomodation','expense',87,1),(33,'240402R','Other Cost Related to guest reception','expense',87,1),(34,'250102R','Managerial consultancies work','expense',88,1),(35,'250202R','Construction Works','expense',88,1),(36,'250302R','Audit Fees','expense',88,1),(37,'211002R','Bank charges and Financial cost','expense',85,1),(38,'250402R','Translation','expense',88,1),(39,'260102R','Christmas Gift to Children','expense',89,1),(40,'260202R','Gift to others in Kind','expense',89,1),(41,'260302R','Donations and other support','expense',89,1),(42,'270102R','School fees for supported children','expense',91,1),(43,'270202R','CBHI Payment to supported children','expense',91,1),(46,'270302R','Other school related support to children','expense',91,1),(47,'310102R',' Frw 0001432374137  Rwanda Children Educational Foundation','finances',81,1),(48,'310202R','USD 0001432370883 Rwanda Children Educational Foundation','finances',81,1),(49,'310302R','Access Frw  7002190104271501 RCEF','finances',81,1),(50,'310402R','Access Frw 7002460204271503 RCEF Saving Account','finances',81,1),(51,'310502R','SACCO KIMIHURURA Frw 4755 Rwanda  Children Educational Foundation','finances',81,1),(52,'310602R',' Petty Cash for RCEF','finances',82,1),(53,'320102R','Stock in office in Kigali','stock',79,1),(54,'320101P','Stock  for the polyclinic','stock',79,1),(55,'510102R','Purchase of Office Chair','asset',93,1),(56,'510202R','Purchase of Office Tables','asset',93,1),(57,'510302R','Purchase of Computer Lap top and accessories','asset',93,1),(58,'510402R','Purchase of Computer Desktop and accessories','asset',93,1),(59,'510502R','Purchase of Printer','asset',93,1),(60,'510602R','Purchase of Other ICT equipement','asset',93,1),(62,'42001R2','MUSABYIMANA  Ange Rose','receivables',78,1),(63,'280102R','Maintenance of Building','expense',92,1),(64,'4200102R','UWIMANA  Clementine','receivables',78,1),(65,'280202R','Maintenance of Vehicle and spare parts','expense',92,1),(66,'280302R','MAintenance of ICT equipments and Spare parts','expense',92,1),(67,'280402R','Maintenance of Office equipments and spare parts','expense',92,1),(68,'280502R','Other Maintenance and spare parts','expense',92,1),(69,'510702R','Purchase of Other equipment','asset',93,1),(70,'6101','Adjustment on cash balances','finances',80,1),(71,'6102','Adjustment on Receivable balances','receivables',80,1),(72,'6103','Adjustment on payable balances','payables',80,1),(73,'6104','Adjustment on Asset balances','asset',0,1),(74,'6201','Regularization of revenue','revenue',76,1),(75,'6202','Regularization of expense','expense',0,1),(76,'100','REVENUE','revenue',0,1),(78,'42','Account Receivable','receivables',0,1),(79,'32','Stock','stock',0,1),(80,'61','Adjustment on opening Balances','expense',0,1),(81,'310','Bank account','finances',0,1),(82,'311','Petty Cash','finances',0,1),(83,'20','Salaries','expense',0,1),(85,'21','Office expenses','expense',0,1),(86,'23','Transport and Travel','expense',0,1),(87,'24','Meeting and Reception','expense',0,1),(88,'25','Contractual and professional works','expense',0,1),(89,'26','Gift and Donation in Kind','expense',0,1),(90,'260402R','Support to Vulnerable families','expense',89,1),(91,'27','School fees and related support','expense',0,1),(92,'28','Maintenance and spare parts','expense',0,1),(93,'51','Purchase of asset','asset',0,1),(94,'211202R','Exchange Loss','expense',85,1),(95,'41001','Panatech Ltd','liability',0,1),(96,'41002','Test Supplier','liability',0,1),(98,'4200202R','pascal Twizerimana','receivables',0,1),(99,'4200302R','Panatech ltd','receivables',1,1),(100,'41003','Twizerimana Pascal','liability',1,1),(101,'41004','IISS','liability',1,1),(102,'41005','IISSlll','liability',1,1),(103,'41006','NAME','liability',1,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donations`
--

LOCK TABLES `donations` WRITE;
/*!40000 ALTER TABLE `donations` DISABLE KEYS */;
INSERT INTO `donations` VALUES (1,5,155.00,'RWF','2025-04-09','dsf','sdfsfsd',NULL,0,'5',1,NULL),(2,5,10000.00,'RWF','2025-04-09','cash','dcsdfs',NULL,0,'10',1,NULL),(3,7,100000.00,'RWF','2025-04-14','BANK TRANSFER','Test',NULL,0,'RWD545',1,NULL),(4,6,10000.00,'RWF','2025-04-30','BANK TRANSFER','TEST',NULL,0,'RWD5454',1,NULL),(5,5,78000.00,'RWF','2025-04-23','CASH','TEST',NULL,0,'10',1,NULL),(6,9,10000.00,'RWF','2025-04-22','BANK TRANSFER','To build Houses',NULL,1,'RWD5454',7,NULL),(7,6,10000.00,'RWF','2025-04-22','BANK TRANSFER','dsc',NULL,0,'RWD5454',7,NULL),(8,6,150000000.00,'RWF','2025-04-29','BANK TRANSFER','test',NULL,0,'RWD5454',10,NULL),(9,6,150000000.00,'RWF','2025-04-29','BANK TRANSFER','test',NULL,0,'RWD5454',10,NULL),(10,6,150000000.00,'RWF','2025-04-29','BANK TRANSFER','',NULL,0,'RWD5454',10,NULL),(11,12,50000.00,'RWF','2025-06-06','BANK TRANSFER','hjg',NULL,1,'RWD5454',10,NULL),(12,12,50000.00,'RWF','2025-05-08','BANK TRANSFER','fghj',NULL,0,'RWD5454',10,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donors`
--

LOCK TABLES `donors` WRITE;
/*!40000 ALTER TABLE `donors` DISABLE KEYS */;
INSERT INTO `donors` VALUES (5,'Test Company XYZ','individual',1,'protegene@mail.com','0786074570','1277','155','2025-04-09 12:35:17'),(6,'Pascal Twizerimana','organization',2,'protegene@mail.com','0786074570','somen','123','2025-04-14 10:17:28'),(7,'Test Donor name','organization',2,'email@mail.com','0786074570','Test','45','2025-04-14 10:19:28'),(8,'Somen','individual',1,'SMA@gmail.com','','ASD','55','2025-04-14 10:20:15'),(9,'Ufitumukiza Faustin','individual',1,'panatechrwanda@gmail.com','78607470','kigali Rwanda','USA','2025-04-22 17:18:56'),(10,'Panatech Company ltd','organization',2,'panatechrwanda@gmail.com','78607470','kigali','jm','2025-05-08 10:16:55'),(11,'Panatech Company ltd','organization',1,'panatechrwanda@gmail.com','78607470','Somen','jm','2025-05-08 10:17:12'),(12,'ghjkl','organization',2,'panatechrwanda@gmail.com','78607470','hgjk','jm','2025-05-08 10:18:43'),(13,'Panatech Company ltd','organization',1,'panatechrwanda@gmail.com','78607470','kigali Rwanda','fgyhujkl','2025-05-08 10:19:01'),(14,'Panatech Company ltd','organization',2,'panatechrwanda@gmail.com','78607470','fgyhujkl','fgyhujkl','2025-05-08 10:21:01'),(15,'Somen','organization',2,'panatechrwanda@gmail.com','78607470','sdvc','fgyhujkl','2025-05-08 10:24:43'),(16,'Somen','individual',0,'panatechrwanda@gmail.com','78607470','test','fgyhujkl','2025-05-08 10:25:40'),(17,'Somen','individual',0,'panatechrwanda@gmail.com','78607470','sdvc','fgyhujkl','2025-05-08 10:26:46');
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
) ENGINE=InnoDB AUTO_INCREMENT=752 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_permissions`
--

LOCK TABLES `employee_permissions` WRITE;
/*!40000 ALTER TABLE `employee_permissions` DISABLE KEYS */;
INSERT INTO `employee_permissions` VALUES (184,21,35),(185,21,36),(186,21,37),(187,21,38),(188,21,39),(712,25,1),(713,25,2),(714,25,3),(715,25,42),(716,25,24),(717,25,25),(718,25,26),(719,25,27),(720,25,40),(721,25,41),(722,25,44),(723,25,20),(724,25,22),(725,25,23),(726,25,4),(727,25,5),(728,25,6),(729,25,7),(730,25,8),(731,25,9),(732,25,10),(733,25,11),(734,25,12),(735,25,13),(736,25,14),(737,25,15),(738,25,16),(739,25,19),(740,25,35),(741,25,36),(742,25,37),(743,25,38),(744,25,39),(745,25,28),(746,25,29),(747,25,30),(748,25,31),(749,25,33),(750,25,34),(751,25,47);
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
) ENGINE=InnoDB AUTO_INCREMENT=533 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_roles`
--

LOCK TABLES `employee_roles` WRITE;
/*!40000 ALTER TABLE `employee_roles` DISABLE KEYS */;
INSERT INTO `employee_roles` VALUES (1,12,3),(2,12,1),(3,12,8),(4,12,7),(5,12,2),(6,12,4),(7,12,5),(8,12,6),(23,23,3),(24,23,8),(25,23,7),(26,23,4),(27,24,3),(28,24,1),(29,24,8),(30,24,7),(31,24,2),(32,24,4),(33,24,5),(34,24,6),(43,22,3),(44,22,1),(45,22,8),(46,22,7),(47,22,2),(48,22,4),(49,22,5),(50,22,6),(122,7,3),(123,7,1),(124,7,8),(125,7,7),(126,7,2),(127,7,4),(128,7,5),(129,7,6),(130,20,3),(172,28,1),(173,31,8),(326,21,3),(327,21,8),(328,21,7),(329,21,2),(330,21,4),(331,21,5),(332,21,6),(525,25,3),(526,25,1),(527,25,8),(528,25,7),(529,25,2),(530,25,4),(531,25,5),(532,25,6);
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (7,'Panatech Company ltd','panatechrwanda@gmail.com','78607470','2025-04-13',250000.00,1,'2025-04-14 03:24:00'),(12,'pascal Twizerimana','pascal@panatechrwanda.com','11100000','2025-04-13',250000.00,1,'2025-04-14 03:31:18'),(20,'gthg','po@gmail.com','78607470','2025-04-14',5000.00,1,'2025-04-14 12:39:32'),(21,'NKURIKIYIMANA Edmond','nkuredi@yahoo.fr','0788359272','2025-04-14',728541.00,1,'2025-04-14 12:40:46'),(22,'UWIMANA Jeanne','jeanneuwimana06@gmail.com','0786584968','2025-05-01',383046.00,1,'2025-04-14 12:42:06'),(23,'GAHIZI Gloria','glorigah12@gmail.com','078387832','2025-05-06',320251.00,1,'2025-04-14 12:43:17'),(24,'RCEF','rcefrw2013@gmail.com','0787893208','2025-04-14',0.00,1,'2025-04-14 12:44:54'),(25,'Admin Testing account','panatech@gmail.com','0786074570','2025-05-01',500000.00,1,'2025-04-14 15:06:16'),(28,'Panatech Company ltd','sss@gmail.com','78607470','2025-05-06',250000.00,1,'2025-05-06 12:38:18'),(31,'ewrewr','sses@gmail.com','78607470','2025-05-06',250000.00,1,'2025-05-06 12:46:53');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixed_assets`
--

LOCK TABLES `fixed_assets` WRITE;
/*!40000 ALTER TABLE `fixed_assets` DISABLE KEYS */;
INSERT INTO `fixed_assets` VALUES (1,'HP - 494','Electronic','2025-04-02',430000.00,5,'reducing_balance',0.00,'2025-04-25 12:18:39');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internal_requisition_items`
--

LOCK TABLES `internal_requisition_items` WRITE;
/*!40000 ALTER TABLE `internal_requisition_items` DISABLE KEYS */;
INSERT INTO `internal_requisition_items` VALUES (1,1,2,1.00,'pieses','','pending',0.00),(2,1,1,1.00,'','','pending',0.00),(3,2,2,52.00,'pieses','tesr','pending',0.00),(4,3,2,100.00,'pieses',' distribution to  Children','pending',0.00),(5,4,2,10.00,'pieses','','pending',0.00),(6,5,2,10.00,'pieses','10','pending',0.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internal_requisitions`
--

LOCK TABLES `internal_requisitions` WRITE;
/*!40000 ALTER TABLE `internal_requisitions` DISABLE KEYS */;
INSERT INTO `internal_requisitions` VALUES (1,'REQ-20250409-0001',1,1,'2025-04-09 03:20:45','2025-04-16','tes','draft',NULL,'2025-04-09 04:09:47'),(2,'REQ-20250409-0002',1,1,'2025-04-09 03:21:35','2025-04-16','tes','draft',NULL,'2025-04-09 04:09:27'),(3,'REQ-20250425-0001',6,2,'2025-04-25 16:01:30','2025-05-02',' Ditribution to children','submitted',NULL,'2025-04-25 16:01:30'),(4,'REQ-20250425-0002',10,2,'2025-04-25 16:32:25','2025-05-02','Test','submitted',NULL,'2025-04-25 16:32:25'),(5,'REQ-20250427-0001',10,2,'2025-04-27 13:53:29','2025-05-04','Test','approved',NULL,'2025-04-27 13:53:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
INSERT INTO `inventory_items` VALUES (1,'Test','0','Test',1,1,'testasdfa','',0.00,0.00,1000.00,1200.00,NULL,1),(2,'001','','Shoes',1,1,'test','pieses',0.00,0.00,1000.00,100.00,NULL,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_repayments`
--

LOCK TABLES `loan_repayments` WRITE;
/*!40000 ALTER TABLE `loan_repayments` DISABLE KEYS */;
INSERT INTO `loan_repayments` VALUES (1,2,'2025-05-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(2,2,'2025-06-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(3,2,'2025-07-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(4,2,'2025-08-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(5,2,'2025-09-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(6,2,'2025-10-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(7,2,'2025-11-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(8,2,'2025-12-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(9,2,'2026-01-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(10,2,'2026-02-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(11,2,'2026-03-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(12,2,'2026-04-25',42803.74,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(13,3,'2025-05-25',131177.22,131177.22,'2025-04-30',NULL,'cash','RCEF-20250430111310','paid',1),(14,3,'2025-06-25',131177.22,131177.22,'2025-04-30',NULL,'cash','RCEF-20250430113118','paid',1),(15,3,'2025-07-25',131177.22,131177.20,'2025-04-30',NULL,'cash','RCEF-20250430113506','partial',0),(16,3,'2025-08-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(17,3,'2025-09-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(18,3,'2025-10-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(19,3,'2025-11-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(20,3,'2025-12-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(21,3,'2026-01-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(22,3,'2026-02-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(23,3,'2026-03-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(24,3,'2026-04-25',131177.22,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(25,4,'2025-05-25',13372.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(26,4,'2025-06-25',13372.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(27,4,'2025-07-25',13372.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(28,4,'2025-08-25',13372.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(29,4,'2025-09-25',13372.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(30,4,'2025-10-25',13372.25,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(31,5,'2025-05-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(32,5,'2025-06-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(33,5,'2025-07-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(34,5,'2025-08-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(35,5,'2025-09-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(36,5,'2025-10-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(37,5,'2025-11-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(38,5,'2025-12-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(39,5,'2026-01-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(40,5,'2026-02-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(41,5,'2026-03-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(42,5,'2026-04-25',5027.12,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(43,7,'2025-05-25',1782.05,1782.05,'2025-04-29',NULL,'bank_transfer','RCEF-20250429105957','paid',1),(44,7,'2025-06-25',1782.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(45,7,'2025-07-25',1782.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(46,7,'2025-08-25',1782.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(47,7,'2025-09-25',1782.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(48,7,'2025-05-25',1782.05,1782.05,'2025-04-30',NULL,'bank_transfer','RCEF-20250430110949','paid',1),(49,7,'2025-06-25',1782.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(50,7,'2025-07-25',1782.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(51,7,'2025-08-25',1782.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(52,7,'2025-09-25',1782.05,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(53,6,'2025-05-25',10146.34,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(54,6,'2025-06-25',10146.34,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(55,6,'2025-07-25',10146.34,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(56,6,'2025-08-25',10146.34,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(57,6,'2025-09-25',10146.34,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(58,6,'2025-10-25',10146.34,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(59,8,'2025-05-25',10125.35,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(60,8,'2025-06-25',10125.35,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(61,8,'2025-07-25',10125.35,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(62,8,'2025-08-25',10125.35,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(63,8,'2025-09-25',10125.35,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(64,9,'2025-05-25',2503.13,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(65,9,'2025-06-25',2503.13,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(66,10,'2025-05-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(67,10,'2025-06-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(68,10,'2025-07-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(69,10,'2025-08-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(70,10,'2025-09-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(71,10,'2025-10-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(72,10,'2025-11-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(73,10,'2025-12-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(74,10,'2026-01-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(75,10,'2026-02-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(76,10,'2026-03-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(77,10,'2026-04-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(78,10,'2026-05-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(79,10,'2026-06-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(80,10,'2026-07-25',12080.16,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(81,11,'2025-05-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(82,11,'2025-06-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(83,11,'2025-07-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(84,11,'2025-08-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(85,11,'2025-09-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(86,11,'2025-10-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(87,11,'2025-11-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(88,11,'2025-12-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(89,11,'2026-01-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(90,11,'2026-03-01',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(91,11,'2026-03-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(92,11,'2026-04-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(93,11,'2026-05-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(94,11,'2026-06-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(95,11,'2026-07-29',40267.18,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(96,12,'2025-05-30',40535.41,40535.41,'2025-04-30',NULL,'cash','RCEF-20250430112619','paid',1),(97,12,'2025-06-30',40535.41,40535.41,'2025-04-30',NULL,'cash','RCEF-20250430112921','paid',1),(98,12,'2025-07-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(99,12,'2025-08-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(100,12,'2025-09-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(101,12,'2025-10-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(102,12,'2025-11-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(103,12,'2025-12-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(104,12,'2026-01-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(105,12,'2026-03-02',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(106,12,'2026-03-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(107,12,'2026-04-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(108,12,'2026-05-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(109,12,'2026-06-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(110,12,'2026-07-30',40535.41,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(111,14,'2025-06-08',165653.65,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(112,14,'2025-07-08',165653.65,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(113,14,'2025-08-08',165653.65,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(114,14,'2025-09-08',165653.65,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(115,14,'2025-10-08',165653.65,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(116,13,'2025-06-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(117,13,'2025-07-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(118,13,'2025-08-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(119,13,'2025-09-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(120,13,'2025-10-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(121,13,'2025-11-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(122,13,'2025-12-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(123,13,'2026-01-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(124,13,'2026-02-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(125,13,'2026-03-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(126,13,'2026-04-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0),(127,13,'2026-05-08',44424.39,0.00,NULL,NULL,NULL,NULL,'unpaid',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

LOCK TABLES `loans` WRITE;
/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
INSERT INTO `loans` VALUES (1,'4200102',500000.00,0.00,6,'2025-04-25','approved','Loan disbursed',6,'2025-04-25 13:07:04'),(2,'1',500000.00,5.00,12,'2025-04-25','approved','Loan Disbursement',6,'2025-04-25 13:29:38'),(3,'3',1500000.00,9.00,12,'2025-04-25','approved','Testing Loan module',10,'2025-04-25 18:13:52'),(4,'3',80000.00,1.00,6,'2025-04-25','approved','Account',10,'2025-04-25 19:25:24'),(5,'2',60000.00,1.00,12,'2025-04-25','approved','test',10,'2025-04-25 19:31:39'),(6,'3',60000.00,5.00,6,'2025-04-25','approved','g',10,'2025-04-25 19:36:02'),(7,'3',8888.00,1.00,5,'2025-04-25','approved','fd',10,'2025-04-25 19:38:13'),(8,'3',50000.00,5.00,5,'2025-04-25','approved','1',10,'2025-04-25 19:42:25'),(9,'2',5000.00,1.00,2,'2025-04-25','approved','test',10,'2025-04-25 19:58:57'),(10,'3',180000.00,1.00,15,'2025-04-25','approved','test',10,'2025-04-25 21:24:30'),(11,'3',600000.00,1.00,15,'2025-04-29','approved','Security',10,'2025-04-29 08:41:46'),(12,'4',600000.00,2.00,15,'2025-04-30','approved','',10,'2025-04-30 09:24:28'),(13,'4',500000.00,12.00,12,'2025-05-08','approved','Testing',10,'2025-05-08 11:06:06'),(14,'3',800050.00,14.00,5,'2025-05-08','approved','Test',10,'2025-05-08 11:07:10');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_types`
--

LOCK TABLES `payment_types` WRITE;
/*!40000 ALTER TABLE `payment_types` DISABLE KEYS */;
INSERT INTO `payment_types` VALUES (1,'Cash',1,0),(2,'M-Pesa',0,0),(3,'Credit Card',0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrolls`
--

LOCK TABLES `payrolls` WRITE;
/*!40000 ALTER TABLE `payrolls` DISABLE KEYS */;
INSERT INTO `payrolls` VALUES (1,7,250000.00,30000.00,15000.00,18750.00,750.00,750.00,35250.00,15000.00,4400.00,18750.00,750.00,750.00,39650.00,214750.00,'2025-01','2025-04-22 16:03:35'),(2,13,150000.00,0.00,9000.00,11250.00,450.00,450.00,21150.00,9000.00,3000.00,11250.00,450.00,450.00,24150.00,128850.00,'2025-01','2025-04-22 16:07:14'),(3,21,721456.00,0.00,43287.36,54109.20,2164.37,2164.37,101725.30,43287.36,14429.12,54109.20,2164.37,2164.37,116154.42,619730.70,'2025-04','2025-04-24 07:17:46'),(4,23,285000.00,0.00,17100.00,85500.00,855.00,855.00,104310.00,17100.00,5700.00,21375.00,855.00,855.00,45885.00,180690.00,'2025-04','2025-04-25 13:45:42'),(5,23,300000.00,1000.00,NULL,54000.00,900.00,900.00,55800.00,18000.00,5980.00,22500.00,900.00,900.00,48280.00,244200.00,'2025-03','2025-04-29 08:26:54'),(6,25,20000.00,0.00,NULL,0.00,60.00,60.00,120.00,1200.00,400.00,1500.00,60.00,60.00,3220.00,19880.00,'2025-04','2025-04-29 08:34:49'),(7,20,500000.00,1000.00,NULL,114000.00,1500.00,1500.00,117000.00,30000.00,9980.00,37500.00,1500.00,1500.00,80480.00,383000.00,'2025-12','2025-04-29 08:38:46'),(8,12,500000.00,1000.00,NULL,114000.00,1500.00,1500.00,117000.00,30000.00,9980.00,37500.00,1500.00,1500.00,80480.00,383000.00,'2025-11','2025-04-29 08:40:03'),(9,7,200000.00,0.00,12000.00,24000.00,600.00,600.00,37200.00,12000.00,4000.00,0.00,600.00,600.00,17200.00,162800.00,'2025-12','2025-04-29 12:16:38'),(10,23,500000.00,0.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,10000.00,0.00,1500.00,1500.00,43000.00,353000.00,'2025-11','2025-04-29 12:18:24'),(11,22,500000.00,0.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,10000.00,0.00,1500.00,1500.00,43000.00,353000.00,'2025-11','2025-04-29 12:22:50'),(12,25,500000.00,0.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,10000.00,0.00,1500.00,1500.00,43000.00,353000.00,'2025-12','2025-04-29 12:30:08'),(13,25,300000.00,10.00,18000.00,54000.00,900.00,900.00,73800.00,18000.00,5999.80,0.00,900.00,900.00,25799.80,226200.00,'2025-12','2025-04-29 12:32:43'),(14,23,500000.00,0.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,10000.00,0.00,1500.00,1500.00,43000.00,353000.00,'2025-08','2025-04-29 12:36:43'),(15,23,500000.00,0.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,10000.00,0.00,1500.00,1500.00,43000.00,353000.00,'2025-08','2025-04-29 12:39:29'),(16,23,500000.00,0.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,10000.00,0.00,1500.00,1500.00,43000.00,353000.00,'2025-08','2025-04-29 12:41:38'),(17,7,500000.00,1.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,9999.98,0.00,1500.00,1500.00,42999.98,353000.00,'2025-10','2025-04-29 12:49:39'),(18,7,500000.00,0.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,10000.00,0.00,1500.00,1500.00,43000.00,353000.00,'2025-10','2025-04-29 12:50:57'),(19,7,500000.00,0.00,30000.00,114000.00,1500.00,1500.00,147000.00,30000.00,10000.00,0.00,1500.00,1500.00,43000.00,353000.00,'2025-10','2025-04-29 12:54:26'),(20,12,300000.00,0.00,18000.00,54000.00,900.00,0.00,72900.00,24000.00,6000.00,0.00,900.00,0.00,30900.00,227100.00,'2025-05','2025-05-01 20:52:56');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_activities`
--

LOCK TABLES `project_activities` WRITE;
/*!40000 ALTER TABLE `project_activities` DISABLE KEYS */;
INSERT INTO `project_activities` VALUES (1,2,'Build Platform',150000.00,140000.00),(2,2,'Domain Name',12000.00,15000.00),(3,1,'Employee salary ',1500000.00,1500000.00),(4,3,'Test Activity 1 ',200000.00,150000.00),(5,3,'Test Activity 2',300000.00,250000.00),(6,4,'Test Activity 1',20000.00,15000.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'Panatech Company ltd','asrsad',1000000.00,0.00,'2025-04-22 13:42:31'),(2,'Iduka Platform','Ecomerce Platform',1000000.00,0.00,'2025-04-22 13:44:41'),(3,'Test Project','Test Project to test formulars',1000000.00,1000000.00,'2025-04-22 14:30:51'),(4,'Test Project','test',100000.00,50000.00,'2025-04-23 07:27:53');
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
INSERT INTO `purchase_order_items` VALUES (13,3,2,15.00,''),(14,3,1,8.00,''),(15,4,1,5000.00,''),(19,7,2,1000.00,''),(20,8,2,5000.00,''),(21,9,2,15.00,'5500'),(22,10,2,444.00,'2000'),(23,11,2,144.00,'200'),(24,12,2,500.00,'4000'),(25,13,2,44.00,'10000'),(26,14,2,4555.00,'120'),(27,15,5,20.00,'150000');
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
INSERT INTO `purchase_orders` VALUES (3,1,'2025-04-28','1','Testing','received','2025-04-28 15:14:12',''),(4,3,'2025-04-28','10','Test','received','2025-04-28 21:47:20',''),(7,1,'2025-04-29','10','Testing','received','2025-04-29 00:09:02',''),(8,1,'2025-04-29','10','test','received','2025-04-29 09:33:03','uploads/delivery_notes/1745925096_sale_summary _ CSPK.pdf'),(9,1,'2025-04-29','10','Test','received','2025-04-29 10:39:53',''),(10,1,'2025-04-30','10','sdc','received','2025-04-30 14:07:24',''),(11,1,'2025-04-30','10','ads','received','2025-04-30 14:12:44',''),(12,1,'2025-04-30','10','dxsd','received','2025-04-30 14:14:26',''),(13,3,'2025-04-30','10','ds','received','2025-04-30 14:15:30',''),(14,4,'2025-05-06','10','rr','approved','2025-05-06 10:35:56',''),(15,7,'2025-05-09','10','Test','draft','2025-05-09 10:08:04','');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requisition_items`
--

LOCK TABLES `requisition_items` WRITE;
/*!40000 ALTER TABLE `requisition_items` DISABLE KEYS */;
INSERT INTO `requisition_items` VALUES (3,2,2,4.00),(6,3,1,552.00),(7,3,2,444.00),(9,4,2,10.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requisitions`
--

LOCK TABLES `requisitions` WRITE;
/*!40000 ALTER TABLE `requisitions` DISABLE KEYS */;
INSERT INTO `requisitions` VALUES (2,NULL,NULL,'tet','draft','2025-04-28 23:25:59'),(3,NULL,NULL,'For testing','approved','2025-04-28 23:27:00'),(4,NULL,NULL,'For testing','approved','2025-05-09 10:28:19');
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
  `id` int(11) NOT NULL,
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
INSERT INTO `sponsors` VALUES (2,'Pascal Twizerimana','protegene@mail.com','0786074570','ss','2025-04-14 21:53:25'),(3,'TEST','protegene@mail.com','0786074570','Test','2025-04-14 22:01:44'),(4,'pascalss','panatechrwandsa@gmail.com','78607470','asdasc','2025-04-21 21:22:52');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_items`
--

LOCK TABLES `stock_items` WRITE;
/*!40000 ALTER TABLE `stock_items` DISABLE KEYS */;
INSERT INTO `stock_items` VALUES (1,'Shoes','Test','Item','2025-04-28 12:33:10'),(2,'Paper','Paper','Package','2025-04-28 13:34:15'),(3,'Sugar','Desc','KG','2025-05-08 09:32:00'),(4,'Computers','455','Pieces','2025-05-08 09:34:23'),(5,'Laptop','455','Pieces','2025-05-08 09:36:30'),(6,'Phone','455','Pieces','2025-05-08 09:47:40');
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,'2025-04-28',0.00,552.00,'requisition',3,'Requisition Approved','2025-04-28 23:27:49'),(2,2,'2025-04-28',0.00,444.00,'requisition',3,'Requisition Approved','2025-04-28 23:27:50'),(3,1,'2025-04-29',8.00,0.00,'',3,'PO Receiving','2025-04-29 00:04:37'),(4,2,'2025-04-29',15.00,0.00,'',3,'PO Receiving','2025-04-29 00:04:37'),(5,1,'2025-04-29',8.00,0.00,'',3,'PO Receiving','2025-04-29 00:06:08'),(6,2,'2025-04-29',15.00,0.00,'',3,'PO Receiving','2025-04-29 00:06:08'),(7,1,'2025-04-29',8.00,0.00,'',3,'PO Receiving','2025-04-29 00:07:57'),(8,2,'2025-04-29',15.00,0.00,'',3,'PO Receiving','2025-04-29 00:07:57'),(9,1,'2025-04-29',5000.00,0.00,'',4,'PO Receiving','2025-04-29 00:08:18'),(10,2,'2025-04-29',1000.00,0.00,'',7,'PO Receiving','2025-04-29 00:10:19'),(11,2,'2025-04-29',5000.00,0.00,'',8,'PO Receiving','2025-04-29 11:11:36'),(12,2,'2025-04-30',15.00,0.00,'',9,'PO Receiving','2025-04-30 13:59:19'),(13,2,'2025-04-30',15.00,0.00,'',9,'PO Receiving','2025-04-30 14:02:19'),(14,2,'2025-04-30',15.00,0.00,'',9,'PO Receiving','2025-04-30 14:04:32'),(15,2,'2025-04-30',15.00,0.00,'',9,'PO Receiving','2025-04-30 14:06:10'),(16,2,'2025-04-30',444.00,0.00,'',10,'PO Receiving','2025-04-30 14:07:45'),(17,2,'2025-04-30',444.00,0.00,'',10,'PO Receiving','2025-04-30 14:10:01'),(18,2,'2025-04-30',444.00,0.00,'',10,'PO Receiving','2025-04-30 14:10:33'),(19,2,'2025-04-30',444.00,0.00,'',10,'PO Receiving','2025-04-30 14:11:15'),(20,2,'2025-04-30',144.00,0.00,'',11,'PO Receiving','2025-04-30 14:13:05'),(21,2,'2025-04-30',500.00,0.00,'',12,'PO Receiving','2025-04-30 14:14:44'),(22,2,'2025-04-30',44.00,0.00,'',13,'PO Receiving','2025-04-30 14:15:56'),(23,2,'2025-04-30',44.00,0.00,'',13,'PO Receiving','2025-04-30 14:16:29'),(24,2,'2025-04-30',44.00,0.00,'',13,'PO Receiving','2025-04-30 14:17:05'),(25,2,'2025-04-30',44.00,0.00,'',13,'PO Receiving','2025-04-30 14:17:59'),(26,2,'2025-05-09',0.00,10.00,'requisition',4,'Requisition Approved','2025-05-09 10:33:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_documents`
--

LOCK TABLES `student_documents` WRITE;
/*!40000 ALTER TABLE `student_documents` DISABLE KEYS */;
INSERT INTO `student_documents` VALUES (1,23,'1745316517_index.php','','application/octet-stream','2025-04-22 10:08:37'),(2,24,'1745316574_resto (22).sql','','application/octet-stream','2025-04-22 10:09:34'),(4,26,'1745322392_resto (24).sql','','application/octet-stream','2025-04-22 11:46:32'),(5,27,'1745322449_New Order _ CSPKh.pdf','','application/pdf','2025-04-22 11:47:29'),(6,28,'1745322521_sample-1.pdf','','application/pdf','2025-04-22 11:48:41'),(9,1,'Test','','application/octet-stream','2025-05-06 14:11:27'),(10,1,'test','','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','2025-05-06 14:14:16'),(11,1,'Rwanda Sport Company','','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','2025-05-06 14:16:44'),(12,1,'report','','application/octet-stream','2025-05-06 14:19:29'),(13,1,'681a1b178178d.sql','','application/octet-stream','2025-05-06 14:22:15'),(14,1,'Panatech Company ltd','','application/octet-stream','2025-05-06 14:26:23'),(15,1,'pascal Twizerimana','681a1d561106f.sql','application/octet-stream','2025-05-06 14:31:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_payments`
--

LOCK TABLES `student_payments` WRITE;
/*!40000 ALTER TABLE `student_payments` DISABLE KEYS */;
INSERT INTO `student_payments` VALUES (1,1,2,'2025-04-25 15:08:43'),(2,1,2,'2025-04-25 16:39:08'),(3,2,2,'2025-04-25 16:39:09'),(10,1,2,'2025-04-27 13:45:17'),(11,2,2,'2025-04-27 13:45:17'),(12,1,2,'2025-04-29 01:13:30');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_report`
--

LOCK TABLES `student_report` WRITE;
/*!40000 ALTER TABLE `student_report` DISABLE KEYS */;
INSERT INTO `student_report` VALUES (1,1,2,'80','2025-05-06 13:30:11'),(2,3,2,'500','2025-05-08 11:31:19'),(3,3,2,'5000','2025-05-08 11:33:52');
/*!40000 ALTER TABLE `student_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_sponsor`
--

DROP TABLE IF EXISTS `student_sponsor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_sponsor` (
  `id` int(11) NOT NULL,
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
INSERT INTO `student_sponsor` VALUES (3,8,4,'2025-04-21 21:40:29'),(4,9,4,'2025-04-21 21:41:14'),(5,10,4,'2025-04-21 21:44:08'),(6,11,4,'2025-04-21 21:46:19'),(7,12,4,'2025-04-21 21:46:27'),(8,13,4,'2025-04-21 21:47:20'),(9,14,4,'2025-04-21 21:47:56'),(10,15,4,'2025-04-21 21:48:49'),(11,16,2,'2025-04-21 21:52:12'),(12,17,2,'2025-04-21 21:52:24'),(13,18,2,'2025-04-21 21:52:57'),(14,19,2,'2025-04-21 21:54:46'),(15,20,2,'2025-04-21 21:58:32'),(16,21,2,'2025-04-21 21:58:38'),(17,22,2,'2025-04-22 10:07:54'),(18,23,2,'2025-04-22 10:08:37'),(19,24,3,'2025-04-22 10:09:34'),(21,26,4,'2025-04-22 11:46:32'),(22,27,4,'2025-04-22 11:47:29'),(23,28,2,'2025-04-22 11:48:41'),(25,30,4,'2025-04-22 19:10:42');
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
  `mother_name` varchar(20) DEFAULT NULL,
  `guardian_name` varchar(200) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'Pascal ','Twizerimana','Male','2025-04-25',NULL,'0786074570','Kigali Rwanda','2025-04-25 06:40:35','Muhabura ','50000','BANK OF KIGALI','78852220000144','FATHER NAME','MOTHER NAME','NONE',1),(2,'Kalisa','Paul','Male','2025-04-26',NULL,'0786074571','TEST','2025-04-25 07:11:56','Kigali','15000','AB BANK','782354555220','FATHER NAME','MOTHER NAME','NONE',1),(3,'SOmen','Pascal','Female','2025-05-22',NULL,'78607470','','2025-05-08 11:26:16','Muhabura high school','50000','HHGGHHG','782354555220','FATHER NAME','CGHJNM','',1),(4,'sdfdsf','Pascal','Male','2025-05-22',NULL,'78607470','','2025-05-08 11:28:47','Muhabura high school','50000','HHGGHHG','782354555220','FATHER NAME','CGHJNM','',1);
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Panatech Ltd','0786074570','panatechrwanda@gmail.com','0786074570','Kigali','108647570',1,'Pascal Twizerimana','41001'),(3,'Nkurikiyimana Edmond','Nkurikiyimana Edmond','nkuredi@yahoo.fr','','SH 337st','12045',1,'Nkurikiyimana Edmond','41002'),(4,'Twizerimana Pascal','Twizerimana Pascal','pascal@panatechrwanda.com','0786074570','kigali','150',1,'Twizerimana Pascal','41003'),(5,'IISS','Twizerimana Pascal','pascal@panatechrwanda.com','0786074570','sadsadsa','150',1,'Twizerimana Pascal','41004'),(6,'IISSlll','Twizerimana Pascal','pascal@panatechrwanda.com','0786074570','ds','150',1,'Twizerimana Pascal','41005'),(7,'NAME','0788555535','','','','150',1,'','41006');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
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
INSERT INTO `tax_rates` VALUES (1,'VAT',16.00,'VAT16',0),(2,'Withholding Tax',5.00,'WHT5',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terms`
--

LOCK TABLES `terms` WRITE;
/*!40000 ALTER TABLE `terms` DISABLE KEYS */;
INSERT INTO `terms` VALUES (2,'First Terms','2025','2025-04-16','2025-04-29','2025-04-25 12:08:09'),(3,'Term 2','2024','2025-05-29','2025-05-30','2025-05-09 09:14:51'),(5,'Term 5','2024 - 2025','2025-05-29','2025-05-30','2025-05-09 09:16:18');
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
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_lines`
--

LOCK TABLES `transaction_lines` WRITE;
/*!40000 ALTER TABLE `transaction_lines` DISABLE KEYS */;
INSERT INTO `transaction_lines` VALUES (1,1,47,3000000000.00,0.00),(2,1,1,0.00,3000000000.00),(3,2,10,5000000.00,0.00),(4,2,11,3000000.00,0.00),(5,2,13,50000.00,0.00),(6,2,12,2000000.00,0.00),(7,2,47,0.00,10050000.00),(8,3,48,150000000.00,0.00),(9,3,1,0.00,150000000.00),(10,4,47,15000000.00,0.00),(11,4,48,0.00,15000000.00),(28,13,98,180000.00,0.00),(29,13,51,0.00,180000.00),(38,19,42,65000.00,0.00),(39,19,47,0.00,65000.00),(40,20,42,NULL,0.00),(41,20,47,0.00,NULL),(42,21,42,50000.00,0.00),(43,21,47,0.00,50000.00),(44,22,98,600000.00,0.00),(45,22,51,0.00,600000.00),(46,23,3,1782.05,0.00),(47,23,51,0.00,1782.05),(48,24,7,150000000.00,0.00),(49,24,41,0.00,150000000.00),(50,25,3,1782.05,0.00),(51,25,51,0.00,1782.05),(52,26,3,131177.22,0.00),(53,26,51,0.00,131177.22),(54,27,99,600000.00,0.00),(55,27,51,0.00,600000.00),(56,28,4,40535.41,0.00),(57,28,51,0.00,40535.41),(58,29,4,40535.41,0.00),(59,29,51,0.00,40535.41),(60,30,3,131177.22,0.00),(61,30,51,0.00,131177.22),(62,31,98,131177.20,0.00),(63,31,51,0.00,131177.20),(64,32,0,28800.00,0.00),(65,32,1,0.00,28800.00),(66,33,0,2000000.00,0.00),(67,33,95,0.00,2000000.00),(68,34,0,440000.00,0.00),(69,34,96,0.00,440000.00),(70,35,0,440000.00,0.00),(71,35,96,0.00,440000.00),(72,36,0,440000.00,0.00),(73,36,96,0.00,440000.00),(74,37,53,440000.00,0.00),(75,37,96,0.00,440000.00),(76,38,7,50000.00,0.00),(77,38,41,0.00,50000.00),(78,39,7,50000.00,0.00),(79,39,41,0.00,50000.00),(80,40,98,800050.00,0.00),(81,40,51,0.00,800050.00),(82,41,99,500000.00,0.00),(83,41,51,0.00,500000.00);
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
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `period_id` (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,'2025-04-23','RCEF-20250423034705','Donation for construction of polyclinic',0.00,'RWF',1.0000,NULL,0,6,'2025-04-23 13:47:33','journal_entry','posted',6,'2025-04-23 15:47:34',2),(2,'2025-04-24','RCEF-20250424085742','Payment of staff salaries April 2025',0.00,'RWF',1.0000,NULL,0,6,'2025-04-24 06:59:50','journal_entry','posted',6,'2025-04-24 08:59:51',2),(3,'2025-04-24','RCEF-20250424091904','Donation from US',0.00,'RWF',1.0000,NULL,0,6,'2025-04-24 07:20:20','journal_entry','posted',6,'2025-04-24 09:20:20',2),(4,'2025-04-24','RCEF-20250424092021','Transfer to Frw Account',0.00,'RWF',1.0000,NULL,0,6,'2025-04-24 07:21:15','journal_entry','posted',6,'2025-04-24 09:21:15',2),(13,'2025-04-25','RCEF-20250425112445','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,10,'2025-04-25 21:24:45','journal_entry','posted',10,'2025-04-25 14:24:45',2),(19,'2025-04-27','RCEF-20250427104518','School fees payment',0.00,'RWF',1.0000,NULL,0,10,'2025-04-27 20:45:18','journal_entry','posted',10,'2025-04-27 13:45:18',2),(20,'2025-04-27','RCEF-20250427104635','School fees payment',0.00,'RWF',1.0000,NULL,0,10,'2025-04-27 20:46:35','journal_entry','posted',10,'2025-04-27 13:46:36',2),(21,'2025-04-29','RCEF-20250429101330','School fees payment',0.00,'RWF',1.0000,NULL,0,10,'2025-04-29 08:13:30','journal_entry','posted',10,'2025-04-29 01:13:31',2),(22,'2025-04-29','RCEF-20250429105358','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,10,'2025-04-29 08:53:58','journal_entry','posted',10,'2025-04-29 01:53:58',2),(23,'2025-04-29','RCEF-20250429105957','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,10,'2025-04-29 08:59:57','journal_entry','posted',10,'2025-04-29 01:59:57',2),(24,'2025-04-29','RCEF-20250429072919','Received Donation',0.00,'RWF',1.0000,NULL,0,10,'2025-04-29 17:29:19','journal_entry','posted',10,'2025-04-29 10:29:19',2),(25,'2025-04-30','RCEF-20250430110949','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 09:09:49','journal_entry','posted',10,'2025-04-30 02:09:49',2),(26,'2025-04-30','RCEF-20250430111310','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 09:13:10','journal_entry','posted',10,'2025-04-30 02:13:10',2),(27,'2025-04-30','RCEF-20250430112538','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 09:25:38','journal_entry','posted',10,'2025-04-30 02:25:38',2),(28,'2025-04-30','RCEF-20250430112619','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 09:26:19','journal_entry','posted',10,'2025-04-30 02:26:19',2),(29,'2025-04-30','RCEF-20250430112921','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 09:29:21','journal_entry','posted',10,'2025-04-30 02:29:21',2),(30,'2025-04-30','RCEF-20250430113118','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 09:31:18','journal_entry','posted',10,'2025-04-30 02:31:18',2),(31,'2025-04-30','RCEF-20250430113506','Loan repayment transaction',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 09:35:06','journal_entry','posted',10,'2025-04-30 02:35:06',2),(32,'2025-04-30','20250430041305','Receiving purchase order',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 14:13:05','journal_entry','posted',10,'2025-04-30 07:13:05',2),(33,'2025-04-30','20250430041444','Receiving purchase order',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 14:14:44','journal_entry','posted',10,'2025-04-30 07:14:44',2),(34,'2025-04-30','20250430041556','Receiving purchase order',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 14:15:56','journal_entry','posted',10,'2025-04-30 07:15:56',2),(35,'2025-04-30','20250430041629','Receiving purchase order',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 14:16:29','journal_entry','posted',10,'2025-04-30 07:16:29',2),(36,'2025-04-30','20250430041705','Receiving purchase order',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 14:17:05','journal_entry','posted',10,'2025-04-30 07:17:05',2),(37,'2025-04-30','20250430041759','Receiving purchase order',0.00,'RWF',1.0000,NULL,0,10,'2025-04-30 14:17:59','journal_entry','posted',10,'2025-04-30 07:18:00',2),(38,'2025-05-08','RCEF-20250508122215','Received Donation',0.00,'RWF',1.0000,NULL,0,10,'2025-05-08 10:22:15','journal_entry','posted',10,'2025-05-08 03:22:15',2),(39,'2025-05-08','RCEF-20250508122349','Received Donation',0.00,'RWF',1.0000,NULL,0,10,'2025-05-08 10:23:49','journal_entry','posted',10,'2025-05-08 03:23:49',2),(40,'2025-05-08','RCEF-20250508011117','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,10,'2025-05-08 11:11:17','journal_entry','posted',10,'2025-05-08 04:11:17',2),(41,'2025-05-08','RCEF-20250508011226','Loan transaction Approval',0.00,'RWF',1.0000,NULL,0,10,'2025-05-08 11:12:26','journal_entry','posted',10,'2025-05-08 04:12:26',2);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$m0Wxgar0EfNpgu.WEILIOeQ313eLDo7YdtVS.Uq3pl99AH8cCzzbW','admin@example.com','System Administrator','admin','2025-04-23 10:57:33','2025-04-07 21:36:14',1),(6,'nkuredi@yahoo.fr','$2y$10$0W/uLLaaHNEz6HM0MIYUT.JNN8d8sQ4Qc.jiQj8CPf6pas2o7/FTi','nkuredi@yahoo.fr',NULL,'user','2025-04-25 15:07:58','2025-04-14 12:40:46',1),(7,'jeanneuwimana06@gmail.com','$2y$10$s/CTkTw9PmtTtVMt7n9MkeGw1pAV3xDWA1oxpdrkW6E8xrRVmcfgS','jeanneuwimana06@gmail.com',NULL,'user','2025-04-14 17:07:29','2025-04-14 12:42:06',1),(8,'glorigah12@gmail.com','$2y$10$G3uLBsuogdotroceAnBbI.JdUw3DGqwafctnQTbOntdQ7LUjzCsVq','glorigah12@gmail.com',NULL,'user','2025-04-14 17:15:40','2025-04-14 12:43:17',1),(9,'rcefrw2013@gmail.com','$2y$10$cWXn8goTQ8iHUGEUNY77n.vcA.H234Y/Eh.PWXbfM30yBZNdDeT3u','rcefrw2013@gmail.com',NULL,'user',NULL,'2025-04-14 12:44:54',1),(10,'panatech@gmail.com','$2y$10$CETqkGBBOgCW/.nfNZ5mTechSd9baquYcJARZJ8VR22ND.L9NuX1i','panatech@gmail.com',NULL,'user','2025-05-09 01:49:44','2025-04-14 15:06:16',1),(11,'sses@gmail.com','$2y$10$51Wh015voj5bteVV7cSLJOnJlhMqGNe3BSfjq.gGoFRrSvfJLYSsu','sses@gmail.com',NULL,'user',NULL,'2025-05-06 12:46:54',1);
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

-- Dump completed on 2025-05-09  5:52:11
