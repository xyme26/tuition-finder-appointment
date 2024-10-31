-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2024 at 04:28 AM
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
-- Database: `tuition_finder_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', 'testing', NULL, '2024-10-11 14:13:52'),
(3, 'admin1', '$2y$10$cQ8PTsCs1Yk5R3eQ4u7GyuChL98WZoZhyqlcuQoDJHF5GqYHin0QS', 'admin@example.com', '2024-10-20 06:19:15');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tuition_center_id` int(11) DEFAULT NULL,
  `appointment_datetime` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','rescheduled','cancelled') NOT NULL DEFAULT 'pending',
  `notification_sent` tinyint(1) NOT NULL DEFAULT 0,
  `reschedule_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `tuition_center_id`, `appointment_datetime`, `admin_id`, `created_at`, `reason`, `status`, `notification_sent`, `reschedule_reason`) VALUES
(1, 1, NULL, '2024-10-30 12:00:00', NULL, '2024-10-21 11:15:35', 'test', 'pending', 0, NULL),
(2, 1, 2, '2024-10-24 11:00:00', NULL, '2024-10-21 11:21:15', 'etete', 'rescheduled', 0, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tuition_center_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `tuition_center_id`, `created_at`) VALUES
(3, 1, 2, '2024-10-22 09:43:38');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` enum('good','neutral','bad') NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 'good', '', '2024-10-22 13:56:50');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tuition_center_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reply` text DEFAULT NULL,
  `liked_by_admin` tinyint(4) DEFAULT 0,
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `tuition_center_id`, `rating`, `comment`, `created_at`, `reply`, `liked_by_admin`, `approved`) VALUES
(1, NULL, 2, 4, 'test', '2024-10-20 13:31:14', NULL, 0, 0),
(2, NULL, 2, 3, 'TEST', '2024-10-20 13:36:50', NULL, 0, 0),
(3, NULL, 2, 5, 'test', '2024-10-20 13:43:45', NULL, 0, 0),
(4, NULL, 2, 4, 'SDS', '2024-10-20 13:46:21', NULL, 0, 0),
(5, NULL, 2, 4, 'sdsd', '2024-10-20 13:55:30', NULL, 0, 0),
(6, 1, 2, 4, 'dsd', '2024-10-20 13:59:31', 'test', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tuition_centers`
--

CREATE TABLE `tuition_centers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `course_tags` varchar(255) DEFAULT NULL,
  `price_range` varchar(50) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tuition_centers`
--

INSERT INTO `tuition_centers` (`id`, `name`, `address`, `city`, `latitude`, `longitude`, `image`, `created_at`, `course_tags`, `price_range`, `contact`, `description`) VALUES
(2, 'ABC', 'No. 3, Jalan SS15/8,47500 Subang Jaya, Selangor Darul Ehsan,Malaysia', NULL, NULL, NULL, 'uploads/purple.jpg', '2024-10-20 08:48:57', 'Science,English,History,Chemistry,Biology,Music', 'RM10-RM20', '012312131213', '123testing sfssddsd');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `user_role` enum('admin','user') DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `fav_update` tinyint(1) DEFAULT 0,
  `upcoming_appointment` tinyint(1) DEFAULT 0,
  `appointment_confirmation` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `password`, `email`, `user_role`, `profile_picture`, `created_at`, `reset_token`, `phone_number`, `address`, `fav_update`, `upcoming_appointment`, `appointment_confirmation`) VALUES
(1, 'xyme', 'Cheong', 'En Ying', '$2y$10$F88Tx0/MF8w4zHbG5Xeev.V8NII7qEizRJ5SkPPrikYfpdqNeUfMG', 'enyingcheong@gmail.com', 'user', NULL, '2024-10-17 05:58:45', NULL, '123', 'dsds', 0, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_admin_id` (`admin_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_favorite` (`user_id`),
  ADD KEY `fk_tuition_center_favorite` (`tuition_center_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`tuition_center_id`),
  ADD UNIQUE KEY `user_id_2` (`user_id`,`tuition_center_id`),
  ADD KEY `fk_reviews_tuition_center_id` (`tuition_center_id`);

--
-- Indexes for table `tuition_centers`
--
ALTER TABLE `tuition_centers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lat_lng` (`latitude`,`longitude`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tuition_centers`
--
ALTER TABLE `tuition_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`tuition_center_id`) REFERENCES `tuition_centers` (`id`),
  ADD CONSTRAINT `fk_tuition_center_favorite` FOREIGN KEY (`tuition_center_id`) REFERENCES `tuition_centers` (`id`),
  ADD CONSTRAINT `fk_user_favorite` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_tuition_center_id` FOREIGN KEY (`tuition_center_id`) REFERENCES `tuition_centers` (`id`),
  ADD CONSTRAINT `fk_reviews_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`tuition_center_id`) REFERENCES `tuition_centers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
