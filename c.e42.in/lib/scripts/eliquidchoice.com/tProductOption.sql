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
-- Table structure for table `tProductOption`
--

DROP TABLE IF EXISTS `tProductOption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tProductOption` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `option_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `option_value` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `option_name_disp_order` int(11) NOT NULL DEFAULT '100',
  `option_value_disp_order` int(11) NOT NULL DEFAULT '1000',
  `shop_id` int(11) NOT NULL,
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`,`option_value`,`shop_id`),
  KEY `shop_id` (`shop_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `tproductoption_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `tProduct` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tproductoption_ibfk_1` FOREIGN KEY (`shop_id`) REFERENCES `tShop` (`shop_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tProductOption`
--

LOCK TABLES `tProductOption` WRITE;
/*!40000 ALTER TABLE `tProductOption` DISABLE KEYS */;
INSERT INTO `tProductOption` VALUES (132,586192,'Nicotine Strength ','6mg(low)',1,1,6192),(133,586192,'Nicotine Strength ','12mg(medium)',1,2,6192),(134,586192,'Nicotine Strength ','18mg(hig)',1,3,6192),(135,572914,'Select Nicotine Strength ','6mg(Low)',1,1,6192),(136,572914,'Select Nicotine Strength ','12mg(Medium)',1,2,6192),(137,572914,'Select Nicotine Strength ','18mg(High)',1,3,6192),(138,572834,'Select Nicotine %','6mg(low)',1,1,6192),(139,572834,'Select Nicotine %','12mg(Medium)',1,2,6192),(140,572834,'Select Nicotine %','18mg(High)',1,3,6192),(141,572912,'Select Nicotine %','6mg[Low]',1,1,6192),(142,572912,'Select Nicotine %','12mg[Medium]',1,2,6192),(143,572912,'Select Nicotine %','18mg[High]',1,5,6192),(152,572913,'Select Nicotine %','6mg',1,1,6192),(153,572913,'Select Nicotine %','12mg',1,2,6192),(154,572913,'Select Nicotine %','18mg',1,5,6192),(177,585330,'Color','red',1,1,3589);
/*!40000 ALTER TABLE `tProductOption` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-10-16 19:09:27
