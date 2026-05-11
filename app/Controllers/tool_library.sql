-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 11:55 PM
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
-- Database: `tool_library`
--

-- --------------------------------------------------------

--
-- Table structure for table `available_date`
--

CREATE TABLE `available_date` (
  `id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `available_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `battery_logs`
--

CREATE TABLE `battery_logs` (
  `id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `charge_cycles` int(11) DEFAULT 0,
  `health_status` varchar(50) DEFAULT NULL,
  `last_checked` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `battery_logs`
--

INSERT INTO `battery_logs` (`id`, `tool_id`, `charge_cycles`, `health_status`, `last_checked`) VALUES
(1, 1, 2, 'Good', '2026-05-28'),
(2, 1, 1, 'Good', '2026-05-28'),
(3, 1, 4, 'Good', '2026-05-28');

-- --------------------------------------------------------

--
-- Table structure for table `campaign`
--

CREATE TABLE `campaign` (
  `campaign_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `discount_code` varchar(50) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `expiry_date` date NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `name`) VALUES
(4, 'Electricity'),
(1, 'General Tools');

-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE `certifications` (
  `id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certifications`
--

INSERT INTO `certifications` (`id`, `tool_id`, `type`, `issue_date`, `expiry_date`) VALUES
(1, 1, 'mmm', '2026-05-01', '2026-05-25'),
(2, 4, 'mmm', '2026-05-05', '2026-05-09');

-- --------------------------------------------------------

--
-- Table structure for table `consumable`
--

CREATE TABLE `consumable` (
  `consumable_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `damage_declarations`
--

CREATE TABLE `damage_declarations` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `reservation_id` varchar(100) NOT NULL,
  `tool_name` varchar(255) NOT NULL,
  `damage_date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `damage_type` varchar(100) NOT NULL,
  `severity` enum('low','medium','high') NOT NULL,
  `description` text DEFAULT NULL,
  `witness` varchar(255) DEFAULT NULL,
  `document_path` varchar(500) DEFAULT NULL,
  `photos` text DEFAULT NULL,
  `reference_no` varchar(50) NOT NULL,
  `status` enum('pending','reviewing','resolved') DEFAULT 'pending',
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `damage_declarations`
--

INSERT INTO `damage_declarations` (`id`, `reporter_id`, `reservation_id`, `tool_name`, `damage_date`, `location`, `damage_type`, `severity`, `description`, `witness`, `document_path`, `photos`, `reference_no`, `status`, `submitted_at`) VALUES
(1, 11, '5', 'Bosch Professional Hammer Drill', '2026-06-05', 'cc', 'cosmetic', 'high', 'nnnnnn', '', '', '[\"uploads\\/damage_photos\\/1778275572_0_Screenshot 2025-07-30 022646.png\",\"uploads\\/damage_photos\\/1778275572_1_Screenshot 2025-07-31 024457.png\"]', 'DMG-2026-2670', 'pending', '2026-05-09 00:26:12'),
(2, 11, '5', 'Bosch Professional Hammer Drill', '2026-05-28', 'cc', 'electrical', 'medium', 'cc', '', '', '[\"uploads\\/damage_photos\\/1778275923_0_Screenshot 2025-07-02 131508.png\",\"uploads\\/damage_photos\\/1778275923_1_Screenshot 2025-07-05 185949.png\",\"uploads\\/damage_photos\\/1778275923_2_Screenshot 2025-07-05 190358.png\"]', 'DMG-2026-3641', 'pending', '2026-05-09 00:32:03'),
(3, 11, '5', 'Bosch Professional Hammer Drill', '2026-05-28', 'cc', 'electrical', 'medium', 'cc', '', '', '[\"uploads\\/damage_photos\\/1778276042_0_Screenshot 2025-07-02 131508.png\",\"uploads\\/damage_photos\\/1778276042_1_Screenshot 2025-07-05 185949.png\",\"uploads\\/damage_photos\\/1778276042_2_Screenshot 2025-07-05 190358.png\"]', 'DMG-2026-2585', 'pending', '2026-05-09 00:34:02'),
(4, 11, '5', 'Bosch Professional Hammer Drill', '2026-05-28', 'cc', 'electrical', 'medium', 'cc', '', '', '[\"uploads\\/damage_photos\\/1778276049_0_Screenshot 2025-07-02 131508.png\",\"uploads\\/damage_photos\\/1778276049_1_Screenshot 2025-07-05 185949.png\",\"uploads\\/damage_photos\\/1778276049_2_Screenshot 2025-07-05 190358.png\"]', 'DMG-2026-4189', 'pending', '2026-05-09 00:34:09'),
(5, 11, '5', 'Bosch Professional Hammer Drill', '2026-04-30', 'cc', 'mechanical', 'medium', 'ؤؤ', '', '', '[\"uploads\\/damage_photos\\/1778276099_0_Screenshot 2025-02-22 233209.png\",\"uploads\\/damage_photos\\/1778276099_1_Screenshot 2025-09-28 010840.png\",\"uploads\\/damage_photos\\/1778276099_2_Screenshot 2025-09-28 230326.png\"]', 'DMG-2026-4343', 'pending', '2026-05-09 00:34:59'),
(6, 11, '5', 'Bosch Professional Hammer Drill', '2026-04-30', 'cc', 'mechanical', 'medium', 'ؤؤ', '', '', '[\"uploads\\/damage_photos\\/1778276153_0_Screenshot 2025-02-22 233209.png\",\"uploads\\/damage_photos\\/1778276153_1_Screenshot 2025-09-28 010840.png\",\"uploads\\/damage_photos\\/1778276153_2_Screenshot 2025-09-28 230326.png\"]', 'DMG-2026-9669', 'pending', '2026-05-09 00:35:53'),
(7, 11, '5', 'Bosch Professional Hammer Drill', '2026-04-30', 'cc', 'mechanical', 'medium', 'ؤؤ', '', '', '[\"uploads\\/damage_photos\\/1778276295_0_Screenshot 2025-02-22 233209.png\",\"uploads\\/damage_photos\\/1778276295_1_Screenshot 2025-09-28 010840.png\",\"uploads\\/damage_photos\\/1778276295_2_Screenshot 2025-09-28 230326.png\"]', 'DMG-2026-1082', 'pending', '2026-05-09 00:38:15'),
(8, 11, '5', 'Bosch Professional Hammer Drill', '2026-04-30', 'cc', 'mechanical', 'medium', 'ؤؤ', '', '', '[\"uploads\\/damage_photos\\/1778276440_0_Screenshot 2025-02-22 233209.png\",\"uploads\\/damage_photos\\/1778276440_1_Screenshot 2025-09-28 010840.png\",\"uploads\\/damage_photos\\/1778276440_2_Screenshot 2025-09-28 230326.png\"]', 'DMG-2026-1591', 'pending', '2026-05-09 00:40:40');

-- --------------------------------------------------------

--
-- Table structure for table `deposit`
--

CREATE TABLE `deposit` (
  `deposit_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('held','returned','forfeited') NOT NULL DEFAULT 'held'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dispute`
--

CREATE TABLE `dispute` (
  `dispute_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `handled_by` int(11) NOT NULL,
  `status` enum('open','resolved','rejected') NOT NULL DEFAULT 'open',
  `resolution` text DEFAULT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `reported_user_id` int(11) DEFAULT NULL,
  `tool_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `evidence_path` varchar(500) DEFAULT NULL,
  `decision` varchar(20) DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `insurance_claim`
--

CREATE TABLE `insurance_claim` (
  `claim_id` int(11) NOT NULL,
  `dispute_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `status` enum('submitted','approved','rejected') NOT NULL DEFAULT 'submitted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_logs`
--

CREATE TABLE `maintenance_logs` (
  `id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `action` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `encrypted` tinyint(1) NOT NULL DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `content`, `encrypted`, `is_read`) VALUES
(1, 12, 10, 'ووو', 0, 0),
(2, 11, 12, 'zzzzz', 0, 1),
(3, 11, 12, 'zzzz', 0, 1),
(4, 12, 11, 'zz', 0, 1),
(5, 12, 14, 'mm', 0, 0),
(6, 12, 6, 'mm', 0, 0),
(7, 12, 9, 'mmm', 0, 0),
(8, 12, 10, 'mm', 0, 0),
(9, 12, 11, 'mm', 0, 1),
(10, 12, 13, 'mmmm', 0, 1),
(11, 12, 7, 'hello', 0, 0),
(12, 11, 12, 'ok', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_price`, `status`, `order_date`) VALUES
(1, 1, 150.00, 'completed', '2026-05-03 18:15:34'),
(2, 2, 200.00, 'completed', '2026-05-03 18:15:34'),
(3, 3, 450.00, 'completed', '2026-05-03 18:15:34');

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

CREATE TABLE `parts` (
  `part_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `stock_qty` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('rental','deposit','late_fee','refund') NOT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `renter_id` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `final_price` decimal(10,2) NOT NULL,
  `discount_applied` decimal(10,2) NOT NULL DEFAULT 0.00,
  `actual_return_date` date DEFAULT NULL,
  `late_fee` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `reservation_id`, `tool_id`, `renter_id`, `duration`, `final_price`, `discount_applied`, `actual_return_date`, `late_fee`) VALUES
(1, 1, 6, 11, 15, 900.00, 0.00, NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `repair_requests`
--

CREATE TABLE `repair_requests` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `reservation_id` varchar(100) NOT NULL,
  `tool_name` varchar(255) NOT NULL,
  `damage_date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `damage_type` varchar(100) NOT NULL,
  `severity` enum('low','medium','high') NOT NULL,
  `description` text DEFAULT NULL,
  `witness` varchar(255) DEFAULT NULL,
  `document_path` varchar(500) DEFAULT NULL,
  `photos` text DEFAULT NULL,
  `reference_no` varchar(50) NOT NULL,
  `status` enum('pending','reviewing','in_progress','completed','resolved') DEFAULT 'pending',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `technician_id` int(11) DEFAULT NULL,
  `tech_note` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_requests`
--

INSERT INTO `repair_requests` (`id`, `reporter_id`, `reservation_id`, `tool_name`, `damage_date`, `location`, `damage_type`, `severity`, `description`, `witness`, `document_path`, `photos`, `reference_no`, `status`, `submitted_at`, `technician_id`, `tech_note`, `admin_note`, `updated_at`) VALUES
(12, 11, '2', '3D printer', '2026-05-29', 'kjkj', 'electrical', 'medium', 'zzz', '', '', '[\"uploads\\/damage_photos\\/1778266275_0_Screenshot 2025-01-15 154602.png\"]', 'DMG-2026-9136', 'completed', '2026-05-08 21:51:15', 13, 'fgfgf', '', '2026-05-09 00:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending'
) ;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `tool_id`, `start_date`, `end_date`, `status`) VALUES
(1, 11, 6, '2026-05-14', '2026-05-29', 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `tools`
--

CREATE TABLE `tools` (
  `tool_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `state` varchar(50) NOT NULL DEFAULT 'good',
  `availability` tinyint(1) NOT NULL DEFAULT 1,
  `warranty_expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `manual_path` varchar(255) DEFAULT NULL,
  `video_link` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tools`
--

INSERT INTO `tools` (`tool_id`, `owner_id`, `category_id`, `name`, `description`, `base_price`, `state`, `availability`, `warranty_expiry_date`, `created_at`, `manual_path`, `video_link`, `image_path`) VALUES
(1, 6, 4, 'mmmm', 'nnnn', 50.00, 'good', 1, '2026-05-05', '2026-05-03 10:59:28', NULL, NULL, NULL),
(4, 11, 4, 'Bosch Professional Hammer Drill', 'Heavy-duty electric hammer drill suitable for construction, woodwork, and industrial maintenance. Includes impact mode and safety clutch.', 75.00, 'Good', 0, '2026-05-13', '2026-05-05 22:53:42', 'uploads/manuals/1778021622_manual_Classdiagram.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', NULL),
(5, 11, 4, '3D printer', 'bbbbbbbb', 30.00, 'Good', 1, '2026-05-22', '2026-05-07 22:32:05', 'uploads/manuals/1778193125_manual_DataStructureQuiz.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', NULL),
(6, 14, 4, '3D printer', 'mmmmmmm', 60.00, 'Good', 1, '2026-05-27', '2026-05-08 12:42:04', 'uploads/manuals/1778244124_manual_DataStructureQuiz.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778244124_image_Screenshot2025-04-07214507.png');

-- --------------------------------------------------------

--
-- Table structure for table `tool_campaign`
--

CREATE TABLE `tool_campaign` (
  `tool_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tool_compatibility_checks`
--

CREATE TABLE `tool_compatibility_checks` (
  `id` int(11) NOT NULL,
  `tool_name` varchar(255) NOT NULL,
  `tool_model` varchar(255) DEFAULT NULL,
  `tool_accessory` varchar(255) DEFAULT NULL,
  `project_type` varchar(100) NOT NULL,
  `material` varchar(255) NOT NULL,
  `material_size` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `result_status` enum('compatible','warning','incomplete') NOT NULL,
  `result_summary` text DEFAULT NULL,
  `checked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tool_consumable`
--

CREATE TABLE `tool_consumable` (
  `tool_id` int(11) NOT NULL,
  `consumable_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tool_document`
--

CREATE TABLE `tool_document` (
  `document_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `manual` varchar(500) DEFAULT NULL,
  `safety_video` varchar(500) DEFAULT NULL,
  `warranty_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('technical','client','admin') DEFAULT 'client',
  `membership_tier` enum('basic','premium','vip') NOT NULL DEFAULT 'basic',
  `trust_score` decimal(3,2) DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `zone_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `membership_tier`, `trust_score`, `status`, `zone_id`) VALUES
(3, 'Mahmoud', 'mahmoud@gmail.com', '$2y$10$V1J9qk9v8b3cYh8m2x0vOe0nK1lGfXl8pQk8rZcQmZ8h0lWwE2m6e', 'admin', 'basic', 0.00, 'active', NULL),
(6, 'Hassan', 'hass@gmail.com', '$2y$10$vQ8cWYe90o94.rI241CbyuxaLkCNnnU.vvDobcifn4Ve4fMvkJbsa', 'client', 'basic', 0.00, 'active', NULL),
(7, 'aaa', 'aaa@gmail.com', '$2y$10$Noyvw9TKjyttG/94u56VH.L4MgGiTyZbOp1a.e.HXleSkL45MiBi6', 'technical', 'basic', 0.90, 'active', NULL),
(8, 'Mahmoud', 'mahmoud123abdallah456@gmail.com', '$2y$10$8n8yyv.iPhTB8fi6I.79v.FwyvFnkLLPg3/RjWI8SeP655xpwGcGW', 'admin', 'basic', 0.00, 'active', NULL),
(9, 'wiener', 'mmm@gmail.com', '$2y$10$u1sXMcST98C5RNZVZKBHd.V9TwYliFENFBcJacpxoB6pyIlSWlqXK', 'client', 'basic', 0.00, 'active', NULL),
(10, 'wiener', 'm@gmail.com', '$2y$10$lDsfVJvG2nLdMc9hHfYoB.ytP1cHKQNbM.Ek.oQmaEwz56XP8Icoe', 'client', 'basic', 0.00, 'active', NULL),
(11, 'wiener', 'client@gmail.com', '$2y$10$zKVgiaxhnlHYxxnB215eSOQ32AT2Y97TyulKdi/oXafNM90XImr.G', 'client', 'basic', 0.00, 'active', NULL),
(12, 'Mahmoud', 'admin@gmail.com', '$2y$10$yO9Dpm7gnYk8EqPFshoS0.m/1epuN5BSj0vZeevMaupAj3YoHrCCi', 'admin', 'basic', 0.00, 'active', NULL),
(13, 'techno', 'tech@gmail.com', '$2y$10$rnsoG.9DIzVR8uE6B1yOOuNZEqZvvHWhhkS8zCdsc3fSWp1V.EXUq', 'technical', 'basic', 0.00, 'active', NULL),
(14, 'ali', 'all@gmail.com', '$2y$10$oAHrc2toz8gks19QlYuBi.Nl8OVE7NDWJObzyKaHD1c3GrnWTFrXy', 'client', 'basic', 0.70, 'active', 20);

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `zone_id` int(11) NOT NULL,
  `zone_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`zone_id`, `zone_name`) VALUES
(1, 'Cairo'),
(2, 'Giza'),
(3, 'Alexandria'),
(4, 'Dakahlia'),
(5, 'Beheira'),
(6, 'Gharbia'),
(7, 'Sharqia'),
(8, 'Menofia'),
(9, 'Qalyubia'),
(10, 'Kafr El-Sheikh'),
(11, 'Damietta'),
(12, 'Port Said'),
(13, 'Ismailia'),
(14, 'Suez'),
(15, 'North Sinai'),
(16, 'South Sinai'),
(17, 'Fayoum'),
(18, 'Beni Suef'),
(19, 'Minya'),
(20, 'Asyut'),
(21, 'Sohag'),
(22, 'Qena'),
(23, 'Luxor'),
(24, 'Aswan'),
(25, 'Red Sea'),
(26, 'New Valley'),
(27, 'Matrouh');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `available_date`
--
ALTER TABLE `available_date`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tool_id` (`tool_id`);

--
-- Indexes for table `battery_logs`
--
ALTER TABLE `battery_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tool_id` (`tool_id`);

--
-- Indexes for table `campaign`
--
ALTER TABLE `campaign`
  ADD PRIMARY KEY (`campaign_id`),
  ADD UNIQUE KEY `discount_code` (`discount_code`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tool_id` (`tool_id`);

--
-- Indexes for table `consumable`
--
ALTER TABLE `consumable`
  ADD PRIMARY KEY (`consumable_id`);

--
-- Indexes for table `damage_declarations`
--
ALTER TABLE `damage_declarations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposit`
--
ALTER TABLE `deposit`
  ADD PRIMARY KEY (`deposit_id`),
  ADD UNIQUE KEY `rental_id` (`rental_id`);

--
-- Indexes for table `dispute`
--
ALTER TABLE `dispute`
  ADD PRIMARY KEY (`dispute_id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `handled_by` (`handled_by`);

--
-- Indexes for table `insurance_claim`
--
ALTER TABLE `insurance_claim`
  ADD PRIMARY KEY (`claim_id`),
  ADD UNIQUE KEY `dispute_id` (`dispute_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tool_id` (`tool_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`part_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD UNIQUE KEY `reservation_id` (`reservation_id`),
  ADD KEY `tool_id` (`tool_id`),
  ADD KEY `renter_id` (`renter_id`);

--
-- Indexes for table `repair_requests`
--
ALTER TABLE `repair_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tool_id` (`tool_id`);

--
-- Indexes for table `tools`
--
ALTER TABLE `tools`
  ADD PRIMARY KEY (`tool_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tool_campaign`
--
ALTER TABLE `tool_campaign`
  ADD PRIMARY KEY (`tool_id`,`campaign_id`),
  ADD KEY `campaign_id` (`campaign_id`);

--
-- Indexes for table `tool_compatibility_checks`
--
ALTER TABLE `tool_compatibility_checks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tool_consumable`
--
ALTER TABLE `tool_consumable`
  ADD PRIMARY KEY (`tool_id`,`consumable_id`),
  ADD KEY `consumable_id` (`consumable_id`);

--
-- Indexes for table `tool_document`
--
ALTER TABLE `tool_document`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `tool_id` (`tool_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_zones` (`zone_id`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`zone_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `available_date`
--
ALTER TABLE `available_date`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `battery_logs`
--
ALTER TABLE `battery_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `campaign`
--
ALTER TABLE `campaign`
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `consumable`
--
ALTER TABLE `consumable`
  MODIFY `consumable_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `damage_declarations`
--
ALTER TABLE `damage_declarations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `deposit`
--
ALTER TABLE `deposit`
  MODIFY `deposit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispute`
--
ALTER TABLE `dispute`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `insurance_claim`
--
ALTER TABLE `insurance_claim`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `part_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `repair_requests`
--
ALTER TABLE `repair_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tools`
--
ALTER TABLE `tools`
  MODIFY `tool_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tool_compatibility_checks`
--
ALTER TABLE `tool_compatibility_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tool_document`
--
ALTER TABLE `tool_document`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `available_date`
--
ALTER TABLE `available_date`
  ADD CONSTRAINT `available_date_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE;

--
-- Constraints for table `battery_logs`
--
ALTER TABLE `battery_logs`
  ADD CONSTRAINT `battery_logs_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE;

--
-- Constraints for table `certifications`
--
ALTER TABLE `certifications`
  ADD CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE;

--
-- Constraints for table `deposit`
--
ALTER TABLE `deposit`
  ADD CONSTRAINT `deposit_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `dispute`
--
ALTER TABLE `dispute`
  ADD CONSTRAINT `dispute_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispute_ibfk_2` FOREIGN KEY (`handled_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `insurance_claim`
--
ALTER TABLE `insurance_claim`
  ADD CONSTRAINT `insurance_claim_ibfk_1` FOREIGN KEY (`dispute_id`) REFERENCES `dispute` (`dispute_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `insurance_claim_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`payment_id`);

--
-- Constraints for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD CONSTRAINT `maintenance_logs_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`),
  ADD CONSTRAINT `rentals_ibfk_3` FOREIGN KEY (`renter_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE;

--
-- Constraints for table `tools`
--
ALTER TABLE `tools`
  ADD CONSTRAINT `tools_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tools_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `tool_campaign`
--
ALTER TABLE `tool_campaign`
  ADD CONSTRAINT `tool_campaign_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tool_campaign_ibfk_2` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE;

--
-- Constraints for table `tool_consumable`
--
ALTER TABLE `tool_consumable`
  ADD CONSTRAINT `tool_consumable_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tool_consumable_ibfk_2` FOREIGN KEY (`consumable_id`) REFERENCES `consumable` (`consumable_id`) ON DELETE CASCADE;

--
-- Constraints for table `tool_document`
--
ALTER TABLE `tool_document`
  ADD CONSTRAINT `tool_document_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_zones` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
