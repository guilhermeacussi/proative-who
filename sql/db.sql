/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: who_db
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

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
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conteudo` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `answers`
--

LOCK TABLES `answers` WRITE;
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;
INSERT INTO `answers` VALUES
(10,'',30,2,'<p>não</p>','2025-12-08 19:15:07'),
(11,'',31,3,'<p>Não contamine o Who com música ruim pfvr </p>','2025-12-08 19:22:16'),
(12,'',31,2,'<p>calado</p>','2025-12-08 19:23:48'),
(13,'',32,2,'<p>Não sei tbm</p>','2025-12-08 20:29:55'),
(14,'',36,3,'<p>concordo com vc</p>','2025-12-09 02:45:39'),
(15,'',37,29,'<p>Bixo besta</p>','2025-12-09 02:56:44'),
(16,'',37,3,'<p>Tonto </p>','2025-12-09 02:56:59'),
(17,'',36,2,'<p>vai levar ban</p>','2025-12-09 03:12:39'),
(18,'',43,31,'<p>Au au au </p>','2025-12-09 14:57:15'),
(19,'',44,3,'<p>Muito obrigado!!! </p>','2025-12-09 15:10:37'),
(20,'',44,3,'<p>Muito obrigado!!! </p>','2025-12-09 15:10:43'),
(21,'',44,2,'<p>valeuu</p>','2025-12-09 15:16:03'),
(22,'',46,8,'<p>Uhullllll </p>','2025-12-09 15:41:36'),
(23,'',48,3,'<p>Autista</p>','2025-12-11 02:56:33'),
(24,'',48,33,'<p>Fazer um autista? </p>','2025-12-11 02:58:47'),
(25,'',50,2,'<p>sim</p>','2025-12-15 16:02:34');
/*!40000 ALTER TABLE `answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conteudo` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `answer_id` (`answer_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followers`
--

DROP TABLE IF EXISTS `followers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `followers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `follower_id` int(11) NOT NULL COMMENT 'ID do usuário que está seguindo',
  `followed_id` int(11) NOT NULL COMMENT 'ID do usuário sendo seguido',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_follow` (`follower_id`,`followed_id`) COMMENT 'Impede duplicatas: um usuário não pode seguir outro mais de uma vez',
  KEY `follower_id` (`follower_id`),
  KEY `followed_id` (`followed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followers`
--

LOCK TABLES `followers` WRITE;
/*!40000 ALTER TABLE `followers` DISABLE KEYS */;
INSERT INTO `followers` VALUES
(38,2,16,'2025-12-08 19:18:35'),
(39,16,2,'2025-12-08 20:05:59');
/*!40000 ALTER TABLE `followers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`user_id`,`question_id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES
(1,3,14,'2025-11-09 03:58:40'),
(2,3,13,'2025-11-09 03:58:42'),
(3,3,6,'2025-11-09 04:00:29'),
(4,3,7,'2025-11-09 04:00:30'),
(10,3,20,'2025-11-13 10:44:57'),
(11,7,16,'2025-11-13 11:40:03'),
(12,3,23,'2025-11-13 17:51:01'),
(13,3,22,'2025-11-13 17:51:02'),
(18,3,16,'2025-11-13 17:51:09'),
(19,14,28,'2025-12-05 21:56:33'),
(20,2,28,'2025-12-08 18:36:06'),
(21,9,28,'2025-12-08 19:09:19'),
(22,2,31,'2025-12-08 19:24:15'),
(23,9,35,'2025-12-08 23:20:04'),
(24,2,35,'2025-12-09 00:46:07'),
(25,3,42,'2025-12-09 12:51:22'),
(26,9,44,'2025-12-09 15:10:38'),
(27,2,46,'2025-12-09 15:17:14'),
(28,2,44,'2025-12-09 15:17:16'),
(29,8,47,'2025-12-09 15:41:05'),
(30,8,46,'2025-12-09 15:41:06'),
(31,9,49,'2025-12-13 21:49:12'),
(32,35,51,'2025-12-24 14:09:39'),
(33,35,42,'2025-12-24 14:09:50'),
(39,36,42,'2026-02-08 05:02:14');
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `likes_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES
(42,3,'Bem vindos Proative!','<p>Sejam bem vindos ao Who</p>','2025-12-09 12:50:36',3),
(43,31,'Olar','<p>Rapaiz Ã© conplicadi issae ein</p>','2025-12-09 14:56:52',0),
(44,32,'ParabÃ©ns a Ghost corp','<p>ParabÃ©ns pelo o aplicativo funcional</p>','2025-12-09 14:59:05',2),
(45,5,'ghost tech na proative!!','<p>incrÃ­vel ðŸ’œ</p>','2025-12-09 15:13:01',0),
(46,2,'Ghost na Proativeee','<p>Muito obrigada pela oportunidade!!</p>','2025-12-09 15:16:43',2),
(47,8,'Oi oi oi oi oi','<p><br></p>','2025-12-09 15:40:58',1),
(48,33,'Gordinha mais tÃ¡ bom','<p>Acho que estou grÃ¡vida, o que posso fazer? </p>','2025-12-11 02:51:32',0),
(49,9,'aaa','<p>testando</p>','2025-12-13 21:49:04',1),
(50,34,'O lula Ã© um bom presidente?','<p><br></p>','2025-12-14 14:29:42',0),
(51,35,'Oi','<p>Oii</p>','2025-12-24 14:09:34',1),
(52,3,'Oiiii','<p>Ijgjn</p>','2025-12-28 03:36:20',0),
(53,36,'Bulls campeÃ£o da nba esse ano, sim ou claro?','<p><br></p>','2026-02-08 05:03:14',0);
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT 'default.png',
  `pgp_key` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(2,'malu','malugaldino99@gmail.com','$2y$10$2A7bzXxnaOJaBFKnqPhL7uSmgFLjJyRXl6ReDfKZxPhZ5PiygLu9G','Jesus save my life.\r\nHE choose me.\r\nHE loves me first.\r\nHE\'s my true love.','uploads/avatar_69129eddc8041.jpg','2025-11-06 00:12:58','uploads/avatar_69129eddc8041.jpg','','2025-11-08 06:32:45',1),
(3,'gacussi','guilhermehacussi@gmail.com','$2y$10$eHv1iyTk5aIQpfJVNtNCau4Ar4kX2JTA3d1pbqc.qpQcTXq3fxGqy','basketball player\r\n11','uploads/avatar_6914c2c55ea95.jpg','2025-11-08 13:59:05','','','2025-11-08 06:32:45',1),
(4,'Kemy','kemillsousa737@gmail.com','$2y$10$NQnpwbxVaEW6Xo27mrfyJeXAKNQfN87rPLWxUjsioQhrQKuROeCUS',NULL,NULL,'2025-11-12 17:21:30','default.png',NULL,'2025-11-12 09:21:30',0),
(5,'gio','giovannawitoriabp@gmail.com','$2y$10$J8oG.OB5d9k.6sbiP7xkpuiTuGQUl9UERwHnPatL8NLL3NlpVhXjO','hater n 1 do Guilherme Henrique Acussi Marra',NULL,'2025-11-13 09:56:22','uploads/avatar_6915abf7f1637.jpg','','2025-11-13 01:56:22',0),
(6,'duda','dudad3254@gmail.com','$2y$10$l4UJutwoqfK080Kcmd.QoOa7hXPqVrZRihzFG63KrIJALqXZ1Re8K','oiiiiiii',NULL,'2025-11-13 09:57:56','','','2025-11-13 01:57:56',0),
(7,'sahhmindelliii','sabinecontareserva@gmail.com','$2y$10$orQzNc72zKyUauyUXvnUOuNzrBsCRPh/45jPUnQo/1qtVxPJKupL.','sei laaaaaa',NULL,'2025-11-13 11:37:52','','','2025-11-13 03:37:52',0),
(8,'davi','davisoarescintra2010@gmail.com','$2y$10$YKkr4wWXYufnSZiKjiPRx.iKf937OObxcbv72K88r5RzszAkww2cK','????',NULL,'2025-11-13 13:15:07','','','2025-11-13 05:15:07',0),
(9,'rafaeduardax','rafadivinha@gmail.com','$2y$10$rL6nKbLTHvDgQLdcri8GcuuZU9ZaxS.KYlLFNhStvLIMhoZ/5/X5e','',NULL,'2025-11-13 15:25:01','','','2025-11-13 07:25:01',0),
(10,'Leonardo','leogameque@gmail.coml','$2y$10$BJUpidbw5x.khlf4PXnkN.PY9ZH1od5QSFc5Ju.Vq4q5mSv2UqIY6',NULL,NULL,'2025-11-13 17:53:33','default.png',NULL,'2025-11-13 09:53:33',0),
(11,'Milena','stargirl00777@gmail.com','$2y$10$JG5x0MYi6kANGr431h844evEyIC48LL9DZVtAfzacC4XIvXzpt4Oi',NULL,NULL,'2025-11-18 11:57:52','default.png',NULL,'2025-11-18 03:57:52',0),
(12,'Maria Eduarda Chella','chelladudama@gmail.com','$2y$10$X.f0pzQGbZeCd2dBrRsHYuhVZAzim0ITt6SyyogV0mEh5tgj2hHMi',NULL,NULL,'2025-11-18 13:42:58','default.png',NULL,'2025-11-18 05:42:58',0),
(13,'Antonella Cobianchi Prucoli','antonellacobianchi9@gmail.com','$2y$10$y9N7OPb9IAE94jeKFSXBD.V9BBeVRlR8TePt/t9ewOzWxI8AMrknm',NULL,NULL,'2025-11-19 17:23:21','default.png',NULL,'2025-11-19 09:23:21',0),
(14,'mari','domicianomariaclara87@gmail.com','$2y$10$miBiJK4zgJVyfoABiO1IlORztJhXF.hbW.wJTRuOg9E46zYJDPZhe',NULL,NULL,'2025-12-05 21:55:05','default.png',NULL,'2025-12-05 13:55:05',0),
(15,'Juh.Wah','juh.wah13@gmail.com','$2y$10$rtGinddZgo5I3wvkj6ES2.0qwLDcpYdQ10Q2peMWO6nKPiKprKyjW',NULL,NULL,'2025-12-08 18:59:37','default.png',NULL,'2025-12-08 10:59:37',0),
(16,'Carola','soarescardozoana@gmail.com','$2y$10$/3267difQ.DbBnDjupzcYeiM2JmZEItUMEHJhcofrQdJRh9R5VhH6',NULL,NULL,'2025-12-08 19:12:52','default.png',NULL,'2025-12-08 11:12:52',0),
(17,'Nicolas','nicolasthiagoferreira1@gmail.com','$2y$10$mR3RREJ6WHVSSxSQRNzs8eKEPUuuWdYdOhxs18vOnHRl2keMRSSxG',NULL,NULL,'2025-12-08 20:04:05','default.png',NULL,'2025-12-08 12:04:05',0),
(18,'RNTHEFIRST','rnlfreitas05@gmail.com','$2y$10$iphTwqB3U3xVNird0zgc5OA01garNxkVE3TKkVgimZf4dw0s/ucSi',NULL,NULL,'2025-12-08 20:10:59','default.png',NULL,'2025-12-08 12:10:59',0),
(19,'Raissa','raissavnascimento8@gmail.com','$2y$10$pT6IEX8kaX4jwYfKLDuTV.7DMmLUZ0U4.BiM.rMhndf3Eu8JfCG9q',NULL,NULL,'2025-12-08 20:21:15','default.png',NULL,'2025-12-08 12:21:15',0),
(20,'Suandra','suandrasantos97@gmail.com','$2y$10$ItOsK4x/XQ3n4AHRp.sGcesBA9PwWRwokVWCqXYLvxIGA6JoofTha',NULL,NULL,'2025-12-08 20:24:34','default.png',NULL,'2025-12-08 12:24:34',0),
(21,'Reidogado222','go0101074@gmail.com','$2y$10$LGWCxHZDCv7apQtiOIRzu.iZFd0UJB0oyOjPjOMHlYZSE050BdepS',NULL,NULL,'2025-12-08 20:49:41','default.png',NULL,'2025-12-08 12:49:41',0),
(22,'Isabella','isabellaferreiraarruda5@gmail.com','$2y$10$o.QfkL.gp80K15Ei3dc3u.P7Zic3fpd/Ylr8WE0jDlQWE2CKE9q6.',NULL,NULL,'2025-12-08 21:08:16','default.png',NULL,'2025-12-08 13:08:16',0),
(23,'Natanael Vieira Guilherme','natanvieira2501@gmail.com','$2y$10$ojLDzvWdKRd0P.wXX1qS2.UE6ITqy95zvgKA3F14L2vlWFhqaO6J.',NULL,NULL,'2025-12-08 21:29:46','default.png',NULL,'2025-12-08 13:29:46',0),
(24,'Cacique','esoarescamillo@gmail.com','$2y$10$P7PxCwyiz6CyY2aDn5xle.GuqrP9nGX23Vs7hDNbMP4lRNgkoware',NULL,NULL,'2025-12-08 22:41:37','default.png',NULL,'2025-12-08 14:41:37',0),
(25,'teste','testew8r28@gmail.com','$2y$10$0VNBhTeRc6U6By44LGGHH.QAangoXqK.oc171AE7BZMsO3GWQc.MC',NULL,NULL,'2025-12-09 01:46:26','default.png',NULL,'2025-12-08 17:46:26',0),
(26,'wtesteq','qtestew8wr28@gmail.com','$2y$10$MovJ1IEjhf7hEeTphU.wrOfQr4s3RM8fLnr7aAgMGTwnbXErLTUb6',NULL,NULL,'2025-12-09 01:50:59','default.png',NULL,'2025-12-08 17:50:59',0),
(27,'344r33w','te6q6nzyf@mozmail.com3','$2y$10$IisJlHJB8m6F3zFR1/ZJfOuezWOQh2OqOhONweoV3UuxaNdmNEbje',NULL,NULL,'2025-12-09 01:54:13','default.png',NULL,'2025-12-08 17:54:13',0),
(28,'ss2ds2ds2ed','ynryxl1uj@mozmail.com','$2y$10$mbDEMSniuWJ2GxUgq/U0be1EI7VfZ0lTwZEO23oI/y/8dI8YI1rBS',NULL,NULL,'2025-12-09 01:56:00','default.png',NULL,'2025-12-08 17:56:00',0),
(29,'Hshehehs','hahahshs@gmail.com','$2y$10$lQI.Pu9i0eQ.akGe7ba7Y.ztw8i3zZgrhK00i.6Fp8UrUBgMIL7cS',NULL,NULL,'2025-12-09 02:05:55','default.png',NULL,'2025-12-08 18:05:55',0),
(30,'Não queria estar fazendo isso','vaitomarnocuguilherne@gmail.com','$2y$10$hRT3d05Q3qas6aVCeQxo8eaXxq30.quITyX6jaKt25gNWSZjDtUue',NULL,NULL,'2025-12-09 02:50:03','default.png',NULL,'2025-12-08 18:50:03',0),
(31,'Guigui','ass@gmail.com','$2y$10$O0iY0L2OEw7gtP6/sbPLjOn8QhxMgOp4J8at/w2/A1ArB7Deoy.VS',NULL,NULL,'2025-12-09 14:56:26','default.png',NULL,'2025-12-09 06:56:26',0),
(32,'Josenaldo Bananal','lorenzomarqalves@gmail.com','$2y$10$aTXvpnauj/Im2LQwpahphey3WQPmCEvCeOTHjwYXEKA.RXR6ecvR2',NULL,NULL,'2025-12-09 14:58:19','default.png',NULL,'2025-12-09 06:58:19',0),
(33,'Marquinhos matador','marquinhosmatador@gmail.com','$2y$10$nRAMoV9N7HVhHwVG0nTi6u2ffEMTGpoMgM2NP3HVYjSGmgkEiG9AC',NULL,NULL,'2025-12-11 02:50:53','default.png',NULL,'2025-12-10 18:50:53',0),
(34,'Rafael Batista Gomes de Oliveira','rafaelbatistagomesrodriguesdos@gmail.com','$2y$10$ddFghVMti0ZRiCMSiwS5KO8xq0hW.w7BP81sBvtr/x7LWoGSLkNCS',NULL,NULL,'2025-12-14 14:28:51','default.png',NULL,'2025-12-14 06:28:51',0),
(35,'Laís','laishelenasouzadasilva@gmail.com','$2y$10$rWSF2DD9uv/1/qUIy821WuvPDq8QKvz1emdfpb8jBgpbaNAfGKlgi',NULL,NULL,'2025-12-24 14:08:15','default.png',NULL,'2025-12-24 06:08:15',0),
(36,'Will','wcobain87@gmail.com','$2y$10$84Fs3fvbsbteKf7pyAEYle72126khYpBK6FebCJapCLJFeXDhGGmW',NULL,NULL,'2026-02-08 05:01:50','default.png',NULL,'2026-02-07 21:01:50',0);
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

-- Dump completed on 2026-02-19 15:32:58
