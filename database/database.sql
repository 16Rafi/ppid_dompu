-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2026 at 03:13 AM
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
-- Database: `db_ppid_dompu`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_audit_log`
--

CREATE TABLE `admin_audit_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_username` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_audit_log`
--

INSERT INTO `admin_audit_log` (`id`, `admin_id`, `admin_username`, `action`, `target_type`, `target_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-01 04:18:22'),
(2, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-01 05:01:04'),
(3, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-01 05:15:47'),
(4, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-01 06:39:31'),
(5, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-01 06:39:37'),
(6, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-01 06:41:14'),
(7, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-01 06:46:48'),
(8, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 02:36:43'),
(9, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 03:24:42'),
(10, 6, 'rafi', 'UPDATE_PERMOHONAN_STATUS', 'permohonan_informasi', 2, 'Update status to: selesai', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 03:24:58'),
(11, 6, 'rafi', 'EXPORT_LAPORAN', 'permohonan_informasi', NULL, 'Export CSV laporan permohonan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 03:35:07'),
(12, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 01:46:38'),
(13, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 04:04:06'),
(14, 6, 'rafi', 'UPDATE_NEWS', 'news', 10, 'Updated news: HTML', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 11:49:33'),
(15, 6, 'rafi', 'UPDATE_PERMOHONAN_STATUS', 'permohonan_informasi', 1, 'Update status to: diproses', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 11:50:27'),
(16, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:23:57'),
(17, 6, 'rafi', 'REORDER_MENU', 'menu', NULL, 'Reordered menus', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 17:07:12'),
(18, 6, 'rafi', 'DELETE_MENU', 'menu', 18, 'Deleted menu ID: 18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 17:07:36'),
(19, 6, 'rafi', 'REORDER_MENU', 'menu', NULL, 'Reordered menus', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 17:08:27'),
(20, 6, 'rafi', 'REORDER_MENU', 'menu', NULL, 'Reordered menus', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 17:08:41'),
(21, 6, 'rafi', 'CREATE_PAGE', 'page', 1, 'Created page: Tes (slug: tes)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 19:20:42'),
(22, 6, 'rafi', 'UPDATE_PAGE', 'page', 1, 'Updated page: Tes (ID: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 19:20:53'),
(23, 6, 'rafi', 'DELETE_MENU', 'menu', 19, 'Deleted menu ID: 19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 20:31:00'),
(24, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 22:04:35'),
(25, 6, 'rafi', 'REORDER_MENU', 'menu', NULL, 'Reordered menus', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 22:08:58'),
(26, 6, 'rafi', 'UPDATE_MENU', 'menu', 17, 'Updated menu: PROSEDUR (ID: 17)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:21:35'),
(27, 6, 'rafi', 'LOGIN', NULL, NULL, 'Admin login successful for user: rafi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 14:06:42'),
(28, 6, 'rafi', 'CREATE_PAGE', 'page', 2, 'Created page: NEW (slug: new)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 15:19:10'),
(29, 6, 'rafi', 'DELETE_PAGE', 'page', 1, 'Deleted page ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 15:19:13'),
(30, 6, 'rafi', 'UPDATE_MENU', 'menu', 12, 'Updated menu: DIP (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 15:19:21'),
(31, 6, 'rafi', 'UPDATE_PAGE', 'page', 2, 'Updated page: NEW (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 18:43:34'),
(32, 6, 'rafi', 'UPDATE_PAGE', 'page', 2, 'Updated page: NEW (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 18:43:39'),
(33, 6, 'rafi', 'UPDATE_PAGE', 'page', 2, 'Updated page: NEW (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 18:44:07'),
(34, 6, 'rafi', 'CREATE_PAGE', 'page', 3, 'Created page: a (slug: a)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 18:44:15'),
(35, 6, 'rafi', 'DELETE_PAGE', 'page', 3, 'Deleted page ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 18:44:19'),
(36, 6, 'rafi', 'DELETE_NEWS', 'news', 10, 'Deleted news with ID: 10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 18:47:12'),
(37, 6, 'rafi', 'DELETE_NEWS', 'news', 9, 'Deleted news with ID: 9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 18:47:16'),
(38, 6, 'rafi', 'EXPORT_LAPORAN', 'keberatan', NULL, 'Export CSV laporan keberatan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 19:31:53'),
(39, 6, 'rafi', 'EXPORT_LAPORAN', 'permohonan_informasi', NULL, 'Export CSV laporan permohonan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 19:36:36'),
(40, 6, 'rafi', 'UPDATE_MENU', 'menu', 12, 'Updated menu: DIP (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 19:42:11'),
(41, 6, 'rafi', 'UPDATE_MENU', 'menu', 12, 'Updated menu: DIP (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 19:42:15'),
(42, 6, 'rafi', 'CREATE_PAGE', 'page', 4, 'Created page: b (slug: b)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 19:43:18'),
(43, 6, 'rafi', 'UPDATE_MENU', 'menu', 12, 'Updated menu: DIP (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 19:43:24'),
(44, 6, 'rafi', 'UPDATE_MENU', 'menu', 12, 'Updated menu: DIP (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 19:46:32'),
(45, 6, 'rafi', 'ADD_NEWS', 'news', 11, 'Added new news: tes', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 19:59:45'),
(46, 6, 'rafi', 'UPDATE_MENU', 'menu', 26, 'Updated menu: KONTAK (ID: 26)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 23:21:29'),
(47, 6, 'rafi', 'CREATE_DIP', 'daftar_informasi_publik', 1, 'Created DIP: add', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 00:51:21'),
(48, 6, 'rafi', 'DELETE_DIP', 'daftar_informasi_publik', 1, 'Deleted DIP ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 00:52:10'),
(49, 6, 'rafi', 'CREATE_MENU', 'menu', 35, 'Created menu: Daftar Keberatan (ID: 35)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:12:48'),
(50, 6, 'rafi', 'UPDATE_MENU', 'menu', 35, 'Updated menu: Daftar Keberatan (ID: 35)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:13:46'),
(51, 6, 'rafi', 'UPDATE_KEBERATAN_STATUS', 'keberatan', 1, 'Update status to: ditolak', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:19:33'),
(52, 6, 'rafi', 'UPDATE_KEBERATAN_STATUS', 'keberatan', 1, 'Update status to: selesai', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:19:40'),
(53, 6, 'rafi', 'EXPORT_LAPORAN', 'permohonan_informasi', NULL, 'Export CSV laporan permohonan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:21:05'),
(54, 6, 'rafi', 'EXPORT_LAPORAN', 'keberatan', NULL, 'Export CSV laporan keberatan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:23:15'),
(55, 6, 'rafi', 'EXPORT_LAPORAN', 'permohonan_informasi', NULL, 'Export CSV laporan permohonan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:23:50'),
(56, 6, 'rafi', 'EXPORT_LAPORAN', 'keberatan', NULL, 'Export CSV laporan keberatan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:24:55'),
(57, 6, 'rafi', 'EXPORT_LAPORAN', 'keberatan', NULL, 'Export CSV laporan keberatan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:25:22');

-- --------------------------------------------------------

--
-- Table structure for table `daftar_informasi_publik`
--

CREATE TABLE `daftar_informasi_publik` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `ringkasan` text DEFAULT NULL,
  `kategori` enum('berkala','serta-merta','setiap-saat') NOT NULL,
  `tahun` year(4) DEFAULT NULL,
  `file_id` int(11) DEFAULT NULL,
  `status_publikasi` enum('draft','published') DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `external_websites`
--

CREATE TABLE `external_websites` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `external_websites`
--

INSERT INTO `external_websites` (`id`, `name`, `url`, `description`, `image`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
(7, 'Website Kabupaten Dompu', 'https://dompukab.go.id', 'Portal resmi Kabupaten Dompu', 'img/website-kabupaten-dompu.jpg', 1, 1, '2026-02-08 23:45:15', '2026-02-08 23:45:15'),
(8, 'SP4N LAPOR!', 'https://www.lapor.go.id', 'Layanan aspirasi dan pengaduan online', 'img/sp4n-lapor.jpg', 2, 1, '2026-02-08 23:45:15', '2026-02-08 23:45:15'),
(9, 'Satu Data Dompu', 'https://satudata.dompukab.go.id', 'Portal data terpadu Kabupaten Dompu', 'img/satu-data-dompu.jpg', 3, 1, '2026-02-08 23:45:15', '2026-02-08 23:45:15'),
(10, 'SIRUP', 'https://sirup.lkpp.go.id', 'Sistem Informasi Rencana Umum Pengadaan', 'img/sirup.jpg', 4, 1, '2026-02-08 23:45:15', '2026-02-08 23:45:15'),
(11, 'BPS Dompu', 'https://dompukab.bps.go.id', 'Badan Pusat Statistik Kabupaten Dompu', 'img/bps-dompu.jpg', 5, 1, '2026-02-08 23:45:15', '2026-02-08 23:45:15'),
(12, 'E-SAKIP', 'https://esakip.dompukab.go.id', 'Sistem Akuntabilitas Kinerja Instansi Pemerintah', 'img/e-sakip.jpg', 6, 1, '2026-02-08 23:45:15', '2026-02-08 23:45:15');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `tipe_file` varchar(50) DEFAULT NULL,
  `ukuran` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `nama_file`, `path`, `tipe_file`, `ukuran`, `uploaded_by`, `uploaded_at`) VALUES
(1, 'White Tosca Orange Simple Professional Annual Report.pdf', 'uploads/White_Tosca_Orange_Simple_Professional_Annual_Report_2026-02-09_01-51-21_347d8822.pdf', 'pdf', 466544, 6, '2026-02-09 00:51:21');

-- --------------------------------------------------------

--
-- Table structure for table `informasi_lampiran`
--

CREATE TABLE `informasi_lampiran` (
  `id` int(11) NOT NULL,
  `informasi_id` int(11) NOT NULL,
  `tipe` enum('file','link') NOT NULL,
  `nama` varchar(255) NOT NULL,
  `path_or_url` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jawaban_permohonan`
--

CREATE TABLE `jawaban_permohonan` (
  `id` int(11) NOT NULL,
  `permohonan_id` int(11) NOT NULL,
  `isi_jawaban` text NOT NULL,
  `file_lampiran` varchar(255) DEFAULT NULL,
  `dijawab_oleh` int(11) DEFAULT NULL,
  `tanggal_jawab` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keberatan`
--

CREATE TABLE `keberatan` (
  `id` int(11) NOT NULL,
  `nomor_registrasi` varchar(50) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `identitas` varchar(50) NOT NULL,
  `no_identitas` varchar(100) NOT NULL,
  `scan_identitas` varchar(255) NOT NULL,
  `informasi_diminta` text NOT NULL,
  `alasan_pengajuan` text NOT NULL,
  `keterangan_tambahan` text DEFAULT NULL,
  `status` enum('diajukan','diproses','selesai','ditolak') DEFAULT 'diajukan',
  `putusan` text DEFAULT NULL,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_putusan` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keberatan`
--

INSERT INTO `keberatan` (`id`, `nomor_registrasi`, `nama_lengkap`, `identitas`, `no_identitas`, `scan_identitas`, `informasi_diminta`, `alasan_pengajuan`, `keterangan_tambahan`, `status`, `putusan`, `tanggal_pengajuan`, `tanggal_putusan`) VALUES
(1, 'KBR-20260209-0001', 'rafi', 'KTP', '123456', 'uploads/talk_2026-02-09_02-14-47_7df3a3d0.png', 'KUHP', 'Permohonan Informasi Di Tolak', 'sudah lewat 13 hari', 'selesai', NULL, '2026-02-09 01:14:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `log_status_keberatan`
--

CREATE TABLE `log_status_keberatan` (
  `id` int(11) NOT NULL,
  `keberatan_id` int(11) NOT NULL,
  `status_lama` varchar(30) DEFAULT NULL,
  `status_baru` varchar(30) NOT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_status_keberatan`
--

INSERT INTO `log_status_keberatan` (`id`, `keberatan_id`, `status_lama`, `status_baru`, `diubah_oleh`, `waktu`) VALUES
(1, 1, NULL, 'diajukan', NULL, '2026-02-09 01:14:47'),
(2, 1, 'diajukan', 'ditolak', 6, '2026-02-09 01:19:33'),
(3, 1, 'ditolak', 'selesai', 6, '2026-02-09 01:19:40');

-- --------------------------------------------------------

--
-- Table structure for table `log_status_permohonan`
--

CREATE TABLE `log_status_permohonan` (
  `id` int(11) NOT NULL,
  `permohonan_id` int(11) NOT NULL,
  `status_lama` varchar(30) DEFAULT NULL,
  `status_baru` varchar(30) NOT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_status_permohonan`
--

INSERT INTO `log_status_permohonan` (`id`, `permohonan_id`, `status_lama`, `status_baru`, `diubah_oleh`, `waktu`) VALUES
(1, 1, NULL, 'diajukan', NULL, '2026-02-02 02:26:47'),
(2, 2, NULL, 'diajukan', NULL, '2026-02-02 02:36:14'),
(3, 2, 'diajukan', 'selesai', 6, '2026-02-02 03:24:58'),
(4, 1, 'diajukan', 'diproses', 6, '2026-02-03 11:50:27'),
(5, 3, NULL, 'diajukan', NULL, '2026-02-03 11:51:23'),
(6, 4, NULL, 'diajukan', NULL, '2026-02-06 00:57:39'),
(7, 5, NULL, 'diajukan', NULL, '2026-02-06 00:59:11'),
(8, 6, NULL, 'diajukan', NULL, '2026-02-06 01:01:09'),
(9, 7, NULL, 'diajukan', NULL, '2026-02-06 01:07:11'),
(10, 8, NULL, 'diajukan', NULL, '2026-02-06 01:18:58'),
(11, 9, NULL, 'diajukan', NULL, '2026-02-06 01:25:20'),
(12, 10, NULL, 'diajukan', NULL, '2026-02-06 01:25:35'),
(13, 11, NULL, 'diajukan', NULL, '2026-02-06 01:29:33'),
(14, 12, NULL, 'diajukan', NULL, '2026-02-06 01:29:54'),
(15, 13, NULL, 'diajukan', NULL, '2026-02-06 01:30:57'),
(16, 14, NULL, 'diajukan', NULL, '2026-02-06 01:36:43'),
(17, 15, NULL, 'diajukan', NULL, '2026-02-06 01:36:49'),
(18, 16, NULL, 'diajukan', NULL, '2026-02-06 01:38:14');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `order_index` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `url`, `parent_id`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Profil', '#', 0, 3, 1, '2026-01-29 07:55:40', '2026-02-05 22:08:58'),
(3, 'Layanan', '#', 0, 5, 1, '2026-01-29 07:55:40', '2026-02-05 22:08:58'),
(4, 'Informasi Publik', '#', 0, 11, 1, '2026-01-29 07:55:40', '2026-02-05 22:08:58'),
(6, 'Berita', 'pages/berita.php', 3, 6, 1, '2026-01-29 07:55:40', '2026-02-05 22:08:58'),
(7, 'Permohonan Informasi', 'pages/permohonan-informasi.php', 3, 7, 1, '2026-01-29 07:55:40', '2026-02-05 22:08:58'),
(8, 'Pengajuan Keberatan', 'pages/pengajuan-keberatan.php', 3, 8, 1, '2026-01-29 07:55:40', '2026-02-05 22:08:58'),
(9, 'Pengaduan Nomor', 'pages/pengaduan-nomor.php', 3, 9, 1, '2026-01-29 07:55:40', '2026-02-05 22:08:58'),
(10, 'Pengaduan Konten', 'pages/pengaduan-konten.php', 3, 10, 1, '2026-01-29 07:55:40', '2026-02-05 22:08:58'),
(12, 'DIP', '/pages/dip.php', 0, 2, 1, '2026-01-29 08:01:15', '2026-02-08 19:46:32'),
(14, 'Visi Misi', 'pages/profil.php', 29, 1, 1, '2026-01-29 08:01:15', '2026-01-29 17:13:08'),
(15, 'Struktur Organisasi', 'pages/struktur.php', 29, 2, 1, '2026-01-29 08:01:15', '2026-01-29 17:13:02'),
(16, 'Tugas dan Fungsi', 'pages/tugas.php', 29, 3, 1, '2026-01-29 08:01:15', '2026-01-29 17:13:17'),
(17, 'PROSEDUR', '/pages/tes.php', 0, 13, 1, '2026-01-29 08:01:15', '2026-02-06 02:21:35'),
(20, 'KEUANGAN', 'pages/keuangan.php', 0, 14, 1, '2026-01-29 08:01:15', '2026-02-05 22:08:58'),
(21, 'LHKPN & LHKASN', 'pages/lhkpn.php', 0, 15, 1, '2026-01-29 08:01:15', '2026-02-05 22:08:58'),
(22, 'UNDUH', 'pages/unduh.php', 0, 16, 1, '2026-01-29 08:01:15', '2026-02-05 22:08:58'),
(23, 'SKM', 'pages/skm.php', 0, 17, 1, '2026-01-29 08:01:15', '2026-02-05 22:08:58'),
(24, 'FKP', 'pages/fkp.php', 0, 18, 1, '2026-01-29 08:01:15', '2026-02-05 22:08:58'),
(25, 'SP', 'pages/sp.php', 0, 19, 1, '2026-01-29 08:01:15', '2026-02-05 22:08:58'),
(26, 'KONTAK', 'pages/template.php?slug=new', 0, 20, 1, '2026-01-29 08:01:15', '2026-02-08 23:21:29'),
(28, 'BERANDA', 'index.php', 0, 1, 1, '2026-01-29 08:03:39', '2026-02-05 22:08:58'),
(29, 'PROFIL PPID', '#', 2, 4, 1, '2026-01-29 08:03:39', '2026-02-05 22:08:58'),
(33, 'Daftar Permohonan', 'pages/daftar-permohonan-publik.php', 4, 12, 1, '2026-02-02 02:23:15', '2026-02-05 22:08:58'),
(35, 'Daftar Keberatan', '/pages/daftar-keberatan-publik.php', 4, 13, 1, '2026-02-09 01:12:48', '2026-02-09 01:13:46');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `kategori`, `slug`, `excerpt`, `content`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Selamat Datang di PPID Kabupaten Dompu', '', 'selamat-datang-di-ppid-kabupaten-dompu', 'Website resmi Pejabat Pengelola Informasi dan Dokumentasi Kabupaten Dompu telah diluncurkan untuk memberikan layanan informasi publik yang lebih transparan dan akuntabel.', '<p>Website resmi PPID Kabupaten Dompu telah diluncurkan untuk memberikan layanan informasi publik yang lebih transparan dan akuntabel.</p><p>Melalui website ini, masyarakat dapat mengakses berbagai informasi terkait pemerintahan daerah Kabupaten Dompu.</p><p>PPID berkomitmen untuk memberikan pelayanan informasi yang cepat, tepat, dan akurat kepada seluruh masyarakat.</p>', NULL, 'published', '2026-01-29 07:55:40', '2026-01-29 07:55:40'),
(2, 'Hari Pertama Bertugas Kadis Kominfo Dompu Sampaikan Terima Kasih', 'Kegiatan Kepala', 'hari-pertama-bertugas-kadis-kominfo-dompu-sampaikan-terima-kasih', 'Dompu – Hari pertama sejak dilantik sebagai Kepala Dinas Komunikasi dan Informatika Kabupaten Dompu, Muhammad Nursalam, ST. memimpin apel pagi bersama seluruh jajaran di Kantor Dinas Kominfo Kabupat...', '<p>Dompu – Hari pertama sejak dilantik sebagai Kepala Dinas Komunikasi dan Informatika Kabupaten Dompu, Muhammad Nursalam, ST. memimpin apel pagi bersama seluruh jajaran di Kantor Dinas Kominfo Kabupaten Dompu, Selasa (13/1/2026).</p><p>Dalam kesempatan tersebut, Muhammad Nursalam, ST menyampaikan ucapan terima kasih dan apresiasi kepada seluruh jajaran Dinas Kominfo Kabupaten Dompu atas kehadiran, sambutan, serta dukungan yang telah diberikan selama ini.</p>', 'https://ppid.dompukab.go.id/wp-content/uploads/2026/01/a14.jpg', 'published', '2026-01-14 00:00:00', '2026-01-31 11:12:03'),
(3, 'Kepala Dinas Kominfo Dompu Resmi Dilantik', '', 'kepala-dinas-kominfo-dompu-resmi-dilantik', 'Dompu — Kepala Dinas Komunikasi dan Informatika (Kominfo) Kabupaten Dompu, Yani Hartono, SP. resmi berpindah tugas ke Dinas Sosial Kabupaten Dompu setelah dilantik langsung oleh Bupati Dompu.', '<p>Dompu — Kepala Dinas Komunikasi dan Informatika (Kominfo) Kabupaten Dompu, Yani Hartono, SP. resmi berpindah tugas ke Dinas Sosial Kabupaten Dompu setelah dilantik langsung oleh Bupati Dompu, Bambang Firdaus, SE. pada Senin pagi, 12 Januari 2026.</p><p>Pelantikan tersebut dilaksanakan di Aula Pendopo Bupati Dompu bersama sejumlah pejabat lainnya sebagai bagian dari penataan dan penyegaran organisasi perangkat daerah.</p>', 'https://ppid.dompukab.go.id/wp-content/uploads/2026/01/b1.jpg', 'published', '2026-01-14 00:00:00', '2026-01-29 07:55:40'),
(4, 'Akselerasi Kinerja Daerah', 'berita', 'akselerasi-kinerja-daerah', 'Dompu — Langkah strategis diambil Bupati Dompu, Bambang Firdaus, SE, dalam memperkokoh struktur birokrasi daerah. Bertempat di Pendopo Bupati pada Senin (12/1/2026), sebanyak 15 pejabat Organisasi P...', '<p>Dompu — Langkah strategis diambil Bupati Dompu, Bambang Firdaus, SE, dalam memperkokoh struktur birokrasi daerah. Bertempat di Pendopo Bupati pada Senin (12/1/2026), sebanyak 15 pejabat Organisasi Perangkat Daerah (OPD) resmi dilantik dan diambil sumpah jabatannya.</p>', 'https://ppid.dompukab.go.id/wp-content/uploads/2026/01/a8-1.jpg', 'published', '2026-01-14 00:00:00', '2026-01-31 16:35:36'),
(5, 'STQ Desa Daha Resmi Digelar', '', 'stq-desa-daha-resmi-digelar-bupati-dompu-tegaskan-komitmen-pembinaan-keagamaan', 'Dompu – Komitmen Pemerintah Kabupaten Dompu dalam memperkuat pembinaan keagamaan di tingkat desa kembali ditunjukkan melalui pelaksanaan Seleksi Tilawatil Qur\'an (STQ) di Desa Daha.', '<p>Dompu – Komitmen Pemerintah Kabupaten Dompu dalam memperkuat pembinaan keagamaan di tingkat desa kembali ditunjukkan melalui pelaksanaan Seleksi Tilawatil Qur\'an (STQ) di Desa Daha, Kecamatan Hu\'u.</p><p>Kegiatan tersebut secara resmi dibuka oleh Bupati Dompu, Bambang Firdaus, SE, pada Jumat (08/01/2026).</p>', 'https://ppid.dompukab.go.id/wp-content/uploads/2026/01/bb.jpg', 'published', '2026-01-11 00:00:00', '2026-01-29 07:55:40'),
(6, 'PPID Kabupaten Dompu Luncurkan Layanan Informasi Digital', '', 'ppid-kabupaten-dompu-luncurkan-layanan-informasi-digital', 'PPID Kabupaten Dompu secara resmi meluncurkan layanan informasi digital...', 'PPID Kabupaten Dompu secara resmi meluncurkan layanan informasi digital untuk memudahkan masyarakat dalam mengakses informasi publik.', NULL, 'published', '2026-01-29 08:03:39', '2026-01-29 08:03:39'),
(7, 'Sosialisasi Keterbukaan Informasi Publik', 'berita', 'sosialisasi-keterbukaan-informasi-publik', 'PPID Kabupaten Dompu mengadakan sosialisasi keterbukaan informasi publik....', 'PPID Kabupaten Dompu mengadakan sosialisasi keterbukaan informasi publik.', '', 'published', '2026-01-29 08:03:39', '2026-01-31 16:35:39'),
(8, 'TES BERITA BARU', 'berita', 'tes-berita-baru', 'Dompu - lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lor...', 'Dompu - lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit amet lorem ipsum dolor sit ametv lorem ipsum dolor sit amet', '1769790152_01KF0851YRGQNHH7VB6NDYS0TM.jpg', 'published', '2026-01-30 16:22:32', '2026-01-31 12:50:24'),
(11, 'tes', 'berita', 'tes', 'asdfghjkl...', 'asdfghjkl', '', 'published', '2026-02-08 19:59:45', '2026-02-08 19:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `created_at`, `updated_at`) VALUES
(2, 'NEW', 'new', '', '2026-02-08 15:19:10', '2026-02-08 18:44:07'),
(4, 'b', 'b', '', '2026-02-08 19:43:18', '2026-02-08 19:43:18');

-- --------------------------------------------------------

--
-- Table structure for table `page_blocks`
--

CREATE TABLE `page_blocks` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `type` enum('text','table','file','link') NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_blocks`
--

INSERT INTO `page_blocks` (`id`, `page_id`, `type`, `position`, `created_at`) VALUES
(7, 2, 'text', 0, '2026-02-09 02:44:07'),
(8, 2, 'table', 1, '2026-02-09 02:44:07'),
(11, 4, 'text', 0, '2026-02-09 03:43:18');

-- --------------------------------------------------------

--
-- Table structure for table `page_files`
--

CREATE TABLE `page_files` (
  `block_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_image_blocks`
--

CREATE TABLE `page_image_blocks` (
  `id` int(11) NOT NULL,
  `block_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_links`
--

CREATE TABLE `page_links` (
  `block_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `target` enum('_self','_blank') NOT NULL DEFAULT '_self'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_table_blocks`
--

CREATE TABLE `page_table_blocks` (
  `block_id` int(11) NOT NULL,
  `enable_search` tinyint(1) NOT NULL DEFAULT 1,
  `enable_sort` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_table_blocks`
--

INSERT INTO `page_table_blocks` (`block_id`, `enable_search`, `enable_sort`) VALUES
(8, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `page_table_cells`
--

CREATE TABLE `page_table_cells` (
  `row_id` int(11) NOT NULL,
  `column_id` int(11) NOT NULL,
  `cell_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_table_cells`
--

INSERT INTO `page_table_cells` (`row_id`, `column_id`, `cell_value`) VALUES
(9, 9, '1'),
(9, 10, 'SAO'),
(10, 9, '2'),
(10, 10, 'MHA');

-- --------------------------------------------------------

--
-- Table structure for table `page_table_columns`
--

CREATE TABLE `page_table_columns` (
  `id` int(11) NOT NULL,
  `table_block_id` int(11) NOT NULL,
  `header_name` varchar(255) NOT NULL,
  `column_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_table_columns`
--

INSERT INTO `page_table_columns` (`id`, `table_block_id`, `header_name`, `column_order`) VALUES
(9, 8, 'No', 0),
(10, 8, 'JUDUL', 1);

-- --------------------------------------------------------

--
-- Table structure for table `page_table_rows`
--

CREATE TABLE `page_table_rows` (
  `id` int(11) NOT NULL,
  `table_block_id` int(11) NOT NULL,
  `row_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_table_rows`
--

INSERT INTO `page_table_rows` (`id`, `table_block_id`, `row_order`) VALUES
(9, 8, 0),
(10, 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `page_text_blocks`
--

CREATE TABLE `page_text_blocks` (
  `block_id` int(11) NOT NULL,
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_text_blocks`
--

INSERT INTO `page_text_blocks` (`block_id`, `content`) VALUES
(7, 'INI adalah Tabel'),
(11, 'b');

-- --------------------------------------------------------

--
-- Table structure for table `permohonan_informasi`
--

CREATE TABLE `permohonan_informasi` (
  `id` int(11) NOT NULL,
  `nomor_registrasi` varchar(50) NOT NULL,
  `nama_pemohon` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `informasi_diminta` text NOT NULL,
  `tujuan_penggunaan` text DEFAULT NULL,
  `cara_memperoleh` enum('melihat','salinan','softcopy') NOT NULL,
  `status` enum('diajukan','diproses','ditolak','selesai') DEFAULT 'diajukan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permohonan_informasi`
--

INSERT INTO `permohonan_informasi` (`id`, `nomor_registrasi`, `nama_pemohon`, `email`, `no_hp`, `alamat`, `informasi_diminta`, `tujuan_penggunaan`, `cara_memperoleh`, `status`, `created_at`) VALUES
(1, 'PPID-20260202-0001', 'rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'bali1', 'Dinas Perpustakaan dan Kearsipan - a', 'woi kerja yang bener', '', 'diproses', '2026-02-02 02:26:47'),
(2, 'PPID-20260202-0002', 'rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'bali1', 'Dinas Perpustakaan dan Kearsipan - a', 'woi kerja yang bener', '', 'selesai', '2026-02-02 02:36:14'),
(3, 'PPID-20260203-0001', 'rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'asdfghjk', 'Dinas Kesehatan - dfghjkl;&#039;', 'sdfghjkl;&#039;', '', 'diajukan', '2026-02-03 11:51:23'),
(4, 'PPID-20260206-0001', 'Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'alkdss', 'Sekretariat Daerah - Melakukan download melalui website online', 'Untuk kebutuhan PKL', '', 'diajukan', '2026-02-06 00:57:39'),
(5, 'PPID-20260206-0002', 'Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'alkdss', 'Sekretariat Daerah - Melakukan download melalui website online', 'Untuk kebutuhan PKL', '', 'diajukan', '2026-02-06 00:59:11'),
(6, 'PPID-20260206-0003', 'rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'skdnxcnkn ncanca kdnsnc', 'Dinas Sosial - Dengan download melalui web OPD terkait informasi', 'Untuk kebutuhan kegiatan pelaksaan PKL', '', 'diajukan', '2026-02-06 01:01:09'),
(7, 'PPID-20260206-0004', 'Ali', 'aliali@gmail.com', '0895614220050', 'rfads', 'Dinas Tenaga Kerja - qwertyuiop[lkjhgfzxcvbnm', 'qwertyuiop[', '', 'diajukan', '2026-02-06 01:07:11'),
(8, 'PPID-20260206-0005', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'asndjnmckxkm ca', 'Sekretariat Daerah - wertyuiopkjhgfdsazxcvbnlkjfdawertyuio', 'cn nkcm kk mk mmcnmkmkkjn', '', 'diajukan', '2026-02-06 01:18:58'),
(9, 'PPID-20260206-0006', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'a', 'Sekretariat Daerah - ertyui', 'sdfghjk', '', 'diajukan', '2026-02-06 01:25:20'),
(10, 'PPID-20260206-0007', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'a', 'Sekretariat Daerah - ertyui', 'sdfghjk', '', 'diajukan', '2026-02-06 01:25:35'),
(11, 'PPID-20260206-0008', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'a', 'Sekretariat Daerah - ertyui', 'sdfghjk', '', 'diajukan', '2026-02-06 01:29:33'),
(12, 'PPID-20260206-0009', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'asndjnmckxkm ca', 'Sekretariat Daerah - wertyuiopkjhgfdsazxcvbnlkjfdawertyuio', 'cn nkcm kk mk mmcnmkmkkjn', '', 'diajukan', '2026-02-06 01:29:54'),
(13, 'PPID-20260206-0010', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'sdfghjk', 'Dinas Pekerjaan Umum - s', 's', '', 'diajukan', '2026-02-06 01:30:57'),
(14, 'PPID-20260206-0011', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'sdfghjk', 'Dinas Pekerjaan Umum - s', 's', '', 'diajukan', '2026-02-06 01:36:43'),
(15, 'PPID-20260206-0012', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'asndjnmckxkm ca', 'Sekretariat Daerah - wertyuiopkjhgfdsazxcvbnlkjfdawertyuio', 'cn nkcm kk mk mmcnmkmkkjn', '', 'diajukan', '2026-02-06 01:36:49'),
(16, 'PPID-20260206-0013', '3_ R. Rafi', 'rafiyudipramana@gmail.com', '0895614220050', 'a', 'Dinas Pendidikan - a', 'aa', '', 'diajukan', '2026-02-06 01:38:14');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`) VALUES
(1, 'site_title', 'PPID Kabupaten Dompu', '2026-01-29 08:01:15'),
(2, 'site_description', 'Pejabat Pengelola Informasi dan Dokumentasi Kabupaten Dompu', '2026-01-29 08:01:15'),
(3, 'hero_title', 'Informasi Mengenai PPID Kabupaten Dompu', '2026-01-29 08:01:15'),
(4, 'hero_description', 'PPID Kabupaten Dompu berkomitmen untuk memberikan layanan informasi publik yang transparan dan akuntabel sesuai dengan amanat Undang-Undang No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik.', '2026-01-29 08:01:15');

-- --------------------------------------------------------

--
-- Table structure for table `statistik_layanan`
--

CREATE TABLE `statistik_layanan` (
  `id` int(11) NOT NULL,
  `jenis_layanan` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_aktif` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`, `status_aktif`, `last_login`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ppid.dompu.go.id', 'admin', '2026-01-29 08:01:15', 1, NULL),
(4, 'a', '123', 'a', 'admin', '2026-01-29 23:55:35', 1, NULL),
(6, 'rafi', '$2y$10$P3Sq1NY2L0MBXhBRJ/reCe1VzTraAbBYsSIFtVB4LpIUJrDLbEC6K', 'a', 'admin', '2026-01-29 23:55:56', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `daftar_informasi_publik`
--
ALTER TABLE `daftar_informasi_publik`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dip_file` (`file_id`),
  ADD KEY `fk_dip_user` (`created_by`);

--
-- Indexes for table `external_websites`
--
ALTER TABLE `external_websites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_files_user` (`uploaded_by`);

--
-- Indexes for table `informasi_lampiran`
--
ALTER TABLE `informasi_lampiran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lampiran_informasi` (`informasi_id`);

--
-- Indexes for table `jawaban_permohonan`
--
ALTER TABLE `jawaban_permohonan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jawaban_permohonan` (`permohonan_id`),
  ADD KEY `fk_jawaban_user` (`dijawab_oleh`);

--
-- Indexes for table `keberatan`
--
ALTER TABLE `keberatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_status_keberatan`
--
ALTER TABLE `log_status_keberatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_keberatan` (`keberatan_id`),
  ADD KEY `fk_log_keberatan_user` (`diubah_oleh`);

--
-- Indexes for table `log_status_permohonan`
--
ALTER TABLE `log_status_permohonan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_permohonan` (`permohonan_id`),
  ADD KEY `fk_log_user` (`diubah_oleh`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `page_blocks`
--
ALTER TABLE `page_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page_blocks_page_pos` (`page_id`,`position`);

--
-- Indexes for table `page_files`
--
ALTER TABLE `page_files`
  ADD PRIMARY KEY (`block_id`);

--
-- Indexes for table `page_image_blocks`
--
ALTER TABLE `page_image_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `block_id` (`block_id`);

--
-- Indexes for table `page_links`
--
ALTER TABLE `page_links`
  ADD PRIMARY KEY (`block_id`);

--
-- Indexes for table `page_table_blocks`
--
ALTER TABLE `page_table_blocks`
  ADD PRIMARY KEY (`block_id`);

--
-- Indexes for table `page_table_cells`
--
ALTER TABLE `page_table_cells`
  ADD PRIMARY KEY (`row_id`,`column_id`),
  ADD KEY `fk_cell_column` (`column_id`);

--
-- Indexes for table `page_table_columns`
--
ALTER TABLE `page_table_columns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_table_columns_order` (`table_block_id`,`column_order`);

--
-- Indexes for table `page_table_rows`
--
ALTER TABLE `page_table_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_table_rows_order` (`table_block_id`,`row_order`);

--
-- Indexes for table `page_text_blocks`
--
ALTER TABLE `page_text_blocks`
  ADD PRIMARY KEY (`block_id`);

--
-- Indexes for table `permohonan_informasi`
--
ALTER TABLE `permohonan_informasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_registrasi` (`nomor_registrasi`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `statistik_layanan`
--
ALTER TABLE `statistik_layanan`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `daftar_informasi_publik`
--
ALTER TABLE `daftar_informasi_publik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `external_websites`
--
ALTER TABLE `external_websites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `informasi_lampiran`
--
ALTER TABLE `informasi_lampiran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jawaban_permohonan`
--
ALTER TABLE `jawaban_permohonan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keberatan`
--
ALTER TABLE `keberatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `log_status_keberatan`
--
ALTER TABLE `log_status_keberatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `log_status_permohonan`
--
ALTER TABLE `log_status_permohonan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `page_blocks`
--
ALTER TABLE `page_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `page_image_blocks`
--
ALTER TABLE `page_image_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page_table_columns`
--
ALTER TABLE `page_table_columns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `page_table_rows`
--
ALTER TABLE `page_table_rows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permohonan_informasi`
--
ALTER TABLE `permohonan_informasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `statistik_layanan`
--
ALTER TABLE `statistik_layanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daftar_informasi_publik`
--
ALTER TABLE `daftar_informasi_publik`
  ADD CONSTRAINT `fk_dip_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dip_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `fk_files_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `informasi_lampiran`
--
ALTER TABLE `informasi_lampiran`
  ADD CONSTRAINT `fk_lampiran_informasi` FOREIGN KEY (`informasi_id`) REFERENCES `daftar_informasi_publik` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jawaban_permohonan`
--
ALTER TABLE `jawaban_permohonan`
  ADD CONSTRAINT `fk_jawaban_permohonan` FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan_informasi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jawaban_user` FOREIGN KEY (`dijawab_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `log_status_keberatan`
--
ALTER TABLE `log_status_keberatan`
  ADD CONSTRAINT `fk_log_keberatan` FOREIGN KEY (`keberatan_id`) REFERENCES `keberatan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_keberatan_user` FOREIGN KEY (`diubah_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `log_status_permohonan`
--
ALTER TABLE `log_status_permohonan`
  ADD CONSTRAINT `fk_log_permohonan` FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan_informasi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`diubah_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `page_blocks`
--
ALTER TABLE `page_blocks`
  ADD CONSTRAINT `fk_page_blocks_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_files`
--
ALTER TABLE `page_files`
  ADD CONSTRAINT `fk_file_block` FOREIGN KEY (`block_id`) REFERENCES `page_blocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_image_blocks`
--
ALTER TABLE `page_image_blocks`
  ADD CONSTRAINT `page_image_blocks_ibfk_1` FOREIGN KEY (`block_id`) REFERENCES `page_blocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_links`
--
ALTER TABLE `page_links`
  ADD CONSTRAINT `fk_link_block` FOREIGN KEY (`block_id`) REFERENCES `page_blocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_table_blocks`
--
ALTER TABLE `page_table_blocks`
  ADD CONSTRAINT `fk_table_block` FOREIGN KEY (`block_id`) REFERENCES `page_blocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_table_cells`
--
ALTER TABLE `page_table_cells`
  ADD CONSTRAINT `fk_cell_column` FOREIGN KEY (`column_id`) REFERENCES `page_table_columns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cell_row` FOREIGN KEY (`row_id`) REFERENCES `page_table_rows` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_table_columns`
--
ALTER TABLE `page_table_columns`
  ADD CONSTRAINT `fk_table_columns` FOREIGN KEY (`table_block_id`) REFERENCES `page_table_blocks` (`block_id`) ON DELETE CASCADE;

--
-- Constraints for table `page_table_rows`
--
ALTER TABLE `page_table_rows`
  ADD CONSTRAINT `fk_table_rows` FOREIGN KEY (`table_block_id`) REFERENCES `page_table_blocks` (`block_id`) ON DELETE CASCADE;

--
-- Constraints for table `page_text_blocks`
--
ALTER TABLE `page_text_blocks`
  ADD CONSTRAINT `fk_text_block` FOREIGN KEY (`block_id`) REFERENCES `page_blocks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
