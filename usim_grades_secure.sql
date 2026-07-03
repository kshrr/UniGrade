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
-- Database: `usim_grades_secure`
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
('1230500', 'UQ6481001 BACHELOR OF COMPUTER SCIENCE WITH HONOURS (INFORMATION SECURITY AND ASSURANCE) (QC13)', '[A252] - SEMESTER II, SESI AKADEMIK 2025/2026', 6, 'REGISTERED', 3.54, 3.65),
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
(1, '1230500', 'A251', 'SKE3012', 'CYBER DEVELOPMENT', 'EP', 2, 'A-', 8.00, 'D'),
(2, '1230500', 'A251', 'SKJ3013', 'ADVANCED JAVA PROGRAMMING', 'EP', 3, 'A+', 12.00, 'D'),
(3, '1230500', 'A251', 'SKJ3143', 'INFORMATION SECURITY MANAGEMENT', 'EP', 3, 'B+', 10.50, 'D'),
(4, '1230500', 'A251', 'SKJ3183', 'ARTIFICIAL INTELLIGENCE', 'WP', 3, 'A-', 11.25, 'D'),
(5, '1230500', 'A251', 'SKJ3192', 'DIGITAL TECHNOLOGY', 'WP', 2, 'A-', 7.50, 'D'),
(6, '1230500', 'A251', 'SKJ4143', 'CRYPTOGRAPHY AND APPLICATION', 'WP', 3, 'C', 12.00, 'D'),
(7, '1230500', 'A251', 'UTU3012', 'ENTREPRENEURSHIP', 'WU', 2, 'A', 8.00, 'D'),
(8, '1230501', 'A251', 'SKE3012', 'CYBER DEVELOPMENT', 'EP', 2, 'C', 4.00, 'D');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `matric_no` varchar(20) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `matric_no`, `token`, `expires_at`, `is_used`) VALUES
(1, '1230500', '3d58befca322f2e998c6aa2809fc0fbd8dc4341a9d8f5e195e571544081cf227', '2026-07-02 17:59:53', 0),
(2, '1230501', 'c147f500fafb8221c571b02ae369221f0a40be20f929a96054c3ca06d7ce98f4', '2026-07-02 18:00:33', 0),
(3, '1230501', '3767fb374d1b4283468e96c5fd2f28b8de38d4ddff66f882c6684a893cc0ba71', '2026-07-03 00:05:41', 1),
(4, 'admin', '6ea9b47b5e8bad2a7159372be1dc82f7b0f59de1c74d1463874fc24de01fb407', '2026-07-03 00:16:39', 0),
(5, '1230500', 'd029c590bff895892a7098b46b688ce900d89061367c4219d49b015dae894f54', '2026-07-03 12:47:43', 0);

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `matric_no` varchar(20) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `severity` varchar(10) DEFAULT 'INFO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `timestamp`, `matric_no`, `action`, `description`, `ip_address`, `severity`) VALUES
(1, '2026-06-23 15:52:12', '1230500', 'UNAUTHORIZED_IDOR_ATTEMPT', 'User attempted to access transcript records belonging to matric identifier: 1230501', '::1', 'CRITICAL'),
(2, '2026-06-23 16:04:43', '1230500', 'PRIVILEGE_ESCALATION_ATTEMPT', 'Regular student attempted to load or interact with administrative grade modifier panel.', '::1', 'CRITICAL'),
(3, '2026-06-23 16:15:00', '1230500', 'PRIVILEGE_ESCALATION_ATTEMPT', 'Regular student attempted to load or interact with administrative grade modifier panel.', '::1', 'CRITICAL'),
(4, '2026-06-23 19:13:46', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(5, '2026-06-23 19:13:53', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(6, '2026-06-23 19:14:25', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(7, '2026-06-23 19:16:50', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(8, '2026-06-23 19:17:55', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(9, '2026-06-23 19:18:17', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(10, '2026-06-23 19:22:42', '1230500', 'IDOR_ATTEMPT', 'User attempted unauthorized horizontal record scraping for matric: 1230501', '::1', 'HIGH'),
(11, '2026-06-23 19:24:21', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(12, '2026-06-23 19:24:42', 'admin', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(13, '2026-06-23 19:24:54', '1230501', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(14, '2026-06-23 19:30:36', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(15, '2026-06-23 19:34:54', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(16, '2026-06-23 19:35:06', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(17, '2026-06-23 19:35:16', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(18, '2026-06-23 19:35:19', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(19, '2026-06-23 19:35:26', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(20, '2026-06-23 19:38:15', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(21, '2026-06-23 19:38:28', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(22, '2026-06-23 19:39:09', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(23, '2026-06-23 20:00:21', 'admin', 'GRADE_UPDATE', 'Updated GPA for <script>alert(\'Your session cookie is: \' + document.cookie);</script> to <script>alert(\'Your session cookie is: \' + document.cookie);</script>', '::1', 'INFO'),
(24, '2026-07-02 15:44:53', '1230500', 'PASSWORD_RESET_REQ', 'Password reset validation lifetime token issued.', '::1', 'INFO'),
(25, '2026-07-02 15:45:33', '1230501', 'PASSWORD_RESET_REQ', 'Password reset validation lifetime token issued.', '::1', 'INFO'),
(26, '2026-07-02 15:50:41', '1230501', 'PASSWORD_RESET_REQ', 'Password reset validation lifetime token issued.', '::1', 'INFO'),
(27, '2026-07-02 15:51:06', '1230501', 'PASSWORD_RESET_SUCCESS', 'User verified password sequence updated successfully via lifecycle tokens tracking.', '::1', 'INFO'),
(28, '2026-07-02 16:00:49', '1230501', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(29, '2026-07-02 16:01:39', 'admin', 'PASSWORD_RESET_REQ', 'Password reset validation lifetime token issued.', '::1', 'INFO'),
(30, '2026-07-02 16:04:58', '1230501', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(31, '2026-07-03 02:03:33', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(32, '2026-07-03 02:08:03', '1230500', 'IDOR_ATTEMPT', 'User attempted unauthorized horizontal record scraping for matric: 1230501', '::1', 'HIGH'),
(33, '2026-07-03 02:11:17', 'admin', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(34, '2026-07-03 02:46:10', 'admin', 'GRADE_OVERRIDE', 'Altered 1230500: Course SKE3012 to A-, GPA to 3.60, CGPA to 3.67', '::1', 'INFO'),
(35, '2026-07-03 02:46:25', 'admin', 'GRADE_OVERRIDE', 'Altered 1230500: Course SKJ4143 to C+, GPA to 3.60, CGPA to 3.66', '::1', 'INFO'),
(36, '2026-07-03 03:13:59', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(37, '2026-07-03 03:14:19', '1230501', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(38, '2026-07-03 03:17:26', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(39, '2026-07-03 03:17:38', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(40, '2026-07-03 03:37:12', 'admin', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(41, '2026-07-03 03:37:37', 'admin', 'GRADE_OVERRIDE', 'Altered 1230500: Course SKJ4143 to C, GPA to 3.58, CGPA to 3.64. Cryptographic ledger block minted.', '::1', 'INFO'),
(42, '2026-07-03 03:38:00', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(43, '2026-07-03 03:39:34', 'admin', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(44, '2026-07-03 03:40:01', 'admin', 'GRADE_OVERRIDE', 'Altered 1230500: Course SKE3012 to A-, GPA to 3.54, CGPA to 3.65. Cryptographic ledger block minted.', '::1', 'INFO'),
(45, '2026-07-03 03:40:21', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(46, '2026-07-03 03:55:03', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(47, '2026-07-03 03:58:55', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(48, '2026-07-03 04:04:25', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(49, '2026-07-03 04:26:22', 'admin', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(50, '2026-07-03 04:32:43', '1230500', 'PASSWORD_RESET_REQ', 'Password reset validation lifetime token issued.', '::1', 'INFO'),
(51, '2026-07-03 10:46:46', '1230500', 'LOGIN_FAILED', 'Failed login attempt.', '::1', 'WARNING'),
(52, '2026-07-03 10:46:54', '1230500', 'LOGIN_SUCCESS', 'User logged in successfully.', '::1', 'INFO'),
(53, '2026-07-03 10:55:14', '1230500', 'PROFILE_UPDATE_SUCCESS', 'User successfully synchronized profile information matrix.', '::1', 'INFO'),
(54, '2026-07-03 10:55:22', '1230500', 'PROFILE_UPDATE_SUCCESS', 'User successfully synchronized profile information matrix.', '::1', 'INFO');

-- --------------------------------------------------------

--
-- Table structure for table `transcript_ledger`
--

CREATE TABLE `transcript_ledger` (
  `id` int(11) NOT NULL,
  `matric_no` varchar(20) NOT NULL,
  `block_hash` varchar(64) NOT NULL,
  `previous_hash` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transcript_ledger`
--

INSERT INTO `transcript_ledger` (`id`, `matric_no`, `block_hash`, `previous_hash`, `created_at`) VALUES
(1, '1230500', 'ac59edef8a89f2cebe5dce0a52ca11c6218dd1f20f0f7e72550dd6a710f993a0', '0000000000000000000000000000000000000000000000000000000000000000', '2026-07-03 03:37:37'),
(2, '1230500', 'ac59edef8a89f2cebe5dce0a52ca11c6218dd1f20f0f7e72550dd6a710f993a0', 'ac59edef8a89f2cebe5dce0a52ca11c6218dd1f20f0f7e72550dd6a710f993a0', '2026-07-03 03:40:01');

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
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'default_avatar.png', 'System Administrator', NULL, NULL),
(2, '1230500', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'e1967d0d73f1c5e8733da29cae1ba09b.jpeg', 'Ubaidah Bin Amran', 'ubaidah.amran@raudah.usim.edu.my', '01156838287'),
(3, '1230501', '$2y$12$v1F.i2a29Ro9dFDzVEIS1OhGwjrerBWE820pGeNl7AhhDcs/Syd9K', 'student', 'default_avatar.png', 'Ali bin Abu', NULL, NULL);

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
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transcript_ledger`
--
ALTER TABLE `transcript_ledger`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `transcript_ledger`
--
ALTER TABLE `transcript_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
