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
-- Database: `brgy_inspector_piggery`
--

-- --------------------------------------------------------

--
-- Table structure for table `incident_actions`
--

CREATE TABLE `incident_actions` (
  `action_id` int(11) NOT NULL,
  `incident_id` int(11) NOT NULL,
  `action_taken` text DEFAULT NULL,
  `resolved_by` varchar(100) DEFAULT NULL,
  `resolved_date` date DEFAULT NULL,
  `action_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_results`
--

CREATE TABLE `inspection_results` (
  `inspection_result_id` int(11) NOT NULL,
  `inspection_id` int(11) NOT NULL,
  `compliance_status` enum('Compliant','Non-compliant') DEFAULT 'Compliant',
  `health_status` varchar(50) DEFAULT 'Good',
  `observations` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `inspection_date` date NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspect_piggeries`
--

CREATE TABLE `inspect_piggeries` (
  `inspection_id` int(11) NOT NULL,
  `piggery_id` int(11) NOT NULL,
  `inspector_id` int(11) NOT NULL,
  `piggery_name` varchar(255) NOT NULL,
  `owner_first_name` varchar(100) NOT NULL,
  `owner_middle_name` varchar(100) NOT NULL,
  `owner_last_name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `num_of_pigs` int(11) NOT NULL,
  `last_inspection_date` date NOT NULL,
  `next_inspection_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `incident_actions`
--
ALTER TABLE `incident_actions`
  ADD PRIMARY KEY (`action_id`);

--
-- Indexes for table `inspection_results`
--
ALTER TABLE `inspection_results`
  ADD PRIMARY KEY (`inspection_result_id`),
  ADD KEY `inspection_id` (`inspection_id`);

--
-- Indexes for table `inspect_piggeries`
--
ALTER TABLE `inspect_piggeries`
  ADD PRIMARY KEY (`inspection_id`),
  ADD KEY `piggery_id` (`piggery_id`),
  ADD KEY `inspector_id` (`inspector_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `incident_actions`
--
ALTER TABLE `incident_actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_results`
--
ALTER TABLE `inspection_results`
  MODIFY `inspection_result_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspect_piggeries`
--
ALTER TABLE `inspect_piggeries`
  MODIFY `inspection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inspection_results`
--
ALTER TABLE `inspection_results`
  ADD CONSTRAINT `inspection_results_ibfk_1` FOREIGN KEY (`inspection_id`) REFERENCES `inspect_piggeries` (`inspection_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
