-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 21, 2026 at 03:23 AM
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
  `uploader_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploader_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `artwork_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mime_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stored_filename` (`stored_filename`),
  KEY `idx_image_uploads_user` (`user_id`),
  KEY `idx_image_uploads_date` (`uploaded_at`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `image_uploads`
--

INSERT INTO `image_uploads` (`id`, `user_id`, `uploader_name`, `uploader_email`, `stored_filename`, `original_filename`, `artwork_title`, `mime_type`, `is_pinned`, `uploaded_at`) VALUES
(4, 5, 'Alistair', 'abasola@gmail.com', 'e956dd2dad5eab590451eb8ecd739b7e.png', 'Screenshot_2026-09-03_225229-removebg-preview.png', 'my pforile hehe', 'image/png', 0, '2026-10-20 10:20:36'),
(6, 9, 'Commander Sarge', 'commander@gmail.com', '5274e798e6ae9ac69e4da297cc30de0c.png', 'ChatGPT Image Jan 7, 2026, 04_55_02 PM.png', 'Zenith Poster', 'image/png', 0, '2026-10-20 10:26:58'),
(7, 10, 'Zeckerr', 'Jim@gmail.com', '1e9d7a6f0a97ad13cc5625530b8414f2.webp', '01e91559361639f45494cb3d45f970bd.webp', 'Boreder Collie', 'image/webp', 1, '2026-10-20 21:37:37'),
(8, 10, 'Zeckerr', 'Jim@gmail.com', '2bfc74d54312120880810b6fdb7ade7b.jpg', 'f18fb783e089f9f8a88f3cd0d12e2160.jpg', 'Arknights Promotional', 'image/jpeg', 1, '2026-10-20 21:38:18'),
(9, 10, 'Zeckerr', 'Jim@gmail.com', '352de18f610a3587d1f917a5dc9aa8b5.jpg', 'bf768a60bf2a5f0edf8a34de8ea4b3a0.jpg', 'Kitsune', 'image/jpeg', 1, '2026-10-20 21:38:29'),
(10, 11, 'Nagabe', 'Nagabe@gmail.com', '4ae205c96e9cb39222d13e306ce8dba5.jpg', '807bc8452d99ab3da4e3ffba51456753.jpg', 'Smell', 'image/jpeg', 0, '2026-10-20 21:39:56'),
(11, 11, 'Nagabe', 'Nagabe@gmail.com', '922ab4dc892e4b0d9b65a0b79574ed7e.webp', 'POUJYHGFD.webp', 'Lycaon Promotional', 'image/webp', 1, '2026-10-20 21:40:25'),
(12, 11, 'Nagabe', 'Nagabe@gmail.com', 'e4790466aeae41726165e813231497e9.webp', 'UYGFDX.webp', 'Crocs Promotional', 'image/webp', 1, '2026-10-20 21:40:59'),
(13, 11, 'Nagabe', 'Nagabe@gmail.com', 'e3260da0cc2b006eec2186d97d568e50.jpg', 'IJHGFVCX.jpg', 'Claw machine', 'image/jpeg', 0, '2026-10-20 21:41:17'),
(14, 11, 'Nagabe', 'Nagabe@gmail.com', '9cf89e5315fa8fd708bab5e411343475.webp', 'ijhgfds.webp', 'Counter', 'image/webp', 1, '2026-10-20 21:41:33'),
(15, 11, 'Nagabe', 'Nagabe@gmail.com', 'c4e4009414ab3633cebb9113852095e8.jpg', 'images.jpg', 'IT', 'image/jpeg', 0, '2026-10-20 21:41:57'),
(16, 12, 'RinoTuna', 'Tuna@gmail.com', '46a72dcf081ebefbe3f3ba5b03fcc1ca.jpg', 'rinotuna-1.jpg', 'Sketch #1', 'image/jpeg', 1, '2026-10-20 21:44:00'),
(17, 12, 'RinoTuna', 'Tuna@gmail.com', 'ca5c958c5c3d2d9fa709ce020b599d23.jpg', 'rinotuna-3.jpg', 'Sketch #2', 'image/jpeg', 0, '2026-10-20 21:44:12'),
(18, 11, 'Nagabe', 'Nagabe@gmail.com', '9e86734b65ef65db4b9d75b00f036fea.jpg', '144396276.jpg', 'Manga Cover', 'image/jpeg', 1, '2026-10-20 21:45:07'),
(19, 11, 'Nagabe', 'Nagabe@gmail.com', '0cc4a97832faa6629eb4b516f919f87e.jpg', '1614848471-c2c45960a00e60776710ea11f3ba8f61.jpeg', 'Girl From The Other Side', 'image/jpeg', 0, '2026-10-20 21:45:50'),
(20, 12, 'RinoTuna', 'Tuna@gmail.com', '3a83b5a3a62bc64e83e0b71fbe9dfeb5.jpg', 'rinotuna-4.jpg', 'Sketch #3', 'image/jpeg', 1, '2026-10-20 21:46:23'),
(22, 11, 'Nagabe', 'Nagabe@gmail.com', '14821f2bee711521c25fba4fc2ce389a.webp', 'new-lycaon-collab-art-by-nagabe-v0-gimu4245iodg1.webp', 'Goatskin', 'image/webp', 0, '2026-10-20 21:47:10'),
(23, 12, 'RinoTuna', 'Tuna@gmail.com', '45770ef25f42eca1ea3d507bb5587b29.webp', 'dfsdf.webp', 'Bird Ref', 'image/webp', 1, '2026-10-20 21:47:47'),
(24, 10, 'Zeckerr', 'Jim@gmail.com', '6875890f574037bd5e5dff4d5e2c84fa.jpg', 'wjqejyyb8s2b1.jpg', 'Street Cat', 'image/jpeg', 1, '2026-10-20 21:48:19'),
(25, 10, 'Zeckerr', 'Jim@gmail.com', '6de12eebed42402684b8819b48fd5c10.jpg', '78c3efb1bcde8074884b17d414d71760_4785075802843591604.jpg', 'Concept Art', 'image/jpeg', 1, '2026-10-20 21:48:47'),
(26, 12, 'RinoTuna', 'Tuna@gmail.com', '53c62b1f4da7273cdec30ed2cf22e591.jpg', '__pinkfong_drawn_by_rinotuna__b12fafbd3a8f2c0082b69c5f116e1f97.jpg', 'Pink Fong', 'image/jpeg', 1, '2026-10-20 21:50:09'),
(27, 13, 'Ceaser Ritz', 'ritz@gmail.com', 'art_6ad8255b01bc13.92405897.png', 'Cesar_Ritz_(oval_crop).png', 'My new profile. hehehe', 'image/png', 1, '2026-10-21 02:37:15'),
(28, 4, 'Zurt Zames Zohol', 'kurt@gmail.com', 'art_6ad82e3c41d207.16606120.webp', 'POUJYHGFD.webp', 'I made this hehe', 'image/webp', 0, '2026-10-21 03:15:08');

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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `photo`) VALUES
(11, 'Nagabe', 'Nagabe@gmail.com', '$2y$10$RzL8b6HQXupKwwJ7WiMD5.LHXnxzvqwkHtdlOYzrJuBQqF.P0Pes6', 'artist', 'profile_6ad7df9be1af80.32213373.png'),
(10, 'Zeckerr', 'Jim@gmail.com', '$2y$10$CC50NHGF7PSyKT3hfvIrW.iT1N.ebZYOgb.by8zjIMJx/NwQ/SZX6', 'artist', 'profile_6ad7decb2e1051.68393829.jpg'),
(4, 'Zurt Zames Zohol', 'kurt@gmail.com', '$2y$10$jqXFevg3ypSowxFBJa5ZrOGCM4cnARpZB8LS1CJVO0dgFPIkuZ/7m', 'admin', 'profile_6ad7e2900c2f24.52352607.png'),
(5, 'Alistair', 'abasola@gmail.com', '$2y$10$fwPai4RJ3ofqdzvirAyEHuwBEryP8CN/zhuUxmBG0RWE37m/L3GiC', 'artist', 'profile_6ad74064c3d950.53720334.png'),
(6, 'Albino Raccoon', 'albino@gmail.com', '$2y$10$JMZLPxhuNNZ7J7w0waLpgeLIeRzgCp7WfjJ.7C6ISg2gBdV/iaahy', 'admin', 'profile_6ad740cf5030d3.75436943.png'),
(13, 'Ceaser Ritz', 'ritz@gmail.com', '$2y$10$Csg0Q1V6FWvjyhOL378xT.7qmfY5SBV9qCmCa6D4ZLYcPceiAl.N6', 'artist', 'profile_6ad82513578d63.38604938.png'),
(8, 'Common Folk', 'comissioner@gmail.com', '$2y$10$B3ZL3gS2QvI2xMa92WhUHO7fXOUwCY/hpHzTGjo6Mtwi0YlPBoO9W', 'commissioner', 'profile_6ad7414cc92c57.13933302.png'),
(9, 'Commander Sarge', 'commander@gmail.com', '$2y$10$DiBz1ki107b0w5kejDN80ON/6NXU6AQYqYs6HgXAM9v0veEFnqXvu', 'artist', 'profile_6ad74183785b38.87231962.png'),
(12, 'RinoTuna', 'Tuna@gmail.com', '$2y$10$odh.RJATal3SX1bfA/1Xiuu/Vesk1R/s/w1W6NFpTBZTP4HCnaWTW', 'artist', 'profile_6ad7e08d428834.43281824.jpg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
