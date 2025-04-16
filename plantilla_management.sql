-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 05:30 AM
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
-- Database: `plantilla_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `id` int(11) NOT NULL,
  `id_no` varchar(50) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `extname` varchar(20) DEFAULT NULL,
  `mi` varchar(10) DEFAULT NULL,
  `sex` enum('Male','Female','Others') DEFAULT NULL,
  `position_title` varchar(255) DEFAULT NULL,
  `item_number` varchar(50) DEFAULT NULL,
  `techcode` varchar(50) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `appointment_status` enum('PERMANENT','TEMPORARY') DEFAULT NULL,
  `sg` int(11) DEFAULT NULL,
  `step` int(11) DEFAULT NULL,
  `monthly_salary` decimal(12,2) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_orig_appt` date DEFAULT NULL,
  `date_govt_srvc` date DEFAULT NULL,
  `date_last_promotion` date DEFAULT NULL,
  `date_last_increment` date DEFAULT NULL,
  `date_of_longevity` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_records`
--

CREATE TABLE `applicant_records` (
  `id` int(11) NOT NULL,
  `id_no` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `ext_name` varchar(20) DEFAULT NULL,
  `mi` varchar(10) DEFAULT NULL,
  `sex` enum('Male','Female','Others') DEFAULT NULL,
  `position_title` varchar(255) DEFAULT NULL,
  `item_number` varchar(50) DEFAULT NULL,
  `tech_code` varchar(50) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `appointment_status` enum('Temporary','Permanent') DEFAULT NULL,
  `sg` int(11) DEFAULT NULL,
  `step` int(11) DEFAULT NULL,
  `monthly_salary` decimal(12,2) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_orig_appt` date DEFAULT NULL,
  `date_govt_srvc` date DEFAULT NULL,
  `date_last_promotion` date DEFAULT NULL,
  `date_last_increment` date DEFAULT NULL,
  `date_of_longevity` date DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `file_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `id` int(11) NOT NULL,
  `code` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `division_definitions`
--

CREATE TABLE `division_definitions` (
  `id` int(11) NOT NULL,
  `division_name` varchar(255) NOT NULL,
  `division_code` varchar(10) NOT NULL,
  `division_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `division_definitions`
--

INSERT INTO `division_definitions` (`id`, `division_name`, `division_code`, `division_order`) VALUES
(1, 'Office of the Administrator', 'OA', 1),
(2, 'Administrative Division', 'AD', 2),
(3, 'Human Resources Management and Development Section', 'HRMDS', 3),
(4, 'Records Management Section', 'RMS', 4),
(5, 'Procurement, Property and General Services Section', 'PPGSS', 5),
(6, 'Financial, Planning and Management Division', 'FPMD', 6),
(7, 'Accounting Section', 'AS', 7),
(8, 'Budget and Planning Section', 'BPS', 8),
(9, 'Management Services Section', 'MSS', 9),
(10, 'Engineering and Technical Services Division', 'ETSD', 10),
(11, 'Meteorological Equipment and Telecommunications Technology Services Section', 'METTSS', 11),
(12, 'Meteorological Guides and Standards Section', 'MGSS', 12),
(13, 'Mechanical, Electrical and Infrastructure Engineering Section', 'MEIES', 13),
(14, 'Weather Division', 'WD', 14),
(15, 'Weather Forecasting Section', 'WFS', 15),
(16, 'Meteorological Data and Information Exchange Section', 'MDIES', 16),
(17, 'Techniques Application and Meteorological Satellite Section', 'TAMSS', 17),
(18, 'Aeronautical Meteorological Satellite Section', 'AMSS', 18),
(19, 'Marine Meteorological Services Section', 'MMSS', 19),
(20, 'Hydro-Meteorological Division', 'HMD', 20),
(21, 'Hydrometeorological Data Applications Sections', 'HDAS', 21),
(22, 'Flood Forecasting and Warning Section', 'FFWS', 22),
(23, 'Hydrometeorological Telemetry Section', 'HTS', 23),
(24, 'Climatology and Agrometeorology Division', 'CAD', 24),
(25, 'Climate Monitoring and Prediction Section', 'CMPS', 25),
(26, 'Farm Weather Services Section', 'FWSS', 26),
(27, 'Impact Assessment and Applications Section', 'IAAS', 27),
(28, 'Climate and Agrometeorology Data Section', 'CADS', 28),
(29, 'Research and Development and Training Division', 'RDTD', 29),
(30, 'Astronomy and Space Sciences Section', 'ASSS', 30),
(31, 'Climate and Agrometeorology Research and Development Section', 'CARDS', 31),
(32, 'Hydrometeorology, Tropical Meteorology and Instrument Research and Development', 'HTMIRD', 32),
(33, 'Numerical Modeling Section', 'NMS', 33),
(34, 'Training and Public Information Section', 'TPIS', 34),
(35, 'Northern Luzon PAGASA Regional Services Division', 'NLPRSD', 35),
(36, 'Agno Flood Forecasting and Warning System', 'AFFWS', 36),
(37, 'Pampanga Flood Forecasting and Warning System', 'PFFWS', 37),
(38, 'Southern Luzon PAGASA Regional Services Division', 'SLPRSD', 38),
(39, 'Bicol Flood Forecasting and Warning System', 'BFFWS', 39),
(40, 'Visayas PAGASA Regional Services Division', 'VPRSD', 40),
(41, 'Northern Mindanao PAGASA Regional Services Division', 'NMPRSD', 41),
(42, 'Southern Mindanao PAGASA Regional Services Division', 'SMPRSD', 42),
(43, 'Field Stations', 'FS', 43);

