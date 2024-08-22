-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 19, 2024 at 01:42 PM
-- Server version: 5.7.36
-- PHP Version: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tenant_heritage`
--

-- --------------------------------------------------------

--
-- Table structure for table `sub_topics`
--

DROP TABLE IF EXISTS `sub_topics`;
CREATE TABLE IF NOT EXISTS `sub_topics` (
  `sub_topic_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `topic_id` bigint(20) NOT NULL,
  `lesson_id` bigint(20) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `sub_topic` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `creator_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sub_topic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_topics`
--

INSERT INTO `sub_topics` (`sub_topic_id`, `topic_id`, `lesson_id`, `subject_id`, `sub_topic`, `created_by`, `creator_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 'sub topic 1', 4, 'TA', 'Active', '2024-07-18 03:02:45', '2024-07-18 03:02:45'),
(2, 1, 2, 1, 'sub topic2', 4, 'TA', 'Active', '2024-07-18 03:02:45', '2024-07-18 03:02:45'),
(3, 2, 3, 1, 'object', 4, 'TA', 'Active', '2024-07-18 06:16:58', '2024-07-18 06:16:58'),
(4, 2, 3, 1, 'salt', 4, 'TA', 'Active', '2024-07-18 06:16:58', '2024-07-18 06:16:58'),
(5, 2, 3, 1, 'mixture', 4, 'TA', 'Active', '2024-07-18 06:16:58', '2024-07-18 06:16:58'),
(6, 1, 2, 1, 'sub topic3', 4, 'TA', 'Active', '2024-07-19 07:12:35', '2024-07-19 07:22:30'),
(7, 1, 2, 1, 'sub topic4', 4, 'TA', 'Active', '2024-07-19 07:17:14', '2024-07-19 07:17:14'),
(8, 1, 2, 1, 'sub topic5', 4, 'TA', 'Active', '2024-07-19 07:25:07', '2024-07-19 07:25:07'),
(9, 1, 2, 1, 'sub topic5', 4, 'TA', 'Active', '2024-07-19 07:25:33', '2024-07-19 07:25:33');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
