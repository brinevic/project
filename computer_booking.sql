-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2025 at 05:23 PM
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
-- Database: `computer_booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `ticket_number` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `computer_id`, `start_time`, `end_time`, `ticket_number`) VALUES
(13, 1, 14, '2025-04-08 15:14:59', '2025-04-08 17:14:59', 'TKT-FE969F'),
(14, 6, 15, '2025-04-08 15:19:11', '2025-04-08 17:19:11', 'TKT-F0F5E6');

-- --------------------------------------------------------

--
-- Table structure for table `computers`
--

CREATE TABLE `computers` (
  `id` int(11) NOT NULL,
  `computer_name` varchar(100) NOT NULL,
  `status` enum('available','in_use') DEFAULT 'available',
  `in_use` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computers`
--

INSERT INTO `computers` (`id`, `computer_name`, `status`, `in_use`) VALUES
(13, 'Computer 5', 'available', 1),
(14, 'Computer 8', 'available', 1),
(15, 'computer 6', 'available', 1),
(16, 'computer 9', 'available', 0),
(17, 'Computer 10', 'available', 0),
(18, 'Computer 11', 'available', 0),
(19, 'Computer 3', 'available', 0),
(20, 'Computer 1', 'available', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(100) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `password`, `email`, `created_at`, `name`, `mobile`, `role`) VALUES
(1, '$2y$10$f2iOSLEYDAlO7r6fENz5FeWvesUQOf31m9tdyC3MJzRFua/2ZzR9C', 'oloobryan204@gmail.com', '2025-04-06 16:39:44', 'OLOO BRYAN', '+25471234567', 'admin'),
(2, '$2y$10$6lzOY.i2wzM1GCiPUyrmVO/BNR1OsUxhTvy//pVSw6aWir8OZ8umS', 'john@example.com', '2025-04-06 16:55:14', 'john doe', '+25471234567', 'user'),
(3, '$2y$10$jgoXjlzWDv1bANmmpmLcleCzqNqsLxjPtTYbSH0gQEtdAkle4Fo7S', 'oloobryan414@gmail.com', '2025-04-06 17:01:01', 'richard odhiambo', '+254798644485', 'user'),
(4, '$2y$10$yHxKB3e19L.Q6DFKHNOraedx3jGQWeuA/2vSJemVFrAqEdXH8554.', 'brianvictor343@gmail.com', '2025-04-06 21:56:06', 'BRIAN OLUOCH', '+254798644485', 'user'),
(5, '$2y$10$Aapwc7kg.FJSQQG57LkqzeJh3bcxwky49TZESZj2ze.LP114DqVYG', 'grayepaul22@gmail.com', '2025-04-07 07:19:42', 'Grace Paul', '+25471234906', 'user'),
(6, '$2y$10$.AjtDcm3sZhfxIlmh8mpu.4rHPA0nDrygMxA6DGdz2pIdS3XCTila', 'annl84480@gmail.com', '2025-04-07 07:47:46', 'Leah Ann', '+25479812346', 'user'),
(7, '$2y$10$rQ7KCRGR3fgF7k9f99MrPuFnlCCHy9/0RUdxCtb4KR7S2pzv9.Po6', 'briankama15@gmail.com', '2025-04-07 08:13:30', 'Brian Kamau', '+25476534578', 'user'),
(8, '$2y$10$C07oD733EM8ejW8pZrFkgu1GgA7ArlQu5tD2KdIt/z9mS2F3N3NS6', 'oluochv871@gmail.com', '2025-04-07 08:17:25', 'Victor Oluoch', '+25473456178', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `computer_id` (`computer_id`);

--
-- Indexes for table `computers`
--
ALTER TABLE `computers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `computers`
--
ALTER TABLE `computers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
