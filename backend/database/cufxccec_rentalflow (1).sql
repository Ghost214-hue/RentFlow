-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 01, 2026 at 12:29 PM
-- Server version: 8.4.10
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cufxccec_rentalflow`
--

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `house_id` int UNSIGNED NOT NULL,
  `tenant_id` int UNSIGNED DEFAULT NULL COMMENT 'Snapshotted at generation time',
  `month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'YYYY-MM format',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Rent',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rent` decimal(12,2) DEFAULT '0.00',
  `water` decimal(12,2) DEFAULT '0.00',
  `electricity` decimal(12,2) DEFAULT '0.00',
  `total` decimal(12,2) DEFAULT '0.00',
  `status` enum('paid','partial','pending','overdue') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `owner_id`, `house_id`, `tenant_id`, `month`, `type`, `description`, `rent`, `water`, `electricity`, `total`, `status`, `due_date`, `created_at`, `updated_at`) VALUES
(70, 7, 57, 46, '2026-08', 'Rent', NULL, 6000.00, 0.00, 0.00, 6000.00, 'partial', '2026-08-05', '2026-08-01 11:17:26', '2026-08-12 06:25:42'),
(71, 7, 75, 47, '2026-08', 'Rent', NULL, 6500.00, 0.00, 0.00, 6500.00, 'paid', '2026-08-05', '2026-08-01 12:34:14', '2026-08-12 06:21:02'),
(72, 7, 77, 48, '2026-08', 'Rent', NULL, 6500.00, 0.00, 0.00, 6500.00, 'paid', '2026-08-05', '2026-08-01 14:47:22', '2026-08-12 06:21:02'),
(75, 7, 64, 51, '2026-08', 'Rent', NULL, 7000.00, 0.00, 0.00, 7000.00, 'paid', '2026-08-05', '2026-08-02 17:05:10', '2026-08-05 15:26:05'),
(76, 7, 66, 52, '2026-08', 'Rent', NULL, 7000.00, 0.00, 0.00, 7000.00, 'paid', '2026-08-05', '2026-08-02 17:13:07', '2026-08-05 15:26:05'),
(78, 7, 52, 54, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 12000.00, 'partial', '2026-08-05', '2026-08-03 08:59:28', '2026-09-01 08:44:07'),
(79, 7, 59, 55, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 6000.00, 'partial', '2026-08-05', '2026-08-03 10:02:26', '2026-08-08 13:19:56'),
(80, 7, 58, 56, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 6000.00, 'partial', '2026-08-05', '2026-08-03 10:15:08', '2026-08-08 13:19:27'),
(81, 7, 53, 43, '2026-08', 'Rent', NULL, 6000.00, 0.00, 0.00, 6000.00, 'paid', '2026-08-05', '2026-08-03 16:05:15', '2026-08-05 18:25:58'),
(82, 7, 55, 45, '2026-08', 'Rent', NULL, 6000.00, 0.00, 0.00, 6000.00, 'paid', '2026-08-05', '2026-08-03 16:05:15', '2026-08-07 05:20:18'),
(84, 7, 61, 57, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'partial', '2026-08-05', '2026-08-03 17:01:41', '2026-09-01 08:40:58'),
(85, 7, 60, 58, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'paid', '2026-08-05', '2026-08-03 17:10:06', '2026-08-30 12:01:12'),
(86, 7, 79, 59, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 6000.00, 'partial', '2026-08-05', '2026-08-03 17:45:48', '2026-08-08 13:20:36'),
(87, 13, 85, 60, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7500.00, 'paid', '2026-08-05', '2026-08-06 16:00:19', '2026-08-06 16:01:29'),
(88, 13, 83, 50, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7500.00, 'pending', '2026-08-05', '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(89, 13, 84, 53, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7500.00, 'pending', '2026-08-05', '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(90, 7, 70, 61, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'paid', '2026-08-05', '2026-08-09 06:37:54', '2026-08-09 06:39:17'),
(91, 7, 72, 62, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 0.00, 'pending', '2026-08-05', '2026-08-20 02:43:12', '2026-09-01 11:27:13'),
(92, 7, 81, 63, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-08-05', '2026-08-20 18:06:50', '2026-09-01 09:26:27'),
(93, 7, 67, 64, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 0.00, 'paid', '2026-08-05', '2026-08-22 07:31:06', '2026-09-01 09:39:01'),
(94, 7, 76, 65, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 12000.00, 'partial', '2026-08-05', '2026-08-22 15:23:35', '2026-09-01 09:44:53'),
(95, 7, 54, 66, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 12000.00, 'pending', '2026-08-05', '2026-08-28 12:18:44', '2026-09-01 09:18:09'),
(96, 7, 73, 67, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'partial', '2026-08-05', '2026-08-28 12:42:05', '2026-08-28 12:45:24'),
(97, 7, 63, 68, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-08-05', '2026-08-28 13:00:49', '2026-08-28 13:00:49'),
(98, 7, 68, 69, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-08-05', '2026-08-30 19:07:43', '2026-09-01 09:33:14'),
(99, 7, 62, 70, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 06:01:29', '2026-09-01 06:01:29'),
(100, 7, 80, 71, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-09-05', '2026-09-01 09:56:32', '2026-09-01 09:56:32'),
(101, 7, 57, 46, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(102, 7, 70, 61, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(103, 7, 58, 56, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(104, 7, 59, 55, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(105, 7, 60, 58, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(106, 7, 61, 57, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(107, 7, 64, 51, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(108, 7, 66, 52, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(109, 7, 68, 69, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 21000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(110, 7, 52, 54, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 18500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(111, 7, 53, 43, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 12500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(112, 7, 54, 66, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 18500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(113, 7, 55, 45, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 12500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(114, 7, 63, 68, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(115, 7, 67, 64, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 0.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:30'),
(116, 7, 73, 67, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 19500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(117, 7, 75, 47, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(118, 7, 77, 48, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(119, 7, 79, 59, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(120, 7, 81, 63, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 21000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(121, 7, 72, 62, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(122, 7, 76, 65, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 19000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `bills_backup_deposit_fix`
--

CREATE TABLE `bills_backup_deposit_fix` (
  `id` int UNSIGNED NOT NULL DEFAULT '0',
  `owner_id` int UNSIGNED NOT NULL,
  `house_id` int UNSIGNED NOT NULL,
  `tenant_id` int UNSIGNED DEFAULT NULL COMMENT 'Snapshotted at generation time',
  `month` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'YYYY-MM format',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Rent',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rent` decimal(12,2) DEFAULT '0.00',
  `water` decimal(12,2) DEFAULT '0.00',
  `electricity` decimal(12,2) DEFAULT '0.00',
  `total` decimal(12,2) DEFAULT '0.00',
  `status` enum('paid','partial','pending','overdue') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bills_backup_deposit_fix`
--

INSERT INTO `bills_backup_deposit_fix` (`id`, `owner_id`, `house_id`, `tenant_id`, `month`, `type`, `description`, `rent`, `water`, `electricity`, `total`, `status`, `due_date`, `created_at`, `updated_at`) VALUES
(70, 7, 57, 46, '2026-08', 'Rent', NULL, 6000.00, 0.00, 0.00, 6000.00, 'partial', '2026-08-05', '2026-08-01 11:17:26', '2026-08-12 06:25:42'),
(71, 7, 75, 47, '2026-08', 'Rent', NULL, 6500.00, 0.00, 0.00, 6500.00, 'paid', '2026-08-05', '2026-08-01 12:34:14', '2026-08-12 06:21:02'),
(72, 7, 77, 48, '2026-08', 'Rent', NULL, 6500.00, 0.00, 0.00, 6500.00, 'paid', '2026-08-05', '2026-08-01 14:47:22', '2026-08-12 06:21:02'),
(75, 7, 64, 51, '2026-08', 'Rent', NULL, 7000.00, 0.00, 0.00, 7000.00, 'paid', '2026-08-05', '2026-08-02 17:05:10', '2026-08-05 15:26:05'),
(76, 7, 66, 52, '2026-08', 'Rent', NULL, 7000.00, 0.00, 0.00, 7000.00, 'paid', '2026-08-05', '2026-08-02 17:13:07', '2026-08-05 15:26:05'),
(78, 7, 52, 54, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 12000.00, 'partial', '2026-08-05', '2026-08-03 08:59:28', '2026-09-01 08:44:07'),
(79, 7, 59, 55, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 6000.00, 'partial', '2026-08-05', '2026-08-03 10:02:26', '2026-08-08 13:19:56'),
(80, 7, 58, 56, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 6000.00, 'partial', '2026-08-05', '2026-08-03 10:15:08', '2026-08-08 13:19:27'),
(81, 7, 53, 43, '2026-08', 'Rent', NULL, 6000.00, 0.00, 0.00, 6000.00, 'paid', '2026-08-05', '2026-08-03 16:05:15', '2026-08-05 18:25:58'),
(82, 7, 55, 45, '2026-08', 'Rent', NULL, 6000.00, 0.00, 0.00, 6000.00, 'paid', '2026-08-05', '2026-08-03 16:05:15', '2026-08-07 05:20:18'),
(84, 7, 61, 57, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'partial', '2026-08-05', '2026-08-03 17:01:41', '2026-09-01 08:40:58'),
(85, 7, 60, 58, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'paid', '2026-08-05', '2026-08-03 17:10:06', '2026-08-30 12:01:12'),
(86, 7, 79, 59, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 6000.00, 'partial', '2026-08-05', '2026-08-03 17:45:48', '2026-08-08 13:20:36'),
(87, 13, 85, 60, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7500.00, 'paid', '2026-08-05', '2026-08-06 16:00:19', '2026-08-06 16:01:29'),
(88, 13, 83, 50, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7500.00, 'pending', '2026-08-05', '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(89, 13, 84, 53, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7500.00, 'pending', '2026-08-05', '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(90, 7, 70, 61, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'paid', '2026-08-05', '2026-08-09 06:37:54', '2026-08-09 06:39:17'),
(91, 7, 72, 62, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 0.00, 'pending', '2026-08-05', '2026-08-20 02:43:12', '2026-09-01 11:27:13'),
(92, 7, 81, 63, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-08-05', '2026-08-20 18:06:50', '2026-09-01 09:26:27'),
(93, 7, 67, 64, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 0.00, 'paid', '2026-08-05', '2026-08-22 07:31:06', '2026-09-01 09:39:01'),
(94, 7, 76, 65, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 12000.00, 'partial', '2026-08-05', '2026-08-22 15:23:35', '2026-09-01 09:44:53'),
(95, 7, 54, 66, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 12000.00, 'pending', '2026-08-05', '2026-08-28 12:18:44', '2026-09-01 09:18:09'),
(96, 7, 73, 67, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'partial', '2026-08-05', '2026-08-28 12:42:05', '2026-08-28 12:45:24'),
(97, 7, 63, 68, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-08-05', '2026-08-28 13:00:49', '2026-08-28 13:00:49'),
(98, 7, 68, 69, '2026-08', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-08-05', '2026-08-30 19:07:43', '2026-09-01 09:33:14'),
(99, 7, 62, 70, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 06:01:29', '2026-09-01 06:01:29'),
(100, 7, 80, 71, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-09-05', '2026-09-01 09:56:32', '2026-09-01 09:56:32'),
(101, 7, 57, 46, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(102, 7, 70, 61, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(103, 7, 58, 56, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(104, 7, 59, 55, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(105, 7, 60, 58, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(106, 7, 61, 57, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(107, 7, 64, 51, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(108, 7, 66, 52, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(109, 7, 68, 69, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 21000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(110, 7, 52, 54, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 18500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(111, 7, 53, 43, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 12500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(112, 7, 54, 66, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 18500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(113, 7, 55, 45, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 12500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(114, 7, 63, 68, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 14000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(115, 7, 67, 64, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 0.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:30'),
(116, 7, 73, 67, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 19500.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(117, 7, 75, 47, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(118, 7, 77, 48, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(119, 7, 79, 59, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 13000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(120, 7, 81, 63, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 21000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(121, 7, 72, 62, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 7000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(122, 7, 76, 65, '2026-09', 'Rent', NULL, 0.00, 0.00, 0.00, 19000.00, 'pending', '2026-09-05', '2026-09-01 11:27:21', '2026-09-01 11:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `bill_items`
--

CREATE TABLE `bill_items` (
  `id` int UNSIGNED NOT NULL,
  `bill_id` int UNSIGNED NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','partial','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bill_items`
--

INSERT INTO `bill_items` (`id`, `bill_id`, `type`, `description`, `amount`, `paid`, `status`, `created_at`, `updated_at`) VALUES
(4, 71, 'Rent', 'Monthly Rent', 6500.00, 6500.00, 'paid', '2026-08-03 08:34:49', '2026-08-03 16:05:15'),
(5, 72, 'Rent', 'Monthly Rent', 6500.00, 6500.00, 'paid', '2026-08-03 08:35:25', '2026-08-03 16:05:15'),
(6, 70, 'Rent', 'Monthly Rent', 6500.00, 6000.00, 'partial', '2026-08-03 08:35:45', '2026-08-11 10:50:21'),
(7, 76, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-03 08:35:58', '2026-08-03 16:05:15'),
(8, 75, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-03 08:39:25', '2026-08-03 16:05:15'),
(9, 78, 'Rent', 'Monthly Rent', 6000.00, 6000.00, 'paid', '2026-08-03 08:59:28', '2026-09-01 08:44:07'),
(11, 79, 'Rent', 'Monthly Rent', 6500.00, 6000.00, 'partial', '2026-08-03 10:02:26', '2026-08-05 13:41:19'),
(13, 80, 'Rent', 'Monthly Rent', 6500.00, 6000.00, 'partial', '2026-08-03 10:15:08', '2026-08-05 06:38:50'),
(15, 81, 'Rent', 'Monthly Rent', 6000.00, 6000.00, 'paid', '2026-08-03 16:05:15', '2026-08-05 18:25:58'),
(16, 82, 'Rent', 'Monthly Rent', 6000.00, 6000.00, 'paid', '2026-08-03 16:05:15', '2026-08-07 05:20:18'),
(21, 84, 'Rent', 'Monthly Rent', 6500.00, 6500.00, 'paid', '2026-08-03 17:01:41', '2026-08-12 06:30:09'),
(23, 85, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-03 17:10:06', '2026-08-30 12:01:12'),
(25, 86, 'Rent', 'Monthly Rent', 6500.00, 6000.00, 'partial', '2026-08-03 17:45:48', '2026-08-05 06:25:12'),
(27, 87, 'Rent', 'Monthly Rent', 7500.00, 7500.00, 'paid', '2026-08-06 16:00:19', '2026-08-06 16:01:29'),
(28, 88, 'Rent', 'Monthly Rent', 7500.00, 0.00, 'pending', '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(29, 89, 'Rent', 'Monthly Rent', 7500.00, 0.00, 'pending', '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(30, 90, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-09 06:37:54', '2026-08-09 06:39:17'),
(31, 91, 'Rent', 'Monthly Rent', 0.00, 0.00, 'pending', '2026-08-20 02:43:12', '2026-09-01 11:27:13'),
(33, 92, 'Deposit', 'Security Deposit', 7000.00, 0.00, 'pending', '2026-08-20 18:06:50', '2026-08-20 18:06:50'),
(34, 93, 'Rent', 'Monthly Rent', 0.00, 7000.00, 'paid', '2026-08-22 07:31:06', '2026-09-01 09:39:01'),
(35, 94, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-22 15:23:35', '2026-08-22 15:24:42'),
(36, 94, 'Deposit', 'Security Deposit', 5000.00, 0.00, 'pending', '2026-08-22 15:23:35', '2026-09-01 09:44:53'),
(37, 95, 'Rent', 'Monthly Rent', 6000.00, 0.00, 'pending', '2026-08-28 12:18:44', '2026-09-01 09:18:09'),
(38, 96, 'Rent', 'Monthly Rent', 6500.00, 6500.00, 'paid', '2026-08-28 12:42:05', '2026-08-28 12:45:24'),
(39, 96, 'Deposit', 'Security Deposit', 6500.00, 0.00, 'pending', '2026-08-28 12:42:05', '2026-08-28 12:42:05'),
(40, 97, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-08-28 13:00:49', '2026-08-28 13:00:49'),
(42, 98, 'Deposit', 'Security Deposit', 7000.00, 0.00, 'pending', '2026-08-30 19:07:43', '2026-08-30 19:07:43'),
(43, 99, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 06:01:29', '2026-09-01 06:01:29'),
(44, 99, 'Deposit', 'Security Deposit', 7000.00, 0.00, 'pending', '2026-09-01 06:01:29', '2026-09-01 06:01:29'),
(45, 84, 'Deposit', 'Deposit', 6500.00, 0.00, 'pending', '2026-09-01 08:40:58', '2026-09-01 08:40:58'),
(46, 78, 'Deposit', 'Deposit', 6000.00, 0.00, 'pending', '2026-09-01 08:43:33', '2026-09-01 08:43:33'),
(47, 95, 'Deposit', 'Deposit', 6000.00, 0.00, 'pending', '2026-09-01 09:18:09', '2026-09-01 09:18:09'),
(48, 100, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 09:56:32', '2026-09-01 09:56:32'),
(49, 101, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(50, 101, 'Opening Balance', 'Previous Month Balance', 500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(52, 102, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(54, 103, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(55, 103, 'Opening Balance', 'Previous Month Balance', 500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(57, 104, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(58, 104, 'Opening Balance', 'Previous Month Balance', 500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(60, 105, 'Rent', 'Monthly Rent', 7500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(62, 106, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(63, 106, 'Opening Balance', 'Previous Month Balance', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(64, 107, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(66, 108, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(68, 109, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(69, 109, 'Opening Balance', 'Previous Month Balance', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(71, 110, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(72, 110, 'Opening Balance', 'Previous Month Balance', 6000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(74, 111, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(76, 112, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(77, 112, 'Opening Balance', 'Previous Month Balance', 12000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(78, 113, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(80, 114, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(81, 114, 'Opening Balance', 'Previous Month Balance', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(82, 116, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(83, 116, 'Opening Balance', 'Previous Month Balance', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(85, 117, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(87, 118, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(89, 119, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(90, 119, 'Opening Balance', 'Previous Month Balance', 500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(92, 120, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(93, 120, 'Opening Balance', 'Previous Month Balance', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(95, 121, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(96, 122, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(97, 122, 'Opening Balance', 'Previous Month Balance', 5000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `bill_items_backup_deposit_fix`
--

CREATE TABLE `bill_items_backup_deposit_fix` (
  `id` int UNSIGNED NOT NULL DEFAULT '0',
  `bill_id` int UNSIGNED NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','partial','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bill_items_backup_deposit_fix`
--

INSERT INTO `bill_items_backup_deposit_fix` (`id`, `bill_id`, `type`, `description`, `amount`, `paid`, `status`, `created_at`, `updated_at`) VALUES
(4, 71, 'Rent', 'Monthly Rent', 6500.00, 6500.00, 'paid', '2026-08-03 08:34:49', '2026-08-03 16:05:15'),
(5, 72, 'Rent', 'Monthly Rent', 6500.00, 6500.00, 'paid', '2026-08-03 08:35:25', '2026-08-03 16:05:15'),
(6, 70, 'Rent', 'Monthly Rent', 6500.00, 6000.00, 'partial', '2026-08-03 08:35:45', '2026-08-11 10:50:21'),
(7, 76, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-03 08:35:58', '2026-08-03 16:05:15'),
(8, 75, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-03 08:39:25', '2026-08-03 16:05:15'),
(9, 78, 'Rent', 'Monthly Rent', 6000.00, 6000.00, 'paid', '2026-08-03 08:59:28', '2026-09-01 08:44:07'),
(11, 79, 'Rent', 'Monthly Rent', 6500.00, 6000.00, 'partial', '2026-08-03 10:02:26', '2026-08-05 13:41:19'),
(13, 80, 'Rent', 'Monthly Rent', 6500.00, 6000.00, 'partial', '2026-08-03 10:15:08', '2026-08-05 06:38:50'),
(15, 81, 'Rent', 'Monthly Rent', 6000.00, 6000.00, 'paid', '2026-08-03 16:05:15', '2026-08-05 18:25:58'),
(16, 82, 'Rent', 'Monthly Rent', 6000.00, 6000.00, 'paid', '2026-08-03 16:05:15', '2026-08-07 05:20:18'),
(21, 84, 'Rent', 'Monthly Rent', 6500.00, 6500.00, 'paid', '2026-08-03 17:01:41', '2026-08-12 06:30:09'),
(23, 85, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-03 17:10:06', '2026-08-30 12:01:12'),
(25, 86, 'Rent', 'Monthly Rent', 6500.00, 6000.00, 'partial', '2026-08-03 17:45:48', '2026-08-05 06:25:12'),
(27, 87, 'Rent', 'Monthly Rent', 7500.00, 7500.00, 'paid', '2026-08-06 16:00:19', '2026-08-06 16:01:29'),
(28, 88, 'Rent', 'Monthly Rent', 7500.00, 0.00, 'pending', '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(29, 89, 'Rent', 'Monthly Rent', 7500.00, 0.00, 'pending', '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(30, 90, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-09 06:37:54', '2026-08-09 06:39:17'),
(31, 91, 'Rent', 'Monthly Rent', 0.00, 0.00, 'pending', '2026-08-20 02:43:12', '2026-09-01 11:27:13'),
(33, 92, 'Deposit', 'Security Deposit', 7000.00, 0.00, 'pending', '2026-08-20 18:06:50', '2026-08-20 18:06:50'),
(34, 93, 'Rent', 'Monthly Rent', 0.00, 7000.00, 'paid', '2026-08-22 07:31:06', '2026-09-01 09:39:01'),
(35, 94, 'Rent', 'Monthly Rent', 7000.00, 7000.00, 'paid', '2026-08-22 15:23:35', '2026-08-22 15:24:42'),
(36, 94, 'Deposit', 'Security Deposit', 5000.00, 0.00, 'pending', '2026-08-22 15:23:35', '2026-09-01 09:44:53'),
(37, 95, 'Rent', 'Monthly Rent', 6000.00, 0.00, 'pending', '2026-08-28 12:18:44', '2026-09-01 09:18:09'),
(38, 96, 'Rent', 'Monthly Rent', 6500.00, 6500.00, 'paid', '2026-08-28 12:42:05', '2026-08-28 12:45:24'),
(39, 96, 'Deposit', 'Security Deposit', 6500.00, 0.00, 'pending', '2026-08-28 12:42:05', '2026-08-28 12:42:05'),
(40, 97, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-08-28 13:00:49', '2026-08-28 13:00:49'),
(42, 98, 'Deposit', 'Security Deposit', 7000.00, 0.00, 'pending', '2026-08-30 19:07:43', '2026-08-30 19:07:43'),
(43, 99, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 06:01:29', '2026-09-01 06:01:29'),
(44, 99, 'Deposit', 'Security Deposit', 7000.00, 0.00, 'pending', '2026-09-01 06:01:29', '2026-09-01 06:01:29'),
(45, 84, 'Deposit', 'Deposit', 6500.00, 0.00, 'pending', '2026-09-01 08:40:58', '2026-09-01 08:40:58'),
(46, 78, 'Deposit', 'Deposit', 6000.00, 0.00, 'pending', '2026-09-01 08:43:33', '2026-09-01 08:43:33'),
(47, 95, 'Deposit', 'Deposit', 6000.00, 0.00, 'pending', '2026-09-01 09:18:09', '2026-09-01 09:18:09'),
(48, 100, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 09:56:32', '2026-09-01 09:56:32'),
(49, 101, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(50, 101, 'Opening Balance', 'Previous Month Balance', 500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(52, 102, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(54, 103, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(55, 103, 'Opening Balance', 'Previous Month Balance', 500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(57, 104, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(58, 104, 'Opening Balance', 'Previous Month Balance', 500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(60, 105, 'Rent', 'Monthly Rent', 7500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(62, 106, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(63, 106, 'Opening Balance', 'Previous Month Balance', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(64, 107, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(66, 108, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(68, 109, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(69, 109, 'Opening Balance', 'Previous Month Balance', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(71, 110, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(72, 110, 'Opening Balance', 'Previous Month Balance', 6000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(74, 111, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(76, 112, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(77, 112, 'Opening Balance', 'Previous Month Balance', 12000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(78, 113, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(80, 114, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(81, 114, 'Opening Balance', 'Previous Month Balance', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(82, 116, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(83, 116, 'Opening Balance', 'Previous Month Balance', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(85, 117, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(87, 118, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(89, 119, 'Rent', 'Monthly Rent', 6500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(90, 119, 'Opening Balance', 'Previous Month Balance', 500.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(92, 120, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(93, 120, 'Opening Balance', 'Previous Month Balance', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(95, 121, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(96, 122, 'Rent', 'Monthly Rent', 7000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(97, 122, 'Opening Balance', 'Previous Month Balance', 5000.00, 0.00, 'pending', '2026-09-01 11:27:21', '2026-09-01 11:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_ips`
--

CREATE TABLE `blocked_ips` (
  `id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `blocked_until` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bot_detections`
--

CREATE TABLE `bot_detections` (
  `id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempts` int DEFAULT '1',
  `last_seen` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bot_detections`
--

INSERT INTO `bot_detections` (`id`, `ip_address`, `user_agent`, `endpoint`, `method`, `attempts`, `last_seen`, `created_at`) VALUES
(9871, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:46:24'),
(9872, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:46:24'),
(9890, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:46:51'),
(9891, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:46:51'),
(9894, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:05'),
(9895, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:05'),
(9896, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:25'),
(9897, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:25'),
(9898, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:29'),
(9899, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:29'),
(9900, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:31'),
(9901, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:31'),
(9902, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:36'),
(9903, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:36'),
(9904, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:40'),
(9905, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:40'),
(9906, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:40'),
(9907, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:53'),
(9908, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:54'),
(9909, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:47:54'),
(9910, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/55', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:48:08'),
(9911, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:48:08'),
(9921, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:48:33'),
(9922, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:48:34'),
(9923, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:48:34'),
(9924, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:48:35'),
(9930, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/59', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:48:43'),
(9931, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-25 11:48:43'),
(9937, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/59', 'GET', 1, '2026-08-25 18:55:24', '2026-08-25 18:55:24'),
(9938, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/59', 'GET', 1, '2026-08-25 18:55:25', '2026-08-25 18:55:25'),
(9939, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-25 18:55:26', '2026-08-25 18:55:26'),
(9940, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:12:19'),
(9941, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:12:19'),
(9942, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:12:19'),
(9943, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:12:24'),
(9944, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:12:26'),
(9945, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:12:26'),
(9946, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:12:27'),
(9947, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/89/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1Mywicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTMsImlhdCI6MTc4NjE4OTI4NiwiZXhwIjoxNzg2Nzk0MDg2fQ.7ZsUsmcam0XBmeTOFmdlbnM9eWv53Wmn7jTYtqru9zI', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:12:47'),
(9948, '172.253.7.124', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '/api/bills/89/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1Mywicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTMsImlhdCI6MTc4NjE4OTI4NiwiZXhwIjoxNzg2Nzk0MDg2fQ.7ZsUsmcam0XBmeTOFmdlbnM9eWv53Wmn7jTYtqru9zI', 'GET', 1, '2026-08-26 06:12:55', '2026-08-26 06:12:55'),
(9949, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/578', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:01'),
(9950, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/577', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:35'),
(9951, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:57'),
(9952, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:58'),
(9953, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:59'),
(9954, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:59'),
(9955, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:59'),
(9956, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:59'),
(9957, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:59'),
(9958, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-26 06:13:59'),
(9959, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:02'),
(9960, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:02'),
(9961, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:02'),
(9962, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:06'),
(9963, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:06'),
(9964, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:06'),
(9965, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:06'),
(9966, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:08'),
(9967, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/577', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:24'),
(9968, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/577', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:28'),
(9969, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:35'),
(9970, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:36'),
(9971, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:36'),
(9972, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:14:37'),
(9973, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/571', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:15:06'),
(9974, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/574', 'GET', 3, '2026-08-30 11:40:46', '2026-08-26 06:15:19'),
(9975, '154.159.252.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-27 18:25:16', '2026-08-27 18:25:16'),
(9976, '154.159.252.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-27 18:25:18', '2026-08-27 18:25:18'),
(9977, '154.159.252.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-27 18:25:19', '2026-08-27 18:25:19'),
(9978, '154.159.252.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-27 19:20:23', '2026-08-27 19:20:23'),
(9979, '154.159.252.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-27 19:20:23', '2026-08-27 19:20:23'),
(9980, '154.159.252.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-27 19:20:23', '2026-08-27 19:20:23'),
(9981, '154.159.252.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-27 19:20:24', '2026-08-27 19:20:24'),
(9982, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 11:43:57'),
(9983, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 11:43:57'),
(9984, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 11:44:57'),
(9985, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 11:44:57'),
(9986, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:44:59'),
(9987, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:44:59'),
(9988, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:44:59'),
(9989, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:04'),
(9990, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:05'),
(9991, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:05'),
(9992, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:38'),
(9993, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:38'),
(9994, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:38'),
(9995, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:38'),
(9996, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:38'),
(9997, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:39'),
(9998, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:54'),
(9999, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:54'),
(10000, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:54'),
(10001, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:45:54'),
(10002, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:07'),
(10003, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:24'),
(10004, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:24'),
(10005, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:24'),
(10006, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:32'),
(10007, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:33'),
(10008, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:33'),
(10009, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:40'),
(10010, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:40'),
(10011, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:40'),
(10012, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:40'),
(10013, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:47'),
(10014, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:51'),
(10015, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:51'),
(10016, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:46:51'),
(10017, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:06'),
(10018, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:06'),
(10019, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:06'),
(10020, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:06'),
(10021, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:16'),
(10022, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:16'),
(10023, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:16'),
(10024, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:17'),
(10025, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:17'),
(10026, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:17'),
(10027, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:22'),
(10028, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:23'),
(10029, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:23'),
(10030, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:23'),
(10031, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/94/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:50'),
(10032, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/91/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:48:56'),
(10033, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:09'),
(10034, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:12'),
(10035, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:12'),
(10036, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:12'),
(10037, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:35'),
(10038, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:35'),
(10039, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:35'),
(10040, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:35'),
(10041, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:37'),
(10042, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:37'),
(10043, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:37'),
(10044, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:49:37'),
(10045, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:02'),
(10046, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:02'),
(10047, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:02'),
(10048, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:02'),
(10049, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:02'),
(10050, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:02'),
(10051, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:06'),
(10052, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:06'),
(10053, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:06'),
(10054, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:07'),
(10055, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:07'),
(10056, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:07'),
(10057, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:09'),
(10058, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:09'),
(10059, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:09'),
(10060, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:09'),
(10061, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:09'),
(10062, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:09'),
(10063, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:10'),
(10064, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:10'),
(10065, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:10'),
(10066, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:14'),
(10067, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:14'),
(10068, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:14'),
(10069, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:14'),
(10070, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:15'),
(10071, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:15'),
(10072, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:15'),
(10073, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:22'),
(10074, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:23'),
(10075, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:23'),
(10076, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:23'),
(10077, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:36'),
(10078, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:36'),
(10079, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:36'),
(10080, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:37'),
(10081, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:49'),
(10082, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:50'),
(10083, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:50'),
(10084, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:53'),
(10085, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:53'),
(10086, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:53'),
(10087, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:50:53'),
(10088, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:03'),
(10089, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:04'),
(10090, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:04'),
(10091, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:28'),
(10092, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:28'),
(10093, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:28'),
(10094, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:29'),
(10095, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/58', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:53'),
(10096, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:51:54'),
(10097, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/58', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 11:52:00'),
(10098, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:52:01'),
(10099, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/59', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 11:52:24'),
(10100, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:52:25'),
(10101, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/59', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 11:52:30'),
(10102, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:52:30'),
(10103, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:52:34'),
(10104, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:52:34'),
(10105, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:03'),
(10106, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:03'),
(10107, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:03'),
(10108, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:03'),
(10109, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:04'),
(10110, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:06'),
(10111, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/59', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:09'),
(10112, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:10'),
(10113, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:10'),
(10114, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:10'),
(10115, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:10'),
(10116, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:10'),
(10117, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:11'),
(10118, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:12'),
(10119, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:13'),
(10120, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:13'),
(10121, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:16'),
(10122, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:16'),
(10123, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:16'),
(10124, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:16'),
(10125, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:39'),
(10126, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:39'),
(10127, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:39'),
(10128, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:53'),
(10129, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:56');
INSERT INTO `bot_detections` (`id`, `ip_address`, `user_agent`, `endpoint`, `method`, `attempts`, `last_seen`, `created_at`) VALUES
(10130, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:56'),
(10131, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:53:57'),
(10132, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:54:52'),
(10133, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:54:54'),
(10134, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:54:54'),
(10135, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:08'),
(10136, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:09'),
(10137, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/60', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:10'),
(10138, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:18'),
(10139, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:19'),
(10140, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/61', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:20'),
(10141, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:47'),
(10142, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:47'),
(10143, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:55:47'),
(10144, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:56:31'),
(10145, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:56:31'),
(10146, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:56:31'),
(10147, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/57', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 11:57:00'),
(10148, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:57:01'),
(10149, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:57:20'),
(10150, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:57:20'),
(10151, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:57:20'),
(10152, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:57:20'),
(10153, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/84/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:57:31'),
(10154, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/85/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:57:47'),
(10155, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:11'),
(10156, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:11'),
(10157, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:11'),
(10158, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:39'),
(10159, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:46'),
(10160, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:46'),
(10161, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:49'),
(10162, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:53'),
(10163, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:58:53'),
(10164, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:59:05'),
(10165, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:59:05'),
(10166, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:59:05'),
(10167, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:59:05'),
(10168, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:59:53'),
(10169, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:59:54'),
(10170, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 11:59:54'),
(10171, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:17'),
(10172, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:18'),
(10173, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/61', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:18'),
(10174, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/57', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:24'),
(10175, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:24'),
(10176, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:28'),
(10177, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:28'),
(10178, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:31'),
(10179, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:31'),
(10180, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:31'),
(10181, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:31'),
(10182, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/62', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:42'),
(10183, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:42'),
(10184, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:43'),
(10185, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:43'),
(10186, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:55'),
(10187, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:55'),
(10188, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:55'),
(10189, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/57', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:57'),
(10190, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:00:57'),
(10191, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:06'),
(10192, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:06'),
(10193, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:06'),
(10194, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/57', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:30'),
(10195, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:30'),
(10196, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/57', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:30'),
(10197, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:30'),
(10198, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:32'),
(10199, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:32'),
(10200, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:33'),
(10201, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:33'),
(10202, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:33'),
(10203, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/52', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:39'),
(10204, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:39'),
(10205, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:01:57'),
(10206, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:02:03'),
(10207, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:04'),
(10208, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:04'),
(10209, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:04'),
(10210, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/52', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:15'),
(10211, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:16'),
(10212, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/52', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:22'),
(10213, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:23'),
(10214, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:46'),
(10215, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:47'),
(10216, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:03:48'),
(10217, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:04:15'),
(10218, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:04:16'),
(10219, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/52', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:04:17'),
(10220, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/54', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:04:28'),
(10221, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:04:30'),
(10222, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:05:50'),
(10223, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:05:51'),
(10224, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:05:56'),
(10225, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:05:57'),
(10226, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/52', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:05:58'),
(10227, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/54', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:06:04'),
(10228, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:06:06'),
(10229, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:06:18'),
(10230, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:06:18'),
(10231, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:06:19'),
(10232, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:06:20'),
(10233, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:06:20'),
(10234, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:06:20'),
(10235, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/79', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:07:27'),
(10236, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:07:29'),
(10237, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/54', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:07:57'),
(10238, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:07:57'),
(10239, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/54', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:14'),
(10240, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:14'),
(10241, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:16'),
(10242, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:16'),
(10243, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:16'),
(10244, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:17'),
(10245, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:17'),
(10246, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:17'),
(10247, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/54', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:27'),
(10248, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:29'),
(10249, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:31'),
(10250, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:31'),
(10251, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:31'),
(10252, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:32'),
(10253, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:32'),
(10254, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:32'),
(10255, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/63', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:55'),
(10256, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:08:55'),
(10257, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:11:21'),
(10258, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:11:22'),
(10259, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:11:22'),
(10260, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:11:24'),
(10261, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:17:35'),
(10262, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 12:18:44'),
(10263, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:18:45'),
(10264, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:18:56'),
(10265, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:18:56'),
(10266, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:18:59'),
(10267, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:18:59'),
(10268, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:18:59'),
(10269, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:00'),
(10270, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:24'),
(10271, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:25'),
(10272, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:25'),
(10273, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:25'),
(10274, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:32'),
(10275, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:32'),
(10276, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:32'),
(10277, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:38'),
(10278, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:38'),
(10279, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:38'),
(10280, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:39'),
(10281, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:57'),
(10282, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:57'),
(10283, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:57'),
(10284, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:19:57'),
(10285, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:20:05'),
(10286, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:20:06'),
(10287, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:20:06'),
(10288, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:23:14'),
(10289, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:23:14'),
(10290, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:23:14'),
(10291, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:23:14'),
(10292, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:23:37'),
(10293, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:23:38'),
(10294, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:23:38'),
(10295, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:25:41'),
(10296, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:25:43'),
(10297, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:25:43'),
(10298, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:26:12'),
(10299, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:26:13'),
(10300, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:26:13'),
(10301, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:26:13'),
(10302, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:26:37'),
(10303, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:26:37'),
(10304, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:26:37'),
(10305, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:26:37'),
(10306, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:27:30'),
(10307, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:27:31'),
(10308, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:27:31'),
(10309, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:27:31'),
(10310, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:27:44'),
(10311, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:27:45'),
(10312, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:27:45'),
(10313, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:27:45'),
(10314, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:28:02'),
(10315, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:28:02'),
(10316, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:28:02'),
(10317, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:28:03'),
(10318, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/92/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:28:09'),
(10319, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:20'),
(10320, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:22'),
(10321, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:22'),
(10322, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:22'),
(10323, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:23'),
(10324, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:23'),
(10325, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:23'),
(10326, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:23'),
(10327, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=10', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:51'),
(10328, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:29:55'),
(10329, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:09'),
(10330, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:10'),
(10331, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:10'),
(10332, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:10'),
(10333, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:28'),
(10334, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:28'),
(10335, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:28'),
(10336, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:28'),
(10337, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:38'),
(10338, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:39'),
(10339, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:31:39'),
(10340, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/66', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:05'),
(10341, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:05'),
(10342, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:21'),
(10343, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:21'),
(10344, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:22'),
(10345, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:22'),
(10346, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:25'),
(10347, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:25'),
(10348, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:25'),
(10349, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:25'),
(10350, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:42'),
(10351, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:42'),
(10352, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=50', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:42'),
(10353, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:33:42');
INSERT INTO `bot_detections` (`id`, `ip_address`, `user_agent`, `endpoint`, `method`, `attempts`, `last_seen`, `created_at`) VALUES
(10354, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:34:22'),
(10355, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:34:22'),
(10356, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:34:22'),
(10357, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:34:22'),
(10358, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:34:25'),
(10359, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:34:39'),
(10360, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:34:39'),
(10361, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:34:40'),
(10362, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/71/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:35:22'),
(10363, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:36:21'),
(10364, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:36:22'),
(10365, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:36:22'),
(10366, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:36:22'),
(10367, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:36:52'),
(10368, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:36:53'),
(10369, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:36:53'),
(10370, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:36:54'),
(10371, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:37:11'),
(10372, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:37:11'),
(10373, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:37:11'),
(10374, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:37:12'),
(10375, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/581', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:37:15'),
(10376, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=bill&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:37:37'),
(10377, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=bill&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:37:40'),
(10378, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:37:53'),
(10379, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:38:03'),
(10380, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:38:03'),
(10381, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:38:03'),
(10382, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:38:04'),
(10383, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:40:48'),
(10384, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:05'),
(10385, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:06'),
(10386, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:11'),
(10387, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:11'),
(10388, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:11'),
(10389, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:11'),
(10390, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:25'),
(10391, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:25'),
(10392, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:25'),
(10393, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:25'),
(10394, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:33'),
(10395, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:33'),
(10396, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:33'),
(10397, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:42:33'),
(10398, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:16'),
(10399, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:17'),
(10400, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:22'),
(10401, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:22'),
(10402, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:22'),
(10403, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:22'),
(10404, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:25'),
(10405, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:25'),
(10406, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:25'),
(10407, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:25'),
(10408, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/96/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:43:56'),
(10409, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:44:57'),
(10410, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:44:57'),
(10411, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:44:57'),
(10412, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:44:57'),
(10413, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:44:58'),
(10414, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments/tenant-finance/67', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:12'),
(10415, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:24'),
(10416, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:25'),
(10417, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:28'),
(10418, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:28'),
(10419, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:28'),
(10420, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:29'),
(10421, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:53'),
(10422, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:53'),
(10423, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:53'),
(10424, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:53'),
(10425, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/583', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:45:59'),
(10426, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:18'),
(10427, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:18'),
(10428, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:18'),
(10429, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:18'),
(10430, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:19'),
(10431, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:40'),
(10432, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:41'),
(10433, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:41'),
(10434, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:41'),
(10435, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/583', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:46:44'),
(10436, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:47:09'),
(10437, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:47:09'),
(10438, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:47:09'),
(10439, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:47:09'),
(10440, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:48:28'),
(10441, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:48:28'),
(10442, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/documents', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:48:28'),
(10443, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:48:28'),
(10444, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:49:19'),
(10445, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:49:19'),
(10446, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:49:19'),
(10447, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:49:19'),
(10448, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:49:34'),
(10449, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:49:34'),
(10450, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:21'),
(10451, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:22'),
(10452, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:22'),
(10453, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:22'),
(10454, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:24'),
(10455, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:24'),
(10456, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:25'),
(10457, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:27'),
(10458, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:27'),
(10459, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:27'),
(10460, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:27'),
(10461, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:33'),
(10462, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:33'),
(10463, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:33'),
(10464, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:50:59'),
(10465, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:52:54'),
(10466, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:52:54'),
(10467, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:52:54'),
(10468, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:52:54'),
(10469, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:54:52'),
(10470, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:54:52'),
(10471, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:54:52'),
(10472, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:54:58'),
(10473, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:55:02'),
(10474, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:55:02'),
(10475, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:55:04'),
(10476, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 12:58:56'),
(10477, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 13:00:49'),
(10478, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:00:50'),
(10479, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:00:54'),
(10480, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:00:55'),
(10481, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:00:55'),
(10482, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:00:55'),
(10483, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:01:02'),
(10484, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:01:03'),
(10485, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:01:03'),
(10486, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:01:06'),
(10487, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:01:07'),
(10488, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:01:07'),
(10489, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:01:07'),
(10490, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:09:57'),
(10491, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:09:58'),
(10492, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:09:58'),
(10493, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:09:58'),
(10494, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:10:39'),
(10495, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:10:40'),
(10496, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:10:40'),
(10497, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:10:40'),
(10498, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:11:07'),
(10499, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:11:08'),
(10500, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:11:08'),
(10501, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:11:27'),
(10502, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:11:27'),
(10503, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:11:35'),
(10504, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:11:35'),
(10505, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/67', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:11:35'),
(10506, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/64', 'PUT', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:05'),
(10507, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:05'),
(10508, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:09'),
(10509, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:09'),
(10510, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:09'),
(10511, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:09'),
(10512, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:14'),
(10513, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:14'),
(10514, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:23'),
(10515, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:25'),
(10516, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:25'),
(10517, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:12:25'),
(10518, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:13:40'),
(10519, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:13:40'),
(10520, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:13:40'),
(10521, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:13:41'),
(10522, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:13:41'),
(10523, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments/tenant-finance/64', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:13:44'),
(10524, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'POST', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:03'),
(10525, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:05'),
(10526, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:12'),
(10527, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:12'),
(10528, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:12'),
(10529, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:13'),
(10530, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODc5MTc0OTcsImV4cCI6MTc4ODUyMjI5N30.BCFmWBt2R3p7QOsby1otQSQFdha04unW2yheE6F4fGM', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:22'),
(10531, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:49'),
(10532, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:49'),
(10533, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:50'),
(10534, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:51'),
(10535, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:51'),
(10536, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:57'),
(10537, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:57'),
(10538, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/67', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:14:58'),
(10539, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:15:16'),
(10540, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:15:16'),
(10541, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:15:28'),
(10542, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:15:29'),
(10543, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:15:29'),
(10544, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:15:29'),
(10545, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:18:31'),
(10546, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:18:32'),
(10547, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:18:32'),
(10548, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:18:32'),
(10549, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:18:59'),
(10550, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:18:59'),
(10551, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:18:59'),
(10552, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:19:00'),
(10553, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/78', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:19:06'),
(10554, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:19:06'),
(10555, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:19:08'),
(10556, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:19:08'),
(10557, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:19:08'),
(10558, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:13'),
(10559, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:13'),
(10560, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:13'),
(10561, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:13'),
(10562, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:23'),
(10563, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:24'),
(10564, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:24'),
(10565, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:24'),
(10566, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:42'),
(10567, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:42'),
(10568, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:42'),
(10569, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:20:42'),
(10570, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:21:50'),
(10571, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:21:58'),
(10572, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:21:58'),
(10573, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:21:58'),
(10574, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:21:58'),
(10575, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:22:13');
INSERT INTO `bot_detections` (`id`, `ip_address`, `user_agent`, `endpoint`, `method`, `attempts`, `last_seen`, `created_at`) VALUES
(10576, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:22:13'),
(10577, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:22:14'),
(10578, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:22:22'),
(10579, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:22:23'),
(10580, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:23:21'),
(10581, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:23:21'),
(10582, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:23:21'),
(10583, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-28 13:23:21'),
(10584, '172.253.15.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ', 'GET', 1, '2026-08-28 13:59:29', '2026-08-28 13:59:29'),
(10585, '102.213.49.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ', 'GET', 1, '2026-08-28 14:00:00', '2026-08-28 14:00:00'),
(10586, '142.250.32.97', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ', 'GET', 1, '2026-08-28 14:00:02', '2026-08-28 14:00:02'),
(10587, '142.250.32.97', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ', 'GET', 1, '2026-08-28 14:00:02', '2026-08-28 14:00:02'),
(10588, '192.178.11.2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ', 'GET', 1, '2026-08-28 14:00:02', '2026-08-28 14:00:02'),
(10589, '102.213.49.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ', 'GET', 1, '2026-08-28 14:00:18', '2026-08-28 14:00:18'),
(10590, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 1, '2026-08-28 16:55:09', '2026-08-28 16:55:09'),
(10591, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 1, '2026-08-28 16:55:09', '2026-08-28 16:55:09'),
(10592, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-28 16:55:09', '2026-08-28 16:55:09'),
(10593, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-28 16:55:12', '2026-08-28 16:55:12'),
(10594, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-28 16:55:12', '2026-08-28 16:55:12'),
(10595, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-08-28 16:55:12', '2026-08-28 16:55:12'),
(10596, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-28 16:55:12', '2026-08-28 16:55:12'),
(10597, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses/62', 'PUT', 1, '2026-08-28 16:55:34', '2026-08-28 16:55:34'),
(10598, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-08-28 16:55:35', '2026-08-28 16:55:35'),
(10599, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-28 16:55:44', '2026-08-28 16:55:44'),
(10600, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-28 16:55:44', '2026-08-28 16:55:44'),
(10601, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-28 16:55:44', '2026-08-28 16:55:44'),
(10602, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-28 16:55:47', '2026-08-28 16:55:47'),
(10603, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-28 17:52:57', '2026-08-28 17:52:57'),
(10604, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-28 17:52:57', '2026-08-28 17:52:57'),
(10605, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-28 17:52:58', '2026-08-28 17:52:58'),
(10606, '102.219.208.90', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-28 17:52:58', '2026-08-28 17:52:58'),
(10607, '41.90.4.219', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/setup-password?token=3b8116713c6fd90e9a20cbe3aa961e6aed452e9a71e5e3a5798c9a6d7e38fe91', 'GET', 1, '2026-08-30 05:22:44', '2026-08-30 05:22:44'),
(10608, '41.90.217.168', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g', 'GET', 1, '2026-08-30 05:22:57', '2026-08-30 05:22:57'),
(10609, '74.125.208.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g', 'GET', 1, '2026-08-30 05:23:03', '2026-08-30 05:23:03'),
(10610, '74.125.208.226', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g', 'GET', 1, '2026-08-30 05:23:03', '2026-08-30 05:23:03'),
(10611, '66.102.9.161', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g', 'GET', 1, '2026-08-30 05:23:04', '2026-08-30 05:23:04'),
(10612, '41.90.217.168', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g', 'GET', 1, '2026-08-30 05:23:52', '2026-08-30 05:23:52'),
(10613, '41.90.217.168', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g', 'GET', 1, '2026-08-30 05:23:56', '2026-08-30 05:23:56'),
(10614, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:46'),
(10615, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:46'),
(10616, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/dashboard', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:48'),
(10617, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:48'),
(10618, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:48'),
(10619, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:51'),
(10620, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:52'),
(10621, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:52'),
(10622, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:33:52'),
(10623, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:27'),
(10624, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/dashboard', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:27'),
(10625, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:27'),
(10626, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:27'),
(10627, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:28'),
(10628, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:29'),
(10629, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/tenants', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:29'),
(10630, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:29'),
(10631, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:32'),
(10632, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:32'),
(10633, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:32'),
(10634, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 3, '2026-08-30 11:40:46', '2026-08-30 11:35:32'),
(10635, '197.248.82.231', 'curl/8.21.0', '/api/health-check', 'GET', 2, '2026-08-30 11:40:46', '2026-08-30 11:40:45'),
(10636, '197.248.82.231', 'curl/8.21.0', '/api/bills?month=2026-08&page=1&per_page=5', 'GET', 1, '2026-08-30 11:40:46', '2026-08-30 11:40:46'),
(10637, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 1, '2026-08-30 11:48:08', '2026-08-30 11:48:08'),
(10638, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 11:48:08', '2026-08-30 11:48:08'),
(10639, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 11:48:09', '2026-08-30 11:48:09'),
(10640, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-30 11:48:18', '2026-08-30 11:48:18'),
(10641, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-30 11:48:21', '2026-08-30 11:48:21'),
(10642, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-30 11:48:21', '2026-08-30 11:48:21'),
(10643, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/dashboard', 'GET', 1, '2026-08-30 11:48:23', '2026-08-30 11:48:23'),
(10644, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 11:48:23', '2026-08-30 11:48:23'),
(10645, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 11:48:23', '2026-08-30 11:48:23'),
(10646, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 11:48:29', '2026-08-30 11:48:29'),
(10647, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 1, '2026-08-30 11:48:29', '2026-08-30 11:48:29'),
(10648, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 11:48:29', '2026-08-30 11:48:29'),
(10649, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 11:48:29', '2026-08-30 11:48:29'),
(10650, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 1, '2026-08-30 11:50:27', '2026-08-30 11:50:27'),
(10651, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 11:50:28', '2026-08-30 11:50:28'),
(10652, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 11:50:28', '2026-08-30 11:50:28'),
(10653, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 12:00:44', '2026-08-30 12:00:44'),
(10654, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 1, '2026-08-30 12:00:44', '2026-08-30 12:00:44'),
(10655, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 12:00:44', '2026-08-30 12:00:44'),
(10656, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/85', 'GET', 1, '2026-08-30 12:00:51', '2026-08-30 12:00:51'),
(10657, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/85', 'GET', 1, '2026-08-30 12:01:04', '2026-08-30 12:01:04'),
(10658, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/85', 'PUT', 1, '2026-08-30 12:01:12', '2026-08-30 12:01:12'),
(10659, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 1, '2026-08-30 12:01:12', '2026-08-30 12:01:12'),
(10660, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-30 12:01:24', '2026-08-30 12:01:24'),
(10661, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/auth/setup-password?token=df850d1d0ca5c1db62fd2e2e3434e937557c0385ad110c40902e35ff938adc46', 'GET', 1, '2026-08-30 18:59:06', '2026-08-30 18:59:06'),
(10662, '172.253.192.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/setup-password?token=df850d1d0ca5c1db62fd2e2e3434e937557c0385ad110c40902e35ff938adc46', 'GET', 1, '2026-08-30 18:59:10', '2026-08-30 18:59:10'),
(10663, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-30 18:59:16', '2026-08-30 18:59:16'),
(10664, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-30 18:59:16', '2026-08-30 18:59:16'),
(10665, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 18:59:18', '2026-08-30 18:59:18'),
(10666, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 18:59:18', '2026-08-30 18:59:18'),
(10667, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 18:59:18', '2026-08-30 18:59:18'),
(10668, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 18:59:18', '2026-08-30 18:59:18'),
(10669, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 18:59:19', '2026-08-30 18:59:19'),
(10670, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 18:59:28', '2026-08-30 18:59:28'),
(10671, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 18:59:28', '2026-08-30 18:59:28'),
(10672, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 18:59:28', '2026-08-30 18:59:28'),
(10673, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:02:03', '2026-08-30 19:02:03'),
(10674, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-08-30 19:06:01', '2026-08-30 19:06:01'),
(10675, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'POST', 1, '2026-08-30 19:07:42', '2026-08-30 19:07:42'),
(10676, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:07:44', '2026-08-30 19:07:44'),
(10677, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants/69', 'GET', 1, '2026-08-30 19:08:23', '2026-08-30 19:08:23'),
(10678, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:08:23', '2026-08-30 19:08:23'),
(10679, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:08:55', '2026-08-30 19:08:55'),
(10680, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:08:55', '2026-08-30 19:08:55'),
(10681, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:08:56', '2026-08-30 19:08:56'),
(10682, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:08:56', '2026-08-30 19:08:56'),
(10683, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:09:04', '2026-08-30 19:09:04'),
(10684, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-08-30 19:09:04', '2026-08-30 19:09:04'),
(10685, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/68', 'GET', 1, '2026-08-30 19:09:05', '2026-08-30 19:09:05'),
(10686, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/auth/setup-password?token=df850d1d0ca5c1db62fd2e2e3434e937557c0385ad110c40902e35ff938adc46', 'GET', 1, '2026-08-30 19:10:33', '2026-08-30 19:10:33'),
(10687, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-30 19:11:03', '2026-08-30 19:11:03'),
(10688, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-30 19:11:03', '2026-08-30 19:11:03'),
(10689, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:11:05', '2026-08-30 19:11:05'),
(10690, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:11:05', '2026-08-30 19:11:05'),
(10691, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 19:11:05', '2026-08-30 19:11:05'),
(10692, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:11:05', '2026-08-30 19:11:05'),
(10693, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:11:05', '2026-08-30 19:11:05'),
(10694, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:11:12', '2026-08-30 19:11:12'),
(10695, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:11:12', '2026-08-30 19:11:12'),
(10696, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-08-30 19:11:12', '2026-08-30 19:11:12'),
(10697, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:11:12', '2026-08-30 19:11:12'),
(10698, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:11:19', '2026-08-30 19:11:19'),
(10699, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments/tenant-finance/69', 'GET', 1, '2026-08-30 19:11:29', '2026-08-30 19:11:29'),
(10700, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'POST', 1, '2026-08-30 19:12:22', '2026-08-30 19:12:22'),
(10701, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:12:28', '2026-08-30 19:12:28'),
(10702, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:12:28', '2026-08-30 19:12:28'),
(10703, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:12:28', '2026-08-30 19:12:28'),
(10704, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 19:12:28', '2026-08-30 19:12:28'),
(10705, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:12:28', '2026-08-30 19:12:28'),
(10706, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:12:29', '2026-08-30 19:12:29'),
(10707, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:12:29', '2026-08-30 19:12:29'),
(10708, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:12:29', '2026-08-30 19:12:29'),
(10709, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 19:12:29', '2026-08-30 19:12:29'),
(10710, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:12:29', '2026-08-30 19:12:29'),
(10711, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:12:37', '2026-08-30 19:12:37'),
(10712, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:12:38', '2026-08-30 19:12:38'),
(10713, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:12:38', '2026-08-30 19:12:38'),
(10714, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:12:45', '2026-08-30 19:12:45'),
(10715, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-08-30 19:12:46', '2026-08-30 19:12:46'),
(10716, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/68', 'GET', 1, '2026-08-30 19:12:46', '2026-08-30 19:12:46'),
(10717, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants/69', 'PUT', 1, '2026-08-30 19:13:24', '2026-08-30 19:13:24'),
(10718, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:13:25', '2026-08-30 19:13:25'),
(10719, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:11', '2026-08-30 19:14:11'),
(10720, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:14:11', '2026-08-30 19:14:11'),
(10721, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:11', '2026-08-30 19:14:11'),
(10722, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:14:19', '2026-08-30 19:14:19'),
(10723, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-08-30 19:14:19', '2026-08-30 19:14:19'),
(10724, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/68', 'GET', 1, '2026-08-30 19:14:20', '2026-08-30 19:14:20'),
(10725, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:14:21', '2026-08-30 19:14:21'),
(10726, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:14:21', '2026-08-30 19:14:21'),
(10727, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:21', '2026-08-30 19:14:21'),
(10728, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 19:14:21', '2026-08-30 19:14:21'),
(10729, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:22', '2026-08-30 19:14:22'),
(10730, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:14:22', '2026-08-30 19:14:22'),
(10731, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:14:22', '2026-08-30 19:14:22'),
(10732, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:22', '2026-08-30 19:14:22'),
(10733, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 19:14:22', '2026-08-30 19:14:22'),
(10734, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:22', '2026-08-30 19:14:22'),
(10735, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:26', '2026-08-30 19:14:26'),
(10736, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:14:27', '2026-08-30 19:14:27'),
(10737, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:27', '2026-08-30 19:14:27'),
(10738, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:14:32', '2026-08-30 19:14:32'),
(10739, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-08-30 19:14:32', '2026-08-30 19:14:32'),
(10740, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/81', 'GET', 1, '2026-08-30 19:14:33', '2026-08-30 19:14:33'),
(10741, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10742, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10743, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10744, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10745, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10746, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10747, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10748, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10749, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:14:53', '2026-08-30 19:14:53'),
(10750, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:54', '2026-08-30 19:14:54'),
(10751, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:58', '2026-08-30 19:14:58'),
(10752, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:14:59', '2026-08-30 19:14:59'),
(10753, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:14:59', '2026-08-30 19:14:59'),
(10754, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:15:06', '2026-08-30 19:15:06'),
(10755, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-08-30 19:15:06', '2026-08-30 19:15:06'),
(10756, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/68', 'GET', 1, '2026-08-30 19:15:06', '2026-08-30 19:15:06'),
(10757, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants/69', 'PUT', 1, '2026-08-30 19:15:24', '2026-08-30 19:15:24'),
(10758, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:15:24', '2026-08-30 19:15:24'),
(10759, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:15:29', '2026-08-30 19:15:29'),
(10760, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:15:29', '2026-08-30 19:15:29'),
(10761, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-30 19:16:06', '2026-08-30 19:16:06'),
(10762, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-08-30 19:16:06', '2026-08-30 19:16:06'),
(10763, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/houses/68', 'GET', 1, '2026-08-30 19:16:06', '2026-08-30 19:16:06'),
(10764, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants/69', 'PUT', 1, '2026-08-30 19:16:32', '2026-08-30 19:16:32'),
(10765, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:16:32', '2026-08-30 19:16:32'),
(10766, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-30 19:16:40', '2026-08-30 19:16:40'),
(10767, '154.159.252.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/30.0 Chrome/143.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-30 19:16:40', '2026-08-30 19:16:40'),
(10768, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 1, '2026-08-31 09:00:33', '2026-08-31 09:00:33'),
(10769, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 1, '2026-08-31 09:00:33', '2026-08-31 09:00:33'),
(10770, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:00:33', '2026-08-31 09:00:33'),
(10771, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:00:43', '2026-08-31 09:00:43'),
(10772, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:00:44', '2026-08-31 09:00:44'),
(10773, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-08-31 09:00:44', '2026-08-31 09:00:44'),
(10774, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:00:44', '2026-08-31 09:00:44'),
(10775, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:01:41', '2026-08-31 09:01:41'),
(10776, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:01:41', '2026-08-31 09:01:41'),
(10777, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 1, '2026-08-31 09:01:46', '2026-08-31 09:01:46'),
(10778, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:01:46', '2026-08-31 09:01:46'),
(10779, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:01:46', '2026-08-31 09:01:46'),
(10780, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:03:28', '2026-08-31 09:03:28'),
(10781, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:03:29', '2026-08-31 09:03:29');
INSERT INTO `bot_detections` (`id`, `ip_address`, `user_agent`, `endpoint`, `method`, `attempts`, `last_seen`, `created_at`) VALUES
(10782, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-08-31 09:03:29', '2026-08-31 09:03:29'),
(10783, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:03:29', '2026-08-31 09:03:29'),
(10784, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:03:51', '2026-08-31 09:03:51'),
(10785, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 1, '2026-08-31 09:03:51', '2026-08-31 09:03:51'),
(10786, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 1, '2026-08-31 09:03:51', '2026-08-31 09:03:51'),
(10787, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:03:51', '2026-08-31 09:03:51'),
(10788, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 1, '2026-08-31 09:03:55', '2026-08-31 09:03:55'),
(10789, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 1, '2026-08-31 09:03:55', '2026-08-31 09:03:55'),
(10790, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:03:55', '2026-08-31 09:03:55'),
(10791, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:04:08', '2026-08-31 09:04:08'),
(10792, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 1, '2026-08-31 09:04:08', '2026-08-31 09:04:08'),
(10793, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:04:08', '2026-08-31 09:04:08'),
(10794, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:04:08', '2026-08-31 09:04:08'),
(10795, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:04:46', '2026-08-31 09:04:46'),
(10796, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:04:47', '2026-08-31 09:04:47'),
(10797, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:04:47', '2026-08-31 09:04:47'),
(10798, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:04:58', '2026-08-31 09:04:58'),
(10799, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:04:58', '2026-08-31 09:04:58'),
(10800, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-08-31 09:04:59', '2026-08-31 09:04:59'),
(10801, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:04:59', '2026-08-31 09:04:59'),
(10802, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:05:11', '2026-08-31 09:05:11'),
(10803, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-08-31 09:05:12', '2026-08-31 09:05:12'),
(10804, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-08-31 09:05:12', '2026-08-31 09:05:12'),
(10805, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:05:12', '2026-08-31 09:05:12'),
(10806, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 09:05:16', '2026-08-31 09:05:16'),
(10807, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:05:20', '2026-08-31 09:05:20'),
(10808, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:05:20', '2026-08-31 09:05:20'),
(10809, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 1, '2026-08-31 09:05:22', '2026-08-31 09:05:22'),
(10810, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:05:22', '2026-08-31 09:05:22'),
(10811, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:05:23', '2026-08-31 09:05:23'),
(10812, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:05:36', '2026-08-31 09:05:36'),
(10813, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=25', 'GET', 1, '2026-08-31 09:05:36', '2026-08-31 09:05:36'),
(10814, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:05:37', '2026-08-31 09:05:37'),
(10815, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:05:37', '2026-08-31 09:05:37'),
(10816, '41.80.116.10', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 09:05:46', '2026-08-31 09:05:46'),
(10817, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:06:59', '2026-08-31 09:06:59'),
(10818, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:07:00', '2026-08-31 09:07:00'),
(10819, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:07:00', '2026-08-31 09:07:00'),
(10820, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants/54', 'GET', 1, '2026-08-31 09:07:18', '2026-08-31 09:07:18'),
(10821, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:07:18', '2026-08-31 09:07:18'),
(10822, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:07:26', '2026-08-31 09:07:26'),
(10823, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:07:26', '2026-08-31 09:07:26'),
(10824, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:07:36', '2026-08-31 09:07:36'),
(10825, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 1, '2026-08-31 09:07:36', '2026-08-31 09:07:36'),
(10826, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:07:36', '2026-08-31 09:07:36'),
(10827, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 09:07:45', '2026-08-31 09:07:45'),
(10828, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:07:50', '2026-08-31 09:07:50'),
(10829, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:07:50', '2026-08-31 09:07:50'),
(10830, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:07:57', '2026-08-31 09:07:57'),
(10831, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:07:57', '2026-08-31 09:07:57'),
(10832, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-08-31 09:07:57', '2026-08-31 09:07:57'),
(10833, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:07:57', '2026-08-31 09:07:57'),
(10834, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:08:05', '2026-08-31 09:08:05'),
(10835, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:08:05', '2026-08-31 09:08:05'),
(10836, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses/52', 'GET', 1, '2026-08-31 09:08:12', '2026-08-31 09:08:12'),
(10837, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:08:12', '2026-08-31 09:08:12'),
(10838, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:08:18', '2026-08-31 09:08:18'),
(10839, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:08:18', '2026-08-31 09:08:18'),
(10840, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:08:25', '2026-08-31 09:08:25'),
(10841, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:08:25', '2026-08-31 09:08:25'),
(10842, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:08:25', '2026-08-31 09:08:25'),
(10843, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:08:25', '2026-08-31 09:08:25'),
(10844, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:08:26', '2026-08-31 09:08:26'),
(10845, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 09:08:29', '2026-08-31 09:08:29'),
(10846, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:08:51', '2026-08-31 09:08:51'),
(10847, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:08:51', '2026-08-31 09:08:51'),
(10848, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:08:53', '2026-08-31 09:08:53'),
(10849, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:08:53', '2026-08-31 09:08:53'),
(10850, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:08:53', '2026-08-31 09:08:53'),
(10851, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:08:53', '2026-08-31 09:08:53'),
(10852, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:08:53', '2026-08-31 09:08:53'),
(10853, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:08:56', '2026-08-31 09:08:56'),
(10854, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:08:57', '2026-08-31 09:08:57'),
(10855, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:08:57', '2026-08-31 09:08:57'),
(10856, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 09:09:00', '2026-08-31 09:09:00'),
(10857, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:09:05', '2026-08-31 09:09:05'),
(10858, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 09:09:05', '2026-08-31 09:09:05'),
(10859, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:09:07', '2026-08-31 09:09:07'),
(10860, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-08-31 09:09:07', '2026-08-31 09:09:07'),
(10861, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:07', '2026-08-31 09:09:07'),
(10862, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?tenant_id=50', 'GET', 1, '2026-08-31 09:09:08', '2026-08-31 09:09:08'),
(10863, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:09:08', '2026-08-31 09:09:08'),
(10864, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:08', '2026-08-31 09:09:08'),
(10865, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/consent-data-protection', 'POST', 1, '2026-08-31 09:09:12', '2026-08-31 09:09:12'),
(10866, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:09:13', '2026-08-31 09:09:13'),
(10867, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-08-31 09:09:13', '2026-08-31 09:09:13'),
(10868, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:13', '2026-08-31 09:09:13'),
(10869, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?tenant_id=50', 'GET', 1, '2026-08-31 09:09:14', '2026-08-31 09:09:14'),
(10870, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:09:14', '2026-08-31 09:09:14'),
(10871, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:14', '2026-08-31 09:09:14'),
(10872, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:16', '2026-08-31 09:09:16'),
(10873, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:09:17', '2026-08-31 09:09:17'),
(10874, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-08-31 09:09:17', '2026-08-31 09:09:17'),
(10875, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:17', '2026-08-31 09:09:17'),
(10876, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:09:17', '2026-08-31 09:09:17'),
(10877, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:35', '2026-08-31 09:09:35'),
(10878, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=25', 'GET', 1, '2026-08-31 09:09:35', '2026-08-31 09:09:35'),
(10879, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 09:09:35', '2026-08-31 09:09:35'),
(10880, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:35', '2026-08-31 09:09:35'),
(10881, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:09:35', '2026-08-31 09:09:35'),
(10882, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/88/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwidGVuYW50X2lkIjo1MCwiZW1haWwiOiJkdW5jYW5rYXJlbmp1NzUwQGdtYWlsLmNvbSIsInJvbGUiOiJ0ZW5hbnQiLCJuYW1lIjoiRHVuY2FuIEthcmVuanUgR2F0aG9nbyIsImlhdCI6MTc4ODE2NzM0NSwiZXhwIjoxNzg4NzcyMTQ1fQ.qgHYpFaVHsHwIaYfvGfS8nEOPhTTNyYYJ5lO5FnPonA', 'GET', 1, '2026-08-31 09:09:38', '2026-08-31 09:09:38'),
(10883, '74.125.208.227', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/88/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwidGVuYW50X2lkIjo1MCwiZW1haWwiOiJkdW5jYW5rYXJlbmp1NzUwQGdtYWlsLmNvbSIsInJvbGUiOiJ0ZW5hbnQiLCJuYW1lIjoiRHVuY2FuIEthcmVuanUgR2F0aG9nbyIsImlhdCI6MTc4ODE2NzM0NSwiZXhwIjoxNzg4NzcyMTQ1fQ.qgHYpFaVHsHwIaYfvGfS8nEOPhTTNyYYJ5lO5FnPonA', 'GET', 1, '2026-08-31 09:09:40', '2026-08-31 09:09:40'),
(10884, '192.178.11.3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/88/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwidGVuYW50X2lkIjo1MCwiZW1haWwiOiJkdW5jYW5rYXJlbmp1NzUwQGdtYWlsLmNvbSIsInJvbGUiOiJ0ZW5hbnQiLCJuYW1lIjoiRHVuY2FuIEthcmVuanUgR2F0aG9nbyIsImlhdCI6MTc4ODE2NzM0NSwiZXhwIjoxNzg4NzcyMTQ1fQ.qgHYpFaVHsHwIaYfvGfS8nEOPhTTNyYYJ5lO5FnPonA', 'GET', 1, '2026-08-31 09:09:40', '2026-08-31 09:09:40'),
(10885, '66.102.9.162', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/88/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwidGVuYW50X2lkIjo1MCwiZW1haWwiOiJkdW5jYW5rYXJlbmp1NzUwQGdtYWlsLmNvbSIsInJvbGUiOiJ0ZW5hbnQiLCJuYW1lIjoiRHVuY2FuIEthcmVuanUgR2F0aG9nbyIsImlhdCI6MTc4ODE2NzM0NSwiZXhwIjoxNzg4NzcyMTQ1fQ.qgHYpFaVHsHwIaYfvGfS8nEOPhTTNyYYJ5lO5FnPonA', 'GET', 1, '2026-08-31 09:09:40', '2026-08-31 09:09:40'),
(10886, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:09:42', '2026-08-31 09:09:42'),
(10887, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-08-31 09:09:42', '2026-08-31 09:09:42'),
(10888, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:42', '2026-08-31 09:09:42'),
(10889, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 09:09:42', '2026-08-31 09:09:42'),
(10890, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-08-31 09:09:42', '2026-08-31 09:09:42'),
(10891, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 09:09:42', '2026-08-31 09:09:42'),
(10892, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 09:09:42', '2026-08-31 09:09:42'),
(10893, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 12:19:01', '2026-08-31 12:19:01'),
(10894, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-08-31 12:19:01', '2026-08-31 12:19:01'),
(10895, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 12:19:01', '2026-08-31 12:19:01'),
(10896, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 12:19:01', '2026-08-31 12:19:01'),
(10897, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-08-31 12:19:01', '2026-08-31 12:19:01'),
(10898, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 12:19:02', '2026-08-31 12:19:02'),
(10899, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 12:19:02', '2026-08-31 12:19:02'),
(10900, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 14:35:10', '2026-08-31 14:35:10'),
(10901, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 14:35:10', '2026-08-31 14:35:10'),
(10902, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 14:35:13', '2026-08-31 14:35:13'),
(10903, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 14:35:13', '2026-08-31 14:35:13'),
(10904, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 14:35:13', '2026-08-31 14:35:13'),
(10905, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 14:35:13', '2026-08-31 14:35:13'),
(10906, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 14:35:13', '2026-08-31 14:35:13'),
(10907, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 14:35:17', '2026-08-31 14:35:17'),
(10908, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 14:35:21', '2026-08-31 14:35:21'),
(10909, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 14:35:21', '2026-08-31 14:35:21'),
(10910, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 14:35:23', '2026-08-31 14:35:23'),
(10911, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 14:35:23', '2026-08-31 14:35:23'),
(10912, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 14:35:23', '2026-08-31 14:35:23'),
(10913, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 14:35:23', '2026-08-31 14:35:23'),
(10914, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 14:35:23', '2026-08-31 14:35:23'),
(10915, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 14:35:28', '2026-08-31 14:35:28'),
(10916, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 14:35:33', '2026-08-31 14:35:33'),
(10917, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 14:35:33', '2026-08-31 14:35:33'),
(10918, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 14:35:36', '2026-08-31 14:35:36'),
(10919, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-08-31 14:35:36', '2026-08-31 14:35:36'),
(10920, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 14:35:36', '2026-08-31 14:35:36'),
(10921, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments?tenant_id=50', 'GET', 1, '2026-08-31 14:35:36', '2026-08-31 14:35:36'),
(10922, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 14:35:36', '2026-08-31 14:35:36'),
(10923, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 14:35:36', '2026-08-31 14:35:36'),
(10924, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 14:35:41', '2026-08-31 14:35:41'),
(10925, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 15:00:18', '2026-08-31 15:00:18'),
(10926, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 15:00:18', '2026-08-31 15:00:18'),
(10927, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/tenants', 'GET', 1, '2026-08-31 15:00:21', '2026-08-31 15:00:21'),
(10928, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:00:21', '2026-08-31 15:00:21'),
(10929, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-08-31 15:00:21', '2026-08-31 15:00:21'),
(10930, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments?tenant_id=50', 'GET', 1, '2026-08-31 15:00:21', '2026-08-31 15:00:21'),
(10931, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 15:00:21', '2026-08-31 15:00:21'),
(10932, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:00:22', '2026-08-31 15:00:22'),
(10933, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:11:38', '2026-08-31 15:11:38'),
(10934, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 15:11:39', '2026-08-31 15:11:39'),
(10935, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/documents', 'GET', 1, '2026-08-31 15:11:39', '2026-08-31 15:11:39'),
(10936, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:11:39', '2026-08-31 15:11:39'),
(10937, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 15:11:40', '2026-08-31 15:11:40'),
(10938, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-08-31 15:11:40', '2026-08-31 15:11:40'),
(10939, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 15:11:45', '2026-08-31 15:11:45'),
(10940, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-08-31 15:11:45', '2026-08-31 15:11:45'),
(10941, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-08-31 15:11:47', '2026-08-31 15:11:47'),
(10942, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/dashboard', 'GET', 1, '2026-08-31 15:11:47', '2026-08-31 15:11:47'),
(10943, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:11:47', '2026-08-31 15:11:47'),
(10944, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:11:53', '2026-08-31 15:11:53'),
(10945, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 15:11:53', '2026-08-31 15:11:53'),
(10946, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:11:53', '2026-08-31 15:11:53'),
(10947, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/houses?page=1&per_page=50', 'GET', 1, '2026-08-31 15:11:53', '2026-08-31 15:11:53'),
(10948, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:11:59', '2026-08-31 15:11:59'),
(10949, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 1, '2026-08-31 15:11:59', '2026-08-31 15:11:59'),
(10950, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-08-31 15:11:59', '2026-08-31 15:11:59'),
(10951, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-08-31 15:11:59', '2026-08-31 15:11:59'),
(10952, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/90/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgxODkxMDUsImV4cCI6MTc4ODc5MzkwNX0.KDlUScnNinuU8sL2S_z4gDjGxhIklhbDjHwhkoyOrPc', 'GET', 1, '2026-08-31 15:12:02', '2026-08-31 15:12:02'),
(10953, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/80', 'GET', 1, '2026-08-31 15:12:08', '2026-08-31 15:12:08'),
(10954, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 1, '2026-09-01 05:23:50', '2026-09-01 05:23:50'),
(10955, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 1, '2026-09-01 05:23:50', '2026-09-01 05:23:50'),
(10956, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:23:50', '2026-09-01 05:23:50'),
(10957, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:24:11', '2026-09-01 05:24:11'),
(10958, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 05:24:11', '2026-09-01 05:24:11'),
(10959, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 05:24:11', '2026-09-01 05:24:11'),
(10960, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:24:11', '2026-09-01 05:24:11'),
(10961, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 1, '2026-09-01 05:42:42', '2026-09-01 05:42:42'),
(10962, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 1, '2026-09-01 05:42:42', '2026-09-01 05:42:42'),
(10963, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:42:42', '2026-09-01 05:42:42'),
(10964, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:42:49', '2026-09-01 05:42:49'),
(10965, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:42:49', '2026-09-01 05:42:49'),
(10966, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 05:42:49', '2026-09-01 05:42:49'),
(10967, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 05:42:49', '2026-09-01 05:42:49'),
(10968, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:42:49', '2026-09-01 05:42:49'),
(10969, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:43:05', '2026-09-01 05:43:05'),
(10970, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 05:43:06', '2026-09-01 05:43:06'),
(10971, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:43:06', '2026-09-01 05:43:06'),
(10972, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 05:52:59', '2026-09-01 05:52:59'),
(10973, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-09-01 05:56:14', '2026-09-01 05:56:14'),
(10974, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:57:17', '2026-09-01 05:57:17'),
(10975, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 05:57:17', '2026-09-01 05:57:17'),
(10976, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 05:57:17', '2026-09-01 05:57:17'),
(10977, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:57:18', '2026-09-01 05:57:18'),
(10978, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses/62', 'PUT', 1, '2026-09-01 05:57:34', '2026-09-01 05:57:34'),
(10979, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 05:57:35', '2026-09-01 05:57:35'),
(10980, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:57:43', '2026-09-01 05:57:43'),
(10981, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 05:57:43', '2026-09-01 05:57:43'),
(10982, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 05:57:43', '2026-09-01 05:57:43'),
(10983, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 05:57:47', '2026-09-01 05:57:47'),
(10984, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-09-01 06:00:40', '2026-09-01 06:00:40'),
(10985, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'POST', 1, '2026-09-01 06:01:29', '2026-09-01 06:01:29'),
(10986, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 06:01:30', '2026-09-01 06:01:30'),
(10987, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 06:01:45', '2026-09-01 06:01:45'),
(10988, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:01:45', '2026-09-01 06:01:45'),
(10989, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:01:50', '2026-09-01 06:01:50'),
(10990, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 06:01:50', '2026-09-01 06:01:50'),
(10991, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 06:01:50', '2026-09-01 06:01:50'),
(10992, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:01:51', '2026-09-01 06:01:51'),
(10993, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:02:06', '2026-09-01 06:02:06'),
(10994, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 06:02:07', '2026-09-01 06:02:07'),
(10995, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 06:02:07', '2026-09-01 06:02:07'),
(10996, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:02:07', '2026-09-01 06:02:07'),
(10997, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 06:02:16', '2026-09-01 06:02:16'),
(10998, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/99', 'GET', 1, '2026-09-01 06:03:12', '2026-09-01 06:03:12'),
(10999, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/auth/setup-password?token=f0446746ed65e08229b65896b8deb5328e4a9d38c126e31bdb6ff657b30fc4b4', 'GET', 1, '2026-09-01 06:12:25', '2026-09-01 06:12:25'),
(11000, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/auth/setup-password', 'POST', 1, '2026-09-01 06:13:33', '2026-09-01 06:13:33'),
(11001, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/auth/login', 'POST', 1, '2026-09-01 06:14:20', '2026-09-01 06:14:20'),
(11002, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/auth/login', 'POST', 1, '2026-09-01 06:14:20', '2026-09-01 06:14:20'),
(11003, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/tenants', 'GET', 1, '2026-09-01 06:14:22', '2026-09-01 06:14:22');
INSERT INTO `bot_detections` (`id`, `ip_address`, `user_agent`, `endpoint`, `method`, `attempts`, `last_seen`, `created_at`) VALUES
(11004, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/tenants/property-contact', 'GET', 1, '2026-09-01 06:14:22', '2026-09-01 06:14:22'),
(11005, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/complaints', 'GET', 1, '2026-09-01 06:14:23', '2026-09-01 06:14:23'),
(11006, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/payments?tenant_id=70', 'GET', 1, '2026-09-01 06:14:23', '2026-09-01 06:14:23'),
(11007, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/payments', 'GET', 1, '2026-09-01 06:14:23', '2026-09-01 06:14:23'),
(11008, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/complaints', 'GET', 1, '2026-09-01 06:14:23', '2026-09-01 06:14:23'),
(11009, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 06:14:43', '2026-09-01 06:14:43'),
(11010, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 06:14:43', '2026-09-01 06:14:43'),
(11011, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/tenants/consent-data-protection', 'POST', 1, '2026-09-01 06:14:43', '2026-09-01 06:14:43'),
(11012, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 06:14:44', '2026-09-01 06:14:44'),
(11013, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 06:14:44', '2026-09-01 06:14:44'),
(11014, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:14:44', '2026-09-01 06:14:44'),
(11015, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/tenants', 'GET', 1, '2026-09-01 06:14:45', '2026-09-01 06:14:45'),
(11016, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/tenants/property-contact', 'GET', 1, '2026-09-01 06:14:45', '2026-09-01 06:14:45'),
(11017, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/complaints', 'GET', 1, '2026-09-01 06:14:45', '2026-09-01 06:14:45'),
(11018, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/payments?tenant_id=70', 'GET', 1, '2026-09-01 06:14:45', '2026-09-01 06:14:45'),
(11019, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/payments', 'GET', 1, '2026-09-01 06:14:45', '2026-09-01 06:14:45'),
(11020, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/complaints', 'GET', 1, '2026-09-01 06:14:46', '2026-09-01 06:14:46'),
(11021, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/tenants/property-contact', 'GET', 1, '2026-09-01 06:15:06', '2026-09-01 06:15:06'),
(11022, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/tenants', 'GET', 1, '2026-09-01 06:15:06', '2026-09-01 06:15:06'),
(11023, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/complaints', 'GET', 1, '2026-09-01 06:15:06', '2026-09-01 06:15:06'),
(11024, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/payments?tenant_id=70', 'GET', 1, '2026-09-01 06:15:06', '2026-09-01 06:15:06'),
(11025, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/payments', 'GET', 1, '2026-09-01 06:15:06', '2026-09-01 06:15:06'),
(11026, '105.160.94.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/151.0.7922.57 Mobile/15E148 Safari/604.1', '/api/complaints', 'GET', 1, '2026-09-01 06:15:06', '2026-09-01 06:15:06'),
(11027, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/setup-password?token=ff10d756556189061a87b08cd1ba02a95dc45ac9da682536415bf3cb5aa08dc3', 'GET', 1, '2026-09-01 06:31:05', '2026-09-01 06:31:05'),
(11028, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/resend-setup-email', 'POST', 1, '2026-09-01 06:31:09', '2026-09-01 06:31:09'),
(11029, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 06:31:21', '2026-09-01 06:31:21'),
(11030, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 06:31:21', '2026-09-01 06:31:21'),
(11031, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 06:31:23', '2026-09-01 06:31:23'),
(11032, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-09-01 06:31:23', '2026-09-01 06:31:23'),
(11033, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:31:23', '2026-09-01 06:31:23'),
(11034, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?tenant_id=65', 'GET', 1, '2026-09-01 06:31:24', '2026-09-01 06:31:24'),
(11035, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 06:31:24', '2026-09-01 06:31:24'),
(11036, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:31:24', '2026-09-01 06:31:24'),
(11037, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/94/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NSwiaWF0IjoxNzg3NDEyMjgyLCJleHAiOjE3ODgwMTcwODJ9.aoI6Zh7_mrayY6F5kmA8Rp3tAcu72reUMhE8gMmSutY', 'GET', 1, '2026-09-01 06:33:19', '2026-09-01 06:33:19'),
(11038, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/setup-password?token=ff10d756556189061a87b08cd1ba02a95dc45ac9da682536415bf3cb5aa08dc3', 'GET', 1, '2026-09-01 06:33:31', '2026-09-01 06:33:31'),
(11039, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/resend-setup-email', 'POST', 1, '2026-09-01 06:33:34', '2026-09-01 06:33:34'),
(11040, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 06:33:40', '2026-09-01 06:33:40'),
(11041, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 06:33:40', '2026-09-01 06:33:40'),
(11042, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 06:33:43', '2026-09-01 06:33:43'),
(11043, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-09-01 06:33:43', '2026-09-01 06:33:43'),
(11044, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:33:43', '2026-09-01 06:33:43'),
(11045, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?tenant_id=65', 'GET', 1, '2026-09-01 06:33:43', '2026-09-01 06:33:43'),
(11046, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 06:33:43', '2026-09-01 06:33:43'),
(11047, '105.161.217.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:33:43', '2026-09-01 06:33:43'),
(11048, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 1, '2026-09-01 06:44:58', '2026-09-01 06:44:58'),
(11049, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 1, '2026-09-01 06:44:58', '2026-09-01 06:44:58'),
(11050, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:44:58', '2026-09-01 06:44:58'),
(11051, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:45:04', '2026-09-01 06:45:04'),
(11052, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 06:45:05', '2026-09-01 06:45:05'),
(11053, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:45:05', '2026-09-01 06:45:05'),
(11054, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants/65', 'GET', 1, '2026-09-01 06:45:18', '2026-09-01 06:45:18'),
(11055, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 06:45:19', '2026-09-01 06:45:19'),
(11056, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 07:25:16', '2026-09-01 07:25:16'),
(11057, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 07:25:16', '2026-09-01 07:25:16'),
(11058, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 07:25:16', '2026-09-01 07:25:16'),
(11059, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 07:25:16', '2026-09-01 07:25:16'),
(11060, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 07:25:26', '2026-09-01 07:25:26'),
(11061, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:08:11', '2026-09-01 08:08:11'),
(11062, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:08:11', '2026-09-01 08:08:11'),
(11063, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:08:11', '2026-09-01 08:08:11'),
(11064, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 08:08:13', '2026-09-01 08:08:13'),
(11065, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:33:27', '2026-09-01 08:33:27'),
(11066, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:33:27', '2026-09-01 08:33:27'),
(11067, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:33:27', '2026-09-01 08:33:27'),
(11068, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 08:33:28', '2026-09-01 08:33:28'),
(11069, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 08:33:39', '2026-09-01 08:33:39'),
(11070, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-09-01 08:33:47', '2026-09-01 08:33:47'),
(11071, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties/available', 'GET', 1, '2026-09-01 08:37:48', '2026-09-01 08:37:48'),
(11072, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/caretakers?page=1&per_page=25', 'GET', 1, '2026-09-01 08:37:48', '2026-09-01 08:37:48'),
(11073, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:37:48', '2026-09-01 08:37:48'),
(11074, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 08:37:56', '2026-09-01 08:37:56'),
(11075, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 08:37:56', '2026-09-01 08:37:56'),
(11076, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:37:56', '2026-09-01 08:37:56'),
(11077, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 08:37:57', '2026-09-01 08:37:57'),
(11078, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 08:37:57', '2026-09-01 08:37:57'),
(11079, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:37:57', '2026-09-01 08:37:57'),
(11080, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 08:37:57', '2026-09-01 08:37:57'),
(11081, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-09-01 08:38:00', '2026-09-01 08:38:00'),
(11082, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 08:38:04', '2026-09-01 08:38:04'),
(11083, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 08:38:04', '2026-09-01 08:38:04'),
(11084, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 1, '2026-09-01 08:38:08', '2026-09-01 08:38:08'),
(11085, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 08:38:08', '2026-09-01 08:38:08'),
(11086, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:08', '2026-09-01 08:38:08'),
(11087, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:08', '2026-09-01 08:38:08'),
(11088, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:38:08', '2026-09-01 08:38:08'),
(11089, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:38:08', '2026-09-01 08:38:08'),
(11090, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:09', '2026-09-01 08:38:09'),
(11091, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 08:38:09', '2026-09-01 08:38:09'),
(11092, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:12', '2026-09-01 08:38:12'),
(11093, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:38:12', '2026-09-01 08:38:12'),
(11094, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:38:12', '2026-09-01 08:38:12'),
(11095, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:12', '2026-09-01 08:38:12'),
(11096, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:38:13', '2026-09-01 08:38:13'),
(11097, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:38:16', '2026-09-01 08:38:16'),
(11098, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:38:16', '2026-09-01 08:38:16'),
(11099, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:16', '2026-09-01 08:38:16'),
(11100, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 08:38:17', '2026-09-01 08:38:17'),
(11101, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 08:38:17', '2026-09-01 08:38:17'),
(11102, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 08:38:21', '2026-09-01 08:38:21'),
(11103, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:38:48', '2026-09-01 08:38:48'),
(11104, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:38:48', '2026-09-01 08:38:48'),
(11105, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:48', '2026-09-01 08:38:48'),
(11106, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 08:38:48', '2026-09-01 08:38:48'),
(11107, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 08:38:52', '2026-09-01 08:38:52'),
(11108, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 08:38:57', '2026-09-01 08:38:57'),
(11109, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 1, '2026-09-01 08:38:57', '2026-09-01 08:38:57'),
(11110, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:57', '2026-09-01 08:38:57'),
(11111, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 1, '2026-09-01 08:38:58', '2026-09-01 08:38:58'),
(11112, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 08:38:58', '2026-09-01 08:38:58'),
(11113, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:38:58', '2026-09-01 08:38:58'),
(11114, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:39:01', '2026-09-01 08:39:01'),
(11115, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:39:01', '2026-09-01 08:39:01'),
(11116, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:39:01', '2026-09-01 08:39:01'),
(11117, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:39:01', '2026-09-01 08:39:01'),
(11118, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:39:01', '2026-09-01 08:39:01'),
(11119, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:39:16', '2026-09-01 08:39:16'),
(11120, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:39:16', '2026-09-01 08:39:16'),
(11121, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:39:16', '2026-09-01 08:39:16'),
(11122, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:39:16', '2026-09-01 08:39:16'),
(11123, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:39:16', '2026-09-01 08:39:16'),
(11124, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 08:39:17', '2026-09-01 08:39:17'),
(11125, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:40:04', '2026-09-01 08:40:04'),
(11126, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 08:40:04', '2026-09-01 08:40:04'),
(11127, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:40:05', '2026-09-01 08:40:05'),
(11128, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:40:11', '2026-09-01 08:40:11'),
(11129, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:40:11', '2026-09-01 08:40:11'),
(11130, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 08:40:11', '2026-09-01 08:40:11'),
(11131, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:40:11', '2026-09-01 08:40:11'),
(11132, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:40:23', '2026-09-01 08:40:23'),
(11133, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:40:23', '2026-09-01 08:40:23'),
(11134, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:40:23', '2026-09-01 08:40:23'),
(11135, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:40:23', '2026-09-01 08:40:23'),
(11136, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:40:23', '2026-09-01 08:40:23'),
(11137, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 08:40:27', '2026-09-01 08:40:27'),
(11138, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/84', 'GET', 1, '2026-09-01 08:40:40', '2026-09-01 08:40:40'),
(11139, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/84', 'GET', 1, '2026-09-01 08:40:47', '2026-09-01 08:40:47'),
(11140, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/84', 'GET', 1, '2026-09-01 08:40:48', '2026-09-01 08:40:48'),
(11141, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/84', 'PUT', 1, '2026-09-01 08:40:58', '2026-09-01 08:40:58'),
(11142, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:40:58', '2026-09-01 08:40:58'),
(11143, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:40:59', '2026-09-01 08:40:59'),
(11144, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:40:59', '2026-09-01 08:40:59'),
(11145, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 08:41:05', '2026-09-01 08:41:05'),
(11146, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/84', 'GET', 1, '2026-09-01 08:41:19', '2026-09-01 08:41:19'),
(11147, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/84/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNTE4ODQsImV4cCI6MTc4ODg1NjY4NH0.pq9VL8rcFAxbdk4dKPUTSxsixFIeaspuvQJVuW49Cog', 'GET', 1, '2026-09-01 08:41:22', '2026-09-01 08:41:22'),
(11148, '142.250.32.98', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/84/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNTE4ODQsImV4cCI6MTc4ODg1NjY4NH0.pq9VL8rcFAxbdk4dKPUTSxsixFIeaspuvQJVuW49Cog', 'GET', 1, '2026-09-01 08:41:24', '2026-09-01 08:41:24'),
(11149, '142.250.32.99', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/84/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNTE4ODQsImV4cCI6MTc4ODg1NjY4NH0.pq9VL8rcFAxbdk4dKPUTSxsixFIeaspuvQJVuW49Cog', 'GET', 1, '2026-09-01 08:41:25', '2026-09-01 08:41:25'),
(11150, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:42:00', '2026-09-01 08:42:00'),
(11151, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:42:00', '2026-09-01 08:42:00'),
(11152, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:42:01', '2026-09-01 08:42:01'),
(11153, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 08:42:01', '2026-09-01 08:42:01'),
(11154, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 08:42:06', '2026-09-01 08:42:06'),
(11155, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/78', 'GET', 1, '2026-09-01 08:43:08', '2026-09-01 08:43:08'),
(11156, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/78', 'PUT', 1, '2026-09-01 08:43:33', '2026-09-01 08:43:33'),
(11157, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:43:33', '2026-09-01 08:43:33'),
(11158, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:43:34', '2026-09-01 08:43:34'),
(11159, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:43:34', '2026-09-01 08:43:34'),
(11160, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 08:43:37', '2026-09-01 08:43:37'),
(11161, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/78', 'GET', 1, '2026-09-01 08:43:51', '2026-09-01 08:43:51'),
(11162, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/78', 'PUT', 1, '2026-09-01 08:44:07', '2026-09-01 08:44:07'),
(11163, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:44:07', '2026-09-01 08:44:07'),
(11164, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:44:08', '2026-09-01 08:44:08'),
(11165, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:44:08', '2026-09-01 08:44:08'),
(11166, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:44:11', '2026-09-01 08:44:11'),
(11167, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:44:11', '2026-09-01 08:44:11'),
(11168, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:44:11', '2026-09-01 08:44:11'),
(11169, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 08:44:11', '2026-09-01 08:44:11'),
(11170, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 08:44:12', '2026-09-01 08:44:12'),
(11171, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 08:44:15', '2026-09-01 08:44:15'),
(11172, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/95', 'GET', 1, '2026-09-01 08:44:48', '2026-09-01 08:44:48'),
(11173, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:45:07', '2026-09-01 08:45:07'),
(11174, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:45:07', '2026-09-01 08:45:07'),
(11175, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:45:07', '2026-09-01 08:45:07'),
(11176, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 08:45:07', '2026-09-01 08:45:07'),
(11177, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 08:45:08', '2026-09-01 08:45:08'),
(11178, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:45:19', '2026-09-01 08:45:19'),
(11179, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 08:45:20', '2026-09-01 08:45:20'),
(11180, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 08:45:20', '2026-09-01 08:45:20'),
(11181, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:45:20', '2026-09-01 08:45:20'),
(11182, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 08:45:28', '2026-09-01 08:45:28'),
(11183, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:45:53', '2026-09-01 08:45:53'),
(11184, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 08:45:53', '2026-09-01 08:45:53'),
(11185, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:45:53', '2026-09-01 08:45:53'),
(11186, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 08:45:53', '2026-09-01 08:45:53'),
(11187, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=Mbai', 'GET', 1, '2026-09-01 08:46:00', '2026-09-01 08:46:00'),
(11188, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:46:11', '2026-09-01 08:46:11'),
(11189, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:46:11', '2026-09-01 08:46:11'),
(11190, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:46:11', '2026-09-01 08:46:11'),
(11191, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:46:11', '2026-09-01 08:46:11'),
(11192, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:46:11', '2026-09-01 08:46:11'),
(11193, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 08:46:14', '2026-09-01 08:46:14'),
(11194, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:53:48', '2026-09-01 08:53:48'),
(11195, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 08:53:48', '2026-09-01 08:53:48'),
(11196, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 08:53:48', '2026-09-01 08:53:48'),
(11197, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:53:48', '2026-09-01 08:53:48'),
(11198, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 08:54:02', '2026-09-01 08:54:02'),
(11199, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 08:54:02', '2026-09-01 08:54:02'),
(11200, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:54:02', '2026-09-01 08:54:02'),
(11201, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:54:05', '2026-09-01 08:54:05'),
(11202, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 08:54:05', '2026-09-01 08:54:05'),
(11203, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 08:54:05', '2026-09-01 08:54:05'),
(11204, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:54:05', '2026-09-01 08:54:05'),
(11205, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:54:07', '2026-09-01 08:54:07'),
(11206, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 08:54:13', '2026-09-01 08:54:13'),
(11207, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/70', 'GET', 1, '2026-09-01 08:54:29', '2026-09-01 08:54:29'),
(11208, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months?property_id=34', 'GET', 1, '2026-09-01 08:55:02', '2026-09-01 08:55:02'),
(11209, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?property_id=34&month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 08:55:02', '2026-09-01 08:55:02'),
(11210, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 08:55:08', '2026-09-01 08:55:08'),
(11211, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 08:55:08', '2026-09-01 08:55:08'),
(11212, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:55:08', '2026-09-01 08:55:08'),
(11213, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 08:55:08', '2026-09-01 08:55:08'),
(11214, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 08:55:08', '2026-09-01 08:55:08'),
(11215, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:55:08', '2026-09-01 08:55:08'),
(11216, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:55:10', '2026-09-01 08:55:10'),
(11217, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 08:55:10', '2026-09-01 08:55:10');
INSERT INTO `bot_detections` (`id`, `ip_address`, `user_agent`, `endpoint`, `method`, `attempts`, `last_seen`, `created_at`) VALUES
(11218, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 08:55:10', '2026-09-01 08:55:10'),
(11219, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 08:55:10', '2026-09-01 08:55:10'),
(11220, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/589', 'GET', 1, '2026-09-01 08:55:16', '2026-09-01 08:55:16'),
(11221, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 09:08:48', '2026-09-01 09:08:48'),
(11222, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 09:08:48', '2026-09-01 09:08:48'),
(11223, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:08:48', '2026-09-01 09:08:48'),
(11224, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:08:51', '2026-09-01 09:08:51'),
(11225, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:08:51', '2026-09-01 09:08:51'),
(11226, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:08:51', '2026-09-01 09:08:51'),
(11227, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:08:52', '2026-09-01 09:08:52'),
(11228, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:08:52', '2026-09-01 09:08:52'),
(11229, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:08:55', '2026-09-01 09:08:55'),
(11230, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:16:43', '2026-09-01 09:16:43'),
(11231, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:16:43', '2026-09-01 09:16:43'),
(11232, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:16:43', '2026-09-01 09:16:43'),
(11233, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:16:44', '2026-09-01 09:16:44'),
(11234, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:16:44', '2026-09-01 09:16:44'),
(11235, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:16:44', '2026-09-01 09:16:44'),
(11236, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:16:44', '2026-09-01 09:16:44'),
(11237, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:16:46', '2026-09-01 09:16:46'),
(11238, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:16:53', '2026-09-01 09:16:53'),
(11239, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:16:53', '2026-09-01 09:16:53'),
(11240, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:16:53', '2026-09-01 09:16:53'),
(11241, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:16:53', '2026-09-01 09:16:53'),
(11242, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:17:01', '2026-09-01 09:17:01'),
(11243, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/95', 'GET', 1, '2026-09-01 09:17:38', '2026-09-01 09:17:38'),
(11244, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/95', 'PUT', 1, '2026-09-01 09:18:09', '2026-09-01 09:18:09'),
(11245, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:18:10', '2026-09-01 09:18:10'),
(11246, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:18:10', '2026-09-01 09:18:10'),
(11247, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:18:11', '2026-09-01 09:18:11'),
(11248, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:18:22', '2026-09-01 09:18:22'),
(11249, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:18:23', '2026-09-01 09:18:23'),
(11250, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:18:25', '2026-09-01 09:18:25'),
(11251, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 09:18:25', '2026-09-01 09:18:25'),
(11252, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:18:26', '2026-09-01 09:18:26'),
(11253, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:18:28', '2026-09-01 09:18:28'),
(11254, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/95/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNTE4ODQsImV4cCI6MTc4ODg1NjY4NH0.pq9VL8rcFAxbdk4dKPUTSxsixFIeaspuvQJVuW49Cog', 'GET', 1, '2026-09-01 09:18:31', '2026-09-01 09:18:31'),
(11255, '74.125.208.225', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/95/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNTE4ODQsImV4cCI6MTc4ODg1NjY4NH0.pq9VL8rcFAxbdk4dKPUTSxsixFIeaspuvQJVuW49Cog', 'GET', 1, '2026-09-01 09:18:33', '2026-09-01 09:18:33'),
(11256, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments/tenant-finance/66', 'GET', 1, '2026-09-01 09:18:33', '2026-09-01 09:18:33'),
(11257, '192.178.11.4', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/95/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNTE4ODQsImV4cCI6MTc4ODg1NjY4NH0.pq9VL8rcFAxbdk4dKPUTSxsixFIeaspuvQJVuW49Cog', 'GET', 1, '2026-09-01 09:18:33', '2026-09-01 09:18:33'),
(11258, '74.125.208.227', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', '/api/bills/95/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNTE4ODQsImV4cCI6MTc4ODg1NjY4NH0.pq9VL8rcFAxbdk4dKPUTSxsixFIeaspuvQJVuW49Cog', 'GET', 1, '2026-09-01 09:18:34', '2026-09-01 09:18:34'),
(11259, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'POST', 1, '2026-09-01 09:18:44', '2026-09-01 09:18:44'),
(11260, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 09:18:46', '2026-09-01 09:18:46'),
(11261, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:18:49', '2026-09-01 09:18:49'),
(11262, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:18:49', '2026-09-01 09:18:49'),
(11263, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:18:49', '2026-09-01 09:18:49'),
(11264, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:18:50', '2026-09-01 09:18:50'),
(11265, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:18:52', '2026-09-01 09:18:52'),
(11266, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:18:58', '2026-09-01 09:18:58'),
(11267, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:18:59', '2026-09-01 09:18:59'),
(11268, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:18:59', '2026-09-01 09:18:59'),
(11269, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:02', '2026-09-01 09:19:02'),
(11270, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:19:02', '2026-09-01 09:19:02'),
(11271, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:19:02', '2026-09-01 09:19:02'),
(11272, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:02', '2026-09-01 09:19:02'),
(11273, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:19:03', '2026-09-01 09:19:03'),
(11274, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:19:05', '2026-09-01 09:19:05'),
(11275, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:19:06', '2026-09-01 09:19:06'),
(11276, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:19:06', '2026-09-01 09:19:06'),
(11277, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:06', '2026-09-01 09:19:06'),
(11278, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:19:06', '2026-09-01 09:19:06'),
(11279, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:08', '2026-09-01 09:19:08'),
(11280, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 09:19:08', '2026-09-01 09:19:08'),
(11281, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 09:19:08', '2026-09-01 09:19:08'),
(11282, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:08', '2026-09-01 09:19:08'),
(11283, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:16', '2026-09-01 09:19:16'),
(11284, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:19:16', '2026-09-01 09:19:16'),
(11285, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:19:16', '2026-09-01 09:19:16'),
(11286, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:16', '2026-09-01 09:19:16'),
(11287, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:19:16', '2026-09-01 09:19:16'),
(11288, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:19:19', '2026-09-01 09:19:19'),
(11289, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:44', '2026-09-01 09:19:44'),
(11290, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:19:44', '2026-09-01 09:19:44'),
(11291, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 09:19:44', '2026-09-01 09:19:44'),
(11292, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:44', '2026-09-01 09:19:44'),
(11293, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:57', '2026-09-01 09:19:57'),
(11294, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:19:57', '2026-09-01 09:19:57'),
(11295, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:19:57', '2026-09-01 09:19:57'),
(11296, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:19:58', '2026-09-01 09:19:58'),
(11297, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:19:58', '2026-09-01 09:19:58'),
(11298, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:20:01', '2026-09-01 09:20:01'),
(11299, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:20:01', '2026-09-01 09:20:01'),
(11300, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:20:01', '2026-09-01 09:20:01'),
(11301, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:20:02', '2026-09-01 09:20:02'),
(11302, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:20:04', '2026-09-01 09:20:04'),
(11303, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/95', 'GET', 1, '2026-09-01 09:20:10', '2026-09-01 09:20:10'),
(11304, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/95', 'PUT', 1, '2026-09-01 09:20:19', '2026-09-01 09:20:19'),
(11305, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:20:19', '2026-09-01 09:20:19'),
(11306, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:20:20', '2026-09-01 09:20:20'),
(11307, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:20:20', '2026-09-01 09:20:20'),
(11308, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:20:26', '2026-09-01 09:20:26'),
(11309, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/92', 'GET', 1, '2026-09-01 09:22:25', '2026-09-01 09:22:25'),
(11310, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:22:40', '2026-09-01 09:22:40'),
(11311, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:22:40', '2026-09-01 09:22:40'),
(11312, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 09:22:40', '2026-09-01 09:22:40'),
(11313, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:22:41', '2026-09-01 09:22:41'),
(11314, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:22:42', '2026-09-01 09:22:42'),
(11315, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:22:42', '2026-09-01 09:22:42'),
(11316, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 09:22:42', '2026-09-01 09:22:42'),
(11317, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:22:42', '2026-09-01 09:22:42'),
(11318, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:22:59', '2026-09-01 09:22:59'),
(11319, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 09:22:59', '2026-09-01 09:22:59'),
(11320, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 09:22:59', '2026-09-01 09:22:59'),
(11321, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:22:59', '2026-09-01 09:22:59'),
(11322, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=Prec', 'GET', 1, '2026-09-01 09:23:06', '2026-09-01 09:23:06'),
(11323, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:23:25', '2026-09-01 09:23:25'),
(11324, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:23:26', '2026-09-01 09:23:26'),
(11325, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:23:26', '2026-09-01 09:23:26'),
(11326, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:23:26', '2026-09-01 09:23:26'),
(11327, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:23:26', '2026-09-01 09:23:26'),
(11328, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:23:29', '2026-09-01 09:23:29'),
(11329, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 09:23:30', '2026-09-01 09:23:30'),
(11330, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:23:30', '2026-09-01 09:23:30'),
(11331, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:23:30', '2026-09-01 09:23:30'),
(11332, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:23:46', '2026-09-01 09:23:46'),
(11333, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:23:47', '2026-09-01 09:23:47'),
(11334, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:23:47', '2026-09-01 09:23:47'),
(11335, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:23:47', '2026-09-01 09:23:47'),
(11336, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:23:47', '2026-09-01 09:23:47'),
(11337, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:23:50', '2026-09-01 09:23:50'),
(11338, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/95/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNTE4ODQsImV4cCI6MTc4ODg1NjY4NH0.pq9VL8rcFAxbdk4dKPUTSxsixFIeaspuvQJVuW49Cog', 'GET', 1, '2026-09-01 09:23:57', '2026-09-01 09:23:57'),
(11339, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-09-01 09:24:09', '2026-09-01 09:24:09'),
(11340, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 09:24:20', '2026-09-01 09:24:20'),
(11341, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 09:24:20', '2026-09-01 09:24:20'),
(11342, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/dashboard', 'GET', 1, '2026-09-01 09:24:33', '2026-09-01 09:24:33'),
(11343, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 09:24:33', '2026-09-01 09:24:33'),
(11344, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:24:33', '2026-09-01 09:24:33'),
(11345, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:24:37', '2026-09-01 09:24:37'),
(11346, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:24:38', '2026-09-01 09:24:38'),
(11347, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:24:38', '2026-09-01 09:24:38'),
(11348, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:24:49', '2026-09-01 09:24:49'),
(11349, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:24:50', '2026-09-01 09:24:50'),
(11350, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:24:50', '2026-09-01 09:24:50'),
(11351, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:24:50', '2026-09-01 09:24:50'),
(11352, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:24:50', '2026-09-01 09:24:50'),
(11353, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:24:53', '2026-09-01 09:24:53'),
(11354, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:25:23', '2026-09-01 09:25:23'),
(11355, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:25:26', '2026-09-01 09:25:26'),
(11356, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 09:25:26', '2026-09-01 09:25:26'),
(11357, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:25:26', '2026-09-01 09:25:26'),
(11358, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:25:29', '2026-09-01 09:25:29'),
(11359, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments/tenant-finance/63', 'GET', 1, '2026-09-01 09:25:59', '2026-09-01 09:25:59'),
(11360, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/92', 'GET', 1, '2026-09-01 09:26:19', '2026-09-01 09:26:19'),
(11361, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/92', 'PUT', 1, '2026-09-01 09:26:27', '2026-09-01 09:26:27'),
(11362, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:26:28', '2026-09-01 09:26:28'),
(11363, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:26:28', '2026-09-01 09:26:28'),
(11364, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:26:29', '2026-09-01 09:26:29'),
(11365, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:26:32', '2026-09-01 09:26:32'),
(11366, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'POST', 1, '2026-09-01 09:26:34', '2026-09-01 09:26:34'),
(11367, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 09:26:36', '2026-09-01 09:26:36'),
(11368, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:26:45', '2026-09-01 09:26:45'),
(11369, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 09:26:45', '2026-09-01 09:26:45'),
(11370, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 09:26:45', '2026-09-01 09:26:45'),
(11371, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:26:45', '2026-09-01 09:26:45'),
(11372, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 09:26:46', '2026-09-01 09:26:46'),
(11373, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:26:46', '2026-09-01 09:26:46'),
(11374, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:26:46', '2026-09-01 09:26:46'),
(11375, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 09:26:52', '2026-09-01 09:26:52'),
(11376, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 09:26:52', '2026-09-01 09:26:52'),
(11377, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:26:52', '2026-09-01 09:26:52'),
(11378, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:26:58', '2026-09-01 09:26:58'),
(11379, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:26:59', '2026-09-01 09:26:59'),
(11380, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:26:59', '2026-09-01 09:26:59'),
(11381, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:26:59', '2026-09-01 09:26:59'),
(11382, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:26:59', '2026-09-01 09:26:59'),
(11383, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:27:00', '2026-09-01 09:27:00'),
(11384, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:27:00', '2026-09-01 09:27:00'),
(11385, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:27:00', '2026-09-01 09:27:00'),
(11386, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:27:00', '2026-09-01 09:27:00'),
(11387, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:27:00', '2026-09-01 09:27:00'),
(11388, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:27:03', '2026-09-01 09:27:03'),
(11389, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:27:03', '2026-09-01 09:27:03'),
(11390, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:27:11', '2026-09-01 09:27:11'),
(11391, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:27:11', '2026-09-01 09:27:11'),
(11392, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:27:11', '2026-09-01 09:27:11'),
(11393, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:27:12', '2026-09-01 09:27:12'),
(11394, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:27:13', '2026-09-01 09:27:13'),
(11395, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 09:27:19', '2026-09-01 09:27:19'),
(11396, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 09:27:19', '2026-09-01 09:27:19'),
(11397, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:27:19', '2026-09-01 09:27:19'),
(11398, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 09:27:19', '2026-09-01 09:27:19'),
(11399, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 09:27:19', '2026-09-01 09:27:19'),
(11400, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:27:19', '2026-09-01 09:27:19'),
(11401, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:27:23', '2026-09-01 09:27:23'),
(11402, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:27:23', '2026-09-01 09:27:23'),
(11403, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:27:23', '2026-09-01 09:27:23'),
(11404, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:27:23', '2026-09-01 09:27:23'),
(11405, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:27:23', '2026-09-01 09:27:23'),
(11406, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:27:27', '2026-09-01 09:27:27'),
(11407, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:31:38', '2026-09-01 09:31:38'),
(11408, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:31:46', '2026-09-01 09:31:46'),
(11409, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:31:57', '2026-09-01 09:31:57'),
(11410, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:31:59', '2026-09-01 09:31:59'),
(11411, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/98', 'GET', 1, '2026-09-01 09:33:08', '2026-09-01 09:33:08'),
(11412, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/98', 'PUT', 1, '2026-09-01 09:33:14', '2026-09-01 09:33:14'),
(11413, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:33:14', '2026-09-01 09:33:14'),
(11414, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:33:14', '2026-09-01 09:33:14'),
(11415, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:33:15', '2026-09-01 09:33:15'),
(11416, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:33:18', '2026-09-01 09:33:18'),
(11417, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:33:40', '2026-09-01 09:33:40'),
(11418, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:33:40', '2026-09-01 09:33:40'),
(11419, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:33:40', '2026-09-01 09:33:40'),
(11420, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:33:41', '2026-09-01 09:33:41'),
(11421, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:33:51', '2026-09-01 09:33:51'),
(11422, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:35:35', '2026-09-01 09:35:35'),
(11423, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:35:38', '2026-09-01 09:35:38'),
(11424, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:35:38', '2026-09-01 09:35:38'),
(11425, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:35:40', '2026-09-01 09:35:40'),
(11426, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:35:41', '2026-09-01 09:35:41'),
(11427, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 09:35:41', '2026-09-01 09:35:41'),
(11428, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:35:41', '2026-09-01 09:35:41'),
(11429, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:35:50', '2026-09-01 09:35:50');
INSERT INTO `bot_detections` (`id`, `ip_address`, `user_agent`, `endpoint`, `method`, `attempts`, `last_seen`, `created_at`) VALUES
(11430, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:35:50', '2026-09-01 09:35:50'),
(11431, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:35:50', '2026-09-01 09:35:50'),
(11432, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:35:50', '2026-09-01 09:35:50'),
(11433, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:35:50', '2026-09-01 09:35:50'),
(11434, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:35:52', '2026-09-01 09:35:52'),
(11435, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/93', 'GET', 1, '2026-09-01 09:36:56', '2026-09-01 09:36:56'),
(11436, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/93', 'GET', 1, '2026-09-01 09:37:14', '2026-09-01 09:37:14'),
(11437, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/93', 'PUT', 1, '2026-09-01 09:37:26', '2026-09-01 09:37:26'),
(11438, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:37:26', '2026-09-01 09:37:26'),
(11439, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:37:27', '2026-09-01 09:37:27'),
(11440, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:37:27', '2026-09-01 09:37:27'),
(11441, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:37:31', '2026-09-01 09:37:31'),
(11442, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/93', 'GET', 1, '2026-09-01 09:37:48', '2026-09-01 09:37:48'),
(11443, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:38:02', '2026-09-01 09:38:02'),
(11444, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:38:02', '2026-09-01 09:38:02'),
(11445, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=100', 'GET', 1, '2026-09-01 09:38:02', '2026-09-01 09:38:02'),
(11446, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:38:02', '2026-09-01 09:38:02'),
(11447, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:38:14', '2026-09-01 09:38:14'),
(11448, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:38:14', '2026-09-01 09:38:14'),
(11449, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:38:14', '2026-09-01 09:38:14'),
(11450, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:38:14', '2026-09-01 09:38:14'),
(11451, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:38:14', '2026-09-01 09:38:14'),
(11452, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:38:18', '2026-09-01 09:38:18'),
(11453, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/93', 'GET', 1, '2026-09-01 09:38:36', '2026-09-01 09:38:36'),
(11454, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/93', 'PUT', 1, '2026-09-01 09:39:01', '2026-09-01 09:39:01'),
(11455, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:39:01', '2026-09-01 09:39:01'),
(11456, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:39:02', '2026-09-01 09:39:02'),
(11457, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=25', 'GET', 1, '2026-09-01 09:39:02', '2026-09-01 09:39:02'),
(11458, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=25', 'GET', 1, '2026-09-01 09:39:05', '2026-09-01 09:39:05'),
(11459, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:39:20', '2026-09-01 09:39:20'),
(11460, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:39:20', '2026-09-01 09:39:20'),
(11461, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:39:20', '2026-09-01 09:39:20'),
(11462, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:39:20', '2026-09-01 09:39:20'),
(11463, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:39:23', '2026-09-01 09:39:23'),
(11464, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:39:58', '2026-09-01 09:39:58'),
(11465, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:39:58', '2026-09-01 09:39:58'),
(11466, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:39:58', '2026-09-01 09:39:58'),
(11467, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:39:58', '2026-09-01 09:39:58'),
(11468, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:40:03', '2026-09-01 09:40:03'),
(11469, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=1&per_page=100', 'GET', 1, '2026-09-01 09:40:16', '2026-09-01 09:40:16'),
(11470, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:40:25', '2026-09-01 09:40:25'),
(11471, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:40:25', '2026-09-01 09:40:25'),
(11472, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 09:40:25', '2026-09-01 09:40:25'),
(11473, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:40:25', '2026-09-01 09:40:25'),
(11474, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:40:34', '2026-09-01 09:40:34'),
(11475, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:40:34', '2026-09-01 09:40:34'),
(11476, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:40:34', '2026-09-01 09:40:34'),
(11477, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:40:34', '2026-09-01 09:40:34'),
(11478, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:40:34', '2026-09-01 09:40:34'),
(11479, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:40:37', '2026-09-01 09:40:37'),
(11480, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:40:53', '2026-09-01 09:40:53'),
(11481, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:40:53', '2026-09-01 09:40:53'),
(11482, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 09:40:53', '2026-09-01 09:40:53'),
(11483, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:40:54', '2026-09-01 09:40:54'),
(11484, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:42:03', '2026-09-01 09:42:03'),
(11485, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:42:04', '2026-09-01 09:42:04'),
(11486, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:42:04', '2026-09-01 09:42:04'),
(11487, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:42:04', '2026-09-01 09:42:04'),
(11488, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:42:04', '2026-09-01 09:42:04'),
(11489, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:42:08', '2026-09-01 09:42:08'),
(11490, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:43:19', '2026-09-01 09:43:19'),
(11491, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:43:19', '2026-09-01 09:43:19'),
(11492, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:43:19', '2026-09-01 09:43:19'),
(11493, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:43:19', '2026-09-01 09:43:19'),
(11494, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:43:23', '2026-09-01 09:43:23'),
(11495, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:44:33', '2026-09-01 09:44:33'),
(11496, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:44:33', '2026-09-01 09:44:33'),
(11497, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 09:44:33', '2026-09-01 09:44:33'),
(11498, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:44:34', '2026-09-01 09:44:34'),
(11499, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/94', 'GET', 1, '2026-09-01 09:44:44', '2026-09-01 09:44:44'),
(11500, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/94', 'PUT', 1, '2026-09-01 09:44:53', '2026-09-01 09:44:53'),
(11501, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:44:54', '2026-09-01 09:44:54'),
(11502, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:44:54', '2026-09-01 09:44:54'),
(11503, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:44:54', '2026-09-01 09:44:54'),
(11504, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:44:58', '2026-09-01 09:44:58'),
(11505, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:45:07', '2026-09-01 09:45:07'),
(11506, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:45:11', '2026-09-01 09:45:11'),
(11507, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?page=1&per_page=25', 'GET', 1, '2026-09-01 09:45:11', '2026-09-01 09:45:11'),
(11508, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:45:11', '2026-09-01 09:45:11'),
(11509, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:45:16', '2026-09-01 09:45:16'),
(11510, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=25', 'GET', 1, '2026-09-01 09:45:16', '2026-09-01 09:45:16'),
(11511, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:45:16', '2026-09-01 09:45:16'),
(11512, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:45:18', '2026-09-01 09:45:18'),
(11513, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:45:20', '2026-09-01 09:45:20'),
(11514, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:45:20', '2026-09-01 09:45:20'),
(11515, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses?page=1&per_page=100', 'GET', 1, '2026-09-01 09:45:21', '2026-09-01 09:45:21'),
(11516, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:45:34', '2026-09-01 09:45:34'),
(11517, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:45:37', '2026-09-01 09:45:37'),
(11518, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:45:37', '2026-09-01 09:45:37'),
(11519, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:46:19', '2026-09-01 09:46:19'),
(11520, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:46:19', '2026-09-01 09:46:19'),
(11521, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:46:19', '2026-09-01 09:46:19'),
(11522, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:46:19', '2026-09-01 09:46:19'),
(11523, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:46:20', '2026-09-01 09:46:20'),
(11524, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:46:23', '2026-09-01 09:46:23'),
(11525, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:48:19', '2026-09-01 09:48:19'),
(11526, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:48:21', '2026-09-01 09:48:21'),
(11527, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:48:22', '2026-09-01 09:48:22'),
(11528, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:48:23', '2026-09-01 09:48:23'),
(11529, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:48:50', '2026-09-01 09:48:50'),
(11530, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-09-01 09:48:51', '2026-09-01 09:48:51'),
(11531, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses/54', 'GET', 1, '2026-09-01 09:48:52', '2026-09-01 09:48:52'),
(11532, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:48:58', '2026-09-01 09:48:58'),
(11533, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/houses/available?property_id=34', 'GET', 1, '2026-09-01 09:55:04', '2026-09-01 09:55:04'),
(11534, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'POST', 1, '2026-09-01 09:56:32', '2026-09-01 09:56:32'),
(11535, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:56:32', '2026-09-01 09:56:32'),
(11536, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:56:42', '2026-09-01 09:56:42'),
(11537, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 09:56:43', '2026-09-01 09:56:43'),
(11538, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 09:56:43', '2026-09-01 09:56:43'),
(11539, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:56:43', '2026-09-01 09:56:43'),
(11540, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:56:49', '2026-09-01 09:56:49'),
(11541, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:56:49', '2026-09-01 09:56:49'),
(11542, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:56:49', '2026-09-01 09:56:49'),
(11543, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:56:50', '2026-09-01 09:56:50'),
(11544, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:56:50', '2026-09-01 09:56:50'),
(11545, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:57:16', '2026-09-01 09:57:16'),
(11546, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:57:18', '2026-09-01 09:57:18'),
(11547, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:57:18', '2026-09-01 09:57:18'),
(11548, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:57:26', '2026-09-01 09:57:26'),
(11549, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:57:53', '2026-09-01 09:57:53'),
(11550, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 09:58:00', '2026-09-01 09:58:00'),
(11551, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 09:58:00', '2026-09-01 09:58:00'),
(11552, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:58:01', '2026-09-01 09:58:01'),
(11553, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 09:58:08', '2026-09-01 09:58:08'),
(11554, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 09:58:25', '2026-09-01 09:58:25'),
(11555, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 09:58:53', '2026-09-01 09:58:53'),
(11556, '197.248.82.231', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 09:58:53', '2026-09-01 09:58:53'),
(11557, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/setup-password?token=ff10d756556189061a87b08cd1ba02a95dc45ac9da682536415bf3cb5aa08dc3', 'GET', 1, '2026-09-01 11:01:57', '2026-09-01 11:01:57'),
(11558, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/resend-setup-email', 'POST', 1, '2026-09-01 11:02:03', '2026-09-01 11:02:03'),
(11559, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 11:02:15', '2026-09-01 11:02:15'),
(11560, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 11:02:15', '2026-09-01 11:02:15'),
(11561, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 11:02:17', '2026-09-01 11:02:17'),
(11562, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-09-01 11:02:17', '2026-09-01 11:02:17'),
(11563, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:02:17', '2026-09-01 11:02:17'),
(11564, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?tenant_id=65', 'GET', 1, '2026-09-01 11:02:17', '2026-09-01 11:02:17'),
(11565, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 11:02:17', '2026-09-01 11:02:17'),
(11566, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:02:17', '2026-09-01 11:02:17'),
(11567, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 11:02:19', '2026-09-01 11:02:19'),
(11568, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-09-01 11:02:19', '2026-09-01 11:02:19'),
(11569, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:02:19', '2026-09-01 11:02:19'),
(11570, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?tenant_id=65', 'GET', 1, '2026-09-01 11:02:19', '2026-09-01 11:02:19'),
(11571, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 11:02:19', '2026-09-01 11:02:19'),
(11572, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:02:20', '2026-09-01 11:02:20'),
(11573, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants', 'GET', 1, '2026-09-01 11:02:43', '2026-09-01 11:02:43'),
(11574, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/tenants/property-contact', 'GET', 1, '2026-09-01 11:02:43', '2026-09-01 11:02:43'),
(11575, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:02:43', '2026-09-01 11:02:43'),
(11576, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments?tenant_id=65', 'GET', 1, '2026-09-01 11:02:43', '2026-09-01 11:02:43'),
(11577, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 11:02:43', '2026-09-01 11:02:43'),
(11578, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:02:43', '2026-09-01 11:02:43'),
(11579, '154.159.252.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', '/api/auth/setup-password?token=ff10d756556189061a87b08cd1ba02a95dc45ac9da682536415bf3cb5aa08dc3', 'GET', 1, '2026-09-01 11:03:02', '2026-09-01 11:03:02'),
(11580, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 11:25:47', '2026-09-01 11:25:47'),
(11581, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 11:25:47', '2026-09-01 11:25:47'),
(11582, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/dashboard', 'GET', 1, '2026-09-01 11:25:49', '2026-09-01 11:25:49'),
(11583, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 11:25:49', '2026-09-01 11:25:49'),
(11584, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:25:49', '2026-09-01 11:25:49'),
(11585, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:25:52', '2026-09-01 11:25:52'),
(11586, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 11:25:53', '2026-09-01 11:25:53'),
(11587, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 11:25:53', '2026-09-01 11:25:53'),
(11588, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:25:53', '2026-09-01 11:25:53'),
(11589, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 11:25:53', '2026-09-01 11:25:53'),
(11590, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 11:26:04', '2026-09-01 11:26:04'),
(11591, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/91', 'GET', 1, '2026-09-01 11:26:57', '2026-09-01 11:26:57'),
(11592, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/91', 'GET', 1, '2026-09-01 11:27:06', '2026-09-01 11:27:06'),
(11593, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/91', 'PUT', 1, '2026-09-01 11:27:13', '2026-09-01 11:27:13'),
(11594, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 11:27:13', '2026-09-01 11:27:13'),
(11595, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 11:27:14', '2026-09-01 11:27:14'),
(11596, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 11:27:14', '2026-09-01 11:27:14'),
(11597, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/generate', 'POST', 1, '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(11598, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 11:27:29', '2026-09-01 11:27:29'),
(11599, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 11:27:30', '2026-09-01 11:27:30'),
(11600, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:28:15', '2026-09-01 11:28:15'),
(11601, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/email-logs/stats', 'GET', 1, '2026-09-01 11:28:16', '2026-09-01 11:28:16'),
(11602, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/email-logs?page=1&per_page=25&status=&email_type=&date_from=&date_to=&search=', 'GET', 1, '2026-09-01 11:28:16', '2026-09-01 11:28:16'),
(11603, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:28:16', '2026-09-01 11:28:16'),
(11604, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/email-logs/629', 'GET', 1, '2026-09-01 11:28:25', '2026-09-01 11:28:25'),
(11605, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:28:49', '2026-09-01 11:28:49'),
(11606, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 11:28:50', '2026-09-01 11:28:50'),
(11607, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 11:28:50', '2026-09-01 11:28:50'),
(11608, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:28:50', '2026-09-01 11:28:50'),
(11609, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 11:28:50', '2026-09-01 11:28:50'),
(11610, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 11:28:57', '2026-09-01 11:28:57'),
(11611, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/95/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNjE5NDcsImV4cCI6MTc4ODg2Njc0N30.VuUp2mommwWD_CFFDUoO71tfbjynbLKLLXjpWGUmR_w', 'GET', 1, '2026-09-01 11:29:18', '2026-09-01 11:29:18'),
(11612, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 11:29:23', '2026-09-01 11:29:23'),
(11613, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/102/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjcsImVtYWlsIjoiYmV0aGphYnNAZ21haWwuY29tIiwicm9sZSI6Im93bmVyIiwibmFtZSI6IkphY29iIE1idXRoaWEiLCJpYXQiOjE3ODgyNjE5NDcsImV4cCI6MTc4ODg2Njc0N30.VuUp2mommwWD_CFFDUoO71tfbjynbLKLLXjpWGUmR_w', 'GET', 1, '2026-09-01 11:29:26', '2026-09-01 11:29:26'),
(11614, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 11:30:05', '2026-09-01 11:30:05'),
(11615, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:30:35', '2026-09-01 11:30:35'),
(11616, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 11:30:35', '2026-09-01 11:30:35'),
(11617, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/dashboard', 'GET', 1, '2026-09-01 11:30:35', '2026-09-01 11:30:35'),
(11618, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 11:30:36', '2026-09-01 11:30:36'),
(11619, '41.90.217.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/auth/setup-password?token=7851a3fe3df02ebb53874e626c6e0af28e17b28f1efaade76f1c607d02800170', 'GET', 1, '2026-09-01 12:24:07', '2026-09-01 12:24:07'),
(11620, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/logout', 'POST', 1, '2026-09-01 12:24:41', '2026-09-01 12:24:41'),
(11621, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 12:24:44', '2026-09-01 12:24:44'),
(11622, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/auth/login', 'POST', 1, '2026-09-01 12:24:44', '2026-09-01 12:24:44'),
(11623, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/dashboard', 'GET', 1, '2026-09-01 12:24:47', '2026-09-01 12:24:47'),
(11624, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/payments', 'GET', 1, '2026-09-01 12:24:47', '2026-09-01 12:24:47'),
(11625, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 12:24:47', '2026-09-01 12:24:47'),
(11626, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 12:24:50', '2026-09-01 12:24:50'),
(11627, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/properties', 'GET', 1, '2026-09-01 12:24:50', '2026-09-01 12:24:50'),
(11628, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months', 'GET', 1, '2026-09-01 12:24:50', '2026-09-01 12:24:50'),
(11629, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/complaints', 'GET', 1, '2026-09-01 12:24:50', '2026-09-01 12:24:50'),
(11630, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 12:24:50', '2026-09-01 12:24:50'),
(11631, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?month=2026-08&page=%5Bobject+Event%5D&per_page=100', 'GET', 1, '2026-09-01 12:25:06', '2026-09-01 12:25:06'),
(11632, '41.90.217.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '/api/bills/112/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NiwiaWF0IjoxNzg4MjYyMDQ1LCJleHAiOjE3ODg4NjY4NDV9.kxCX7MaLpzXq5kdv_lBbguzfAsmeEpzF3n3dJdehKbA', 'GET', 1, '2026-09-01 12:25:16', '2026-09-01 12:25:16'),
(11633, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills/months?property_id=34', 'GET', 1, '2026-09-01 12:25:36', '2026-09-01 12:25:36'),
(11634, '197.248.82.231', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '/api/bills?property_id=34&month=2026-09&page=1&per_page=100', 'GET', 1, '2026-09-01 12:25:36', '2026-09-01 12:25:36'),
(11635, '102.0.19.200', 'WhatsApp/2.23.20.0', '/api/bills/105/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OCwiaWF0IjoxNzg4MjYyMDQzLCJleHAiOjE3ODg4NjY4NDN9.vS5VeGPXh7PIV8vHmRFdHVhw-gVvqwHWKDsoGBEs1gw', 'GET', 1, '2026-09-01 12:26:43', '2026-09-01 12:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `caretakers`
--

CREATE TABLE `caretakers` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'CT',
  `assigned_properties` text COLLATE utf8mb4_unicode_ci COMMENT 'Comma-separated property IDs',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `caretakers`
--

INSERT INTO `caretakers` (`id`, `owner_id`, `name`, `email`, `phone`, `id_number`, `password`, `avatar`, `assigned_properties`, `created_at`, `updated_at`) VALUES
(37, 13, 'Rental Flow', 'rentalflow.realestate@gmail.com', '+254112554479', '40135584', '$2y$10$2LnF2oPIAX4cbfT4hGLOFek8xbyo9d1kXcpPPdjpvniGgsJgngM1q', 'CT', '36', '2026-08-02 13:16:10', '2026-08-02 13:17:01'),
(38, 7, 'Fares Nthiwa Mutua', 'faresnthiwa@gmail.com', '0703288106', '42770923', '$2y$10$twBtYZSyDbkNbDvs7azBAe8NtuHEE2laVDMh5ZeJm5kMgkSIBLhtq', 'CT', '34', '2026-08-09 06:07:57', '2026-08-09 06:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `communications`
--

CREATE TABLE `communications` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `type` enum('whatsapp','email','sms') COLLATE utf8mb4_unicode_ci DEFAULT 'email',
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `date` date DEFAULT NULL,
  `status` enum('sent','delivered','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'sent',
  `template` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `tenant_id` int UNSIGNED DEFAULT NULL,
  `house_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Other',
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('open','in-progress','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `date` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `timeline` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON timeline data',
  `comments` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON comments data',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sender_role` enum('tenant','owner','caretaker') COLLATE utf8mb4_unicode_ci DEFAULT 'tenant',
  `recipient_type` enum('individual','property','all') COLLATE utf8mb4_unicode_ci DEFAULT 'individual',
  `recipient_ids` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of tenant IDs for individual; property_id for property-wide',
  `property_id` int UNSIGNED DEFAULT NULL,
  `read_by` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of tenant IDs who read this',
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `owner_id`, `tenant_id`, `house_id`, `title`, `category`, `priority`, `status`, `date`, `description`, `timeline`, `comments`, `created_at`, `updated_at`, `sender_role`, `recipient_type`, `recipient_ids`, `property_id`, `read_by`, `read_at`) VALUES
(104, 12, 49, 82, 'No silence ', 'Noise', 'high', 'open', '2026-08-01', 'No loud music people want to sleep ', '[{\"date\":\"2026-08-01 18:31:14\",\"status\":\"Owner Sent\",\"note\":\"Owner created this message\"}]', '[]', '2026-08-01 18:31:14', '2026-08-01 18:31:14', 'owner', 'individual', '[49]', 35, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int NOT NULL,
  `owner_id` int NOT NULL,
  `to_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `status` enum('sent','failed','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'sent',
  `error` text COLLATE utf8mb4_unicode_ci,
  `sent_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `owner_id`, `to_email`, `to_name`, `subject`, `body`, `status`, `error`, `sent_at`, `created_at`) VALUES
(486, 7, 'josephokotch01@gmail.com', 'Joseph okotch', 'Welcome to RentaFlow - Set Up Your Account', 'Dear Joseph okotch,\r\n\r\nWelcome to RentaFlow! You have been registered as a caretaker.\r\n\r\nYour email: josephokotch01@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=a34a918c0ec0e1e382817dff2f7e6f7cc9bac5a90838fc00a2025100e52d7dee\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-01 10:03:57', '2026-08-01 10:03:57'),
(487, 7, 'sevelle376@gmail.com', 'Mark Njoroge Waweru', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Mark Njoroge Waweru,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: B2\r\nYour email: sevelle376@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=728736954b2417660e71287c7960394aa2fcce7341a51079f5c67f9595793284\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-01 10:39:35', '2026-08-01 10:39:35'),
(488, 7, 'billyndume2@gmail.com', 'Billy Ndume Muruu', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Billy Ndume Muruu,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: B4\r\nYour email: billyndume2@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=389461d3719924c6606110152cc284db8be90f6c7f34f65dc71ed645cd9fb1dc\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-01 10:45:39', '2026-08-01 10:45:39'),
(489, 7, 'billyndume2@gmail.com', 'Billy Ndume Muruu', 'Tenancy Termination Notice - Jakes Apartments B4', 'Dear Billy Ndume Muruu,\n\nThis is to inform you that your tenancy has been terminated.\n\nDetails:\n- Property: Jakes Apartments\n- Unit: B4\n- Termination Date: 2026-08-02\n- Reason: Not provided\n\nPlease ensure the property is vacated by the termination date. Contact your property manager for any handover instructions.\n\nBest regards,\nJacob Mbuthia\nRentFlow', 'sent', NULL, '2026-08-01 10:56:23', '2026-08-01 10:56:23'),
(490, 7, 'billyndume2@gmail.com', 'Billy Ndume Muruu', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Billy Ndume Muruu,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: B4\r\nYour email: billyndume2@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=3792a0b817c303e9bf46cd56bfde5ab0d4aa22bab1dad47118dc5a9b0be1cc04\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-01 10:59:13', '2026-08-01 10:59:13'),
(491, 7, 'zaharazainabu09@gmail.com', 'Zahra Zainabu', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Zahra Zainabu,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: A1\r\nYour email: zaharazainabu09@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=c64518fc4ab1d602023191f183c6786fea5156a06703e5bb40d8499238130278\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-01 11:17:26', '2026-08-01 11:17:26'),
(492, 7, 'ruthjepchumba731@gmail.com', 'Ruth jepchumba', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Ruth jepchumba,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: C2\r\nYour email: ruthjepchumba731@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=148265bc0998cc26a9e22e1869a79767658812dbd3ebe53ea6168289d4e816c8\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-01 12:34:14', '2026-08-01 12:34:14'),
(493, 7, 'ruthjepchumba731@gmail.com', 'Ruth jepchumba', 'Payment Confirmation - KES 13,000.00', 'Hi Ruth jepchumba,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 13,000.00\n- Category: Rent\n- Date: 2026-08-01\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/71/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NywiaWF0IjoxNzg1NTg3ODUzLCJleHAiOjE3ODYxOTI2NTN9.GUaihsQ4jXguNVrA_2qAclve-PTQ41_5xSKQjOHdPbo\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/71/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NywiaWF0IjoxNzg1NTg3ODUzLCJleHAiOjE3ODYxOTI2NTN9.GUaihsQ4jXguNVrA_2qAclve-PTQ41_5xSKQjOHdPbo\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-01 12:37:33', '2026-08-01 12:37:33'),
(494, 7, 'jepkorirp@gmail.com', 'Pauline Jepkorir Tuitoek', 'Payment Confirmation - KES 13,000.00', 'Hi Ruth jepchumba,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 13,000.00\n- Category: Rent\n- Date: 2026-08-01\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/71/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NywiaWF0IjoxNzg1NTg3ODUzLCJleHAiOjE3ODYxOTI2NTN9.GUaihsQ4jXguNVrA_2qAclve-PTQ41_5xSKQjOHdPbo\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/71/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NywiaWF0IjoxNzg1NTg3ODUzLCJleHAiOjE3ODYxOTI2NTN9.GUaihsQ4jXguNVrA_2qAclve-PTQ41_5xSKQjOHdPbo\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-01 12:37:33', '2026-08-01 12:37:33'),
(495, 7, 'jepkorirp@gmail.com', 'Pauline Jepkorir Tuitoek', 'Payment Receipt - Ruth jepchumba - Pauline Jepkorir Tuitoek', 'Dear Pauline Jepkorir Tuitoek,\n\ntenant_name: Ruth jepchumba\namount: 13,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-7853\ndate: 2026-08-01\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-01 12:37:33', '2026-08-01 12:37:33'),
(496, 7, 'felistajepchirchir@gmail.com', 'felista jepchirchir rotich', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear felista jepchirchir rotich,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: C3\r\nYour email: felistajepchirchir@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=fa318f29e9d017001c65b6bb60c8cb615df52a657e62c9707ac4b82f28824aac\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-01 14:47:22', '2026-08-01 14:47:22'),
(497, 7, 'felistajepchirchir@gmail.com', 'felista jepchirchir rotich', 'Payment Confirmation - KES 13,000.00', 'Hi felista jepchirchir rotich,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 13,000.00\n- Category: Rent\n- Date: 2026-08-01\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-01 14:48:45', '2026-08-01 14:48:45'),
(498, 7, 'gilbertchangwony@gmail.com', 'Gilbert Changwony', 'Payment Confirmation - KES 13,000.00', 'Hi felista jepchirchir rotich,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 13,000.00\n- Category: Rent\n- Date: 2026-08-01\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/72/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg1NTk1NzI1LCJleHAiOjE3ODYyMDA1MjV9.zcpJIyoTxEljwn_SfDsBPPVoKU58HxkRpUigKnslO3g\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-01 14:48:46', '2026-08-01 14:48:46'),
(499, 7, 'gilbertchangwony@gmail.com', 'Gilbert Changwony', 'Payment Receipt - felista jepchirchir rotich - Gilbert Changwony', 'Dear Gilbert Changwony,\n\ntenant_name: felista jepchirchir rotich\namount: 13,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-5725\ndate: 2026-08-01\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-01 14:48:46', '2026-08-01 14:48:46'),
(500, 12, 'kwatlita@gmail.com', 'G1 owner', 'Welcome to Aluta mtoto apartments  - Set Up Your Account', 'Dear G1 owner,\r\n\r\nWelcome to Aluta mtoto apartments ! We are excited to have you as our tenant.\r\n\r\nYour unit: G1\r\nYour email: kwatlita@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=731de77a1e1c2be83155fe4101f62a8ab9785a03451fcaa76cbe08932e9d11e5\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-01 18:29:58', '2026-08-01 18:29:58'),
(501, 12, 'kwatlita@gmail.com', 'kwat lita', 'New notice created', 'You created a new notice.\n\nTitle: No silence \nDescription: No loud music people want to sleep \nCategory: Noise\nPriority: High', 'sent', NULL, '2026-08-01 18:31:14', '2026-08-01 18:31:14'),
(502, 12, 'kwatlita@gmail.com', 'G1 owner', 'No silence  - RentFlow', 'Dear G1 owner,\n\nYou have received an important notice from kwat lita.\n\nProperty: Aluta mtoto apartments \nUnit: G1\nDate: 2026-08-01\n\nCategory: Noise\n\nSubject: No silence \n\nMessage:\nNo loud music people want to sleep \n\nPlease log in to your RentFlow account to view full details, track updates, and respond if needed.\n\nBest regards,\nkwat lita', 'sent', NULL, '2026-08-01 18:31:14', '2026-08-01 18:31:14'),
(503, 13, 'rentalflow.realestate@gmail.com', 'Rental Flow', 'Welcome to RentaFlow - Set Up Your Account', 'Dear Rental Flow,\r\n\r\nWelcome to RentaFlow! You have been registered as a caretaker.\r\n\r\nYour email: rentalflow.realestate@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=9e22649fcbdd21b61ceb00a3cbffd5177272f2e92b820fe6c333ae4c47a73654\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-02 13:16:10', '2026-08-02 13:16:10'),
(504, 13, 'duncankarenju750@gmail.com', 'Duncan Karenju Gathogo', 'Welcome to Rentii Suites - Set Up Your Account', 'Dear Duncan Karenju Gathogo,\r\n\r\nWelcome to Rentii Suites! We are excited to have you as our tenant.\r\n\r\nYour unit: C1\r\nYour email: duncankarenju750@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=794d908ecb048ec2fc97fda4752075978ac9cad24f58ca6ca061123a02126f90\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-02 13:18:55', '2026-08-02 13:18:55'),
(505, 13, 'duncankarenju750@gmail.com', 'Duncan Karenju Gathogo', 'Payment Confirmation - KES 15,000.00', 'Hi Duncan Karenju Gathogo,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 15,000.00\n- Category: Rent\n- Date: 2026-08-02\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/74/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTAsImlhdCI6MTc4NTY3Nzc0MSwiZXhwIjoxNzg2MjgyNTQxfQ.NWFfjcCC7oSaCtcuRJ2EUAV0xlhh07n7bTL4-xgNE6Y\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/74/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTAsImlhdCI6MTc4NTY3Nzc0MSwiZXhwIjoxNzg2MjgyNTQxfQ.NWFfjcCC7oSaCtcuRJ2EUAV0xlhh07n7bTL4-xgNE6Y\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-02 13:35:41', '2026-08-02 13:35:41'),
(506, 13, 'karenjuduncan750@gmail.com', 'jane mwangi', 'Payment Confirmation - KES 15,000.00', 'Hi Duncan Karenju Gathogo,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 15,000.00\n- Category: Rent\n- Date: 2026-08-02\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/74/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTAsImlhdCI6MTc4NTY3Nzc0MSwiZXhwIjoxNzg2MjgyNTQxfQ.NWFfjcCC7oSaCtcuRJ2EUAV0xlhh07n7bTL4-xgNE6Y\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/74/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTAsImlhdCI6MTc4NTY3Nzc0MSwiZXhwIjoxNzg2MjgyNTQxfQ.NWFfjcCC7oSaCtcuRJ2EUAV0xlhh07n7bTL4-xgNE6Y\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-02 13:35:41', '2026-08-02 13:35:41'),
(507, 13, 'karenjuduncan750@gmail.com', 'jane mwangi', 'Payment Receipt - Duncan Karenju Gathogo - jane mwangi', 'Dear jane mwangi,\n\ntenant_name: Duncan Karenju Gathogo\namount: 15,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-7740\ndate: 2026-08-02\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-02 13:35:41', '2026-08-02 13:35:41'),
(508, 7, 'bichiinicole@gmail.com', 'Bichii Nicole Cheruto', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Bichii Nicole Cheruto,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: A7\r\nYour email: bichiinicole@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=4782c767e3eb926ce92c0d65769f32ddd03ffbc5a813b2ab72325989f784dcce\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-02 17:05:10', '2026-08-02 17:05:10'),
(509, 7, 'bichiinicole@gmail.com', 'Bichii Nicole Cheruto', 'Payment Confirmation - KES 14,000.00', 'Hi Bichii Nicole Cheruto,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 14,000.00\n- Category: Rent\n- Date: 2026-08-02\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/75/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MSwiaWF0IjoxNzg1NjkwMzM4LCJleHAiOjE3ODYyOTUxMzh9.qvQ4BhcJAJU2W95bIzVglEibLN4VVjZ4vwj20FXRhIU\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/75/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MSwiaWF0IjoxNzg1NjkwMzM4LCJleHAiOjE3ODYyOTUxMzh9.qvQ4BhcJAJU2W95bIzVglEibLN4VVjZ4vwj20FXRhIU\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-02 17:05:38', '2026-08-02 17:05:38'),
(510, 7, 'kkipkoech002@gmail.com', 'Kevin Kipkoech', 'Payment Confirmation - KES 14,000.00', 'Hi Bichii Nicole Cheruto,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 14,000.00\n- Category: Rent\n- Date: 2026-08-02\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/75/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MSwiaWF0IjoxNzg1NjkwMzM4LCJleHAiOjE3ODYyOTUxMzh9.qvQ4BhcJAJU2W95bIzVglEibLN4VVjZ4vwj20FXRhIU\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/75/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MSwiaWF0IjoxNzg1NjkwMzM4LCJleHAiOjE3ODYyOTUxMzh9.qvQ4BhcJAJU2W95bIzVglEibLN4VVjZ4vwj20FXRhIU\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-02 17:05:38', '2026-08-02 17:05:38'),
(511, 7, 'kkipkoech002@gmail.com', 'Kevin Kipkoech', 'Payment Receipt - Bichii Nicole Cheruto - Kevin Kipkoech', 'Dear Kevin Kipkoech,\n\ntenant_name: Bichii Nicole Cheruto\namount: 14,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-0338\ndate: 2026-08-02\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-02 17:05:38', '2026-08-02 17:05:38'),
(512, 7, 'wanjikuaustin84@gmail.com', 'Austin Kariuki', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Austin Kariuki,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: A8\r\nYour email: wanjikuaustin84@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=7e498c22edd741d2f11d394e060f36e9efdf6286bd3a83aed97363f73ae31a7e\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-02 17:13:07', '2026-08-02 17:13:07'),
(513, 7, 'wanjikuaustin84@gmail.com', 'Austin Kariuki', 'Payment Confirmation - KES 14,000.00', 'Hi Austin Kariuki,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 14,000.00\n- Category: Rent\n- Date: 2026-08-02\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/76/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUyLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MiwiaWF0IjoxNzg1NjkwODg1LCJleHAiOjE3ODYyOTU2ODV9.pFV33lM_xL5vj2AegLfrG4RxfpNiImB7urLuF9q4gcI\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/76/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUyLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MiwiaWF0IjoxNzg1NjkwODg1LCJleHAiOjE3ODYyOTU2ODV9.pFV33lM_xL5vj2AegLfrG4RxfpNiImB7urLuF9q4gcI\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-02 17:14:45', '2026-08-02 17:14:45'),
(514, 7, 'valentinenj@ueab.ac.ke', 'Valentine Wanjiru Njeri', 'Payment Confirmation - KES 14,000.00', 'Hi Austin Kariuki,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 14,000.00\n- Category: Rent\n- Date: 2026-08-02\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/76/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUyLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MiwiaWF0IjoxNzg1NjkwODg1LCJleHAiOjE3ODYyOTU2ODV9.pFV33lM_xL5vj2AegLfrG4RxfpNiImB7urLuF9q4gcI\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/76/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUyLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MiwiaWF0IjoxNzg1NjkwODg1LCJleHAiOjE3ODYyOTU2ODV9.pFV33lM_xL5vj2AegLfrG4RxfpNiImB7urLuF9q4gcI\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-02 17:14:45', '2026-08-02 17:14:45'),
(515, 7, 'valentinenj@ueab.ac.ke', 'Valentine Wanjiru Njeri', 'Payment Receipt - Austin Kariuki - Valentine Wanjiru Njeri', 'Dear Valentine Wanjiru Njeri,\n\ntenant_name: Austin Kariuki\namount: 14,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-0885\ndate: 2026-08-02\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-02 17:14:46', '2026-08-02 17:14:46'),
(516, 0, 'bichiinicole@gmail.com', 'Bichii Nicole Cheruto', 'Welcome to  - Set Up Your Account', 'Dear Bichii Nicole Cheruto,\r\n\r\nWelcome to ! We are excited to have you as our tenant.\r\n\r\nYour unit: \r\nYour email: bichiinicole@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=ea2985d34592cdd68afd1b923fe940a38d304498b65582c2fd43bebba7badd5a\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-02 17:51:46', '2026-08-02 17:51:46'),
(517, 13, 'mainapetermwangi2017@gmail.com', 'Andrew Kibet', 'Welcome to Rentii Suites - Set Up Your Account', 'Dear Andrew Kibet,\r\n\r\nWelcome to Rentii Suites! We are excited to have you as our tenant.\r\n\r\nYour unit: C2\r\nYour email: mainapetermwangi2017@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=f0493787744556607553a2dc79b407ebf4fd04a37b4a231dccc62990817b9594\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 04:43:55', '2026-08-03 04:43:55'),
(518, 13, 'mainapetermwangi2017@gmail.com', 'Andrew Kibet', 'Payment Confirmation - KES 15,000.00', 'Hi Andrew Kibet,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 15,000.00\n- Category: Rent\n- Date: 2026-08-03\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/77/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1Mywicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTMsImlhdCI6MTc4NTczMjI3OCwiZXhwIjoxNzg2MzM3MDc4fQ.mYeDGoGBF_l3STEqIznqH27I6TG0MRI75vGBXaGdbDw\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/77/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1Mywicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTMsImlhdCI6MTc4NTczMjI3OCwiZXhwIjoxNzg2MzM3MDc4fQ.mYeDGoGBF_l3STEqIznqH27I6TG0MRI75vGBXaGdbDw\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-03 04:44:38', '2026-08-03 04:44:38'),
(519, 13, 'rentalflow.realestate@gmail.com', 'James Mwangi', 'Payment Confirmation - KES 15,000.00', 'Hi Andrew Kibet,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 15,000.00\n- Category: Rent\n- Date: 2026-08-03\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/77/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1Mywicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTMsImlhdCI6MTc4NTczMjI3OCwiZXhwIjoxNzg2MzM3MDc4fQ.mYeDGoGBF_l3STEqIznqH27I6TG0MRI75vGBXaGdbDw\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/77/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1Mywicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTMsImlhdCI6MTc4NTczMjI3OCwiZXhwIjoxNzg2MzM3MDc4fQ.mYeDGoGBF_l3STEqIznqH27I6TG0MRI75vGBXaGdbDw\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-03 04:44:38', '2026-08-03 04:44:38'),
(520, 13, 'rentalflow.realestate@gmail.com', 'James Mwangi', 'Payment Receipt - Andrew Kibet - James Mwangi', 'Dear James Mwangi,\n\ntenant_name: Andrew Kibet\namount: 15,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-2278\ndate: 2026-08-03\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-03 04:44:38', '2026-08-03 04:44:38'),
(521, 0, 'josephokotch01@gmail.com', 'Joseph okotch', 'Welcome to RentaFlow - Set Up Your Account', 'Dear Joseph okotch,\r\n\r\nWelcome to RentaFlow! You have been registered as a caretaker.\r\n\r\nYour email: josephokotch01@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=7b8d7596e7ab956a43b571bd11a67de214983340b314a797456d39712dcf3f18\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'pending', NULL, '2026-08-03 08:51:20', '2026-08-03 08:51:20'),
(522, 7, 'cherutotracy12@gmail.com', 'Kiptoo Tracy Cheruto', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Kiptoo Tracy Cheruto,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: B1\r\nYour email: cherutotracy12@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=62cd5f47c80ca2f0ce70710d4e5a6d91e256f50b796655ad4b11cd210670ee66\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 08:59:28', '2026-08-03 08:59:28'),
(523, 7, 'mureithisheila968@gmail.com', 'Sheila Gathoni Mureithi', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Sheila Gathoni Mureithi,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: A3\r\nYour email: mureithisheila968@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=f0cba33cd84b2057dd660d74134687de094c5b65e557d3a16f45f4667e9eb1a9\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 10:02:26', '2026-08-03 10:02:26'),
(524, 7, 'bestadryan01@gmail.com', 'Adryan Kipkirui', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Adryan Kipkirui,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: A2\r\nYour email: bestadryan01@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=f8b436561b38c0b6398af0c207ab9ea0c8a52e5edc717ed2f30a8ef49e6b445e\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 10:15:08', '2026-08-03 10:15:08'),
(525, 0, 'ruthjepchumba731@gmail.com', 'Ruth jepchumba', 'Welcome to  - Set Up Your Account', 'Dear Ruth jepchumba,\r\n\r\nWelcome to ! We are excited to have you as our tenant.\r\n\r\nYour unit: \r\nYour email: ruthjepchumba731@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=3b8116713c6fd90e9a20cbe3aa961e6aed452e9a71e5e3a5798c9a6d7e38fe91\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 15:52:48', '2026-08-03 15:52:48'),
(526, 7, 'mbithesylvia404@gmail.com', 'Sylvia Mbithe Musyoka', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Sylvia Mbithe Musyoka,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: A5\r\nYour email: mbithesylvia404@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=1f1c53de94c60f208ac159f67a0008e7623258aef7678f34dd63befd072ff845\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 17:01:41', '2026-08-03 17:01:41'),
(527, 7, 'lennynjoroge325@gmail.com', 'Lenny John Mwangi Njoroge', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Lenny John Mwangi Njoroge,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: A4\r\nYour email: lennynjoroge325@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=716f4f1fcc880f1f3567cca31825ae4bf620877be32438a308aff3cba962c868\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 17:10:06', '2026-08-03 17:10:06'),
(528, 7, 'juliahkahora@gmail.com', 'Juliah Wangui Kahora', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Juliah Wangui Kahora,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: C4\r\nYour email: juliahkahora@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=1ff5ee1254cb79cd47aa0ab7b4d6e639c28d9678fcbd95da8d70326628e5b661\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 17:45:48', '2026-08-03 17:45:48'),
(529, 0, 'cherutotracy12@gmail.com', 'Kiptoo Tracy Cheruto', 'Welcome to  - Set Up Your Account', 'Dear Kiptoo Tracy Cheruto,\r\n\r\nWelcome to ! We are excited to have you as our tenant.\r\n\r\nYour unit: \r\nYour email: cherutotracy12@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=abb3de61b02c0142c00b5e2a1758b6fa01f98b9f643f8dc260abd67160d05ceb\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-03 18:54:31', '2026-08-03 18:54:31'),
(530, 7, 'juliahkahora@gmail.com', 'Juliah Wangui Kahora', 'Payment Confirmation - KES 6,000.00', 'Hi Juliah Wangui Kahora,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-05\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/86/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU5LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OSwiaWF0IjoxNzg1OTExMTEyLCJleHAiOjE3ODY1MTU5MTJ9.NkWv7bCH9_NKvzALZDWWfZT8htWluI2_QQ4bNsFNWz0\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/86/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU5LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OSwiaWF0IjoxNzg1OTExMTEyLCJleHAiOjE3ODY1MTU5MTJ9.NkWv7bCH9_NKvzALZDWWfZT8htWluI2_QQ4bNsFNWz0\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-05 06:25:12', '2026-08-05 06:25:12'),
(531, 7, 'bestadryan01@gmail.com', 'Adryan Kipkirui Langat', 'Payment Confirmation - KES 6,000.00', 'Hi Adryan Kipkirui Langat,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-05\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/80/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NiwiaWF0IjoxNzg1OTExOTMwLCJleHAiOjE3ODY1MTY3MzB9.zSj_zp4Bv8t2ePugNloqGN1Y-hL84GRfIFSkaJ2D37E\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/80/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NiwiaWF0IjoxNzg1OTExOTMwLCJleHAiOjE3ODY1MTY3MzB9.zSj_zp4Bv8t2ePugNloqGN1Y-hL84GRfIFSkaJ2D37E\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-05 06:38:50', '2026-08-05 06:38:50'),
(532, 7, 'memowinnie65@gmail.com', 'Langat Chepkemoi Wilfrida', 'Payment Confirmation - KES 6,000.00', 'Hi Adryan Kipkirui Langat,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-05\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/80/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NiwiaWF0IjoxNzg1OTExOTMwLCJleHAiOjE3ODY1MTY3MzB9.zSj_zp4Bv8t2ePugNloqGN1Y-hL84GRfIFSkaJ2D37E\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/80/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NiwiaWF0IjoxNzg1OTExOTMwLCJleHAiOjE3ODY1MTY3MzB9.zSj_zp4Bv8t2ePugNloqGN1Y-hL84GRfIFSkaJ2D37E\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-05 06:38:51', '2026-08-05 06:38:51'),
(533, 7, 'memowinnie65@gmail.com', 'Langat Chepkemoi Wilfrida', 'Payment Receipt - Adryan Kipkirui Langat - Langat Chepkemoi Wilfrida', 'Dear Langat Chepkemoi Wilfrida,\n\ntenant_name: Adryan Kipkirui Langat\namount: 6,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-1930\ndate: 2026-08-05\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-05 06:38:51', '2026-08-05 06:38:51'),
(534, 7, 'mureithisheila968@gmail.com', 'Sheila Gathoni Mureithi', 'Payment Confirmation - KES 6,000.00', 'Hi Sheila Gathoni Mureithi,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-05\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/79/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NSwiaWF0IjoxNzg1OTM3Mjc5LCJleHAiOjE3ODY1NDIwNzl9.rVOQwRv72Q9cSWTFNliVExGJyEqh3NlHqPfMzF-kcWk\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/79/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NSwiaWF0IjoxNzg1OTM3Mjc5LCJleHAiOjE3ODY1NDIwNzl9.rVOQwRv72Q9cSWTFNliVExGJyEqh3NlHqPfMzF-kcWk\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-05 13:41:19', '2026-08-05 13:41:19'),
(535, 7, 'jmureithi69@gmail.com', 'John Mureithi Macharia', 'Payment Confirmation - KES 6,000.00', 'Hi Sheila Gathoni Mureithi,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-05\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/79/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NSwiaWF0IjoxNzg1OTM3Mjc5LCJleHAiOjE3ODY1NDIwNzl9.rVOQwRv72Q9cSWTFNliVExGJyEqh3NlHqPfMzF-kcWk\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/79/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NSwiaWF0IjoxNzg1OTM3Mjc5LCJleHAiOjE3ODY1NDIwNzl9.rVOQwRv72Q9cSWTFNliVExGJyEqh3NlHqPfMzF-kcWk\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-05 13:41:20', '2026-08-05 13:41:20'),
(536, 7, 'jmureithi69@gmail.com', 'John Mureithi Macharia', 'Payment Receipt - Sheila Gathoni Mureithi - John Mureithi Macharia', 'Dear John Mureithi Macharia,\n\ntenant_name: Sheila Gathoni Mureithi\namount: 6,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-7279\ndate: 2026-08-05\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-05 13:41:20', '2026-08-05 13:41:20'),
(537, 7, 'sevelle376@gmail.com', 'Mark Njoroge Waweru', 'Payment Confirmation - KES 6,000.00', 'Hi Mark Njoroge Waweru,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-05\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/81/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQzLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0MywiaWF0IjoxNzg1OTU0MzU4LCJleHAiOjE3ODY1NTkxNTh9.8HF2km28RavboKW4vTowfnw-KcNHubi0Wi7mHynu-0s\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/81/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQzLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0MywiaWF0IjoxNzg1OTU0MzU4LCJleHAiOjE3ODY1NTkxNTh9.8HF2km28RavboKW4vTowfnw-KcNHubi0Wi7mHynu-0s\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-05 18:25:58', '2026-08-05 18:25:58'),
(538, 0, 'juliahkahora@gmail.com', 'Juliah Wangui Kahora', 'Welcome to  - Set Up Your Account', 'Dear Juliah Wangui Kahora,\r\n\r\nWelcome to ! We are excited to have you as our tenant.\r\n\r\nYour unit: \r\nYour email: juliahkahora@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=132382073780b1771e4fd3b36fbca7f422e7ff03d768323adc4b6fe938e548f0\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-05 18:34:05', '2026-08-05 18:34:05'),
(539, 0, 'bestadryan01@gmail.com', 'Adryan Kipkirui Langat', 'Password Reset Code - RentalFlow', 'Dear Adryan Kipkirui Langat,\n\nYou requested a password reset. Use the following code to reset your password:\n\nCode: 811804\n\nThis code expires in 15 minutes.\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nRentalFlow Team', 'sent', NULL, '2026-08-05 18:45:37', '2026-08-05 18:45:37'),
(540, 0, 'bestadryan01@gmail.com', 'Adryan Kipkirui Langat', 'Password Reset Code - RentalFlow', 'Dear Adryan Kipkirui Langat,\n\nYou requested a password reset. Use the following code to reset your password:\n\nCode: 105583\n\nThis code expires in 15 minutes.\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nRentalFlow Team', 'sent', NULL, '2026-08-05 18:47:16', '2026-08-05 18:47:16'),
(541, 7, 'cherutotracy12@gmail.com', 'Kiptoo Tracy Cheruto', 'Payment Confirmation - KES 6,000.00', 'Hi Kiptoo Tracy Cheruto,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-06\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/78/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NCwiaWF0IjoxNzg2MDE3NzMwLCJleHAiOjE3ODY2MjI1MzB9.o4Xx4CouhaOemgpv8WYhVWV3XnES4odsorZlEBIC5Xw\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/78/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NCwiaWF0IjoxNzg2MDE3NzMwLCJleHAiOjE3ODY2MjI1MzB9.o4Xx4CouhaOemgpv8WYhVWV3XnES4odsorZlEBIC5Xw\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-06 12:02:10', '2026-08-06 12:02:10'),
(542, 7, 'winsix97@gmail.com', 'Dr Kiptoo Wincer Kirui', 'Payment Confirmation - KES 6,000.00', 'Hi Kiptoo Tracy Cheruto,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-06\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/78/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NCwiaWF0IjoxNzg2MDE3NzMwLCJleHAiOjE3ODY2MjI1MzB9.o4Xx4CouhaOemgpv8WYhVWV3XnES4odsorZlEBIC5Xw\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/78/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NCwiaWF0IjoxNzg2MDE3NzMwLCJleHAiOjE3ODY2MjI1MzB9.o4Xx4CouhaOemgpv8WYhVWV3XnES4odsorZlEBIC5Xw\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-06 12:02:11', '2026-08-06 12:02:11'),
(543, 7, 'winsix97@gmail.com', 'Dr Kiptoo Wincer Kirui', 'Payment Receipt - Kiptoo Tracy Cheruto - Dr Kiptoo Wincer Kirui', 'Dear Dr Kiptoo Wincer Kirui,\n\ntenant_name: Kiptoo Tracy Cheruto\namount: 6,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-7730\ndate: 2026-08-06\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-06 12:02:12', '2026-08-06 12:02:12'),
(544, 13, 'duncankarenju750@gmail.com', 'Duncan Karenju Gathogo', 'Payment Confirmation - KES 7,500.00', 'Hi Duncan Karenju Gathogo,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 7,500.00\n- Category: Rent\n- Date: 2026-08-06\n- Current Balance: KES 0.00\n\n\n\nOr download your invoice directly:\n\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-06 15:54:37', '2026-08-06 15:54:37'),
(545, 13, 'karenjuduncan750@gmail.com', 'jane mwangi', 'Payment Confirmation - KES 7,500.00', 'Hi Duncan Karenju Gathogo,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 7,500.00\n- Category: Rent\n- Date: 2026-08-06\n- Current Balance: KES 0.00\n\n\n\nOr download your invoice directly:\n\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-06 15:54:38', '2026-08-06 15:54:38'),
(546, 13, 'karenjuduncan750@gmail.com', 'jane mwangi', 'Payment Receipt - Duncan Karenju Gathogo - jane mwangi', 'Dear jane mwangi,\n\ntenant_name: Duncan Karenju Gathogo\namount: 7,500.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-1677\ndate: 2026-08-06\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-06 15:54:38', '2026-08-06 15:54:38'),
(547, 13, 'bsclmr317222@spu.ac.ke', 'Njagi Nickson', 'Welcome to Rentii Suites - Set Up Your Account', 'Dear Njagi Nickson,\r\n\r\nWelcome to Rentii Suites! We are excited to have you as our tenant.\r\n\r\nYour unit: C3\r\nYour email: bsclmr317222@spu.ac.ke\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=2aa8972aefee08dd448c56e5691977ceb7cfa5df51bcdedfba8140cd243767d2\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-06 16:00:19', '2026-08-06 16:00:19'),
(548, 13, 'bsclmr317222@spu.ac.ke', 'Njagi Nickson', 'Payment Confirmation - KES 7,500.00', 'Hi Njagi Nickson,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 7,500.00\n- Category: Rent\n- Date: 2026-08-06\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/87/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo2MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NjAsImlhdCI6MTc4NjAzMjA4OSwiZXhwIjoxNzg2NjM2ODg5fQ.mk7bfOzFQJoyXi0sv1nvxAJoJ7pA_t_9ErlWfm1f77g\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/87/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo2MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NjAsImlhdCI6MTc4NjAzMjA4OSwiZXhwIjoxNzg2NjM2ODg5fQ.mk7bfOzFQJoyXi0sv1nvxAJoJ7pA_t_9ErlWfm1f77g\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-06 16:01:29', '2026-08-06 16:01:29'),
(549, 13, 'karenjuduncan750@gmail.com', 'jane mwangi', 'Payment Confirmation - KES 7,500.00', 'Hi Njagi Nickson,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 7,500.00\n- Category: Rent\n- Date: 2026-08-06\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/87/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo2MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NjAsImlhdCI6MTc4NjAzMjA4OSwiZXhwIjoxNzg2NjM2ODg5fQ.mk7bfOzFQJoyXi0sv1nvxAJoJ7pA_t_9ErlWfm1f77g\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/87/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo2MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NjAsImlhdCI6MTc4NjAzMjA4OSwiZXhwIjoxNzg2NjM2ODg5fQ.mk7bfOzFQJoyXi0sv1nvxAJoJ7pA_t_9ErlWfm1f77g\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-06 16:01:29', '2026-08-06 16:01:29'),
(550, 13, 'karenjuduncan750@gmail.com', 'jane mwangi', 'Payment Receipt - Njagi Nickson - jane mwangi', 'Dear jane mwangi,\n\ntenant_name: Njagi Nickson\namount: 7,500.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-2089\ndate: 2026-08-06\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-06 16:01:30', '2026-08-06 16:01:30'),
(551, 7, 'billyndume2@gmail.com', 'Billy Ndume Muruu', 'Payment Confirmation - KES 6,000.00', 'Hi Billy Ndume Muruu,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-07\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/82/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NSwiaWF0IjoxNzg2MDgwMDE4LCJleHAiOjE3ODY2ODQ4MTh9.WAgRrijSNNBpJN3CaEtw69eJd4go90pq5n4t-YrU4zM\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/82/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NSwiaWF0IjoxNzg2MDgwMDE4LCJleHAiOjE3ODY2ODQ4MTh9.WAgRrijSNNBpJN3CaEtw69eJd4go90pq5n4t-YrU4zM\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-07 05:20:18', '2026-08-07 05:20:18'),
(552, 7, 'shadrackmuruu10@gmail.com', 'Shadrack Ndume', 'Payment Confirmation - KES 6,000.00', 'Hi Billy Ndume Muruu,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-07\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/82/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NSwiaWF0IjoxNzg2MDgwMDE4LCJleHAiOjE3ODY2ODQ4MTh9.WAgRrijSNNBpJN3CaEtw69eJd4go90pq5n4t-YrU4zM\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/82/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NSwiaWF0IjoxNzg2MDgwMDE4LCJleHAiOjE3ODY2ODQ4MTh9.WAgRrijSNNBpJN3CaEtw69eJd4go90pq5n4t-YrU4zM\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-07 05:20:20', '2026-08-07 05:20:20'),
(553, 7, 'shadrackmuruu10@gmail.com', 'Shadrack Ndume', 'Payment Receipt - Billy Ndume Muruu - Shadrack Ndume', 'Dear Shadrack Ndume,\n\ntenant_name: Billy Ndume Muruu\namount: 6,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-0018\ndate: 2026-08-07\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-07 05:20:20', '2026-08-07 05:20:20'),
(554, 13, 'duncankarenju750@gmail.com', 'Duncan Karenju Gathogo', 'Billing Notification - Duncan Karenju Gathogo', 'Dear Duncan Karenju Gathogo,\n\ntenant: Duncan Karenju Gathogo\namount: 7,500.00\nmonth: August 2026\nproperty: Rentii Suites\nhouse: C1\nbalance: 7,500.00\ndate: 2026-08-08\ninvoice_url: https://rentalflow.co.ke/api/bills/88/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTAsImlhdCI6MTc4NjE4OTI4NSwiZXhwIjoxNzg2Nzk0MDg1fQ.KTgwHsNosCf_LNbXJrrBtXfTwdbHn4MXO-9v0gxunIQ\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-08 11:41:25', '2026-08-08 11:41:25'),
(555, 13, 'karenjuduncan750@gmail.com', 'jane mwangi', 'Billing Notification - jane mwangi', 'Dear jane mwangi,\n\ntenant: Duncan Karenju Gathogo\namount: 7,500.00\nmonth: August 2026\nproperty: Rentii Suites\nhouse: C1\nbalance: 7,500.00\ndate: 2026-08-08\ninvoice_url: https://rentalflow.co.ke/api/bills/88/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1MCwicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTAsImlhdCI6MTc4NjE4OTI4NSwiZXhwIjoxNzg2Nzk0MDg1fQ.KTgwHsNosCf_LNbXJrrBtXfTwdbHn4MXO-9v0gxunIQ\nrecipient_name: jane mwangi\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-08 11:41:26', '2026-08-08 11:41:26');
INSERT INTO `email_logs` (`id`, `owner_id`, `to_email`, `to_name`, `subject`, `body`, `status`, `error`, `sent_at`, `created_at`) VALUES
(556, 13, 'mainapetermwangi2017@gmail.com', 'Andrew Kibet', 'Billing Notification - Andrew Kibet', 'Dear Andrew Kibet,\n\ntenant: Andrew Kibet\namount: 7,500.00\nmonth: August 2026\nproperty: Rentii Suites\nhouse: C2\nbalance: 7,500.00\ndate: 2026-08-08\ninvoice_url: https://rentalflow.co.ke/api/bills/89/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1Mywicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTMsImlhdCI6MTc4NjE4OTI4NiwiZXhwIjoxNzg2Nzk0MDg2fQ.7ZsUsmcam0XBmeTOFmdlbnM9eWv53Wmn7jTYtqru9zI\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-08 11:41:26', '2026-08-08 11:41:26'),
(557, 13, 'rentalflow.realestate@gmail.com', 'James Mwangi', 'Billing Notification - James Mwangi', 'Dear James Mwangi,\n\ntenant: Andrew Kibet\namount: 7,500.00\nmonth: August 2026\nproperty: Rentii Suites\nhouse: C2\nbalance: 7,500.00\ndate: 2026-08-08\ninvoice_url: https://rentalflow.co.ke/api/bills/89/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6MTMsImFjdG9yX2lkIjo1Mywicm9sZSI6InRlbmFudCIsInRlbmFudF9pZCI6NTMsImlhdCI6MTc4NjE4OTI4NiwiZXhwIjoxNzg2Nzk0MDg2fQ.7ZsUsmcam0XBmeTOFmdlbnM9eWv53Wmn7jTYtqru9zI\nrecipient_name: James Mwangi\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-08 11:41:27', '2026-08-08 11:41:27'),
(558, 7, 'faresnthiwa@gmail.com', 'Fares Nthiwa Mutua', 'Welcome to RentaFlow - Set Up Your Account', 'Dear Fares Nthiwa Mutua,\r\n\r\nWelcome to RentaFlow! You have been registered as a caretaker.\r\n\r\nYour email: faresnthiwa@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=df850d1d0ca5c1db62fd2e2e3434e937557c0385ad110c40902e35ff938adc46\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-09 06:07:57', '2026-08-09 06:07:57'),
(559, 7, 'michelkavesh01@gmail.com', 'Michel Kavemba Kilo', 'Welcome to Jakes Apartments - Set Up Your Account', 'Dear Michel Kavemba Kilo,\r\n\r\nWelcome to Jakes Apartments! We are excited to have you as our tenant.\r\n\r\nYour unit: A10\r\nYour email: michelkavesh01@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=78b2cf9a54a811ac72e01087a9ae5501707f0a8c2eb85b2b130db7a8f8d1c3db\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-09 06:37:54', '2026-08-09 06:37:54'),
(560, 7, 'michelkavesh01@gmail.com', 'Michel Kavemba Kilo', 'Payment Confirmation - KES 14,000.00', 'Hi Michel Kavemba Kilo,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 14,000.00\n- Category: Rent\n- Date: 2026-08-09\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/90/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MSwiaWF0IjoxNzg2MjU3NTU3LCJleHAiOjE3ODY4NjIzNTd9.PD5sNEok1fOTFh1GOHbXsW3cEBM_pUhj1LT-tg2XbWE\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/90/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MSwiaWF0IjoxNzg2MjU3NTU3LCJleHAiOjE3ODY4NjIzNTd9.PD5sNEok1fOTFh1GOHbXsW3cEBM_pUhj1LT-tg2XbWE\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-09 06:39:17', '2026-08-09 06:39:17'),
(561, 7, 'kiokogeoffrey233@gmail.com', 'Geoffrey Kilo Kioko', 'Payment Confirmation - KES 14,000.00', 'Hi Michel Kavemba Kilo,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 14,000.00\n- Category: Rent\n- Date: 2026-08-09\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/90/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MSwiaWF0IjoxNzg2MjU3NTU3LCJleHAiOjE3ODY4NjIzNTd9.PD5sNEok1fOTFh1GOHbXsW3cEBM_pUhj1LT-tg2XbWE\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/90/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MSwiaWF0IjoxNzg2MjU3NTU3LCJleHAiOjE3ODY4NjIzNTd9.PD5sNEok1fOTFh1GOHbXsW3cEBM_pUhj1LT-tg2XbWE\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-09 06:39:17', '2026-08-09 06:39:17'),
(562, 7, 'kiokogeoffrey233@gmail.com', 'Geoffrey Kilo Kioko', 'Payment Receipt - Michel Kavemba Kilo - Geoffrey Kilo Kioko', 'Dear Geoffrey Kilo Kioko,\n\ntenant_name: Michel Kavemba Kilo\namount: 14,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-7557\ndate: 2026-08-09\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-09 06:39:17', '2026-08-09 06:39:17'),
(563, 0, 'mbithesylvia404@gmail.com', 'Sylvia Mbithe Musyoka', 'Welcome to  - Set Up Your Account', 'Dear Sylvia Mbithe Musyoka,\r\n\r\nWelcome to ! We are excited to have you as our tenant.\r\n\r\nYour unit: \r\nYour email: mbithesylvia404@gmail.com\r\n\r\nTo activate your account and set your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=0bef149327af86dcd3e5e693d6fb269665b3a0cd1581fcb7cc6c565c657554b5\r\n\r\nThis link will expire in 48 hours. If you need a new link, please contact your property manager.\r\n\r\nBest regards,\r\nRentaFlow Team', 'sent', NULL, '2026-08-09 17:58:39', '2026-08-09 17:58:39'),
(564, 7, 'zaharazainabu09@gmail.com', 'Zahra Zainabu', 'Payment Confirmation - KES 6,000.00', 'Hi Zahra Zainabu,\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES 6,000.00\n- Category: Rent\n- Date: 2026-08-11\n- Current Balance: KES 0.00\n\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/70/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NiwiaWF0IjoxNzg2NDQ1NDIxLCJleHAiOjE3ODcwNTAyMjF9.N2wIN1kJnpfZSnELatgOaqLnRu7JiUGltpjpykC8p7Q\n\n\n\nOr download your invoice directly:\nhttps://rentalflow.co.ke/api/bills/70/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NiwiaWF0IjoxNzg2NDQ1NDIxLCJleHAiOjE3ODcwNTAyMjF9.N2wIN1kJnpfZSnELatgOaqLnRu7JiUGltpjpykC8p7Q\n\nThank you for your payment!\n\nBest regards,\nProperty Management', 'sent', NULL, '2026-08-11 10:50:21', '2026-08-11 10:50:21'),
(565, 7, 'zaharazainabu09@gmail.com', 'Zainab azadin', 'Payment Receipt - Zahra Zainabu - Zainab azadin', 'Dear Zainab azadin,\n\ntenant_name: Zahra Zainabu\namount: 6,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-5421\ndate: 2026-08-11\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-11 10:50:22', '2026-08-11 10:50:22'),
(566, 7, 'mbithesylvia404@gmail.com', 'Sylvia Mbithe Musyoka', 'Payment Successfully Received - KES 6,500.00', 'Dear Sylvia Mbithe Musyoka,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 6,500.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-12\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/84/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NywiaWF0IjoxNzg2NTE2MjA5LCJleHAiOjE3ODcxMjEwMDl9.5nZGqJfyt7R0XeAJ2Js68LrLQLts6CKy37qYQe-01B4\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/84/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NywiaWF0IjoxNzg2NTE2MjA5LCJleHAiOjE3ODcxMjEwMDl9.5nZGqJfyt7R0XeAJ2Js68LrLQLts6CKy37qYQe-01B4\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-12 06:30:09', '2026-08-12 06:30:09'),
(567, 7, 'wambuaelizabeth413@gmail.com', 'Elizabeth Nduku', 'Payment Successfully Received - KES 6,500.00', 'Dear Sylvia Mbithe Musyoka,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 6,500.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-12\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/84/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NywiaWF0IjoxNzg2NTE2MjA5LCJleHAiOjE3ODcxMjEwMDl9.5nZGqJfyt7R0XeAJ2Js68LrLQLts6CKy37qYQe-01B4\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/84/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NywiaWF0IjoxNzg2NTE2MjA5LCJleHAiOjE3ODcxMjEwMDl9.5nZGqJfyt7R0XeAJ2Js68LrLQLts6CKy37qYQe-01B4\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-12 06:30:10', '2026-08-12 06:30:10'),
(568, 7, 'wambuaelizabeth413@gmail.com', 'Elizabeth Nduku', 'Payment Receipt - Sylvia Mbithe Musyoka - Elizabeth Nduku', 'Dear Elizabeth Nduku,\n\ntenant_name: Sylvia Mbithe Musyoka\namount: 6,500.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-6209\ndate: 2026-08-12\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-12 06:30:10', '2026-08-12 06:30:10'),
(569, 7, 'lennynjoroge325@gmail.com', 'Lenny John Mwangi Njoroge', 'Payment Successfully Received - KES 7,000.00', 'Dear Lenny John Mwangi Njoroge,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 7,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-12\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/85/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OCwiaWF0IjoxNzg2NTE2MzM1LCJleHAiOjE3ODcxMjExMzV9.ppN8fQU-qYzo96NPPaeYIhyScdHfV8I4mcm6XoUBOWw\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/85/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OCwiaWF0IjoxNzg2NTE2MzM1LCJleHAiOjE3ODcxMjExMzV9.ppN8fQU-qYzo96NPPaeYIhyScdHfV8I4mcm6XoUBOWw\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-12 06:32:15', '2026-08-12 06:32:15'),
(570, 7, 'milesnjo@gmail.com', 'Naftaly Njoroge', 'Payment Successfully Received - KES 7,000.00', 'Dear Lenny John Mwangi Njoroge,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 7,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-12\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/85/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OCwiaWF0IjoxNzg2NTE2MzM1LCJleHAiOjE3ODcxMjExMzV9.ppN8fQU-qYzo96NPPaeYIhyScdHfV8I4mcm6XoUBOWw\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/85/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OCwiaWF0IjoxNzg2NTE2MzM1LCJleHAiOjE3ODcxMjExMzV9.ppN8fQU-qYzo96NPPaeYIhyScdHfV8I4mcm6XoUBOWw\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-12 06:32:15', '2026-08-12 06:32:15'),
(571, 7, 'milesnjo@gmail.com', 'Naftaly Njoroge', 'Payment Receipt - Lenny John Mwangi Njoroge - Naftaly Njoroge', 'Dear Naftaly Njoroge,\n\ntenant_name: Lenny John Mwangi Njoroge\namount: 7,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-6335\ndate: 2026-08-12\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-12 06:32:15', '2026-08-12 06:32:15'),
(572, 7, 'stphn0445@gmail.com', 'John Stephen', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear John Stephen,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: C6\r\nRegistered Email: stphn0445@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=c36eef28c08b85d667e8f6e196d4141ccc2e4f90e0e5d2343451ba15e62c319a\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-08-20 02:43:12', '2026-08-20 02:43:12'),
(573, 7, 'pierrahprecious@gmail.com', 'Precious Ntinyari Mati', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Precious Ntinyari Mati,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: C5\r\nRegistered Email: pierrahprecious@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=fea50b3be426914b4c5a536ecec00d9a380c9f5c166aac86fa5bf70450933d60\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-08-20 18:06:50', '2026-08-20 18:06:50'),
(574, 7, 'israeldylan001@gmail.com', 'Dylan Israel', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Dylan Israel,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: B8\r\nRegistered Email: israeldylan001@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=7ab407074c926008bfb7818b4fea3fac7ea9e31d7a9fd60cd6e57bef9734141a\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-08-22 07:31:06', '2026-08-22 07:31:06'),
(575, 7, 'biammokua@gmail.com', 'Biam Mokua Onsomu', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Biam Mokua Onsomu,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: C8\r\nRegistered Email: biammokua@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=b5df5d908c51f27d589d973eb225e198520b79a7e2f03a9cb4e22be7a595d126\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-08-22 15:23:35', '2026-08-22 15:23:35'),
(576, 7, 'biammokua@gmail.com', 'Biam Mokua Onsomu', 'Payment Successfully Received - KES 7,000.00', 'Dear Biam Mokua Onsomu,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 7,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-22\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/94/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NSwiaWF0IjoxNzg3NDEyMjgyLCJleHAiOjE3ODgwMTcwODJ9.aoI6Zh7_mrayY6F5kmA8Rp3tAcu72reUMhE8gMmSutY\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/94/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NSwiaWF0IjoxNzg3NDEyMjgyLCJleHAiOjE3ODgwMTcwODJ9.aoI6Zh7_mrayY6F5kmA8Rp3tAcu72reUMhE8gMmSutY\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-22 15:24:42', '2026-08-22 15:24:42'),
(577, 7, 'denisotisoomollo@gmail.com', 'Denis Otiso Omollo', 'Payment Successfully Received - KES 7,000.00', 'Dear Biam Mokua Onsomu,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 7,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-22\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/94/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NSwiaWF0IjoxNzg3NDEyMjgyLCJleHAiOjE3ODgwMTcwODJ9.aoI6Zh7_mrayY6F5kmA8Rp3tAcu72reUMhE8gMmSutY\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/94/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NSwiaWF0IjoxNzg3NDEyMjgyLCJleHAiOjE3ODgwMTcwODJ9.aoI6Zh7_mrayY6F5kmA8Rp3tAcu72reUMhE8gMmSutY\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-22 15:24:43', '2026-08-22 15:24:43'),
(578, 7, 'denisotisoomollo@gmail.com', 'Denis Otiso Omollo', 'Payment Receipt - Biam Mokua Onsomu - Denis Otiso Omollo', 'Dear Denis Otiso Omollo,\n\ntenant_name: Biam Mokua Onsomu\namount: 7,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-2282\ndate: 2026-08-22\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-22 15:24:43', '2026-08-22 15:24:43'),
(579, 0, 'biammokua@gmail.com', 'Biam Mokua Onsomu', 'Welcome to  - Activate Your Tenant Account', 'Dear Biam Mokua Onsomu,\r\n\r\nWelcome to .\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: \r\nUnit/House: \r\nRegistered Email: biammokua@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=ff10d756556189061a87b08cd1ba02a95dc45ac9da682536415bf3cb5aa08dc3\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\n', 'sent', NULL, '2026-08-22 15:48:48', '2026-08-22 15:48:48'),
(580, 0, 'bestadryan01@gmail.com', 'Adryan Kipkirui Langat', 'Password Reset Code - RentalFlow', 'Dear Adryan Kipkirui Langat,\n\nYou requested a password reset. Use the following code to reset your password:\n\nCode: 738923\n\nThis code expires in 15 minutes.\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nRentalFlow Team', 'sent', NULL, '2026-08-22 15:51:41', '2026-08-22 15:51:41'),
(581, 7, 'adrianmbai01@gmail.com', 'Adrian Mbai', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Adrian Mbai,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: B3\r\nRegistered Email: adrianmbai01@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=7851a3fe3df02ebb53874e626c6e0af28e17b28f1efaade76f1c607d02800170\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-08-28 12:18:44', '2026-08-28 12:18:44'),
(582, 7, 'albertkariuki860@gmail.com', 'Albert Kariuki Njau', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Albert Kariuki Njau,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: C1\r\nRegistered Email: albertkariuki860@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=f7dbb72893bed570cb3a0bdbf9102e7f5b85061c1821b544d3d5bf964ed90106\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-08-28 12:42:05', '2026-08-28 12:42:05'),
(583, 7, 'albertkariuki860@gmail.com', 'Albert Kariuki Njau', 'Payment Successfully Received - KES 6,500.00', 'Dear Albert Kariuki Njau,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 6,500.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-28\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/96/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NywiaWF0IjoxNzg3OTIxMTI0LCJleHAiOjE3ODg1MjU5MjR9.HNdA1Esrl5mtoo4APGddjaM05cA26MpPz-0WgiyM1zE\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/96/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NywiaWF0IjoxNzg3OTIxMTI0LCJleHAiOjE3ODg1MjU5MjR9.HNdA1Esrl5mtoo4APGddjaM05cA26MpPz-0WgiyM1zE\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-28 12:45:24', '2026-08-28 12:45:24'),
(584, 7, 'ismaelosmanmohamed4@gmail.com', 'Ismael Osman Mohamed', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Ismael Osman Mohamed,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: B6\r\nRegistered Email: ismaelosmanmohamed4@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=efb1161a6f9aebeb77bfa38415e5fa8347e2d35d066c89bfec351927c87117ed\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-08-28 13:00:49', '2026-08-28 13:00:49'),
(585, 7, 'israeldylan001@gmail.com', 'Dylan Israel', 'Payment Successfully Received - KES 14,000.00', 'Dear Dylan Israel,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 14,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-28\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-28 13:14:03', '2026-08-28 13:14:03'),
(586, 7, 'stanleytonui1@gmail.com', 'Stanley Tonui', 'Payment Successfully Received - KES 14,000.00', 'Dear Dylan Israel,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 14,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-08-28\r\nCurrent Account Balance: KES 0.00\r\n\r\n\n\nDownload Invoice:\nhttps://rentalflow.co.ke/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ\n\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\nhttps://rentalflow.co.ke/api/bills/93/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg3OTIyODQzLCJleHAiOjE3ODg1Mjc2NDN9.hiFgYTfOWTD10EUKCc1--m0CBeFnFuZGvwe4ihWKYvQ\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-08-28 13:14:04', '2026-08-28 13:14:04'),
(587, 7, 'stanleytonui1@gmail.com', 'Stanley Tonui', 'Payment Receipt - Dylan Israel - Stanley Tonui', 'Dear Stanley Tonui,\n\ntenant_name: Dylan Israel\namount: 14,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-2843\ndate: 2026-08-28\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-08-28 13:14:04', '2026-08-28 13:14:04'),
(588, 7, 'preciousunicious7@gmail.com', 'Precious Nkatha Murithi', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Precious Nkatha Murithi,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: A9\r\nRegistered Email: preciousunicious7@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=66d7c5c493149464fb1c07d213556226e66897ad6a5c46c0dddb59792e0d7052\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-08-30 19:07:43', '2026-08-30 19:07:43'),
(589, 7, 'gkimesis@gmail.com', 'Grace Jepkemoi Kimesis', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Grace Jepkemoi Kimesis,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: A6\r\nRegistered Email: gkimesis@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=f0446746ed65e08229b65896b8deb5328e4a9d38c126e31bdb6ff657b30fc4b4\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-09-01 06:01:29', '2026-09-01 06:01:29'),
(590, 0, 'biammokua@gmail.com', 'Biam Mokua Onsomu', 'Welcome to  - Activate Your Tenant Account', 'Dear Biam Mokua Onsomu,\r\n\r\nWelcome to .\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: \r\nUnit/House: \r\nRegistered Email: biammokua@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=d2b0946dd2bdab357d54217ec3819a6e5a899cead4dd77ae5e299fe9d0f34f29\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\n', 'sent', NULL, '2026-09-01 06:31:09', '2026-09-01 06:31:09'),
(591, 0, 'biammokua@gmail.com', 'Biam Mokua Onsomu', 'Welcome to  - Activate Your Tenant Account', 'Dear Biam Mokua Onsomu,\r\n\r\nWelcome to .\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: \r\nUnit/House: \r\nRegistered Email: biammokua@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=c14286795c639583af235b6e50881e1ff0ea2ab659a70cb4d99fdc4057ae4006\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\n', 'sent', NULL, '2026-09-01 06:33:34', '2026-09-01 06:33:34'),
(592, 7, 'adrianmbai01@gmail.com', 'Adrian Mbai', 'Payment Successfully Received - KES 12,000.00', 'Dear Adrian Mbai,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 12,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-09-01\r\nCurrent Account Balance: KES 0.00\r\n\r\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\n\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-09-01 09:18:44', '2026-09-01 09:18:44'),
(593, 7, 'cosmbai@gmail.com', 'Cosmus Mbai Mutuku', 'Payment Successfully Received - KES 12,000.00', 'Dear Adrian Mbai,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 12,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-09-01\r\nCurrent Account Balance: KES 0.00\r\n\r\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\n\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-09-01 09:18:44', '2026-09-01 09:18:44'),
(594, 7, 'cosmbai@gmail.com', 'Cosmus Mbai Mutuku', 'Payment Receipt - Adrian Mbai - Cosmus Mbai Mutuku', 'Dear Cosmus Mbai Mutuku,\n\ntenant_name: Adrian Mbai\namount: 12,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-4324\ndate: 2026-09-01\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-09-01 09:18:45', '2026-09-01 09:18:45'),
(595, 7, 'pierrahprecious@gmail.com', 'Precious Ntinyari Mati', 'Payment Successfully Received - KES 7,000.00', 'Dear Precious Ntinyari Mati,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 7,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-09-01\r\nCurrent Account Balance: KES 0.00\r\n\r\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\n\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-09-01 09:26:34', '2026-09-01 09:26:34'),
(596, 7, 'liliankiende20@gmail.com', 'Evangeline Kinya', 'Payment Successfully Received - KES 7,000.00', 'Dear Precious Ntinyari Mati,\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES 7,000.00\r\nPayment Category: Rent\r\nPayment Date: 2026-09-01\r\nCurrent Account Balance: KES 0.00\r\n\r\n\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\n\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', 'sent', NULL, '2026-09-01 09:26:35', '2026-09-01 09:26:35'),
(597, 7, 'liliankiende20@gmail.com', 'Evangeline Kinya', 'Payment Receipt - Precious Ntinyari Mati - Evangeline Kinya', 'Dear Evangeline Kinya,\n\ntenant_name: Precious Ntinyari Mati\namount: 7,000.00\nbalance: 0.00\nproperty: \nunit: \nreceipt: RCP-2026-4794\ndate: 2026-09-01\n\nBest regards,\nRentalFlow', 'sent', NULL, '2026-09-01 09:26:35', '2026-09-01 09:26:35'),
(598, 7, 'julietwamucii@gmail.com', 'Juliet Wamucii Nyaga', 'Welcome to Jakes Apartments - Activate Your Tenant Account', 'Dear Juliet Wamucii Nyaga,\r\n\r\nWelcome to Jakes Apartments.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: Jakes Apartments\r\nUnit/House: C10\r\nRegistered Email: julietwamucii@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=2baddb1425c181c682d4e0d13edcbb9a313bbe09dbf64dacb2ce630365cb43af\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\nJakes Apartments', 'sent', NULL, '2026-09-01 09:56:32', '2026-09-01 09:56:32');
INSERT INTO `email_logs` (`id`, `owner_id`, `to_email`, `to_name`, `subject`, `body`, `status`, `error`, `sent_at`, `created_at`) VALUES
(599, 0, 'biammokua@gmail.com', 'Biam Mokua Onsomu', 'Welcome to  - Activate Your Tenant Account', 'Dear Biam Mokua Onsomu,\r\n\r\nWelcome to .\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: \r\nUnit/House: \r\nRegistered Email: biammokua@gmail.com\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\nhttps://rentalflow.co.ke/setup-password?token=6fb010cec4a3c71dccd098ba09d11b963542b9e6370af062831e8c46c48e7f51\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\n', 'sent', NULL, '2026-09-01 11:02:03', '2026-09-01 11:02:03'),
(600, 7, 'zaharazainabu09@gmail.com', 'Zahra Zainabu', 'Your September 2026 invoice for Jakes Apartments A1 - KES 13,000.00', 'Dear Zahra Zainabu,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A1\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/101/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NiwiaWF0IjoxNzg4MjYyMDQxLCJleHAiOjE3ODg4NjY4NDF9.nUfuqIz-K05-kt95DtbCngp8QmhM8rr4OEODpGvFjAg\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(601, 7, 'zaharazainabu09@gmail.com', 'Zainab azadin', 'Your September 2026 invoice for Jakes Apartments A1 - KES 13,000.00', 'Dear Zainab azadin,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A1\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/101/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NiwiaWF0IjoxNzg4MjYyMDQxLCJleHAiOjE3ODg4NjY4NDF9.nUfuqIz-K05-kt95DtbCngp8QmhM8rr4OEODpGvFjAg\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:21', '2026-09-01 11:27:21'),
(602, 7, 'michelkavesh01@gmail.com', 'Michel Kavemba Kilo', 'Your September 2026 invoice for Jakes Apartments A10 - KES 14,000.00', 'Dear Michel Kavemba Kilo,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A10\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,000.00\r\n    Balance:         KES 14,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/102/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MSwiaWF0IjoxNzg4MjYyMDQyLCJleHAiOjE3ODg4NjY4NDJ9.w-QUsGgH-qZz34unfzPY6mcYpAlvtZrd64hse98rS28\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:22', '2026-09-01 11:27:22'),
(603, 7, 'kiokogeoffrey233@gmail.com', 'Geoffrey Kilo Kioko', 'Your September 2026 invoice for Jakes Apartments A10 - KES 14,000.00', 'Dear Geoffrey Kilo Kioko,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A10\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,000.00\r\n    Balance:         KES 14,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/102/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MSwiaWF0IjoxNzg4MjYyMDQyLCJleHAiOjE3ODg4NjY4NDJ9.w-QUsGgH-qZz34unfzPY6mcYpAlvtZrd64hse98rS28\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:22', '2026-09-01 11:27:22'),
(604, 7, 'bestadryan01@gmail.com', 'Adryan Kipkirui Langat', 'Your September 2026 invoice for Jakes Apartments A2 - KES 13,000.00', 'Dear Adryan Kipkirui Langat,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A2\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/103/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NiwiaWF0IjoxNzg4MjYyMDQyLCJleHAiOjE3ODg4NjY4NDJ9.DXGlzhK1uqcv2W5VwB2WKukptp5RfjH_iawL7sqTJ-E\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:22', '2026-09-01 11:27:22'),
(605, 7, 'memowinnie65@gmail.com', 'Langat Chepkemoi Wilfrida', 'Your September 2026 invoice for Jakes Apartments A2 - KES 13,000.00', 'Dear Langat Chepkemoi Wilfrida,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A2\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/103/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NiwiaWF0IjoxNzg4MjYyMDQyLCJleHAiOjE3ODg4NjY4NDJ9.DXGlzhK1uqcv2W5VwB2WKukptp5RfjH_iawL7sqTJ-E\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:22', '2026-09-01 11:27:22'),
(606, 7, 'mureithisheila968@gmail.com', 'Sheila Gathoni Mureithi', 'Your September 2026 invoice for Jakes Apartments A3 - KES 13,000.00', 'Dear Sheila Gathoni Mureithi,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A3\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/104/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NSwiaWF0IjoxNzg4MjYyMDQyLCJleHAiOjE3ODg4NjY4NDJ9.f6IosEZXbYg3Oz3OHPq_Q5Eg26yvBpbCT-g6Gubvg74\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:22', '2026-09-01 11:27:22'),
(607, 7, 'jmureithi69@gmail.com', 'John Mureithi Macharia', 'Your September 2026 invoice for Jakes Apartments A3 - KES 13,000.00', 'Dear John Mureithi Macharia,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A3\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/104/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NSwiaWF0IjoxNzg4MjYyMDQyLCJleHAiOjE3ODg4NjY4NDJ9.f6IosEZXbYg3Oz3OHPq_Q5Eg26yvBpbCT-g6Gubvg74\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:23', '2026-09-01 11:27:23'),
(608, 7, 'lennynjoroge325@gmail.com', 'Lenny John Mwangi Njoroge', 'Your September 2026 invoice for Jakes Apartments A4 - KES 14,500.00', 'Dear Lenny John Mwangi Njoroge,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A4\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,500.00\r\n    Balance:         KES 14,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/105/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OCwiaWF0IjoxNzg4MjYyMDQzLCJleHAiOjE3ODg4NjY4NDN9.vS5VeGPXh7PIV8vHmRFdHVhw-gVvqwHWKDsoGBEs1gw\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:23', '2026-09-01 11:27:23'),
(609, 7, 'milesnjo@gmail.com', 'Naftaly Njoroge', 'Your September 2026 invoice for Jakes Apartments A4 - KES 14,500.00', 'Dear Naftaly Njoroge,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A4\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,500.00\r\n    Balance:         KES 14,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/105/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OCwiaWF0IjoxNzg4MjYyMDQzLCJleHAiOjE3ODg4NjY4NDN9.vS5VeGPXh7PIV8vHmRFdHVhw-gVvqwHWKDsoGBEs1gw\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:23', '2026-09-01 11:27:23'),
(610, 7, 'mbithesylvia404@gmail.com', 'Sylvia Mbithe Musyoka', 'Your September 2026 invoice for Jakes Apartments A5 - KES 13,000.00', 'Dear Sylvia Mbithe Musyoka,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A5\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/106/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NywiaWF0IjoxNzg4MjYyMDQzLCJleHAiOjE3ODg4NjY4NDN9.Fjay_rd-KIRp_6qa8UDa8CPhw9sYHtoNaGvYhM9H4Ms\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:23', '2026-09-01 11:27:23'),
(611, 7, 'wambuaelizabeth413@gmail.com', 'Elizabeth Nduku', 'Your September 2026 invoice for Jakes Apartments A5 - KES 13,000.00', 'Dear Elizabeth Nduku,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A5\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/106/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NywiaWF0IjoxNzg4MjYyMDQzLCJleHAiOjE3ODg4NjY4NDN9.Fjay_rd-KIRp_6qa8UDa8CPhw9sYHtoNaGvYhM9H4Ms\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:23', '2026-09-01 11:27:23'),
(612, 7, 'bichiinicole@gmail.com', 'Bichii Nicole Cheruto', 'Your September 2026 invoice for Jakes Apartments A7 - KES 14,000.00', 'Dear Bichii Nicole Cheruto,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A7\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,000.00\r\n    Balance:         KES 14,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/107/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MSwiaWF0IjoxNzg4MjYyMDQzLCJleHAiOjE3ODg4NjY4NDN9.LGjdv38ku_wh4XW7ZA96clxU4gFsoFfyjk72KtbyDI8\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:23', '2026-09-01 11:27:23'),
(613, 7, 'kkipkoech002@gmail.com', 'Kevin Kipkoech', 'Your September 2026 invoice for Jakes Apartments A7 - KES 14,000.00', 'Dear Kevin Kipkoech,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A7\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,000.00\r\n    Balance:         KES 14,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/107/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUxLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MSwiaWF0IjoxNzg4MjYyMDQzLCJleHAiOjE3ODg4NjY4NDN9.LGjdv38ku_wh4XW7ZA96clxU4gFsoFfyjk72KtbyDI8\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:24', '2026-09-01 11:27:24'),
(614, 7, 'wanjikuaustin84@gmail.com', 'Austin Kariuki', 'Your September 2026 invoice for Jakes Apartments A8 - KES 14,000.00', 'Dear Austin Kariuki,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A8\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,000.00\r\n    Balance:         KES 14,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/108/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUyLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MiwiaWF0IjoxNzg4MjYyMDQ0LCJleHAiOjE3ODg4NjY4NDR9.lD6HuVtX2dOuv2kZqa-AF09ZOVM8a36nsngNdRLmcVI\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:24', '2026-09-01 11:27:24'),
(615, 7, 'valentinenj@ueab.ac.ke', 'Valentine Wanjiru Njeri', 'Your September 2026 invoice for Jakes Apartments A8 - KES 14,000.00', 'Dear Valentine Wanjiru Njeri,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A8\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,000.00\r\n    Balance:         KES 14,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/108/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjUyLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1MiwiaWF0IjoxNzg4MjYyMDQ0LCJleHAiOjE3ODg4NjY4NDR9.lD6HuVtX2dOuv2kZqa-AF09ZOVM8a36nsngNdRLmcVI\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:24', '2026-09-01 11:27:24'),
(616, 7, 'preciousunicious7@gmail.com', 'Precious Nkatha Murithi', 'Your September 2026 invoice for Jakes Apartments A9 - KES 21,000.00', 'Dear Precious Nkatha Murithi,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A9\r\n    Billing period:  September 2026\r\n    Amount due:      KES 21,000.00\r\n    Balance:         KES 21,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/109/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY5LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2OSwiaWF0IjoxNzg4MjYyMDQ0LCJleHAiOjE3ODg4NjY4NDR9.n7ErbH-Z9M6lC4WRGHgyexVtLWxKlrDj_66yFCsNmcM\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:24', '2026-09-01 11:27:24'),
(617, 7, 'emurithi@gmail.com', 'Eric Murithi', 'Your September 2026 invoice for Jakes Apartments A9 - KES 21,000.00', 'Dear Eric Murithi,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            A9\r\n    Billing period:  September 2026\r\n    Amount due:      KES 21,000.00\r\n    Balance:         KES 21,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/109/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY5LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2OSwiaWF0IjoxNzg4MjYyMDQ0LCJleHAiOjE3ODg4NjY4NDR9.n7ErbH-Z9M6lC4WRGHgyexVtLWxKlrDj_66yFCsNmcM\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:24', '2026-09-01 11:27:24'),
(618, 7, 'cherutotracy12@gmail.com', 'Kiptoo Tracy Cheruto', 'Your September 2026 invoice for Jakes Apartments B1 - KES 18,500.00', 'Dear Kiptoo Tracy Cheruto,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B1\r\n    Billing period:  September 2026\r\n    Amount due:      KES 18,500.00\r\n    Balance:         KES 18,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/110/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NCwiaWF0IjoxNzg4MjYyMDQ0LCJleHAiOjE3ODg4NjY4NDR9.SGrbtDGq4WgxXZFspMM5Ax37zLWvIiqC7pz36-oBz1M\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:24', '2026-09-01 11:27:24'),
(619, 7, 'winsix97@gmail.com', 'Dr Kiptoo Wincer Kirui', 'Your September 2026 invoice for Jakes Apartments B1 - KES 18,500.00', 'Dear Dr Kiptoo Wincer Kirui,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B1\r\n    Billing period:  September 2026\r\n    Amount due:      KES 18,500.00\r\n    Balance:         KES 18,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/110/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1NCwiaWF0IjoxNzg4MjYyMDQ0LCJleHAiOjE3ODg4NjY4NDR9.SGrbtDGq4WgxXZFspMM5Ax37zLWvIiqC7pz36-oBz1M\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:25', '2026-09-01 11:27:25'),
(620, 7, 'sevelle376@gmail.com', 'Mark Njoroge Waweru', 'Your September 2026 invoice for Jakes Apartments B2 - KES 12,500.00', 'Dear Mark Njoroge Waweru,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B2\r\n    Billing period:  September 2026\r\n    Amount due:      KES 12,500.00\r\n    Balance:         KES 12,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/111/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQzLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0MywiaWF0IjoxNzg4MjYyMDQ1LCJleHAiOjE3ODg4NjY4NDV9.wG2r7q-MCyxr3f8kD2agyRIJ7U88VdGNB95KLrBz8f8\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:25', '2026-09-01 11:27:25'),
(621, 7, 'adrianmbai01@gmail.com', 'Adrian Mbai', 'Your September 2026 invoice for Jakes Apartments B3 - KES 18,500.00', 'Dear Adrian Mbai,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B3\r\n    Billing period:  September 2026\r\n    Amount due:      KES 18,500.00\r\n    Balance:         KES 18,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/112/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NiwiaWF0IjoxNzg4MjYyMDQ1LCJleHAiOjE3ODg4NjY4NDV9.kxCX7MaLpzXq5kdv_lBbguzfAsmeEpzF3n3dJdehKbA\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:25', '2026-09-01 11:27:25'),
(622, 7, 'cosmbai@gmail.com', 'Cosmus Mbai Mutuku', 'Your September 2026 invoice for Jakes Apartments B3 - KES 18,500.00', 'Dear Cosmus Mbai Mutuku,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B3\r\n    Billing period:  September 2026\r\n    Amount due:      KES 18,500.00\r\n    Balance:         KES 18,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/112/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY2LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NiwiaWF0IjoxNzg4MjYyMDQ1LCJleHAiOjE3ODg4NjY4NDV9.kxCX7MaLpzXq5kdv_lBbguzfAsmeEpzF3n3dJdehKbA\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:25', '2026-09-01 11:27:25'),
(623, 7, 'billyndume2@gmail.com', 'Billy Ndume Muruu', 'Your September 2026 invoice for Jakes Apartments B4 - KES 12,500.00', 'Dear Billy Ndume Muruu,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B4\r\n    Billing period:  September 2026\r\n    Amount due:      KES 12,500.00\r\n    Balance:         KES 12,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/113/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NSwiaWF0IjoxNzg4MjYyMDQ1LCJleHAiOjE3ODg4NjY4NDV9.CGWxAzpaOtpmEcjQGmkNGIJD0MRCXg2Huvm7qOLnRFM\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:25', '2026-09-01 11:27:25'),
(624, 7, 'shadrackmuruu10@gmail.com', 'Shadrack Ndume', 'Your September 2026 invoice for Jakes Apartments B4 - KES 12,500.00', 'Dear Shadrack Ndume,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B4\r\n    Billing period:  September 2026\r\n    Amount due:      KES 12,500.00\r\n    Balance:         KES 12,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/113/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NSwiaWF0IjoxNzg4MjYyMDQ1LCJleHAiOjE3ODg4NjY4NDV9.CGWxAzpaOtpmEcjQGmkNGIJD0MRCXg2Huvm7qOLnRFM\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:25', '2026-09-01 11:27:25'),
(625, 7, 'ismaelosmanmohamed4@gmail.com', 'Ismael Osman Mohamed', 'Your September 2026 invoice for Jakes Apartments B6 - KES 14,000.00', 'Dear Ismael Osman Mohamed,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B6\r\n    Billing period:  September 2026\r\n    Amount due:      KES 14,000.00\r\n    Balance:         KES 14,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/114/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2OCwiaWF0IjoxNzg4MjYyMDQ2LCJleHAiOjE3ODg4NjY4NDZ9.Y6GDGc3ntLdyDxOhOvNR8LZKFcXmr_vkfAVnP5bGdWA\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:26', '2026-09-01 11:27:26'),
(626, 7, 'israeldylan001@gmail.com', 'Dylan Israel', 'Your September 2026 invoice for Jakes Apartments B8 - KES 0.00', 'Dear Dylan Israel,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B8\r\n    Billing period:  September 2026\r\n    Amount due:      KES 0.00\r\n    Balance:         KES 0.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/115/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg4MjYyMDQ2LCJleHAiOjE3ODg4NjY4NDZ9.KVm-Bo7in1trcE_3xFTFFFgBo_yQt-jACSkFAqjpoyU\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:26', '2026-09-01 11:27:26'),
(627, 7, 'stanleytonui1@gmail.com', 'Stanley Tonui', 'Your September 2026 invoice for Jakes Apartments B8 - KES 0.00', 'Dear Stanley Tonui,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            B8\r\n    Billing period:  September 2026\r\n    Amount due:      KES 0.00\r\n    Balance:         KES 0.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/115/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY0LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NCwiaWF0IjoxNzg4MjYyMDQ2LCJleHAiOjE3ODg4NjY4NDZ9.KVm-Bo7in1trcE_3xFTFFFgBo_yQt-jACSkFAqjpoyU\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:26', '2026-09-01 11:27:26'),
(628, 7, 'albertkariuki860@gmail.com', 'Albert Kariuki Njau', 'Your September 2026 invoice for Jakes Apartments C1 - KES 19,500.00', 'Dear Albert Kariuki Njau,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C1\r\n    Billing period:  September 2026\r\n    Amount due:      KES 19,500.00\r\n    Balance:         KES 19,500.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/116/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NywiaWF0IjoxNzg4MjYyMDQ2LCJleHAiOjE3ODg4NjY4NDZ9.ABM7ZrVVHloe76rHsP-hJQaAnDV4nrq5TruOCGQuHfw\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:26', '2026-09-01 11:27:26'),
(629, 7, 'ruthjepchumba731@gmail.com', 'Ruth jepchumba', 'Your September 2026 invoice for Jakes Apartments C2 - KES 13,000.00', 'Dear Ruth jepchumba,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C2\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/117/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NywiaWF0IjoxNzg4MjYyMDQ2LCJleHAiOjE3ODg4NjY4NDZ9.FTdS2YhZBYiS-su3Li7FSr8BGrZzEe2l5dy1mkWoLfk\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:26', '2026-09-01 11:27:26'),
(630, 7, 'jepkorirp@gmail.com', 'Pauline Jepkorir Tuitoek', 'Your September 2026 invoice for Jakes Apartments C2 - KES 13,000.00', 'Dear Pauline Jepkorir Tuitoek,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C2\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/117/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ3LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0NywiaWF0IjoxNzg4MjYyMDQ2LCJleHAiOjE3ODg4NjY4NDZ9.FTdS2YhZBYiS-su3Li7FSr8BGrZzEe2l5dy1mkWoLfk\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:26', '2026-09-01 11:27:26'),
(631, 7, 'felistajepchirchir@gmail.com', 'felista jepchirchir rotich', 'Your September 2026 invoice for Jakes Apartments C3 - KES 13,000.00', 'Dear felista jepchirchir rotich,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C3\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/118/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg4MjYyMDQ3LCJleHAiOjE3ODg4NjY4NDd9.13WvFWUh0EU93EHpOOvs4AIamm4RlP0CGHTJjUnbEg4\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:27', '2026-09-01 11:27:27'),
(632, 7, 'gilbertchangwony@gmail.com', 'Gilbert Changwony', 'Your September 2026 invoice for Jakes Apartments C3 - KES 13,000.00', 'Dear Gilbert Changwony,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C3\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/118/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjQ4LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo0OCwiaWF0IjoxNzg4MjYyMDQ3LCJleHAiOjE3ODg4NjY4NDd9.13WvFWUh0EU93EHpOOvs4AIamm4RlP0CGHTJjUnbEg4\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:27', '2026-09-01 11:27:27'),
(633, 7, 'juliahkahora@gmail.com', 'Juliah Wangui Kahora', 'Your September 2026 invoice for Jakes Apartments C4 - KES 13,000.00', 'Dear Juliah Wangui Kahora,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C4\r\n    Billing period:  September 2026\r\n    Amount due:      KES 13,000.00\r\n    Balance:         KES 13,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/119/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjU5LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo1OSwiaWF0IjoxNzg4MjYyMDQ3LCJleHAiOjE3ODg4NjY4NDd9.rQ-UhNL4JLWRfOaQZk_cJb_wAMOB9Nf8A9VBeZ3YG0U\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:27', '2026-09-01 11:27:27'),
(634, 7, 'pierrahprecious@gmail.com', 'Precious Ntinyari Mati', 'Your September 2026 invoice for Jakes Apartments C5 - KES 21,000.00', 'Dear Precious Ntinyari Mati,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C5\r\n    Billing period:  September 2026\r\n    Amount due:      KES 21,000.00\r\n    Balance:         KES 21,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/120/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYzLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MywiaWF0IjoxNzg4MjYyMDQ3LCJleHAiOjE3ODg4NjY4NDd9.EC09BMO36Wy6yAVZ_Vw0q7DjKXmnOcsNqKIK9JxUTq4\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:27', '2026-09-01 11:27:27'),
(635, 7, 'liliankiende20@gmail.com', 'Evangeline Kinya', 'Your September 2026 invoice for Jakes Apartments C5 - KES 21,000.00', 'Dear Evangeline Kinya,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C5\r\n    Billing period:  September 2026\r\n    Amount due:      KES 21,000.00\r\n    Balance:         KES 21,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/120/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYzLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MywiaWF0IjoxNzg4MjYyMDQ3LCJleHAiOjE3ODg4NjY4NDd9.EC09BMO36Wy6yAVZ_Vw0q7DjKXmnOcsNqKIK9JxUTq4\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:27', '2026-09-01 11:27:27'),
(636, 7, 'stphn0445@gmail.com', 'John Stephen', 'Your September 2026 invoice for Jakes Apartments C6 - KES 7,000.00', 'Dear John Stephen,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C6\r\n    Billing period:  September 2026\r\n    Amount due:      KES 7,000.00\r\n    Balance:         KES 7,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/121/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYyLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MiwiaWF0IjoxNzg4MjYyMDQ3LCJleHAiOjE3ODg4NjY4NDd9.aDyH0b3oOOglqTLsGrjg7RP87SolV4nut94Eq3DGB4w\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:27', '2026-09-01 11:27:27'),
(637, 7, 'stiffin50@gmail.com', 'Stephen Ndambuki', 'Your September 2026 invoice for Jakes Apartments C6 - KES 7,000.00', 'Dear Stephen Ndambuki,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C6\r\n    Billing period:  September 2026\r\n    Amount due:      KES 7,000.00\r\n    Balance:         KES 7,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/121/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjYyLCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2MiwiaWF0IjoxNzg4MjYyMDQ3LCJleHAiOjE3ODg4NjY4NDd9.aDyH0b3oOOglqTLsGrjg7RP87SolV4nut94Eq3DGB4w\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:27', '2026-09-01 11:27:27'),
(638, 7, 'biammokua@gmail.com', 'Biam Mokua Onsomu', 'Your September 2026 invoice for Jakes Apartments C8 - KES 19,000.00', 'Dear Biam Mokua Onsomu,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C8\r\n    Billing period:  September 2026\r\n    Amount due:      KES 19,000.00\r\n    Balance:         KES 19,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/122/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NSwiaWF0IjoxNzg4MjYyMDQ4LCJleHAiOjE3ODg4NjY4NDh9.QceC0T5hGuYB7gVTfABtw048tGrFleh3NuDjCAM7ND8\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:28', '2026-09-01 11:27:28'),
(639, 7, 'denisotisoomollo@gmail.com', 'Denis Otiso Omollo', 'Your September 2026 invoice for Jakes Apartments C8 - KES 19,000.00', 'Dear Denis Otiso Omollo,\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        Jakes Apartments\r\n    Unit:            C8\r\n    Billing period:  September 2026\r\n    Amount due:      KES 19,000.00\r\n    Balance:         KES 19,000.00\r\n    Invoice date:    2026-09-01\r\n\r\nPlease arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.\r\n\r\nYou can view and download a copy of your invoice here:\r\nhttps://rentalflow.co.ke/api/bills/122/invoice?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvd25lcl9pZCI6NywiYWN0b3JfaWQiOjY1LCJyb2xlIjoidGVuYW50IiwidGVuYW50X2lkIjo2NSwiaWF0IjoxNzg4MjYyMDQ4LCJleHAiOjE3ODg4NjY4NDh9.QceC0T5hGuYB7gVTfABtw048tGrFleh3NuDjCAM7ND8\r\n\r\nIf you have any questions about this invoice, please contact the property office (Jacob Mbuthia).\r\n\r\nThank you,\r\nRentaFlow Team', 'sent', NULL, '2026-09-01 11:27:28', '2026-09-01 11:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int NOT NULL,
  `owner_id` int NOT NULL,
  `template_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` int DEFAULT '0',
  `attempts` int DEFAULT '0',
  `max_attempts` int DEFAULT '3',
  `status` enum('pending','processing','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_queue_settings`
--

CREATE TABLE `email_queue_settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_queue_settings`
--

INSERT INTO `email_queue_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'batch_size', '10', '2026-07-08 07:46:20', '2026-07-08 07:46:20'),
(2, 'process_interval', '60', '2026-07-08 07:46:20', '2026-07-08 07:46:20'),
(3, 'max_retry_delay', '300', '2026-07-08 07:46:20', '2026-07-08 07:46:20'),
(4, 'enabled', '1', '2026-07-08 07:46:20', '2026-07-08 07:46:20');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'email',
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `owner_id`, `name`, `type`, `subject`, `body`, `created_at`) VALUES
(1, 1, 'Tenant Welcome', 'email', 'Welcome to {{property}} - Activate Your Tenant Account', 'Dear {{tenant}},\r\n\r\nWelcome to {{property}}.\r\n\r\nWe are delighted to have you as a valued tenant and would like to officially welcome you to our community. Our goal is to provide you with a comfortable, secure, and well-managed living environment throughout your tenancy.\r\n\r\nYour tenancy details are as follows:\r\n\r\nProperty: {{property}}\r\nUnit/House: {{house}}\r\nRegistered Email: {{email}}\r\n\r\nTo help you manage your tenancy conveniently, we have created an online tenant account for you. Through the tenant portal, you will be able to:\r\n\r\n• View your tenancy information\r\n• Track rent payments and account balances\r\n• Submit maintenance requests and complaints\r\n• Receive important property notices and updates\r\n• Access invoices and payment receipts\r\n• Communicate with property management\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\n{{setup_link}}\r\n\r\nPlease note that this activation link will expire within 48 hours for security reasons. If the link expires before you complete the setup process, kindly contact your property manager for assistance.\r\n\r\nShould you have any questions regarding your tenancy, payments, or account setup, please do not hesitate to contact us.\r\n\r\nWe look forward to serving you and making your stay enjoyable.\r\n\r\nKind regards,\r\n\r\nProperty Management Team\r\n{{property}}', '2026-06-26 08:40:41'),
(2, 1, 'Caretaker Welcome', 'email', 'Welcome to RentFlow - Caretaker Account Activation', 'Dear {{tenant}},\r\n\r\nWelcome to RentFlow.\r\n\r\nYou have been successfully registered as a caretaker within our property management system. We appreciate your commitment and look forward to working with you in maintaining a safe, organized, and well-managed environment for our tenants and property owners.\r\n\r\nRegistered Email: {{email}}\r\n\r\nAs a caretaker, your account may be used to:\r\n\r\n• Monitor occupancy and tenant activities\r\n• Report maintenance and repair issues\r\n• Receive management notices and updates\r\n• Assist with inspections and property administration\r\n• Communicate with management regarding operational matters\r\n\r\nTo activate your account and create your password, please click the link below:\r\n\r\n{{setup_link}}\r\n\r\nFor security purposes, this activation link will expire within 48 hours.\r\n\r\nIf you experience any difficulties during activation or require clarification regarding your responsibilities, please contact the property administrator.\r\n\r\nThank you for your support and cooperation.\r\n\r\nKind regards,\r\n\r\nRentFlow Team', '2026-06-26 08:40:41'),
(3, 1, 'Payment Confirmation', 'email', 'Payment Successfully Received - KES {{amount}}', 'Dear {{tenant}},\r\n\r\nThank you for your payment.\r\n\r\nThis email serves as confirmation that your payment has been successfully received and recorded in our system.\r\n\r\nPayment Details:\r\n\r\nAmount Paid: KES {{amount}}\r\nPayment Category: {{category}}\r\nPayment Date: {{date}}\r\nCurrent Account Balance: KES {{balance}}\r\n\r\n{{invoice_section}}\r\n\r\nYou may also access or download your invoice using the link below:\r\n\r\n{{invoice_url}}\r\n\r\nWe recommend retaining this confirmation for your personal records.\r\n\r\nIf you believe there is any discrepancy regarding the payment amount, account balance, or invoice information, kindly contact management immediately for assistance.\r\n\r\nWe appreciate your prompt payment and continued cooperation.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', '2026-06-26 08:40:41'),
(4, 1, 'Complaint Update', 'email', 'Complaint Received - Reference #{{id}}', 'Dear {{tenant}},\r\n\r\nThank you for bringing your concern to our attention.\r\n\r\nThis email confirms that we have successfully received your complaint and registered it under the reference number shown below.\r\n\r\nComplaint Details:\r\n\r\nReference Number: #{{id}}\r\nCategory: {{category}}\r\nDate Submitted: {{date}}\r\n\r\nOur team will review the matter carefully and take the necessary action as soon as possible. Depending on the nature of the issue, additional information may be requested from you to help facilitate a timely resolution.\r\n\r\nPlease keep this reference number for future correspondence regarding this matter.\r\n\r\nWe appreciate your patience and assure you that your concern is important to us.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', '2026-06-26 08:40:41'),
(5, 1, 'Complaint Reply', 'email', 'Update Regarding Complaint #{{id}}', 'Dear {{tenant}},\r\n\r\nWe would like to inform you that there has been an update regarding your complaint.\r\n\r\nComplaint Reference: #{{id}}\r\nCategory: {{category}}\r\n\r\nOur team has reviewed your concern and provided a response through the tenant portal.\r\n\r\nTo view the latest update, comments, recommendations, or actions taken, please log in using the link below:\r\n\r\n{{link}}\r\n\r\nShould you require further clarification or wish to provide additional information, you may respond through the portal or contact management directly.\r\n\r\nThank you for your cooperation and patience while we work to resolve your concern.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', '2026-06-26 08:40:41'),
(6, 1, 'Rent Reminder', 'email', 'Friendly Rent Reminder - {{month}}', 'Dear {{tenant}},\r\n\r\nWe hope you are doing well.\r\n\r\nThis is a friendly reminder that your rent payment for {{month}} is due soon.\r\n\r\nAccount Summary:\r\n\r\nAmount Due: KES {{amount}}\r\nDue Date: 5th of the Month\r\nCurrent Balance: KES {{balance}}\r\n\r\nPayment Instructions:\r\n\r\n{{payment_instructions}}\r\n\r\nTo avoid late payment penalties, service interruptions, or additional follow-up notices, we kindly request that payment be made on or before the due date.\r\n\r\nIf payment has already been made, please disregard this reminder. If your payment has not yet reflected in your account, kindly share the payment confirmation for verification.\r\n\r\nThank you for your cooperation and continued tenancy.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', '2026-06-26 08:40:41'),
(7, 1, 'Rent Reminder Final', 'email', 'Urgent Rent Reminder - Payment Due Tomorrow ({{month}})', 'Dear {{tenant}},\r\n\r\nThis is an urgent reminder that your rent payment for {{month}} is due tomorrow.\r\n\r\nAccount Summary:\r\n\r\nAmount Due: KES {{amount}}\r\nDue Date: 5th of the Month\r\nCurrent Balance: KES {{balance}}\r\n\r\nPayment Instructions:\r\n\r\n{{payment_instructions}}\r\n\r\nTo avoid penalties, additional charges, or unnecessary follow-up actions, we strongly encourage you to settle the outstanding amount before the due date.\r\n\r\nIf payment has already been made, please disregard this message. If you have proof of payment that has not yet been reflected in your account, kindly share it with management for verification.\r\n\r\nWe appreciate your prompt attention to this matter.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', '2026-06-26 08:40:41'),
(8, 1, 'Lease Renewal', 'email', 'Lease Renewal Discussion - {{house}}', 'Dear {{tenant}},\r\n\r\nWe hope you have enjoyed your stay at {{property}}.\r\n\r\nOur records indicate that your tenancy agreement for {{house}} is approaching its expiry date.\r\n\r\nAs a valued tenant, we would like to discuss available renewal options with you before the lease expires. Early communication helps ensure a smooth renewal process and allows both parties sufficient time to make any necessary arrangements.\r\n\r\nWe kindly request that you contact management at your earliest convenience to discuss:\r\n\r\n• Lease renewal options\r\n• Updated tenancy terms if applicable\r\n• Future occupancy plans\r\n• Any questions or concerns you may have\r\n\r\nIf you do not intend to renew your tenancy, please notify management within the notice period specified in your tenancy agreement.\r\n\r\nThank you for being a valued member of our community. We look forward to hearing from you.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', '2026-06-26 08:40:41'),
(9, 1, 'Password Reset', 'email', 'Password Reset Verification Code - RentFlow', 'Dear {{name}},\r\n\r\nWe recently received a request to reset the password associated with your RentFlow account.\r\n\r\nYour verification code is:\r\n\r\n{{code}}\r\n\r\nThis code will expire in {{expires}}.\r\n\r\nTo complete your password reset:\r\n\r\n1. Enter the verification code above.\r\n2. Verify your identity.\r\n3. Create a new secure password.\r\n4. Log in using your new credentials.\r\n\r\nIf you did not request a password reset, please ignore this email. No changes will be made to your account unless the verification code is used successfully.\r\n\r\nFor your security, never share this verification code with anyone.\r\n\r\nKind regards,\r\n\r\nRentFlow Security Team', '2026-06-28 06:11:46'),
(10, 1, 'Termination Request', 'email', 'Tenant Termination Request Requiring Review - {{property}} {{house}}', 'Dear {{owner_name}},\r\n\r\nThis email is to notify you that a tenant has submitted a request to terminate their tenancy agreement.\r\n\r\nRequest Details:\r\n\r\nTenant: {{tenant_name}}\r\nProperty: {{property}}\r\nUnit: {{house}}\r\nRequested Termination Date: {{date}}\r\nReason Provided: {{reason}}\r\n\r\nWe kindly request that you review this request and determine whether approval can be granted in accordance with the tenancy agreement and applicable property policies.\r\n\r\nPlease log in to your dashboard to review the request and take the appropriate action.\r\n\r\nShould you require additional information, please contact management.\r\n\r\nKind regards,\r\n\r\nRentFlow Team', '2026-06-28 07:25:55'),
(11, 1, 'Termination Notice', 'email', 'Official Tenancy Termination Notice - {{property}} {{house}}', 'Dear {{tenant_name}},\r\n\r\nThis email serves as official notice that your tenancy agreement has been terminated.\r\n\r\nTermination Details:\r\n\r\nProperty: {{property}}\r\nUnit: {{house}}\r\nTermination Date: {{date}}\r\nReason: {{reason}}\r\n\r\nPlease ensure that the premises are vacated by the termination date indicated above and that all keys, access devices, and any property belonging to management are returned as instructed.\r\n\r\nBefore vacating, kindly ensure:\r\n\r\n• Outstanding balances are settled\r\n• Personal belongings are removed\r\n• Property handover procedures are completed\r\n• Any damages are reported appropriately\r\n\r\nIf you have questions regarding the move-out process or final account reconciliation, please contact management.\r\n\r\nKind regards,\r\n\r\n{{owner_name}}\r\nRentFlow', '2026-06-28 07:25:55'),
(12, 1, 'Management Notice', 'email', '{{title}} - Important Property Notice', 'Dear {{tenant_name}},\r\n\r\nYou have received an important communication from {{sender_name}}.\r\n\r\nNotice Details:\r\n\r\nProperty: {{property}}\r\nUnit: {{house}}\r\nDate: {{date}}\r\nCategory: {{category}}\r\n\r\nSubject:\r\n{{title}}\r\n\r\nMessage:\r\n{{description}}\r\n\r\nWe kindly request that you review this notice carefully and take any action required within the specified timeframe.\r\n\r\nFor additional details, updates, or responses related to this notice, please log in to your RentFlow account.\r\n\r\nIf you require clarification, please contact management directly.\r\n\r\nKind regards,\r\n\r\n{{sender_name}}', '2026-06-28 07:59:55'),
(13, 1, 'Tenant Vacate', 'email', 'Confirmation of Tenancy Termination - {{property}}', 'Dear {{tenant}},\r\n\r\nThis email serves as official confirmation that your tenancy has been successfully terminated and your occupancy record has been updated accordingly.\r\n\r\nTermination Details:\r\n\r\nProperty: {{property}}\r\nUnit/House: {{house}}\r\nTermination Date: {{date}}\r\nNational ID: {{national_id}}\r\n\r\nYour unit has now been marked as vacant and is available for future occupancy.\r\n\r\nBefore final closure of your tenancy, kindly ensure that:\r\n\r\n• All outstanding balances have been settled\r\n• Property keys and access devices have been returned\r\n• Any agreed handover procedures have been completed\r\n• Personal belongings have been removed from the premises\r\n\r\nWe sincerely appreciate the opportunity to have served you during your tenancy and thank you for your cooperation.\r\n\r\nShould you require tenancy records, payment statements, recommendation letters, or any additional assistance, please contact management.\r\n\r\nWe wish you every success in your future endeavors and your next home.\r\n\r\nKind regards,\r\n\r\nProperty Management Team', '2026-07-16 03:41:05');

-- --------------------------------------------------------

--
-- Table structure for table `houses`
--

CREATE TABLE `houses` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `property_id` int UNSIGNED NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '1 Bedroom',
  `status` enum('occupied','vacant') COLLATE utf8mb4_unicode_ci DEFAULT 'vacant',
  `tenant_id` int UNSIGNED DEFAULT NULL,
  `rent` decimal(12,2) DEFAULT '0.00',
  `water_meter` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `elec_meter` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_reading` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `houses`
--

INSERT INTO `houses` (`id`, `owner_id`, `property_id`, `unit`, `type`, `status`, `tenant_id`, `rent`, `water_meter`, `elec_meter`, `last_reading`, `created_at`, `updated_at`) VALUES
(52, 7, 34, 'B1', 'Studio', 'occupied', 54, 6500.00, '432201-01328455', '92110889414', 0.00, '2026-08-01 10:16:28', '2026-08-28 12:03:22'),
(53, 7, 34, 'B2', 'Studio', 'occupied', 43, 6500.00, '43192-01328446', '92110889372', 0.00, '2026-08-01 10:16:37', '2026-08-03 18:08:43'),
(54, 7, 34, 'B3', 'Studio', 'occupied', 66, 6500.00, '01328454', '92110889422', 0.00, '2026-08-01 10:18:17', '2026-08-28 12:18:44'),
(55, 7, 34, 'B4', 'Studio', 'occupied', 45, 6500.00, '01328457', '92110889380', 0.00, '2026-08-01 10:19:52', '2026-08-03 18:10:52'),
(56, 7, 34, 'B5', 'Studio', 'vacant', NULL, 6500.00, '01328447', '92110889430', 0.00, '2026-08-01 10:20:04', '2026-08-03 18:12:15'),
(57, 7, 34, 'A1', 'Studio', 'occupied', 46, 6500.00, '43196-01328450', '92110889398', 0.00, '2026-08-01 10:20:27', '2026-08-03 17:59:20'),
(58, 7, 34, 'A2', 'Studio', 'occupied', 56, 6500.00, '43195-01328449', '58103918629', 0.00, '2026-08-01 10:20:49', '2026-08-28 11:52:00'),
(59, 7, 34, 'A3', 'Studio', 'occupied', 55, 6500.00, '43194-01328448', '92110889356', 0.00, '2026-08-01 10:21:04', '2026-08-28 11:52:30'),
(60, 7, 34, 'A4', 'Studio', 'occupied', 58, 7500.00, '43199-01328453', '92110889406', 0.00, '2026-08-01 10:21:19', '2026-08-03 18:03:59'),
(61, 7, 34, 'A5', 'Studio', 'occupied', 57, 6500.00, '43198-01328452', '92110889364', 0.00, '2026-08-01 10:21:46', '2026-08-03 18:05:23'),
(62, 7, 34, 'A6', 'Studio', 'occupied', 70, 7000.00, '', '', 0.00, '2026-08-01 10:22:25', '2026-09-01 06:01:29'),
(63, 7, 34, 'B6', 'Studio', 'occupied', 68, 7000.00, '', '', 0.00, '2026-08-01 10:22:32', '2026-08-28 13:00:49'),
(64, 7, 34, 'A7', 'Studio', 'occupied', 51, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:22:42', '2026-08-02 17:05:10'),
(65, 7, 34, 'B7', 'Studio', 'vacant', NULL, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:22:44', '2026-08-01 10:22:44'),
(66, 7, 34, 'A8', 'Studio', 'occupied', 52, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:22:55', '2026-08-02 17:13:07'),
(67, 7, 34, 'B8', 'Studio', 'occupied', 64, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:22:57', '2026-08-22 07:31:06'),
(68, 7, 34, 'A9', 'Studio', 'occupied', 69, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:23:06', '2026-08-30 19:07:43'),
(69, 7, 34, 'B9', 'Studio', 'vacant', NULL, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:23:07', '2026-08-01 10:23:07'),
(70, 7, 34, 'A10', 'Studio', 'occupied', 61, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:23:20', '2026-08-09 06:37:54'),
(71, 7, 34, 'B10', 'Studio', 'vacant', NULL, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:23:28', '2026-08-01 10:23:28'),
(72, 7, 34, 'C6', 'Studio', 'occupied', 62, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:24:16', '2026-08-20 02:43:12'),
(73, 7, 34, 'C1', 'Studio', 'occupied', 67, 6500.00, NULL, NULL, 0.00, '2026-08-01 10:24:19', '2026-08-28 12:42:05'),
(74, 7, 34, 'C7', 'Studio', 'vacant', NULL, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:24:28', '2026-08-01 10:24:28'),
(75, 7, 34, 'C2', 'Studio', 'occupied', 47, 6500.00, '01328607', '58103918569', 0.00, '2026-08-01 10:24:31', '2026-08-03 18:20:35'),
(76, 7, 34, 'C8', 'Studio', 'occupied', 65, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:24:37', '2026-08-22 15:23:35'),
(77, 7, 34, 'C3', 'Studio', 'occupied', 48, 6500.00, '01328603', '58103918635', 0.00, '2026-08-01 10:24:45', '2026-08-03 18:18:35'),
(78, 7, 34, 'C9', 'Studio', 'vacant', NULL, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:24:46', '2026-08-01 10:24:46'),
(79, 7, 34, 'C4', 'Studio', 'occupied', 59, 6500.00, '01328606', '58103918643', 0.00, '2026-08-01 10:24:56', '2026-08-28 12:07:27'),
(80, 7, 34, 'C10', 'Studio', 'occupied', 71, 7000.00, NULL, NULL, 0.00, '2026-08-01 10:24:56', '2026-09-01 09:56:32'),
(81, 7, 34, 'C5', 'Studio', 'occupied', 63, 7000.00, '01328604', '58103918585', 0.00, '2026-08-01 10:26:50', '2026-08-20 18:06:50'),
(82, 12, 35, 'G1', 'Studio', 'occupied', 49, 20000.00, NULL, NULL, 0.00, '2026-08-01 18:27:46', '2026-08-01 18:29:58'),
(83, 13, 36, 'C1', '1 Bedroom', 'occupied', 50, 7500.00, '220098-9900', 'KPLC-99087641', 0.00, '2026-08-02 13:15:21', '2026-08-03 15:04:21'),
(84, 13, 36, 'C2', '1 Bedroom', 'occupied', 53, 7500.00, NULL, NULL, 0.00, '2026-08-02 13:15:42', '2026-08-03 04:43:55'),
(85, 13, 36, 'C3', 'Studio', 'occupied', 60, 7500.00, '220098-9900', 'KPLC-99087641', 0.00, '2026-08-06 16:00:02', '2026-08-06 16:00:19');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_records`
--

CREATE TABLE `maintenance_records` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `property_id` int UNSIGNED DEFAULT NULL,
  `house_id` int UNSIGNED DEFAULT NULL,
  `tenant_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'General',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('pending','in-progress','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `assigned_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost` decimal(12,2) DEFAULT '0.00',
  `cost_notes` text COLLATE utf8mb4_unicode_ci,
  `vendor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recipient_type` enum('individual','property','all') COLLATE utf8mb4_unicode_ci DEFAULT 'individual',
  `recipient_ids` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of tenant IDs',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'OW',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `name`, `email`, `password`, `phone`, `avatar`, `last_login`, `created_at`, `updated_at`) VALUES
(7, 'Jacob Mbuthia', 'bethjabs@gmail.com', '$2y$10$lDpHRtxSxqKup/ajFH77t.lAc0vegdty32UdKRgCXX3Ui1/OyYmUO', '0715038398', 'JM', '2026-09-01 12:24:44', '2026-07-16 17:15:25', '2026-09-01 12:24:44'),
(12, 'kwat lita', 'kwatlita@gmail.com', '$2y$10$SMh3VqVSTyhnmjQVaMVCLeG953.yLe3BAbhEasi84DiImOMtNpoyW', '0757846560', 'KL', NULL, '2026-08-01 18:22:30', '2026-08-01 18:22:30'),
(13, 'duncan karenju', 'karenjuduncan750@gmail.com', '$2y$10$KiYyEmS/0JkgJqzLADc1b.AGqYIbZ4gkJCYEsGt/v8n5zhR6c/.Lq', '+254112554479', 'DK', '2026-08-31 09:07:27', '2026-08-02 13:13:42', '2026-08-31 09:07:27'),
(14, 'Tracy Cheruto ', 'cherutotracy12@gmail.com', '$2y$10$S/cvoFT.r.db2egof.vOSuAxA1.yC2lNzFIrK6mxt3Ft/58qDxbbu', '+254791947331', 'TC', '2026-08-31 09:05:20', '2026-08-03 18:52:56', '2026-08-31 09:05:20');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED DEFAULT NULL,
  `tenant_id` int UNSIGNED DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `attempts` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `owner_id`, `tenant_id`, `email`, `code`, `expires_at`, `used`, `attempts`, `created_at`) VALUES
(66, NULL, 56, 'bestadryan01@gmail.com', '811804', '2026-08-05 19:00:37', 1, 0, '2026-08-05 18:45:37'),
(67, NULL, 56, 'bestadryan01@gmail.com', '105583', '2026-08-05 19:02:16', 1, 1, '2026-08-05 18:47:16'),
(68, NULL, 56, 'bestadryan01@gmail.com', '738923', '2026-08-22 16:06:41', 0, 0, '2026-08-22 15:51:41');

-- --------------------------------------------------------

--
-- Table structure for table `password_setup_tokens`
--

CREATE TABLE `password_setup_tokens` (
  `id` int UNSIGNED NOT NULL,
  `user_type` enum('tenant','caretaker') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `token` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_setup_tokens`
--

INSERT INTO `password_setup_tokens` (`id`, `user_type`, `user_id`, `owner_id`, `token`, `expires_at`, `used_at`, `created_at`) VALUES
(33, 'caretaker', 36, 7, 'a34a918c0ec0e1e382817dff2f7e6f7cc9bac5a90838fc00a2025100e52d7dee', '2026-08-03 10:03:57', '2026-08-01 10:14:36', '2026-08-01 10:03:57'),
(34, 'tenant', 43, 7, '728736954b2417660e71287c7960394aa2fcce7341a51079f5c67f9595793284', '2026-08-03 10:39:35', NULL, '2026-08-01 10:39:35'),
(35, 'tenant', 44, 7, '389461d3719924c6606110152cc284db8be90f6c7f34f65dc71ed645cd9fb1dc', '2026-08-03 10:45:39', NULL, '2026-08-01 10:45:39'),
(36, 'tenant', 45, 7, '3792a0b817c303e9bf46cd56bfde5ab0d4aa22bab1dad47118dc5a9b0be1cc04', '2026-08-03 10:59:13', NULL, '2026-08-01 10:59:13'),
(37, 'tenant', 46, 7, 'c64518fc4ab1d602023191f183c6786fea5156a06703e5bb40d8499238130278', '2026-08-03 11:17:26', NULL, '2026-08-01 11:17:26'),
(38, 'tenant', 47, 7, '148265bc0998cc26a9e22e1869a79767658812dbd3ebe53ea6168289d4e816c8', '2026-08-03 12:34:14', '2026-08-03 15:52:48', '2026-08-01 12:34:14'),
(39, 'tenant', 48, 7, 'fa318f29e9d017001c65b6bb60c8cb615df52a657e62c9707ac4b82f28824aac', '2026-08-03 14:47:22', '2026-08-01 14:48:52', '2026-08-01 14:47:22'),
(40, 'tenant', 49, 12, '731de77a1e1c2be83155fe4101f62a8ab9785a03451fcaa76cbe08932e9d11e5', '2026-08-03 18:29:58', NULL, '2026-08-01 18:29:58'),
(41, 'caretaker', 37, 13, '9e22649fcbdd21b61ceb00a3cbffd5177272f2e92b820fe6c333ae4c47a73654', '2026-08-04 13:16:10', '2026-08-02 13:17:01', '2026-08-02 13:16:10'),
(42, 'tenant', 50, 13, '794d908ecb048ec2fc97fda4752075978ac9cad24f58ca6ca061123a02126f90', '2026-08-04 13:18:55', '2026-08-02 13:57:44', '2026-08-02 13:18:55'),
(43, 'tenant', 51, 7, '4782c767e3eb926ce92c0d65769f32ddd03ffbc5a813b2ab72325989f784dcce', '2026-08-04 17:05:10', '2026-08-02 17:50:16', '2026-08-02 17:05:10'),
(44, 'tenant', 52, 7, '7e498c22edd741d2f11d394e060f36e9efdf6286bd3a83aed97363f73ae31a7e', '2026-08-04 17:13:07', '2026-08-03 12:41:58', '2026-08-02 17:13:07'),
(45, 'tenant', 51, 7, 'ea2985d34592cdd68afd1b923fe940a38d304498b65582c2fd43bebba7badd5a', '2026-08-04 17:51:46', '2026-08-04 09:57:01', '2026-08-02 17:51:46'),
(46, 'tenant', 53, 13, 'f0493787744556607553a2dc79b407ebf4fd04a37b4a231dccc62990817b9594', '2026-08-05 04:43:55', NULL, '2026-08-03 04:43:55'),
(47, 'caretaker', 36, 7, '7b8d7596e7ab956a43b571bd11a67de214983340b314a797456d39712dcf3f18', '2026-08-05 08:51:20', NULL, '2026-08-03 08:51:20'),
(48, 'tenant', 54, 7, '62cd5f47c80ca2f0ce70710d4e5a6d91e256f50b796655ad4b11cd210670ee66', '2026-08-05 08:59:28', '2026-08-03 18:50:25', '2026-08-03 08:59:28'),
(49, 'tenant', 55, 7, 'f0cba33cd84b2057dd660d74134687de094c5b65e557d3a16f45f4667e9eb1a9', '2026-08-05 10:02:26', '2026-08-03 10:06:29', '2026-08-03 10:02:26'),
(50, 'tenant', 56, 7, 'f8b436561b38c0b6398af0c207ab9ea0c8a52e5edc717ed2f30a8ef49e6b445e', '2026-08-05 10:15:08', '2026-08-03 18:47:11', '2026-08-03 10:15:08'),
(51, 'tenant', 47, 7, '3b8116713c6fd90e9a20cbe3aa961e6aed452e9a71e5e3a5798c9a6d7e38fe91', '2026-08-05 15:52:48', '2026-08-03 15:58:28', '2026-08-03 15:52:48'),
(52, 'tenant', 57, 7, '1f1c53de94c60f208ac159f67a0008e7623258aef7678f34dd63befd072ff845', '2026-08-05 17:01:41', '2026-08-04 07:40:29', '2026-08-03 17:01:41'),
(53, 'tenant', 58, 7, '716f4f1fcc880f1f3567cca31825ae4bf620877be32438a308aff3cba962c868', '2026-08-05 17:10:06', '2026-08-03 17:12:05', '2026-08-03 17:10:06'),
(54, 'tenant', 59, 7, '1ff5ee1254cb79cd47aa0ab7b4d6e639c28d9678fcbd95da8d70326628e5b661', '2026-08-05 17:45:48', '2026-08-03 18:44:27', '2026-08-03 17:45:48'),
(55, 'tenant', 54, 7, 'abb3de61b02c0142c00b5e2a1758b6fa01f98b9f643f8dc260abd67160d05ceb', '2026-08-05 18:54:31', '2026-08-03 18:56:06', '2026-08-03 18:54:31'),
(56, 'tenant', 59, 7, '132382073780b1771e4fd3b36fbca7f422e7ff03d768323adc4b6fe938e548f0', '2026-08-07 18:34:05', NULL, '2026-08-05 18:34:05'),
(57, 'tenant', 60, 13, '2aa8972aefee08dd448c56e5691977ceb7cfa5df51bcdedfba8140cd243767d2', '2026-08-08 16:00:19', NULL, '2026-08-06 16:00:19'),
(58, 'caretaker', 38, 7, 'df850d1d0ca5c1db62fd2e2e3434e937557c0385ad110c40902e35ff938adc46', '2026-08-11 06:07:57', '2026-08-09 06:08:51', '2026-08-09 06:07:57'),
(59, 'tenant', 61, 7, '78b2cf9a54a811ac72e01087a9ae5501707f0a8c2eb85b2b130db7a8f8d1c3db', '2026-08-11 06:37:54', '2026-08-09 18:54:22', '2026-08-09 06:37:54'),
(60, 'tenant', 57, 7, '0bef149327af86dcd3e5e693d6fb269665b3a0cd1581fcb7cc6c565c657554b5', '2026-08-11 17:58:39', NULL, '2026-08-09 17:58:39'),
(61, 'tenant', 62, 7, 'c36eef28c08b85d667e8f6e196d4141ccc2e4f90e0e5d2343451ba15e62c319a', '2026-08-22 02:43:12', '2026-08-20 05:19:33', '2026-08-20 02:43:12'),
(62, 'tenant', 63, 7, 'fea50b3be426914b4c5a536ecec00d9a380c9f5c166aac86fa5bf70450933d60', '2026-08-22 18:06:50', '2026-08-21 13:26:50', '2026-08-20 18:06:50'),
(63, 'tenant', 64, 7, '7ab407074c926008bfb7818b4fea3fac7ea9e31d7a9fd60cd6e57bef9734141a', '2026-08-24 07:31:06', '2026-08-22 08:19:46', '2026-08-22 07:31:06'),
(64, 'tenant', 65, 7, 'b5df5d908c51f27d589d973eb225e198520b79a7e2f03a9cb4e22be7a595d126', '2026-08-24 15:23:35', '2026-08-22 15:24:40', '2026-08-22 15:23:35'),
(65, 'tenant', 65, 7, 'ff10d756556189061a87b08cd1ba02a95dc45ac9da682536415bf3cb5aa08dc3', '2026-08-24 15:48:48', '2026-09-01 06:31:09', '2026-08-22 15:48:48'),
(66, 'tenant', 66, 7, '7851a3fe3df02ebb53874e626c6e0af28e17b28f1efaade76f1c607d02800170', '2026-08-30 12:18:44', NULL, '2026-08-28 12:18:44'),
(67, 'tenant', 67, 7, 'f7dbb72893bed570cb3a0bdbf9102e7f5b85061c1821b544d3d5bf964ed90106', '2026-08-30 12:42:05', NULL, '2026-08-28 12:42:05'),
(68, 'tenant', 68, 7, 'efb1161a6f9aebeb77bfa38415e5fa8347e2d35d066c89bfec351927c87117ed', '2026-08-30 13:00:49', NULL, '2026-08-28 13:00:49'),
(69, 'tenant', 69, 7, '66d7c5c493149464fb1c07d213556226e66897ad6a5c46c0dddb59792e0d7052', '2026-09-01 19:07:43', NULL, '2026-08-30 19:07:43'),
(70, 'tenant', 70, 7, 'f0446746ed65e08229b65896b8deb5328e4a9d38c126e31bdb6ff657b30fc4b4', '2026-09-03 06:01:29', '2026-09-01 06:13:33', '2026-09-01 06:01:29'),
(71, 'tenant', 65, 7, 'd2b0946dd2bdab357d54217ec3819a6e5a899cead4dd77ae5e299fe9d0f34f29', '2026-09-03 06:31:09', '2026-09-01 06:33:34', '2026-09-01 06:31:09'),
(72, 'tenant', 65, 7, 'c14286795c639583af235b6e50881e1ff0ea2ab659a70cb4d99fdc4057ae4006', '2026-09-03 06:33:34', '2026-09-01 11:02:03', '2026-09-01 06:33:34'),
(73, 'tenant', 71, 7, '2baddb1425c181c682d4e0d13edcbb9a313bbe09dbf64dacb2ce630365cb43af', '2026-09-03 09:56:32', NULL, '2026-09-01 09:56:32'),
(74, 'tenant', 65, 7, '6fb010cec4a3c71dccd098ba09d11b963542b9e6370af062831e8c46c48e7f51', '2026-09-03 11:02:03', NULL, '2026-09-01 11:02:03');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `tenant_id` int UNSIGNED NOT NULL,
  `house_id` int UNSIGNED DEFAULT NULL,
  `month` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'YYYY-MM format for billing period',
  `amount` decimal(12,2) NOT NULL,
  `type` enum('Rent','Water','Electricity','Deposit','Mixed') COLLATE utf8mb4_unicode_ci DEFAULT 'Rent',
  `method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'M-Pesa',
  `date` date DEFAULT NULL,
  `status` enum('completed','pending','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'completed',
  `tenant_confirmed` tinyint(1) DEFAULT '0',
  `confirmed_at` datetime DEFAULT NULL,
  `receipt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `owner_id`, `tenant_id`, `house_id`, `month`, `amount`, `type`, `method`, `date`, `status`, `tenant_confirmed`, `confirmed_at`, `receipt`, `description`, `created_at`) VALUES
(55, 7, 47, 75, '2026-08', 13000.00, 'Rent', 'M-Pesa', '2026-08-01', 'completed', 0, NULL, 'RCP-2026-7853', 'Rent Payment', '2026-08-01 12:37:33'),
(56, 7, 48, 77, '2026-08', 13000.00, 'Rent', 'M-Pesa', '2026-08-01', 'completed', 1, '2026-08-01 14:53:13', 'RCP-2026-5725', 'Rent Payment', '2026-08-01 14:48:45'),
(57, 13, 50, 83, '2026-08', 15000.00, 'Rent', 'M-Pesa', '2026-08-02', 'completed', 0, NULL, 'RCP-2026-7740', 'Rent Payment', '2026-08-02 13:35:40'),
(58, 7, 51, 64, '2026-08', 14000.00, 'Rent', 'M-Pesa', '2026-08-02', 'completed', 1, '2026-08-02 17:52:14', 'RCP-2026-0338', 'Rent Payment', '2026-08-02 17:05:38'),
(59, 7, 52, 66, '2026-08', 14000.00, 'Rent', 'M-Pesa', '2026-08-02', 'completed', 0, NULL, 'RCP-2026-0885', 'Rent Payment', '2026-08-02 17:14:45'),
(60, 13, 53, 84, '2026-08', 15000.00, 'Rent', 'M-Pesa', '2026-08-03', 'completed', 0, NULL, 'RCP-2026-2278', 'Rent Payment', '2026-08-03 04:44:38'),
(61, 7, 59, 79, '2026-08', 6000.00, 'Rent', 'M-Pesa', '2026-08-05', 'completed', 1, '2026-08-06 04:45:54', 'RCP-2026-1112', 'Rent Payment', '2026-08-05 06:25:12'),
(62, 7, 56, 58, '2026-08', 6000.00, 'Rent', 'M-Pesa', '2026-08-05', 'completed', 0, NULL, 'RCP-2026-1930', 'Rent Payment', '2026-08-05 06:38:50'),
(63, 7, 55, 59, '2026-08', 6000.00, 'Rent', 'M-Pesa', '2026-08-05', 'completed', 0, NULL, 'RCP-2026-7279', 'Rent Payment', '2026-08-05 13:41:19'),
(64, 7, 43, 53, '2026-08', 6000.00, 'Rent', 'M-Pesa', '2026-08-05', 'completed', 0, NULL, 'RCP-2026-4358', 'Rent Payment', '2026-08-05 18:25:58'),
(65, 7, 54, 52, '2026-08', 6000.00, 'Rent', 'M-Pesa', '2026-08-06', 'completed', 0, NULL, 'RCP-2026-7730', 'Rent Payment', '2026-08-06 12:02:10'),
(66, 13, 50, 83, '2026-08', 7500.00, 'Rent', 'M-Pesa', '2026-08-06', 'completed', 0, NULL, 'RCP-2026-1677', 'Rent Payment', '2026-08-06 15:54:37'),
(67, 13, 60, 85, '2026-08', 7500.00, 'Rent', 'M-Pesa', '2026-08-06', 'completed', 0, NULL, 'RCP-2026-2089', 'Rent Payment', '2026-08-06 16:01:29'),
(68, 7, 45, 55, '2026-08', 6000.00, 'Rent', 'M-Pesa', '2026-08-07', 'completed', 0, NULL, 'RCP-2026-0018', 'Rent Payment', '2026-08-07 05:20:18'),
(69, 7, 61, 70, '2026-08', 14000.00, 'Rent', 'M-Pesa', '2026-08-09', 'completed', 0, NULL, 'RCP-2026-7557', 'Rent Payment', '2026-08-09 06:39:17'),
(70, 7, 46, 57, '2026-08', 6000.00, 'Rent', 'M-Pesa', '2026-08-11', 'completed', 0, NULL, 'RCP-2026-5421', 'Rent Payment', '2026-08-11 10:50:21'),
(71, 7, 57, 61, '2026-08', 6500.00, 'Rent', 'M-Pesa', '2026-08-12', 'completed', 0, NULL, 'RCP-2026-6209', 'Rent Payment', '2026-08-12 06:30:09'),
(72, 7, 58, 60, '2026-08', 7000.00, 'Rent', 'M-Pesa', '2026-08-12', 'completed', 0, NULL, 'RCP-2026-6335', 'Rent Payment', '2026-08-12 06:32:15'),
(73, 7, 65, 76, '2026-08', 7000.00, 'Rent', 'M-Pesa', '2026-08-22', 'completed', 0, NULL, 'RCP-2026-2282', 'Rent Payment', '2026-08-22 15:24:42'),
(74, 7, 67, 73, '2026-08', 6500.00, 'Rent', 'M-Pesa', '2026-08-28', 'completed', 0, NULL, 'RCP-2026-1124', 'Rent Payment', '2026-08-28 12:45:24'),
(75, 7, 64, 67, '2026-08', 14000.00, 'Rent', 'M-Pesa', '2026-08-28', 'completed', 0, NULL, 'RCP-2026-2843', 'Rent Payment', '2026-08-28 13:14:03'),
(76, 7, 66, 54, '2026-09', 12000.00, 'Rent', 'M-Pesa', '2026-09-01', 'completed', 0, NULL, 'RCP-2026-4324', 'Rent Payment', '2026-09-01 09:18:44'),
(77, 7, 63, 81, '2026-09', 7000.00, 'Rent', 'M-Pesa', '2026-09-01', 'completed', 0, NULL, 'RCP-2026-4794', 'Rent Payment', '2026-09-01 09:26:34');

-- --------------------------------------------------------

--
-- Table structure for table `payment_allocations`
--

CREATE TABLE `payment_allocations` (
  `id` int UNSIGNED NOT NULL,
  `payment_id` int UNSIGNED NOT NULL,
  `bill_item_id` int UNSIGNED NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_allocations`
--

INSERT INTO `payment_allocations` (`id`, `payment_id`, `bill_item_id`, `category`, `amount`, `created_at`) VALUES
(10, 55, 4, 'Rent', 6500.00, '2026-08-03 16:23:58'),
(11, 56, 5, 'Rent', 6500.00, '2026-08-03 16:23:58'),
(12, 58, 8, 'Rent', 7000.00, '2026-08-03 16:23:58'),
(13, 59, 7, 'Rent', 7000.00, '2026-08-03 16:23:58'),
(14, 61, 25, 'Rent', 6000.00, '2026-08-05 06:25:12'),
(15, 62, 13, 'Rent', 6000.00, '2026-08-05 06:38:50'),
(16, 63, 11, 'Rent', 6000.00, '2026-08-05 13:41:19'),
(17, 64, 15, 'Rent', 6000.00, '2026-08-05 18:25:58'),
(18, 65, 9, 'Rent', 6000.00, '2026-08-06 12:02:10'),
(19, 67, 27, 'Rent', 7500.00, '2026-08-06 16:01:29'),
(20, 68, 16, 'Rent', 6000.00, '2026-08-07 05:20:18'),
(21, 69, 30, 'Rent', 7000.00, '2026-08-09 06:39:17'),
(22, 70, 6, 'Rent', 6000.00, '2026-08-11 10:50:21'),
(23, 71, 21, 'Rent', 6500.00, '2026-08-12 06:30:09'),
(24, 72, 23, 'Rent', 7000.00, '2026-08-12 06:32:15'),
(25, 73, 35, 'Rent', 7000.00, '2026-08-22 15:24:42'),
(26, 74, 38, 'Rent', 6500.00, '2026-08-28 12:45:24'),
(27, 75, 34, 'Rent', 7000.00, '2026-08-28 13:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `payment_allocations_backup_deposit_fix`
--

CREATE TABLE `payment_allocations_backup_deposit_fix` (
  `id` int UNSIGNED NOT NULL DEFAULT '0',
  `payment_id` int UNSIGNED NOT NULL,
  `bill_item_id` int UNSIGNED NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_allocations_backup_deposit_fix`
--

INSERT INTO `payment_allocations_backup_deposit_fix` (`id`, `payment_id`, `bill_item_id`, `category`, `amount`, `created_at`) VALUES
(10, 55, 4, 'Rent', 6500.00, '2026-08-03 16:23:58'),
(11, 56, 5, 'Rent', 6500.00, '2026-08-03 16:23:58'),
(12, 58, 8, 'Rent', 7000.00, '2026-08-03 16:23:58'),
(13, 59, 7, 'Rent', 7000.00, '2026-08-03 16:23:58'),
(14, 61, 25, 'Rent', 6000.00, '2026-08-05 06:25:12'),
(15, 62, 13, 'Rent', 6000.00, '2026-08-05 06:38:50'),
(16, 63, 11, 'Rent', 6000.00, '2026-08-05 13:41:19'),
(17, 64, 15, 'Rent', 6000.00, '2026-08-05 18:25:58'),
(18, 65, 9, 'Rent', 6000.00, '2026-08-06 12:02:10'),
(19, 67, 27, 'Rent', 7500.00, '2026-08-06 16:01:29'),
(20, 68, 16, 'Rent', 6000.00, '2026-08-07 05:20:18'),
(21, 69, 30, 'Rent', 7000.00, '2026-08-09 06:39:17'),
(22, 70, 6, 'Rent', 6000.00, '2026-08-11 10:50:21'),
(23, 71, 21, 'Rent', 6500.00, '2026-08-12 06:30:09'),
(24, 72, 23, 'Rent', 7000.00, '2026-08-12 06:32:15'),
(25, 73, 35, 'Rent', 7000.00, '2026-08-22 15:24:42'),
(26, 74, 38, 'Rent', 6500.00, '2026-08-28 12:45:24'),
(27, 75, 34, 'Rent', 7000.00, '2026-08-28 13:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Apartment Block',
  `units` int UNSIGNED DEFAULT '0',
  `occupied` int UNSIGNED DEFAULT '0',
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caretaker_id` int UNSIGNED DEFAULT NULL,
  `rent` decimal(12,2) DEFAULT '0.00',
  `payment_method_type` enum('paybill','till','bank','mobile_money') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paybill_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paybill_account` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `till_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_money_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `owner_id`, `name`, `address`, `type`, `units`, `occupied`, `image`, `caretaker_id`, `rent`, `payment_method_type`, `paybill_number`, `paybill_account`, `till_number`, `bank_name`, `bank_account`, `bank_branch`, `mobile_money_number`, `created_at`, `updated_at`) VALUES
(34, 7, 'Jakes Apartments', 'Murang\'a', 'Apartment Block', 30, 24, NULL, NULL, 0.00, 'paybill', '247247', '0715038398', NULL, NULL, NULL, NULL, NULL, '2026-08-01 09:58:17', '2026-09-01 09:56:32'),
(35, 12, 'Aluta mtoto apartments ', 'Rajab Manzil House, Tom Mboya St, City Centre', 'Townhouses', 1, 1, NULL, NULL, 0.00, 'paybill', '08791', '5782', NULL, NULL, NULL, NULL, NULL, '2026-08-01 18:24:40', '2026-08-01 18:29:58'),
(36, 13, 'Rentii Suites', 'Block AD kangundo', 'Apartment Block', 3, 3, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-02 13:15:01', '2026-08-06 16:00:19');

-- --------------------------------------------------------

--
-- Table structure for table `property_documents`
--

CREATE TABLE `property_documents` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `property_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('rules','regulations','policy','other') COLLATE utf8mb4_unicode_ci DEFAULT 'rules',
  `version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  `is_active` tinyint(1) DEFAULT '1',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` int DEFAULT '1',
  `window_start` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `ip_address`, `type`, `attempts`, `window_start`, `created_at`, `updated_at`) VALUES
(76, '105.164.5.139', 'register', 1, '2026-07-28 17:01:06', '2026-07-28 17:01:06', '2026-07-28 17:01:06'),
(77, '196.96.236.106', 'api', 6, '2026-07-28 19:38:57', '2026-07-28 19:38:57', '2026-07-28 19:39:29'),
(81, '74.125.184.181', 'api', 1, '2026-07-29 17:10:59', '2026-07-29 17:10:59', '2026-07-29 17:10:59'),
(83, '74.125.184.190', 'api', 1, '2026-07-29 17:16:46', '2026-07-29 17:16:46', '2026-07-29 17:16:46'),
(85, '197.248.82.231', 'api', 12, '2026-09-01 12:24:41', '2026-08-01 09:31:38', '2026-09-01 12:25:36'),
(86, '41.80.116.55', 'api', 8, '2026-08-01 11:37:41', '2026-08-01 09:53:29', '2026-08-01 11:38:02'),
(87, '41.80.116.55', 'login', 1, '2026-08-01 11:37:53', '2026-08-01 09:53:29', '2026-08-01 11:37:53'),
(88, '41.80.119.249', 'api', 14, '2026-08-01 12:15:42', '2026-08-01 09:54:51', '2026-08-01 12:16:23'),
(89, '41.80.119.249', 'login', 1, '2026-08-01 09:54:51', '2026-08-01 09:54:51', '2026-08-01 09:54:51'),
(90, '102.208.171.8', 'api', 29, '2026-08-14 10:52:11', '2026-08-01 10:13:50', '2026-08-14 10:52:26'),
(93, '105.160.95.111', 'api', 10, '2026-08-01 10:40:56', '2026-08-01 10:39:48', '2026-08-01 10:41:04'),
(94, '41.90.5.49', 'api', 4, '2026-08-01 14:33:32', '2026-08-01 14:33:32', '2026-08-01 14:33:33'),
(95, '41.90.5.251', 'api', 3, '2026-08-01 16:50:37', '2026-08-01 16:50:37', '2026-08-01 16:50:38'),
(96, '197.220.99.102', 'api', 8, '2026-08-02 13:09:20', '2026-08-01 18:22:30', '2026-08-02 13:09:21'),
(97, '197.220.99.102', 'register', 1, '2026-08-01 18:22:30', '2026-08-01 18:22:30', '2026-08-01 18:22:30'),
(98, '102.219.208.90', 'api', 4, '2026-08-28 17:52:57', '2026-08-01 19:14:33', '2026-08-28 17:52:58'),
(99, '41.90.4.216', 'api', 5, '2026-08-01 19:23:57', '2026-08-01 19:15:40', '2026-08-01 19:24:38'),
(100, '41.90.4.216', 'login', 1, '2026-08-01 19:15:40', '2026-08-01 19:15:40', '2026-08-01 19:15:40'),
(101, '139.59.136.184', 'api', 3, '2026-08-02 03:51:01', '2026-08-02 03:51:01', '2026-08-02 03:51:03'),
(102, '164.92.244.132', 'api', 3, '2026-08-02 03:51:12', '2026-08-02 03:51:12', '2026-08-02 03:51:17'),
(107, '102.208.171.8', 'login', 1, '2026-08-14 10:49:52', '2026-08-02 08:13:20', '2026-08-14 10:49:52'),
(108, '197.248.82.231', 'register', 1, '2026-08-02 13:13:42', '2026-08-02 13:13:42', '2026-08-02 13:13:42'),
(109, '172.253.192.245', 'api', 1, '2026-08-02 13:16:33', '2026-08-02 13:16:33', '2026-08-02 13:16:33'),
(110, '74.125.184.182', 'api', 1, '2026-08-02 13:56:53', '2026-08-02 13:56:53', '2026-08-02 13:56:53'),
(111, '172.253.234.211', 'api', 1, '2026-08-02 13:57:34', '2026-08-02 13:57:34', '2026-08-02 13:57:34'),
(112, '41.90.231.147', 'api', 6, '2026-08-05 18:33:55', '2026-08-02 17:39:37', '2026-08-05 18:34:43'),
(113, '105.161.15.244', 'api', 8, '2026-08-02 17:55:45', '2026-08-02 17:49:34', '2026-08-02 17:56:12'),
(114, '66.249.93.130', 'api', 1, '2026-08-03 10:01:12', '2026-08-02 17:49:37', '2026-08-03 10:01:12'),
(115, '66.249.88.66', 'api', 1, '2026-08-02 17:49:37', '2026-08-02 17:49:37', '2026-08-02 17:49:37'),
(116, '66.249.88.67', 'api', 1, '2026-08-02 17:49:37', '2026-08-02 17:49:37', '2026-08-02 17:49:37'),
(117, '105.161.15.244', 'login', 1, '2026-08-02 17:56:09', '2026-08-02 17:50:28', '2026-08-02 17:56:09'),
(123, '172.253.216.57', 'api', 1, '2026-08-03 04:57:20', '2026-08-03 04:57:20', '2026-08-03 04:57:20'),
(124, '197.248.71.81', 'api', 2, '2026-08-03 05:50:20', '2026-08-03 05:48:48', '2026-08-03 05:51:04'),
(125, '196.96.235.107', 'api', 3, '2026-08-03 09:23:25', '2026-08-03 08:50:39', '2026-08-03 09:23:26'),
(126, '197.136.9.5', 'api', 1, '2026-08-03 08:50:49', '2026-08-03 08:50:49', '2026-08-03 08:50:49'),
(127, '196.96.235.107', 'login', 1, '2026-08-03 09:15:01', '2026-08-03 08:51:25', '2026-08-03 09:15:01'),
(128, '196.96.60.242', 'api', 16, '2026-08-03 09:03:50', '2026-08-03 09:03:50', '2026-08-03 09:04:29'),
(129, '66.249.93.129', 'api', 1, '2026-08-03 10:01:11', '2026-08-03 10:01:11', '2026-08-03 10:01:11'),
(130, '192.178.11.130', 'api', 1, '2026-08-03 10:01:12', '2026-08-03 10:01:12', '2026-08-03 10:01:12'),
(131, '102.204.14.227', 'api', 9, '2026-08-03 10:09:06', '2026-08-03 10:04:45', '2026-08-03 10:09:18'),
(132, '102.204.14.227', 'login', 2, '2026-08-03 10:07:01', '2026-08-03 10:07:01', '2026-08-03 10:07:15'),
(137, '41.90.231.147', 'login', 1, '2026-08-03 12:42:32', '2026-08-03 12:42:32', '2026-08-03 12:42:32'),
(139, '197.248.217.103', 'api', 11, '2026-08-03 16:01:12', '2026-08-03 15:50:20', '2026-08-03 16:01:14'),
(142, '197.248.217.103', 'login', 2, '2026-08-03 16:00:21', '2026-08-03 15:59:02', '2026-08-03 16:00:49'),
(143, '74.125.184.183', 'api', 1, '2026-08-03 17:11:21', '2026-08-03 17:11:21', '2026-08-03 17:11:21'),
(144, '154.159.252.229', 'api', 1, '2026-08-03 17:11:30', '2026-08-03 17:11:30', '2026-08-03 17:11:30'),
(145, '102.204.13.243', 'api', 1, '2026-08-12 07:30:51', '2026-08-03 17:12:05', '2026-08-12 07:30:51'),
(146, '102.204.13.243', 'login', 1, '2026-08-03 17:49:14', '2026-08-03 17:12:28', '2026-08-03 17:49:14'),
(148, '102.219.208.90', 'login', 1, '2026-08-20 17:54:50', '2026-08-03 17:16:39', '2026-08-20 17:54:50'),
(149, '173.194.92.183', 'api', 1, '2026-08-03 17:50:38', '2026-08-03 17:50:38', '2026-08-03 17:50:38'),
(150, '105.161.72.224', 'api', 21, '2026-08-03 18:44:57', '2026-08-03 18:43:53', '2026-08-03 18:45:26'),
(151, '197.155.95.4', 'api', 1, '2026-08-03 18:44:11', '2026-08-03 18:44:11', '2026-08-03 18:44:11'),
(152, '105.161.72.224', 'login', 1, '2026-08-03 18:44:57', '2026-08-03 18:44:57', '2026-08-03 18:44:57'),
(153, '102.213.49.4', 'api', 14, '2026-08-05 18:48:10', '2026-08-03 18:46:48', '2026-08-05 18:48:40'),
(155, '154.159.237.144', 'api', 2, '2026-08-03 18:56:06', '2026-08-03 18:49:38', '2026-08-03 18:56:41'),
(157, '154.159.237.144', 'register', 1, '2026-08-03 18:52:56', '2026-08-03 18:52:56', '2026-08-03 18:52:56'),
(159, '197.248.141.215', 'api', 27, '2026-08-09 18:03:24', '2026-08-04 07:40:00', '2026-08-09 18:04:18'),
(160, '197.248.141.215', 'login', 1, '2026-08-09 18:03:43', '2026-08-04 07:41:26', '2026-08-09 18:03:43'),
(161, '105.161.114.230', 'api', 26, '2026-08-04 09:57:44', '2026-08-04 09:56:44', '2026-08-04 09:58:28'),
(162, '105.161.114.230', 'login', 2, '2026-08-04 09:57:14', '2026-08-04 09:57:14', '2026-08-04 09:57:30'),
(163, '66.249.93.131', 'api', 1, '2026-08-04 09:57:52', '2026-08-04 09:57:52', '2026-08-04 09:57:52'),
(164, '192.178.11.129', 'api', 1, '2026-08-04 09:57:52', '2026-08-04 09:57:52', '2026-08-04 09:57:52'),
(165, '192.178.11.131', 'api', 1, '2026-08-10 16:39:27', '2026-08-04 09:57:53', '2026-08-10 16:39:27'),
(166, '196.97.1.155', 'api', 7, '2026-08-04 20:17:31', '2026-08-04 20:17:31', '2026-08-04 20:17:42'),
(167, '196.201.218.35', 'api', 9, '2026-08-05 18:25:09', '2026-08-05 13:39:40', '2026-08-05 18:26:00'),
(169, '196.96.112.252', 'api', 14, '2026-08-05 18:34:44', '2026-08-05 18:33:41', '2026-08-05 18:34:57'),
(170, '196.96.112.252', 'login', 1, '2026-08-05 18:34:24', '2026-08-05 18:34:24', '2026-08-05 18:34:24'),
(174, '102.213.49.4', 'forgot_password', 2, '2026-08-05 18:45:37', '2026-08-05 18:45:37', '2026-08-05 18:47:16'),
(175, '102.213.49.4', 'login', 1, '2026-08-05 18:48:10', '2026-08-05 18:48:10', '2026-08-05 18:48:10'),
(176, '105.161.203.182', 'api', 38, '2026-08-06 05:01:41', '2026-08-06 04:42:57', '2026-08-06 05:02:11'),
(177, '105.161.203.182', 'login', 1, '2026-08-06 05:02:04', '2026-08-06 04:43:18', '2026-08-06 05:02:04'),
(178, '154.159.237.233', 'api', 2, '2026-08-06 13:04:46', '2026-08-06 13:04:46', '2026-08-06 13:04:46'),
(180, '41.80.118.122', 'api', 3, '2026-08-06 13:08:06', '2026-08-06 13:08:06', '2026-08-06 13:08:06'),
(181, '172.253.192.115', 'api', 1, '2026-08-06 15:39:08', '2026-08-06 15:39:08', '2026-08-06 15:39:08'),
(182, '172.253.15.238', 'api', 1, '2026-08-06 15:39:08', '2026-08-06 15:39:08', '2026-08-06 15:39:08'),
(183, '41.90.230.207', 'api', 1, '2026-08-07 06:47:03', '2026-08-07 06:47:03', '2026-08-07 06:47:03'),
(184, '172.253.216.49', 'api', 1, '2026-08-08 11:45:10', '2026-08-08 11:45:10', '2026-08-08 11:45:10'),
(185, '196.96.237.96', 'api', 3, '2026-08-09 06:43:24', '2026-08-09 06:06:13', '2026-08-09 06:43:25'),
(186, '196.96.237.96', 'login', 1, '2026-08-09 06:06:13', '2026-08-09 06:06:13', '2026-08-09 06:06:13'),
(187, '154.159.252.139', 'api', 4, '2026-08-09 06:39:23', '2026-08-09 06:08:27', '2026-08-09 06:39:24'),
(188, '173.194.92.187', 'api', 1, '2026-08-09 06:08:34', '2026-08-09 06:08:34', '2026-08-09 06:08:34'),
(189, '154.159.252.139', 'login', 1, '2026-08-09 06:33:01', '2026-08-09 06:09:16', '2026-08-09 06:33:01'),
(190, '172.253.192.255', 'api', 1, '2026-08-09 06:37:30', '2026-08-09 06:37:30', '2026-08-09 06:37:30'),
(191, '196.96.239.184', 'api', 2, '2026-08-09 06:43:24', '2026-08-09 06:38:36', '2026-08-09 06:43:24'),
(192, '196.201.210.197', 'api', 4, '2026-08-09 17:38:30', '2026-08-09 17:38:30', '2026-08-09 17:38:31'),
(193, '102.204.198.92', 'api', 36, '2026-08-09 18:58:17', '2026-08-09 18:53:36', '2026-08-09 18:59:09'),
(194, '102.204.198.92', 'login', 1, '2026-08-09 18:54:56', '2026-08-09 18:54:56', '2026-08-09 18:54:56'),
(195, '41.90.130.66', 'api', 6, '2026-08-10 07:54:38', '2026-08-10 07:54:38', '2026-08-10 07:54:59'),
(196, '196.201.218.30', 'api', 4, '2026-08-10 16:43:27', '2026-08-10 16:36:22', '2026-08-10 16:43:27'),
(197, '74.125.208.227', 'api', 1, '2026-09-01 09:18:34', '2026-08-10 16:39:27', '2026-09-01 09:18:34'),
(198, '64.233.172.45', 'api', 1, '2026-08-10 16:39:27', '2026-08-10 16:39:27', '2026-08-10 16:39:27'),
(199, '105.164.79.246', 'api', 3, '2026-08-11 05:07:34', '2026-08-11 05:07:34', '2026-08-11 05:07:35'),
(201, '105.160.9.149', 'api', 7, '2026-08-11 10:50:52', '2026-08-11 10:49:48', '2026-08-11 10:51:35'),
(202, '105.160.53.208', 'api', 7, '2026-08-11 16:50:50', '2026-08-11 16:47:48', '2026-08-11 16:51:20'),
(203, '105.161.164.71', 'api', 1, '2026-08-12 06:39:38', '2026-08-12 06:28:09', '2026-08-12 06:39:38'),
(204, '105.161.164.71', 'login', 1, '2026-08-12 06:33:52', '2026-08-12 06:33:52', '2026-08-12 06:33:52'),
(205, '105.161.152.103', 'api', 7, '2026-08-12 09:47:27', '2026-08-12 09:47:27', '2026-08-12 09:47:43'),
(206, '154.159.237.158', 'api', 5, '2026-08-12 09:51:43', '2026-08-12 09:51:43', '2026-08-12 09:52:31'),
(212, '136.109.167.91', 'api', 13, '2026-08-12 22:55:19', '2026-08-12 22:55:19', '2026-08-12 22:55:21'),
(213, '154.159.237.152', 'api', 1, '2026-08-13 08:07:19', '2026-08-13 08:07:19', '2026-08-13 08:07:19'),
(215, '172.253.15.237', 'api', 1, '2026-08-14 10:49:50', '2026-08-14 10:49:50', '2026-08-14 10:49:50'),
(216, '154.159.252.129', 'api', 9, '2026-08-15 06:28:51', '2026-08-15 06:28:51', '2026-08-15 06:28:53'),
(217, '154.159.252.0', 'api', 10, '2026-08-20 02:49:14', '2026-08-20 02:37:47', '2026-08-20 02:49:30'),
(218, '154.159.252.0', 'login', 1, '2026-08-20 02:49:22', '2026-08-20 02:37:57', '2026-08-20 02:49:22'),
(219, '102.213.49.86', 'api', 7, '2026-08-22 16:26:29', '2026-08-20 05:16:01', '2026-08-22 16:26:30'),
(220, '102.213.49.86', 'login', 1, '2026-08-22 16:24:44', '2026-08-20 05:20:13', '2026-08-22 16:24:44'),
(221, '154.159.252.240', 'api', 1, '2026-08-21 04:30:25', '2026-08-21 04:20:24', '2026-08-21 04:30:25'),
(222, '154.159.252.240', 'login', 1, '2026-08-21 04:26:35', '2026-08-21 04:20:48', '2026-08-21 04:26:35'),
(223, '154.159.252.158', 'api', 1, '2026-08-21 13:30:01', '2026-08-21 13:26:23', '2026-08-21 13:30:01'),
(224, '154.159.252.158', 'login', 1, '2026-08-21 13:28:17', '2026-08-21 13:28:17', '2026-08-21 13:28:17'),
(225, '154.159.252.11', 'api', 9, '2026-08-22 16:55:40', '2026-08-22 07:24:16', '2026-08-22 16:55:57'),
(226, '172.253.15.234', 'api', 1, '2026-08-22 07:24:19', '2026-08-22 07:24:19', '2026-08-22 07:24:19'),
(227, '154.159.252.11', 'login', 1, '2026-08-22 16:55:49', '2026-08-22 07:24:31', '2026-08-22 16:55:49'),
(228, '173.194.92.182', 'api', 1, '2026-08-22 08:19:11', '2026-08-22 08:19:11', '2026-08-22 08:19:11'),
(229, '102.213.49.14', 'api', 17, '2026-08-22 08:21:23', '2026-08-22 08:19:15', '2026-08-22 08:21:30'),
(230, '102.213.49.14', 'login', 2, '2026-08-22 08:20:02', '2026-08-22 08:20:02', '2026-08-22 08:20:53'),
(231, '196.96.234.169', 'api', 8, '2026-08-22 15:24:14', '2026-08-22 15:17:49', '2026-08-22 15:24:43'),
(232, '102.2.76.150', 'api', 9, '2026-08-22 15:49:59', '2026-08-22 15:24:16', '2026-08-22 15:50:52'),
(233, '102.2.76.150', 'login', 1, '2026-08-22 15:48:57', '2026-08-22 15:28:42', '2026-08-22 15:48:57'),
(234, '129.222.187.235', 'api', 1, '2026-08-22 15:51:41', '2026-08-22 15:50:38', '2026-08-22 15:51:41'),
(237, '129.222.187.235', 'forgot_password', 1, '2026-08-22 15:51:41', '2026-08-22 15:51:41', '2026-08-22 15:51:41'),
(238, '172.253.15.232', 'api', 1, '2026-08-22 16:55:41', '2026-08-22 16:55:41', '2026-08-22 16:55:41'),
(239, '154.159.252.50', 'api', 2, '2026-08-24 17:43:10', '2026-08-24 17:41:25', '2026-08-24 17:43:10'),
(240, '154.159.252.94', 'api', 4, '2026-08-24 17:45:19', '2026-08-24 17:45:19', '2026-08-24 17:45:21'),
(241, '154.159.252.132', 'api', 19, '2026-08-25 11:48:28', '2026-08-25 11:44:13', '2026-08-25 11:48:44'),
(242, '154.159.252.132', 'login', 2, '2026-08-25 11:46:05', '2026-08-25 11:46:05', '2026-08-25 11:46:42'),
(249, '172.253.7.124', 'api', 1, '2026-08-26 06:12:55', '2026-08-26 06:12:55', '2026-08-26 06:12:55'),
(250, '154.159.252.225', 'api', 4, '2026-08-27 19:20:23', '2026-08-27 18:25:16', '2026-08-27 19:20:24'),
(251, '172.253.15.229', 'api', 1, '2026-08-28 13:59:29', '2026-08-28 13:59:29', '2026-08-28 13:59:29'),
(252, '102.213.49.10', 'api', 2, '2026-08-28 14:00:00', '2026-08-28 14:00:00', '2026-08-28 14:00:18'),
(253, '142.250.32.97', 'api', 2, '2026-08-28 14:00:02', '2026-08-28 14:00:02', '2026-08-28 14:00:02'),
(254, '192.178.11.2', 'api', 1, '2026-08-28 14:00:02', '2026-08-28 14:00:02', '2026-08-28 14:00:02'),
(255, '41.90.4.219', 'api', 1, '2026-08-30 05:22:44', '2026-08-30 05:22:44', '2026-08-30 05:22:44'),
(256, '41.90.217.168', 'api', 3, '2026-08-30 05:22:57', '2026-08-30 05:22:57', '2026-08-30 05:23:56'),
(257, '74.125.208.225', 'api', 1, '2026-09-01 09:18:33', '2026-08-30 05:23:03', '2026-09-01 09:18:33'),
(258, '74.125.208.226', 'api', 1, '2026-08-30 05:23:03', '2026-08-30 05:23:03', '2026-08-30 05:23:03'),
(259, '66.102.9.161', 'api', 1, '2026-08-30 05:23:04', '2026-08-30 05:23:04', '2026-08-30 05:23:04'),
(260, '154.159.252.61', 'api', 4, '2026-08-30 19:16:32', '2026-08-30 18:59:06', '2026-08-30 19:16:40'),
(261, '172.253.192.119', 'api', 1, '2026-08-30 18:59:10', '2026-08-30 18:59:10', '2026-08-30 18:59:10'),
(262, '154.159.252.61', 'login', 1, '2026-08-30 19:11:03', '2026-08-30 18:59:16', '2026-08-30 19:11:03'),
(263, '41.80.116.10', 'api', 1, '2026-08-31 09:05:46', '2026-08-31 09:01:41', '2026-08-31 09:05:46'),
(264, '41.80.116.10', 'login', 1, '2026-08-31 09:05:20', '2026-08-31 09:01:41', '2026-08-31 09:05:20'),
(266, '197.248.82.231', 'login', 1, '2026-09-01 12:24:44', '2026-08-31 09:08:18', '2026-09-01 12:24:44'),
(267, '192.178.11.3', 'api', 1, '2026-08-31 09:09:40', '2026-08-31 09:09:40', '2026-08-31 09:09:40'),
(268, '66.102.9.162', 'api', 1, '2026-08-31 09:09:40', '2026-08-31 09:09:40', '2026-08-31 09:09:40'),
(269, '105.160.94.226', 'api', 13, '2026-09-01 06:14:43', '2026-09-01 06:12:25', '2026-09-01 06:15:06'),
(270, '105.160.94.226', 'login', 1, '2026-09-01 06:14:20', '2026-09-01 06:14:20', '2026-09-01 06:14:20'),
(271, '105.161.217.50', 'api', 10, '2026-09-01 06:33:19', '2026-09-01 06:31:05', '2026-09-01 06:33:43'),
(272, '105.161.217.50', 'login', 1, '2026-09-01 06:33:40', '2026-09-01 06:31:21', '2026-09-01 06:33:40'),
(273, '142.250.32.98', 'api', 1, '2026-09-01 08:41:24', '2026-09-01 08:41:24', '2026-09-01 08:41:24'),
(274, '142.250.32.99', 'api', 1, '2026-09-01 08:41:25', '2026-09-01 08:41:25', '2026-09-01 08:41:25'),
(275, '192.178.11.4', 'api', 1, '2026-09-01 09:18:33', '2026-09-01 09:18:33', '2026-09-01 09:18:33'),
(276, '154.159.252.12', 'api', 1, '2026-09-01 11:03:02', '2026-09-01 11:01:57', '2026-09-01 11:03:02'),
(277, '154.159.252.12', 'login', 1, '2026-09-01 11:02:15', '2026-09-01 11:02:15', '2026-09-01 11:02:15'),
(278, '41.90.217.137', 'api', 1, '2026-09-01 12:25:16', '2026-09-01 12:24:07', '2026-09-01 12:25:16'),
(279, '102.0.19.200', 'api', 1, '2026-09-01 12:26:43', '2026-09-01 12:26:43', '2026-09-01 12:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `rent_reminders`
--

CREATE TABLE `rent_reminders` (
  `id` int NOT NULL,
  `owner_id` int NOT NULL,
  `tenant_id` int DEFAULT NULL,
  `house_id` int NOT NULL,
  `bill_id` int DEFAULT NULL,
  `month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `days_before_due` int DEFAULT NULL,
  `sent_at` datetime NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_audit_log`
--

CREATE TABLE `security_audit_log` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` int DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `severity` enum('info','warning','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `security_audit_log`
--

INSERT INTO `security_audit_log` (`id`, `user_id`, `ip_address`, `action`, `resource_type`, `resource_id`, `details`, `severity`, `created_at`) VALUES
(142, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 09:31:38'),
(143, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 09:53:29'),
(144, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 09:54:41'),
(145, NULL, '41.80.119.249', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 09:54:51'),
(146, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-01 10:14:55'),
(147, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:18:22'),
(148, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:18:57'),
(149, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:19:04'),
(150, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:19:13'),
(151, NULL, '102.208.171.8', 'login_failed', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-01 10:19:19'),
(152, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:19:23'),
(153, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:19:36'),
(154, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:26:56'),
(155, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-01 10:27:57'),
(156, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:36:51'),
(157, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 10:53:05'),
(158, NULL, '41.80.116.55', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 11:37:54'),
(159, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 12:10:41'),
(160, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 14:16:54'),
(161, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 14:37:32'),
(162, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"felistajepchirchir@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-01 14:49:21'),
(163, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 14:57:54'),
(164, NULL, '41.90.4.216', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-01 19:15:40'),
(165, NULL, '102.208.171.8', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-02 08:10:45'),
(166, NULL, '102.208.171.8', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-02 08:10:50'),
(167, NULL, '102.208.171.8', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-02 08:11:13'),
(168, NULL, '102.208.171.8', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-02 08:12:38'),
(169, NULL, '102.208.171.8', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-02 08:13:00'),
(170, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-02 08:13:20'),
(171, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-02 13:17:06'),
(172, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-02 13:17:15'),
(173, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-02 13:31:32'),
(174, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-02 13:31:59'),
(175, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-02 13:32:14'),
(176, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-02 13:32:52'),
(177, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-02 13:45:18'),
(178, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-02 13:58:07'),
(179, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-02 16:21:17'),
(180, NULL, '105.161.15.244', 'login_success', 'authentication', NULL, '{\"email\":\"bichiinicole@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-02 17:50:28'),
(181, NULL, '105.161.15.244', 'login_success', 'authentication', NULL, '{\"email\":\"bichiinicole@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-02 17:51:59'),
(182, NULL, '105.161.15.244', 'login_success', 'authentication', NULL, '{\"email\":\"bichiinicole@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-02 17:56:09'),
(183, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 04:31:00'),
(184, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 04:38:22'),
(185, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 04:38:34'),
(186, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 04:38:41'),
(187, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 04:38:49'),
(188, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 04:38:58'),
(189, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 04:39:29'),
(190, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"njorogeloice602@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 04:50:56'),
(191, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 04:51:02'),
(192, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 04:51:15'),
(193, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 04:51:56'),
(194, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 04:55:42'),
(195, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 05:12:20'),
(196, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 08:31:39'),
(197, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 08:31:52'),
(198, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 08:37:23'),
(199, NULL, '196.96.235.107', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 08:51:25'),
(200, NULL, '196.96.235.107', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 09:15:01'),
(201, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 09:37:53'),
(202, NULL, '102.204.14.227', 'login_success', 'authentication', NULL, '{\"email\":\"mureithisheila968@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 10:07:01'),
(203, NULL, '102.204.14.227', 'login_success', 'authentication', NULL, '{\"email\":\"mureithisheila968@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 10:07:15'),
(204, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 10:28:35'),
(205, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 11:46:43'),
(206, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 11:56:51'),
(207, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 11:57:04'),
(208, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 11:57:17'),
(209, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 11:57:56'),
(210, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 11:58:08'),
(211, NULL, '41.90.231.147', 'login_success', 'authentication', NULL, '{\"email\":\"wanjikuaustin84@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 12:42:32'),
(212, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 12:50:17'),
(213, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 12:50:33'),
(214, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 14:53:37'),
(215, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 15:03:52'),
(216, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 15:15:53'),
(217, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-03 15:38:57'),
(218, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 15:48:53'),
(219, NULL, '197.248.217.103', 'login_failed', 'authentication', NULL, '{\"email\":\"ruthjepchumba731@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 15:53:29'),
(220, NULL, '197.248.217.103', 'login_failed', 'authentication', NULL, '{\"email\":\"ruthjepchumba731@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 15:53:36'),
(221, NULL, '197.248.217.103', 'login_success', 'authentication', NULL, '{\"email\":\"ruthjepchumba731@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 15:59:02'),
(222, NULL, '197.248.217.103', 'login_success', 'authentication', NULL, '{\"email\":\"ruthjepchumba731@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 16:00:21'),
(223, NULL, '197.248.217.103', 'login_success', 'authentication', NULL, '{\"email\":\"ruthjepchumba731@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 16:00:49'),
(224, NULL, '102.204.13.243', 'login_success', 'authentication', NULL, '{\"email\":\"lennynjoroge325@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 17:12:28'),
(225, NULL, '102.219.208.90', 'login_failed', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 17:16:19'),
(226, NULL, '102.219.208.90', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 17:16:39'),
(227, NULL, '102.204.13.243', 'login_success', 'authentication', NULL, '{\"email\":\"lennynjoroge325@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 17:49:14'),
(228, NULL, '102.219.208.90', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-03 18:27:38'),
(229, NULL, '105.161.72.224', 'login_success', 'authentication', NULL, '{\"email\":\"juliahkahora@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 18:44:57'),
(230, NULL, '102.213.49.4', 'login_success', 'authentication', NULL, '{\"email\":\"bestadryan01@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-03 18:48:26'),
(231, NULL, '154.159.237.144', 'login_failed', 'authentication', NULL, '{\"email\":\"cherutotracy12@gmail.co\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 18:51:25'),
(232, NULL, '154.159.237.144', 'login_failed', 'authentication', NULL, '{\"email\":\"cherutotracy12@gmail.co\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-03 18:53:43'),
(233, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-04 04:29:20'),
(234, NULL, '197.248.141.215', 'login_success', 'authentication', NULL, '{\"email\":\"mbithesylvia404@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-04 07:41:27'),
(235, NULL, '105.161.114.230', 'login_success', 'authentication', NULL, '{\"email\":\"bichiinicole@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-04 09:57:14'),
(236, NULL, '105.161.114.230', 'login_success', 'authentication', NULL, '{\"email\":\"bichiinicole@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-04 09:57:30'),
(237, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-05 06:33:30'),
(238, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-05 14:39:32'),
(239, NULL, '196.96.112.252', 'login_success', 'authentication', NULL, '{\"email\":\"juliahkahora@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-05 18:34:24'),
(240, NULL, '102.213.49.4', 'login_failed', 'authentication', NULL, '{\"email\":\"bestadryan01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-05 18:44:49'),
(241, NULL, '102.213.49.4', 'login_failed', 'authentication', NULL, '{\"email\":\"bestadryan01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-05 18:45:02'),
(242, NULL, '102.213.49.4', 'login_failed', 'authentication', NULL, '{\"email\":\"bestadryan01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-05 18:45:07'),
(243, NULL, '102.213.49.4', 'login_failed', 'authentication', NULL, '{\"email\":\"bestadryan01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-05 18:45:14'),
(244, NULL, '102.213.49.4', 'login_success', 'authentication', NULL, '{\"email\":\"bestadryan01@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-05 18:48:11'),
(245, NULL, '105.161.203.182', 'login_success', 'authentication', NULL, '{\"email\":\"juliahkahora@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-06 04:43:18'),
(246, NULL, '105.161.203.182', 'login_success', 'authentication', NULL, '{\"email\":\"juliahkahora@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-06 04:43:55'),
(247, NULL, '105.161.203.182', 'login_success', 'authentication', NULL, '{\"email\":\"juliahkahora@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-06 05:02:05'),
(248, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-06 08:49:58'),
(249, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-06 15:53:59'),
(250, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-06 15:58:25'),
(251, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-07 07:57:01'),
(252, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-08 10:13:21'),
(253, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-08 10:13:53'),
(254, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-08 11:16:13'),
(255, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-08 11:56:19'),
(256, NULL, '196.96.237.96', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-09 06:06:13'),
(257, NULL, '154.159.252.139', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-09 06:09:16'),
(258, NULL, '154.159.252.139', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-09 06:33:01'),
(259, NULL, '197.248.141.215', 'login_success', 'authentication', NULL, '{\"email\":\"mbithesylvia404@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-09 17:59:35'),
(260, NULL, '197.248.141.215', 'login_success', 'authentication', NULL, '{\"email\":\"mbithesylvia404@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-09 18:03:43'),
(261, NULL, '102.204.198.92', 'login_success', 'authentication', NULL, '{\"email\":\"michelkavesh01@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-09 18:54:56'),
(262, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-10 07:00:53'),
(263, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 04:23:17'),
(264, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-12 04:30:10'),
(265, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 04:36:25'),
(266, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-12 04:36:32'),
(267, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 04:48:17'),
(268, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 04:54:14'),
(269, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 04:54:32'),
(270, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-12 04:54:39'),
(271, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 04:55:27'),
(272, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 05:42:21'),
(273, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 06:27:38'),
(274, NULL, '105.161.164.71', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-12 06:33:52'),
(275, NULL, '154.159.237.158', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-12 09:51:43'),
(276, NULL, '154.159.237.158', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-12 09:51:54'),
(277, NULL, '154.159.237.158', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-12 09:52:09'),
(278, NULL, '154.159.237.158', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-12 09:52:20'),
(279, NULL, '154.159.237.158', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-12 09:52:31'),
(280, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-12 10:50:31'),
(281, NULL, '154.159.237.152', 'login_failed', 'authentication', NULL, '{\"email\":\"josephokotch01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-13 08:07:19'),
(282, NULL, '102.208.171.8', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-14 10:49:52'),
(283, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-19 06:01:05'),
(284, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-19 12:40:03'),
(285, NULL, '154.159.252.0', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-20 02:37:57'),
(286, NULL, '154.159.252.0', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-20 02:49:22'),
(287, NULL, '102.213.49.86', 'login_success', 'authentication', NULL, '{\"email\":\"stphn0445@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-20 05:20:13'),
(288, NULL, '102.219.208.90', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-20 17:54:50'),
(289, NULL, '154.159.252.240', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-21 04:20:48'),
(290, NULL, '154.159.252.240', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-21 04:26:35'),
(291, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-21 05:34:27'),
(292, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-21 05:47:11'),
(293, NULL, '154.159.252.158', 'login_success', 'authentication', NULL, '{\"email\":\"pierrahprecious@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-21 13:28:17'),
(294, NULL, '154.159.252.11', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-22 07:24:31'),
(295, NULL, '102.213.49.14', 'login_success', 'authentication', NULL, '{\"email\":\"israeldylan001@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-22 08:20:02'),
(296, NULL, '102.213.49.14', 'login_success', 'authentication', NULL, '{\"email\":\"israeldylan001@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-22 08:20:54'),
(297, NULL, '102.2.76.150', 'login_success', 'authentication', NULL, '{\"email\":\"biammokua@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-22 15:28:42'),
(298, NULL, '102.2.76.150', 'login_success', 'authentication', NULL, '{\"email\":\"biammokua@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-22 15:48:57'),
(299, NULL, '129.222.187.235', 'login_failed', 'authentication', NULL, '{\"email\":\"bestadryan01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-22 15:51:14'),
(300, NULL, '129.222.187.235', 'login_failed', 'authentication', NULL, '{\"email\":\"bestadryan01@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-22 15:51:28'),
(301, NULL, '102.213.49.86', 'login_success', 'authentication', NULL, '{\"email\":\"stphn0445@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-22 16:24:44'),
(302, NULL, '154.159.252.11', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-22 16:55:49'),
(303, NULL, '154.159.252.132', 'login_success', 'authentication', NULL, '{\"email\":\"mureithisheila968@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-25 11:46:05'),
(304, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-25 11:46:25'),
(305, NULL, '154.159.252.132', 'login_success', 'authentication', NULL, '{\"email\":\"mureithisheila968@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-25 11:46:42'),
(306, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-25 11:46:51'),
(307, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-25 11:47:06'),
(308, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-25 11:47:25'),
(309, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-25 11:47:29'),
(310, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-25 11:47:31'),
(311, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-25 11:47:36'),
(312, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-26 06:13:59'),
(313, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-28 11:43:57'),
(314, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-28 11:44:57'),
(315, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-30 11:33:46'),
(316, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-30 11:48:21'),
(317, NULL, '154.159.252.61', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-30 18:59:16'),
(318, NULL, '154.159.252.61', 'login_success', 'authentication', NULL, '{\"email\":\"faresnthiwa@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-30 19:11:03'),
(319, NULL, '41.80.116.10', 'login_success', 'authentication', NULL, '{\"email\":\"cherutotracy12@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-31 09:01:42'),
(320, NULL, '41.80.116.10', 'login_success', 'authentication', NULL, '{\"email\":\"cherutotracy12@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-31 09:05:20'),
(321, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"karenjuduncan750@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-31 09:07:27'),
(322, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"njorogeloice602@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-31 09:07:50'),
(323, NULL, '197.248.82.231', 'login_failed', 'authentication', NULL, '{\"email\":\"dokeyo390@gmail.com\",\"role\":\"unknown\",\"success\":false}', 'warning', '2026-08-31 09:08:06'),
(324, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-31 09:08:18'),
(325, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-31 09:08:51'),
(326, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-31 09:09:05'),
(327, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-31 14:35:10'),
(328, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"rentalflow.realestate@gmail.com\",\"role\":\"caretaker\",\"success\":true}', 'info', '2026-08-31 14:35:21'),
(329, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-31 14:35:33'),
(330, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"duncankarenju750@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-08-31 15:00:19'),
(331, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-08-31 15:11:45'),
(332, NULL, '105.160.94.226', 'login_success', 'authentication', NULL, '{\"email\":\"gkimesis@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-09-01 06:14:20'),
(333, NULL, '105.161.217.50', 'login_success', 'authentication', NULL, '{\"email\":\"biammokua@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-09-01 06:31:21'),
(334, NULL, '105.161.217.50', 'login_success', 'authentication', NULL, '{\"email\":\"biammokua@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-09-01 06:33:40'),
(335, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-09-01 08:38:04'),
(336, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-09-01 09:24:20'),
(337, NULL, '154.159.252.12', 'login_success', 'authentication', NULL, '{\"email\":\"biammokua@gmail.com\",\"role\":\"tenant\",\"success\":true}', 'info', '2026-09-01 11:02:15'),
(338, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-09-01 11:25:47'),
(339, NULL, '197.248.82.231', 'login_success', 'authentication', NULL, '{\"email\":\"bethjabs@gmail.com\",\"role\":\"owner\",\"success\":true}', 'info', '2026-09-01 12:24:44');

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('whatsapp','email','sms') COLLATE utf8mb4_unicode_ci DEFAULT 'email',
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `owner_id`, `name`, `type`, `subject`, `body`, `created_at`) VALUES
(97, 12, 'Rent Reminder', 'email', 'Rent Reminder - {{month}}', 'Dear {{tenant}},\\n\\nThis is a friendly reminder that your rent of KES {{amount}} for {{month}} is due on {{dueDate}}.\\n\\nThank you,\\n{{owner}}', '2026-08-01 18:22:30'),
(98, 12, 'Rent Confirmation', 'whatsapp', '', 'Hi {{tenant}},\\n\\nYour payment of KES {{amount}} for {{month}} has been received. Receipt: {{receipt}}', '2026-08-01 18:22:30'),
(99, 12, 'Maintenance Notice', 'email', 'Scheduled Maintenance - {{property}}', 'Dear Residents,\\n\\nPlease be informed that maintenance will be conducted on {{date}} from {{time}}: {{details}}\\n\\nThank you for your patience.', '2026-08-01 18:22:30'),
(100, 12, 'Complaint Update', 'email', 'Update on Your Complaint #{{id}}', 'Dear {{tenant}},\\n\\nYour complaint regarding {{issue}} has been updated to status: {{status}}.\\n\\n{{message}}', '2026-08-01 18:22:30'),
(101, 12, 'Lease Renewal', 'email', 'Lease Renewal Notice', 'Dear {{tenant}},\\n\\nYour lease for {{house}} expires on {{date}}. Please contact us to discuss renewal options.\\n\\nBest regards,\\n{{owner}}', '2026-08-01 18:22:30'),
(102, 12, 'Password Reset', 'email', 'Password Reset Code - {{code}}', 'Dear {{name}},\\n\\nYou requested a password reset. Use the following code to reset your password:\\n\\nCode: {{code}}\\n\\nThis code expires in {{expires}}.\\n\\nIf you did not request this, please ignore this email.\\n\\nBest regards,\\nRentaFlow', '2026-08-01 18:22:30'),
(103, 13, 'Rent Reminder', 'email', 'Rent Reminder - {{month}}', 'Dear {{tenant}},\\n\\nThis is a friendly reminder that your rent of KES {{amount}} for {{month}} is due on {{dueDate}}.\\n\\nThank you,\\n{{owner}}', '2026-08-02 13:13:42'),
(104, 13, 'Rent Confirmation', 'whatsapp', '', 'Hi {{tenant}},\\n\\nYour payment of KES {{amount}} for {{month}} has been received. Receipt: {{receipt}}', '2026-08-02 13:13:42'),
(105, 13, 'Maintenance Notice', 'email', 'Scheduled Maintenance - {{property}}', 'Dear Residents,\\n\\nPlease be informed that maintenance will be conducted on {{date}} from {{time}}: {{details}}\\n\\nThank you for your patience.', '2026-08-02 13:13:42'),
(106, 13, 'Complaint Update', 'email', 'Update on Your Complaint #{{id}}', 'Dear {{tenant}},\\n\\nYour complaint regarding {{issue}} has been updated to status: {{status}}.\\n\\n{{message}}', '2026-08-02 13:13:42'),
(107, 13, 'Lease Renewal', 'email', 'Lease Renewal Notice', 'Dear {{tenant}},\\n\\nYour lease for {{house}} expires on {{date}}. Please contact us to discuss renewal options.\\n\\nBest regards,\\n{{owner}}', '2026-08-02 13:13:42'),
(108, 13, 'Password Reset', 'email', 'Password Reset Code - {{code}}', 'Dear {{name}},\\n\\nYou requested a password reset. Use the following code to reset your password:\\n\\nCode: {{code}}\\n\\nThis code expires in {{expires}}.\\n\\nIf you did not request this, please ignore this email.\\n\\nBest regards,\\nRentaFlow', '2026-08-02 13:13:42'),
(109, 14, 'Rent Reminder', 'email', 'Rent Reminder - {{month}}', 'Dear {{tenant}},\\n\\nThis is a friendly reminder that your rent of KES {{amount}} for {{month}} is due on {{dueDate}}.\\n\\nThank you,\\n{{owner}}', '2026-08-03 18:52:56'),
(110, 14, 'Rent Confirmation', 'whatsapp', '', 'Hi {{tenant}},\\n\\nYour payment of KES {{amount}} for {{month}} has been received. Receipt: {{receipt}}', '2026-08-03 18:52:56'),
(111, 14, 'Maintenance Notice', 'email', 'Scheduled Maintenance - {{property}}', 'Dear Residents,\\n\\nPlease be informed that maintenance will be conducted on {{date}} from {{time}}: {{details}}\\n\\nThank you for your patience.', '2026-08-03 18:52:56'),
(112, 14, 'Complaint Update', 'email', 'Update on Your Complaint #{{id}}', 'Dear {{tenant}},\\n\\nYour complaint regarding {{issue}} has been updated to status: {{status}}.\\n\\n{{message}}', '2026-08-03 18:52:56'),
(113, 14, 'Lease Renewal', 'email', 'Lease Renewal Notice', 'Dear {{tenant}},\\n\\nYour lease for {{house}} expires on {{date}}. Please contact us to discuss renewal options.\\n\\nBest regards,\\n{{owner}}', '2026-08-03 18:52:56'),
(114, 14, 'Password Reset', 'email', 'Password Reset Code - {{code}}', 'Dear {{name}},\\n\\nYou requested a password reset. Use the following code to reset your password:\\n\\nCode: {{code}}\\n\\nThis code expires in {{expires}}.\\n\\nIf you did not request this, please ignore this email.\\n\\nBest regards,\\nRentaFlow', '2026-08-03 18:52:56'),
(116, 7, 'Billing Notification', 'email', 'Your {{month}} invoice for {{property}} {{house}} - KES {{amount}}', 'Dear {{recipient_name}},\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        {{property}}\r\n    Unit:            {{house}}\r\n    Billing period:  {{month}}\r\n    Amount due:      KES {{amount}}\r\n    Balance:         KES {{balance}}\r\n    Invoice date:    {{date}}\r\n\r\n{{payment_instructions}}\r\n\r\nYou can view and download a copy of your invoice here:\r\n{{invoice_url}}\r\n\r\nIf you have any questions about this invoice, please contact the property office ({{owner_name}}).\r\n\r\nThank you,\r\nRentaFlow Team', '2026-08-08 11:54:30'),
(117, 14, 'Billing Notification', 'email', 'Your {{month}} invoice for {{property}} {{house}} - KES {{amount}}', 'Dear {{recipient_name}},\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        {{property}}\r\n    Unit:            {{house}}\r\n    Billing period:  {{month}}\r\n    Amount due:      KES {{amount}}\r\n    Balance:         KES {{balance}}\r\n    Invoice date:    {{date}}\r\n\r\n{{payment_instructions}}\r\n\r\nYou can view and download a copy of your invoice here:\r\n{{invoice_url}}\r\n\r\nIf you have any questions about this invoice, please contact the property office ({{owner_name}}).\r\n\r\nThank you,\r\nRentaFlow Team', '2026-08-08 11:54:30'),
(118, 13, 'Billing Notification', 'email', 'Your {{month}} invoice for {{property}} {{house}} - KES {{amount}}', 'Dear {{recipient_name}},\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        {{property}}\r\n    Unit:            {{house}}\r\n    Billing period:  {{month}}\r\n    Amount due:      KES {{amount}}\r\n    Balance:         KES {{balance}}\r\n    Invoice date:    {{date}}\r\n\r\n{{payment_instructions}}\r\n\r\nYou can view and download a copy of your invoice here:\r\n{{invoice_url}}\r\n\r\nIf you have any questions about this invoice, please contact the property office ({{owner_name}}).\r\n\r\nThank you,\r\nRentaFlow Team', '2026-08-08 11:54:30'),
(119, 12, 'Billing Notification', 'email', 'Your {{month}} invoice for {{property}} {{house}} - KES {{amount}}', 'Dear {{recipient_name}},\r\n\r\n{{recipient_note}}\r\n\r\nHere are the details of your invoice:\r\n\r\n    Property:        {{property}}\r\n    Unit:            {{house}}\r\n    Billing period:  {{month}}\r\n    Amount due:      KES {{amount}}\r\n    Balance:         KES {{balance}}\r\n    Invoice date:    {{date}}\r\n\r\n{{payment_instructions}}\r\n\r\nYou can view and download a copy of your invoice here:\r\n{{invoice_url}}\r\n\r\nIf you have any questions about this invoice, please contact the property office ({{owner_name}}).\r\n\r\nThank you,\r\nRentaFlow Team', '2026-08-08 11:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `tenancy_terminations`
--

CREATE TABLE `tenancy_terminations` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `tenant_id` int UNSIGNED NOT NULL,
  `property_id` int UNSIGNED DEFAULT NULL,
  `house_id` int UNSIGNED DEFAULT NULL,
  `initiated_by` enum('tenant','owner','caretaker') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'owner',
  `initiated_by_user_id` int UNSIGNED DEFAULT NULL COMMENT 'The user who initiated',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `effective_date` date NOT NULL,
  `status` enum('pending','approved','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `property_id` int UNSIGNED DEFAULT NULL,
  `house_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Uploaded profile picture URL',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_of_kin_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_of_kin_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_of_kin_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'National ID',
  `lease_start` date DEFAULT NULL,
  `lease_end` date DEFAULT NULL,
  `deposit` decimal(12,2) DEFAULT '0.00',
  `balance` decimal(12,2) DEFAULT '0.00',
  `credit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `water_balance` decimal(12,2) DEFAULT '0.00',
  `elec_balance` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','pending_termination','terminated') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `documents` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of uploaded documents',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_protection_consent_at` datetime DEFAULT NULL COMMENT 'Timestamp when tenant consented to data collection'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `owner_id`, `property_id`, `house_id`, `name`, `email`, `profile_picture`, `password`, `phone`, `id_number`, `next_of_kin_name`, `next_of_kin_phone`, `next_of_kin_email`, `id_type`, `lease_start`, `lease_end`, `deposit`, `balance`, `credit`, `water_balance`, `elec_balance`, `status`, `documents`, `created_at`, `updated_at`, `data_protection_consent_at`) VALUES
(43, 7, 34, 53, 'Mark Njoroge Waweru', 'sevelle376@gmail.com', NULL, '$2y$10$HuJNibfIJ7ZIb9nkooF4Nuu05vjZ5jgmdmCVq8t8oR.Z01fgX4Zfq', '0142048412', '222877412', 'Catherine Kamau', '0723894848', NULL, 'National ID', '2026-08-01', NULL, 6000.00, 6500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-01 10:39:35', '2026-09-01 12:24:18', NULL),
(45, 7, 34, 55, 'Billy Ndume Muruu', 'billyndume2@gmail.com', NULL, '$2y$10$iu3l4xFOQis8b4QzCUDY5ueTHm/hIH5G1/FpmRWbZRnt4SPY0oXzG', '0792891026', '432800781', 'Shadrack Ndume', '0721205458', 'shadrackmuruu10@gmail.com', 'National ID', '2026-08-01', NULL, 6000.00, 6500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-01 10:59:13', '2026-09-01 12:24:18', NULL),
(46, 7, 34, 57, 'Zahra Zainabu', 'zaharazainabu09@gmail.com', NULL, '$2y$10$QCaDTE07/D1gMgOmhL1eve/6jVurVWTiOp1Mnftv5BXL2YsP8UL62', '0117556216', '21589238', 'Zainab azadin', '0117556216', 'zaharazainabu09@gmail.com', 'National ID', '2026-08-01', NULL, 6000.00, 7500.00, 6000.00, 0.00, 0.00, 'active', NULL, '2026-08-01 11:17:26', '2026-09-01 12:24:18', NULL),
(47, 7, 34, 75, 'Ruth jepchumba', 'ruthjepchumba731@gmail.com', NULL, '$2y$10$F/NeDFvZ2UOI2.44tUoS4uHkbZavriEIOWn6uGgbb2hCcSJoKXNcG', '0713841545', '635507271', 'Pauline Jepkorir Tuitoek', '0720879466', 'jepkorirp@gmail.com', 'National ID', '2026-08-01', NULL, 6500.00, 6500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-01 12:34:14', '2026-09-01 12:24:18', '2026-08-03 15:59:18'),
(48, 7, 34, 77, 'felista jepchirchir rotich', 'felistajepchirchir@gmail.com', NULL, '$2y$10$Tj51bpBjBdWtRYxw6E2Dv./jGYSRafVRbcM4Zj56JxdMp3fndzxT6', '0720986624', '666568616', 'Gilbert Changwony', '0726695945', 'gilbertchangwony@gmail.com', 'National ID', '2026-08-01', NULL, 6500.00, 6500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-01 14:47:22', '2026-09-01 12:24:18', NULL),
(49, 12, 35, 82, 'G1 owner', 'kwatlita@gmail.com', NULL, '$2y$10$wCJGhpeEX2t..V2BLJGpvumW.EvS1IpuwxmBPZPP/qAkbdtWOgXXy', '0757846560', '7283839', 'G1 onwers brother', '0787846464', 'kwatlita@gmail.com', 'National ID', '2026-08-13', '2026-08-19', 20000.00, 0.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-01 18:29:58', '2026-08-01 18:29:58', NULL),
(50, 13, 36, 83, 'Duncan Karenju Gathogo', 'duncankarenju750@gmail.com', 'uploads/doc_2a9f7ce126b24da3ae938ee376e32c69.jpeg', '$2y$10$e6RECRGn5LxIGryYCn09FeP9/Y4w6GNwud0EUP.QboxlvhZzVkcoC', '0112554479', '40135584', 'jane mwangi', '0746984430', 'karenjuduncan750@gmail.com', 'National ID', NULL, NULL, 7500.00, 7500.00, 22500.00, 0.00, 0.00, 'active', NULL, '2026-08-02 13:18:55', '2026-09-01 12:24:18', '2026-08-31 09:09:12'),
(51, 7, 34, 64, 'Bichii Nicole Cheruto', 'bichiinicole@gmail.com', NULL, '$2y$10$/9SSpyEqyokw9Sf7UryFZefHPhOn2I2oe4Q88c1NCOfz1vUEuu8BW', '0745158449', '644571018', 'Kevin Kipkoech', '0721735964', 'kkipkoech002@gmail.com', 'National ID', '2026-08-02', NULL, 7000.00, 7000.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-02 17:05:10', '2026-09-01 12:24:18', '2026-08-02 17:50:34'),
(52, 7, 34, 66, 'Austin Kariuki', 'wanjikuaustin84@gmail.com', NULL, '$2y$10$4wnf0hlhds/tU/0J1F03P.UVJvjsr2tchoMK8l2GoI.YT.6ndG4WS', '0743306764', '320091424', 'Valentine Wanjiru Njeri', '0794010790', 'valentinenj@ueab.ac.ke', 'National ID', '2026-08-02', NULL, 7000.00, 7000.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-02 17:13:07', '2026-09-01 12:24:18', '2026-08-03 12:42:44'),
(53, 13, 36, 84, 'Andrew Kibet', 'mainapetermwangi2017@gmail.com', NULL, '$2y$10$ciiyBAbE5ehnQXP10Qr0l.Ipu8k8RaXLFPBZ3Q6HY/KZOtGWe4Oei', '0707454717', '40135584', 'James Mwangi', '0746984430', 'rentalflow.realestate@gmail.com', 'National ID', NULL, NULL, 7500.00, 7500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-03 04:43:55', '2026-09-01 12:24:18', NULL),
(54, 7, 34, 52, 'Kiptoo Tracy Cheruto', 'cherutotracy12@gmail.com', NULL, '$2y$10$skc1Qb8w3gsmdc7lL.URx.EhVSVRywDhKUTgzds5NAz5f1E5h0dqa', '0791947331', '182137381', 'Dr Kiptoo Wincer Kirui', '0735400865', 'winsix97@gmail.com', 'National ID', '2026-08-01', NULL, 6000.00, 18500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-03 08:59:28', '2026-09-01 12:24:18', NULL),
(55, 7, 34, 59, 'Sheila Gathoni Mureithi', 'mureithisheila968@gmail.com', NULL, '$2y$10$IFYfXppz6RQObKjGFH8A8OmIxptUeCFrLw/8fMSVo1QOixkMfx0.e', '0140799778', '313245448', 'John Mureithi Macharia', '0725500531', 'jmureithi69@gmail.com', 'National ID', '2026-05-01', NULL, 6000.00, 7500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-03 10:02:26', '2026-09-01 12:24:18', '2026-08-03 10:07:58'),
(56, 7, 34, 58, 'Adryan Kipkirui Langat', 'bestadryan01@gmail.com', NULL, '$2y$10$0AS50.Kv7ViOlcm1dROTeOPOf4wBb5PLHFKS6bvysURiWSxNK2DgW', '0768459350', '447817124', 'Langat Chepkemoi Wilfrida', '0716909700', 'memowinnie65@gmail.com', 'National ID', '2026-08-01', NULL, 6000.00, 7500.00, 0.00, 0.00, 0.00, 'active', '[{\"name\":\"Scanned ID (1).pdf\",\"url\":\"uploads/doc_1d24fb12b04d264c3625d2223d34d82b.pdf\",\"size\":84498,\"type\":\"application/pdf\",\"hash\":\"50fafa70f50679ce5b6398aababd23d897b96e45a4fd8b9d7cfb5e3d54d02b90\"}]', '2026-08-03 10:15:08', '2026-09-01 12:24:18', '2026-08-05 18:48:19'),
(57, 7, 34, 61, 'Sylvia Mbithe Musyoka', 'mbithesylvia404@gmail.com', NULL, '$2y$10$Wdn2.1FtNM86sRes.V2qoOV5RhSHQJcjBY2k8ll1Xv2d7zfBkS8fG', '0757481880', '436402374', 'Elizabeth Nduku', '0757481880', 'wambuaelizabeth413@gmail.com', 'National ID', '2026-08-03', NULL, 0.00, 19500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-03 17:01:41', '2026-09-01 12:24:18', '2026-08-04 07:42:24'),
(58, 7, 34, 60, 'Lenny John Mwangi Njoroge', 'lennynjoroge325@gmail.com', NULL, '$2y$10$PAdBiZT3CyfRX8pQOgTTQu03ndg/mQAGKOjnsi5eGahPvjWL28Tfy', '0708313490', '477973024', 'Naftaly Njoroge', '0726315445', 'milesnjo@gmail.com', 'National ID', '2026-08-03', NULL, 7000.00, 7500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-03 17:10:06', '2026-09-01 12:24:18', '2026-08-03 17:12:45'),
(59, 7, 34, 79, 'Juliah Wangui Kahora', 'juliahkahora@gmail.com', NULL, '$2y$10$cIbfYCWu4lAs/3UwwSb1weIIITuaEcix3y5/IlI09oAVpO7jGGOCy', '0746511124', '42366652', 'Simon Kahora', '0722154722', NULL, 'National ID', '2026-08-03', NULL, 6000.00, 7500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-03 17:45:48', '2026-09-01 12:24:18', '2026-08-03 18:45:06'),
(60, 13, 36, 85, 'Njagi Nickson', 'bsclmr317222@spu.ac.ke', 'uploads/doc_3ff2a46bfae24074718eef76daf6646a.jpeg', '$2y$10$NHtr91nyr4fD1/O4LrMltuVlrpxxYOFVxcuOHBi.uTTmFPI1uK/G.', '0721956331', '3987653', 'jane mwangi', '0746984430', 'karenjuduncan750@gmail.com', 'National ID', NULL, NULL, 5300.00, 0.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-06 16:00:19', '2026-08-06 16:00:19', NULL),
(61, 7, 34, 70, 'Michel Kavemba Kilo', 'michelkavesh01@gmail.com', NULL, '$2y$10$OW9HYqmV55IpIz4TKC576ekYVNGsjbW92oEoyr3LlP5EgzV2DCHBS', '0724699717', '380798049', 'Geoffrey Kilo Kioko', '0720361655', 'kiokogeoffrey233@gmail.com', 'National ID', '2026-08-08', NULL, 7000.00, 7000.00, 7000.00, 0.00, 0.00, 'active', NULL, '2026-08-09 06:37:54', '2026-09-01 12:24:18', '2026-08-09 18:55:47'),
(62, 7, 34, 72, 'John Stephen', 'stphn0445@gmail.com', NULL, '$2y$10$K5zLpHaGG2FZRWItcEAS.uCm5iwTiTKL7Eqe8ycpRdLu3/wE7Cfvi', '0723927924', '570464425', 'Stephen Ndambuki', '0723927827', 'stiffin50@gmail.com', 'National ID', '2026-08-19', NULL, 0.00, 7000.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-20 02:43:12', '2026-09-01 12:24:18', '2026-08-22 16:24:48'),
(63, 7, 34, 81, 'Precious Ntinyari Mati', 'pierrahprecious@gmail.com', NULL, '$2y$10$fosjnsnwzVZ8jOqygCB4k.mOqeN9W3zla5ldr55e72gVaBJe5WDGy', '0710587110', '13478497', 'Evangeline Kinya', '0790149708', 'liliankiende20@gmail.com', 'National ID', '2026-08-20', NULL, 7000.00, 21000.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-20 18:06:50', '2026-09-01 12:24:18', '2026-08-21 13:28:53'),
(64, 7, 34, 67, 'Dylan Israel', 'israeldylan001@gmail.com', NULL, '$2y$10$jiN7ePBbEsPyAX5itBUDpO5FnVAeBgBPgMDXZJUmNRmWJTrcTWiQK', '0702912869', '517659679', 'Stanley Tonui', '0722225730', 'stanleytonui1@gmail.com', 'National ID', '2026-08-22', NULL, 0.00, 0.00, 14000.00, 0.00, 0.00, 'active', NULL, '2026-08-22 07:31:06', '2026-09-01 09:39:01', '2026-08-22 08:20:17'),
(65, 7, 34, 76, 'Biam Mokua Onsomu', 'biammokua@gmail.com', NULL, '$2y$10$7.9v4fQPp8jCqGVF.eheeeowfJtL.xwPSi3lx2nkKse8/NdeojGca', '0792710958', '743663612', 'Denis Otiso Omollo', '0704638252', 'denisotisoomollo@gmail.com', 'National ID', '2026-08-22', NULL, 7000.00, 17000.00, 0.00, 0.00, 0.00, 'active', '[{\"name\":\"17874120659552492458635775758004.jpg\",\"url\":\"uploads/doc_dabf63701c99acf678b3cf608243ec93.jpg\",\"size\":3364377,\"type\":\"image/jpeg\",\"hash\":\"e803d9b0302036735ac5723abe0f989aa2d57ce9055833ef60e1037f490c5b64\"},{\"name\":\"17874120888385051487836643649912.jpg\",\"url\":\"uploads/doc_4f088e63b71fa6edd17b572826e03122.jpg\",\"size\":4722175,\"type\":\"image/jpeg\",\"hash\":\"f5fae1b01935b3413834febdef42e901e28d4db133c6aae724f9b000e35be338\"}]', '2026-08-22 15:23:35', '2026-09-01 12:24:18', '2026-08-22 15:50:00'),
(66, 7, 34, 54, 'Adrian Mbai', 'adrianmbai01@gmail.com', NULL, '$2y$10$5TEkw.jbCjXpEM5ASBRbLu/WML9BP8CVKbTT4H7FSuY8wfhRBVA.C', '0724781358', '384696495', 'Cosmus Mbai Mutuku', '0729066730', 'cosmbai@gmail.com', 'National ID', '2026-07-01', NULL, 0.00, 30500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-28 12:18:44', '2026-09-01 12:24:18', NULL),
(67, 7, 34, 73, 'Albert Kariuki Njau', 'albertkariuki860@gmail.com', NULL, '$2y$10$BLMOb1BTKJn8A0CI14KXQOAS75nFJyQXD.EN/hn3qmMr19oSnWGbu', '0795461371', '977784579', 'Josphat Njau', '0712447811', NULL, 'National ID', '2026-07-01', NULL, 6500.00, 19500.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-28 12:42:05', '2026-09-01 12:24:18', NULL),
(68, 7, 34, 63, 'Ismael Osman Mohamed', 'ismaelosmanmohamed4@gmail.com', NULL, '$2y$10$.SEcXMqfXie10bjaQwk/Tua5GjvrC/uwyEinew1gqfxFxAaUWrSmK', '0119805898', '158718712', 'Dekow Issak', '0722830811', NULL, 'National ID', NULL, NULL, 0.00, 21000.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-28 13:00:49', '2026-09-01 12:24:18', NULL),
(69, 7, 34, 68, 'Precious Nkatha Murithi', 'preciousunicious7@gmail.com', NULL, '$2y$10$HATrYuQAY5Fg9WpjNAKRFOR.uHD0BCVL3Z78OmUKTgxT/Pu5wXO4i', '0703968978', '301444339', 'Eric Murithi', '0720244438', 'emurithi@gmail.com', 'National ID', '2026-08-01', NULL, 7000.00, 21000.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-08-30 19:07:43', '2026-09-01 12:24:18', NULL),
(70, 7, 34, 62, 'Grace Jepkemoi Kimesis', 'gkimesis@gmail.com', NULL, '$2y$10$MKhvGrcvrcTCDA25R1pXu.N/xSdNrJzZo2YVU9CE6lwTwAn/NtwMS', '0795064719', '520228195', 'Paul Kimesis Serem', '0721317842', 'paulmesis57@gmail.com', 'National ID', '2026-09-01', NULL, 7000.00, 14000.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-09-01 06:01:29', '2026-09-01 06:14:43', '2026-09-01 06:14:43'),
(71, 7, 34, 80, 'Juliet Wamucii Nyaga', 'julietwamucii@gmail.com', NULL, '$2y$10$JArMo8hCn3hZf0DhIegnEOb9XST/mdJXJsSl386qK.H4E4V2QdcQS', '0112917591', '0000', 'Simon Nyaga Kagure', '0721739037', 'simonkagure9@gmail.com', 'National ID', '2026-09-01', NULL, 0.00, 7000.00, 0.00, 0.00, 0.00, 'active', NULL, '2026-09-01 09:56:32', '2026-09-01 09:56:32', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_bills_house_month` (`house_id`,`month`),
  ADD UNIQUE KEY `uq_bills_house_month` (`house_id`,`month`),
  ADD UNIQUE KEY `uq_bills_owner_tenant_month` (`owner_id`,`tenant_id`,`month`),
  ADD KEY `idx_bills_owner` (`owner_id`),
  ADD KEY `idx_bills_house` (`house_id`),
  ADD KEY `idx_bills_month` (`month`),
  ADD KEY `idx_bills_owner_status_due` (`owner_id`,`status`,`due_date`),
  ADD KEY `idx_bills_tenant` (`tenant_id`),
  ADD KEY `idx_bills_owner_house_month` (`owner_id`,`house_id`,`month`);

--
-- Indexes for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bill_items_bill` (`bill_id`),
  ADD KEY `idx_bill_items_type` (`type`);

--
-- Indexes for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_address` (`ip_address`),
  ADD KEY `idx_blocked_until` (`blocked_until`);

--
-- Indexes for table `bot_detections`
--
ALTER TABLE `bot_detections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_last_seen` (`last_seen`);

--
-- Indexes for table `caretakers`
--
ALTER TABLE `caretakers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_caretakers_owner` (`owner_id`),
  ADD KEY `idx_caretakers_email` (`email`),
  ADD KEY `idx_caretakers_owner_email` (`owner_id`,`email`);

--
-- Indexes for table `communications`
--
ALTER TABLE `communications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_communications_owner` (`owner_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaints_owner` (`owner_id`),
  ADD KEY `idx_complaints_tenant` (`tenant_id`),
  ADD KEY `idx_complaints_status` (`status`),
  ADD KEY `idx_complaints_owner_status_created` (`owner_id`,`status`,`created_at`),
  ADD KEY `idx_complaints_property` (`property_id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_owner_id` (`owner_id`),
  ADD KEY `idx_to_email` (`to_email`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_owner` (`owner_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled` (`scheduled_at`),
  ADD KEY `idx_priority` (`priority`,`created_at`);

--
-- Indexes for table `email_queue_settings`
--
ALTER TABLE `email_queue_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_owner_id` (`owner_id`),
  ADD KEY `idx_name` (`name`);

--
-- Indexes for table `houses`
--
ALTER TABLE `houses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_houses_owner_property_unit` (`owner_id`,`property_id`,`unit`),
  ADD KEY `idx_houses_owner` (`owner_id`),
  ADD KEY `idx_houses_property` (`property_id`),
  ADD KEY `idx_houses_status` (`status`),
  ADD KEY `idx_houses_owner_property_status` (`owner_id`,`property_id`,`status`),
  ADD KEY `idx_houses_owner_property` (`owner_id`,`property_id`),
  ADD KEY `idx_houses_property_unit` (`property_id`,`unit`);

--
-- Indexes for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_maintenance_owner` (`owner_id`),
  ADD KEY `idx_maintenance_property` (`property_id`),
  ADD KEY `idx_maintenance_house` (`house_id`),
  ADD KEY `idx_maintenance_tenant` (`tenant_id`),
  ADD KEY `idx_maintenance_status` (`status`),
  ADD KEY `idx_maintenance_priority` (`priority`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_owners_email` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_used` (`used`);

--
-- Indexes for table `password_setup_tokens`
--
ALTER TABLE `password_setup_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user` (`user_type`,`user_id`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_used` (`used_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payments_owner` (`owner_id`),
  ADD KEY `idx_payments_tenant` (`tenant_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_owner_created` (`owner_id`,`created_at`),
  ADD KEY `idx_payments_owner_tenant_created` (`owner_id`,`tenant_id`,`created_at`),
  ADD KEY `idx_payments_month` (`month`),
  ADD KEY `idx_payments_house_owner_month` (`house_id`,`owner_id`,`month`);

--
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_allocations_payment` (`payment_id`),
  ADD KEY `idx_payment_allocations_bill_item` (`bill_item_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_properties_owner` (`owner_id`);

--
-- Indexes for table `property_documents`
--
ALTER TABLE `property_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_owner_id` (`owner_id`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rate_limit` (`ip_address`,`type`),
  ADD KEY `idx_ip_type` (`ip_address`,`type`),
  ADD KEY `idx_window` (`window_start`);

--
-- Indexes for table `rent_reminders`
--
ALTER TABLE `rent_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_owner_id` (`owner_id`),
  ADD KEY `idx_tenant_id` (`tenant_id`),
  ADD KEY `idx_month` (`month`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `security_audit_log`
--
ALTER TABLE `security_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_templates_owner` (`owner_id`);

--
-- Indexes for table `tenancy_terminations`
--
ALTER TABLE `tenancy_terminations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tt_owner` (`owner_id`),
  ADD KEY `idx_tt_tenant` (`tenant_id`),
  ADD KEY `idx_tt_status` (`status`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenants_owner` (`owner_id`),
  ADD KEY `idx_tenants_property` (`property_id`),
  ADD KEY `idx_tenants_house` (`house_id`),
  ADD KEY `idx_tenants_owner_name` (`owner_id`,`name`),
  ADD KEY `idx_tenants_owner_house` (`owner_id`,`house_id`),
  ADD KEY `idx_tenants_owner_lease_end` (`owner_id`,`lease_end`),
  ADD KEY `idx_tenants_email` (`email`),
  ADD KEY `idx_tenants_status` (`status`),
  ADD KEY `idx_tenants_house_owner` (`house_id`,`owner_id`),
  ADD KEY `idx_tenants_data_protection_consent` (`data_protection_consent_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `bill_items`
--
ALTER TABLE `bill_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bot_detections`
--
ALTER TABLE `bot_detections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11636;

--
-- AUTO_INCREMENT for table `caretakers`
--
ALTER TABLE `caretakers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=640;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `email_queue_settings`
--
ALTER TABLE `email_queue_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `houses`
--
ALTER TABLE `houses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `password_setup_tokens`
--
ALTER TABLE `password_setup_tokens`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `property_documents`
--
ALTER TABLE `property_documents`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

--
-- AUTO_INCREMENT for table `rent_reminders`
--
ALTER TABLE `rent_reminders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_audit_log`
--
ALTER TABLE `security_audit_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `tenancy_terminations`
--
ALTER TABLE `tenancy_terminations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `fk_bills_house` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bills_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD CONSTRAINT `fk_bill_items_bill` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `caretakers`
--
ALTER TABLE `caretakers`
  ADD CONSTRAINT `fk_caretakers_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `communications`
--
ALTER TABLE `communications`
  ADD CONSTRAINT `fk_communications_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `fk_complaints_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_complaints_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `houses`
--
ALTER TABLE `houses`
  ADD CONSTRAINT `fk_houses_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_houses_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD CONSTRAINT `fk_payment_allocations_bill_item` FOREIGN KEY (`bill_item_id`) REFERENCES `bill_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_allocations_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `fk_properties_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_documents`
--
ALTER TABLE `property_documents`
  ADD CONSTRAINT `property_documents_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_documents_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `templates`
--
ALTER TABLE `templates`
  ADD CONSTRAINT `fk_templates_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenancy_terminations`
--
ALTER TABLE `tenancy_terminations`
  ADD CONSTRAINT `fk_tt_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tt_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `fk_tenants_house` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tenants_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tenants_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
