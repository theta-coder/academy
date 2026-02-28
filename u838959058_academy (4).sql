-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2026 at 02:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u838959058_academy`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `year_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `year_name`, `start_date`, `end_date`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '2026-2027', '2026-02-07', '2027-02-07', 1, NULL, '2026-02-07 07:37:53', '2026-02-22 12:00:57'),
(2, '2025-2026', '2025-02-07', '2026-02-07', 0, NULL, '2026-02-07 07:41:05', '2026-02-07 07:41:05'),
(3, '2024-2025', '2024-02-07', '2025-02-07', 0, NULL, '2026-02-07 07:43:37', '2026-02-07 07:43:37');

-- --------------------------------------------------------

--
-- Table structure for table `academy_payment_accounts`
--

CREATE TABLE `academy_payment_accounts` (
  `id` int(11) NOT NULL,
  `account_title` varchar(150) NOT NULL COMMENT 'e.g. JazzCash - Admin, HBL Account',
  `payment_method` enum('jazzcash','easypaisa','bank_transfer','raast') NOT NULL,
  `account_number` varchar(100) NOT NULL COMMENT 'Phone number ya IBAN',
  `bank_name` varchar(100) DEFAULT NULL COMMENT 'Agar bank transfer ho',
  `branch_name` varchar(100) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `instructions` text DEFAULT NULL COMMENT 'Student k liye instructions',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Academy k JazzCash, EasyPaisa, Bank accounts jahan fees aati hain';

--
-- Dumping data for table `academy_payment_accounts`
--

INSERT INTO `academy_payment_accounts` (`id`, `account_title`, `payment_method`, `account_number`, `bank_name`, `branch_name`, `iban`, `instructions`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'JazzCash - Academy', 'jazzcash', '03001234567', NULL, NULL, NULL, 'JazzCash par send karein, screenshot zaroor lein', 1, '2026-02-22 06:36:12', '2026-02-22 06:36:12'),
(2, 'EasyPaisa - Academy', 'easypaisa', '03111234567', NULL, NULL, NULL, 'EasyPaisa par send karein, transaction ID note karein', 1, '2026-02-22 06:36:12', '2026-02-22 06:36:12'),
(3, 'HBL Bank Transfer', 'bank_transfer', '01234567890123', 'HBL', NULL, NULL, 'Account title: ABC Academy, Branch: Main Branch', 1, '2026-02-22 06:36:12', '2026-02-22 06:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `location`, `phone`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Main Campus', 'Comboh Coloney', '0301234567899', 1, '2026-02-22 10:45:33', '2026-02-06 16:27:35', '2026-02-22 10:45:33'),
(2, 'Main Girl Campus', 'Near Bank Alflah Chung Multan road LahoreE', '03123456789', 1, NULL, '2026-02-07 06:59:17', '2026-02-07 16:10:13'),
(3, 'KiDs Campus', 'AlFalah', '03123456789', 1, NULL, '2026-02-07 08:44:25', '2026-02-22 10:44:44'),
(4, 'Main Girl Campus', 'saas', '03123456789', 1, '2026-02-22 11:36:04', '2026-02-08 17:53:39', '2026-02-22 11:36:04'),
(5, 'Ifeoma Hansen', 'Incididunt eu maxime', '+1 (773) 156-8256', 0, '2026-02-22 11:38:19', '2026-02-22 11:35:47', '2026-02-22 11:38:19'),
(6, 'Nicole Randolph', 'Unde expedita nisi d', '+1 (647) 895-1472', 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39');

-- --------------------------------------------------------

--
-- Table structure for table `branch_classes`
--

CREATE TABLE `branch_classes` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_classes`
--

INSERT INTO `branch_classes` (`id`, `branch_id`, `class_id`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(7, 2, 7, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(8, 2, 8, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(9, 2, 9, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(10, 2, 10, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(11, 2, 11, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(12, 2, 12, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(13, 2, 13, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(14, 2, 14, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(15, 2, 15, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(16, 2, 16, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(17, 2, 17, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(18, 2, 18, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(19, 2, 19, 1, NULL, '2026-02-07 16:10:13', '2026-02-07 16:10:13'),
(41, 3, 7, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(42, 3, 8, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(43, 3, 9, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(44, 3, 10, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(45, 3, 11, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(46, 3, 12, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(47, 3, 13, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(48, 3, 14, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(49, 3, 15, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(50, 3, 16, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(51, 3, 17, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(52, 3, 18, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(53, 3, 19, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(54, 3, 20, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(55, 3, 21, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(56, 3, 22, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(57, 3, 23, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(58, 3, 24, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(59, 3, 25, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(60, 3, 26, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(61, 3, 27, 1, NULL, '2026-02-08 04:47:58', '2026-02-22 10:44:44'),
(90, 6, 10, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(91, 6, 11, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(92, 6, 12, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(93, 6, 14, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(94, 6, 17, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(95, 6, 18, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(96, 6, 19, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(97, 6, 21, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(98, 6, 24, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39'),
(99, 6, 25, 1, NULL, '2026-02-22 11:46:39', '2026-02-22 11:46:39');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `display_order`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(7, 'Play Group', 1, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(8, 'Nursery', 2, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(9, 'Prep', 3, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(10, 'Class 1', 4, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(11, 'Class 2', 5, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(12, 'Class 3', 6, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(13, 'Class 4', 7, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(14, 'Class 5', 8, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(15, 'Class 6', 9, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(16, 'Class 7', 10, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(17, 'Class 8', 11, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(18, 'Class 9', 12, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(19, 'Class 10', 13, 1, NULL, '2026-02-07 20:55:11', '2026-02-07 20:55:11'),
(20, 'O Level', 20, 1, NULL, '2026-02-07 20:56:13', '2026-02-07 20:56:13'),
(21, 'A Level', 21, 1, NULL, '2026-02-07 20:56:13', '2026-02-07 20:56:13'),
(22, 'Matric', 22, 1, NULL, '2026-02-07 20:56:13', '2026-02-07 20:56:13'),
(23, 'Intermediate (FA)', 23, 1, NULL, '2026-02-07 20:56:13', '2026-02-07 20:56:13'),
(24, 'Intermediate (FSc)', 24, 1, NULL, '2026-02-07 20:56:13', '2026-02-07 20:56:13'),
(25, 'ICS', 25, 1, NULL, '2026-02-07 20:56:13', '2026-02-07 20:56:13'),
(26, 'I.Com', 26, 1, NULL, '2026-02-07 20:56:13', '2026-02-07 20:56:13'),
(27, 'Entry Test Preparation', 27, 1, NULL, '2026-02-07 20:56:13', '2026-02-07 20:56:13');

-- --------------------------------------------------------

--
-- Table structure for table `class_sections`
--

CREATE TABLE `class_sections` (
  `id` int(11) NOT NULL,
  `branch_class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `capacity` int(11) DEFAULT 30,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_sections`
--

INSERT INTO `class_sections` (`id`, `branch_class_id`, `section_id`, `capacity`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 30, 0, NULL, '2026-02-07 18:53:09', '2026-02-08 02:40:46');

-- --------------------------------------------------------

--
-- Table structure for table `class_section_subjects`
--

CREATE TABLE `class_section_subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_section_id` int(11) NOT NULL,
  `subject_group_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_section_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_advance_adjustments`
--

CREATE TABLE `fee_advance_adjustments` (
  `id` int(11) NOT NULL,
  `student_enrollment_id` int(11) NOT NULL COMMENT 'FK → student_enrollments',
  `from_payment_id` int(11) NOT NULL COMMENT 'FK → fee_payments — jis advance payment se amount liya',
  `to_voucher_id` int(11) NOT NULL COMMENT 'FK → fee_vouchers — jis voucher par adjust kiya',
  `adjusted_amount` decimal(10,2) NOT NULL COMMENT 'Kitne rupay adjust kiye',
  `adjusted_by` bigint(20) UNSIGNED NOT NULL COMMENT 'FK → users — kisne adjust kiya',
  `adjusted_at` date NOT NULL COMMENT 'Kab adjust kiya',
  `notes` text DEFAULT NULL COMMENT 'Optional note',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Advance payment ko future voucher mein adjust karne ka complete record';

-- --------------------------------------------------------

--
-- Table structure for table `fee_collection_summary`
--

CREATE TABLE `fee_collection_summary` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL COMMENT 'FK → branches',
  `academic_year_id` int(11) NOT NULL COMMENT 'FK → academic_years',
  `summary_month` tinyint(3) DEFAULT NULL COMMENT '1-12 — monthly summary ke liye, NULL = yearly',
  `summary_year` year(4) NOT NULL COMMENT '2025, 2026 etc.',
  `total_students` int(11) NOT NULL DEFAULT 0 COMMENT 'Is mahine enrolled students',
  `total_billed` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total vouchers ka amount',
  `total_discount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total discount/concession diya',
  `total_fine` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total late fine laga',
  `total_net` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'total_billed - total_discount + total_fine',
  `total_collected` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total payment receive ki',
  `total_pending` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total abhi baki hai',
  `total_waived` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total maafi di',
  `vouchers_paid` int(11) NOT NULL DEFAULT 0 COMMENT 'Kitne vouchers fully paid hue',
  `vouchers_partial` int(11) NOT NULL DEFAULT 0 COMMENT 'Kitne partial paid hain',
  `vouchers_pending` int(11) NOT NULL DEFAULT 0 COMMENT 'Kitne abhi pending hain',
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Kab generate ya refresh hua',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Monthly branch-wise collection summary — reports ke liye fast lookup';

-- --------------------------------------------------------

--
-- Table structure for table `fee_concession_types`
--

CREATE TABLE `fee_concession_types` (
  `id` int(11) NOT NULL,
  `concession_name` varchar(100) NOT NULL COMMENT 'e.g. Brother/Sister Discount',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `default_discount_value` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'percentage mein ya rupay mein — depends on discount_type',
  `applies_to` enum('all_fees','monthly_only','specific') NOT NULL DEFAULT 'monthly_only' COMMENT 'Kaunsi fees par discount lagega',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Discount categories — manually assign karte hain students ko';

--
-- Dumping data for table `fee_concession_types`
--

INSERT INTO `fee_concession_types` (`id`, `concession_name`, `discount_type`, `default_discount_value`, `applies_to`, `description`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Orphan Discount', 'percentage', 50.00, 'monthly_only', 'Yateem bacha — 50% monthly discount', 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(2, 'Staff Child Discount', 'percentage', 100.00, 'monthly_only', 'Staff ka bacha — puri monthly maafi', 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(3, 'Merit Scholarship', 'percentage', 25.00, 'monthly_only', '25% monthly fee maafi', 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(4, 'Hardship Case', 'percentage', 30.00, 'monthly_only', 'Ghareeb talab — 30% discount', 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(5, 'Special Discount', 'fixed', 500.00, 'monthly_only', 'Fixed amount maafi — management decision', 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28');

-- --------------------------------------------------------

--
-- Table structure for table `fee_fine_rules`
--

CREATE TABLE `fee_fine_rules` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL COMMENT 'NULL=sab branches par apply, ya specific branch',
  `fee_type_id` int(11) DEFAULT NULL COMMENT 'NULL=sab fee types par apply, ya specific fee type par sirf',
  `applies_to_all_fee_types` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=sab fees par lagti hai, 0=sirf fee_type_id wali fee par',
  `days_after_due` int(11) NOT NULL DEFAULT 0 COMMENT 'Due date ke kitne din baad fine shuru ho — 0 matlab same day',
  `fine_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `fine_value` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Fixed rupay ya percentage of remaining amount',
  `max_fine` decimal(8,2) DEFAULT NULL COMMENT 'Fine ki maximum limit — NULL=no limit',
  `description` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Late fee rules — due date ke baad kitni fine lagegi';

--
-- Dumping data for table `fee_fine_rules`
--

INSERT INTO `fee_fine_rules` (`id`, `branch_id`, `fee_type_id`, `applies_to_all_fee_types`, `days_after_due`, `fine_type`, `fine_value`, `max_fine`, `description`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 1, 10, 'fixed', 50.00, NULL, 'Due date ke 10 din baad 50 rupay fine — sab branches par', 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `receipt_no` varchar(30) NOT NULL COMMENT 'Unique receipt number — RCP-2026-00001',
  `voucher_id` int(11) NOT NULL COMMENT 'FK → fee_vouchers',
  `student_enrollment_id` int(11) NOT NULL COMMENT 'FK → student_enrollments',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank_transfer','cheque','online') NOT NULL DEFAULT 'cash',
  `bank_name` varchar(100) DEFAULT NULL COMMENT 'Agar bank transfer ya cheque ho',
  `transaction_ref` varchar(100) DEFAULT NULL COMMENT 'Bank transaction ID ya cheque number',
  `received_by` bigint(20) UNSIGNED NOT NULL COMMENT 'FK → users — kisne receive kiya',
  `is_advance` tinyint(1) DEFAULT 0 COMMENT '1=advance payment hai',
  `notes` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Actual payments — ek voucher ke liye multiple partial payments possible';

-- --------------------------------------------------------

--
-- Table structure for table `fee_refunds`
--

CREATE TABLE `fee_refunds` (
  `id` int(11) NOT NULL,
  `student_enrollment_id` int(11) NOT NULL COMMENT 'FK → student_enrollments',
  `payment_id` int(11) DEFAULT NULL COMMENT 'FK → fee_payments — konsi payment ka refund',
  `refund_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `refund_date` date NOT NULL,
  `reason` text NOT NULL COMMENT 'Refund ki wajah zaroori hai',
  `refund_method` enum('cash','bank_transfer','cheque') NOT NULL DEFAULT 'cash',
  `bank_details` varchar(200) DEFAULT NULL,
  `refunded_by` bigint(20) UNSIGNED NOT NULL COMMENT 'FK → users',
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Fee refunds — student gaya ya overpayment hua';

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL COMMENT 'FK → academic_years',
  `branch_id` int(11) NOT NULL COMMENT 'FK → branches — branch wise alag fees',
  `class_id` int(11) NOT NULL COMMENT 'FK → classes — class wise alag fees',
  `fee_type_id` int(11) NOT NULL COMMENT 'FK → fee_types',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_day` tinyint(3) DEFAULT 10 COMMENT 'Har mahine ki kitni tarikh tak jama ho — default 10',
  `effective_from` date NOT NULL COMMENT 'Ye fee kab se lagu hogi',
  `effective_to` date DEFAULT NULL COMMENT 'Kab tak — NULL matlab indefinite',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CORE: Branch+Class+Year wise fee amount — yahan se fees nikalti hain';

-- --------------------------------------------------------

--
-- Table structure for table `fee_types`
--

CREATE TABLE `fee_types` (
  `id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL COMMENT 'e.g. Monthly Tuition Fee, Annual Fund',
  `fee_category` enum('school','academy','both') NOT NULL DEFAULT 'both' COMMENT 'school=sirf school students, academy=sirf academy, both=sab',
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=har mahine lagti hai (monthly), 0=ek dafa (one-time)',
  `recurring_months` varchar(50) DEFAULT NULL COMMENT 'Agar specific months mein ho jaise "1,6,12" — Jan, Jun, Dec',
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0 COMMENT 'UI mein display order',
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Fee categories — Monthly/Annual Fund/Admission/Exam etc.';

--
-- Dumping data for table `fee_types`
--

INSERT INTO `fee_types` (`id`, `fee_name`, `fee_category`, `is_recurring`, `recurring_months`, `description`, `display_order`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Monthly Tuition Fee', 'school', 1, NULL, 'Har mahine ki school tuition fee', 1, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(2, 'Academy Monthly Fee', 'academy', 1, NULL, 'Har mahine ki academy fee', 2, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(3, 'Annual Fund', 'both', 0, NULL, 'Saalanah fund — saal mein ek baar', 3, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(4, 'Admission Fee', 'both', 0, NULL, 'Ek dafa admission ke waqt', 4, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(5, 'Examination Fee', 'school', 0, NULL, 'Exam ke waqt lagti hai', 5, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(6, 'Books Fee', 'both', 0, NULL, 'Kitabon ki fees', 6, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(7, 'Transport Fee', 'both', 1, NULL, 'Van/bus ki monthly fees', 7, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(8, 'Laboratory Fee', 'school', 0, NULL, 'Science lab fee', 8, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(9, 'Sports Fee', 'school', 0, NULL, 'Saalana sports fund', 9, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(10, 'Computer Fee', 'both', 0, NULL, 'Computer lab fee', 10, 1, NULL, '2026-02-22 04:55:28', '2026-02-22 04:55:28');

-- --------------------------------------------------------

--
-- Table structure for table `fee_vouchers`
--

CREATE TABLE `fee_vouchers` (
  `id` int(11) NOT NULL,
  `voucher_no` varchar(30) NOT NULL COMMENT 'Unique — e.g. VCH-2026-00001',
  `student_enrollment_id` int(11) NOT NULL COMMENT 'FK → student_enrollments',
  `fee_type_id` int(11) NOT NULL COMMENT 'FK → fee_types',
  `academic_year_id` int(11) NOT NULL COMMENT 'FK → academic_years',
  `month` tinyint(3) DEFAULT NULL COMMENT '1-12 — monthly fees ke liye, one-time ke liye NULL',
  `year` year(4) DEFAULT NULL COMMENT 'e.g. 2025, 2026',
  `generated_for` varchar(20) NOT NULL DEFAULT '' COMMENT 'Monthly: "2026-02" | One-time: "ONE-TIME" | Exam: "EXAM-2026" — NULL problem solve',
  `original_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'fee_structures se aaya',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Concession + sibling discount',
  `fine_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Late payment fine',
  `net_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'original - discount + fine',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Abhi tak jama hua',
  `remaining_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'net - paid',
  `due_date` date NOT NULL COMMENT 'Jis tarikh tak jama karna hai',
  `status` enum('pending','partial','paid','waived','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'pending=kuch nahi diya, partial=kuch diya, paid=pura, waived=maaf, cancelled=cancel',
  `notes` text DEFAULT NULL,
  `generated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK → users',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CORE: Har student ka fee voucher — monthly ya one-time';

-- --------------------------------------------------------

--
-- Table structure for table `fee_voucher_fines`
--

CREATE TABLE `fee_voucher_fines` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL COMMENT 'FK → fee_vouchers',
  `fine_rule_id` int(11) DEFAULT NULL COMMENT 'FK → fee_fine_rules — konsa rule apply hua',
  `days_overdue` int(11) NOT NULL DEFAULT 0 COMMENT 'Due date ke baad kitne din guzre',
  `fine_type` enum('fixed','percentage') NOT NULL COMMENT 'Fixed rupay ya percentage',
  `fine_value` decimal(8,2) NOT NULL COMMENT 'Rule mein jo value thi',
  `calculated_amount` decimal(10,2) NOT NULL COMMENT 'Asli rupay mein kitna fine laga',
  `applied_on` date NOT NULL COMMENT 'Kab fine apply hua',
  `applied_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL = system ne auto lagaya, ya user id',
  `is_waived` tinyint(1) DEFAULT 0 COMMENT '1 = fine maaf kar diya',
  `waived_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK → users — kisne fine maaf kiya',
  `notes` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Har voucher par lagi fine ka breakdown — system ya manual';

-- --------------------------------------------------------

--
-- Table structure for table `fee_waivers`
--

CREATE TABLE `fee_waivers` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL COMMENT 'FK → fee_vouchers',
  `student_enrollment_id` int(11) NOT NULL COMMENT 'FK → student_enrollments',
  `waived_amount` decimal(10,2) NOT NULL COMMENT 'Kitne rupay maaf kiye',
  `waiver_reason` text NOT NULL COMMENT 'Wajah zaroori hai — audit ke liye',
  `approved_by` bigint(20) UNSIGNED NOT NULL COMMENT 'FK → users — kisne approve kiya',
  `approved_on` date NOT NULL COMMENT 'Kab approve hua',
  `status` enum('approved','reversed') NOT NULL DEFAULT 'approved' COMMENT 'reversed=waiver wapas le liya',
  `reversed_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK → users — kisne reverse kiya',
  `reversal_reason` text DEFAULT NULL COMMENT 'Reverse kyun kiya',
  `notes` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Fee waiver record — kisne, kab, kyun maaf kiya — full audit trail';

-- --------------------------------------------------------

--
-- Table structure for table `installment_plans`
--

CREATE TABLE `installment_plans` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL COMMENT 'e.g. 2 Kiston Mein, 3 Kiston Mein',
  `total_installments` int(11) NOT NULL COMMENT 'Kitni Kistain Hongi',
  `applicable_fee_type_id` int(11) NOT NULL COMMENT 'Sirf Annual Fund ya koi bhi fee type',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Kisne banaya — FK → users',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Annual fund ya kisi bhi fee k liye installment plan templates';

--
-- Dumping data for table `installment_plans`
--

INSERT INTO `installment_plans` (`id`, `plan_name`, `total_installments`, `applicable_fee_type_id`, `description`, `is_active`, `created_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '2 Kiston Mein', 2, 2, 'Annual fund 2 barabar hisson mein', 1, NULL, NULL, '2026-02-22 06:36:12', '2026-02-22 06:36:12'),
(2, '3 Kiston Mein', 3, 2, 'Annual fund 3 barabar hisson mein', 1, NULL, NULL, '2026-02-22 06:36:12', '2026-02-22 06:36:12'),
(3, '4 Kiston Mein', 4, 2, 'Annual fund har teen mahine mein', 1, NULL, NULL, '2026-02-22 06:36:12', '2026-02-22 06:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `installment_schedule`
--

CREATE TABLE `installment_schedule` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL COMMENT 'student_installment_assignments ka id',
  `kist_number` int(11) NOT NULL COMMENT '1st, 2nd, 3rd kist',
  `kist_amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `status` enum('pending','paid','partial','overdue') DEFAULT 'pending',
  `payment_id` int(11) DEFAULT NULL COMMENT 'fee_payments ka reference',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Har student ki har kist ki detail aur due date';

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `online_payment_proofs`
--

CREATE TABLE `online_payment_proofs` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL COMMENT 'Kis voucher ki fee hai',
  `student_enrollment_id` int(11) NOT NULL,
  `academy_account_id` int(11) NOT NULL COMMENT 'Kaunse account par bheja',
  `payment_method` enum('jazzcash','easypaisa','bank_transfer','raast') NOT NULL,
  `sender_name` varchar(150) DEFAULT NULL COMMENT 'Jis ne bheja uska naam',
  `sender_number` varchar(20) DEFAULT NULL COMMENT 'JazzCash/EasyPaisa number',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT 'JazzCash transaction ID',
  `amount_sent` decimal(10,2) NOT NULL,
  `payment_datetime` datetime NOT NULL COMMENT 'Kab bheja gaya',
  `screenshot_path` varchar(500) DEFAULT NULL COMMENT 'Screenshot upload ka path',
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Parent ya student ne submit kiya — FK → users',
  `submission_notes` text DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Kisne verify kiya — FK → users',
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `fee_payment_id` int(11) DEFAULT NULL COMMENT 'Verify hone k baad fee_payments ka id',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Online payments ka proof - JazzCash screenshot, bank transfer etc. Admin verify karega';

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `father_name` varchar(100) NOT NULL,
  `father_cnic` varchar(15) DEFAULT NULL COMMENT 'e.g. 35201-1234567-1',
  `father_phone` varchar(20) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_cnic` varchar(15) DEFAULT NULL,
  `mother_phone` varchar(20) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Parent/Guardian master record — linked to multiple students';

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL COMMENT 'fees, students, reports, users etc.',
  `permission_key` varchar(100) NOT NULL COMMENT 'fees.collect, students.create etc.',
  `display_name` varchar(150) NOT NULL COMMENT 'UI mein dikhne wala naam',
  `description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Har kaam ka ek permission — granular control';

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module`, `permission_key`, `display_name`, `description`, `created_at`) VALUES
(1, 'students', 'students.view', 'Students Dekhna', 'Student list aur profile dekh sakta hai', '2026-02-22 05:48:43'),
(2, 'students', 'students.create', 'Student Add Karna', 'Naya student register karna', '2026-02-22 05:48:43'),
(3, 'students', 'students.edit', 'Student Edit Karna', 'Student info update karna', '2026-02-22 05:48:43'),
(4, 'students', 'students.delete', 'Student Delete Karna', 'Student record delete karna', '2026-02-22 05:48:43'),
(5, 'students', 'students.enroll', 'Enrollment Karna', 'Student ko class mein enroll karna', '2026-02-22 05:48:43'),
(6, 'fees', 'fees.view_vouchers', 'Vouchers Dekhna', 'Fee vouchers list dekh sakta hai', '2026-02-22 05:48:43'),
(7, 'fees', 'fees.collect', 'Fee Receive Karna', 'Student se payment lena', '2026-02-22 05:48:43'),
(8, 'fees', 'fees.generate_vouchers', 'Vouchers Banana', 'Monthly/one-time vouchers generate karna', '2026-02-22 05:48:43'),
(9, 'fees', 'fees.edit_voucher', 'Voucher Edit Karna', 'Voucher amount ya due date change karna', '2026-02-22 05:48:43'),
(10, 'fees', 'fees.waive', 'Fee Waive Karna', 'Voucher mafi dena — sirf senior authority', '2026-02-22 05:48:43'),
(11, 'fees', 'fees.refund', 'Refund Karna', 'Paid fee wapas karna', '2026-02-22 05:48:43'),
(12, 'fees', 'fees.apply_fine', 'Fine Lagana', 'Late fee fine manually apply karna', '2026-02-22 05:48:43'),
(13, 'fees', 'fees.manage_concession', 'Concession Manage Karna', 'Student ko discount assign ya remove karna', '2026-02-22 05:48:43'),
(14, 'fee_structure', 'fee_structure.view', 'Fee Structure Dekhna', 'Branch/class wise fees dekh sakta hai', '2026-02-22 05:48:43'),
(15, 'fee_structure', 'fee_structure.create', 'Fee Structure Banana', 'Naya fee structure set karna', '2026-02-22 05:48:43'),
(16, 'fee_structure', 'fee_structure.edit', 'Fee Structure Edit Karna', 'Existing fees change karna', '2026-02-22 05:48:43'),
(17, 'fee_structure', 'fee_structure.delete', 'Fee Structure Delete', 'Fee structure delete karna', '2026-02-22 05:48:43'),
(18, 'reports', 'reports.fee_collection', 'Fee Collection Report', 'Daily/monthly collection report', '2026-02-22 05:48:43'),
(19, 'reports', 'reports.outstanding', 'Outstanding Fees Report', 'Pending fees ka report', '2026-02-22 05:48:43'),
(20, 'reports', 'reports.student_ledger', 'Student Ledger Dekhna', 'Individual student ka account', '2026-02-22 05:48:43'),
(21, 'reports', 'reports.financial', 'Financial Report', 'Full financial summary — sirf admin', '2026-02-22 05:48:43'),
(22, 'users', 'users.view', 'Users Dekhna', 'Staff list dekh sakta hai', '2026-02-22 05:48:43'),
(23, 'users', 'users.create', 'User Add Karna', 'Naya user/staff banana', '2026-02-22 05:48:43'),
(24, 'users', 'users.edit', 'User Edit Karna', 'User info aur role change karna', '2026-02-22 05:48:43'),
(25, 'users', 'users.delete', 'User Delete', 'User account delete karna', '2026-02-22 05:48:43'),
(26, 'branches', 'branches.view', 'Branches Dekhna', 'Branch list dekh sakta hai', '2026-02-22 05:48:43'),
(27, 'branches', 'branches.create', 'Branch Add Karna', 'Naya branch banana', '2026-02-22 05:48:43'),
(28, 'branches', 'branches.edit', 'Branch Edit Karna', 'Branch details update karna', '2026-02-22 05:48:43'),
(29, 'classes', 'classes.view', 'Classes Dekhna', 'Class aur section list dekh sakta hai', '2026-02-22 05:48:43'),
(30, 'classes', 'classes.create', 'Class Add Karna', 'Naya class/section banana', '2026-02-22 05:48:43'),
(31, 'classes', 'classes.edit', 'Class Edit Karna', 'Class details update karna', '2026-02-22 05:48:43'),
(32, 'attendance', 'attendance.view', 'Attendance Dekhna', 'Attendance records dekh sakta hai', '2026-02-22 05:48:43'),
(33, 'attendance', 'attendance.mark', 'Attendance Lagana', 'Daily attendance mark karna', '2026-02-22 05:48:43'),
(34, 'attendance', 'attendance.edit', 'Attendance Edit', 'Past attendance correct karna', '2026-02-22 05:48:43'),
(35, 'settings', 'settings.view', 'Settings Dekhna', 'System settings dekh sakta hai', '2026-02-22 05:48:43'),
(36, 'settings', 'settings.edit', 'Settings Edit Karna', 'System settings change karna — sirf admin', '2026-02-22 05:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL COMMENT 'admin, cashier, branch_manager etc.',
  `display_name` varchar(100) NOT NULL COMMENT 'UI mein dikhne wala naam',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System roles — Admin, Cashier, Branch Manager etc.';

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `display_name`, `description`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Super Admin', 'Sab kuch kar sakta hai — koi restriction nahi', 1, NULL, '2026-02-22 05:48:43', '2026-02-22 05:48:43'),
(2, 'branch_manager', 'Branch Manager', 'Apni branch ka sab kuch dekh sakta hai', 1, NULL, '2026-02-22 05:48:43', '2026-02-22 05:48:43'),
(3, 'accountant', 'Accountant', 'Fees, reports, vouchers manage karta hai', 1, NULL, '2026-02-22 05:48:43', '2026-02-22 05:48:43'),
(4, 'cashier', 'Cashier', 'Sirf fee receive karta hai aur receipt deta hai', 1, NULL, '2026-02-22 05:48:43', '2026-02-22 05:48:43'),
(5, 'teacher', 'Teacher', 'Students aur attendance dekh sakta hai', 1, NULL, '2026-02-22 05:48:43', '2026-02-22 05:48:43'),
(6, 'receptionist', 'Receptionist', 'Students enroll karta hai, basic info dekh sakta hai', 1, NULL, '2026-02-22 05:48:43', '2026-02-22 05:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL COMMENT 'FK → roles',
  `permission_id` int(11) NOT NULL COMMENT 'FK → permissions',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Role ko assign kiye hue permissions';

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 32, '2026-02-22 05:48:44'),
(2, 1, 33, '2026-02-22 05:48:44'),
(3, 1, 34, '2026-02-22 05:48:44'),
(4, 1, 26, '2026-02-22 05:48:44'),
(5, 1, 27, '2026-02-22 05:48:44'),
(6, 1, 28, '2026-02-22 05:48:44'),
(7, 1, 29, '2026-02-22 05:48:44'),
(8, 1, 30, '2026-02-22 05:48:44'),
(9, 1, 31, '2026-02-22 05:48:44'),
(10, 1, 6, '2026-02-22 05:48:44'),
(11, 1, 7, '2026-02-22 05:48:44'),
(12, 1, 8, '2026-02-22 05:48:44'),
(13, 1, 9, '2026-02-22 05:48:44'),
(14, 1, 10, '2026-02-22 05:48:44'),
(15, 1, 11, '2026-02-22 05:48:44'),
(16, 1, 12, '2026-02-22 05:48:44'),
(17, 1, 13, '2026-02-22 05:48:44'),
(18, 1, 14, '2026-02-22 05:48:44'),
(19, 1, 15, '2026-02-22 05:48:44'),
(20, 1, 16, '2026-02-22 05:48:44'),
(21, 1, 17, '2026-02-22 05:48:44'),
(22, 1, 18, '2026-02-22 05:48:44'),
(23, 1, 19, '2026-02-22 05:48:44'),
(24, 1, 20, '2026-02-22 05:48:44'),
(25, 1, 21, '2026-02-22 05:48:44'),
(26, 1, 35, '2026-02-22 05:48:44'),
(27, 1, 36, '2026-02-22 05:48:44'),
(28, 1, 1, '2026-02-22 05:48:44'),
(29, 1, 2, '2026-02-22 05:48:44'),
(30, 1, 3, '2026-02-22 05:48:44'),
(31, 1, 4, '2026-02-22 05:48:44'),
(32, 1, 5, '2026-02-22 05:48:44'),
(33, 1, 22, '2026-02-22 05:48:44'),
(34, 1, 23, '2026-02-22 05:48:44'),
(35, 1, 24, '2026-02-22 05:48:44'),
(36, 1, 25, '2026-02-22 05:48:44'),
(64, 2, 34, '2026-02-22 05:48:44'),
(65, 2, 33, '2026-02-22 05:48:44'),
(66, 2, 32, '2026-02-22 05:48:44'),
(67, 2, 26, '2026-02-22 05:48:44'),
(68, 2, 30, '2026-02-22 05:48:44'),
(69, 2, 31, '2026-02-22 05:48:44'),
(70, 2, 29, '2026-02-22 05:48:44'),
(71, 2, 12, '2026-02-22 05:48:44'),
(72, 2, 7, '2026-02-22 05:48:44'),
(73, 2, 8, '2026-02-22 05:48:44'),
(74, 2, 13, '2026-02-22 05:48:44'),
(75, 2, 6, '2026-02-22 05:48:44'),
(76, 2, 10, '2026-02-22 05:48:44'),
(77, 2, 14, '2026-02-22 05:48:44'),
(78, 2, 18, '2026-02-22 05:48:44'),
(79, 2, 19, '2026-02-22 05:48:44'),
(80, 2, 20, '2026-02-22 05:48:44'),
(81, 2, 2, '2026-02-22 05:48:44'),
(82, 2, 3, '2026-02-22 05:48:44'),
(83, 2, 5, '2026-02-22 05:48:44'),
(84, 2, 1, '2026-02-22 05:48:44'),
(85, 2, 22, '2026-02-22 05:48:44'),
(95, 3, 26, '2026-02-22 05:48:44'),
(96, 3, 29, '2026-02-22 05:48:44'),
(97, 3, 12, '2026-02-22 05:48:44'),
(98, 3, 7, '2026-02-22 05:48:44'),
(99, 3, 8, '2026-02-22 05:48:44'),
(100, 3, 13, '2026-02-22 05:48:44'),
(101, 3, 11, '2026-02-22 05:48:44'),
(102, 3, 6, '2026-02-22 05:48:44'),
(103, 3, 15, '2026-02-22 05:48:44'),
(104, 3, 16, '2026-02-22 05:48:44'),
(105, 3, 14, '2026-02-22 05:48:44'),
(106, 3, 18, '2026-02-22 05:48:44'),
(107, 3, 21, '2026-02-22 05:48:44'),
(108, 3, 19, '2026-02-22 05:48:44'),
(109, 3, 20, '2026-02-22 05:48:44'),
(110, 3, 1, '2026-02-22 05:48:44'),
(126, 4, 7, '2026-02-22 05:48:44'),
(127, 4, 6, '2026-02-22 05:48:44'),
(128, 4, 18, '2026-02-22 05:48:44'),
(129, 4, 1, '2026-02-22 05:48:44'),
(133, 5, 33, '2026-02-22 05:48:44'),
(134, 5, 32, '2026-02-22 05:48:44'),
(135, 5, 29, '2026-02-22 05:48:44'),
(136, 5, 1, '2026-02-22 05:48:44'),
(140, 6, 32, '2026-02-22 05:48:44'),
(141, 6, 26, '2026-02-22 05:48:44'),
(142, 6, 29, '2026-02-22 05:48:44'),
(143, 6, 6, '2026-02-22 05:48:44'),
(144, 6, 2, '2026-02-22 05:48:44'),
(145, 6, 3, '2026-02-22 05:48:44'),
(146, 6, 5, '2026-02-22 05:48:44'),
(147, 6, 1, '2026-02-22 05:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `scholarships`
--

CREATE TABLE `scholarships` (
  `id` int(11) NOT NULL,
  `scholarship_name` varchar(150) NOT NULL COMMENT 'e.g. 1st Position Scholarship, Merit Award',
  `criteria` varchar(255) DEFAULT NULL COMMENT 'e.g. Class Topper, 90%+ Marks, Hafiz e Quran',
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL COMMENT 'e.g. 100% ya 5000 Rs fixed',
  `applies_to` enum('all_fees','specific_fee_type','tuition_only') DEFAULT 'all_fees',
  `applicable_fee_type_id` int(11) DEFAULT NULL COMMENT 'Agar specific fee type par apply ho',
  `max_recipients` int(11) DEFAULT NULL COMMENT 'Ek class mein kitne bachon ko mil sakti hai',
  `is_renewable` tinyint(1) DEFAULT 0 COMMENT 'Kya har saal renew hogi?',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Kisne banaya — FK → users',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Academy ki scholarship types jaise 1st position ya merit';

--
-- Dumping data for table `scholarships`
--

INSERT INTO `scholarships` (`id`, `scholarship_name`, `criteria`, `discount_type`, `discount_value`, `applies_to`, `applicable_fee_type_id`, `max_recipients`, `is_renewable`, `description`, `is_active`, `created_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '1st Position Scholarship', 'Class mein 1st position', 'percentage', 100.00, 'tuition_only', NULL, 1, 1, NULL, 1, NULL, NULL, '2026-02-22 06:36:12', '2026-02-22 06:36:12'),
(2, '2nd Position Scholarship', 'Class mein 2nd position', 'percentage', 50.00, 'tuition_only', NULL, 1, 1, NULL, 1, NULL, NULL, '2026-02-22 06:36:12', '2026-02-22 06:36:12'),
(3, '3rd Position Scholarship', 'Class mein 3rd position', 'percentage', 25.00, 'tuition_only', NULL, 1, 1, NULL, 1, NULL, NULL, '2026-02-22 06:36:12', '2026-02-22 06:36:12'),
(4, 'Merit Scholarship 90%+', '90% ya ziyada marks', 'percentage', 50.00, 'tuition_only', NULL, NULL, 1, NULL, 1, NULL, NULL, '2026-02-22 06:36:12', '2026-02-22 06:36:12'),
(5, 'Hafiz e Quran Award', 'Quran Hafiz bacha', 'percentage', 100.00, 'all_fees', NULL, NULL, 1, NULL, 1, NULL, NULL, '2026-02-22 06:36:12', '2026-02-22 06:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `section_name` varchar(10) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `section_name`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'A', 1, NULL, '2026-02-07 10:04:58', '2026-02-07 10:04:58'),
(2, 'B', 1, NULL, '2026-02-07 10:09:01', '2026-02-07 10:09:01');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1vmIwy4q7E3g2cj8mzVjt98j4c0MAELeU9qXwNSH', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVHRiWVQxSm1xM2pvT2tIQVlxS2lGSFgxSHE5aTM1a1RWeENRNzNBTiI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772242002),
('FWb3UMf1fsSnp8se7ChQvej1t4K2yZ5VkGRio69f', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibE05UjA0ZElrcUt4M2JWUjJjZkpxU0NhNkZtUGE2Z0d2aThGZ1hhWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jbGFzc2VzLzcvZWRpdCI7czo1OiJyb3V0ZSI7czoxMjoiY2xhc3Nlcy5lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1771780462);

-- --------------------------------------------------------

--
-- Table structure for table `sibling_discount_rules`
--

CREATE TABLE `sibling_discount_rules` (
  `id` int(11) NOT NULL,
  `child_number` tinyint(3) NOT NULL COMMENT '2=2nd child, 3=3rd child, 4=4th child',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(8,2) NOT NULL DEFAULT 0.00,
  `applies_to_fee_type_id` int(11) DEFAULT NULL COMMENT 'NULL=monthly tuition par, ya specific fee type par',
  `description` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Auto sibling discount — 2nd/3rd child par system khud apply karta hai';

--
-- Dumping data for table `sibling_discount_rules`
--

INSERT INTO `sibling_discount_rules` (`id`, `child_number`, `discount_type`, `discount_value`, `applies_to_fee_type_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'percentage', 10.00, NULL, '2nd child — 10% monthly fee discount', 1, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(2, 3, 'percentage', 20.00, NULL, '3rd child — 20% monthly fee discount', 1, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(3, 4, 'percentage', 30.00, NULL, '4th child — 30% monthly fee discount', 1, '2026-02-22 04:55:28', '2026-02-22 04:55:28'),
(4, 5, 'percentage', 50.00, NULL, '5th child aur baad — 50% monthly fee discount', 1, '2026-02-22 04:55:28', '2026-02-22 04:55:28');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `admission_no` varchar(30) DEFAULT NULL COMMENT 'Unique admission number, e.g. ACE-2026-001',
  `parent_id` int(11) NOT NULL COMMENT 'FK → parents',
  `student_name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') NOT NULL DEFAULT 'male',
  `photo` varchar(255) DEFAULT NULL COMMENT 'File path',
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `b_form_no` varchar(20) DEFAULT NULL COMMENT 'Child B-Form number',
  `blood_group` varchar(5) DEFAULT NULL COMMENT 'A+, B-, O+ etc.',
  `religion` varchar(50) DEFAULT NULL,
  `is_hafiz` tinyint(1) DEFAULT 0 COMMENT 'Hafiz e Quran hai? Scholarship k liye helpful',
  `student_type` enum('school','academy','both') NOT NULL DEFAULT 'school' COMMENT 'school=school fees only, academy=academy fees only, both=dono fees',
  `previous_school` varchar(200) DEFAULT NULL,
  `medical_condition` text DEFAULT NULL COMMENT 'Koi bimari ya allergy',
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Student personal info — linked to parent';

-- --------------------------------------------------------

--
-- Table structure for table `student_advance_balance`
--

CREATE TABLE `student_advance_balance` (
  `id` int(11) NOT NULL,
  `student_enrollment_id` int(11) NOT NULL COMMENT 'FK → student_enrollments',
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '+ve = student ne zyada diya (credit), -ve = baqi hai (debt)',
  `last_transaction_id` int(11) DEFAULT NULL COMMENT 'FK → student_ledger — آخری transaction کا reference',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Student ka live balance — har payment/voucher ke baad update hota hai';

-- --------------------------------------------------------

--
-- Table structure for table `student_enrollments`
--

CREATE TABLE `student_enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL COMMENT 'FK → students',
  `academic_year_id` int(11) NOT NULL COMMENT 'FK → academic_years',
  `branch_id` int(11) NOT NULL COMMENT 'FK → branches',
  `class_section_id` int(11) NOT NULL COMMENT 'FK → class_sections',
  `roll_number` varchar(20) DEFAULT NULL,
  `admission_date` date NOT NULL,
  `leaving_date` date DEFAULT NULL COMMENT 'Agar school chhod de',
  `enrollment_type` enum('school','academy','both') NOT NULL DEFAULT 'school' COMMENT 'Is saal is enrollment mein kaunsi fees lagti hain',
  `sibling_order` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1=pehla bacha, 2=doosra, 3=teesra — admission_date se auto-calculate',
  `status` enum('active','left','graduated','transferred','suspended') NOT NULL DEFAULT 'active',
  `leaving_reason` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK → users',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Student enrollment per year — complete history';

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_concessions`
--

CREATE TABLE `student_fee_concessions` (
  `id` int(11) NOT NULL,
  `student_enrollment_id` int(11) NOT NULL COMMENT 'FK → student_enrollments',
  `fee_type_id` int(11) DEFAULT NULL COMMENT 'NULL=sab fees par, ya specific fee par',
  `concession_type_id` int(11) NOT NULL COMMENT 'FK → fee_concession_types',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Override karna ho to yahan custom value dalo',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL COMMENT 'NULL=indefinite jab tak active hai',
  `approved_by` bigint(20) UNSIGNED NOT NULL COMMENT 'FK → users — kisne approve kiya',
  `remarks` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Student ko assign kiya hua discount — per enrollment';

-- --------------------------------------------------------

--
-- Table structure for table `student_installment_assignments`
--

CREATE TABLE `student_installment_assignments` (
  `id` int(11) NOT NULL,
  `student_enrollment_id` int(11) NOT NULL,
  `installment_plan_id` int(11) NOT NULL,
  `fee_voucher_id` int(11) NOT NULL COMMENT 'Original annual fund voucher jis par plan laga',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Total annual fund amount',
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `remaining_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','completed','defaulted') DEFAULT 'active',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Kisne approve kiya — FK → users',
  `notes` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Student ko kaunsa installment plan mila';

-- --------------------------------------------------------

--
-- Table structure for table `student_ledger`
--

CREATE TABLE `student_ledger` (
  `id` int(11) NOT NULL,
  `student_enrollment_id` int(11) NOT NULL COMMENT 'FK → student_enrollments',
  `transaction_type` enum('debit','credit') NOT NULL COMMENT 'debit=student par paisa bana, credit=student ne diya ya maafi mili',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) NOT NULL COMMENT 'e.g. Monthly Fee Feb 2026, Advance Payment',
  `reference_type` enum('voucher','payment','refund','concession','adjustment') NOT NULL COMMENT 'Kaunse table ka reference hai',
  `reference_id` int(11) DEFAULT NULL COMMENT 'Us table ka ID',
  `balance_after` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Is transaction ke baad student ka balance — +credit, -debt',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK → users',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Student ka complete account ledger — advance/credit/debit history';

-- --------------------------------------------------------

--
-- Table structure for table `student_scholarships`
--

CREATE TABLE `student_scholarships` (
  `id` int(11) NOT NULL,
  `student_enrollment_id` int(11) NOT NULL,
  `scholarship_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `awarded_on` date NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date DEFAULT NULL COMMENT 'NULL ho to whole year valid',
  `position_achieved` varchar(50) DEFAULT NULL COMMENT 'e.g. 1st, 2nd, 3rd in class',
  `marks_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Agar marks basis par mila',
  `awarded_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Kisne award kiya — FK → users',
  `status` enum('active','expired','revoked') DEFAULT 'active',
  `revoke_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Kaunse student ko kaunsi scholarship mili aur kab tak';

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `is_compulsory` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`, `subject_code`, `is_compulsory`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(6, 'English', 'ENG', 1, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(7, 'Urdu', 'URD', 1, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(8, 'Islamiyat', 'ISL', 1, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(9, 'Pakistan Studies', 'PAK', 1, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(10, 'Mathematics', 'MATH', 1, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(11, 'Physics', 'PHY', 0, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(12, 'Chemistry', 'CHEM', 0, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(13, 'Biology', 'BIO', 0, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(14, 'Computer Science', 'CS', 0, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(15, 'Statistics', 'STAT', 0, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(16, 'Economics', 'ECO', 0, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14'),
(17, 'Accounting', 'ACC', 0, 1, NULL, '2026-02-07 21:23:14', '2026-02-07 21:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `subject_groups`
--

CREATE TABLE `subject_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `subject_ids` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_groups`
--

INSERT INTO `subject_groups` (`id`, `group_name`, `description`, `subject_ids`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(5, 'Matric Science', 'Matric science subjects', '\"6,8,10,9,7,13,12,14,11\"', 1, NULL, '2026-02-07 21:23:56', '2026-02-07 16:31:16'),
(6, 'Matric Arts', 'Matric arts subjects', '1,2,3,4,5,10,11', 1, NULL, '2026-02-07 21:24:04', '2026-02-07 21:24:04'),
(7, 'FSc Pre-Medical', 'Physics Chemistry Biology', '1,2,3,4,6,7,8', 1, NULL, '2026-02-07 21:24:14', '2026-02-07 21:24:14'),
(8, 'FSc Pre-Engineering', 'Physics Chemistry Mathematics', '1,2,3,4,5,6,7', 1, NULL, '2026-02-07 21:24:26', '2026-02-07 21:24:26'),
(9, 'ICS', 'Computer Science group', '1,2,3,4,5,9,6', 1, NULL, '2026-02-07 21:25:09', '2026-02-07 21:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(11) DEFAULT NULL COMMENT 'FK → roles',
  `branch_id` int(11) DEFAULT NULL COMMENT 'Assigned branch — NULL = all branches access',
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `branch_id`, `name`, `phone`, `email`, `email_verified_at`, `password`, `remember_token`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Danish Ali', NULL, 'admin@example.com', NULL, '$2y$12$g1..0USrkB2BdAQxCLPRzOD8up6HLFo.3WYnCp3aiY73uJJIPERD.', 'k0kJyM5tXtYkVXms85aWe8VmknQoRqEWCFs1fzW1l8h0w5sJy5o94MU6z55L', 1, '2026-02-06 15:06:41', '2026-02-06 15:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_extra_permissions`
--

CREATE TABLE `user_extra_permissions` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK → users',
  `permission_id` int(11) NOT NULL COMMENT 'FK → permissions',
  `granted_by` bigint(20) UNSIGNED NOT NULL COMMENT 'Kisne diya — FK → users',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'NULL = permanent, ya expiry date',
  `reason` varchar(200) DEFAULT NULL COMMENT 'Kyun diya',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Kisi user ko role ke upar extra permission deni ho to yahan — optional';

-- --------------------------------------------------------

--
-- Table structure for table `voucher_discount_breakdowns`
--

CREATE TABLE `voucher_discount_breakdowns` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL COMMENT 'FK → fee_vouchers',
  `discount_source` enum('concession','sibling','waiver','manual') NOT NULL COMMENT 'concession=assigned discount, sibling=automatic, waiver=fee maafi, manual=admin override',
  `source_id` int(11) DEFAULT NULL COMMENT 'student_fee_concessions.id ya sibling_discount_rules.id — reference',
  `source_label` varchar(150) NOT NULL COMMENT 'Human readable — e.g. "Staff Child Discount", "2nd Sibling 10%", "Principal Override"',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Percentage ya fixed amount jo apply hua',
  `calculated_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Asli rupay mein kitna discount laga — ye most important field hai',
  `applied_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL=system ne auto apply kiya, ya user id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Voucher ka discount breakdown — har discount ka alag row, full audit trail';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year_name` (`year_name`);

--
-- Indexes for table `academy_payment_accounts`
--
ALTER TABLE `academy_payment_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch_classes`
--
ALTER TABLE `branch_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `branch_classes_ibfk_1` (`branch_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_name` (`class_name`);

--
-- Indexes for table `class_sections`
--
ALTER TABLE `class_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_section_id` (`section_id`),
  ADD KEY `fk_branch_class_id` (`branch_class_id`);

--
-- Indexes for table `class_section_subjects`
--
ALTER TABLE `class_section_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_section_subjects_class_section_id_foreign` (`class_section_id`),
  ADD KEY `class_section_subjects_subject_group_id_foreign` (`subject_group_id`),
  ADD KEY `class_section_subjects_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fee_advance_adjustments`
--
ALTER TABLE `fee_advance_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adj_enrollment_idx` (`student_enrollment_id`),
  ADD KEY `adj_payment_idx` (`from_payment_id`),
  ADD KEY `adj_voucher_idx` (`to_voucher_id`),
  ADD KEY `adj_user_fk` (`adjusted_by`);

--
-- Indexes for table `fee_collection_summary`
--
ALTER TABLE `fee_collection_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_summary` (`branch_id`,`academic_year_id`,`summary_month`,`summary_year`) COMMENT 'Ek branch+year+month ka sirf ek record',
  ADD KEY `summary_branch_idx` (`branch_id`),
  ADD KEY `summary_year_idx` (`academic_year_id`);

--
-- Indexes for table `fee_concession_types`
--
ALTER TABLE `fee_concession_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fee_fine_rules`
--
ALTER TABLE `fee_fine_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fine_branch` (`branch_id`),
  ADD KEY `idx_fine_fee_type` (`fee_type_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_receipt_no` (`receipt_no`),
  ADD KEY `idx_payment_voucher` (`voucher_id`),
  ADD KEY `idx_payment_enrollment` (`student_enrollment_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_payment_method` (`payment_method`),
  ADD KEY `payment_received_by_fk` (`received_by`);

--
-- Indexes for table `fee_refunds`
--
ALTER TABLE `fee_refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refund_enrollment` (`student_enrollment_id`),
  ADD KEY `idx_refund_payment` (`payment_id`),
  ADD KEY `idx_refund_status` (`status`),
  ADD KEY `refund_refunded_by_fk` (`refunded_by`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fee_structure` (`academic_year_id`,`branch_id`,`class_id`,`fee_type_id`) COMMENT 'Ek combination ka sirf ek record',
  ADD KEY `idx_fee_str_year` (`academic_year_id`),
  ADD KEY `idx_fee_str_branch` (`branch_id`),
  ADD KEY `idx_fee_str_class` (`class_id`),
  ADD KEY `idx_fee_str_type` (`fee_type_id`);

--
-- Indexes for table `fee_types`
--
ALTER TABLE `fee_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fee_types_category` (`fee_category`),
  ADD KEY `idx_fee_types_recurring` (`is_recurring`);

--
-- Indexes for table `fee_vouchers`
--
ALTER TABLE `fee_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_voucher_no` (`voucher_no`),
  ADD UNIQUE KEY `unique_voucher_generated_for` (`student_enrollment_id`,`fee_type_id`,`generated_for`) COMMENT 'NULL problem khatam — ab duplicates impossible hain',
  ADD KEY `idx_voucher_enrollment` (`student_enrollment_id`),
  ADD KEY `idx_voucher_fee_type` (`fee_type_id`),
  ADD KEY `idx_voucher_status` (`status`),
  ADD KEY `idx_voucher_due_date` (`due_date`),
  ADD KEY `idx_voucher_year_month` (`year`,`month`),
  ADD KEY `voucher_year_fk` (`academic_year_id`),
  ADD KEY `voucher_generated_by_fk` (`generated_by`);

--
-- Indexes for table `fee_voucher_fines`
--
ALTER TABLE `fee_voucher_fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fine_voucher` (`voucher_id`),
  ADD KEY `idx_fine_rule` (`fine_rule_id`),
  ADD KEY `idx_fine_applied_on` (`applied_on`);

--
-- Indexes for table `fee_waivers`
--
ALTER TABLE `fee_waivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_waiver_voucher` (`voucher_id`),
  ADD KEY `idx_waiver_enrollment` (`student_enrollment_id`),
  ADD KEY `idx_waiver_status` (`status`),
  ADD KEY `waiver_approved_by_fk` (`approved_by`),
  ADD KEY `waiver_reversed_by_fk` (`reversed_by`);

--
-- Indexes for table `installment_plans`
--
ALTER TABLE `installment_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ip_fee_type` (`applicable_fee_type_id`),
  ADD KEY `ip_created_by_fk` (`created_by`);

--
-- Indexes for table `installment_schedule`
--
ALTER TABLE `installment_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_is_assignment` (`assignment_id`),
  ADD KEY `fk_is_payment` (`payment_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `online_payment_proofs`
--
ALTER TABLE `online_payment_proofs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_transaction_id` (`transaction_id`),
  ADD KEY `fk_opp_voucher` (`voucher_id`),
  ADD KEY `fk_opp_enrollment` (`student_enrollment_id`),
  ADD KEY `fk_opp_account` (`academy_account_id`),
  ADD KEY `fk_opp_payment` (`fee_payment_id`),
  ADD KEY `opp_submitted_by_fk` (`submitted_by`),
  ADD KEY `opp_verified_by_fk` (`verified_by`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permission_key` (`permission_key`),
  ADD KEY `idx_permission_module` (`module`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `fk_rp_role` (`role_id`),
  ADD KEY `fk_rp_permission` (`permission_id`);

--
-- Indexes for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_s_fee_type` (`applicable_fee_type_id`),
  ADD KEY `sch_created_by_fk` (`created_by`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_name` (`section_name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sibling_discount_rules`
--
ALTER TABLE `sibling_discount_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_child_number` (`child_number`),
  ADD KEY `idx_sibling_fee_type` (`applies_to_fee_type_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_b_form_no` (`b_form_no`),
  ADD KEY `idx_students_parent_id` (`parent_id`),
  ADD KEY `idx_students_student_type` (`student_type`);

--
-- Indexes for table `student_advance_balance`
--
ALTER TABLE `student_advance_balance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment_balance` (`student_enrollment_id`),
  ADD KEY `idx_balance_enrollment` (`student_enrollment_id`),
  ADD KEY `advance_last_transaction_fk` (`last_transaction_id`);

--
-- Indexes for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`academic_year_id`,`branch_id`) COMMENT 'Ek student ek saal mein ek branch mein sirf ek baar',
  ADD KEY `idx_enrollment_student` (`student_id`),
  ADD KEY `idx_enrollment_year` (`academic_year_id`),
  ADD KEY `idx_enrollment_branch` (`branch_id`),
  ADD KEY `idx_enrollment_class_section` (`class_section_id`),
  ADD KEY `idx_enrollment_status` (`status`);

--
-- Indexes for table `student_fee_concessions`
--
ALTER TABLE `student_fee_concessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_concession_enrollment` (`student_enrollment_id`),
  ADD KEY `idx_concession_type` (`concession_type_id`),
  ADD KEY `idx_concession_fee_type` (`fee_type_id`);

--
-- Indexes for table `student_installment_assignments`
--
ALTER TABLE `student_installment_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sia_enrollment` (`student_enrollment_id`),
  ADD KEY `fk_sia_plan` (`installment_plan_id`),
  ADD KEY `fk_sia_voucher` (`fee_voucher_id`),
  ADD KEY `sia_approved_by_fk` (`approved_by`);

--
-- Indexes for table `student_ledger`
--
ALTER TABLE `student_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ledger_enrollment` (`student_enrollment_id`),
  ADD KEY `idx_ledger_type` (`transaction_type`),
  ADD KEY `idx_ledger_ref` (`reference_type`,`reference_id`),
  ADD KEY `idx_ledger_date` (`created_at`);

--
-- Indexes for table `student_scholarships`
--
ALTER TABLE `student_scholarships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ss_enrollment` (`student_enrollment_id`),
  ADD KEY `fk_ss_scholarship` (`scholarship_id`),
  ADD KEY `fk_ss_year` (`academic_year_id`),
  ADD KEY `ss_awarded_by_fk` (`awarded_by`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `subject_groups`
--
ALTER TABLE `subject_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_fk` (`role_id`),
  ADD KEY `users_branch_fk` (`branch_id`);

--
-- Indexes for table `user_extra_permissions`
--
ALTER TABLE `user_extra_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_id`),
  ADD KEY `fk_uep_user` (`user_id`),
  ADD KEY `fk_uep_permission` (`permission_id`);

--
-- Indexes for table `voucher_discount_breakdowns`
--
ALTER TABLE `voucher_discount_breakdowns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vdb_voucher` (`voucher_id`),
  ADD KEY `idx_vdb_source` (`discount_source`,`source_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `academy_payment_accounts`
--
ALTER TABLE `academy_payment_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `branch_classes`
--
ALTER TABLE `branch_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `class_sections`
--
ALTER TABLE `class_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `class_section_subjects`
--
ALTER TABLE `class_section_subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_advance_adjustments`
--
ALTER TABLE `fee_advance_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_collection_summary`
--
ALTER TABLE `fee_collection_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_concession_types`
--
ALTER TABLE `fee_concession_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fee_fine_rules`
--
ALTER TABLE `fee_fine_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_refunds`
--
ALTER TABLE `fee_refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_types`
--
ALTER TABLE `fee_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fee_vouchers`
--
ALTER TABLE `fee_vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_voucher_fines`
--
ALTER TABLE `fee_voucher_fines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_waivers`
--
ALTER TABLE `fee_waivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `installment_plans`
--
ALTER TABLE `installment_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `installment_schedule`
--
ALTER TABLE `installment_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `online_payment_proofs`
--
ALTER TABLE `online_payment_proofs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sibling_discount_rules`
--
ALTER TABLE `sibling_discount_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_advance_balance`
--
ALTER TABLE `student_advance_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fee_concessions`
--
ALTER TABLE `student_fee_concessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_installment_assignments`
--
ALTER TABLE `student_installment_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_ledger`
--
ALTER TABLE `student_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_scholarships`
--
ALTER TABLE `student_scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `subject_groups`
--
ALTER TABLE `subject_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_extra_permissions`
--
ALTER TABLE `user_extra_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher_discount_breakdowns`
--
ALTER TABLE `voucher_discount_breakdowns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `branch_classes`
--
ALTER TABLE `branch_classes`
  ADD CONSTRAINT `branch_classes_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_classes_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_sections`
--
ALTER TABLE `class_sections`
  ADD CONSTRAINT `class_sections_ibfk_1` FOREIGN KEY (`branch_class_id`) REFERENCES `branch_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_sections_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_branch_class_id` FOREIGN KEY (`branch_class_id`) REFERENCES `branch_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_section_id` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`);

--
-- Constraints for table `class_section_subjects`
--
ALTER TABLE `class_section_subjects`
  ADD CONSTRAINT `class_section_subjects_class_section_id_foreign` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_section_subjects_subject_group_id_foreign` FOREIGN KEY (`subject_group_id`) REFERENCES `subject_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_section_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_advance_adjustments`
--
ALTER TABLE `fee_advance_adjustments`
  ADD CONSTRAINT `adj_enrollment_fk` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`),
  ADD CONSTRAINT `adj_payment_fk` FOREIGN KEY (`from_payment_id`) REFERENCES `fee_payments` (`id`),
  ADD CONSTRAINT `adj_user_fk` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `adj_voucher_fk` FOREIGN KEY (`to_voucher_id`) REFERENCES `fee_vouchers` (`id`);

--
-- Constraints for table `fee_collection_summary`
--
ALTER TABLE `fee_collection_summary`
  ADD CONSTRAINT `summary_branch_fk` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `summary_year_fk` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `fee_fine_rules`
--
ALTER TABLE `fee_fine_rules`
  ADD CONSTRAINT `fine_rules_branch_fk` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fine_rules_fee_type_fk` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `payment_enrollment_fk` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`),
  ADD CONSTRAINT `payment_received_by_fk` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payment_voucher_fk` FOREIGN KEY (`voucher_id`) REFERENCES `fee_vouchers` (`id`);

--
-- Constraints for table `fee_refunds`
--
ALTER TABLE `fee_refunds`
  ADD CONSTRAINT `refund_enrollment_fk` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`),
  ADD CONSTRAINT `refund_payment_fk` FOREIGN KEY (`payment_id`) REFERENCES `fee_payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `refund_refunded_by_fk` FOREIGN KEY (`refunded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_str_branch_fk` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fee_str_class_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `fee_str_type_fk` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
  ADD CONSTRAINT `fee_str_year_fk` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `fee_vouchers`
--
ALTER TABLE `fee_vouchers`
  ADD CONSTRAINT `voucher_enrollment_fk` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`),
  ADD CONSTRAINT `voucher_fee_type_fk` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
  ADD CONSTRAINT `voucher_generated_by_fk` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `voucher_year_fk` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `fee_voucher_fines`
--
ALTER TABLE `fee_voucher_fines`
  ADD CONSTRAINT `fine_rule_fk` FOREIGN KEY (`fine_rule_id`) REFERENCES `fee_fine_rules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fine_voucher_fk` FOREIGN KEY (`voucher_id`) REFERENCES `fee_vouchers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_waivers`
--
ALTER TABLE `fee_waivers`
  ADD CONSTRAINT `waiver_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `waiver_enrollment_fk` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`),
  ADD CONSTRAINT `waiver_reversed_by_fk` FOREIGN KEY (`reversed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `waiver_voucher_fk` FOREIGN KEY (`voucher_id`) REFERENCES `fee_vouchers` (`id`);

--
-- Constraints for table `installment_plans`
--
ALTER TABLE `installment_plans`
  ADD CONSTRAINT `fk_ip_fee_type` FOREIGN KEY (`applicable_fee_type_id`) REFERENCES `fee_types` (`id`),
  ADD CONSTRAINT `ip_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `installment_schedule`
--
ALTER TABLE `installment_schedule`
  ADD CONSTRAINT `fk_is_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `student_installment_assignments` (`id`),
  ADD CONSTRAINT `fk_is_payment` FOREIGN KEY (`payment_id`) REFERENCES `fee_payments` (`id`);

--
-- Constraints for table `online_payment_proofs`
--
ALTER TABLE `online_payment_proofs`
  ADD CONSTRAINT `fk_opp_account` FOREIGN KEY (`academy_account_id`) REFERENCES `academy_payment_accounts` (`id`),
  ADD CONSTRAINT `fk_opp_enrollment` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`),
  ADD CONSTRAINT `fk_opp_payment` FOREIGN KEY (`fee_payment_id`) REFERENCES `fee_payments` (`id`),
  ADD CONSTRAINT `fk_opp_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `fee_vouchers` (`id`),
  ADD CONSTRAINT `opp_submitted_by_fk` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `opp_verified_by_fk` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `rp_permission_fk` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rp_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD CONSTRAINT `fk_s_fee_type` FOREIGN KEY (`applicable_fee_type_id`) REFERENCES `fee_types` (`id`),
  ADD CONSTRAINT `sch_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sibling_discount_rules`
--
ALTER TABLE `sibling_discount_rules`
  ADD CONSTRAINT `sibling_fee_type_fk` FOREIGN KEY (`applies_to_fee_type_id`) REFERENCES `fee_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`);

--
-- Constraints for table `student_advance_balance`
--
ALTER TABLE `student_advance_balance`
  ADD CONSTRAINT `advance_enrollment_fk` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advance_last_transaction_fk` FOREIGN KEY (`last_transaction_id`) REFERENCES `student_ledger` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD CONSTRAINT `enrollments_branch_fk` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `enrollments_class_section_fk` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`),
  ADD CONSTRAINT `enrollments_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `enrollments_year_fk` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `student_fee_concessions`
--
ALTER TABLE `student_fee_concessions`
  ADD CONSTRAINT `concession_enrollment_fk` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `concession_fee_type_fk` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `concession_type_fk` FOREIGN KEY (`concession_type_id`) REFERENCES `fee_concession_types` (`id`);

--
-- Constraints for table `student_installment_assignments`
--
ALTER TABLE `student_installment_assignments`
  ADD CONSTRAINT `fk_sia_enrollment` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`),
  ADD CONSTRAINT `fk_sia_plan` FOREIGN KEY (`installment_plan_id`) REFERENCES `installment_plans` (`id`),
  ADD CONSTRAINT `fk_sia_voucher` FOREIGN KEY (`fee_voucher_id`) REFERENCES `fee_vouchers` (`id`),
  ADD CONSTRAINT `sia_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_ledger`
--
ALTER TABLE `student_ledger`
  ADD CONSTRAINT `ledger_enrollment_fk` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`);

--
-- Constraints for table `student_scholarships`
--
ALTER TABLE `student_scholarships`
  ADD CONSTRAINT `fk_ss_enrollment` FOREIGN KEY (`student_enrollment_id`) REFERENCES `student_enrollments` (`id`),
  ADD CONSTRAINT `fk_ss_scholarship` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`),
  ADD CONSTRAINT `fk_ss_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  ADD CONSTRAINT `ss_awarded_by_fk` FOREIGN KEY (`awarded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_fk` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_extra_permissions`
--
ALTER TABLE `user_extra_permissions`
  ADD CONSTRAINT `uep_permission_fk` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `uep_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `voucher_discount_breakdowns`
--
ALTER TABLE `voucher_discount_breakdowns`
  ADD CONSTRAINT `vdb_voucher_fk` FOREIGN KEY (`voucher_id`) REFERENCES `fee_vouchers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
