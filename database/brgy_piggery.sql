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
-- Database: `brgy_piggery`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `activity_description` text NOT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action_type`, `activity_description`, `module`, `created_at`) VALUES
(67, 10, 'Logout', 'User logged out.', 'Authentication', '2024-12-01 02:20:58'),
(68, 10, 'Login', 'User logged in successfully.', 'Authentication', '2024-12-01 09:49:20'),
(69, 10, 'Logout', 'User logged out.', 'Authentication', '2024-12-01 02:50:00'),
(70, 2, 'Login', 'User logged in successfully.', 'Authentication', '2024-12-01 09:50:13'),
(71, 10, 'Login', 'User logged in successfully.', 'Authentication', '2024-12-01 09:54:13'),
(72, 10, 'Approve', 'Approved piggery with ID 49.', 'Piggery Management', '2024-12-01 09:54:54'),
(73, 10, 'Approve', 'Approved piggery with ID 50.', 'Piggery Management', '2024-12-01 10:14:11'),
(74, 10, 'Approve', 'Approved piggery with ID 51.', 'Piggery Management', '2024-12-01 10:19:48'),
(75, 10, 'Approve', 'Approved piggery with ID 51.', 'Piggery Management', '2024-12-01 10:20:14'),
(76, 13, 'Login', 'User logged in successfully.', 'Authentication', '2024-12-01 10:29:58'),
(77, 2, 'Login', 'User logged in successfully.', 'Authentication', '2024-12-01 10:35:03'),
(78, 2, 'Login', 'User logged in successfully.', 'Authentication', '2024-12-01 13:44:35'),
(79, 13, 'Login', 'User logged in successfully.', 'Authentication', '2024-12-01 13:48:06'),
(80, 10, 'Login', 'User logged in successfully.', 'Authentication', '2024-12-01 13:52:12'),
(81, 10, 'Approve', 'Approved incident ID 24, Status: Approved.', 'Incident Management', '2024-12-01 13:53:51'),
(82, 10, 'Delete', 'Deleted incident ID 24.', 'Incident Management', '2024-12-01 13:54:07');

-- --------------------------------------------------------

--
-- Table structure for table `manage_health`
--

CREATE TABLE `manage_health` (
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
-- Table structure for table `manage_incidents`
--

CREATE TABLE `manage_incidents` (
  `incident_id` int(11) NOT NULL,
  `piggery_id` int(11) NOT NULL,
  `inspector_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `reported_by` varchar(255) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Approved','Resolved') DEFAULT 'Approved',
  `action_taken` text DEFAULT NULL,
  `resolved_by` varchar(255) DEFAULT NULL,
  `resolved_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manage_incidents`
--

