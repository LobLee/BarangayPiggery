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

--
-- Dumping data for table `incident_actions`
--

INSERT INTO `incident_actions` (`action_id`, `incident_id`, `action_taken`, `resolved_by`, `resolved_date`, `action_timestamp`) VALUES
(26, 10, 'Dailyn', 'kill', '2024-11-16', '2024-11-16 15:31:37'),
(27, 10, 'Kill', 'Dailyn', '2024-11-16', '2024-11-16 15:35:36'),
(28, 11, 'FFFFF', 'Dailyn', '2024-11-16', '2024-11-16 15:44:12'),
(29, 12, 'SADF', 'ASDF', '2024-11-16', '2024-11-16 15:48:28'),
(30, 12, 'SADF', 'GGGGG', '2024-11-16', '2024-11-16 15:50:24'),
(31, 11, 'casdf', 'Dailyn', '2024-11-16', '2024-11-16 15:53:30'),
(32, 11, 'bbbb', 'Dailyn', '2024-11-16', '2024-11-16 15:57:16'),
(33, 13, 'fsdfgsf', 'jjjjj', '2024-11-16', '2024-11-16 15:58:45'),
(35, 13, 'BBBBBB', 'jjjjj', '2024-11-17', '2024-11-16 16:02:41'),
(36, 13, 'BBBBBB', 'BBBBBBBB', '2024-11-17', '2024-11-16 16:03:47'),
(37, 12, 'gggggggggg', 'GGGGG', '2024-11-17', '2024-11-16 16:08:45'),
(38, 13, 'BBBBBB', 'aaaaaaaaaaaa', '2024-11-17', '2024-11-16 16:17:40'),
(40, 16, 'asdf', '3', '2024-11-17', '2024-11-16 17:36:37'),
(41, 15, 'asdfasd', '3', '2024-11-17', '2024-11-16 17:42:18'),
(44, 13, 'gggg', '3', '2024-11-17', '2024-11-16 18:06:24'),
(45, 12, 'aaaaaaaaaaaaa', '3', '2024-11-17', '2024-11-16 18:07:22'),
(46, 18, 'qwerty', '12', '2024-11-17', '2024-11-16 18:11:50'),
(47, 19, 'eeeeeeeeeeeeewwww', '12', '2024-11-17', '2024-11-16 18:16:13'),
(48, 11, 'gggggg', '3', '2024-11-17', '2024-11-17 15:56:21'),
(49, 20, 'Medicine\r\n', '3', '2024-11-18', '2024-11-17 16:00:33'),
(50, 21, 'vaccine', '12', '2024-11-18', '2024-11-17 16:12:08'),
(51, 19, 'eeeeewwww', '12', '2024-11-18', '2024-11-17 16:17:56'),
(52, 18, 'zzzzz', '12', '2024-11-18', '2024-11-17 16:18:44'),
(53, 18, 'vvvvvvvv', '12', '2024-11-18', '2024-11-17 16:25:09'),
(54, 19, 'ddddddddd', '12', '2024-11-18', '2024-11-17 16:31:25'),
(55, 21, 'vaccine1', '12', '2024-11-18', '2024-11-17 16:32:04'),
(56, 23, 'applying vaccines', '12', '2024-11-18', '2024-11-18 01:51:18'),
(57, 24, 'Vacines', '13', '2024-12-01', '2024-12-01 13:50:30');

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
-- Dumping data for table `inspect_piggeries`
--

INSERT INTO `inspect_piggeries` (`inspection_id`, `piggery_id`, `inspector_id`, `piggery_name`, `owner_first_name`, `owner_middle_name`, `owner_last_name`, `location`, `num_of_pigs`, `last_inspection_date`, `next_inspection_date`, `created_at`) VALUES
(33, 2, 3, 'qwert', 'Joe', 'Jay', 'Ani', 'Cebu', 12, '2024-11-13', '2024-11-20', '2024-11-13 17:43:14'),
(34, 2, 3, 'asdf', 'Joe', 'Jay', 'Ani', 'sdfas', 4, '2024-11-13', '2024-11-25', '2024-11-13 19:27:42'),
(35, 2, 12, 'adf', 'Joe', 'Jay', 'Ani', 'asdf', 30, '2024-11-16', '2024-11-23', '2024-11-16 18:18:26'),
(36, 2, 12, 'adf', 'Joe', 'Jay', 'Ani', 'asdf', 30, '2024-11-16', '2024-11-23', '2024-11-16 18:19:07'),
(37, 2, 3, 'Laboy', 'Joe', 'Jay', 'Ani', 'Camansi', 10, '2024-11-17', '2024-11-20', '2024-11-17 15:56:01'),
(38, 2, 13, 'Sample Piggery', 'Loblee', 'Ani', 'Guzmana', 'Camansi Tomas Oppus Southern Leyte', 10, '2024-12-01', '2024-12-06', '2024-12-01 06:26:28'),
(39, 2, 13, 'adf', 'Joe', 'Jay', 'Ani', 'asdf', 30, '2024-12-01', '2024-12-06', '2024-12-01 06:49:45'),
(40, 2, 13, 'Sample Piggery', 'Lovely', 'Ani', 'Lovely', 'Camansi', 30, '2024-12-01', '2024-12-18', '2024-12-01 09:15:26');

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
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `inspection_results`
--
ALTER TABLE `inspection_results`
  MODIFY `inspection_result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inspect_piggeries`
--
ALTER TABLE `inspect_piggeries`
  MODIFY `inspection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
