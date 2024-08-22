-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 12, 2024 at 01:22 PM
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
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
CREATE TABLE IF NOT EXISTS `academic_years` (
  `academic_year_id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_year` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_year` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`academic_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`academic_year_id`, `academic_year`, `start_year`, `end_year`, `status`, `created_at`, `updated_at`) VALUES
(1, '2024-2025', '2024', '2025', 'Active', '2024-03-04 04:11:11', '2024-05-14 05:38:43'),
(2, '2023-2024', '2023', '2024', 'Inactive', '2024-04-11 03:58:12', '2024-05-14 05:38:43'),
(3, '2022-2023', '2022', '2023', 'Inactive', '2024-04-22 04:04:11', '2024-05-14 05:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE IF NOT EXISTS `attendances` (
  `attendance_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `attendance_date` date NOT NULL,
  `subject_id` int(11) NOT NULL,
  `lesson_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `is_present` tinyint(3) NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`attendance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`attendance_id`, `attendance_date`, `subject_id`, `lesson_id`, `user_id`, `is_present`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '2024-06-10', 1, 2, 1, 1, '', 4, '2024-06-09 23:41:29', '2024-06-09 23:41:29'),
(2, '2024-06-10', 1, 2, 2, 0, 'check', 4, '2024-06-09 23:41:30', '2024-06-09 23:41:30'),
(4, '2024-06-07', 1, 2, 1, 1, '', 4, '2024-06-10 00:08:48', '2024-06-10 00:08:48'),
(5, '2024-06-07', 1, 2, 2, 1, '', 4, '2024-06-10 00:08:48', '2024-06-10 00:08:48'),
(6, '2024-06-07', 1, 2, 3, 0, 'Sick leave', 4, '2024-06-10 00:08:48', '2024-06-10 00:08:48'),
(7, '2024-06-06', 1, 2, 1, 1, '', 4, '2024-06-10 00:10:20', '2024-06-10 00:10:20'),
(8, '2024-06-06', 1, 2, 2, 1, '', 4, '2024-06-10 00:10:20', '2024-06-10 00:10:20'),
(9, '2024-06-06', 1, 2, 3, 0, 'Sick leave', 4, '2024-06-10 00:10:20', '2024-06-10 00:10:20'),
(13, '2024-06-05', 1, 6, 1, 1, '', 4, '2024-06-10 02:04:23', '2024-06-10 02:04:23'),
(14, '2024-06-05', 1, 6, 2, 0, '', 4, '2024-06-10 02:04:23', '2024-06-10 02:04:23'),
(15, '2024-06-05', 1, 6, 3, 0, '', 4, '2024-06-10 02:04:23', '2024-06-10 02:04:23');

-- --------------------------------------------------------

--
-- Table structure for table `batch_types`
--

DROP TABLE IF EXISTS `batch_types`;
CREATE TABLE IF NOT EXISTS `batch_types` (
  `batch_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `amount` varchar(20) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`batch_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `batch_types`
--

INSERT INTO `batch_types` (`batch_type_id`, `name`, `amount`, `date`, `status`) VALUES
(1, 'one:one', '40', '2023-04-26 06:07:12', 'Active'),
(2, 'group', '80', '2023-04-26 06:07:12', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'IT Services', 'Active', '2024-03-06 02:02:44', '2024-03-06 02:31:55'),
(2, 'Library', 'Active', '2024-03-06 02:31:41', '2024-03-06 02:31:41'),
(3, 'Year 7', 'Active', '2024-06-03 00:00:15', '2024-06-03 00:00:15'),
(4, 'GCSE 8', 'Active', '2024-06-03 00:00:15', '2024-06-03 00:00:15');

-- --------------------------------------------------------

--
-- Table structure for table `discount_coupons`
--

DROP TABLE IF EXISTS `discount_coupons`;
CREATE TABLE IF NOT EXISTS `discount_coupons` (
  `discount_coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `discount_type` enum('A','P') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` float(10,2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `use_per_user` int(11) DEFAULT NULL,
  `no_uses` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`discount_coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `employees`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
`user_profile_id` bigint(20)
,`user_id` bigint(20)
,`first_name` varchar(150)
,`middle_name` varchar(150)
,`last_name` varchar(150)
,`short_name` varchar(20)
,`ni_number` varchar(150)
,`end_date_id` date
,`id_file` varchar(255)
,`dbs_certificate_file` varchar(255)
,`end_date_dbs` date
,`address` varchar(255)
,`about` varchar(255)
,`birthday` date
,`gender` varchar(50)
,`blood_group` varchar(20)
,`batch_type_id` int(11)
,`department_id` int(11)
,`parent_name` varchar(255)
,`parent_phone` varchar(20)
,`parent_email` varchar(191)
,`created_at` timestamp
,`updated_at` timestamp
,`tenant_id` bigint(20)
,`role` enum('A','T','S','TA','OU','P')
,`email` varchar(191)
,`phone` varchar(100)
,`user_logo` varchar(512)
,`status` enum('Active','Inactive')
,`department_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `examinations`
--

DROP TABLE IF EXISTS `examinations`;
CREATE TABLE IF NOT EXISTS `examinations` (
  `examination_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `examination_type` enum('Q','A','O') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Q' COMMENT 'quiz, assesment, other',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year_group_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `lesson_id` bigint(20) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `creator_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `total_time` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`examination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `examinations`
--

INSERT INTO `examinations` (`examination_id`, `examination_type`, `name`, `year_group_id`, `subject_id`, `lesson_id`, `created_by`, `creator_type`, `start_datetime`, `end_datetime`, `total_time`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Q', 'First quiz by teacher1 API', 1, 1, 2, 4, 'TU-T', NULL, NULL, '0:6:0', 'Active', '2024-03-13 06:00:51', '2024-03-26 01:36:41'),
(2, 'Q', 'mmmmm', 1, 1, 1, 5, 'TU-T', NULL, NULL, '1', 'In Design', '2024-03-13 06:09:34', '2024-03-14 02:29:28'),
(3, 'Q', 'Test Quiz interface', 1, 1, 2, 4, 'TU-T', NULL, NULL, '0:17:0', 'Active', '2024-03-13 07:15:20', '2024-05-10 04:41:19'),
(4, 'Q', 'Quiz 2', NULL, NULL, NULL, 4, 'TU-T', NULL, NULL, NULL, 'Inactive', '2024-03-13 07:15:57', '2024-03-26 08:07:10'),
(5, 'A', 'Test assesment', 1, 1, 2, 4, 'TU-T', NULL, NULL, '0:25:0', 'Active', '2024-04-08 02:26:08', '2024-04-09 00:22:51'),
(32, 'A', 'assessment 2', 1, 1, 2, 4, 'TU-T', NULL, NULL, '1:23:0', 'Active', '2024-05-09 02:01:17', '2024-07-05 06:15:38'),
(33, 'A', 'assessment single question only', 1, 1, 2, 4, 'TU-T', NULL, NULL, '0:36:0', 'Active', '2024-05-09 02:07:07', '2024-07-11 01:21:41'),
(34, 'A', 'linked only assesment', 1, 1, 2, 4, 'TU-T', NULL, NULL, '1:19:0', 'In Design', '2024-05-09 02:07:56', '2024-05-10 01:19:42'),
(35, 'A', 'Test assesment add', 1, 1, 2, 4, 'TU-T', NULL, NULL, '0:30:0', 'Active', '2024-05-09 06:51:47', '2024-05-10 04:56:53'),
(36, 'Q', 'Single Question Quiz', 1, 1, 2, 4, 'TU-T', NULL, NULL, '0:5:0', 'Active', '2024-05-15 02:59:04', '2024-05-15 03:01:04'),
(37, 'Q', 'New quiz', NULL, NULL, NULL, 4, 'TU-T', NULL, NULL, NULL, 'In Design', '2024-05-22 02:29:36', '2024-05-22 02:29:36'),
(38, 'Q', 'test q', 1, 1, 2, 4, 'TU-T', NULL, NULL, '0:2:0', 'In Design', '2024-07-12 05:41:44', '2024-07-12 05:44:20');

-- --------------------------------------------------------

--
-- Table structure for table `examination_questions`
--

DROP TABLE IF EXISTS `examination_questions`;
CREATE TABLE IF NOT EXISTS `examination_questions` (
  `examination_question_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `examination_id` bigint(20) NOT NULL,
  `question_id` bigint(20) NOT NULL,
  `parent_examination_question_id` bigint(20) NOT NULL DEFAULT '0',
  `linked_question` tinyint(4) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL,
  `time_inseconds` int(11) NOT NULL,
  `point` decimal(10,2) NOT NULL,
  `question_info` json DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`examination_question_id`),
  KEY `examination_id` (`examination_id`),
  KEY `question_fk` (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='examination question mapping with question bank';

--
-- Dumping data for table `examination_questions`
--

INSERT INTO `examination_questions` (`examination_question_id`, `examination_id`, `question_id`, `parent_examination_question_id`, `linked_question`, `page_id`, `time_inseconds`, `point`, `question_info`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, 0, 1, 60, '2.00', '{\"level\": \"low\", \"source\": \"Q\", \"options\": [{\"is_correct\": 0, \"option_value\": \"opt 1 e\"}, {\"is_correct\": 0, \"option_value\": \"opt 2\"}, {\"is_correct\": 0, \"option_value\": \"opt 3\"}, {\"is_correct\": 1, \"option_value\": \"opt 4\"}], \"question\": \"This is first question from api of examination question\", \"question_type\": \"radio\", \"require_file_upload\": 0}', '2024-03-14 01:24:47', '2024-03-14 02:38:17'),
(2, 2, 2, 0, 0, 1, 80, '2.00', '{\"level\": \"low\", \"source\": \"Q\", \"options\": [{\"is_correct\": 0, \"option_value\": \"opt 1 e\"}, {\"is_correct\": 0, \"option_value\": \"opt 2\"}, {\"is_correct\": 0, \"option_value\": \"opt 3\"}, {\"is_correct\": 1, \"option_value\": \"opt 4\"}], \"question\": \"This is first question from api of examination question\", \"question_type\": \"radio\", \"require_file_upload\": 0}', '2024-03-14 01:28:02', '2024-03-14 02:29:28'),
(4, 1, 4, 0, 0, 1, 60, '2.00', '{\"level\": \"low\", \"source\": \"Q\", \"options\": [{\"is_correct\": \"0\", \"option_value\": \"opt-val 1\"}, {\"is_correct\": \"0\", \"option_value\": \"opt-val 2\"}, {\"is_correct\": \"1\", \"option_value\": \"opt-val 3\"}, {\"is_correct\": \"0\", \"option_value\": \"opt-val 4\"}, {\"is_correct\": \"0\", \"option_value\": \"opt-val 5\"}], \"question\": \"<p>This is third question from api of examination question?</p>\", \"question_type\": \"radio\", \"require_file_upload\": \"0\"}', '2024-03-14 03:06:49', '2024-03-19 06:08:56'),
(6, 1, 7, 0, 0, 1, 60, '2.00', '{\"level\": \"low\", \"source\": \"Q\", \"options\": [{\"is_correct\": \"0\", \"option_value\": \"ans 1\"}, {\"is_correct\": \"0\", \"option_value\": \"ans 2\"}, {\"is_correct\": \"1\", \"option_value\": \"ans 3\"}, {\"is_correct\": \"0\", \"option_value\": \"ans 4\"}], \"question\": \"<p>page 1 3rd</p>\", \"question_type\": \"select\", \"require_file_upload\": \"0\"}', '2024-03-19 00:05:50', '2024-03-19 00:05:50'),
(7, 1, 8, 0, 0, 1, 60, '2.00', '{\"level\": \"medium\", \"source\": \"Q\", \"options\": [{\"is_correct\": \"0\", \"option_value\": \"Droupadi Murmu\"}, {\"is_correct\": \"0\", \"option_value\": \"APJ Abdul Kalam\"}, {\"is_correct\": \"0\", \"option_value\": \"Trunk of Tree\"}, {\"is_correct\": \"1\", \"option_value\": \"Environtmentologist\"}], \"question\": \"<p>Test question?</p>\", \"question_type\": \"checkbox\", \"require_file_upload\": \"0\"}', '2024-03-19 02:23:48', '2024-03-19 06:08:31'),
(8, 1, 11, 0, 0, 1, 60, '5.00', '{\"level\": \"medium\", \"source\": \"Q\", \"question\": \"<p>Free text question with no option?</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-03-19 06:25:33', '2024-03-19 06:38:53'),
(9, 1, 14, 0, 0, 2, 60, '12.00', '{\"level\": \"medium\", \"source\": \"Q\", \"question\": \"<p>Describe process of photosynthesis?</p>\", \"question_type\": \"text\", \"require_file_upload\": \"1\"}', '2024-03-25 23:54:53', '2024-03-25 23:59:53'),
(10, 5, 34, 0, 0, 1, 300, '10.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>What is Acid, Base &amp; Salts?</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-04-08 02:26:08', '2024-04-09 00:22:51'),
(11, 5, 35, 0, 0, 1, 120, '3.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>What is the boiling point of any liquid?</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-04-08 02:26:08', '2024-04-09 00:22:51'),
(12, 5, 36, 0, 0, 1, 180, '2.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>What is the boiling point of water and sulphuric acid?</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-04-08 02:26:08', '2024-04-09 00:22:51'),
(13, 5, 37, 0, 0, 1, 900, '10.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p><img alt=\\\"\\\" src=\\\"/public/uploads/cms_images/image5.png\\\" style=\\\"width: 500px; height: 422px;\\\" /></p>\\r\\n\\r\\n<p>Define the marked parts of the Cell?</p>\", \"question_type\": \"text\", \"require_file_upload\": \"1\"}', '2024-04-09 00:22:19', '2024-04-09 00:22:51'),
(29, 32, 53, 0, 0, 1, 660, '11.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>S1</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-09 02:01:17', '2024-07-05 06:15:38'),
(30, 32, 54, 0, 1, 1, 0, '0.00', '{\"level\": \"\", \"source\": \"A\", \"question\": \"<p>L1</p>\", \"question_type\": \"linked\", \"require_file_upload\": \"0\"}', '2024-05-09 02:01:17', '2024-07-05 06:15:38'),
(31, 32, 55, 30, 0, 1, 960, '16.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>L1 S1</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-09 02:01:17', '2024-07-05 06:15:38'),
(32, 32, 56, 0, 0, 1, 780, '13.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>S2</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-09 02:01:17', '2024-07-05 06:15:39'),
(33, 33, 57, 0, 0, 1, 660, '11.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>S1 ed</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-09 02:07:07', '2024-07-11 01:21:41'),
(34, 33, 58, 0, 0, 1, 720, '12.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>S2 ed</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-09 02:07:07', '2024-07-11 01:21:41'),
(35, 34, 59, 0, 1, 1, 0, '0.00', '{\"level\": \"\", \"source\": \"A\", \"question\": \"<p>L1 edit</p>\", \"question_type\": \"linked\", \"require_file_upload\": \"0\"}', '2024-05-09 02:07:56', '2024-05-10 01:20:27'),
(36, 34, 60, 35, 0, 1, 660, '11.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>L1 S1 edit</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-09 02:07:56', '2024-05-10 01:20:27'),
(37, 35, 61, 0, 1, 1, 0, '0.00', '{\"level\": \"\", \"source\": \"A\", \"question\": \"<p>L1</p>\", \"question_type\": \"linked\", \"require_file_upload\": \"0\"}', '2024-05-09 06:51:47', '2024-05-10 04:56:53'),
(38, 35, 62, 37, 0, 1, 660, '11.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>L1 S1</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-09 06:51:47', '2024-05-10 04:56:53'),
(39, 35, 63, 37, 0, 1, 240, '4.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>L1 S2</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-09 07:00:28', '2024-05-10 04:56:53'),
(40, 35, 64, 37, 0, 1, 600, '10.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>L1 S3 35</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-10 00:04:09', '2024-05-10 04:56:53'),
(41, 34, 65, 35, 0, 1, 600, '10.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>L1 S2</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-10 00:25:40', '2024-05-10 01:20:27'),
(42, 34, 66, 0, 1, 1, 0, '0.00', '{\"level\": \"\", \"source\": \"A\", \"question\": \"<p>L2</p>\", \"question_type\": \"linked\", \"require_file_upload\": \"0\"}', '2024-05-10 00:27:41', '2024-05-10 01:20:27'),
(43, 34, 67, 42, 0, 1, 780, '13.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>L2 S1</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-10 00:27:41', '2024-05-10 01:20:27'),
(44, 34, 68, 42, 0, 1, 840, '14.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>L2 S2</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-10 00:39:59', '2024-05-10 01:20:27'),
(45, 34, 69, 0, 1, 1, 0, '0.00', '{\"level\": \"\", \"source\": \"A\", \"question\": \"<p>L3</p>\", \"question_type\": \"linked\", \"require_file_upload\": \"0\"}', '2024-05-10 00:39:59', '2024-05-10 01:20:27'),
(46, 34, 70, 45, 0, 1, 900, '15.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>L3 S1</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-10 00:39:59', '2024-05-10 01:20:27'),
(47, 34, 71, 0, 1, 1, 0, '0.00', '{\"level\": \"\", \"source\": \"A\", \"question\": \"<p>L4</p>\", \"question_type\": \"linked\", \"require_file_upload\": \"0\"}', '2024-05-10 01:19:42', '2024-05-10 01:20:27'),
(48, 34, 72, 47, 0, 1, 960, '16.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>L4 S1</p>\\r\\n\\r\\n<p><img alt=\\\"\\\" src=\\\"/public/uploads/cms_images/download%20(2).png\\\" style=\\\"width: 100px; height: 100px;\\\" /></p>\", \"question_type\": \"text\", \"require_file_upload\": \"1\"}', '2024-05-10 01:19:42', '2024-05-10 01:20:27'),
(49, 33, 73, 0, 0, 1, 780, '13.00', '{\"level\": \"medium\", \"source\": \"A\", \"question\": \"<p>S3</p>\", \"question_type\": \"text\", \"require_file_upload\": \"1\"}', '2024-05-10 01:24:35', '2024-07-11 01:21:42'),
(51, 32, 75, 30, 0, 1, 900, '15.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>L1 S2</p>\", \"question_type\": \"text\", \"require_file_upload\": \"1\"}', '2024-05-10 01:27:15', '2024-07-05 06:15:38'),
(52, 32, 76, 30, 0, 1, 840, '14.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>L1 S3</p>\", \"question_type\": \"text\", \"require_file_upload\": \"1\"}', '2024-05-10 03:35:53', '2024-07-05 06:15:38'),
(55, 32, 79, 30, 0, 1, 840, '14.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>L1 S3</p>\", \"question_type\": \"text\", \"require_file_upload\": \"1\"}', '2024-05-10 04:24:18', '2024-07-05 06:15:39'),
(56, 3, 80, 0, 0, 1, 120, '5.00', '{\"level\": \"low\", \"source\": \"Q\", \"options\": [{\"is_correct\": \"0\", \"option_value\": \"Detergent used for washing clothes\"}, {\"is_correct\": \"0\", \"option_value\": \"Alkaline liquid used by different industries\"}, {\"is_correct\": \"0\", \"option_value\": \"Photosynthesis by product\"}, {\"is_correct\": \"1\", \"option_value\": \"Light produced by the Sun\"}], \"question\": \"<p>What is Sunlight?</p>\", \"question_type\": \"radio\", \"require_file_upload\": \"0\"}', '2024-05-10 04:39:50', '2024-05-10 04:39:50'),
(57, 3, 81, 0, 0, 1, 900, '10.00', '{\"level\": \"low\", \"source\": \"Q\", \"question\": \"<p>Define photosynthesis and&nbsp;Upload the diagram?</p>\", \"question_type\": \"text\", \"require_file_upload\": \"1\"}', '2024-05-10 04:41:07', '2024-05-10 04:41:07'),
(58, 35, 82, 0, 0, 1, 300, '5.00', '{\"level\": \"low\", \"source\": \"A\", \"question\": \"<p>Single question 1</p>\", \"question_type\": \"text\", \"require_file_upload\": \"0\"}', '2024-05-10 04:51:59', '2024-05-10 04:56:53'),
(59, 36, 83, 0, 0, 1, 300, '5.00', '{\"level\": \"low\", \"source\": \"Q\", \"options\": [{\"is_correct\": \"0\", \"option_value\": \"ans 1\"}, {\"is_correct\": \"0\", \"option_value\": \"ans 2\"}, {\"is_correct\": \"1\", \"option_value\": \"ans 3\"}, {\"is_correct\": \"0\", \"option_value\": \"ans 4\"}], \"question\": \"<p>This is test single question quiz for reported error?</p>\", \"question_type\": \"radio\", \"require_file_upload\": \"0\"}', '2024-05-15 03:00:54', '2024-05-15 03:00:54'),
(60, 38, 85, 0, 0, 1, 120, '2.00', '{\"level\": \"low\", \"source\": \"Q\", \"options\": [{\"is_correct\": \"0\", \"option_value\": \"a\"}, {\"is_correct\": \"0\", \"option_value\": \"b\"}, {\"is_correct\": \"1\", \"option_value\": \"c\"}, {\"is_correct\": \"0\", \"option_value\": \"d\"}], \"question\": \"<p>qes1</p>\", \"question_type\": \"radio\", \"require_file_upload\": \"0\"}', '2024-07-12 05:44:20', '2024-07-12 05:44:20');

-- --------------------------------------------------------

--
-- Table structure for table `external_users`
--

DROP TABLE IF EXISTS `external_users`;
CREATE TABLE IF NOT EXISTS `external_users` (
  `external_user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invited_for` enum('SG') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SG',
  `token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invited_by` bigint(20) NOT NULL DEFAULT '0',
  `invite_token` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`external_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `external_users`
--

INSERT INTO `external_users` (`external_user_id`, `email`, `name`, `invited_for`, `token`, `entity_id`, `invited_by`, `invite_token`, `status`, `created_at`, `updated_at`) VALUES
(1, 'dpsstudent1@mailinator.com', 'dpsstudent1', 'SG', 'NjVxSkdBa2U3MHViend4Yk9EZ1k4MXo0S2x0ZFZ4VjVPbUtSYitIMHZmVjlFSGV0WHlWbVNJMTRTbUg5ZkZlUQ==', 'T2N4ZmdQeDJjaHNIZzR4S1A1OHc1UT09', 1, 'bi93SWdod1pNSG9iNWVhZEZXR0I4dFQyUExMMUNQQTI1QWtBbTU3NXJFcTF3VlloWStGcE5JYXp0bkFTRzdKZndFUStVT1NOV1ZwMU84OUtEK1FLWGx1bHZFZW1pYWx4clhYWlFGNjQ2Wld0N2svdjU2SHIyempHTVFveUxhSlRYQlBwWGsrSFlETXNlU0pKcUxhS0JqL3RONE1hcXBPYkFjQ05hSHQ3d2tCSHNOWmZHa1RweTZ0THRoeUFsUDA5cEk0NjJ2Vm5FWFl1T2x6MUxRZzFSNStUOHk0cW9XQ1g3NW5tRWpSUWZFU2FNc2NsSUZUejcwZytvNkxYS3I3RkVMRStTVTdsV25idzQ1MDJSOWVjeElDZXBPZGFBaDZneHFrQXVCYlVWemc3emQxTDl4cHlobStENm5LcExrczFIZjl0VXhLNGkxNzd5MjNPcWRoZStZR2Z6T3pFem05U1FxcmpyQnB0QWl6R0JjV3dXSEZBQlEwMmtMZlVDVkpNQW93MHNTWE1penJmMjBnVllwNUFuWk9YbWwzKzRkY1N0cSthSENWSEM1Rk5jVldGajk3MlAxdDREYXdvOWZkemRwc2ZHL3dDWVpJdENvemV6bU1zYzFVcldsb2QvRWdDK2hNN0R6QXlQbjN6eHZSUUtYdTkwRFRKV3dIc01LclhRcWhGc1F6dzVtMno3bktVd3pCMkphNkhkMWpHY3RaU2dqQ1B2TTRsNVNNZWNLaHVuSm1aVytNUTgxMytNTlZOSlRwUDhyVkZ0cWhQVXRqSnZLOHgyaStUMGQ3MndWRjZnaC9zMnhXZHZPdEQ5R3JydjFPY21iVllwb3QxRm01OWd2Q2ZOelFLUktMaHZzOVVsNGU3VHV5akxaRHR3dlc2ckFDbXE1eUhOYktMNzM1OHkySzhGTlpuWW51R3ZjMjRyR1hiNElRK09xempyN2FVV3F3MEVRZmdhcnl2ZklWNWFiMlpSbjQ4WjVVRm4rM0hLellnR0hnL0F2cDVwRFVCSE9KSTB4bEZaNUFad0tVczVNZUZZb2hjOGJyTU9BY1V2QnZpODBDVTZzb2daS0wyUWJRNzdCa2tlSWM4ZFI5MHgxQndIWXROa0JoZEEzbVlSOXFnV3F2R2huWGVZKzBOcjJoMmtWRDhSQnZzVG9hT0pXTU9lelZSaFljTHZPN3k5YzVCeTZnUktFT0lpWkU4M0VIS3d1R0NXKzdjK25pWE93T1lNaFhtNWpRPQ==', 'Active', '2024-04-18 05:25:27', '2024-04-18 05:25:27'),
(2, 'dpsstudent2@mailinator.com', 'DPS student 2', 'SG', 'ZEFoMWF1dkpzazVOaStzSGJBN2F5MXo0S2x0ZFZ4VjVPbUtSYitIMHZmVjlFSGV0WHlWbVNJMTRTbUg5ZkZlUQ==', 'T2N4ZmdQeDJjaHNIZzR4S1A1OHc1UT09', 1, 'bi93SWdod1pNSG9iNWVhZEZXR0I4dFQyUExMMUNQQTI1QWtBbTU3NXJFcTF3VlloWStGcE5JYXp0bkFTRzdKZndFUStVT1NOV1ZwMU84OUtEK1FLWGx1bHZFZW1pYWx4clhYWlFGNjQ2Wld0N2svdjU2SHIyempHTVFveUxhSlRYQlBwWGsrSFlETXNlU0pKcUxhS0JqL3RONE1hcXBPYkFjQ05hSHQ3d2tCSHNOWmZHa1RweTZ0THRoeUFsUDA5cEk0NjJ2Vm5FWFl1T2x6MUxRZzFSNStUOHk0cW9XQ1g3NW5tRWpSUWZFU2FNc2NsSUZUejcwZytvNkxYS3I3RkVMRStTVTdsV25idzQ1MDJSOWVjeElDZXBPZGFBaDZneHFrQXVCYlVWemc3emQxTDl4cHlobStENm5LcExrczFIZjl0VXhLNGkxNzd5MjNPcWRoZStZR2Z6T3pFem05U1FxcmpyQnB0QWl6R0JjV3dXSEZBQlEwMmtMZlVDVkpNQW93MHNTWE1penJmMjBnVllwNUFuWk9YbWwzKzRkY1N0cSthSENWSEM1Rk5jVldGajk3MlAxdDREYXdvOWZkemRwc2ZHL3dDWVpJdENvemV6bU1zYzFVcldsb2QvRWdDK2hNN0R6QXlQbjN6eHZSUUtYdTkwRFRKV3dIc01LclhRcWhGc1F6dzVtMno3bktVd3pCMkphNkhkMWpHY3RaU2dqQ1B2TTRsNVNNZWNLaHVuSm1aVytNUTgxMytNTlZOSlRwUDhyVkZ0cWhQVXRqSnZLOHgyaStUMGQ3MndWRjZnaC9zMnhXZHZPdEQ5R3JydjFPY21iVllwb3QxRm01OWd2Q2ZOelFLUktMaHZzOVVsNGU3VHV5akxaRHR3dlc2ckFDbXE1eUhOYktMNzM1OHkySzhGTlpuWW51R3ZjMjRyR1hiNElRK09xempyN2FVV3F3MEVRZmdhcnl2ZklWNWFiMlpSbjQ4WjVVRm4rM0hLellnR0hnL0F2cDVwRFVCSE9KSTB4bEZaNUFad0tVczVNZUZZb2hjOGJyTU9BY1V2QnZpODBDVTZzb2daS0wyUWJRNzdCa2tlSWM4ZFI5MHgxQndIWXROa0JoZEEzbVlSOXFnV3F2R2huWGVZKzBOcjJoMmtWRDhSQnZzVG9hT0pXTU9lelZSaFljTHZPN3k5YzVCeTZnUktFT0lpWkU4M0VIS3d1R0NXKzdjK25pWE93T1lNaFhtNWpRPQ==', 'Active', '2024-04-19 05:00:40', '2024-04-19 05:03:34'),
(4, 'dpsstudent1@mailinator.com', 'DPS student 1', 'SG', 'NjVxSkdBa2U3MHViend4Yk9EZ1k4MXo0S2x0ZFZ4VjVPbUtSYitIMHZmVjlFSGV0WHlWbVNJMTRTbUg5ZkZlUQ==', 'T2lnT2M0UUFFaURZSmZDbFk4WVRMQT09', 1, 'bi93SWdod1pNSG9iNWVhZEZXR0I4anpqK0F3ZGhJenJQcGtpSU9xU1VweSs1UldyVzU4UHB6S2FBTFM2SnBaa3dFUStVT1NOV1ZwMU84OUtEK1FLWGpNV2VQM0JjWjhTWXdFQ0h5R3BWRTJrdThRdkdCT01QaWdVRXprcE4vWUVQbnluWG9jdlFsOGh6TzQ2c3NScTJGUXVaRENRZHRWQVFuaDZ6TlhwWnZKN29MVEt0OVpLSDlaQW00ckpERUUxMnRaN0crbUViTWFQMWluU0J0bEUvVndnbnhKVERqdzRPSEUwaE5UNlZxeDhqRDE2eTF2Vzh0SUFvZHpvQ2lLY0VPZVE5aWRjdXIzTEwrcVM5TUJkQkxYZ1B1T0wyYjM4UDBvY1EyakROMituZ1lxako3eDJQVEEvTExHWTFRSkwvOFNsTkM0Yk1HandFZXBKNmI5WVR5M3NaWjhzZ2VJSmx3NkpSRTJ0VGxpckF1dm9OdU1hWVZZZ3ppTkZMWlpx', 'Invitation', '2024-04-19 06:00:33', '2024-04-19 06:00:33'),
(5, 'dpsstudent1@mailinator.com', 'dpsstudent1', 'SG', 'NjVxSkdBa2U3MHViend4Yk9EZ1k4MXo0S2x0ZFZ4VjVPbUtSYitIMHZmV1N3ZlZkN2syRmlYUE1QTytMSGduZktZRHdmc2dGSTNoTDdqZ1JFSXVGdkMzelNmLyt3Y2ZPRTRWTm9NRzNEWDQ9', 'T2lnT2M0UUFFaURZSmZDbFk4WVRMQT09', 1, 'bi93SWdod1pNSG9iNWVhZEZXR0I4anpqK0F3ZGhJenJQcGtpSU9xU1VweSs1UldyVzU4UHB6S2FBTFM2SnBaa3dFUStVT1NOV1ZwMU84OUtEK1FLWGpNV2VQM0JjWjhTWXdFQ0h5R3BWRTJrdThRdkdCT01QaWdVRXprcE4vWUVQbnluWG9jdlFsOGh6TzQ2c3NScTJGUXVaRENRZHRWQVFuaDZ6TlhwWnZKN29MVEt0OVpLSDlaQW00ckpERUUxMnRaN0crbUViTWFQMWluU0J0bEUvVndnbnhKVERqdzRPSEUwaE5UNlZxeDhqRDE2eTF2Vzh0SUFvZHpvQ2lLY0VPZVE5aWRjdXIzTEwrcVM5TUJkQkxYZ1B1T0wyYjM4UDBvY1EyakROMituZ1lxako3eDJQVEEvTExHWTFRSkwvOFNsTkM0Yk1HandFZXBKNmI5WVR5M3NaWjhzZ2VJSmx3NkpSRTJ0VGxpckF1dm9OdU1hWVZZZ3ppTkZMWlpx', 'Active', '2024-04-19 06:08:18', '2024-04-19 06:10:33');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
CREATE TABLE IF NOT EXISTS `grades` (
  `grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `grade` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_value` float NOT NULL,
  `max_value` float NOT NULL,
  `effective_date` date NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`grade_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `grade`, `min_value`, `max_value`, `effective_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'A*', 90, 100, '2024-04-29', 'Active', '2024-04-29 00:10:47', '2024-05-16 00:45:23'),
(2, 'A', 80, 90, '2024-04-29', 'Active', '2024-04-29 00:14:46', '2024-04-29 03:45:54'),
(3, 'B', 65, 80, '2024-04-29', 'Active', '2024-04-29 03:46:42', '2024-04-29 03:46:42'),
(4, 'C', 50, 65, '2024-04-29', 'Active', '2024-04-29 03:47:56', '2024-04-29 03:47:56'),
(5, 'D', 0, 50, '2024-04-29', 'Active', '2024-04-29 03:48:22', '2024-04-29 03:48:22'),
(6, 'A*', 90, 100, '2024-05-31', 'Active', '2024-06-02 23:58:59', '2024-06-02 23:58:59');

-- --------------------------------------------------------

--
-- Table structure for table `indicators`
--

DROP TABLE IF EXISTS `indicators`;
CREATE TABLE IF NOT EXISTS `indicators` (
  `indicator_id` int(11) NOT NULL AUTO_INCREMENT,
  `indicator_type` enum('O','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'O' COMMENT 'O=Ofsted,\r\nN=NTP',
  `indicator_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_table` varchar(151) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`indicator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `indicators`
--

INSERT INTO `indicators` (`indicator_id`, `indicator_type`, `indicator_name`, `data_table`, `status`, `created_at`, `updated_at`) VALUES
(1, 'O', 'Academia', 'ofstead_academias', 'Inactive', '2024-04-24 07:06:47', NULL),
(2, 'O', 'Admissions & Enrolments', 'ofstead_enrollments', 'Inactive', '2024-04-24 07:10:15', NULL),
(3, 'O', 'Faculty & Staff', 'ofstead_faculties', 'Inactive', '2024-04-24 07:10:15', NULL),
(4, 'O', 'Facilities & Resources', 'ofstead_facilities', 'Inactive', '2024-04-24 07:10:15', NULL),
(5, 'O', 'Transportation & Housing', 'ofstead_transports', 'Inactive', '2024-04-24 07:10:15', NULL),
(6, 'O', 'Finances & accounting', 'ofstead_finances', 'Active', '2024-04-24 07:10:15', NULL),
(7, 'O', 'Behaviour', 'ofstead_behaviours', 'Inactive', '2024-04-24 07:10:15', NULL),
(8, 'O', 'Technology', 'ofstead_technologies', 'Inactive', '2024-04-24 07:10:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `lesson_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `lesson_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lesson_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `creator_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`lesson_id`),
  KEY `lesson_subject` (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`lesson_id`, `subject_id`, `lesson_number`, `lesson_name`, `created_by`, `creator_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, '1', 'Salts', 4, 'TA', 'Active', '2024-03-04 05:09:31', '2024-03-04 05:09:31'),
(2, 1, '1', 'Physical Analysis & Fundamentals', 4, 'TA', 'Active', '2024-03-04 05:09:56', '2024-06-26 23:57:45'),
(3, 1, '2', 'Lesson 2', 4, 'TA', 'Active', '2024-05-14 01:57:08', '2024-06-26 23:58:23'),
(4, 1, '3', 'Lesson 3', 4, 'TA', 'Active', '2024-05-14 01:57:30', '2024-06-26 23:58:41'),
(5, 7, '1', 'Pre bio', 4, 'TA', 'Active', '2024-05-22 04:10:47', '2024-06-26 23:58:02'),
(6, 1, '4', 'import lesson 4', 4, 'TA', 'Active', '2024-05-30 02:06:00', '2024-06-26 23:58:57'),
(7, 1, '5', 'import lesson 5', 4, 'TA', 'Active', '2024-05-30 02:06:00', '2024-06-26 23:59:18'),
(8, 1, '6', 'import lesson 6', 4, 'TA', 'Active', '2024-05-30 02:06:00', '2024-06-26 23:59:38'),
(9, 1, '7', 'import lesson 7', 4, 'TA', 'Active', '2024-05-30 02:06:00', '2024-06-27 00:01:47'),
(10, 1, '8', 'import lesson 8', 4, 'TA', 'Active', '2024-05-30 02:06:00', '2024-06-27 00:05:11'),
(11, 4, '1', 'import lesson 1', 4, 'TA', 'Active', '2024-05-30 02:54:54', '2024-06-27 00:00:33'),
(12, 4, '2', 'import lesson 2', 4, 'TA', 'Active', '2024-05-30 02:54:54', '2024-06-27 00:00:48'),
(13, 4, '3', 'import lesson 3', 4, 'TA', 'Active', '2024-05-30 02:54:54', '2024-06-27 00:01:15'),
(14, 4, '4', 'import lesson 4', 4, 'TA', 'Active', '2024-05-30 02:54:54', '2024-06-27 00:06:18'),
(15, 4, '5', 'import lesson 5', 4, 'TA', 'Active', '2024-05-30 02:54:54', '2024-06-27 00:06:48'),
(16, 2, '2', 'Heat', 4, 'TA', 'Active', '2024-06-27 02:01:08', '2024-06-27 02:05:38'),
(17, 2, '3', 'Energies', 4, 'TU', 'Active', '2024-06-27 05:24:30', '2024-06-27 06:23:38'),
(18, 2, '4', 'Water', 4, 'TU', 'Active', '2024-06-27 05:28:32', '2024-06-27 05:28:32'),
(24, 2, '5', 'import lesson 4', 4, 'TU', 'Active', '2024-06-27 05:35:02', '2024-06-27 05:35:02'),
(25, 2, '6', 'import lesson 5', 4, 'TU', 'Active', '2024-06-27 05:35:02', '2024-06-27 05:35:02'),
(26, 2, '7', 'import lesson 6', 4, 'TU', 'Active', '2024-06-27 05:35:02', '2024-06-27 05:35:02'),
(27, 2, '8', 'import lesson 7', 4, 'TU', 'Active', '2024-06-27 05:35:02', '2024-06-27 05:35:02'),
(28, 2, '9', 'import lesson 8', 4, 'TU', 'Active', '2024-06-27 05:35:02', '2024-06-27 05:35:02');

-- --------------------------------------------------------

--
-- Table structure for table `libraries`
--

DROP TABLE IF EXISTS `libraries`;
CREATE TABLE IF NOT EXISTS `libraries` (
  `library_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint(20) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`library_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `libraries`
--

INSERT INTO `libraries` (`library_id`, `lesson_id`, `title`, `content_type`, `content_file`, `content_url`, `created_by`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'test', 'V', 'uploads/library/1/2/HIM9LIYGSBDDVRXwvpAOaC4prnM72gd2Q1zcne2k.mp4', '', 4, 'Active', '2024-05-27 23:46:40', '2024-05-27 23:46:40'),
(2, 2, 'ppt', 'P', 'uploads/library/1/2/ztAp8VT8qz5dWYKQj0Y1DotvTUfqKl7CEPjrt7XP.pptx', '', 4, 'Active', '2024-05-27 23:49:58', '2024-05-27 23:49:58'),
(3, 2, 'rem', 'U', NULL, 'https://www.youtube.com/embed/1DX8uQkXt7g', 4, 'Inactive', '2024-05-27 23:51:36', '2024-06-05 02:56:00'),
(4, 2, 'excel note', 'N', 'uploads/library/1/2/Lwh0s3WjTZe5EKlLwKNwCsK2WWbPovpEkBihUIo7.xlsx', '', 4, 'Active', '2024-05-27 23:53:06', '2024-05-27 23:53:06'),
(5, 2, 'demo pdf assess', 'A', 'uploads/library/1/2/IJg8CEIM35TBTcbwA3AAf0EoX4Wheub1bl8kykd0.pdf', '', 4, 'Active', '2024-05-27 23:53:52', '2024-05-27 23:53:52'),
(6, 2, 'image assess soln', 'AS', 'uploads/library/1/2/3OHpqV3FtJKzJbZ6G4JTeTXMGBoQeRYNnnex6Ffx.png', '', 4, 'Active', '2024-05-27 23:54:35', '2024-05-27 23:54:35'),
(11, 2, 'doc', 'N', 'uploads/library/1/2/YlLW7mE62CWLJqfKNLBp3XtKwnBd4D8tMGlLVtJh.docx', '', 4, 'Active', '2024-05-28 05:10:25', '2024-05-28 05:10:25'),
(12, 2, '', 'U', 'uploads/library/1/cVZDAt9OL6YB21VwM9WsD9bhedjZQtQqyJiuySjs.xlsx', 'https://www.youtube.com/embed/1DX8uQkXt7g', 4, 'Active', '2024-06-03 01:54:27', '2024-06-03 02:00:17'),
(13, 2, 'demo ontent', 'P', 'uploads/library/1/2/hEErkPDomJ2tfQ9kRbU5bG6wJm5Q4q8Trx74QUs0.pptx', '', 4, 'Active', '2024-07-12 05:50:36', '2024-07-12 05:50:36');

-- --------------------------------------------------------

--
-- Table structure for table `library_content_types`
--

DROP TABLE IF EXISTS `library_content_types`;
CREATE TABLE IF NOT EXISTS `library_content_types` (
  `library_content_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_text` varchar(255) NOT NULL,
  `type_enum` varchar(20) NOT NULL,
  `external_embed_url` enum('Y','N') NOT NULL DEFAULT 'N',
  `video_file` enum('Y','N') NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`library_content_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `library_content_types`
--

INSERT INTO `library_content_types` (`library_content_type_id`, `type_text`, `type_enum`, `external_embed_url`, `video_file`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Assessment', 'A', 'N', 'N', 'Active', '2024-06-06 05:54:21', NULL),
(2, 'Assessment Solution', 'AS', 'N', 'N', 'Active', '2024-06-06 05:54:21', NULL),
(3, 'Mindmap', 'M', 'N', 'N', 'Active', '2024-06-06 05:54:21', NULL),
(4, 'Teacher Note', 'N', 'N', 'N', 'Active', '2024-06-06 05:54:21', NULL),
(5, 'Presentation (PPT)', 'P', 'N', 'N', 'Active', '2024-06-06 05:54:21', NULL),
(6, 'URL', 'U', 'Y', 'N', 'Active', '2024-06-06 05:54:21', NULL),
(7, 'Video', 'V', 'N', 'Y', 'Active', '2024-06-06 05:54:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ofstead_finances`
--

DROP TABLE IF EXISTS `ofstead_finances`;
CREATE TABLE IF NOT EXISTS `ofstead_finances` (
  `ofstead_finance_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `year` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_indicator_id` bigint(11) NOT NULL,
  `value` double DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ofstead_finance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=373 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ofstead_finances`
--

INSERT INTO `ofstead_finances` (`ofstead_finance_id`, `year`, `sub_indicator_id`, `value`, `created_at`, `updated_at`) VALUES
(1, '2022-2023', 1, 435, '2024-06-03 05:38:59', NULL),
(2, '2022-2023', 2, 21.19, '2024-06-03 05:38:59', NULL),
(3, '2022-2023', 3, 3523402.29, '2024-06-03 05:38:59', NULL),
(4, '2022-2023', 4, 2343797.23, '2024-06-03 05:38:59', NULL),
(5, '2022-2023', 5, 380329.17, '2024-06-03 05:38:59', NULL),
(6, '2022-2023', 6, 336156.94, '2024-06-03 05:38:59', NULL),
(7, '2022-2023', 7, 806608.37, '2024-06-03 05:38:59', NULL),
(8, '2022-2023', 8, 0, '2024-06-03 05:38:59', NULL),
(9, '2022-2023', 9, NULL, '2024-06-03 05:38:59', NULL),
(10, '2022-2023', 10, 0, '2024-06-03 05:38:59', NULL),
(11, '2022-2023', 11, 1002.4, '2024-06-03 05:38:59', NULL),
(12, '2022-2023', 12, 1403951.36, '2024-06-03 05:38:59', NULL),
(13, '2022-2023', 13, 68886.17, '2024-06-03 05:38:59', NULL),
(14, '2022-2023', 14, 587029.19, '2024-06-03 05:38:59', NULL),
(15, '2022-2023', 15, 158816.62, '2024-06-03 05:38:59', NULL),
(16, '2022-2023', 16, 125113.89, '2024-06-03 05:38:59', NULL),
(17, '2022-2023', 17, 0, '2024-06-03 05:38:59', NULL),
(18, '2022-2023', 18, 0, '2024-06-03 05:38:59', NULL),
(19, '2022-2023', 19, 35837.35, '2024-06-03 05:38:59', NULL),
(20, '2022-2023', 20, 344491.82, '2024-06-03 05:38:59', NULL),
(21, '2022-2023', 21, 112190.11, '2024-06-03 05:38:59', NULL),
(22, '2022-2023', 22, 9348.48, '2024-06-03 05:38:59', NULL),
(23, '2022-2023', 23, 33536, '2024-06-03 05:38:59', NULL),
(24, '2022-2023', 24, 9970.94, '2024-06-03 05:38:59', NULL),
(25, '2022-2023', 25, 6007.8, '2024-06-03 05:38:59', NULL),
(26, '2022-2023', 26, 165103.61, '2024-06-03 05:38:59', NULL),
(27, '2022-2023', 27, 16737.49, '2024-06-03 05:38:59', NULL),
(28, '2022-2023', 28, 126573.66, '2024-06-03 05:38:59', NULL),
(29, '2022-2023', 29, 318805.4, '2024-06-03 05:38:59', NULL),
(30, '2022-2023', 30, NULL, '2024-06-03 05:38:59', NULL),
(31, '2022-2023', 31, 0, '2024-06-03 05:38:59', NULL),
(32, '2022-2023', 32, 0, '2024-06-03 05:38:59', NULL),
(33, '2022-2023', 33, 0, '2024-06-03 05:38:59', NULL),
(34, '2022-2023', 34, 3551557.55, '2024-06-03 05:38:59', NULL),
(35, '2022-2023', 35, 3447410.51, '2024-06-03 05:38:59', NULL),
(36, '2022-2023', 36, 104147.04, '2024-06-03 05:38:59', NULL),
(37, '2022-2023', 37, 0, '2024-06-03 05:38:59', NULL),
(38, '2022-2023', 38, 28155.26, '2024-06-03 05:38:59', NULL),
(39, '2022-2023', 39, 353945.18, '2024-06-03 05:38:59', NULL),
(40, '2022-2023', 40, 2937518.96, '2024-06-03 05:38:59', NULL),
(41, '2022-2023', 41, 120528.11, '2024-06-03 05:38:59', NULL),
(42, '2022-2023', 42, 389363.44, '2024-06-03 05:38:59', NULL),
(43, '2022-2023', 43, 0, '2024-06-03 05:38:59', NULL),
(44, '2022-2023', 44, 7937.25, '2024-06-03 05:38:59', NULL),
(45, '2022-2023', 45, 65929, '2024-06-03 05:38:59', NULL),
(46, '2022-2023', 46, 900, '2024-06-03 05:38:59', NULL),
(47, '2022-2023', 47, 8210.79, '2024-06-03 05:38:59', NULL),
(48, '2022-2023', 48, 21170, '2024-06-03 05:38:59', NULL),
(49, '2022-2023', 49, 0, '2024-06-03 05:38:59', NULL),
(50, '2022-2023', 50, 49.06, '2024-06-03 05:38:59', NULL),
(51, '2022-2023', 51, 21.19, '2024-06-03 05:38:59', NULL),
(52, '2022-2023', 52, 90.91, '2024-06-03 05:38:59', NULL),
(53, '2022-2023', 53, 4.93, '2024-06-03 05:38:59', NULL),
(54, '2022-2023', 54, 20.16, '2024-06-03 05:38:59', NULL),
(55, '2022-2023', 55, 6.26, '2024-06-03 05:38:59', NULL),
(56, '2022-2023', 56, 1.45, '2024-06-03 05:38:59', NULL),
(57, '2022-2023', 57, 80, '2024-06-03 05:38:59', NULL),
(58, '2022-2023', 58, 77, '2024-06-03 05:38:59', NULL),
(59, '2022-2023', 59, 2.5, '2024-06-03 05:38:59', NULL),
(60, '2022-2023', 60, NULL, '2024-06-03 05:38:59', NULL),
(61, '2022-2023', 61, NULL, '2024-06-03 05:38:59', NULL),
(62, '2022-2023', 62, 2, '2024-06-03 05:38:59', NULL),
(63, '2021-2022', 1, 442, '2024-06-03 05:38:59', NULL),
(64, '2021-2022', 2, 24.1, '2024-06-03 05:38:59', NULL),
(65, '2021-2022', 3, 3340222, '2024-06-03 05:38:59', NULL),
(66, '2021-2022', 4, 2290608, '2024-06-03 05:38:59', NULL),
(67, '2021-2022', 5, 318628, '2024-06-03 05:38:59', NULL),
(68, '2021-2022', 6, 314567, '2024-06-03 05:38:59', NULL),
(69, '2021-2022', 7, 723834, '2024-06-03 05:38:59', NULL),
(70, '2021-2022', 8, 0, '2024-06-03 05:38:59', NULL),
(71, '2021-2022', 9, NULL, '2024-06-03 05:38:59', NULL),
(72, '2021-2022', 10, 0, '2024-06-03 05:38:59', NULL),
(73, '2021-2022', 11, 1000, '2024-06-03 05:38:59', NULL),
(74, '2021-2022', 12, 1400086, '2024-06-03 05:38:59', NULL),
(75, '2021-2022', 13, 75079, '2024-06-03 05:38:59', NULL),
(76, '2021-2022', 14, 544660, '2024-06-03 05:38:59', NULL),
(77, '2021-2022', 15, 157801, '2024-06-03 05:38:59', NULL),
(78, '2021-2022', 16, 112982, '2024-06-03 05:38:59', NULL),
(79, '2021-2022', 17, 0, '2024-06-03 05:38:59', NULL),
(80, '2021-2022', 18, 0, '2024-06-03 05:38:59', NULL),
(81, '2021-2022', 19, 10213, '2024-06-03 05:38:59', NULL),
(82, '2021-2022', 20, 308415, '2024-06-03 05:38:59', NULL),
(83, '2021-2022', 21, 93599, '2024-06-03 05:38:59', NULL),
(84, '2021-2022', 22, 9080, '2024-06-03 05:38:59', NULL),
(85, '2021-2022', 23, 32566, '2024-06-03 05:38:59', NULL),
(86, '2021-2022', 24, 5289, '2024-06-03 05:38:59', NULL),
(87, '2021-2022', 25, 6089, '2024-06-03 05:38:59', NULL),
(88, '2021-2022', 26, 167944, '2024-06-03 05:38:59', NULL),
(89, '2021-2022', 27, 50275, '2024-06-03 05:38:59', NULL),
(90, '2021-2022', 28, 112090, '2024-06-03 05:38:59', NULL),
(91, '2021-2022', 29, 253054, '2024-06-03 05:38:59', NULL),
(92, '2021-2022', 30, NULL, '2024-06-03 05:38:59', NULL),
(93, '2021-2022', 31, NULL, '2024-06-03 05:38:59', NULL),
(94, '2021-2022', 32, 0, '2024-06-03 05:38:59', NULL),
(95, '2021-2022', 33, 0, '2024-06-03 05:38:59', NULL),
(96, '2021-2022', 34, 3227701, '2024-06-03 05:38:59', NULL),
(97, '2021-2022', 35, 3152764, '2024-06-03 05:38:59', NULL),
(98, '2021-2022', 36, 74937, '2024-06-03 05:38:59', NULL),
(99, '2021-2022', 37, 0, '2024-06-03 05:38:59', NULL),
(100, '2021-2022', 38, -112521, '2024-06-03 05:38:59', NULL),
(101, '2021-2022', 39, 325790, '2024-06-03 05:38:59', NULL),
(102, '2021-2022', 40, 2724571, '2024-06-03 05:38:59', NULL),
(103, '2021-2022', 41, 100072, '2024-06-03 05:38:59', NULL),
(104, '2021-2022', 42, 328121, '2024-06-03 05:38:59', NULL),
(105, '2021-2022', 43, 0, '2024-06-03 05:38:59', NULL),
(106, '2021-2022', 44, 5792, '2024-06-03 05:38:59', NULL),
(107, '2021-2022', 45, 66522, '2024-06-03 05:38:59', NULL),
(108, '2021-2022', 46, 0, '2024-06-03 05:38:59', NULL),
(109, '2021-2022', 47, 1123, '2024-06-03 05:38:59', NULL),
(110, '2021-2022', 48, 1500, '2024-06-03 05:38:59', NULL),
(111, '2021-2022', 49, 0, '2024-06-03 05:38:59', NULL),
(112, '2021-2022', 50, 56.77, '2024-06-03 05:38:59', NULL),
(113, '2021-2022', 51, 24.13, '2024-06-03 05:38:59', NULL),
(114, '2021-2022', 52, 92, '2024-06-03 05:38:59', NULL),
(115, '2021-2022', 53, 5, '2024-06-03 05:38:59', NULL),
(116, '2021-2022', 54, 22.63, '2024-06-03 05:38:59', NULL),
(117, '2021-2022', 55, 8.56, '2024-06-03 05:38:59', NULL),
(118, '2021-2022', 56, 1.45, '2024-06-03 05:38:59', NULL),
(119, '2021-2022', 57, 90, '2024-06-03 05:38:59', NULL),
(120, '2021-2022', 58, 86, '2024-06-03 05:38:59', NULL),
(121, '2021-2022', 59, 4.1, '2024-06-03 05:38:59', NULL),
(122, '2021-2022', 60, NULL, '2024-06-03 05:38:59', NULL),
(123, '2021-2022', 61, NULL, '2024-06-03 05:38:59', NULL),
(124, '2021-2022', 62, 2, '2024-06-03 05:38:59', NULL),
(125, '2020-2021', 1, 436, '2024-06-03 05:38:59', NULL),
(126, '2020-2021', 2, 22.1, '2024-06-03 05:38:59', NULL),
(127, '2020-2021', 3, 2952715, '2024-06-03 05:38:59', NULL),
(128, '2020-2021', 4, 2160076, '2024-06-03 05:38:59', NULL),
(129, '2020-2021', 5, 321855, '2024-06-03 05:38:59', NULL),
(130, '2020-2021', 6, 161719, '2024-06-03 05:38:59', NULL),
(131, '2020-2021', 7, 308219, '2024-06-03 05:38:59', NULL),
(132, '2020-2021', 8, 0, '2024-06-03 05:38:59', NULL),
(133, '2020-2021', 9, NULL, '2024-06-03 05:38:59', NULL),
(134, '2020-2021', 10, 0, '2024-06-03 05:38:59', NULL),
(135, '2020-2021', 11, 847, '2024-06-03 05:38:59', NULL),
(136, '2020-2021', 12, 1338948, '2024-06-03 05:38:59', NULL),
(137, '2020-2021', 13, 33669, '2024-06-03 05:38:59', NULL),
(138, '2020-2021', 14, 547159, '2024-06-03 05:38:59', NULL),
(139, '2020-2021', 15, 160017, '2024-06-03 05:38:59', NULL),
(140, '2020-2021', 16, 80283, '2024-06-03 05:38:59', NULL),
(141, '2020-2021', 17, 0, '2024-06-03 05:38:59', NULL),
(142, '2020-2021', 18, 0, '2024-06-03 05:38:59', NULL),
(143, '2020-2021', 19, 7477, '2024-06-03 05:38:59', NULL),
(144, '2020-2021', 20, 314378, '2024-06-03 05:38:59', NULL),
(145, '2020-2021', 21, 18470, '2024-06-03 05:38:59', NULL),
(146, '2020-2021', 22, 5113, '2024-06-03 05:38:59', NULL),
(147, '2020-2021', 23, 33536, '2024-06-03 05:38:59', NULL),
(148, '2020-2021', 24, 3757, '2024-06-03 05:38:59', NULL),
(149, '2020-2021', 25, 6342, '2024-06-03 05:38:59', NULL),
(150, '2020-2021', 26, 94501, '2024-06-03 05:38:59', NULL),
(151, '2020-2021', 27, 34016, '2024-06-03 05:38:59', NULL),
(152, '2020-2021', 28, 116184, '2024-06-03 05:38:59', NULL),
(153, '2020-2021', 29, 158019, '2024-06-03 05:38:59', NULL),
(154, '2020-2021', 30, NULL, '2024-06-03 05:38:59', NULL),
(155, '2020-2021', 31, NULL, '2024-06-03 05:38:59', NULL),
(156, '2020-2021', 32, 0, '2024-06-03 05:38:59', NULL),
(157, '2020-2021', 33, 0, '2024-06-03 05:38:59', NULL),
(158, '2020-2021', 34, 3095324, '2024-06-03 05:38:59', NULL),
(159, '2020-2021', 35, 3059836, '2024-06-03 05:38:59', NULL),
(160, '2020-2021', 36, 35489, '2024-06-03 05:38:59', NULL),
(161, '2020-2021', 37, 0, '2024-06-03 05:38:59', NULL),
(162, '2020-2021', 38, 142609, '2024-06-03 05:38:59', NULL),
(163, '2020-2021', 39, 438312, '2024-06-03 05:38:59', NULL),
(164, '2020-2021', 40, 2597333, '2024-06-03 05:38:59', NULL),
(165, '2020-2021', 41, 96573, '2024-06-03 05:38:59', NULL),
(166, '2020-2021', 42, 365929, '2024-06-03 05:38:59', NULL),
(167, '2020-2021', 43, 0, '2024-06-03 05:38:59', NULL),
(168, '2020-2021', 44, 4258, '2024-06-03 05:38:59', NULL),
(169, '2020-2021', 45, 22921, '2024-06-03 05:38:59', NULL),
(170, '2020-2021', 46, 3000, '2024-06-03 05:38:59', NULL),
(171, '2020-2021', 47, 0, '2024-06-03 05:38:59', NULL),
(172, '2020-2021', 48, 5310, '2024-06-03 05:38:59', NULL),
(173, '2020-2021', 49, 0, '2024-06-03 05:38:59', NULL),
(174, '2020-2021', 50, 54.7, '2024-06-03 05:38:59', NULL),
(175, '2020-2021', 51, 22.1, '2024-06-03 05:38:59', NULL),
(176, '2020-2021', 52, 90.96, '2024-06-03 05:38:59', NULL),
(177, '2020-2021', 53, 5, '2024-06-03 05:38:59', NULL),
(178, '2020-2021', 54, 24.5, '2024-06-03 05:38:59', NULL),
(179, '2020-2021', 55, 6.5, '2024-06-03 05:38:59', NULL),
(180, '2020-2021', 56, 1.6, '2024-06-03 05:38:59', NULL),
(181, '2020-2021', 57, 88, '2024-06-03 05:38:59', NULL),
(182, '2020-2021', 58, 86, '2024-06-03 05:38:59', NULL),
(183, '2020-2021', 59, 4.1, '2024-06-03 05:38:59', NULL),
(184, '2020-2021', 60, NULL, '2024-06-03 05:38:59', NULL),
(185, '2020-2021', 61, NULL, '2024-06-03 05:38:59', NULL),
(186, '2020-2021', 62, 2, '2024-06-03 05:38:59', NULL),
(187, '2019-2020', 1, 409, '2024-06-03 05:38:59', NULL),
(188, '2019-2020', 2, 23.6, '2024-06-03 05:38:59', NULL),
(189, '2019-2020', 3, 3175865, '2024-06-03 05:38:59', NULL),
(190, '2019-2020', 4, 2224370, '2024-06-03 05:38:59', NULL),
(191, '2019-2020', 5, 41052, '2024-06-03 05:38:59', NULL),
(192, '2019-2020', 6, 254851, '2024-06-03 05:38:59', NULL),
(193, '2019-2020', 7, 654503, '2024-06-03 05:38:59', NULL),
(194, '2019-2020', 8, NULL, '2024-06-03 05:38:59', NULL),
(195, '2019-2020', 9, 0, '2024-06-03 05:38:59', NULL),
(196, '2019-2020', 10, 0, '2024-06-03 05:38:59', NULL),
(197, '2019-2020', 11, 1089, '2024-06-03 05:38:59', NULL),
(198, '2019-2020', 12, 1290354, '2024-06-03 05:38:59', NULL),
(199, '2019-2020', 13, 49422, '2024-06-03 05:38:59', NULL),
(200, '2019-2020', 14, 654500, '2024-06-03 05:38:59', NULL),
(201, '2019-2020', 15, 153493, '2024-06-03 05:39:00', NULL),
(202, '2019-2020', 16, 76601, '2024-06-03 05:39:00', NULL),
(203, '2019-2020', 17, 0, '2024-06-03 05:39:00', NULL),
(204, '2019-2020', 18, 0, '2024-06-03 05:39:00', NULL),
(205, '2019-2020', 19, 41052, '2024-06-03 05:39:00', NULL),
(206, '2019-2020', 20, NULL, '2024-06-03 05:39:00', NULL),
(207, '2019-2020', 21, 62606, '2024-06-03 05:39:00', NULL),
(208, '2019-2020', 22, 7843, '2024-06-03 05:39:00', NULL),
(209, '2019-2020', 23, 33012, '2024-06-03 05:39:00', NULL),
(210, '2019-2020', 24, 6351, '2024-06-03 05:39:00', NULL),
(211, '2019-2020', 25, 5761, '2024-06-03 05:39:00', NULL),
(212, '2019-2020', 26, 139278, '2024-06-03 05:39:00', NULL),
(213, '2019-2020', 27, 39184, '2024-06-03 05:39:00', NULL),
(214, '2019-2020', 28, 163672, '2024-06-03 05:39:00', NULL),
(215, '2019-2020', 29, 451647, '2024-06-03 05:39:00', NULL),
(216, '2019-2020', 30, 0, '2024-06-03 05:39:00', NULL),
(217, '2019-2020', 31, 0, '2024-06-03 05:39:00', NULL),
(218, '2019-2020', 32, 0, '2024-06-03 05:39:00', NULL),
(219, '2019-2020', 33, 0, '2024-06-03 05:39:00', NULL),
(220, '2019-2020', 34, 3123072, '2024-06-03 05:39:00', NULL),
(221, '2019-2020', 35, 3061118, '2024-06-03 05:39:00', NULL),
(222, '2019-2020', 36, 61954, '2024-06-03 05:39:00', NULL),
(223, '2019-2020', 37, NULL, '2024-06-03 05:39:00', NULL),
(224, '2019-2020', 38, -52793, '2024-06-03 05:39:00', NULL),
(225, '2019-2020', 39, 295702, '2024-06-03 05:39:00', NULL),
(226, '2019-2020', 40, 2651455, '2024-06-03 05:39:00', NULL),
(227, '2019-2020', 41, 77406, '2024-06-03 05:39:00', NULL),
(228, '2019-2020', 42, 332257, '2024-06-03 05:39:00', NULL),
(229, '2019-2020', 43, 0, '2024-06-03 05:39:00', NULL),
(230, '2019-2020', 44, 11958, '2024-06-03 05:39:00', NULL),
(231, '2019-2020', 45, 44218, '2024-06-03 05:39:00', NULL),
(232, '2019-2020', 46, -1000, '2024-06-03 05:39:00', NULL),
(233, '2019-2020', 47, 6157, '2024-06-03 05:39:00', NULL),
(234, '2019-2020', 48, 620, '2024-06-03 05:39:00', NULL),
(235, '2019-2020', 49, 0, '2024-06-03 05:39:00', NULL),
(236, '2019-2020', 50, 53.23, '2024-06-03 05:39:00', NULL),
(237, '2019-2020', 51, 23.57, '2024-06-03 05:39:00', NULL),
(238, '2019-2020', 52, 91.51, '2024-06-03 05:39:00', NULL),
(239, '2019-2020', 53, 5, '2024-06-03 05:39:00', NULL),
(240, '2019-2020', 54, 21.95, '2024-06-03 05:39:00', NULL),
(241, '2019-2020', 55, 5.95, '2024-06-03 05:39:00', NULL),
(242, '2019-2020', 56, 1.76, '2024-06-03 05:39:00', NULL),
(243, '2019-2020', 57, 77, '2024-06-03 05:39:00', NULL),
(244, '2019-2020', 58, 86, '2024-06-03 05:39:00', NULL),
(245, '2019-2020', 59, 4.1, '2024-06-03 05:39:00', NULL),
(246, '2019-2020', 60, NULL, '2024-06-03 05:39:00', NULL),
(247, '2019-2020', 61, NULL, '2024-06-03 05:39:00', NULL),
(248, '2019-2020', 62, 2, '2024-06-03 05:39:00', NULL),
(311, '2018-2019', 1, 410, '2024-06-04 05:04:27', NULL),
(312, '2018-2019', 2, 22.2, '2024-06-04 05:04:27', NULL),
(313, '2018-2019', 3, 2942104, '2024-06-04 05:04:27', NULL),
(314, '2018-2019', 4, 2101642, '2024-06-04 05:04:27', NULL),
(315, '2018-2019', 5, 3050, '2024-06-04 05:04:27', NULL),
(316, '2018-2019', 6, 257942, '2024-06-04 05:04:27', NULL),
(317, '2018-2019', 7, 578460, '2024-06-04 05:04:27', NULL),
(318, '2018-2019', 8, NULL, '2024-06-04 05:04:27', NULL),
(319, '2018-2019', 9, 0, '2024-06-04 05:04:27', NULL),
(320, '2018-2019', 10, 0, '2024-06-04 05:04:27', NULL),
(321, '2018-2019', 11, 1010, '2024-06-04 05:04:27', NULL),
(322, '2018-2019', 12, 1282163, '2024-06-04 05:04:27', NULL),
(323, '2018-2019', 13, 35190, '2024-06-04 05:04:27', NULL),
(324, '2018-2019', 14, 563396, '2024-06-04 05:04:27', NULL),
(325, '2018-2019', 15, 147058, '2024-06-04 05:04:27', NULL),
(326, '2018-2019', 16, 73835, '2024-06-04 05:04:27', NULL),
(327, '2018-2019', 17, 0, '2024-06-04 05:04:27', NULL),
(328, '2018-2019', 18, 0, '2024-06-04 05:04:27', NULL),
(329, '2018-2019', 19, 3050, '2024-06-04 05:04:27', NULL),
(330, '2018-2019', 20, NULL, '2024-06-04 05:04:27', NULL),
(331, '2018-2019', 21, 65890, '2024-06-04 05:04:27', NULL),
(332, '2018-2019', 22, 7399, '2024-06-04 05:04:27', NULL),
(333, '2018-2019', 23, 5292, '2024-06-04 05:04:27', NULL),
(334, '2018-2019', 24, 6652, '2024-06-04 05:04:27', NULL),
(335, '2018-2019', 25, 5469, '2024-06-04 05:04:27', NULL),
(336, '2018-2019', 26, 167240, '2024-06-04 05:04:27', NULL),
(337, '2018-2019', 27, 20416, '2024-06-04 05:04:27', NULL),
(338, '2018-2019', 28, 85712, '2024-06-04 05:04:27', NULL),
(339, '2018-2019', 29, 472332, '2024-06-04 05:04:27', NULL),
(340, '2018-2019', 30, 0, '2024-06-04 05:04:27', NULL),
(341, '2018-2019', 31, 0, '2024-06-04 05:04:27', NULL),
(342, '2018-2019', 32, 0, '2024-06-04 05:04:27', NULL),
(343, '2018-2019', 33, 0, '2024-06-04 05:04:27', NULL),
(344, '2018-2019', 34, 3044734, '2024-06-04 05:04:27', NULL),
(345, '2018-2019', 35, 2904632, '2024-06-04 05:04:27', NULL),
(346, '2018-2019', 36, 140102, '2024-06-04 05:04:27', NULL),
(347, '2018-2019', 37, NULL, '2024-06-04 05:04:27', NULL),
(348, '2018-2019', 38, 102630, '2024-06-04 05:04:27', NULL),
(349, '2018-2019', 39, 348496, '2024-06-04 05:04:27', NULL),
(350, '2018-2019', 40, 2485753, '2024-06-04 05:04:27', NULL),
(351, '2018-2019', 41, 73978, '2024-06-04 05:04:27', NULL),
(352, '2018-2019', 42, 344901, '2024-06-04 05:04:27', NULL),
(353, '2018-2019', 43, 0, '2024-06-04 05:04:27', NULL),
(354, '2018-2019', 44, 14438, '2024-06-04 05:04:27', NULL),
(355, '2018-2019', 45, 50752, '2024-06-04 05:04:27', NULL),
(356, '2018-2019', 46, 50050, '2024-06-04 05:04:27', NULL),
(357, '2018-2019', 47, 7114, '2024-06-04 05:04:27', NULL),
(358, '2018-2019', 48, 17748, '2024-06-04 05:04:27', NULL),
(359, '2018-2019', 49, 0, '2024-06-04 05:04:27', NULL),
(360, '2018-2019', 50, 50.7, '2024-06-04 05:04:27', NULL),
(361, '2018-2019', 51, 22.2, '2024-06-04 05:04:27', NULL),
(362, '2018-2019', 52, 82.1, '2024-06-04 05:04:27', NULL),
(363, '2018-2019', 53, 4.8, '2024-06-04 05:04:27', NULL),
(364, '2018-2019', 54, 17.9, '2024-06-04 05:04:27', NULL),
(365, '2018-2019', 55, 8.7, '2024-06-04 05:04:27', NULL),
(366, '2018-2019', 56, 1.9, '2024-06-04 05:04:27', NULL),
(367, '2018-2019', 57, 74, '2024-06-04 05:04:27', NULL),
(368, '2018-2019', 58, 86, '2024-06-04 05:04:27', NULL),
(369, '2018-2019', 59, 4.1, '2024-06-04 05:04:27', NULL),
(370, '2018-2019', 60, NULL, '2024-06-04 05:04:27', NULL),
(371, '2018-2019', 61, NULL, '2024-06-04 05:04:27', NULL),
(372, '2018-2019', 62, 2, '2024-06-04 05:04:27', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `parents`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `parents`;
CREATE TABLE IF NOT EXISTS `parents` (
`user_profile_id` bigint(20)
,`user_id` bigint(20)
,`first_name` varchar(150)
,`middle_name` varchar(150)
,`last_name` varchar(150)
,`short_name` varchar(20)
,`ni_number` varchar(150)
,`end_date_id` date
,`id_file` varchar(255)
,`dbs_certificate_file` varchar(255)
,`end_date_dbs` date
,`address` varchar(255)
,`about` varchar(255)
,`birthday` date
,`gender` varchar(50)
,`blood_group` varchar(20)
,`batch_type_id` int(11)
,`department_id` int(11)
,`parent_name` varchar(255)
,`parent_phone` varchar(20)
,`parent_email` varchar(191)
,`have_sensupport_healthcare_plan` enum('Y','N')
,`first_lang_not_eng` enum('Y','N')
,`freeschool_eligible` enum('Y','N')
,`created_at` timestamp
,`updated_at` timestamp
,`tenant_id` bigint(20)
,`role` enum('A','T','S','TA','OU','P')
,`email` varchar(191)
,`code` varchar(255)
,`phone` varchar(100)
,`user_logo` varchar(512)
,`status` enum('Active','Inactive')
);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `question_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_question_id` bigint(20) NOT NULL DEFAULT '0',
  `linked_question` tinyint(4) NOT NULL DEFAULT '0',
  `question_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year_group_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `lesson_id` bigint(20) DEFAULT NULL,
  `question_category_id` int(11) DEFAULT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `require_file_upload` tinyint(4) DEFAULT NULL,
  `source` enum('Q','A','O') COLLATE utf8mb4_unicode_ci DEFAULT 'Q' COMMENT 'Q=Quiz; A=Assesment; O=Other',
  `created_by` int(11) NOT NULL,
  `creator_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Question bank';

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `parent_question_id`, `linked_question`, `question_type`, `year_group_id`, `subject_id`, `lesson_id`, `question_category_id`, `question`, `level`, `require_file_upload`, `source`, `created_by`, `creator_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 0, 0, 'radio', 1, 1, 1, NULL, 'This is first question from api of examination question', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-14 01:24:47', '2024-03-14 01:24:47'),
(2, 0, 0, 'radio', 1, 1, 1, NULL, 'This is first question from api of examination question', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-14 01:28:02', '2024-03-14 01:28:02'),
(3, 0, 0, 'radio', 1, 1, 1, NULL, 'This is second question from api of examination question', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-14 03:00:03', '2024-03-14 03:00:03'),
(4, 0, 0, 'radio', 1, 1, 2, NULL, '<p>This is third question from api of examination question?</p>', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-14 03:06:49', '2024-03-19 06:08:56'),
(5, 0, 0, 'select', 1, 1, 1, NULL, 'This is forth question from api of examination question', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-14 05:36:10', '2024-03-14 05:36:10'),
(6, 0, 0, 'radio', 1, 1, 2, NULL, '<p>test question?</p>', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-18 23:58:53', '2024-03-18 23:58:53'),
(7, 0, 0, 'radio', 1, 1, 2, NULL, '<p>page 1 3rd</p>', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-19 00:05:50', '2024-03-19 00:05:50'),
(8, 0, 0, 'checkbox', 1, 1, 2, NULL, '<p>Test question?</p>', 'medium', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-19 02:23:48', '2024-03-19 06:08:31'),
(11, 0, 0, 'text', 1, 1, 2, NULL, '<p>Free text question with no option?</p>', 'medium', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-19 06:25:33', '2024-03-19 06:38:53'),
(12, 0, 0, 'text', 1, 1, 2, NULL, '<p>Free text in page 2?</p>', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-19 06:30:25', '2024-03-19 06:31:01'),
(13, 0, 0, 'text', 1, 1, 2, NULL, '<p>dfgdgfdgdfg</p>', 'medium', 0, 'Q', 4, 'TU-T', 'Active', '2024-03-19 07:06:36', '2024-03-19 07:06:36'),
(14, 0, 0, 'text', 1, 1, 2, NULL, '<p>Describe process of photosynthesis?</p>', 'medium', 1, 'Q', 4, 'TU-T', 'Active', '2024-03-25 23:54:53', '2024-03-25 23:59:53'),
(15, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is Acid, Base &amp; Salts?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 02:26:08', '2024-04-08 04:50:23'),
(16, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of any liquid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 02:26:08', '2024-04-08 04:50:23'),
(17, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of water and sulphuric acid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 02:26:08', '2024-04-08 04:50:23'),
(18, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is Acid, Base &amp; Salts?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 05:10:29', '2024-04-08 05:10:29'),
(19, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of any liquid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 05:10:29', '2024-04-08 05:10:29'),
(20, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of water and sulphuric acid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 05:10:29', '2024-04-08 05:10:29'),
(21, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is Acid, Base &amp; Salts?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 05:10:48', '2024-04-08 05:10:48'),
(22, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of any liquid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 05:10:48', '2024-04-08 05:10:48'),
(23, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of water and sulphuric acid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 05:10:48', '2024-04-08 05:10:48'),
(24, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is Acid, Base &amp; Salts?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 06:03:31', '2024-04-08 06:03:31'),
(25, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of any liquid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 06:03:31', '2024-04-08 06:03:31'),
(26, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of water and sulphuric acid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 06:03:31', '2024-04-08 06:03:31'),
(27, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is Acid, Base &amp; Salts?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 06:14:45', '2024-04-08 06:14:45'),
(28, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of any liquid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 06:14:45', '2024-04-08 06:14:45'),
(29, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of water and sulphuric acid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-08 06:14:45', '2024-04-08 06:14:45'),
(30, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is Acid, Base &amp; Salts?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-09 00:22:19', '2024-04-09 00:22:19'),
(31, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of any liquid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-09 00:22:19', '2024-04-09 00:22:19'),
(32, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of water and sulphuric acid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-09 00:22:19', '2024-04-09 00:22:19'),
(33, 0, 0, 'text', 1, 1, 2, NULL, '<p><img alt=\"\" src=\"/public/uploads/cms_images/image5.png\" style=\"width: 500px; height: 422px;\" /></p>\r\n\r\n<p>Define the marked parts of the Cell?</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-04-09 00:22:19', '2024-04-09 00:22:19'),
(34, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is Acid, Base &amp; Salts?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-09 00:22:51', '2024-04-09 00:22:51'),
(35, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of any liquid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-09 00:22:51', '2024-04-09 00:22:51'),
(36, 0, 0, 'text', 1, 1, 2, NULL, '<p>What is the boiling point of water and sulphuric acid?</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-04-09 00:22:51', '2024-04-09 00:22:51'),
(37, 0, 0, 'text', 1, 1, 2, NULL, '<p><img alt=\"\" src=\"/public/uploads/cms_images/image5.png\" style=\"width: 500px; height: 422px;\" /></p>\r\n\r\n<p>Define the marked parts of the Cell?</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-04-09 00:22:51', '2024-04-09 00:22:51'),
(53, 0, 0, 'text', 1, 1, 2, NULL, '<p>S1</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 02:01:17', '2024-05-09 02:01:17'),
(54, 0, 1, 'linked', 1, 1, 2, NULL, '<p>L1</p>', NULL, 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 02:01:17', '2024-05-09 02:01:17'),
(55, 54, 0, 'text', 1, 1, 2, NULL, '<p>L1 S1</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 02:01:17', '2024-05-09 02:01:17'),
(56, 0, 0, 'text', 1, 1, 2, NULL, '<p>S2</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 02:01:17', '2024-05-09 02:01:17'),
(57, 0, 0, 'text', 1, 1, 2, NULL, '<p>S1 ed</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 02:07:07', '2024-05-09 05:01:51'),
(58, 0, 0, 'text', 1, 1, 2, NULL, '<p>S2 ed</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 02:07:07', '2024-05-09 05:01:51'),
(59, 0, 1, 'linked', 1, 1, 2, NULL, '<p>L1 edit</p>', NULL, 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 02:07:56', '2024-05-09 04:07:55'),
(60, 59, 0, 'text', 1, 1, 2, NULL, '<p>L1 S1 edit</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 02:07:56', '2024-05-09 04:07:55'),
(61, 0, 1, 'linked', 1, 1, 2, NULL, '<p>L1</p>', NULL, 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 06:51:47', '2024-05-09 06:51:47'),
(62, 61, 0, 'text', 1, 1, 2, NULL, '<p>L1 S1</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 06:51:47', '2024-05-09 06:51:47'),
(63, 61, 0, 'text', 1, 1, 2, NULL, '<p>L1 S2</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-05-09 07:00:28', '2024-05-09 07:00:28'),
(64, 61, 0, 'text', 1, 1, 2, NULL, '<p>L1 S3 35</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 00:04:09', '2024-05-10 00:04:09'),
(65, 59, 0, 'text', 1, 1, 2, NULL, '<p>L1 S2</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 00:25:40', '2024-05-10 00:25:40'),
(66, 0, 1, 'linked', 1, 1, 2, NULL, '<p>L2</p>', NULL, 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 00:27:41', '2024-05-10 00:27:41'),
(67, 66, 0, 'text', 1, 1, 2, NULL, '<p>L2 S1</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 00:27:41', '2024-05-10 00:27:41'),
(68, 66, 0, 'text', 1, 1, 2, NULL, '<p>L2 S2</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 00:39:59', '2024-05-10 00:39:59'),
(69, 0, 1, 'linked', 1, 1, 2, NULL, '<p>L3</p>', NULL, 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 00:39:59', '2024-05-10 00:39:59'),
(70, 69, 0, 'text', 1, 1, 2, NULL, '<p>L3 S1</p>', 'medium', 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 00:39:59', '2024-05-10 00:39:59'),
(71, 0, 1, 'linked', 1, 1, 2, NULL, '<p>L4</p>', NULL, 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 01:19:42', '2024-05-10 01:19:42'),
(72, 71, 0, 'text', 1, 1, 2, NULL, '<p>L4 S1</p>\r\n\r\n<p><img alt=\"\" src=\"/public/uploads/cms_images/download%20(2).png\" style=\"width: 100px; height: 100px;\" /></p>', 'low', 1, 'A', 4, 'TU-T', 'Active', '2024-05-10 01:19:42', '2024-05-10 01:20:27'),
(73, 0, 0, 'text', 1, 1, 2, NULL, '<p>S3</p>', 'medium', 1, 'A', 4, 'TU-T', 'Active', '2024-05-10 01:24:35', '2024-05-10 01:24:35'),
(74, 0, 0, 'text', 1, 1, 2, NULL, '<p>S3</p>', 'low', 1, 'A', 4, 'TU-T', 'Active', '2024-05-10 01:26:25', '2024-05-10 01:26:25'),
(75, 54, 0, 'text', 1, 1, 2, NULL, '<p>L1 S2</p>', 'low', 1, 'A', 4, 'TU-T', 'Active', '2024-05-10 01:27:15', '2024-05-10 01:27:15'),
(76, 54, 0, 'text', 1, 1, 2, NULL, '<p>L1 S3</p>', 'low', 1, 'A', 4, 'TU-T', 'Active', '2024-05-10 03:35:53', '2024-05-10 03:35:53'),
(77, 0, 1, 'linked', 1, 1, 2, NULL, '<p>L2</p>', NULL, 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 03:36:42', '2024-05-10 03:36:42'),
(78, 77, 0, 'text', 1, 1, 2, NULL, '<p>L2 S1</p>', 'low', 1, 'A', 4, 'TU-T', 'Active', '2024-05-10 03:36:42', '2024-05-10 03:36:42'),
(79, 54, 0, 'text', 1, 1, 2, NULL, '<p>L1 S3</p>', 'low', 1, 'A', 4, 'TU-T', 'Active', '2024-05-10 04:24:18', '2024-05-10 04:24:18'),
(80, 0, 0, 'radio', 1, 1, 2, NULL, '<p>What is Sunlight?</p>', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-05-10 04:39:50', '2024-05-10 04:39:50'),
(81, 0, 0, 'text', 1, 1, 2, NULL, '<p>Define photosynthesis and&nbsp;Upload the diagram?</p>', 'low', 1, 'Q', 4, 'TU-T', 'Active', '2024-05-10 04:41:07', '2024-05-10 04:41:07'),
(82, 0, 0, 'text', 1, 1, 2, NULL, '<p>Single question 1</p>', 'low', 0, 'A', 4, 'TU-T', 'Active', '2024-05-10 04:51:59', '2024-05-10 04:51:59'),
(83, 0, 0, 'radio', 1, 1, 2, NULL, '<p>This is test single question quiz for reported error?</p>', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-05-15 03:00:53', '2024-05-15 03:00:53'),
(84, 0, 0, 'radio', 1, 1, 2, NULL, '<p>sdsad</p>', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-07-05 05:25:06', '2024-07-05 05:25:06'),
(85, 0, 0, 'radio', 1, 1, 2, NULL, '<p>qes1</p>', 'low', 0, 'Q', 4, 'TU-T', 'Active', '2024-07-12 05:44:20', '2024-07-12 05:44:20');

-- --------------------------------------------------------

--
-- Table structure for table `question_categories`
--

DROP TABLE IF EXISTS `question_categories`;
CREATE TABLE IF NOT EXISTS `question_categories` (
  `question_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`question_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_categories`
--

INSERT INTO `question_categories` (`question_category_id`, `category_name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Mathematics', 'Active', NULL, '2024-03-13 00:35:07'),
(2, 'General Knowledge', 'Active', '2024-03-12 00:26:24', '2024-03-12 00:26:24');

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

DROP TABLE IF EXISTS `question_options`;
CREATE TABLE IF NOT EXISTS `question_options` (
  `question_option_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) NOT NULL,
  `option_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`question_option_id`),
  KEY `question_option_fk` (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`question_option_id`, `question_id`, `option_value`, `is_correct`, `created_at`, `updated_at`) VALUES
(29, 2, 'opt 1 e', 0, '2024-03-14 02:29:28', '2024-03-14 02:29:28'),
(30, 2, 'opt 2', 0, '2024-03-14 02:29:28', '2024-03-14 02:29:28'),
(31, 2, 'opt 3', 0, '2024-03-14 02:29:28', '2024-03-14 02:29:28'),
(32, 2, 'opt 4', 1, '2024-03-14 02:29:28', '2024-03-14 02:29:28'),
(49, 1, 'opt 1 e', 0, '2024-03-14 02:38:17', '2024-03-14 02:38:17'),
(50, 1, 'opt 2', 0, '2024-03-14 02:38:17', '2024-03-14 02:38:17'),
(51, 1, 'opt 3', 0, '2024-03-14 02:38:17', '2024-03-14 02:38:17'),
(52, 1, 'opt 4', 1, '2024-03-14 02:38:17', '2024-03-14 02:38:17'),
(53, 3, 'opt-val 1', 0, '2024-03-14 03:00:04', '2024-03-14 03:00:04'),
(54, 3, 'opt-val 2', 0, '2024-03-14 03:00:04', '2024-03-14 03:00:04'),
(55, 3, 'opt-val 3', 1, '2024-03-14 03:00:04', '2024-03-14 03:00:04'),
(56, 3, 'opt-val 4', 0, '2024-03-14 03:00:04', '2024-03-14 03:00:04'),
(61, 5, 'opt-val 1', 0, '2024-03-14 05:36:10', '2024-03-14 05:36:10'),
(62, 5, 'opt-val 2', 0, '2024-03-14 05:36:10', '2024-03-14 05:36:10'),
(63, 5, 'opt-val 3', 1, '2024-03-14 05:36:10', '2024-03-14 05:36:10'),
(64, 5, 'opt-val 4', 0, '2024-03-14 05:36:10', '2024-03-14 05:36:10'),
(65, 6, 'ans 1', 0, '2024-03-18 23:58:53', '2024-03-18 23:58:53'),
(66, 6, 'ans 2', 0, '2024-03-18 23:58:53', '2024-03-18 23:58:53'),
(67, 6, 'ans 3', 1, '2024-03-18 23:58:53', '2024-03-18 23:58:53'),
(68, 6, 'ans 4', 0, '2024-03-18 23:58:53', '2024-03-18 23:58:53'),
(69, 7, 'ans 1', 0, '2024-03-19 00:05:50', '2024-03-19 00:05:50'),
(70, 7, 'ans 2', 0, '2024-03-19 00:05:50', '2024-03-19 00:05:50'),
(71, 7, 'ans 3', 1, '2024-03-19 00:05:50', '2024-03-19 00:05:50'),
(72, 7, 'ans 4', 0, '2024-03-19 00:05:50', '2024-03-19 00:05:50'),
(87, 8, 'Droupadi Murmu', 0, '2024-03-19 06:08:31', '2024-03-19 06:08:31'),
(88, 8, 'APJ Abdul Kalam', 0, '2024-03-19 06:08:31', '2024-03-19 06:08:31'),
(89, 8, 'Trunk of Tree', 0, '2024-03-19 06:08:31', '2024-03-19 06:08:31'),
(90, 8, 'Environtmentologist', 1, '2024-03-19 06:08:31', '2024-03-19 06:08:31'),
(91, 4, 'opt-val 1', 0, '2024-03-19 06:08:56', '2024-03-19 06:08:56'),
(92, 4, 'opt-val 2', 0, '2024-03-19 06:08:56', '2024-03-19 06:08:56'),
(93, 4, 'opt-val 3', 1, '2024-03-19 06:08:56', '2024-03-19 06:08:56'),
(94, 4, 'opt-val 4', 0, '2024-03-19 06:08:56', '2024-03-19 06:08:56'),
(95, 4, 'opt-val 5', 0, '2024-03-19 06:08:56', '2024-03-19 06:08:56'),
(96, 80, 'Detergent used for washing clothes', 0, '2024-05-10 04:39:50', '2024-05-10 04:39:50'),
(97, 80, 'Alkaline liquid used by different industries', 0, '2024-05-10 04:39:50', '2024-05-10 04:39:50'),
(98, 80, 'Photosynthesis by product', 0, '2024-05-10 04:39:50', '2024-05-10 04:39:50'),
(99, 80, 'Light produced by the Sun', 1, '2024-05-10 04:39:50', '2024-05-10 04:39:50'),
(100, 83, 'ans 1', 0, '2024-05-15 03:00:53', '2024-05-15 03:00:53'),
(101, 83, 'ans 2', 0, '2024-05-15 03:00:53', '2024-05-15 03:00:53'),
(102, 83, 'ans 3', 1, '2024-05-15 03:00:53', '2024-05-15 03:00:53'),
(103, 83, 'ans 4', 0, '2024-05-15 03:00:53', '2024-05-15 03:00:53'),
(104, 85, 'a', 0, '2024-07-12 05:44:20', '2024-07-12 05:44:20'),
(105, 85, 'b', 0, '2024-07-12 05:44:20', '2024-07-12 05:44:20'),
(106, 85, 'c', 1, '2024-07-12 05:44:20', '2024-07-12 05:44:20'),
(107, 85, 'd', 0, '2024-07-12 05:44:20', '2024-07-12 05:44:20');

-- --------------------------------------------------------

--
-- Stand-in structure for view `students`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
`user_profile_id` bigint(20)
,`user_id` bigint(20)
,`first_name` varchar(150)
,`middle_name` varchar(150)
,`last_name` varchar(150)
,`short_name` varchar(20)
,`ni_number` varchar(150)
,`end_date_id` date
,`id_file` varchar(255)
,`dbs_certificate_file` varchar(255)
,`end_date_dbs` date
,`address` varchar(255)
,`about` varchar(255)
,`birthday` date
,`gender` varchar(50)
,`blood_group` varchar(20)
,`batch_type_id` int(11)
,`department_id` int(11)
,`parent_name` varchar(255)
,`parent_phone` varchar(20)
,`parent_email` varchar(191)
,`have_sensupport_healthcare_plan` enum('Y','N')
,`first_lang_not_eng` enum('Y','N')
,`freeschool_eligible` enum('Y','N')
,`cover_picture` varchar(255)
,`created_at` timestamp
,`updated_at` timestamp
,`tenant_id` bigint(20)
,`role` enum('A','T','S','TA','OU','P')
,`email` varchar(191)
,`phone` varchar(100)
,`user_logo` varchar(512)
,`status` enum('Active','Inactive')
,`code` varchar(255)
,`year_group_names` varchar(256)
,`year_group_ids` varchar(256)
,`subject_ids` varchar(256)
,`subject_names` varchar(256)
);

-- --------------------------------------------------------

--
-- Table structure for table `study_groups`
--

DROP TABLE IF EXISTS `study_groups`;
CREATE TABLE IF NOT EXISTS `study_groups` (
  `study_group_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `group_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`study_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `study_groups`
--

INSERT INTO `study_groups` (`study_group_id`, `name`, `description`, `group_image`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Year 9 Group', 'Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter.Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter.', '/uploads/user_studygroup/661f75f852247.png', 'Active', 1, '2024-04-17 01:40:48', '2024-04-17 01:40:48'),
(2, 'Year 9 Chemistry', 'This is chemistry study group for Year group 9 created by student 2', NULL, 'Active', 2, '2024-04-17 07:20:16', '2024-04-17 07:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `study_group_contents`
--

DROP TABLE IF EXISTS `study_group_contents`;
CREATE TABLE IF NOT EXISTS `study_group_contents` (
  `study_group_content_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `study_group_id` bigint(20) NOT NULL,
  `study_group_member_id` bigint(20) NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`study_group_content_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `study_group_contents`
--

INSERT INTO `study_group_contents` (`study_group_content_id`, `study_group_id`, `study_group_member_id`, `content`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, '1 Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter. Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter. Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter.', '2024-04-17 07:11:32', NULL, NULL),
(2, 1, 1, '2 Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter. Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter. Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter.Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter. Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter. Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter.Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter. Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter. Filler text is text that shares some characteristics of a real written text, but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or to spoof an e-mail spam filter.', '2024-04-17 09:49:22', NULL, NULL),
(3, 1, 1, 'content added from api', '2024-04-17 06:08:42', '2024-04-17 06:08:42', NULL),
(4, 1, 1, '<p>First content from interface added.</p>', '2024-04-17 06:23:09', '2024-04-17 06:23:09', NULL),
(5, 1, 2, '<p>Post from the second student.&nbsp;Filler text is text that shares some characteristics of a real written text but is random or otherwise generated. It may be used to display a sample of fonts, generate text for testing, or spoof an e-mail spam filter.</p>', '2024-04-17 06:52:10', '2024-04-17 06:52:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `study_group_members`
--

DROP TABLE IF EXISTS `study_group_members`;
CREATE TABLE IF NOT EXISTS `study_group_members` (
  `study_group_member_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `study_group_id` bigint(20) NOT NULL,
  `member_user_id` bigint(20) NOT NULL,
  `is_external_member` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`study_group_member_id`),
  KEY `study_group` (`study_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `study_group_members`
--

INSERT INTO `study_group_members` (`study_group_member_id`, `study_group_id`, `member_user_id`, `is_external_member`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'N', 'Active', '2024-04-17 01:40:48', '2024-04-17 01:40:48'),
(2, 1, 2, 'N', 'Active', '2024-04-17 06:25:50', '2024-04-17 06:25:50'),
(3, 2, 2, 'N', 'Active', '2024-04-17 07:20:16', '2024-04-17 07:20:16'),
(4, 2, 1, 'N', 'Active', '2024-04-17 07:24:12', '2024-04-17 07:24:12'),
(7, 1, 1, 'Y', 'Active', NULL, NULL),
(10, 1, 2, 'Y', 'Active', '2024-04-19 05:03:34', NULL),
(11, 2, 5, 'Y', 'Active', '2024-04-19 06:10:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) NOT NULL,
  `board_id` int(11) NOT NULL,
  `year_group_id` int(11) NOT NULL,
  `subject_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `subject_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`subject_id`),
  KEY `subject_academic_year` (`academic_year_id`),
  KEY `subject_board` (`board_id`),
  KEY `subject_class` (`year_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `academic_year_id`, `board_id`, `year_group_id`, `subject_name`, `description`, `subject_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Chemistry', NULL, '/uploads/portal_subject/65eed38e30428.png', 'Active', '2024-03-04 04:12:11', '2024-03-11 04:19:02'),
(2, 1, 2, 2, 'Chemistry', NULL, '/uploads/portal_subject/65eed373d09cf.png', 'Active', '2024-03-04 05:06:05', '2024-03-11 04:18:35'),
(3, 1, 2, 1, 'Physics', NULL, '/uploads/portal_subject/65eed46a27bb9.png', 'Active', '2024-03-11 04:22:42', '2024-03-11 04:22:42'),
(4, 1, 3, 1, 'English', NULL, '/uploads/portal_subject/65eed494402d0.png', 'Active', '2024-03-11 04:23:24', '2024-03-11 04:23:24'),
(5, 1, 2, 1, 'Mathematics', NULL, NULL, 'Active', '2024-03-11 04:26:24', '2024-03-11 04:26:24'),
(6, 1, 3, 2, 'English', NULL, NULL, 'Active', '2024-05-02 04:04:36', '2024-05-02 04:04:36'),
(7, 1, 3, 2, 'Biology', NULL, '/uploads/portal_subject/664482a5beba0.png', 'Inactive', '2024-05-15 04:08:46', '2024-06-06 05:52:54'),
(8, 1, 5, 2, 'Life Skill', NULL, '/uploads/portal_subject/664483896c3f6.png', 'Active', '2024-05-15 04:12:33', '2024-05-15 04:12:33'),
(9, 1, 2, 2, 'Biology', NULL, NULL, 'Active', '2024-05-29 01:38:01', '2024-05-29 01:38:01'),
(10, 1, 4, 1, 'subject 1', 'import subject 1', NULL, 'Active', '2024-05-30 06:11:59', '2024-05-30 06:11:59'),
(11, 1, 4, 1, 'subject 2', 'import subject 2', NULL, 'Active', '2024-05-30 06:11:59', '2024-05-30 06:11:59'),
(12, 1, 5, 1, 'subject 1', 'import subject 1', NULL, 'Active', '2024-05-30 06:12:48', '2024-05-30 06:12:48'),
(13, 1, 5, 1, 'subject 2', 'import subject 2', NULL, 'Active', '2024-05-30 06:12:48', '2024-05-30 06:12:48');

-- --------------------------------------------------------

--
-- Table structure for table `sub_indicators`
--

DROP TABLE IF EXISTS `sub_indicators`;
CREATE TABLE IF NOT EXISTS `sub_indicators` (
  `sub_indicator_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `indicator_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excel_column_identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sub_indicator_id`),
  KEY `ofstead_indicator_id` (`indicator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_indicators`
--

INSERT INTO `sub_indicators` (`sub_indicator_id`, `indicator_id`, `name`, `excel_column_identifier`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 'Number of Pupils', 'number_of_pupils', 'Active', '2024-04-24 09:23:04', NULL),
(2, 6, 'Number of Teachers', 'number_of_teachers', 'Active', '2024-04-24 10:15:41', NULL),
(3, 6, 'Total expenditure', 'total_expenditure', 'Active', '2024-04-24 10:15:41', NULL),
(4, 6, 'Staff total', 'staff_total', 'Active', '2024-04-24 10:15:41', NULL),
(5, 6, 'Premises total', 'premises_total', 'Active', '2024-04-24 10:15:41', NULL),
(6, 6, 'Occupation total', 'occupation_total', 'Active', '2024-04-24 10:15:41', NULL),
(7, 6, 'Supplies and services total', 'supplies_and_services_total', 'Active', '2024-04-24 10:15:41', NULL),
(8, 6, 'Interest charges for loans and banking', 'interest_charges_for_loans_and_banking', 'Active', '2024-04-24 10:15:41', NULL),
(9, 6, 'Cost of finance total', 'cost_of_finance_total', 'Active', '2024-04-24 10:15:41', NULL),
(10, 6, 'Community expenditure total', 'community_expenditure_total', 'Active', '2024-04-24 10:15:41', NULL),
(11, 6, 'Special facilities total', 'special_facilities_total', 'Active', '2024-04-24 10:15:41', NULL),
(12, 6, 'Teaching staff', 'teaching_staff', 'Active', '2024-04-24 10:15:41', NULL),
(13, 6, 'Supply staff', 'supply_staff', 'Active', '2024-04-24 10:15:41', NULL),
(14, 6, 'Education support staff', 'education_support_staff', 'Active', '2024-04-24 10:15:41', NULL),
(15, 6, 'Administrative and clerical staff', 'administrative_and_clerical_staff', 'Active', '2024-04-24 10:15:41', NULL),
(16, 6, 'Other staff costs', 'other_staff_costs', 'Active', '2024-04-24 10:15:41', NULL),
(17, 6, 'Premises staff', 'premises_staff', 'Active', '2024-04-24 10:15:41', NULL),
(18, 6, 'Cleaning and caretaking', 'cleaning_and_caretaking', 'Active', '2024-04-24 10:15:41', NULL),
(19, 6, 'Maintenance and improvement', 'maintenance_and_improvement', 'Active', '2024-04-24 10:15:41', NULL),
(20, 6, 'PFI charges', 'pfi_charges', 'Active', '2024-04-24 10:15:41', NULL),
(21, 6, 'Energy', 'energy', 'Active', '2024-04-24 10:15:41', NULL),
(22, 6, 'Water and sewerage', 'water_and_sewerage', 'Active', '2024-04-24 10:15:41', NULL),
(23, 6, 'Rates', 'rates', 'Active', '2024-04-24 10:15:41', NULL),
(24, 6, 'Other occupation costs', 'other_occupation_costs', 'Active', '2024-04-24 10:15:41', NULL),
(25, 6, 'Other insurance premiums', 'other_insurance_premiums', 'Active', '2024-04-24 10:15:41', NULL),
(26, 6, 'Catering expenditure', 'catering_expenditure', 'Active', '2024-04-24 10:15:41', NULL),
(27, 6, 'Administrative supplies', 'administrative_supplies', 'Active', '2024-04-24 10:15:41', NULL),
(28, 6, 'Educational supplies', 'educational_supplies', 'Active', '2024-04-24 10:15:41', NULL),
(29, 6, 'Bought-in professional services', 'bought_in_professional_services', 'Active', '2024-04-24 10:15:41', NULL),
(30, 6, 'Loan interest', 'loan_interest', 'Active', '2024-04-24 10:15:41', NULL),
(31, 6, 'Direct revenue financing (revenue contributions to capital)', 'direct_revenue_financing_revenue_contributions_to_capital', 'Active', '2024-04-24 10:15:41', NULL),
(32, 6, 'Community focused school staff', 'community_focused_school_staff', 'Active', '2024-04-24 10:15:41', NULL),
(33, 6, 'Community focused school costs', 'community_focused_school_costs', 'Active', '2024-04-24 10:15:41', NULL),
(34, 6, 'Total income', 'total_income', 'Active', '2024-04-24 10:15:41', NULL),
(35, 6, 'Grant funding total', 'grant_funding_total', 'Active', '2024-04-24 10:15:41', NULL),
(36, 6, 'Self-generated funding total', 'self_generated_funding_total', 'Active', '2024-04-24 10:15:41', NULL),
(37, 6, 'Direct revenue financing (capital reserves transfers)', 'direct_revenue_financing_capital_reserves_transfers', 'Active', '2024-04-24 10:15:41', NULL),
(38, 6, 'In-year balance', 'in_year_balance', 'Active', '2024-04-24 10:15:41', NULL),
(39, 6, 'Revenue reserve', 'revenue_reserve', 'Active', '2024-04-24 10:15:41', NULL),
(40, 6, 'Direct grants', 'direct_grants', 'Active', '2024-04-24 10:15:41', NULL),
(41, 6, 'Community grants', 'community_grants', 'Active', '2024-04-24 10:15:41', NULL),
(42, 6, 'Targeted grants', 'targeted_grants', 'Active', '2024-04-24 10:15:41', NULL),
(43, 6, 'Community focused school facilities income', 'community_focused_school_facilities_income', 'Active', '2024-04-24 10:15:41', NULL),
(44, 6, 'Income from facilities and services', 'income_from_facilities_and_services', 'Active', '2024-04-24 10:15:41', NULL),
(45, 6, 'Income from catering', 'income_from_catering', 'Active', '2024-04-24 10:15:41', NULL),
(46, 6, 'Donations and/or voluntary funds', 'donations_andor_voluntary_funds', 'Active', '2024-04-24 10:15:41', NULL),
(47, 6, 'Income from contributions to visits', 'income_from_contributions_to_visits', 'Active', '2024-04-24 10:15:41', NULL),
(48, 6, 'Receipts from supply teacher insurance claims', 'receipts_from_supply_teacher_insurance_claims', 'Active', '2024-04-24 10:15:41', NULL),
(49, 6, 'Receipts from other insurance claims', 'receipts_from_other_insurance_claims', 'Active', '2024-04-24 10:15:41', NULL),
(50, 6, 'School workforce (Full Time Equivalent)', 'school_workforce_full_time_equivalent', 'Active', '2024-04-24 10:15:41', NULL),
(51, 6, 'Total number of teachers (Full Time Equivalent)', 'total_number_of_teachers_full_time_equivalent', 'Active', '2024-04-24 10:15:41', NULL),
(52, 6, 'Teachers with Qualified Teacher Status (%)', 'teachers_with_qualified_teacher_status', 'Active', '2024-04-24 10:15:41', NULL),
(53, 6, 'Senior leadership (Full Time Equivalent)', 'senior_leadership_full_time_equivalent', 'Active', '2024-04-24 10:15:41', NULL),
(54, 6, 'Teaching assistants (Full Time Equivalent)', 'teaching_assistants_full_time_equivalent', 'Active', '2024-04-24 10:15:41', NULL),
(55, 6, 'Non-classroom support staff - excluding auxiliary staff (Full Time Equivalent)', 'non_classroom_support_staff_excluding_auxiliary_staff_full_time_equivalent', 'Active', '2024-04-24 10:15:41', NULL),
(56, 6, 'Auxiliary staff (Full Time Equivalent)', 'auxiliary_staff_full_time_equivalent', 'Active', '2024-04-24 10:15:41', NULL),
(57, 6, 'School workforce (headcount)', 'school_workforce_headcount', 'Active', '2024-04-24 10:15:41', NULL),
(58, 6, 'Key Stage 2 attainment', 'key_stage_2_attainment', 'Active', '2024-04-24 10:15:41', NULL),
(59, 6, 'Key Stage 2 progress', 'key_stage_2_progress', 'Active', '2024-04-24 10:15:41', NULL),
(60, 6, 'Average attainment', 'average_attainment', 'Active', '2024-04-24 10:15:41', NULL),
(61, 6, 'Progress 8 measure', 'progress_8_measure', 'Active', '2024-04-24 10:15:41', NULL),
(62, 6, 'Ofsted rating', 'ofsted_rating', 'Active', '2024-04-24 10:15:41', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `teachers`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `teachers`;
CREATE TABLE IF NOT EXISTS `teachers` (
`user_profile_id` bigint(20)
,`user_id` bigint(20)
,`first_name` varchar(150)
,`middle_name` varchar(150)
,`last_name` varchar(150)
,`short_name` varchar(20)
,`ni_number` varchar(150)
,`end_date_id` date
,`id_file` varchar(255)
,`dbs_certificate_file` varchar(255)
,`end_date_dbs` date
,`address` varchar(255)
,`about` varchar(255)
,`birthday` date
,`gender` varchar(50)
,`blood_group` varchar(20)
,`batch_type_id` int(11)
,`department_id` int(11)
,`parent_name` varchar(255)
,`parent_phone` varchar(20)
,`parent_email` varchar(191)
,`created_at` timestamp
,`updated_at` timestamp
,`tenant_id` bigint(20)
,`role` enum('A','T','S','TA','OU','P')
,`email` varchar(191)
,`phone` varchar(100)
,`user_logo` varchar(512)
,`status` enum('Active','Inactive')
,`year_group_names` varchar(256)
,`year_group_ids` varchar(256)
,`subject_ids` varchar(256)
,`subject_names` varchar(256)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `teacher_assistants`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `teacher_assistants`;
CREATE TABLE IF NOT EXISTS `teacher_assistants` (
`user_profile_id` bigint(20)
,`user_id` bigint(20)
,`first_name` varchar(150)
,`middle_name` varchar(150)
,`last_name` varchar(150)
,`short_name` varchar(20)
,`ni_number` varchar(150)
,`end_date_id` date
,`id_file` varchar(255)
,`dbs_certificate_file` varchar(255)
,`end_date_dbs` date
,`address` varchar(255)
,`about` varchar(255)
,`birthday` date
,`gender` varchar(50)
,`blood_group` varchar(20)
,`batch_type_id` int(11)
,`department_id` int(11)
,`parent_name` varchar(255)
,`parent_phone` varchar(20)
,`parent_email` varchar(191)
,`created_at` timestamp
,`updated_at` timestamp
,`tenant_id` bigint(20)
,`role` enum('A','T','S','TA','OU','P')
,`email` varchar(191)
,`phone` varchar(100)
,`user_logo` varchar(512)
,`status` enum('Active','Inactive')
,`year_group_names` varchar(256)
,`year_group_ids` varchar(256)
,`subject_ids` varchar(256)
,`subject_names` varchar(256)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL DEFAULT '0',
  `user_type` enum('A','AU','SW','TA','TU','T','P') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A' COMMENT 'A=Administrator \r\nAU=Administrator User\r\nSW=Swagger User\r\nTA=Tenant Admin\r\nTU=Tenant User\r\nT=Trustee\r\nP=Parent',
  `role` enum('A','T','S','TA','OU','P') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A=Admin rights\r\nT=Teacher rights\r\nS=Student rights\r\nTA=Teacher assistant rights\r\nOU=Other User without login\r\nP=parent',
  `email` varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_logo` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `social_id_token` varchar(350) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email_user_type` (`email`,`user_type`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `tenant_id`, `user_type`, `role`, `email`, `password`, `phone`, `code`, `user_logo`, `status`, `social_id_token`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'TU', 'S', 'hh_john@heritage.com', '$2y$10$lsoKA3uKYYILcdAkCn7h/uNPYxqI6IjR2JAu36powITjgb2rN9S3q', '2222222220', '2024-2179-04', '/uploads/profile_pic/1/66879d5c5c5cc.png', 'Active', NULL, NULL, '2024-03-04 04:23:29', '2024-07-05 01:44:36'),
(2, 1, 'TU', 'S', 'hhstudent2@domain.com', '$2y$10$6l.28YH1NW1EzfMhSrnJ6.7Ye.WIbTiSVbsf5633G8xIwtjA0tf3y', '234234234', '2024-5746-39', '/uploads/profile_pic/65e6d4026df83.png', 'Active', NULL, NULL, '2024-03-05 00:36:57', '2024-03-08 00:34:33'),
(3, 1, 'TU', 'S', 'hhstudent3@domain.com', '$2y$10$8uSibVUrfkipq4alK5n8/ew8wSenB44Pn3BPWqNOt7AEUOFHdSEq.', '234234234', '2024-4286-93', '/uploads/profile_pic/1/65e6f2ae010d8.png', 'Active', NULL, NULL, '2024-03-05 00:43:03', '2024-03-08 00:34:56'),
(4, 1, 'TU', 'T', 'teacher1@domain.com', '$2y$10$ona/5pbE26c.yKp0hUFT8.uuEtW6YRoicdkzuebfFZVErJl6Xrgm2', NULL, NULL, '/uploads/profile_pic/65eee417c3212.png', 'Active', NULL, NULL, '2024-03-05 05:10:36', '2024-03-11 05:29:35'),
(5, 1, 'TU', 'T', 'teacher2@domain.com', '$2y$10$4aNBxP1jDJ6mdwqFUsPgl.D03FZo7hOIVHvUPdb2q13TPKybXWLYO', NULL, NULL, '/uploads/profile_pic/65eee79f1ff7f.png', 'Active', NULL, NULL, '2024-03-05 05:13:03', '2024-03-11 05:44:39'),
(6, 1, 'TU', 'T', 'teacher3@domain.com', '$2y$10$E5V3U/nSFqBIheWmR0.kZuYtajG8gHsTXF.WqlIwmvyJe5oKFI2uu', NULL, NULL, '/uploads/profile_pic/1/65e7ff67b20fb.png', 'Active', NULL, NULL, '2024-03-06 00:00:16', '2024-03-06 00:00:16'),
(7, 1, 'TU', 'TA', 'ta1@domain.com', '$2y$10$j6kszZSNiOWvO/rYKeqqDebRLCEprFySUIn4yzPlwOjC3x0pWAcSS', '78687676768', NULL, '/uploads/profile_pic/65eee45b76142.png', 'Active', NULL, NULL, '2024-03-06 05:42:48', '2024-03-11 05:30:43'),
(8, 1, 'TU', 'OU', 'testemp@domain.com', '$2y$10$PDJ.WC22ii203gjccSyFNOR2EcHFvv95WnkFqxBphsPx370X8m0mW', '242424234234', NULL, '/uploads/profile_pic/65eee544bc484.png', 'Active', NULL, NULL, '2024-03-07 01:36:39', '2024-03-11 05:34:36'),
(9, 1, 'TU', 'OU', 'dememp@domain.com', '$2y$10$VpmJvX0ZHhrEt6IUz/mw0.u/YcKyzgRC0AYCaS5SFk9395.LLU/r2', NULL, NULL, '/uploads/profile_pic/1/65eee6c01efdf.png', 'Active', NULL, NULL, '2024-03-11 05:40:56', '2024-03-11 05:40:56'),
(10, 1, 'TU', 'T', 'teacher4@mailinator.com', '$2y$10$dwdVpi31DnYsOC8ekbM6yuTSOwYd9dhYaiAb8fT42F8mEKJ2B6obq', NULL, NULL, NULL, 'Active', NULL, NULL, '2024-04-05 05:44:36', '2024-04-05 05:44:36'),
(12, 1, 'P', 'P', 'demoparent@domain.com', '$2y$10$4IAVv2g/AQmXkD.7zstpQurQvmGZ181x61fL.gPCbs0Rw4zbKr/8O', '07439307691', '2024964218', '/uploads/profile_pic/1/6645f4b8a84ee.png', 'Active', NULL, NULL, '2024-05-16 06:27:44', '2024-05-17 01:51:14'),
(13, 1, 'TU', 'T', 'teacher5@domain.com', '$2y$10$cSZWPug7xURm6fAG94UH/.w5V9gDi0E4/sQ/LU5k2hfdnp5exDJku', NULL, NULL, NULL, 'Active', NULL, NULL, '2024-05-22 00:26:42', '2024-05-22 00:26:42'),
(14, 1, 'TU', 'S', 'impstud@domain.com', '$2y$10$yYZpVJcBvZ8aNl.oGxNFi.RLftuacKGPxNZqGJFUk8KpmQ4FPpOIa', '277299929', '2024-0785-24', NULL, 'Active', NULL, NULL, '2024-06-14 02:36:42', '2024-06-14 02:36:42'),
(15, 1, 'TU', 'S', 'impstud1@domain.com', '$2y$10$xNkHb17NHd4eg4Y5sRTVEeVc.u/.BzxXino6ymfGNOm1xyORnXVI6', '277299929', '2024-3816-79', NULL, 'Active', NULL, NULL, '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(16, 1, 'TU', 'T', 'impteacher@domain.com', '$2y$10$XfLX13Itc2uwriy9D0/dzOVXk9XSKDwxTj/vfKqnLRyS6C/qpuyRO', '277299929', NULL, NULL, 'Active', NULL, NULL, '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(17, 1, 'TU', 'T', 'impteacher1@domain.com', '$2y$10$dyb9e72OgeGcq5lxsCubHuDZ1sIlLrBwU1VnWj6nQ70YLhi6K.q8i', '277299929', NULL, NULL, 'Active', NULL, NULL, '2024-06-14 04:49:40', '2024-06-14 04:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `user_external_study_groups`
--

DROP TABLE IF EXISTS `user_external_study_groups`;
CREATE TABLE IF NOT EXISTS `user_external_study_groups` (
  `user_external_study_group_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `group_info` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_external_study_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `user_profile_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `first_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_name` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ni_number` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_date_id` date DEFAULT NULL,
  `id_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dbs_certificate_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_date_dbs` date DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `about` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_group` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batch_type_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `parent_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `have_sensupport_healthcare_plan` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `first_lang_not_eng` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `freeschool_eligible` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `cover_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_profile_id`),
  UNIQUE KEY `user_id_profile` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_profile_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `short_name`, `ni_number`, `end_date_id`, `id_file`, `dbs_certificate_file`, `end_date_dbs`, `address`, `about`, `birthday`, `gender`, `blood_group`, `batch_type_id`, `department_id`, `parent_name`, `parent_phone`, `parent_email`, `have_sensupport_healthcare_plan`, `first_lang_not_eng`, `freeschool_eligible`, `cover_picture`, `created_at`, `updated_at`) VALUES
(1, 1, 'HH John', NULL, 'Doe', NULL, NULL, NULL, NULL, NULL, NULL, 'HH John addr', NULL, NULL, 'Male', NULL, 1, NULL, 'HH John Parent', '1111111110', 'hh_john_parent@domain.com', 'Y', 'N', 'Y', '/uploads/profile_pic/1/66879d3e4ab7d.png', '2024-03-04 04:23:29', '2024-07-05 01:44:06'),
(2, 2, 'HH Student', NULL, 'Two', NULL, NULL, NULL, NULL, NULL, NULL, 'kousick', NULL, NULL, 'Male', NULL, 2, NULL, 'kousick', '234234234', 'a@b.com', 'N', 'N', 'N', NULL, '2024-03-05 00:36:57', '2024-03-05 02:42:50'),
(3, 3, 'HH Student', NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, 'kousick', NULL, NULL, 'Male', NULL, 2, NULL, 'kousick', '234234234', 'a@b.com', 'N', 'N', 'N', NULL, '2024-03-05 00:43:03', '2024-03-05 04:53:42'),
(4, 4, 'Teacher', NULL, '1', NULL, '134422209', '2024-04-07', '/uploads/user_idfile/1/65e80cede64de.png', '/uploads/user_dbsfile/1/667d0f6dd0f42.png', NULL, 'sdsdsadsad', 'Teacher 1 about text', NULL, 'Male', NULL, NULL, 1, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-03-05 05:10:36', '2024-06-27 01:36:22'),
(5, 5, 'Teacher', NULL, '2', NULL, '134422255', '2024-03-31', '/uploads/user_idfile/1/65e8095199627.png', '/uploads/user_dbsfile/1/6618df9b58544.png', '2025-04-12', NULL, 'Teacher 2 about text', NULL, 'Male', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-03-05 05:13:03', '2024-04-12 01:45:39'),
(6, 6, 'Teacher', NULL, '3', NULL, '1344222', '2024-03-31', '/uploads/profile_pic/1/65e7ff68393cd.png', '/uploads/profile_pic/1/65e7ff683ac0e.png', '2024-04-07', 'test addre', 'Test teacher 3 about', NULL, 'Female', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-03-06 00:00:16', '2024-03-06 00:00:16'),
(7, 7, 'Teacher Assistant', NULL, '1', NULL, '13442221111', NULL, NULL, '/uploads/user_dbsfile/1/6647278e8cf2d.png', '2025-05-17', 'aSAsa Asaaa', 'ewrwwq qwee qwe', NULL, 'Female', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-03-06 05:42:48', '2024-05-17 04:16:54'),
(8, 8, 'Test', NULL, 'Employee', NULL, NULL, NULL, NULL, NULL, NULL, 'asdasd asdasd 123', NULL, NULL, 'Male', NULL, NULL, 1, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-03-07 01:36:39', '2024-03-07 01:37:05'),
(9, 9, 'Demo', NULL, 'Employee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Male', NULL, NULL, 1, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-03-11 05:40:56', '2024-03-11 05:40:56'),
(10, 10, 'Teacher', NULL, '4', NULL, '1344222', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Male', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-04-05 05:44:36', '2024-05-22 00:07:37'),
(12, 12, 'Demo', NULL, 'Parent', NULL, NULL, NULL, NULL, NULL, NULL, 'Bradley Lynch Court\r\nFlat 20\r\nMorpeth Street', NULL, NULL, 'Male', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-05-16 06:27:44', '2024-05-17 00:44:42'),
(13, 13, 'teacher', NULL, 'five', NULL, '1344221', '2024-05-22', '/uploads/user_idfile/1/664d927d5b844.png', '/uploads/user_dbsfile/1/664d89570849d.png', '2024-05-23', NULL, NULL, NULL, 'Male', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-05-22 00:26:43', '2024-05-22 01:06:45'),
(14, 14, 'import', NULL, 'student', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Male', NULL, 2, NULL, 'import student parent', NULL, 'impstudparent@domain.com', 'Y', 'N', 'Y', NULL, '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(15, 15, 'import', NULL, 'student1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Female', NULL, 2, NULL, 'import student1 parent', NULL, 'impstud1parent@domain.com', 'N', 'N', 'Y', NULL, '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(16, 16, 'import', NULL, 'teacher', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Male', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(17, 17, 'import', NULL, 'teacher1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Female', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 'N', NULL, '2024-06-14 04:49:40', '2024-06-14 04:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `user_results`
--

DROP TABLE IF EXISTS `user_results`;
CREATE TABLE IF NOT EXISTS `user_results` (
  `user_result_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `examination_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `total_time_in_mins` int(11) DEFAULT NULL,
  `time_taken_inmins` int(11) DEFAULT NULL,
  `total_marks` decimal(8,2) DEFAULT NULL,
  `marks_obtained` decimal(8,2) DEFAULT NULL,
  `grade` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `is_reviewed` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `reviewer_user_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_result_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_results`
--

INSERT INTO `user_results` (`user_result_id`, `examination_id`, `user_id`, `start_time`, `end_time`, `total_time_in_mins`, `time_taken_inmins`, `total_marks`, `marks_obtained`, `grade`, `grade_id`, `is_reviewed`, `reviewer_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2024-04-04 08:00:06', '2024-04-04 08:06:08', 6, 6, '25.00', '18.00', NULL, NULL, 'Y', 4, '2024-04-04 02:36:09', '2024-04-04 02:41:10'),
(2, 5, 1, '2024-04-09 11:37:09', '2024-04-09 11:38:12', 25, 1, '25.00', NULL, NULL, NULL, 'N', NULL, '2024-04-09 06:08:12', '2024-04-10 00:59:30'),
(3, 1, 2, '2024-04-10 09:57:07', '2024-04-10 10:03:10', 6, 6, '25.00', '21.00', NULL, NULL, 'Y', 4, '2024-04-10 04:33:10', '2024-04-10 04:34:55'),
(4, 1, 3, '2024-04-30 09:24:35', '2024-04-30 09:44:02', 6, 19, '25.00', '18.00', 'B', 3, 'Y', 4, '2024-04-30 04:18:19', '2024-04-30 05:26:25'),
(5, 1, 3, '2024-04-30 09:24:35', '2024-04-30 09:44:02', 6, 19, '25.00', '18.00', NULL, NULL, 'Y', 4, '2024-04-30 04:20:00', '2024-04-30 04:41:04'),
(6, 5, 3, '2024-05-02 05:00:10', '2024-05-02 05:02:08', 25, 2, '25.00', '23.00', 'A+', 1, 'Y', 4, '2024-05-01 23:32:10', '2024-05-01 23:49:16'),
(7, 3, 1, '2024-05-10 10:12:16', '2024-05-10 10:17:26', 17, 5, '15.00', '15.00', 'A+', 1, 'Y', 4, '2024-05-10 04:47:27', '2024-05-10 04:48:13'),
(8, 35, 1, '2024-05-10 11:02:36', '2024-05-10 11:04:53', 30, 2, '30.00', '28.00', 'A+', 1, 'Y', 4, '2024-05-10 05:34:54', '2024-05-10 06:38:52'),
(9, 36, 1, '2024-05-15 09:04:44', '2024-05-15 09:04:57', 5, 0, '5.00', NULL, NULL, NULL, 'N', NULL, '2024-05-15 03:34:58', '2024-05-15 03:34:58'),
(10, 33, 1, '2024-07-11 07:12:06', '2024-07-11 07:19:34', 36, 7, '36.00', NULL, NULL, NULL, 'N', NULL, '2024-07-11 01:49:35', '2024-07-11 01:49:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_result_inputs`
--

DROP TABLE IF EXISTS `user_result_inputs`;
CREATE TABLE IF NOT EXISTS `user_result_inputs` (
  `user_result_input_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_result_id` bigint(20) NOT NULL,
  `examination_question_id` bigint(20) NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci,
  `answer_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment_file` text COLLATE utf8mb4_unicode_ci,
  `marks_given` decimal(8,2) DEFAULT NULL,
  `time_taken_inmins` decimal(5,2) DEFAULT NULL,
  `reviewer_comments` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_result_input_id`),
  KEY `user_result_fk` (`user_result_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_result_inputs`
--

INSERT INTO `user_result_inputs` (`user_result_input_id`, `user_result_id`, `examination_question_id`, `answer`, `answer_status`, `attachment_file`, `marks_given`, `time_taken_inmins`, `reviewer_comments`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2', 'Incorrect', NULL, '0.00', '0.18', NULL, '2024-04-04 02:36:09', '2024-04-04 02:41:10'),
(2, 1, 4, '2', 'Correct', NULL, '2.00', '0.07', NULL, '2024-04-04 02:36:09', '2024-04-04 02:41:10'),
(3, 1, 6, '2', 'Correct', NULL, '2.00', '0.12', NULL, '2024-04-04 02:36:09', '2024-04-04 02:41:10'),
(4, 1, 7, NULL, 'Not Attempted', NULL, '0.00', '0.07', NULL, '2024-04-04 02:36:09', '2024-04-04 02:41:10'),
(5, 1, 8, 'Free text question with no option?', 'Correct', NULL, '4.00', '0.42', 'eid mubarak', '2024-04-04 02:36:09', '2024-04-04 02:41:10'),
(6, 1, 9, 'Describe process of photosynthesis?', 'Correct', '/uploads/user_attachments/1/1/660e5f7163dca.pdf', '10.00', '5.15', 'attachment not proper', '2024-04-04 02:36:09', '2024-04-04 02:41:10'),
(7, 2, 10, '<p>What is Acid, Base &amp; Salts</p>', 'Correct', NULL, '6.00', '0.00', 'need more information', '2024-04-09 06:08:12', '2024-04-10 00:59:30'),
(8, 2, 11, '<p>What is the boiling point of any liquid</p>', 'Correct', NULL, '2.00', '0.00', 'need more information', '2024-04-09 06:08:12', '2024-04-10 00:59:30'),
(9, 2, 12, '<p>What is the boiling point of water and sulphuric acid</p>', 'Correct', NULL, '2.00', '0.00', NULL, '2024-04-09 06:08:12', '2024-04-10 00:59:30'),
(10, 2, 13, '<p>Define the marked parts of the Cell</p>', 'Correct', NULL, '7.00', '0.00', 'need more information', '2024-04-09 06:08:12', '2024-04-10 00:59:30'),
(11, 3, 1, '2', 'Incorrect', NULL, '0.00', '0.07', NULL, '2024-04-10 04:33:10', '2024-04-10 04:34:55'),
(12, 3, 4, '2', 'Correct', NULL, '2.00', '0.08', NULL, '2024-04-10 04:33:10', '2024-04-10 04:34:55'),
(13, 3, 6, '2', 'Correct', NULL, '2.00', '0.12', NULL, '2024-04-10 04:33:10', '2024-04-10 04:34:55'),
(14, 3, 7, '1', 'Incorrect', NULL, '0.00', '0.22', NULL, '2024-04-10 04:33:10', '2024-04-10 04:34:55'),
(15, 3, 8, 'hello world we are waiting for clear sky with no pollution around.', 'Correct', NULL, '5.00', '0.65', NULL, '2024-04-10 04:33:10', '2024-04-10 04:34:55'),
(16, 3, 9, 'The process of plants making food is called photosynthesis. Key elements required for this are sunlight, oxygen & chlorophyll.\r\nleaves of plants containing a green pigment called chlorophyll. Plants make their food by synthesizing glucose from chlorophyll with the help of oxygen and sunlight and releasing back oxygen to the environment. These simple glucose molecules are converted into starch and stored in plant leaves and branches for later use as food.', 'Correct', '/uploads/user_attachments/1/2/661663deafd7e.pdf', '12.00', '4.87', NULL, '2024-04-10 04:33:10', '2024-04-10 04:34:55'),
(17, 4, 1, '2', 'Incorrect', NULL, '0.00', '0.03', NULL, '2024-04-30 04:18:19', '2024-04-30 05:26:25'),
(18, 4, 4, '2', 'Correct', NULL, '2.00', '0.02', NULL, '2024-04-30 04:18:19', '2024-04-30 05:09:20'),
(19, 4, 6, '2', 'Correct', NULL, '2.00', '0.02', NULL, '2024-04-30 04:18:19', '2024-04-30 05:09:20'),
(20, 4, 7, '1', 'Incorrect', NULL, '0.00', '0.02', NULL, '2024-04-30 04:18:19', '2024-04-30 05:26:25'),
(21, 4, 8, 'Free text question with no option', 'Correct', NULL, '4.00', '0.02', NULL, '2024-04-30 04:18:19', '2024-04-30 05:26:25'),
(22, 5, 1, '2', 'Attempted', NULL, '0.00', '0.03', NULL, '2024-04-30 04:20:00', '2024-04-30 04:41:04'),
(23, 5, 4, '2', 'Attempted', NULL, '0.00', '0.02', NULL, '2024-04-30 04:20:00', '2024-04-30 04:41:04'),
(24, 5, 6, '2', 'Attempted', NULL, '0.00', '0.02', NULL, '2024-04-30 04:20:00', '2024-04-30 04:41:04'),
(25, 5, 7, '1', 'Attempted', NULL, '0.00', '0.02', NULL, '2024-04-30 04:20:00', '2024-04-30 04:41:04'),
(26, 5, 8, NULL, 'Not Attempted', NULL, '0.00', '0.02', NULL, '2024-04-30 04:20:00', '2024-04-30 04:41:04'),
(27, 5, 9, 'Describe process of photosynthesis', 'Attempted', '[\"\\/uploads\\/user_attachments\\/1\\/3\\/6630bec82049f.jpe\",\"\\/uploads\\/user_attachments\\/1\\/3\\/6630bec822228.jpe\",\"\\/uploads\\/user_attachments\\/1\\/3\\/6630bec8224ed.jpe\"]', '0.00', '0.20', NULL, '2024-04-30 04:20:00', '2024-04-30 04:41:04'),
(28, 4, 9, 'cfscczxcz', 'Correct', NULL, '10.00', NULL, NULL, NULL, '2024-04-30 05:26:25'),
(29, 6, 10, '<p>asda dasdsa asd as</p>', 'Correct', NULL, '10.00', '0.00', NULL, '2024-05-01 23:32:10', '2024-05-01 23:49:16'),
(30, 6, 11, '<p>a sf as afas fasfsaf&nbsp;</p>', 'Correct', NULL, '2.00', '0.00', NULL, '2024-05-01 23:32:10', '2024-05-01 23:49:16'),
(31, 6, 12, '<p>&nbsp;agdsgg g gd gag&nbsp; &nbsp;gdg g&nbsp; g g g</p>', 'Correct', NULL, '2.00', '0.00', NULL, '2024-05-01 23:32:10', '2024-05-01 23:49:16'),
(32, 6, 13, '<p>fa g d dg dadgas ds gd sgasd ad agd sag g&nbsp; &nbsp; ggag gsdagdas g</p>', 'Correct', '[\"\\/uploads\\/user_attachments\\/5\\/3\\/66331e5241c76.png\",\"\\/uploads\\/user_attachments\\/5\\/3\\/66331e527a082.pdf\"]', '9.00', '0.00', NULL, '2024-05-01 23:32:10', '2024-05-01 23:49:16'),
(33, 7, 56, '3', 'Correct', NULL, '5.00', '0.10', NULL, '2024-05-10 04:47:27', '2024-05-10 04:48:13'),
(34, 7, 57, 'Photosynthesis is a process by which plants make their food in form of glucose & starch.\r\nPlant leaves and green branches contains a protein substance called chlorophyll. Plants uses sunlight and carbodioxide to decompose chlorophyl into simple glucose and releases oxygen & water into atmosphere. These simple glucose are stored in leaf veins in form of starch as stored food.\r\nThis whole process is call photosynthesis.', 'Correct', '[\"\\/uploads\\/user_attachments\\/3\\/1\\/663df43713b46.png\"]', '10.00', '5.03', NULL, '2024-05-10 04:47:27', '2024-05-10 04:48:13'),
(35, 8, 38, '<p>zdf zdg dg dgsdfg sdf gdsf gsdf gfsd gdfsg df sd</p>', 'Correct', NULL, '10.00', '0.00', 'sub question L1 S1 remarks', '2024-05-10 05:34:54', '2024-05-10 06:38:52'),
(36, 8, 39, '<p>s dfgdfs&nbsp; gfdsgsdfgdf gdf gsdf fdg</p>', 'Correct', NULL, '4.00', '0.00', 'sub question L1 S2 remarks', '2024-05-10 05:34:54', '2024-05-10 06:38:52'),
(37, 8, 40, '<p>s fdg fsd sgfdsgfdshhresh resre her er er</p>', 'Correct', NULL, '10.00', '0.00', 'sub question L1 S3remarks', '2024-05-10 05:34:54', '2024-05-10 06:38:52'),
(38, 8, 58, '<p>&nbsp;rgrsg rgershs resgsre gerg erg regerh</p>', 'Correct', NULL, '4.00', '0.00', 'Single question 1 remarks', '2024-05-10 05:34:54', '2024-05-10 06:38:52'),
(39, 9, 59, '2', 'Attempted', NULL, NULL, '0.18', NULL, '2024-05-15 03:34:58', '2024-05-15 03:34:58'),
(40, 10, 33, '<p>aaa</p>', 'Attempted', NULL, NULL, '0.00', NULL, '2024-07-11 01:49:35', '2024-07-11 01:49:35'),
(41, 10, 34, '<p>aaaas</p>', 'Attempted', NULL, NULL, '0.00', NULL, '2024-07-11 01:49:35', '2024-07-11 01:49:35'),
(42, 10, 49, NULL, 'Attempted', '[\"\\/uploads\\/user_attachments\\/33\\/1\\/668f8787b2a08.pdf\"]', NULL, '0.00', NULL, '2024-07-11 01:49:35', '2024-07-11 01:49:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_siblings`
--

DROP TABLE IF EXISTS `user_siblings`;
CREATE TABLE IF NOT EXISTS `user_siblings` (
  `user_sibling_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_user_id` bigint(20) NOT NULL,
  `sibling_user_id` bigint(20) NOT NULL,
  `token` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_sibling_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_siblings`
--

INSERT INTO `user_siblings` (`user_sibling_id`, `parent_user_id`, `sibling_user_id`, `token`, `status`, `created_at`, `updated_at`) VALUES
(1, 12, 1, NULL, 'Active', '2024-05-21 06:45:40', '2024-05-21 06:54:01'),
(2, 12, 2, NULL, 'Active', '2024-06-18 01:38:11', '2024-06-18 01:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `user_subjects`
--

DROP TABLE IF EXISTS `user_subjects`;
CREATE TABLE IF NOT EXISTS `user_subjects` (
  `user_subject_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_subject_id`),
  KEY `user_subject_subject` (`subject_id`),
  KEY `user_subject_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_subjects`
--

INSERT INTO `user_subjects` (`user_subject_id`, `user_id`, `subject_id`, `grade_id`, `status`, `created_at`, `updated_at`) VALUES
(12, 6, 1, NULL, 'Active', '2024-03-06 00:00:16', '2024-03-06 00:00:16'),
(13, 6, 2, NULL, 'Active', '2024-03-06 00:00:16', '2024-03-06 00:00:16'),
(35, 3, 1, NULL, 'Active', '2024-03-08 00:34:56', '2024-03-08 00:34:56'),
(36, 3, 2, NULL, 'Active', '2024-03-08 00:34:56', '2024-03-08 00:34:56'),
(48, 2, 1, NULL, 'Active', '2024-03-11 04:38:25', '2024-03-11 04:38:25'),
(49, 2, 2, NULL, 'Active', '2024-03-11 04:38:25', '2024-03-11 04:38:25'),
(76, 7, 1, NULL, 'Active', '2024-05-17 04:16:54', '2024-05-17 04:16:54'),
(77, 7, 2, NULL, 'Active', '2024-05-17 04:16:54', '2024-05-17 04:16:54'),
(78, 10, 1, NULL, 'Active', '2024-05-22 00:07:37', '2024-05-22 00:07:37'),
(79, 10, 3, NULL, 'Active', '2024-05-22 00:07:37', '2024-05-22 00:07:37'),
(80, 10, 4, NULL, 'Active', '2024-05-22 00:07:37', '2024-05-22 00:07:37'),
(81, 10, 5, NULL, 'Active', '2024-05-22 00:07:37', '2024-05-22 00:07:37'),
(88, 13, 1, NULL, 'Active', '2024-05-22 01:06:45', '2024-05-22 01:06:45'),
(89, 13, 2, NULL, 'Active', '2024-05-22 01:06:45', '2024-05-22 01:06:45'),
(90, 14, 1, NULL, 'Active', '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(91, 14, 4, NULL, 'Active', '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(92, 14, 5, NULL, 'Active', '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(93, 15, 1, NULL, 'Active', '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(94, 15, 4, NULL, 'Active', '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(95, 15, 5, NULL, 'Active', '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(96, 16, 1, NULL, 'Active', '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(97, 16, 2, NULL, 'Active', '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(98, 17, 1, NULL, 'Active', '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(99, 17, 2, NULL, 'Active', '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(106, 5, 1, NULL, 'Active', '2024-06-18 06:32:29', '2024-06-18 06:32:29'),
(107, 5, 2, NULL, 'Active', '2024-06-18 06:32:29', '2024-06-18 06:32:29'),
(108, 4, 1, NULL, 'Active', '2024-06-27 01:36:22', '2024-06-27 01:36:22'),
(109, 4, 2, NULL, 'Active', '2024-06-27 01:36:22', '2024-06-27 01:36:22'),
(110, 4, 4, NULL, 'Active', '2024-06-27 01:36:22', '2024-06-27 01:36:22'),
(111, 4, 6, NULL, 'Active', '2024-06-27 01:36:22', '2024-06-27 01:36:22'),
(112, 4, 5, NULL, 'Active', '2024-06-27 01:36:22', '2024-06-27 01:36:22'),
(113, 4, 3, NULL, 'Active', '2024-06-27 01:36:22', '2024-06-27 01:36:22'),
(114, 1, 1, NULL, 'Active', '2024-07-01 07:49:39', '2024-07-01 07:49:39'),
(115, 1, 4, NULL, 'Active', '2024-07-01 07:49:39', '2024-07-01 07:49:39'),
(116, 1, 5, NULL, 'Active', '2024-07-01 07:49:39', '2024-07-01 07:49:39'),
(117, 1, 3, NULL, 'Active', '2024-07-01 07:49:39', '2024-07-01 07:49:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_year_groups`
--

DROP TABLE IF EXISTS `user_year_groups`;
CREATE TABLE IF NOT EXISTS `user_year_groups` (
  `user_year_group_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `year_group_id` int(11) DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_year_group_id`),
  KEY `user_class_class` (`year_group_id`),
  KEY `user_class_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_year_groups`
--

INSERT INTO `user_year_groups` (`user_year_group_id`, `user_id`, `year_group_id`, `status`, `created_at`, `updated_at`) VALUES
(12, 6, 1, 'Active', '2024-03-06 00:00:16', '2024-03-06 00:00:16'),
(13, 6, 2, 'Active', '2024-03-06 00:00:16', '2024-03-06 00:00:16'),
(35, 3, 1, 'Active', '2024-03-08 00:34:56', '2024-03-08 00:34:56'),
(36, 3, 2, 'Active', '2024-03-08 00:34:56', '2024-03-08 00:34:56'),
(45, 2, 1, 'Active', '2024-03-11 04:38:25', '2024-03-11 04:38:25'),
(46, 2, 2, 'Active', '2024-03-11 04:38:25', '2024-03-11 04:38:25'),
(60, 7, 1, 'Active', '2024-05-17 04:16:54', '2024-05-17 04:16:54'),
(61, 7, 2, 'Active', '2024-05-17 04:16:54', '2024-05-17 04:16:54'),
(62, 10, 1, 'Active', '2024-05-22 00:07:37', '2024-05-22 00:07:37'),
(69, 13, 1, 'Active', '2024-05-22 01:06:45', '2024-05-22 01:06:45'),
(70, 13, 2, 'Active', '2024-05-22 01:06:45', '2024-05-22 01:06:45'),
(71, 14, 1, 'Active', '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(72, 15, 1, 'Active', '2024-06-14 02:36:43', '2024-06-14 02:36:43'),
(73, 16, 1, 'Active', '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(74, 16, 2, 'Active', '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(75, 17, 1, 'Active', '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(76, 17, 2, 'Active', '2024-06-14 04:49:40', '2024-06-14 04:49:40'),
(79, 5, 1, 'Active', '2024-06-18 06:32:29', '2024-06-18 06:32:29'),
(80, 5, 2, 'Active', '2024-06-18 06:32:29', '2024-06-18 06:32:29'),
(81, 4, 1, 'Active', '2024-06-27 01:36:22', '2024-06-27 01:36:22'),
(82, 4, 2, 'Active', '2024-06-27 01:36:22', '2024-06-27 01:36:22'),
(83, 1, 1, 'Active', '2024-07-01 07:49:39', '2024-07-01 07:49:39');

-- --------------------------------------------------------

--
-- Table structure for table `year_groups`
--

DROP TABLE IF EXISTS `year_groups`;
CREATE TABLE IF NOT EXISTS `year_groups` (
  `year_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `one_one` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`year_group_id`),
  KEY `class_academic_year` (`academic_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `year_groups`
--

INSERT INTO `year_groups` (`year_group_id`, `academic_year_id`, `name`, `one_one`, `group`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Year 9', '1:1', 'group', 'Active', '2024-03-04 04:11:45', '2024-03-04 04:11:45'),
(2, 1, 'Year 8', '1:1', 'group', 'Active', '2024-03-04 05:05:44', '2024-03-04 05:05:44'),
(3, 2, 'Year 9', '1:1', 'group', 'Active', '2024-04-12 05:33:11', '2024-04-12 05:33:11'),
(4, 1, 'Year 7', '1:1', 'group', 'Active', '2024-06-02 23:59:49', '2024-06-02 23:59:49'),
(5, 1, 'GCSE 8', '1:1', 'group', 'Inactive', '2024-06-02 23:59:49', '2024-06-06 05:41:16');

-- --------------------------------------------------------

--
-- Structure for view `employees`
--
DROP TABLE IF EXISTS `employees`;

DROP VIEW IF EXISTS `employees`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `employees`  AS SELECT `user_profiles`.`user_profile_id` AS `user_profile_id`, `user_profiles`.`user_id` AS `user_id`, `user_profiles`.`first_name` AS `first_name`, `user_profiles`.`middle_name` AS `middle_name`, `user_profiles`.`last_name` AS `last_name`, `user_profiles`.`short_name` AS `short_name`, `user_profiles`.`ni_number` AS `ni_number`, `user_profiles`.`end_date_id` AS `end_date_id`, `user_profiles`.`id_file` AS `id_file`, `user_profiles`.`dbs_certificate_file` AS `dbs_certificate_file`, `user_profiles`.`end_date_dbs` AS `end_date_dbs`, `user_profiles`.`address` AS `address`, `user_profiles`.`about` AS `about`, `user_profiles`.`birthday` AS `birthday`, `user_profiles`.`gender` AS `gender`, `user_profiles`.`blood_group` AS `blood_group`, `user_profiles`.`batch_type_id` AS `batch_type_id`, `user_profiles`.`department_id` AS `department_id`, `user_profiles`.`parent_name` AS `parent_name`, `user_profiles`.`parent_phone` AS `parent_phone`, `user_profiles`.`parent_email` AS `parent_email`, `user_profiles`.`created_at` AS `created_at`, `user_profiles`.`updated_at` AS `updated_at`, `users`.`tenant_id` AS `tenant_id`, `users`.`role` AS `role`, `users`.`email` AS `email`, `users`.`phone` AS `phone`, `users`.`user_logo` AS `user_logo`, `users`.`status` AS `status`, (select `departments`.`department_name` from `departments` where (`departments`.`department_id` = `user_profiles`.`department_id`)) AS `department_name` FROM (`users` join `user_profiles` on((`user_profiles`.`user_id` = `users`.`user_id`))) WHERE ((`users`.`user_type` = 'TU') AND (`users`.`role` = 'OU')) ORDER BY `users`.`user_id` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `parents`
--
DROP TABLE IF EXISTS `parents`;

DROP VIEW IF EXISTS `parents`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `parents`  AS SELECT `user_profiles`.`user_profile_id` AS `user_profile_id`, `user_profiles`.`user_id` AS `user_id`, `user_profiles`.`first_name` AS `first_name`, `user_profiles`.`middle_name` AS `middle_name`, `user_profiles`.`last_name` AS `last_name`, `user_profiles`.`short_name` AS `short_name`, `user_profiles`.`ni_number` AS `ni_number`, `user_profiles`.`end_date_id` AS `end_date_id`, `user_profiles`.`id_file` AS `id_file`, `user_profiles`.`dbs_certificate_file` AS `dbs_certificate_file`, `user_profiles`.`end_date_dbs` AS `end_date_dbs`, `user_profiles`.`address` AS `address`, `user_profiles`.`about` AS `about`, `user_profiles`.`birthday` AS `birthday`, `user_profiles`.`gender` AS `gender`, `user_profiles`.`blood_group` AS `blood_group`, `user_profiles`.`batch_type_id` AS `batch_type_id`, `user_profiles`.`department_id` AS `department_id`, `user_profiles`.`parent_name` AS `parent_name`, `user_profiles`.`parent_phone` AS `parent_phone`, `user_profiles`.`parent_email` AS `parent_email`, `user_profiles`.`have_sensupport_healthcare_plan` AS `have_sensupport_healthcare_plan`, `user_profiles`.`first_lang_not_eng` AS `first_lang_not_eng`, `user_profiles`.`freeschool_eligible` AS `freeschool_eligible`, `user_profiles`.`created_at` AS `created_at`, `user_profiles`.`updated_at` AS `updated_at`, `users`.`tenant_id` AS `tenant_id`, `users`.`role` AS `role`, `users`.`email` AS `email`, `users`.`code` AS `code`, `users`.`phone` AS `phone`, `users`.`user_logo` AS `user_logo`, `users`.`status` AS `status` FROM (`users` join `user_profiles` on((`user_profiles`.`user_id` = `users`.`user_id`))) WHERE ((`users`.`user_type` = 'P') AND (`users`.`role` = 'P')) ORDER BY `users`.`user_id` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `students`
--
DROP TABLE IF EXISTS `students`;

DROP VIEW IF EXISTS `students`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `students`  AS SELECT `user_profiles`.`user_profile_id` AS `user_profile_id`, `user_profiles`.`user_id` AS `user_id`, `user_profiles`.`first_name` AS `first_name`, `user_profiles`.`middle_name` AS `middle_name`, `user_profiles`.`last_name` AS `last_name`, `user_profiles`.`short_name` AS `short_name`, `user_profiles`.`ni_number` AS `ni_number`, `user_profiles`.`end_date_id` AS `end_date_id`, `user_profiles`.`id_file` AS `id_file`, `user_profiles`.`dbs_certificate_file` AS `dbs_certificate_file`, `user_profiles`.`end_date_dbs` AS `end_date_dbs`, `user_profiles`.`address` AS `address`, `user_profiles`.`about` AS `about`, `user_profiles`.`birthday` AS `birthday`, `user_profiles`.`gender` AS `gender`, `user_profiles`.`blood_group` AS `blood_group`, `user_profiles`.`batch_type_id` AS `batch_type_id`, `user_profiles`.`department_id` AS `department_id`, `user_profiles`.`parent_name` AS `parent_name`, `user_profiles`.`parent_phone` AS `parent_phone`, `user_profiles`.`parent_email` AS `parent_email`, `user_profiles`.`have_sensupport_healthcare_plan` AS `have_sensupport_healthcare_plan`, `user_profiles`.`first_lang_not_eng` AS `first_lang_not_eng`, `user_profiles`.`freeschool_eligible` AS `freeschool_eligible`, `user_profiles`.`cover_picture` AS `cover_picture`, `user_profiles`.`created_at` AS `created_at`, `user_profiles`.`updated_at` AS `updated_at`, `users`.`tenant_id` AS `tenant_id`, `users`.`role` AS `role`, `users`.`email` AS `email`, `users`.`phone` AS `phone`, `users`.`user_logo` AS `user_logo`, `users`.`status` AS `status`, `users`.`code` AS `code`, (select group_concat(`year_groups`.`name` separator ',') from (`user_year_groups` join `year_groups`) where ((`year_groups`.`year_group_id` = `user_year_groups`.`year_group_id`) and (`user_year_groups`.`user_id` = `users`.`user_id`))) AS `year_group_names`, (select group_concat(`user_year_groups`.`year_group_id` separator ',') from `user_year_groups` where (`user_year_groups`.`user_id` = `users`.`user_id`)) AS `year_group_ids`, (select group_concat(`user_subjects`.`subject_id` separator ',') from `user_subjects` where (`user_subjects`.`user_id` = `users`.`user_id`)) AS `subject_ids`, (select group_concat(concat(`year_groups`.`name`,'-',`subjects`.`subject_name`),' ' separator ',') from ((`user_subjects` join `subjects`) join `year_groups`) where ((`subjects`.`subject_id` = `user_subjects`.`subject_id`) and (`subjects`.`year_group_id` = `year_groups`.`year_group_id`) and (`user_subjects`.`user_id` = `users`.`user_id`))) AS `subject_names` FROM (`users` join `user_profiles` on((`user_profiles`.`user_id` = `users`.`user_id`))) WHERE ((`users`.`user_type` = 'TU') AND (`users`.`role` = 'S')) ORDER BY `users`.`user_id` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `teachers`
--
DROP TABLE IF EXISTS `teachers`;

DROP VIEW IF EXISTS `teachers`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `teachers`  AS SELECT `user_profiles`.`user_profile_id` AS `user_profile_id`, `user_profiles`.`user_id` AS `user_id`, `user_profiles`.`first_name` AS `first_name`, `user_profiles`.`middle_name` AS `middle_name`, `user_profiles`.`last_name` AS `last_name`, `user_profiles`.`short_name` AS `short_name`, `user_profiles`.`ni_number` AS `ni_number`, `user_profiles`.`end_date_id` AS `end_date_id`, `user_profiles`.`id_file` AS `id_file`, `user_profiles`.`dbs_certificate_file` AS `dbs_certificate_file`, `user_profiles`.`end_date_dbs` AS `end_date_dbs`, `user_profiles`.`address` AS `address`, `user_profiles`.`about` AS `about`, `user_profiles`.`birthday` AS `birthday`, `user_profiles`.`gender` AS `gender`, `user_profiles`.`blood_group` AS `blood_group`, `user_profiles`.`batch_type_id` AS `batch_type_id`, `user_profiles`.`department_id` AS `department_id`, `user_profiles`.`parent_name` AS `parent_name`, `user_profiles`.`parent_phone` AS `parent_phone`, `user_profiles`.`parent_email` AS `parent_email`, `user_profiles`.`created_at` AS `created_at`, `user_profiles`.`updated_at` AS `updated_at`, `users`.`tenant_id` AS `tenant_id`, `users`.`role` AS `role`, `users`.`email` AS `email`, `users`.`phone` AS `phone`, `users`.`user_logo` AS `user_logo`, `users`.`status` AS `status`, (select group_concat(`year_groups`.`name` separator ',') from (`user_year_groups` join `year_groups`) where ((`year_groups`.`year_group_id` = `user_year_groups`.`year_group_id`) and (`user_year_groups`.`user_id` = `users`.`user_id`))) AS `year_group_names`, (select group_concat(`user_year_groups`.`year_group_id` separator ',') from `user_year_groups` where (`user_year_groups`.`user_id` = `users`.`user_id`)) AS `year_group_ids`, (select group_concat(`user_subjects`.`subject_id` separator ',') from `user_subjects` where (`user_subjects`.`user_id` = `users`.`user_id`)) AS `subject_ids`, (select group_concat(concat(`year_groups`.`name`,'-',`subjects`.`subject_name`),' ' separator ',') from ((`user_subjects` join `subjects`) join `year_groups`) where ((`subjects`.`subject_id` = `user_subjects`.`subject_id`) and (`subjects`.`year_group_id` = `year_groups`.`year_group_id`) and (`user_subjects`.`user_id` = `users`.`user_id`))) AS `subject_names` FROM (`users` join `user_profiles` on((`user_profiles`.`user_id` = `users`.`user_id`))) WHERE ((`users`.`user_type` = 'TU') AND (`users`.`role` = 'T')) ORDER BY `users`.`user_id` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `teacher_assistants`
--
DROP TABLE IF EXISTS `teacher_assistants`;

DROP VIEW IF EXISTS `teacher_assistants`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `teacher_assistants`  AS SELECT `user_profiles`.`user_profile_id` AS `user_profile_id`, `user_profiles`.`user_id` AS `user_id`, `user_profiles`.`first_name` AS `first_name`, `user_profiles`.`middle_name` AS `middle_name`, `user_profiles`.`last_name` AS `last_name`, `user_profiles`.`short_name` AS `short_name`, `user_profiles`.`ni_number` AS `ni_number`, `user_profiles`.`end_date_id` AS `end_date_id`, `user_profiles`.`id_file` AS `id_file`, `user_profiles`.`dbs_certificate_file` AS `dbs_certificate_file`, `user_profiles`.`end_date_dbs` AS `end_date_dbs`, `user_profiles`.`address` AS `address`, `user_profiles`.`about` AS `about`, `user_profiles`.`birthday` AS `birthday`, `user_profiles`.`gender` AS `gender`, `user_profiles`.`blood_group` AS `blood_group`, `user_profiles`.`batch_type_id` AS `batch_type_id`, `user_profiles`.`department_id` AS `department_id`, `user_profiles`.`parent_name` AS `parent_name`, `user_profiles`.`parent_phone` AS `parent_phone`, `user_profiles`.`parent_email` AS `parent_email`, `user_profiles`.`created_at` AS `created_at`, `user_profiles`.`updated_at` AS `updated_at`, `users`.`tenant_id` AS `tenant_id`, `users`.`role` AS `role`, `users`.`email` AS `email`, `users`.`phone` AS `phone`, `users`.`user_logo` AS `user_logo`, `users`.`status` AS `status`, (select group_concat(`year_groups`.`name` separator ',') from (`user_year_groups` join `year_groups`) where ((`year_groups`.`year_group_id` = `user_year_groups`.`year_group_id`) and (`user_year_groups`.`user_id` = `users`.`user_id`))) AS `year_group_names`, (select group_concat(`user_year_groups`.`year_group_id` separator ',') from `user_year_groups` where (`user_year_groups`.`user_id` = `users`.`user_id`)) AS `year_group_ids`, (select group_concat(`user_subjects`.`subject_id` separator ',') from `user_subjects` where (`user_subjects`.`user_id` = `users`.`user_id`)) AS `subject_ids`, (select group_concat(concat(`year_groups`.`name`,'-',`subjects`.`subject_name`),' ' separator ',') from ((`user_subjects` join `subjects`) join `year_groups`) where ((`subjects`.`subject_id` = `user_subjects`.`subject_id`) and (`subjects`.`year_group_id` = `year_groups`.`year_group_id`) and (`user_subjects`.`user_id` = `users`.`user_id`))) AS `subject_names` FROM (`users` join `user_profiles` on((`user_profiles`.`user_id` = `users`.`user_id`))) WHERE ((`users`.`user_type` = 'TU') AND (`users`.`role` = 'TA')) ORDER BY `users`.`user_id` ASC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `examination_questions`
--
ALTER TABLE `examination_questions`
  ADD CONSTRAINT `examination_questions_ibfk_1` FOREIGN KEY (`examination_id`) REFERENCES `examinations` (`examination_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `question_fk` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lesson_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `libraries`
--
ALTER TABLE `libraries`
  ADD CONSTRAINT `libraries_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`);

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_option_fk` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `study_group_members`
--
ALTER TABLE `study_group_members`
  ADD CONSTRAINT `study_group` FOREIGN KEY (`study_group_id`) REFERENCES `study_groups` (`study_group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subject_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`academic_year_id`),
  ADD CONSTRAINT `subject_year_group` FOREIGN KEY (`year_group_id`) REFERENCES `year_groups` (`year_group_id`);

--
-- Constraints for table `sub_indicators`
--
ALTER TABLE `sub_indicators`
  ADD CONSTRAINT `sub_indicators_ibfk_1` FOREIGN KEY (`indicator_id`) REFERENCES `indicators` (`indicator_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_userprofile` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user_result_inputs`
--
ALTER TABLE `user_result_inputs`
  ADD CONSTRAINT `user_result_fk` FOREIGN KEY (`user_result_id`) REFERENCES `user_results` (`user_result_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_subjects`
--
ALTER TABLE `user_subjects`
  ADD CONSTRAINT `user_subject_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `user_subject_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_year_groups`
--
ALTER TABLE `user_year_groups`
  ADD CONSTRAINT `user_class_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_yeargroup_yeargroup` FOREIGN KEY (`year_group_id`) REFERENCES `year_groups` (`year_group_id`);

--
-- Constraints for table `year_groups`
--
ALTER TABLE `year_groups`
  ADD CONSTRAINT `class_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`academic_year_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
