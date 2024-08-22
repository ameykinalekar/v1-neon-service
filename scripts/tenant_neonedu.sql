-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 09, 2024 at 12:15 PM
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
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`academic_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
--
-- Dumping data for table `batch_types`
--

INSERT INTO `batch_types` (`batch_type_id`, `name`, `amount`, `date`, `status`) VALUES
(1, 'one:one', '40', '2023-04-26 06:07:12', 'Active'),
(2, 'group', '80', '2023-04-26 06:07:12', 'Active');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `examination_questions`
--

DROP TABLE IF EXISTS `examination_questions`;
CREATE TABLE IF NOT EXISTS `examination_questions` (
  `examination_question_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `examination_id` bigint(20) NOT NULL,
  `question_id` bigint(20) NOT NULL,
  `page_id` int(11) NOT NULL,
  `time_inseconds` int(11) NOT NULL,
  `point` decimal(10,2) NOT NULL,
  `question_info` json DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`examination_question_id`),
  KEY `examination_id` (`examination_id`),
  KEY `question_fk` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='examination question mapping with question bank';

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
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`lesson_id`),
  KEY `lesson_subject` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `question_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year_group_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `lesson_id` bigint(20) DEFAULT NULL,
  `question_category_id` int(11) DEFAULT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `require_file_upload` tinyint(4) NOT NULL,
  `source` enum('Q','A','O') COLLATE utf8mb4_unicode_ci DEFAULT 'Q' COMMENT 'Q=Quiz; A=Assesment; O=Other',
  `created_by` int(11) NOT NULL,
  `creator_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Question bank';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------


--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL DEFAULT '0',
  `user_type` enum('A','AU','SW','TA','TU','T','P') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A' COMMENT 'A=Administrator \r\nAU=Administrator User\r\nSW=Swagger User\r\nTA=Tenant Admin\r\nTU=Tenant User\r\nT=Trustee\r\nP=Parent',
  `role` enum('A','T','S','TA','OU') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A=Admin rights\r\nT=Teacher rights\r\nS=Student rights\r\nTA=Teacher assistant rights\r\nOU=Other User without login',
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_profile_id`),
  UNIQUE KEY `user_id_profile` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `is_reviewed` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `reviewer_user_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_result_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `attachment_file` varchar(151) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marks_given` decimal(8,2) DEFAULT NULL,
  `time_taken_inmins` decimal(5,2) DEFAULT NULL,
  `reviewer_comments` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_result_input_id`),
  KEY `user_result_fk` (`user_result_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_subjects`
--

DROP TABLE IF EXISTS `user_subjects`;
CREATE TABLE IF NOT EXISTS `user_subjects` (
  `user_subject_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_subject_id`),
  KEY `user_subject_subject` (`subject_id`),
  KEY `user_subject_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
CREATE TABLE IF NOT EXISTS `grades` (
  `grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` int(11) NULL,
  `grade` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_value` float NOT NULL,
  `max_value` float NOT NULL,
  `effective_date` date NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`grade_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`,`board_id`, `grade`, `min_value`, `max_value`, `effective_date`, `status`, `created_at`, `updated_at`) VALUES
(1,1, 'A+', 90, 100, '2024-04-29', 'Active', '2024-04-29 00:10:47', '2024-04-29 03:45:28'),
(2,1, 'A', 80, 90, '2024-04-29', 'Active', '2024-04-29 00:14:46', '2024-04-29 03:45:54'),
(3, 1,'B', 65, 80, '2024-04-29', 'Active', '2024-04-29 03:46:42', '2024-04-29 03:46:42'),
(4,1, 'C', 50, 65, '2024-04-29', 'Active', '2024-04-29 03:47:56', '2024-04-29 03:47:56'),
(5,1, 'D', 0, 50, '2024-04-29', 'Active', '2024-04-29 03:48:22', '2024-04-29 03:48:22');

--
-- Table structure for table `external_users`
--

DROP TABLE IF EXISTS `external_users`;
CREATE TABLE IF NOT EXISTS `external_users` (
  `external_user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` bigint(20) NOT NULL DEFAULT '0',
  `invite_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`external_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sub_indicators`
--
ALTER TABLE `sub_indicators`
  ADD CONSTRAINT `sub_indicators_ibfk_1` FOREIGN KEY (`indicator_id`) REFERENCES `indicators` (`indicator_id`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for dumped tables
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Constraints for dumped tables
--
ALTER TABLE `user_profiles` ADD `salutation` VARCHAR(50) NULL AFTER `user_id`;

ALTER TABLE `lessons` ADD `created_by` BIGINT NULL AFTER `lesson_name`;
ALTER TABLE `lessons` ADD `creator_type` VARCHAR(10) NULL AFTER `created_by`;
--UPDATE `lessons` SET `created_by`=4;
--UPDATE `lessons` SET `creator_type`='TA';
--
-- Constraints for table `libraries`
--
ALTER TABLE `libraries`
  ADD CONSTRAINT `libraries_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`);
--
-- Constraints for table `sub_indicators`
--

-- Constraints for dumped tables
--
ALTER TABLE `academic_years` ADD `start_year` VARCHAR(10) NULL AFTER `academic_year`, ADD `end_year` VARCHAR(10) NULL AFTER `start_year`;

ALTER TABLE `user_profiles` ADD `have_sensupport_healthcare_plan` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `parent_email`, ADD `first_lang_not_eng` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `have_sensupport_healthcare_plan`, ADD `freeschool_eligible` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `first_lang_not_eng`;
--
-- Constraints for table `study_group_members`
--
ALTER TABLE `study_group_members`
  ADD CONSTRAINT `study_group` FOREIGN KEY (`study_group_id`) REFERENCES `study_groups` (`study_group_id`) ON DELETE CASCADE ON UPDATE CASCADE;
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
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_option_fk` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subject_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`academic_year_id`),
  ADD CONSTRAINT `subject_year_group` FOREIGN KEY (`year_group_id`) REFERENCES `year_groups` (`year_group_id`);

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

