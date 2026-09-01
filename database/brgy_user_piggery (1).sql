-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2024 at 03:00 PM
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
-- Database: `brgy_user_piggery`
--

-- --------------------------------------------------------

--
-- Table structure for table `health_monitoring`
--

CREATE TABLE `health_monitoring` (
  `monitoring_id` int(11) NOT NULL,
  `piggery_id` int(11) NOT NULL,
  `check_date` date NOT NULL,
  `number_of_sick` int(11) DEFAULT 0,
  `number_of_healthy` int(11) DEFAULT 0,
  `treatments_given` varchar(255) DEFAULT NULL,
  `vaccinations` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `inspected_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_monitoring`
--

INSERT INTO `health_monitoring` (`monitoring_id`, `piggery_id`, `check_date`, `number_of_sick`, `number_of_healthy`, `treatments_given`, `vaccinations`, `notes`, `inspected_by`, `created_at`, `updated_at`) VALUES
(16, 2, '2024-12-01', 3, 2, 'sample', 'sample', 'sample', 'JuanGuzmana', '2024-12-01 06:21:47', '2024-12-01 06:21:47'),
(17, 2, '2024-12-01', 12, 25, 'sample', 'sample', 'sample', 'JuanGuzmana', '2024-12-01 06:46:14', '2024-12-01 09:12:20');

-- --------------------------------------------------------

--
-- Table structure for table `incident_reports`
--

CREATE TABLE `incident_reports` (
  `incident_id` int(11) NOT NULL,
  `piggery_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `reported_by` varchar(100) DEFAULT NULL,
  `incident_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Resolved','Approved') DEFAULT 'Pending',
  `action_taken` text NOT NULL,
  `resolved_by` varchar(100) NOT NULL,
  `resolved_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incident_reports`
--

INSERT INTO `incident_reports` (`incident_id`, `piggery_id`, `report_date`, `reported_by`, `incident_type`, `description`, `status`, `action_taken`, `resolved_by`, `resolved_date`, `created_at`, `updated_at`) VALUES
(27, 2, '2024-11-19', 'Jhon', 'fever', '1 week', 'Approved', '', '', '2024-11-18', '2024-11-17 16:09:31', '2024-11-17 16:10:40'),
(28, 2, '2024-11-19', 'Jake', 'Fever', 'Last week', 'Approved', '', '', '2024-11-18', '2024-11-17 16:34:08', '2024-11-17 16:34:25'),
(29, 2, '2024-11-18', 'Jemarose', 'cough', 'last week', 'Approved', '', '', '2024-11-18', '2024-11-18 01:49:44', '2024-12-01 06:22:30'),
(30, 2, '2024-12-01', 'sample', 'sample', 'sample', 'Pending', '', '', NULL, '2024-12-01 06:23:00', '2024-12-01 06:23:00'),
(31, 2, '2024-12-01', 'sample', 'sample', 'sample', 'Approved', '', '', '2024-12-01', '2024-12-01 06:46:49', '2024-12-01 09:18:16');

-- --------------------------------------------------------

--
-- Table structure for table `piggeries`
--

CREATE TABLE `piggeries` (
  `id` int(11) NOT NULL,
  `piggery_id` int(11) NOT NULL,
  `owner_first_name` varchar(100) NOT NULL,
  `owner_middle_name` varchar(100) NOT NULL,
  `owner_last_name` varchar(100) NOT NULL,
  `piggery_name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `num_of_pigs` int(11) NOT NULL,
  `health_status` varchar(50) DEFAULT 'Healthy',
  `last_inspection_date` date DEFAULT NULL,
  `next_inspection_date` date NOT NULL,
  `compliance_status` varchar(50) DEFAULT 'Compliant',
  `notes` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Pending','Approved') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `piggeries`
--

INSERT INTO `piggeries` (`id`, `piggery_id`, `owner_first_name`, `owner_middle_name`, `owner_last_name`, `piggery_name`, `location`, `num_of_pigs`, `health_status`, `last_inspection_date`, `next_inspection_date`, `compliance_status`, `notes`, `user_id`, `created_at`, `updated_at`, `status`) VALUES
(46, 2, 'Loblee', 'Ani', 'Loblee', 'Laboy', 'Camansi', 40, 'Healthy', '2024-11-25', '0000-00-00', 'Compliant', 'Sample', 2, '2024-12-01 06:20:14', '2024-12-01 06:44:20', 'Approved'),
(51, 2, 'Lovely', 'Ani', 'Lovely', 'Sample Piggery', 'Camansi', 34, 'Healthy', '2024-11-28', '0000-00-00', 'Compliant', 'asdfasd', 2, '2024-12-01 10:19:44', '2024-12-01 10:19:48', 'Approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `health_monitoring`
--
ALTER TABLE `health_monitoring`
  ADD PRIMARY KEY (`monitoring_id`),
  ADD KEY `piggery_id` (`piggery_id`);

--
-- Indexes for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `piggery_id` (`piggery_id`);

--
-- Indexes for table `piggeries`
--
ALTER TABLE `piggeries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `health_monitoring`
--
ALTER TABLE `health_monitoring`
  MODIFY `monitoring_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `incident_reports`
--
ALTER TABLE `incident_reports`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `piggeries`
--
ALTER TABLE `piggeries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD CONSTRAINT `incident_reports_ibfk_1` FOREIGN KEY (`piggery_id`) REFERENCES `brgy_piggery`.`registered_users` (`user_id`);

--
-- Constraints for table `piggeries`
--
ALTER TABLE `piggeries`
  ADD CONSTRAINT `piggeries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `brgy_piggery`.`registered_users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
