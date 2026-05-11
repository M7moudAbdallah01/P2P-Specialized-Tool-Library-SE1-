-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2026 at 11:56 PM
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
(1, 10, 3, 'Good', '2026-05-11');

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
(3, 'Automotive Tools'),
(6, 'Construction Equipment'),
(2, 'Electrical & Energy Equipment'),
(4, 'Hand Tools'),
(5, 'Material Handling'),
(10, 'Measurement Tools'),
(7, 'Metalworking Tools'),
(9, 'Outdoor Power Equipment'),
(8, 'Pneumatic Tools'),
(1, 'Power Tools'),
(11, 'test');

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
(1, 10, 'mmm', '2026-05-07', '2026-05-28');

-- --------------------------------------------------------

--
-- Table structure for table `consumable`
--

CREATE TABLE `consumable` (
  `consumable_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consumable`
--

INSERT INTO `consumable` (`consumable_id`, `name`, `quantity`) VALUES
(1, 'Safety Gloves', 50),
(2, 'Cutting Discs', 60),
(3, 'Welding Rods', 15),
(4, 'Lubricant Oil', 30),
(5, 'Drill Bits Set', 50),
(6, 'Screws Pack', 100),
(7, 'Nails Box', 200),
(8, 'Protective Glasses', 25),
(9, 'Extension Cable', 12),
(10, 'Measuring Tape', 18);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percent` tinyint(4) NOT NULL CHECK (`discount_percent` between 1 and 100),
  `category_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `discount_percent`, `category_id`, `start_date`, `end_date`) VALUES
(1, 'SUMMER2026', 20, NULL, '2026-06-01', '2026-08-31'),
(2, 'POWER10', 10, 1, '2026-05-01', '2026-05-31'),
(3, '78DENG', 12, 2, '2026-05-06', '2026-05-13');

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
  `submitted_at` datetime DEFAULT current_timestamp(),
  `technician_id` int(11) DEFAULT NULL,
  `tech_note` text DEFAULT NULL,
  `diagnosis_type` varchar(100) DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `dispute`
--

INSERT INTO `dispute` (`dispute_id`, `rental_id`, `handled_by`, `status`, `resolution`, `reporter_id`, `reported_user_id`, `tool_id`, `reason`, `evidence_path`, `decision`, `admin_note`, `resolved_at`, `created_at`) VALUES
(7, 3, 10, 'open', NULL, 6, NULL, 5, 'Damage Report (DMG-2026-3540): damage | Type: physical | Severity: medium', 'uploads/damage_photos/1778534142_0_qrcode_255428231_6c83fbf8b2dbaa48e0270404bca662ac.png', NULL, NULL, NULL, '2026-05-12 00:15:42');

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

--
-- Dumping data for table `maintenance_logs`
--

