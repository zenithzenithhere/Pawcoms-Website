-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 20, 2026 at 10:30 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

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
  `uploader_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploader_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `artwork_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mime_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stored_filename` (`stored_filename`),
  KEY `idx_image_uploads_user` (`user_id`),
  KEY `idx_image_uploads_date` (`uploaded_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `image_uploads`
--

INSERT INTO `image_uploads` (`id`, `user_id`, `uploader_name`, `uploader_email`, `stored_filename`, `original_filename`, `artwork_title`, `mime_type`, `is_pinned`, `uploaded_at`) VALUES
(1, 3, 'Zeckerr', 'Jim@gmail.com', '948876adda235e5c968943f6183319bc.jpg', '78c3efb1bcde8074884b17d414d71760_4785075802843591604.jpg', '78c3efb1bcde8074884b17d414d71760_4785075802843591604.jpg', 'image/jpeg', 0, '2026-09-06 15:03:40'),
(2, 3, 'Zeckerr', 'Jim@gmail.com', '1ba614cce17ad11e0949708c81ffae76.jpg', 'dfsfdsd.jpg', 'dfsfdsd.jpg', 'image/jpeg', 1, '2026-09-06 15:13:55'),
(3, 3, 'Zeckerr', 'Jim@gmail.com', '763c5dbfb0b635cc08c0a63270089fb1.jpg', 'asdfhj.jpg', 'asdfhj.jpg', 'image/jpeg', 1, '2026-09-06 15:13:59'),
(4, 5, 'Alistair', 'abasola@gmail.com', 'e956dd2dad5eab590451eb8ecd739b7e.png', 'Screenshot_2026-09-03_225229-removebg-preview.png', 'my pforile hehe', 'image/png', 0, '2026-10-20 10:20:36'),
(5, 7, 'Devils Anus', 'diablo@gmail.com', 'd7ce37a1ab185a8c7ae3a95e2f495a89.png', 'Gemini_Generated_Image_tno43stno43stno4.png', 'Wowie', 'image/png', 0, '2026-10-20 10:23:31'),
(6, 9, 'Commander Sarge', 'commander@gmail.com', '5274e798e6ae9ac69e4da297cc30de0c.png', 'ChatGPT Image Jan 7, 2026, 04_55_02 PM.png', 'Zenith Poster', 'image/png', 0, '2026-10-20 10:26:58');

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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `photo`) VALUES
(1, 'Kurt James B. Bohol', 'kurt@gmail.com', '$2y$10$2wgSq7swksdH61ecn7qh6eReGkE9ZD4wibszkrbXYk.xir0R9ObWm', 'admin', ''),
(3, 'Zeckerr', 'Jim@gmail.com', '$2y$10$EkVEa0S2qL/YvKwN5TBxneV2DKCKvpcdT0kuGJfG8MPxKclcHjnqG', 'artist', 'profile_6a9d80aec6b040.27733070.jpg'),
(4, 'Devlin', 'Dev@gmail.com', '$2y$10$jqXFevg3ypSowxFBJa5ZrOGCM4cnARpZB8LS1CJVO0dgFPIkuZ/7m', 'commissioner', 'profile_6a9d814a0fc0a8.49217531.jpg'),
(5, 'Alistair', 'abasola@gmail.com', '$2y$10$fwPai4RJ3ofqdzvirAyEHuwBEryP8CN/zhuUxmBG0RWE37m/L3GiC', 'artist', 'profile_6ad74064c3d950.53720334.png'),
(6, 'Albino Raccoon', 'albino@gmail.com', '$2y$10$JMZLPxhuNNZ7J7w0waLpgeLIeRzgCp7WfjJ.7C6ISg2gBdV/iaahy', 'commissioner', 'profile_6ad740cf5030d3.75436943.png'),
(7, 'Devils Anus', 'diablo@gmail.com', '$2y$10$YwV5yDNagAS8tlKZfyrOb.UJxMf2FXFywFsbtvWBaeZGgOqi5hZJu', 'artist', 'profile_6ad74104ae0552.08054825.jpg'),
(8, 'Common Folk', 'comissioner@gmail.com', '$2y$10$B3ZL3gS2QvI2xMa92WhUHO7fXOUwCY/hpHzTGjo6Mtwi0YlPBoO9W', 'commissioner', 'profile_6ad7414cc92c57.13933302.png'),
(9, 'Commander Sarge', 'commander@gmail.com', '$2y$10$DiBz1ki107b0w5kejDN80ON/6NXU6AQYqYs6HgXAM9v0veEFnqXvu', 'artist', 'profile_6ad74183785b38.87231962.png');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