INSERT INTO `manage_incidents` (`incident_id`, `piggery_id`, `inspector_id`, `report_date`, `reported_by`, `incident_type`, `description`, `status`, `action_taken`, `resolved_by`, `resolved_date`, `created_at`, `updated_at`) VALUES
(8, 2, 3, '2024-11-13', 'sample', 'sample', 'sample', 'Approved', 'hhhhh', 'bbb', '2024-11-16', '2024-11-13 14:16:50', '2024-11-16 14:55:49'),
(9, 2, 3, '2024-11-14', 'sdf', 'asdf', 'sdf', 'Approved', 'sdfga', 'ffff', '2024-11-16', '2024-11-14 06:10:56', '2024-11-16 14:56:04'),
(10, 2, 3, '2024-11-18', 'rrrr', 'eeee', 'rrrr', 'Approved', 'Kill', 'Dailyn', '2024-11-16', '2024-11-16 15:30:37', '2024-11-16 15:35:37'),
(11, 4, 3, '2024-11-16', 'wwww', 'www', 'www', 'Approved', 'gggggg', '3', '2024-11-17', '2024-11-16 15:43:46', '2024-11-17 15:56:21'),
(12, 2, 3, '2024-11-16', 'dddd', 'dddd', 'ddd', 'Approved', 'aaaaaaaaaaaaa', '3', '2024-11-17', '2024-11-16 15:48:17', '2024-11-16 18:07:23'),
(13, 4, 3, '2024-11-16', 'adf', 'sdf', 'sdfas', 'Approved', 'gggg', '3', '2024-11-17', '2024-11-16 15:58:24', '2024-11-16 18:06:25'),
(14, 4, 3, '2024-11-14', 'sample', 'sample3', 'sample4', 'Approved', 'ddsdff', '3', '2024-11-17', '2024-11-16 16:01:37', '2024-11-16 17:52:00'),
(15, 2, 3, '2024-11-18', 'rrrrrrrr', 'eeeeeeeeeee', 'wwwwwwwww', 'Approved', 'asdfasd', '3', '2024-11-17', '2024-11-16 17:10:12', '2024-11-16 17:42:19'),
(16, 2, 3, '2024-11-19', 'dddddddd', 'sssssssss', 'ddddddd', 'Approved', 'asdf', '3', '2024-11-17', '2024-11-16 17:21:34', '2024-11-16 17:36:38'),
(17, 2, 3, '2024-11-19', 'asdf', 'fasffa', 'ddddddd', 'Approved', 'tttttttttttttt', '2', '2024-11-17', '2024-11-16 17:43:13', '2024-11-16 17:46:34'),
(18, 2, 12, '2024-11-18', 'rrrrrrrr', 'eeeeeeeeeee', 'wwwwwwwww', 'Approved', 'vvvvvvvv', '12', '2024-11-18', '2024-11-16 18:11:33', '2024-11-17 16:25:09'),
(19, 4, 12, '2024-11-16', 'adf', 'sdf', 'sdfas', 'Approved', 'ddddddddd', '12', '2024-11-18', '2024-11-16 18:16:02', '2024-11-17 16:31:28'),
(20, 2, 3, '2024-11-18', 'Me', 'sample', 'sample', 'Approved', 'Medicine\r\n', '3', '2024-11-18', '2024-11-17 15:58:57', '2024-11-17 16:00:33'),
(21, 2, 12, '2024-11-19', 'Jhon', 'fever', '1 week', 'Approved', 'vaccine1', '12', '2024-11-18', '2024-11-17 16:10:40', '2024-11-17 16:32:04'),
(22, 2, 12, '2024-11-19', 'Jake', 'Fever', 'Last week', 'Approved', '', '', '0000-00-00', '2024-11-17 16:34:25', '2024-11-17 16:34:25'),
(23, 2, 12, '2024-11-18', 'Jemarose', 'fever', 'last week', 'Approved', 'applying vaccines', '12', '2024-11-18', '2024-11-18 01:50:16', '2024-11-18 01:51:18'),
(24, 2, 13, '2024-12-01', 'sample', 'sample', 'sample', 'Approved', 'Vacines', '13', '2024-12-01', '2024-12-01 09:18:16', '2024-12-01 13:50:30'),
(25, 2, 13, '0000-00-00', '', '', '', 'Approved', 'Vacines', '13', '2024-12-01', '2024-12-01 13:53:51', '2024-12-01 13:53:51');

-- --------------------------------------------------------

--
-- Table structure for table `manage_piggery`
--

