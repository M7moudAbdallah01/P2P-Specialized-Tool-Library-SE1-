-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 02:35 PM
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
-- Table structure for table `availability_calendar`
--

CREATE TABLE `availability_calendar` (
  `calendar_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `buffer_time` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `resolution` text DEFAULT NULL
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
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `encrypted` tinyint(1) NOT NULL DEFAULT 0
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

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_price`, `status`, `order_date`) VALUES
(1, 1, 150.00, 'completed', '2026-05-03 18:15:34'),
(2, 2, 200.00, 'completed', '2026-05-03 18:15:34'),
(3, 3, 450.00, 'completed', '2026-05-03 18:15:34');

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
-- Table structure for table `rental`
--

CREATE TABLE `rental` (
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

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `rental_date` date NOT NULL,
  `return_date` date NOT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `penalty_fee` decimal(10,2) DEFAULT 0.00,
  `escalation_level` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `user_id`, `tool_id`, `rental_date`, `return_date`, `status`, `penalty_fee`, `escalation_level`) VALUES
(1, 1, 1, '2026-04-20', '2026-04-27', 'active', 130.00, 2),
(1, 1, 1, '2026-04-20', '2026-04-27', 'active', 130.00, 2),
(1, 1, 1, '2026-04-20', '2026-04-27', 'active', 130.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending'
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tools`
--

INSERT INTO `tools` (`tool_id`, `owner_id`, `category_id`, `name`, `description`, `base_price`, `state`, `availability`, `warranty_expiry_date`, `created_at`) VALUES
(1, 6, 4, 'mmmm', 'nnnn', 50.00, 'good', 0, '2026-05-05', '2026-05-03 10:59:28');

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
-- Table structure for table `tool_specifications`
--

CREATE TABLE `tool_specifications` (
  `id` int(11) NOT NULL,
  `tool_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `manual_path` varchar(255) NOT NULL,
  `video_link` varchar(255) DEFAULT NULL,
  `warranty` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tool_specifications`
--

INSERT INTO `tool_specifications` (`id`, `tool_name`, `description`, `manual_path`, `video_link`, `warranty`, `created_at`) VALUES
(2, 'hammer', 'wooden', 'uploads/manuals/1777800837_ERD .drawio.pdf', 'https://youtu.be/ibhIL75VA9Y?si=yNkx9RBYlgF0Wu2k', '2', '2026-05-03 09:33:57');

-- --------------------------------------------------------

--
-- Table structure for table `trust_score`
--

CREATE TABLE `trust_score` (
  `trust_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` decimal(3,2) NOT NULL DEFAULT 0.00,
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `membership_tier`, `trust_score`, `status`) VALUES
(3, 'Mahmoud', 'mahmoud@gmail.com', '$2y$10$V1J9qk9v8b3cYh8m2x0vOe0nK1lGfXl8pQk8rZcQmZ8h0lWwE2m6e', 'admin', 'basic', 0.00, 'active'),
(6, 'Hassan', 'hass@gmail.com', '$2y$10$vQ8cWYe90o94.rI241CbyuxaLkCNnnU.vvDobcifn4Ve4fMvkJbsa', 'client', 'basic', 0.00, 'active'),
(7, 'aaa', 'aaa@gmail.com', '$2y$10$Noyvw9TKjyttG/94u56VH.L4MgGiTyZbOp1a.e.HXleSkL45MiBi6', 'technical', 'basic', 0.90, 'active'),
(8, 'Mahmoud', 'mahmoud123abdallah456@gmail.com', '$2y$10$8n8yyv.iPhTB8fi6I.79v.FwyvFnkLLPg3/RjWI8SeP655xpwGcGW', 'admin', 'basic', 0.00, 'active'),
(9, 'wiener', 'mmm@gmail.com', '$2y$10$u1sXMcST98C5RNZVZKBHd.V9TwYliFENFBcJacpxoB6pyIlSWlqXK', 'client', 'basic', 0.00, 'active'),
(10, 'wiener', 'm@gmail.com', '$2y$10$lDsfVJvG2nLdMc9hHfYoB.ytP1cHKQNbM.Ek.oQmaEwz56XP8Icoe', 'client', 'basic', 0.00, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`id`, `name`) VALUES
(1, 'cairo'),
(2, 'cairo');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `availability_calendar`
--
ALTER TABLE `availability_calendar`
  ADD PRIMARY KEY (`calendar_id`),
  ADD UNIQUE KEY `tool_id` (`tool_id`);

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
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `rental`
--
ALTER TABLE `rental`
  ADD PRIMARY KEY (`rental_id`),
  ADD UNIQUE KEY `reservation_id` (`reservation_id`),
  ADD KEY `tool_id` (`tool_id`),
  ADD KEY `renter_id` (`renter_id`);

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
-- Indexes for table `tool_specifications`
--
ALTER TABLE `tool_specifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trust_score`
--
ALTER TABLE `trust_score`
  ADD PRIMARY KEY (`trust_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `availability_calendar`
--
ALTER TABLE `availability_calendar`
  MODIFY `calendar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `available_date`
--
ALTER TABLE `available_date`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `battery_logs`
--
ALTER TABLE `battery_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consumable`
--
ALTER TABLE `consumable`
  MODIFY `consumable_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposit`
--
ALTER TABLE `deposit`
  MODIFY `deposit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispute`
--
ALTER TABLE `dispute`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `insurance_claim`
--
ALTER TABLE `insurance_claim`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rental`
--
ALTER TABLE `rental`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tools`
--
ALTER TABLE `tools`
  MODIFY `tool_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tool_document`
--
ALTER TABLE `tool_document`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tool_specifications`
--
ALTER TABLE `tool_specifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trust_score`
--
ALTER TABLE `trust_score`
  MODIFY `trust_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `availability_calendar`
--
ALTER TABLE `availability_calendar`
  ADD CONSTRAINT `availability_calendar_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `deposit_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `dispute`
--
ALTER TABLE `dispute`
  ADD CONSTRAINT `dispute_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`rental_id`) ON DELETE CASCADE,
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
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `rental`
--
ALTER TABLE `rental`
  ADD CONSTRAINT `rental_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rental_ibfk_2` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`),
  ADD CONSTRAINT `rental_ibfk_3` FOREIGN KEY (`renter_id`) REFERENCES `users` (`user_id`);

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
-- Constraints for table `trust_score`
--
ALTER TABLE `trust_score`
  ADD CONSTRAINT `trust_score_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
