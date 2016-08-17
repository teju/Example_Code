-- MySQL dump 10.13  Distrib 5.1.73, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: shopnix
-- ------------------------------------------------------
-- Server version	5.1.73-0ubuntu0.10.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tProductVariant`
--

DROP TABLE IF EXISTS `tProductVariant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tProductVariant` (
  `prodvariant_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `option_id_1` int(11) DEFAULT NULL,
  `option_id_2` int(11) DEFAULT NULL,
  `option_id_3` int(11) DEFAULT NULL,
  `sku` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `mrp` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping_charges` decimal(10,2) NOT NULL DEFAULT '0.00',
  `net_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_level` int(11) NOT NULL DEFAULT '0',
  `image` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no_image.jpg',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `disp_order` int(11) NOT NULL DEFAULT '1000',
  `shop_id` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`prodvariant_id`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `product_id` (`product_id`,`option_id_1`,`option_id_2`,`option_id_3`),
  KEY `shop_id` (`shop_id`),
  KEY `option_id_2` (`option_id_2`),
  KEY `option_id_3` (`option_id_3`),
  KEY `option_id_1` (`option_id_1`),
  KEY `product_id_2` (`product_id`),
  CONSTRAINT `tproductvariant_ibfk_5` FOREIGN KEY (`option_id_3`) REFERENCES `tProductOption` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tproductvariant_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `tProduct` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tproductvariant_ibfk_2` FOREIGN KEY (`shop_id`) REFERENCES `tShop` (`shop_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tproductvariant_ibfk_3` FOREIGN KEY (`option_id_1`) REFERENCES `tProductOption` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tproductvariant_ibfk_4` FOREIGN KEY (`option_id_2`) REFERENCES `tProductOption` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tProductVariant`
--

LOCK TABLES `tProductVariant` WRITE;
/*!40000 ALTER TABLE `tProductVariant` DISABLE KEYS */;
INSERT INTO `tProductVariant` VALUES (59,586192,132,NULL,NULL,'9898s','280.00','0.00','0.00','0.00','280.00',100,'no_image.jpg',1,1000,6192,'2014-10-15 11:52:33'),(60,586192,133,NULL,NULL,'9898s1','280.00','0.00','0.00','0.00','280.00',100,'no_image.jpg',1,1000,6192,'2014-10-15 11:52:33'),(61,586192,134,NULL,NULL,'9898s3','280.00','0.00','0.00','0.00','280.00',100,'no_image.jpg',1,1000,6192,'2014-10-15 11:52:33'),(62,572914,135,NULL,NULL,'1023c','280.00','0.00','0.00','0.00','280.00',30,'no_image.jpg',1,1000,6192,'2014-10-15 12:00:02'),(63,572834,138,NULL,NULL,'1020c','280.00','0.00','0.00','0.00','280.00',25,'t5.20141015173826.jpg',1,1000,6192,'2014-10-15 12:08:26'),(64,572912,141,NULL,NULL,'1021b','280.00','0.00','0.00','0.00','280.00',50,'moutain-blend.20141015174800.jpg',1,1000,6192,'2014-10-15 12:18:00'),(70,572913,152,NULL,NULL,'1029c','280.00','0.00','0.00','0.00','280.00',25,'cinnamon-flavor.20141016180221.jpg',1,1000,6192,'2014-10-16 12:32:21'),(71,585330,177,NULL,NULL,'100','100.00','10.00','10.00','10.00','109.00',10,'no_image.jpg',1,1000,3589,'2014-10-16 13:39:21');
/*!40000 ALTER TABLE `tProductVariant` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-10-16 19:09:37
