CREATE DATABASE  IF NOT EXISTS `ampedframework` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `ampedframework`;
-- MySQL dump 10.13  Distrib 5.5.16, for osx10.5 (i386)
--
-- Host: localhost    Database: ampedframework
-- ------------------------------------------------------
-- Server version	5.1.44-log

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
-- Table structure for table `amp_user_followships`
--

DROP TABLE IF EXISTS `amp_user_followships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_user_followships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `following_user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_user_followships`
--

LOCK TABLES `amp_user_followships` WRITE;
/*!40000 ALTER TABLE `amp_user_followships` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_user_followships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amp_item_stats`
--

DROP TABLE IF EXISTS `amp_item_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_item_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `image_id` bigint(20) unsigned NOT NULL,
  `image_views` bigint(20) unsigned NOT NULL DEFAULT '1',
  `image_page_views` bigint(20) unsigned NOT NULL DEFAULT '1',
  `image_last_viewed` timestamp NULL DEFAULT NULL,
  `image_page_last_viewed` timestamp NULL DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `image_id_UNIQUE` (`image_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_item_stats`
--

LOCK TABLES `amp_item_stats` WRITE;
/*!40000 ALTER TABLE `amp_item_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_item_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amp_item_response`
--

DROP TABLE IF EXISTS `amp_item_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_item_response` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `image_id` bigint(20) unsigned NOT NULL,
  `response_image_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_item_response`
--

LOCK TABLES `amp_item_response` WRITE;
/*!40000 ALTER TABLE `amp_item_response` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_item_response` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amp_item_comments`
--

DROP TABLE IF EXISTS `amp_item_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_item_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `image_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` varchar(255) NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_item_comments`
--

LOCK TABLES `amp_item_comments` WRITE;
/*!40000 ALTER TABLE `amp_item_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_item_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amp_verify_tokens`
--

DROP TABLE IF EXISTS `amp_verify_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_verify_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `topic` varchar(255) DEFAULT NULL COMMENT 'This is used for any kinds of token verification such as verifying that the email exists for new users, etc. The topic would define what is being used for, eg. new_user',
  `token` varchar(32) DEFAULT NULL,
  `verified` tinyint(1) unsigned DEFAULT '0' COMMENT 'true:1 or false:0',
  `ts_initiated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ts_verified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This is used for any kinds of token verification';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_verify_tokens`
--

LOCK TABLES `amp_verify_tokens` WRITE;
/*!40000 ALTER TABLE `amp_verify_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_verify_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amp_user_items`
--

DROP TABLE IF EXISTS `amp_user_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_user_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `image_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_images___user_id` (`user_id`),
  KEY `user_images___image_id` (`image_id`),
  CONSTRAINT `user_images___user_id` FOREIGN KEY (`user_id`) REFERENCES `amp_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_user_items`
--

LOCK TABLES `amp_user_items` WRITE;
/*!40000 ALTER TABLE `amp_user_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_user_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amp_users`
--

DROP TABLE IF EXISTS `amp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL,
  `website` varchar(1000) DEFAULT NULL,
  `user_type` enum('loggedin','temp','site') DEFAULT 'loggedin',
  `is_email_verified` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) unsigned DEFAULT '1',
  `ts_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp of row insert',
  `last_visited` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_users`
--

LOCK TABLES `amp_users` WRITE;
/*!40000 ALTER TABLE `amp_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amp_user_networks`
--

DROP TABLE IF EXISTS `amp_user_networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_user_networks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `network` enum('facebook','twitter') DEFAULT NULL,
  `network_profile_id` bigint(20) unsigned DEFAULT NULL,
  `serialized_network_profile` text,
  `serialized_tokens_array` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_profile_id_for_given_network` (`network`,`network_profile_id`),
  KEY `user_networks___user_id` (`user_id`),
  CONSTRAINT `user_networks___user_id` FOREIGN KEY (`user_id`) REFERENCES `amp_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_user_networks`
--

LOCK TABLES `amp_user_networks` WRITE;
/*!40000 ALTER TABLE `amp_user_networks` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_user_networks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amp_items`
--

DROP TABLE IF EXISTS `amp_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amp_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(140) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `data` text,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash_UNIQUE` (`hash`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amp_items`
--

LOCK TABLES `amp_items` WRITE;
/*!40000 ALTER TABLE `amp_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `amp_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-05-29 21:01:52
