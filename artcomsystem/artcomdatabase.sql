-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 13, 2026 at 09:56 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `artcomdatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `image_uploads`
--

DROP TABLE IF EXISTS `image_uploads`;
CREATE TABLE IF NOT EXISTS `image_uploads` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `uploader_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploader_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stored_filename` (`stored_filename`),
  KEY `idx_image_uploads_user` (`user_id`),
  KEY `idx_image_uploads_date` (`uploaded_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `image_uploads`
--

INSERT INTO `image_uploads` (`id`, `user_id`, `uploader_name`, `uploader_email`, `stored_filename`, `original_filename`, `mime_type`, `is_pinned`, `uploaded_at`) VALUES
(1, 3, 'Zeckerr', 'Jim@gmail.com', '948876adda235e5c968943f6183319bc.jpg', '78c3efb1bcde8074884b17d414d71760_4785075802843591604.jpg', 'image/jpeg', 0, '2026-09-06 15:03:40'),
(2, 3, 'Zeckerr', 'Jim@gmail.com', '1ba614cce17ad11e0949708c81ffae76.jpg', 'dfsfdsd.jpg', 'image/jpeg', 1, '2026-09-06 15:13:55'),
(3, 3, 'Zeckerr', 'Jim@gmail.com', '763c5dbfb0b635cc08c0a63270089fb1.jpg', 'asdfhj.jpg', 'image/jpeg', 1, '2026-09-06 15:13:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','artist','commissioner') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'commissioner',
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `photo`) VALUES
(1, 'Kurt James B. Bohol', 'kurt@gmail.com', '$2y$10$2wgSq7swksdH61ecn7qh6eReGkE9ZD4wibszkrbXYk.xir0R9ObWm', 'admin', ''),
(3, 'Zeckerr', 'Jim@gmail.com', '$2y$10$EkVEa0S2qL/YvKwN5TBxneV2DKCKvpcdT0kuGJfG8MPxKclcHjnqG', 'artist', 'profile_6a9d80aec6b040.27733070.jpg'),
(4, 'Devlin', 'Dev@gmail.com', '$2y$10$jqXFevg3ypSowxFBJa5ZrOGCM4cnARpZB8LS1CJVO0dgFPIkuZ/7m', 'commissioner', 'profile_6a9d814a0fc0a8.49217531.jpg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