INSERT INTO `maintenance_logs` (`id`, `tool_id`, `action`, `notes`, `date`) VALUES
(1, 10, 'no', 'no', '2026-05-10');

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
(2, 2, 5, 8, 5, 210.00, 90.00, NULL, 0.00),
(3, 3, 5, 6, 4, 204.00, 36.00, NULL, 0.00);

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
(2, 8, 5, '2026-06-10', '2026-06-15', 'completed'),
(3, 6, 5, '2026-05-26', '2026-05-30', 'confirmed');

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
(3, 6, 1, 'Cordless Drill', 'Portable electric drilling tool used for drilling holes and driving screws in wood, metal, and plastic surfaces.', 50.00, 'Good', 1, '2026-05-20', '2026-05-11 14:18:10', 'uploads/manuals/1778509090_manual_ch5.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509090_image_shopping.webp'),
(4, 6, 7, 'Angle Grinder', 'High-speed rotating power tool designed for cutting, grinding, polishing, and sharpening metal or stone materials.', 20.00, 'Good', 1, '2026-05-26', '2026-05-11 14:22:36', 'uploads/manuals/1778509356_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509356_image_download.webp'),
(5, 7, 10, 'Laser Level', 'Precision leveling instrument that projects laser lines for accurate alignment in construction and installation work.', 60.00, 'Good', 1, '2026-05-30', '2026-05-11 14:23:55', 'uploads/manuals/1778509435_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509435_image_shopping1.webp'),
(6, 7, 8, 'Air Compressor', 'Machine that compresses air for powering pneumatic tools, inflating tires, and industrial cleaning tasks.', 50.00, 'Good', 1, '2026-05-26', '2026-05-11 14:24:42', 'uploads/manuals/1778509482_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509482_image_download1.webp'),
(7, 7, 9, 'Chainsaw', 'Motorized cutting tool with a rotating chain blade designed for cutting wood, trees, and heavy branches.', 20.00, 'Good', 1, '2026-05-27', '2026-05-11 14:25:40', 'uploads/manuals/1778509540_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509540_image_shopping2.webp'),
(8, 8, 3, 'Hydraulic Jack', 'Lifts vehicles safely for tire changes or underbody repairs.', 45.00, 'Good', 1, '2026-06-06', '2026-05-11 14:28:30', 'uploads/manuals/1778509710_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509710_image_shopping3.webp'),
(9, 8, 6, 'Excavator', 'Blends cement, sand, gravel, and water into concrete.', 56.00, 'Good', 1, '2026-05-28', '2026-05-11 14:29:44', 'uploads/manuals/1778509784_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509784_image_shopping4.webp'),
(10, 8, 2, 'Circuit Breaker Tester', 'Checks if breakers function properly under load conditions.', 25.00, 'Good', 1, '2026-05-21', '2026-05-11 14:31:02', 'uploads/manuals/1778509862_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509862_image_shopping5.webp'),
(11, 8, 4, 'Pliers', 'Grips, bends, or cuts wires and small objects', 8.00, 'Good', 0, '2026-05-30', '2026-05-11 14:31:58', 'uploads/manuals/1778509918_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509918_image_shopping6.webp'),
(12, 8, 5, 'Hoist', 'Lifts heavy loads vertically using chains or cables.', 75.00, 'Good', 1, '2026-06-24', '2026-05-11 14:33:04', 'uploads/manuals/1778509984_manual_Architecture.pdf', 'https://youtube.com/playlist?list=PLqyUgadpThTL0utmkfUB9s7VuBbvkz1km&si=UJXcYamwQD0amLhL', 'uploads/tools/1778509984_image_shopping7.webp');

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
  `trust_score` decimal(5,2) NOT NULL DEFAULT 50.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `zone_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `membership_tier`, `trust_score`, `status`, `zone_id`) VALUES
(6, 'Mahmoud', 'client@gmail.com', '$2y$10$ogP8AAQNohTrKSN4wHBUQOYyQQGuRkKnftXqCDitkm1M5aTwU.6Gm', 'client', 'premium', 50.00, 'active', 1),
(7, 'Ali', 'client2@gmail.com', '$2y$10$d3kdG./pfVvibEleRxDbO.K3inNTk4m0E5LLbhAxFaE42KU3ektP2', 'client', 'basic', 50.00, 'active', 4),
(8, 'Hassan', 'client3@gmail.com', '$2y$10$qF2HO8UnC39UgfO0GvfZweajWe3EeX0U6Ot5DNztxaHSa3BhhdTXe', 'client', 'vip', 50.00, 'active', 19),
(9, 'youssif', 'tech@gmail.com', '$2y$10$/2oprL5YsnBWexujdfnd2OhXcF4LEXV4Ywa0yFY9J/FWPKjeqN3Mm', 'technical', 'basic', 50.00, 'active', 5),
(10, 'yassen', 'admin@gmail.com', '$2y$10$Zk9BcJiI1WhJuilFzznO1eqerrCYBaIs6jn4J4X1E7QAH1B7Hy1je', 'admin', 'basic', 50.00, 'active', 3),
(11, 'ali amr', 'tech2@gmail.com', '$2y$10$3MMtALka2Xx9cyNvJOrVbOKeGOfED2kGTGpLpQcd5VeXyvNXNeMZC', 'technical', 'basic', 50.00, 'active', 13);

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
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`);

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
-- AUTO_INCREMENT for table `battery_logs`
--
ALTER TABLE `battery_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `campaign`
--
ALTER TABLE `campaign`
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `consumable`
--
ALTER TABLE `consumable`
  MODIFY `consumable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `damage_declarations`
--
ALTER TABLE `damage_declarations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `deposit`
--
ALTER TABLE `deposit`
  MODIFY `deposit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispute`
--
ALTER TABLE `dispute`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `insurance_claim`
--
ALTER TABLE `insurance_claim`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `repair_requests`
--
ALTER TABLE `repair_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tools`
--
ALTER TABLE `tools`
  MODIFY `tool_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL;

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
