-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Aug 03, 2026 at 08:03 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hometutor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `guardian_requests`
--

CREATE TABLE `guardian_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `class_level` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `additional_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardian_requests`
--

INSERT INTO `guardian_requests` (`id`, `student_id`, `subject`, `class_level`, `location`, `salary`, `description`, `created_at`, `additional_address`) VALUES
(2, 5, 'Math', 'Grade 1-8', 'Badda', 3000.00, '', '2026-07-28 13:22:05', 'House-5'),
(3, 5, 'Math', 'Grade 1-8', 'Badda', 1500.00, '', '2026-07-28 13:37:06', 'house 4');

-- --------------------------------------------------------

--
-- Table structure for table `guardian_request_applications`
--

CREATE TABLE `guardian_request_applications` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `proposed_salary` decimal(10,2) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Accepted','Rejected','Negotiating') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `offered_by` enum('Tutor','Guardian') DEFAULT 'Tutor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardian_request_applications`
--

INSERT INTO `guardian_request_applications` (`id`, `request_id`, `tutor_id`, `proposed_salary`, `message`, `status`, `created_at`, `offered_by`) VALUES
(1, 2, 4, 3800.00, 'Weekly 3 days', 'Accepted', '2026-07-28 13:23:52', 'Tutor'),
(2, 3, 4, 1500.00, 'weekly 3 days', 'Accepted', '2026-07-28 13:37:30', 'Tutor');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Accepted','Rejected','Negotiating') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `proposed_salary` decimal(10,2) DEFAULT NULL,
  `offered_by` enum('Student','Tutor') DEFAULT 'Student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutor_profile`
--

CREATE TABLE `tutor_profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `class_level` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `availability` enum('Available','Not Available') DEFAULT 'Available',
  `picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutor_profile`
--

INSERT INTO `tutor_profile` (`id`, `user_id`, `subject`, `class_level`, `location`, `experience`, `salary`, `description`, `availability`, `picture`) VALUES
(2, 4, 'Math', 'Grade 1-8', 'Badda', '2', 3000.00, '', 'Not Available', '/EduConnect/assets/uploads/tutors/tutor_4_1785244100.jpg'),
(3, 6, 'Math', 'Grade 1-8', 'Badda', '3', 4000.00, '', 'Available', '/EduConnect/assets/uploads/tutors/tutor_6_1785245241.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Tutor','Student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@hometutor.com', 'admin123', 'Admin', '2026-06-08 18:33:56'),
(4, 'Moshiur Rahman', 'moshiur@gmail.com', '123456', 'Tutor', '2026-07-28 13:05:52'),
(5, 'Shahriar Hossain', 'rafi@gmail.com', '123456', 'Student', '2026-07-28 13:08:49'),
(6, 'Akib', 'akib@gmail.com', '123456', 'Tutor', '2026-07-28 13:26:57'),
(7, 'Lima', 'lima@gmail.com', '123456', 'Student', '2026-07-28 13:38:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guardian_requests`
--
ALTER TABLE `guardian_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `guardian_request_applications`
--
ALTER TABLE `guardian_request_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_application` (`request_id`,`tutor_id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indexes for table `tutor_profile`
--
ALTER TABLE `tutor_profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guardian_requests`
--
ALTER TABLE `guardian_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `guardian_request_applications`
--
ALTER TABLE `guardian_request_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tutor_profile`
--
ALTER TABLE `tutor_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guardian_requests`
--
ALTER TABLE `guardian_requests`
  ADD CONSTRAINT `guardian_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guardian_request_applications`
--
ALTER TABLE `guardian_request_applications`
  ADD CONSTRAINT `guardian_request_applications_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `guardian_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guardian_request_applications_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutor_profile`
--
ALTER TABLE `tutor_profile`
  ADD CONSTRAINT `tutor_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
