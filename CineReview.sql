CREATE DATABASE  IF NOT EXISTS `cinereview` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `cinereview`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: cinereview
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_fav` (`user_id`,`movie_id`),
  KEY `movie_id` (`movie_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
INSERT INTO `favorites` VALUES (1,2,3,'2026-03-01 05:42:13'),(2,3,3,'2026-03-29 10:09:18');
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `year` int(11) NOT NULL,
  `genre` varchar(50) NOT NULL,
  `director` varchar(100) NOT NULL,
  `cast_members` varchar(500) NOT NULL,
  `description` text NOT NULL,
  `poster` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movies`
--

LOCK TABLES `movies` WRITE;
/*!40000 ALTER TABLE `movies` DISABLE KEYS */;
INSERT INTO `movies` VALUES (1,'Starfall',2024,'Sci-Fi','Elena Vasquez','James Norton, Lupita Nyong\'o, Oscar Isaac','When a catastrophic event threatens to destroy Earth\'s last space station, a lone astronaut must navigate through a field of debris orbiting a dying planet to save what remains of humanity. A gripping tale of survival and sacrifice among the stars.','https://d2xsxph8kpxj0f.cloudfront.net/104678366/8Kdct4gHG4spTQPe5nDQce/movie-poster-1-FWCEJMNChmiQXYCfH9JZcx.webp'),(2,'Shadows of the City',2023,'Crime','L. Cohen','Ryan Gosling, Ana de Armas, Willem Dafoe','A retired detective is pulled back into the dark underworld of a rain-soaked city when a series of mysterious disappearances echo an unsolved case from his past. The deeper he digs, the more dangerous the truth becomes.','https://d2xsxph8kpxj0f.cloudfront.net/104678366/8Kdct4gHG4spTQPe5nDQce/movie-poster-2-3EZp6L9uS5pzkGC9cuQWHA.webp'),(3,'Aetheria: The Shattered Realms',2024,'Fantasy','Robert Glorson','Timothée Chalamet, Florence Pugh, Idris Elba','In a world where floating islands drift across an endless sky, a young warrior must unite the fractured kingdoms to face an ancient evil that threatens to consume all realms. An epic adventure forged in starlight and legend.','https://d2xsxph8kpxj0f.cloudfront.net/104678366/8Kdct4gHG4spTQPe5nDQce/movie-poster-3-Nn6MCKjp5LdXkyNUDSjJGc.webp'),(4,'Les Ombres de l\'Amour',2023,'Romance','Sophie Marceau','Léa Seydoux, Dev Patel, Marion Cotillard','Two strangers meet on a Parisian rooftop at twilight and share a single evening that changes both their lives forever. A tender and bittersweet exploration of love, loss, and the moments that define us.','https://d2xsxph8kpxj0f.cloudfront.net/104678366/8Kdct4gHG4spTQPe5nDQce/movie-poster-4-YM7Y7bqpweLLd6G7wFz2fT.webp'),(5,'The Last Horizon',2024,'Action','Michael Bay','Chris Hemsworth, Zendaya, John Boyega','When a rogue military unit seizes control of a nuclear submarine, an elite team of operatives must race against time across three continents to prevent a global catastrophe.','https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=400&h=600&fit=crop'),(6,'Whispers in the Dark',2023,'Horror','Ari Aster','Toni Collette, Jack Reynor, Mia Goth','A family moves into an old Victorian house in rural England, only to discover that the walls hold secrets from a dark past. As strange events unfold, they must confront the terrifying truth hidden within.','https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=400&h=600&fit=crop'),(7,'Velocity',2024,'Action','Chad Stahelski','Keanu Reeves, Halle Berry, Donnie Yen','A former street racer turned mechanic is forced back into the underground racing world when his brother\'s life hangs in the balance. High-octane thrills meet heart-pounding drama.','https://images.unsplash.com/photo-1485846234645-a62644f84728?w=400&h=600&fit=crop'),(8,'The Quiet Garden',2023,'Drama','Chloé Zhao','Saoirse Ronan, Adam Driver, Viola Davis','An elderly botanist reflects on a lifetime of love and regret as she tends to the garden she and her late husband built together. A meditative and deeply moving portrait of memory and resilience.','https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=400&h=600&fit=crop');
/*!40000 ALTER TABLE `movies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `movie_id` (`movie_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,1,1,5,'Absolutely stunning visuals and a story that kept me on the edge of my seat. One of the best films I have seen this year!','2026-03-01 05:40:29'),(2,1,2,4,'Great direction and solid performances from the entire cast. The pacing could be a bit tighter in the second act, but overall a very enjoyable experience.','2026-03-01 05:40:29'),(3,1,3,5,'This film is a masterpiece. The cinematography alone is worth the price of admission. Highly recommended for anyone who loves cinema.','2026-03-01 05:40:29'),(4,1,4,3,'Decent film with some memorable moments, but it did not quite live up to the hype for me. Still worth watching though.','2026-03-01 05:40:29'),(5,2,3,5,'Test review','2026-03-01 05:42:37'),(6,3,3,5,'TEstesterter tertert ert ert sda sdfgsdfhgs re yaserysdfhgsdfhs','2026-03-29 10:09:37');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Maria Santos','maria@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','2026-03-01 05:40:29'),(2,'Teste','teste@test.com','$2y$10$7d5IFIkAbD/1Sc0VlMWig.0isoxzZokGNke4yJND4xRTxYC6sEmX6','2026-03-01 05:41:50'),(3,'Teste','teste@teste.com','$2y$10$EVASr4PbljOpoLzMACNuvOQk4hUh77voo8gmtIkLmAbedtTwU8sb.','2026-03-29 10:09:02');
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

-- Dump completed on 2026-03-29 22:07:19
