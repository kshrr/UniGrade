-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jul 03, 2026 at 01:10 PM
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
-- Database: `usim_grades_insecure`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_records`
--

CREATE TABLE `academic_records` (
  `matric_no` varchar(20) NOT NULL,
  `programme` varchar(255) NOT NULL,
  `current_semester` varchar(100) NOT NULL,
  `sem_no` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `gpa` decimal(4,2) NOT NULL,
  `cgpa` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_records`
--

INSERT INTO `academic_records` (`matric_no`, `programme`, `current_semester`, `sem_no`, `status`, `gpa`, `cgpa`) VALUES
('1230500', 'UQ6481001 BACHELOR OF COMPUTER SCIENCE WITH HONOURS (INFORMATION SECURITY AND ASSURANCE) (QC13)', '[A252] - SEMESTER II, SESI AKADEMIK 2025/2026', 6, 'REGISTERED', 3.51, 3.68),
('1230501', 'UQ6481001 BACHELOR OF COMPUTER SCIENCE WITH HONOURS', '[A252] - SEMESTER II, SESI AKADEMIK 2025/2026', 6, 'REGISTERED', 2.50, 2.75);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `matric_no` varchar(20) NOT NULL,
  `semester_id` varchar(50) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `description` varchar(150) NOT NULL,
  `course_component` varchar(10) NOT NULL,
  `credit` int(11) NOT NULL,
  `grade` varchar(5) NOT NULL,
  `grade_point` decimal(5,2) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `matric_no`, `semester_id`, `course_code`, `description`, `course_component`, `credit`, `grade`, `grade_point`, `status`) VALUES
(1, '1230500', 'A251', 'SKE3012', 'CYBER DEVELOPMENT', 'EP', 2, 'A', 8.00, 'D'),
(2, '1230500', 'A251', 'SKJ3013', 'ADVANCED JAVA PROGRAMMING', 'EP', 3, 'A+', 12.00, 'D'),
(3, '1230500', 'A251', 'SKJ3143', 'INFORMATION SECURITY MANAGEMENT', 'EP', 3, 'B+', 10.50, 'D'),
(4, '1230500', 'A251', 'SKJ3183', 'ARTIFICIAL INTELLIGENCE', 'WP', 3, 'A-', 11.25, 'D'),
(5, '1230500', 'A251', 'SKJ3192', 'DIGITAL TECHNOLOGY', 'WP', 2, 'A-', 7.50, 'D'),
(6, '1230500', 'A251', 'SKJ4143', 'CRYPTOGRAPHY AND APPLICATION', 'WP', 3, 'C+', 12.00, 'D'),
(7, '1230500', 'A251', 'UTU3012', 'ENTREPRENEURSHIP', 'WU', 2, 'A', 8.00, 'D'),
(8, '1230501', 'A251', 'SKE3012', 'CYBER DEVELOPMENT', 'EP', 2, 'C', 4.00, 'D');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `matric_no` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'student',
  `profile_pic` varchar(255) DEFAULT 'default_avatar.png',
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `matric_no`, `password`, `role`, `profile_pic`, `name`, `email`, `phone_no`) VALUES
(1, 'admin', 'admin123', 'admin', 'default_avatar.png', 'System Administrator', NULL, NULL),
(2, '1230500', 'student1234', 'student', 'sign1-1.png', 'Ubaidah Bin Amran', 'ubaidah.amran@raudah.usim.edu.my', '01156838287'),
(3, '1230501', 'student123', 'student', 'default_avatar.png', 'Ali bin Abu', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_records`
--
ALTER TABLE `academic_records`
  ADD PRIMARY KEY (`matric_no`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matric_no` (`matric_no`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matric_no` (`matric_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_records`
--
ALTER TABLE `academic_records`
  ADD CONSTRAINT `academic_records_ibfk_1` FOREIGN KEY (`matric_no`) REFERENCES `users` (`matric_no`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`matric_no`) REFERENCES `users` (`matric_no`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
