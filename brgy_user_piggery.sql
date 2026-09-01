-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2026 at 04:52 PM
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
  MODIFY `monitoring_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incident_reports`
--
ALTER TABLE `incident_reports`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `piggeries`
--
ALTER TABLE `piggeries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