CREATE TABLE `manage_piggery` (
  `manage_id` int(11) NOT NULL,
  `piggery_id` int(11) NOT NULL,
  `owner_first_name` varchar(50) NOT NULL,
  `owner_middle_name` varchar(50) DEFAULT NULL,
  `owner_last_name` varchar(50) NOT NULL,
  `piggery_name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `num_of_pigs` int(11) NOT NULL,
  `health_status` varchar(50) NOT NULL,
  `last_inspection_date` date DEFAULT NULL,
  `next_inspection_date` date NOT NULL,
  `compliance_status` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Approved',
  `notes` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manage_piggery`
--

INSERT INTO `manage_piggery` (`manage_id`, `piggery_id`, `owner_first_name`, `owner_middle_name`, `owner_last_name`, `piggery_name`, `location`, `num_of_pigs`, `health_status`, `last_inspection_date`, `next_inspection_date`, `compliance_status`, `status`, `notes`, `recommendations`, `observations`, `created_at`, `updated_at`) VALUES
(10, 2, 'Joe', 'Jay', 'Ani', 'qwert', 'Cebu', 12, 'Healthy', '2024-11-12', '2024-11-20', 'Compliant', 'Approved', NULL, 'asdfsdf', 'sadf', '2024-11-13 17:18:57', '2024-11-13 19:14:16'),
(11, 2, 'Joe', 'Jay', 'Ani', 'bbb', 'vvv', 5, 'Healthy', '2024-11-05', '0000-00-00', 'Compliant', 'Approved', NULL, 'asdffhgh', '', '2024-11-13 17:58:35', '2024-11-13 17:59:21'),
(12, 2, 'Joe', 'Jay', 'Ani', 'asdf', 'sdfas', 4, 'Healthy', '2024-11-14', '2024-11-25', 'Compliant', 'Approved', 'sfasdf', NULL, NULL, '2024-11-13 19:26:15', '2024-11-13 19:27:43'),
(13, 2, 'Joe', 'Jay', 'Ani', 'adf', 'asdf', 30, 'Healthy', '2024-11-13', '2024-12-06', 'Compliant', 'Approved', 'asdfsd', NULL, NULL, '2024-11-14 04:57:17', '2024-12-01 06:49:45'),
(14, 2, 'Joe', 'Jay', 'Ani', 'Laboy', 'Camansi', 10, 'Healthy', '2024-11-12', '2024-11-20', 'Compliant', 'Approved', 'Sample', NULL, NULL, '2024-11-14 06:57:37', '2024-11-17 15:56:01'),
(15, 2, 'Joe', 'Jay', 'Ani', 'Jemarose Piggery', 'Malitbog, Tomas Oppus Southern Leyte', 100, 'Healthy', '2024-11-15', '0000-00-00', 'Compliant', 'Approved', 'Sample', NULL, NULL, '2024-11-18 01:46:20', '2024-11-18 01:46:42'),
(16, 2, 'Loblee', 'Ani', 'Guzmana', 'Sample Piggery', 'Camansi Tomas Oppus Southern Leyte', 10, 'Healthy', '2024-11-01', '2024-12-06', 'Compliant', 'Approved', 'Sample', NULL, NULL, '2024-11-19 00:26:06', '2024-12-01 06:26:28'),
(17, 2, 'Loblee', 'Ani', 'Loblee', 'Laboy', 'Camansi', 45, 'Healthy', '2024-11-25', '0000-00-00', 'Compliant', 'Approved', 'Sample', NULL, NULL, '2024-12-01 06:20:14', '2024-12-01 06:30:20'),
(18, 2, 'Loblee', 'Ani', 'Loblee', 'sdf', 'asdf', 10, 'Healthy', '2024-10-24', '0000-00-00', 'Compliant', 'Approved', 'asdf', NULL, NULL, '2024-11-21 08:36:54', '2024-12-01 06:30:26'),
(19, 2, 'Lovely', 'Ani', 'Lovely', 'Sample Piggery', 'Camansi', 30, 'Healthy', '2024-11-20', '2024-12-18', 'Compliant', 'Approved', 'sample', NULL, NULL, '2024-12-01 06:45:14', '2024-12-01 09:15:27'),
(20, 2, 'Loblee', 'Ani', 'Loblee', 'Sample Piggery', 'Camansi', 20, 'Healthy', '2024-11-20', '0000-00-00', 'Compliant', 'Approved', 'Sample', NULL, NULL, '2024-12-01 09:11:29', '2024-12-01 09:16:50'),
(21, 2, 'Lovely', 'Ani', 'Lovely', 'Sample Piggery', 'Camansi', 23, 'Healthy', '2024-11-20', '0000-00-00', 'Compliant', 'Approved', 'sample', NULL, NULL, '2024-12-01 09:54:49', '2024-12-01 09:54:54'),
(22, 2, 'Lovely', 'Ani', 'Lovely', 'Sample Piggery', 'Camansi', 34, 'Healthy', '2024-11-28', '0000-00-00', 'Compliant', 'Approved', 'aefa', NULL, NULL, '2024-12-01 10:14:07', '2024-12-01 10:14:11'),
(23, 2, 'Lovely', 'Ani', 'Lovely', 'Sample Piggery', 'Camansi', 34, 'Healthy', '2024-11-28', '0000-00-00', 'Compliant', 'Approved', 'asdfasd', NULL, NULL, '2024-12-01 10:19:44', '2024-12-01 10:19:48'),
(24, 2, 'Lovely', 'Ani', 'Lovely', 'Sample Piggery', 'Camansi', 34, 'Healthy', '2024-11-28', '0000-00-00', 'Compliant', 'Approved', 'asdfasd', NULL, NULL, '2024-12-01 10:19:44', '2024-12-01 10:20:14');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `owner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `recipient_id`, `module`, `created_at`, `is_read`, `owner_id`) VALUES
(1, 'Piggery Approved', 'Approved piggery with ID 49.', 10, 'Piggery Management', '2024-12-01 09:54:54', 0, NULL),
(2, 'Piggery Approved', 'Approved piggery with ID 50.', 10, 'Piggery Management', '2024-12-01 10:14:11', 0, 10),
(3, 'Piggery Approved', 'Approved piggery with ID 51.', 10, 'Piggery Management', '2024-12-01 10:20:14', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `registered_users`
--

CREATE TABLE `registered_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','owner','inspector') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `profile_image` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_users`
--

INSERT INTO `registered_users` (`user_id`, `username`, `password`, `role`, `first_name`, `middle_name`, `last_name`, `email`, `status`, `profile_image`, `created_at`, `updated_at`) VALUES
(2, 'loblee', '$2y$10$ywMiZD/0Lcbmmeaexkf62.MogBG3KCsPWqxRlpt.fzR4ng.MawetW', 'owner', 'Lovely', 'Ani', 'Guzmana', 'loblee@gmail.com', 'active', '../Owner/image/86834c13-770a-4e7f-be44-26747b260b66.jpeg', '2024-11-10 14:43:35', '2024-12-01 09:12:58'),
(4, 'ako', '$2y$10$4cgScpgKaBsTVjCI9SfzIO0b6vhZ322PyTPS8AcbKguM52.Bv/kvW', 'owner', 'ako', 'ikaw', 'kami', 'ako@gmail.com', 'active', '../Owner/image/fd65e864-f10f-4b4e-9918-f630c87184a1.jpeg', '2024-11-10 17:35:49', '2024-11-19 00:28:24'),
(10, 'laboy', '$2y$10$Sh.YZcHtPTXbLlQwVhksPujd3IbXba4KHreQS.B5s784OohKu6wrW', 'admin', 'Lovely', 'Ani', 'guzmana', 'aniguzmana99@gmail.com', 'active', 'dog.jpeg', '2024-11-11 04:04:18', '2024-11-28 06:42:07'),
(13, 'juan', '$2y$10$EXi8W345WsAggggU0VQT7.GpgAjrJe5vXHZryyMj5Tvsv4J4NEuwi', 'inspector', 'Juan', 'Ani', 'Guzmana', 'juan@gmail.com', 'active', '../Inspector/image/86834c13-770a-4e7f-be44-26747b260b66.jpeg', '2024-11-17 15:40:37', '2024-12-01 06:28:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `manage_health`
--
ALTER TABLE `manage_health`
  ADD PRIMARY KEY (`monitoring_id`);

--
-- Indexes for table `manage_incidents`
--
ALTER TABLE `manage_incidents`
  ADD PRIMARY KEY (`incident_id`);

--
-- Indexes for table `manage_piggery`
--
ALTER TABLE `manage_piggery`
  ADD PRIMARY KEY (`manage_id`),
  ADD KEY `piggery_id` (`piggery_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `registered_users`
--
ALTER TABLE `registered_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `manage_health`
--
ALTER TABLE `manage_health`
  MODIFY `monitoring_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `manage_incidents`
--
ALTER TABLE `manage_incidents`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `manage_piggery`
--
ALTER TABLE `manage_piggery`
  MODIFY `manage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `registered_users`
--
ALTER TABLE `registered_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `registered_users` (`user_id`);

--
-- Constraints for table `manage_piggery`
--
ALTER TABLE `manage_piggery`
  ADD CONSTRAINT `manage_piggery_ibfk_1` FOREIGN KEY (`piggery_id`) REFERENCES `user_piggery`.`piggeries` (`piggery_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `registered_users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