ALTER TABLE `user_results` ADD `grade_id` INT NULL AFTER `grade`;
ALTER TABLE `user_subjects` ADD `grade_id` INT NULL AFTER `subject_id`;

ALTER TABLE `examination_questions` 
CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL ,
CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL ;

ALTER TABLE `user_result_inputs` CHANGE `attachment_file` `attachment_file` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `questions` CHANGE `level` `level` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, CHANGE `require_file_upload` `require_file_upload` TINYINT(4) NULL;
ALTER TABLE `questions` ADD `parent_question_id` BIGINT NOT NULL DEFAULT '0' AFTER `question_id`, ADD `linked_question` TINYINT NOT NULL DEFAULT '0' AFTER `parent_question_id`;

ALTER TABLE `examination_questions` ADD `parent_examination_question_id` BIGINT NOT NULL DEFAULT '0' AFTER `question_id`, ADD `linked_question` TINYINT NOT NULL DEFAULT '0' AFTER `parent_examination_question_id`;
ALTER TABLE `users` CHANGE `role` `role` ENUM('A','T','S','TA','OU','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A=Admin rights\r\nT=Teacher rights\r\nS=Student rights\r\nTA=Teacher assistant rights\r\nOU=Other User without login\r\nP=parent';

ALTER TABLE `user_profiles` ADD `cover_picture` VARCHAR(255) NULL AFTER `freeschool_eligible`;


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
CREATE TABLE IF NOT EXISTS `topics` (
  `topic_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `lesson_id` bigint(20) NOT NULL,
  `topic` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `creator_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`topic_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Table structure for table `targets`
--

DROP TABLE IF EXISTS `targets`;
CREATE TABLE IF NOT EXISTS `targets` (
  `target_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `set_date` date NOT NULL,
  `year_group_id` int(11) NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `creator_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `target_details`
--

DROP TABLE IF EXISTS `target_details`;
CREATE TABLE IF NOT EXISTS `target_details` (
  `target_detail_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `target_id` bigint(20) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `target` int(11) NOT NULL,
  `target_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`target_detail_id`),
  KEY `target_details_ibfk_1` (`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_for` enum('All','All Teachers','All Students','Specific Teachers','Specific Students') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'All',
  `created_by` bigint(20) NOT NULL,
  `creator_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_users`
--

DROP TABLE IF EXISTS `message_users`;
CREATE TABLE IF NOT EXISTS `message_users` (
  `message_user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`message_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Constraints for dumped tables
--

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
  `rating_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `creator_id` bigint(20) NOT NULL,
  `creator_rating` int(11) NOT NULL DEFAULT '0',
  `creator_remarks` text COLLATE utf8mb4_unicode_ci,
  `content_rating` int(11) NOT NULL DEFAULT '0',
  `content_remarks` text COLLATE utf8mb4_unicode_ci,
  `rating_created_by` bigint(20) NOT NULL,
  `rating_creator_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `year_group_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `lesson_id` bigint(20) NOT NULL,
  `creator_rating_outof` int(11) NOT NULL DEFAULT '5',
  `content_rating_outof` int(11) NOT NULL DEFAULT '5',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`rating_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `task_type` enum('N','Q','A','H') COLLATE utf8mb4_unicode_ci NOT NULL,
  `creator_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_for` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_allocations`
--

DROP TABLE IF EXISTS `task_allocations`;
CREATE TABLE IF NOT EXISTS `task_allocations` (
  `task_allocation_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`task_allocation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_examinations`
--

DROP TABLE IF EXISTS `task_examinations`;
CREATE TABLE IF NOT EXISTS `task_examinations` (
  `task_examination_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) NOT NULL,
  `examination_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`task_examination_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Constraints for table `target_details`
--
ALTER TABLE `target_details`
  ADD CONSTRAINT `target_details_ibfk_1` FOREIGN KEY (`target_id`) REFERENCES `targets` (`target_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `questions` ADD `topic_id` BIGINT NULL AFTER `lesson_id`, ADD `sub_topic_id` BIGINT NULL AFTER `topic_id`;

ALTER TABLE `examination_questions` ADD `topic_id` BIGINT NULL AFTER `examination_id`, ADD `sub_topic_id` BIGINT NULL AFTER `topic_id`;


  ALTER TABLE `examination_questions` ADD `tc` TINYINT(4) NOT NULL DEFAULT '0' AFTER `linked_question`, ADD `ms` TINYINT(4) NOT NULL DEFAULT '0' AFTER `tc`, ADD `ps` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ms`, ADD `at` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ps`;

  ALTER TABLE `questions` ADD `tc` TINYINT(4) NOT NULL DEFAULT '0' AFTER `question`, ADD `ms` TINYINT(4) NOT NULL DEFAULT '0' AFTER `tc`, ADD `ps` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ms`, ADD `at` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ps`;


ALTER TABLE `messages` CHANGE `created_for` `created_for` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'All';
ALTER TABLE `examinations` ADD `homework` TINYINT(4) NOT NULL DEFAULT '0' AFTER `name`;





COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;