-- --------------------------------------------------------

--
-- Table structure for table `file_history`
--

CREATE TABLE `file_history` (
  `id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `action` enum('Created','Edited','Deleted') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_history`
--

INSERT INTO `file_history` (`id`, `file_id`, `action`, `user_id`, `timestamp`) VALUES
(5, 5, 'Created', 13, '2025-04-16 03:02:45');

-- --------------------------------------------------------

--
-- Table structure for table `file_versions`
--

CREATE TABLE `file_versions` (
  `id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL,
  `attempt_time` date NOT NULL,
  `success` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `attempts`, `attempt_time`, `success`) VALUES
(1, 'admin', 0, '0000-00-00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `monthly_files`
--

CREATE TABLE `monthly_files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 7, 'ce93b3de2f63d5553e2e25c1ee603f15', '2025-04-11 23:35:52', '2025-04-11 20:35:52'),
(2, 7, '543ebeb92c22b71e4d06fa88bff89d66', '2025-04-11 23:36:07', '2025-04-11 20:36:07'),
(3, 7, '589582881e1c2aed27ba599371afde31', '2025-04-11 23:36:08', '2025-04-11 20:36:08'),
(4, 7, '66f3991581c1ec4cd206cc46900f78eb', '2025-04-11 23:36:49', '2025-04-11 20:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `raw_data`
--

CREATE TABLE `raw_data` (
  `id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `is_latest` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `permissions`) VALUES
(1, 'Admin', 'Test', '[\"read\",\"write\",\"delete\"]');

-- --------------------------------------------------------

--
-- Table structure for table `status_tracking`
--

CREATE TABLE `status_tracking` (
  `id` int(11) NOT NULL,
  `plantilla_no` varchar(50) DEFAULT NULL,
  `status` enum('On-Process','On-Hold','Not Yet for Filing') NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `month_year` varchar(7) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `last_modified` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `status` enum('processing','completed','error') DEFAULT 'processing',
  `error_message` text DEFAULT NULL,
  `division_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uploaded_files`
--

INSERT INTO `uploaded_files` (`id`, `filename`, `original_filename`, `file_path`, `upload_date`, `month_year`, `uploaded_by`, `modified_by`, `last_modified`, `status`, `error_message`, `division_code`) VALUES
(5, '67ff1dd513847_test db.xlsx', 'test db.xlsx', '../uploads/67ff1dd513847_test db.xlsx', '2025-04-16 03:02:45', '2025-04', 13, NULL, '2025-04-16 03:10:22', 'completed', NULL, 'AD');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `email_verified` int(11) NOT NULL,
  `verification_token` varchar(64) NOT NULL,
  `verification_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `reset_token` varchar(64) NOT NULL,
  `reset_expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `email_verified`, `verification_token`, `verification_expires`, `last_login`, `active`, `reset_token`, `reset_expires`, `created_at`) VALUES
(5, 'FrancisPogi', 'francistengteng10@gmail.com', '$2y$10$9Z9kl3B7LPDxq9amNPcMBeHIjnFgzHz8h03gEoBdx56jBZNRTO.V2', 'admin', 1, '', '2025-04-12 21:52:53', '2025-04-16 10:54:52', 1, '', '0000-00-00 00:00:00', '2025-04-11 19:52:53'),
(13, 'Francis Pogi', 'cabusasfg779@gmail.com', '$2y$10$LtvBgw/qIdv3BfeX55vI9e7R8DnMHmdokgIjyZr.7okBHF3gY5sRq', 'user', 1, '', '2025-04-12 22:52:53', '2025-04-16 10:55:07', 0, '', '0000-00-00 00:00:00', '2025-04-11 20:52:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_applicants_fullname` (`fullname`),
  ADD KEY `idx_applicants_id_no` (`id_no`);

--
-- Indexes for table `applicant_records`
--
ALTER TABLE `applicant_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `division_id` (`division_id`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `division_definitions`
--
ALTER TABLE `division_definitions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `file_history`
--
ALTER TABLE `file_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `file_versions`
--
ALTER TABLE `file_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monthly_files`
--
ALTER TABLE `monthly_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `raw_data`
--
ALTER TABLE `raw_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `status_tracking`
--
ALTER TABLE `status_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `modified_by` (`modified_by`),
  ADD KEY `idx_month_year` (`month_year`);

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
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applicant_records`
--
ALTER TABLE `applicant_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `division_definitions`
--
ALTER TABLE `division_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `file_history`
--
ALTER TABLE `file_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `file_versions`
--
ALTER TABLE `file_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `monthly_files`
--
ALTER TABLE `monthly_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `raw_data`
--
ALTER TABLE `raw_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `status_tracking`
--
ALTER TABLE `status_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applicant_records`
--
ALTER TABLE `applicant_records`
  ADD CONSTRAINT `applicant_records_ibfk_1` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `applicant_records_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `monthly_files` (`id`);

--
-- Constraints for table `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `divisions` (`id`);

--
-- Constraints for table `file_history`
--
ALTER TABLE `file_history`
  ADD CONSTRAINT `file_history_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `uploaded_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `file_versions`
--
ALTER TABLE `file_versions`
  ADD CONSTRAINT `file_versions_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `uploaded_files` (`id`),
  ADD CONSTRAINT `file_versions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `monthly_files`
--
ALTER TABLE `monthly_files`
  ADD CONSTRAINT `monthly_files_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `status_tracking`
--
ALTER TABLE `status_tracking`
  ADD CONSTRAINT `status_tracking_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `uploaded_files_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `uploaded_files_ibfk_2` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
