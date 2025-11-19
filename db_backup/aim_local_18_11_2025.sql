-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 07:59 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aim`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(5, '2025_02_03_205021_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `user_type_id` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `lat` varchar(255) DEFAULT NULL,
  `long` varchar(255) DEFAULT NULL,
  `day` varchar(255) DEFAULT NULL,
  `month` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `fbase` varchar(255) DEFAULT NULL,
  `refer_code` varchar(255) DEFAULT NULL,
  `my_refer_code` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `hotel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `otp_request_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `otp_last_request_date` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `address`, `user_type_id`, `role`, `ip_address`, `lat`, `long`, `day`, `month`, `year`, `fbase`, `refer_code`, `my_refer_code`, `email_verified_at`, `password`, `token`, `status`, `hotel_id`, `image_url`, `image_path`, `otp`, `otp_expires_at`, `otp_request_count`, `otp_last_request_date`, `remember_token`, `created_at`, `updated_at`) VALUES
(5, 'Ruhul Amin Sujon', 'sujon.egov@gmail.com', '01764401651', 'address', '2', 'user', '127.0.0.1', '', '', '08', 'Sep', '2025', 'firebase_token_123', 'REF2025', 'LKC792', NULL, '$2y$12$KRz8bk5ufAOZ3PhaCe1ABuhOrZY1GRyrRmP94njzV2sgMjyt7UsOS', NULL, 'Active', NULL, 'https://aisoft-hotel-project.s3.ap-south-1.amazonaws.com/profile/8431b285-5795-4cd2-a8ab-0d1d7b7974a4.png', 'profile/8431b285-5795-4cd2-a8ab-0d1d7b7974a4.png', NULL, NULL, 0, NULL, NULL, '2025-09-07 22:57:36', '2025-09-22 07:48:01'),
(6, 'Ruhul Amin Sujon', 'sujon.egov2@gmail.com', '01764401652', NULL, '3', 'owner', '127.0.0.1', '23.8103', '90.4125', '08', 'Sep', '2025', 'firebase_token_123', 'LKC792', 'KOZ399', NULL, '$2y$12$y5L50lY/wUzjuFlvC0SetuuinVbaBFFFxZBYmQN4Mdayhnbstm7uW', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2025-09-07 23:56:13', '2025-09-18 06:18:43'),
(7, 'Super Admin', 'superAdmin@gmail.com', '01712345678', NULL, '1', 'super_admin', '127.0.0.1', '0', '0', '08', 'Sep', '2025', NULL, NULL, NULL, NULL, '$2y$12$M9rzdizVT.bhfe7y7GBCoutKiv.SB/k1xPD8qodDhQI3EHYRZuSAy', 'P9XpYVCBKYfbMbgmGpDDCVzygBHhjnFmWa9d63PlkDNcSPCMv1MsQ3XfIjtN', 'Active', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2025-09-08 01:40:41', '2025-09-08 01:40:41'),
(8, 'Ruhul Amin', 'sujon.egov3@gmail.com', '01764401653', NULL, '3', 'owner', '127.0.0.1', '23.8103', '90.4125', '09', 'Sep', '2025', 'firebase_token_123', 'LKC792', 'OD1955', NULL, '$2y$12$J0F4KXrYJ12SONowGfZRP.w7037zluXDAggniD2z2HpXS9wBG2WiW', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2025-09-09 00:06:16', '2025-09-09 01:48:57'),
(10, 'Ruhul Amin', 'sujon.egov4@gmail.com', '01764401654', NULL, '2', 'user', '127.0.0.1', '23.8103', '90.4125', '10', 'Sep', '2025', 'firebase_token_123', 'LKC792', 'LZY534', NULL, '$2y$12$WbO8VTJznD7PK683mdOI1OXu/MImdfIAqo9MM8D4BvaU33Fd2tAoe', NULL, 'Active', NULL, 'https://aisoft-hotel-project.s3.ap-south-1.amazonaws.com/profile/cbf56f63-2dd8-4866-9478-05533ed7e4c4.jpg', 'profile/cbf56f63-2dd8-4866-9478-05533ed7e4c4.jpg', NULL, NULL, 0, NULL, NULL, '2025-09-10 10:22:08', '2025-09-10 10:22:08'),
(14, 'John Doe update', 'johnupdate@example.com', '1234567890', NULL, '4', 'receptionist', '127.0.0.1', '', '', '11', 'Sep', '2025', '', '', 'ZFJ977', NULL, '$2y$12$5Vki4Syeo.BuKLkFuX/D3eqsK4L9t6pS7tj/rqbTiNgFl1uO3dk56', NULL, 'Active', NULL, 'https://aisoft-hotel-project.s3.ap-south-1.amazonaws.com/profile/98c1fc7a-aac4-4132-ab01-8009d26ac388.jpg', 'profile/98c1fc7a-aac4-4132-ab01-8009d26ac388.jpg', NULL, NULL, 0, '2025-10-11 10:01:05', NULL, '2025-09-11 11:26:58', '2025-10-11 10:01:23'),
(15, 'Ruhul Amin', 'sujon.egov5@gmail.com', '01764401655', NULL, '3', 'owner', '127.0.0.1', '23.8103', '90.4125', '13', 'Sep', '2025', 'firebase_token_123', 'LKC792', 'HS8443', NULL, '$2y$12$NCDTCpfXlGW3CL2LPECm/ui3/Pr5BQA4HIKxXMgBKBQM.TcymwD6W', NULL, 'Active', 3, '', '', NULL, NULL, 0, NULL, NULL, '2025-09-13 12:10:07', '2025-09-13 12:11:20'),
(16, 'sujon', 'sujon@gmail.com', '01518376761', NULL, '4', 'receptionist', '127.0.0.1', '', '', '14', 'Sep', '2025', '', '', 'RBJ916', NULL, '$2y$12$JmfnM3RhIuOr4seWmSq.e.Q4HPyQpwxnhQSBMnVCykf1crKknPEhy', NULL, 'Active', 1, '', '', '$2y$12$7X/8MNNr1ifOhC25zzfNg.LBuP.yP.qq7.kOBY7olu28Ans/vk8ee', '2025-10-11 12:14:51', 3, '2025-10-11 12:04:51', NULL, '2025-09-14 05:41:53', '2025-10-11 12:04:51'),
(18, 'Ruhul Amin', 'sujon.egov6@gmail.com', '01764401656', NULL, '3', 'owner', '127.0.0.1', '23.8103', '90.4125', '22', 'Sep', '2025', 'firebase_token_123', 'LKC792', 'RD9293', NULL, '$2y$12$XwissJaDCRqpP8.7uayLGeBg65Cx8CRd6.fMotCOFPzQeQHQhzoYu', NULL, 'Active', NULL, '', '', NULL, NULL, 0, NULL, NULL, '2025-09-22 05:41:03', '2025-09-22 05:41:03'),
(19, 'Ruhul Amin', 'sujon.egov7@gmail.com', '01764401657', NULL, '3', 'owner', '127.0.0.1', '23.8103', '90.4125', '23', 'Sep', '2025', 'firebase_token_123', 'LKC792', 'PGS538', NULL, '$2y$12$IB1oxrAWKhVEmKlnT.Lc4OplorNsCTqsIEmeHGijGMpNqNfq9k5gG', NULL, 'Active', 6, '', '', NULL, NULL, 0, NULL, NULL, '2025-09-23 12:10:28', '2025-09-23 12:10:28'),
(20, 'Ruhul Amin', 'sujon.egov9@gmail.com', '01764401659', NULL, '2', 'owner', '127.0.0.1', '23.8103', '90.4125', '18', 'Nov', '2025', 'firebase_token_123', 'LKC792', 'VCQ915', NULL, '$2y$12$TspwNvuo3fUH25KJ5brRUuBf0qDpblCkWuml8PdfDw0gD1AqffYUG', NULL, 'Active', NULL, '', '', NULL, NULL, 0, NULL, NULL, '2025-11-18 06:39:09', '2025-11-18 06:39:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
