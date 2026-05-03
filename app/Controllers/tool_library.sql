-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 03 مايو 2026 الساعة 12:02
-- إصدار الخادم: 10.4.32-MariaDB
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
-- بنية الجدول `tool_specifications`
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
-- إرجاع أو استيراد بيانات الجدول `tool_specifications`
--

INSERT INTO `tool_specifications` (`id`, `tool_name`, `description`, `manual_path`, `video_link`, `warranty`, `created_at`) VALUES
(2, 'hammer', 'wooden', 'uploads/manuals/1777800837_ERD .drawio.pdf', 'https://youtu.be/ibhIL75VA9Y?si=yNkx9RBYlgF0Wu2k', '2', '2026-05-03 09:33:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tool_specifications`
--
ALTER TABLE `tool_specifications`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tool_specifications`
--
ALTER TABLE `tool_specifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
