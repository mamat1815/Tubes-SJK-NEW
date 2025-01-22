/*!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: achats
-- ------------------------------------------------------
-- Server version	10.11.8-MariaDB-0ubuntu0.24.04.1

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
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `commentId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`commentId`),
  KEY `userId` (`userId`),
  KEY `productId` (`productId`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_images`
--

DROP TABLE IF EXISTS `order_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_images` (
  `orderImageId` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `imagePath` varchar(255) NOT NULL,
  `uploadedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`orderImageId`),
  KEY `orderId` (`orderId`),
  CONSTRAINT `order_images_ibfk_1` FOREIGN KEY (`orderId`) REFERENCES `orders` (`orderId`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_images`
--

LOCK TABLES `order_images` WRITE;
/*!40000 ALTER TABLE `order_images` DISABLE KEYS */;
INSERT INTO `order_images` VALUES
(1,1,'uploads/order_images/img_676de10f0271c2.85608419.jpg','2024-12-26 23:04:47');
/*!40000 ALTER TABLE `order_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `orderId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `sellerId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` double NOT NULL DEFAULT 250000,
  `workTime` int(11) NOT NULL DEFAULT 24,
  `deadline` datetime DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`orderId`),
  KEY `userId` (`userId`),
  KEY `sellerId` (`sellerId`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`sellerId`) REFERENCES `sellers` (`sellerId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,3,1,'mau makan','AAA',250000,24,'2025-01-11 05:56:00','pending','2024-12-26 23:04:47');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productId` int(11) NOT NULL,
  `imagePath` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `productId` (`productId`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES
(1,1,'uploads/products/product_676da85957db65.24931098.png','2024-12-26 19:02:49'),
(2,1,'uploads/products/product_676da859695028.07203128.png','2024-12-26 19:02:49'),
(3,1,'uploads/products/product_676da8597992f6.19444506.png','2024-12-26 19:02:49'),
(4,1,'uploads/products/product_676da8598ba826.06068648.png','2024-12-26 19:02:49');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `productId` int(11) NOT NULL AUTO_INCREMENT,
  `sellerId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `rate` int(11) DEFAULT NULL,
  `sold` int(11) DEFAULT NULL,
  PRIMARY KEY (`productId`),
  KEY `sellerId` (`sellerId`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`sellerId`) REFERENCES `sellers` (`sellerId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(1,1,'Mangkok Nasi Uduk','Produk terbaik sepanjang masa',20000,0,0,2);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productscategories`
--

DROP TABLE IF EXISTS `productscategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productscategories` (
  `productsCategoriesId` int(11) NOT NULL AUTO_INCREMENT,
  `productCategoriesValues` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`productsCategoriesId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productscategories`
--

LOCK TABLES `productscategories` WRITE;
/*!40000 ALTER TABLE `productscategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `productscategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchases` (
  `purchaseId` int(11) NOT NULL AUTO_INCREMENT,
  `transId` varchar(50) DEFAULT NULL,
  `productId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `purchaseDate` datetime DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `amountTotal` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`purchaseId`),
  KEY `productId` (`productId`),
  KEY `userId` (`userId`),
  CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`),
  CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES
(1,'ORDER-676dbc602e8d1',1,3,'2024-12-26 20:28:18',1,20000,'pending'),
(2,'ORDER-676dbc7062c06',1,3,'2024-12-26 20:28:33',1,20000,'pending');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchasestatus`
--

DROP TABLE IF EXISTS `purchasestatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchasestatus` (
  `purchaseStatusId` int(11) NOT NULL AUTO_INCREMENT,
  `purchaseId` int(11) NOT NULL,
  `statusValue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`purchaseStatusId`),
  KEY `purchaseId` (`purchaseId`),
  CONSTRAINT `purchasestatus_ibfk_1` FOREIGN KEY (`purchaseId`) REFERENCES `purchases` (`purchaseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchasestatus`
--

LOCK TABLES `purchasestatus` WRITE;
/*!40000 ALTER TABLE `purchasestatus` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchasestatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sellers`
--

DROP TABLE IF EXISTS `sellers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sellers` (
  `sellerId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `rate` int(11) DEFAULT NULL,
  `hourRate` decimal(10,2) DEFAULT NULL,
  `availability` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sellerId`),
  KEY `userId` (`userId`),
  CONSTRAINT `sellers_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sellers`
--

LOCK TABLES `sellers` WRITE;
/*!40000 ALTER TABLE `sellers` DISABLE KEYS */;
INSERT INTO `sellers` VALUES
(1,3,'Aku Pemain Bola','uploads/images.jpg',0,50.00,'1');
/*!40000 ALTER TABLE `sellers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sellerskill`
--

DROP TABLE IF EXISTS `sellerskill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sellerskill` (
  `sellerSkillId` int(11) NOT NULL AUTO_INCREMENT,
  `sellerId` int(11) NOT NULL,
  `skillId` int(11) NOT NULL,
  PRIMARY KEY (`sellerSkillId`),
  KEY `sellerId` (`sellerId`),
  KEY `skillId` (`skillId`),
  CONSTRAINT `sellerskill_ibfk_1` FOREIGN KEY (`sellerId`) REFERENCES `sellers` (`sellerId`),
  CONSTRAINT `sellerskill_ibfk_2` FOREIGN KEY (`skillId`) REFERENCES `skills` (`skillId`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sellerskill`
--

LOCK TABLES `sellerskill` WRITE;
/*!40000 ALTER TABLE `sellerskill` DISABLE KEYS */;
INSERT INTO `sellerskill` VALUES
(1,1,2),
(2,1,3);
/*!40000 ALTER TABLE `sellerskill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skills` (
  `skillId` int(11) NOT NULL AUTO_INCREMENT,
  `skillValue` varchar(255) NOT NULL,
  PRIMARY KEY (`skillId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES
(1,'bola'),
(2,'pemain bola'),
(3,'html');
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sellerId` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sellerId` (`sellerId`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`sellerId`) REFERENCES `sellers` (`sellerId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES
(1,1,'initialized',0.00,'2024-12-26 19:02:17'),
(2,1,'initialized',20000.00,'2024-12-26 20:28:18'),
(3,1,'initialized',20000.00,'2024-12-26 20:28:33');
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
  `fullName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `number` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Muhammad Afsar Tambawang','23523098@students.uii.ac.id','$2y$10$eTTLT7zCLPgMqO10SVKm0e9z13uum14LkdnsP0mWcrH4qyz.hc2yG','Jl. Suropati No 6 RT 04 RW 05','uploads/images.jpg',82198547240),
(2,'Muhammad Afsar Tambawang','muhafsar1815@gmail.com','$2y$10$11tTDXWoMH5hoYK95HRvfe5QQqLsD6MJ9KjbsJH/FNr5tJGuqXGSq','Jl. Suropati No 6 RT 04 RW 05','uploads/images.jpg',82198547240),
(3,'Afsar','afsartambawang.work@gmail.com','$2y$10$ul6/i0E2yFqROO8gPnxKEu.xs3786.jJ2yv0tUoLSzUhLphzpqLT2','Jl. Suropati No 6 RT 04 RW 05','uploads/images.jpg',82198547240);
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

-- Dump completed on 2024-12-27  6:43:10
