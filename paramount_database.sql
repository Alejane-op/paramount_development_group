-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 24, 2025 at 01:54 PM
-- Server version: 9.1.0
-- PHP Version: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `paramount_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_content`
--

DROP TABLE IF EXISTS `about_content`;
CREATE TABLE IF NOT EXISTS `about_content` (
  `ckey` varchar(80) NOT NULL,
  `cvalue` longtext NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ckey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `about_content`
--

INSERT INTO `about_content` (`ckey`, `cvalue`, `updated_at`) VALUES
('commitment_body', 'Through a disciplined, integrated approach, we provide investors with resilient, risk-adjusted returns while creating lasting impact in the communities.', '2025-09-17 11:03:57'),
('hero_paragraph', 'Paramount Development Group was founded with a clear purpose: to create lasting value for investors while strengthening the communities we serve. With more than 20 years of experience in construction and 15 years of private real estate investing, our team has built a proven track record across multifamily, self-storage, commercial, and redevelopment projects.', '2025-09-17 10:45:42'),
('hero_title', 'Our Paramount Development Background', '2025-09-17 10:45:42'),
('hero_video', '/uploads/hero_20250917_104631.mp4', '2025-09-17 10:46:31'),
('history_15y_body', 'Our disciplined approach has delivered strong returns across multifamily and value-add properties in stable, resilient markets.\r\n', '2025-09-17 10:47:59'),
('history_15y_title', '15+ Years of Real Estate Investments', '2025-09-17 10:32:53'),
('history_20y_body', 'Our foundation is rooted in hands-on building expertise, with vertically integrated companies spanning development, construction, and management.\r\n', '2025-09-17 10:47:59'),
('history_20y_title', '20+ Years in Construction', '2025-09-17 10:32:53'),
('history_comm_body', 'Deep involvement in local initiatives and partnerships ensures our projects serve both investors and the neighborhoods they impact.\r\n', '2025-09-17 10:47:59'),
('history_comm_title', 'Community Ties', '2025-09-17 10:32:53'),
('history_trade_body', 'We own and operate companies in concrete, roofing &amp; siding, and outdoor living, giving us unmatched execution control from the ground up.\r\n', '2025-09-17 10:47:59'),
('history_trade_title', 'Specialized Trade Expertise', '2025-09-17 10:32:53'),
('team_subtitle', 'Our team brings extensive experience in residential, commercial, and investment projects. Guided by thoughtful design, superior craftsmanship, and a commitment to client vision, we deliver projects that embody quality, integrity, and lasting value.', '2025-09-17 11:04:31'),
('team_title', 'At Paramount Development', '2025-09-17 10:32:53'),
('vision_body', 'Para Paramount Development Group is committed to creating enduring value for both investors and communities. Our vision is to build a diversified portfolio that generates lasting generational wealth while enhancing the places where people live and work.\r\nmount Development Group is committed to creating enduring value.', '2025-09-17 10:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `about_goals`
--

DROP TABLE IF EXISTS `about_goals`;
CREATE TABLE IF NOT EXISTS `about_goals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `sort` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `about_goals`
--

INSERT INTO `about_goals` (`id`, `title`, `body`, `sort`, `created_at`, `updated_at`) VALUES
(1, 'Community-Centered Development', 'Prioritize projects that enhance livability, support workforce needs, and contribute to the overall health and resilience of the region.', 0, '2025-09-17 10:56:14', '2025-09-17 10:56:14'),
(2, 'Disciplined Growth', 'Deliver thoughtfully planned multifamily, mixed-use, and commercial projects that balance functionality, sustainability, and investor returns.\r\n', 0, '2025-09-17 10:56:39', '2025-09-17 10:56:39'),
(3, 'Strategic Partnerships', 'Collaborate with city leaders, businesses, and organizations to align each development with the evolving needs of the community.\r\n', 0, '2025-09-17 10:57:19', '2025-09-17 10:57:19'),
(4, 'Generational Impact', 'Approach every project with a long-range vision, ensuring developments stand the test of time and benefit future generations.\r\n', 0, '2025-09-17 10:57:41', '2025-09-17 10:57:41'),
(5, 'Long-Term Value Creation', 'Build assets that generate consistent performance while leaving a lasting positive impact on local economies and neighborhoods.', 0, '2025-09-17 10:57:46', '2025-09-17 11:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `about_milestones`
--

DROP TABLE IF EXISTS `about_milestones`;
CREATE TABLE IF NOT EXISTS `about_milestones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `body` text NOT NULL,
  `sort` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `about_milestones`
--

INSERT INTO `about_milestones` (`id`, `body`, `sort`, `created_at`, `updated_at`) VALUES
(1, 'Launched Paramount Builders in 2001, establishing a reputation for quality craftsmanship and trust.\r\n', 0, '2025-09-17 10:48:44', '2025-09-17 11:27:45'),
(2, 'Grew into a vertically integrated platform, with dedicated companies covering construction services, real estate brokerage, property management, and specialty trades.', 0, '2025-09-17 10:54:44', '2025-09-17 10:54:44'),
(3, 'Expanded into Paramount Development Group to focus on multifamily and value-add investments.\r\n', 0, '2025-09-17 10:55:41', '2025-09-17 10:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `about_strategy`
--

DROP TABLE IF EXISTS `about_strategy`;
CREATE TABLE IF NOT EXISTS `about_strategy` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `sort` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `about_strategy`
--

INSERT INTO `about_strategy` (`id`, `title`, `body`, `sort`, `created_at`, `updated_at`) VALUES
(1, 'Diversified Portfolio with Long-Term Value', 'We balance value-add multifamily opportunities with new development projects in stable, low-volatility markets. This disciplined approach ensures resilient performance across cycles and consistent returns for our investors.', 4, '2025-09-17 11:01:51', '2025-09-17 11:33:20'),
(2, 'Community Enhancement', 'We focus on addressing real housing and development needs in undeserved markets, delivering projects that strengthen local economies and elevate quality of life.', 0, '2025-09-17 11:02:03', '2025-09-17 11:02:03'),
(3, 'Integrated Model for Scalable Growth', 'Our vertically integrated platform—spanning development, construction, and management—allows us to maintain control, drive efficiency, and deliver quality outcomes at scale.', 0, '2025-09-17 11:02:22', '2025-09-17 11:02:22'),
(4, 'Focused Investment Discipline', 'With a dedicated emphasis on multifamily value-add and new development, we target opportunities that align with long-term demand drivers and support sustainable growth.', 0, '2025-09-17 11:02:35', '2025-09-17 11:02:35');

-- --------------------------------------------------------

--
-- Table structure for table `about_values`
--

DROP TABLE IF EXISTS `about_values`;
CREATE TABLE IF NOT EXISTS `about_values` (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon` varchar(80) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `sort` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `about_values`
--

INSERT INTO `about_values` (`id`, `icon`, `title`, `body`, `sort`, `created_at`, `updated_at`) VALUES
(1, 'fa-solid fa-user-shield', 'Integrity', 'We operate with honesty, transparency, and accountability—earning trust through every partnership.', 0, '2025-09-17 10:59:20', '2025-09-17 10:59:20'),
(2, 'fa-solid fa-lightbulb', 'Innovation', 'We embrace forward-thinking strategies and sustainable solutions to drive long-term success.', 0, '2025-09-17 11:00:05', '2025-09-17 11:00:05'),
(3, 'fa-solid fa-handshake', 'Partnership', 'We believe growth is built on strong relationships—with investors, teams, and the communities we serve.', 0, '2025-09-17 11:00:31', '2025-09-17 11:00:31'),
(4, 'fa-solid fa-chart-line', 'Community Impact', 'We measure success not only in financial returns but in the positive change we bring to neighborhoods.', 0, '2025-09-17 11:01:00', '2025-09-17 11:01:00');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT '1',
  `title` varchar(200) NOT NULL,
  `content` mediumtext NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `likes` int NOT NULL DEFAULT '0',
  `views` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `user_id`, `title`, `content`, `image_path`, `likes`, `views`, `created_at`, `updated_at`) VALUES
(9, 1, 'Test Blog', '<p>Testing Blog!</p>', '/uploads/blog_1758644705_d0c4fb1a.jpeg', 1, 2, '2025-09-23 16:25:05', '2025-09-23 16:31:24');

-- --------------------------------------------------------

--
-- Table structure for table `blog_content`
--

DROP TABLE IF EXISTS `blog_content`;
CREATE TABLE IF NOT EXISTS `blog_content` (
  `key` varchar(64) NOT NULL,
  `value` mediumtext,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blog_content`
--

INSERT INTO `blog_content` (`key`, `value`) VALUES
('blogs_hero_h1', 'Paramount: From Concept to Completion, Building success & stronger communities'),
('blogs_hero_p', 'You can browse our blog posts to see featured stories and insightful content that highlight updates, ideas, and inspiration.');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first` varchar(100) NOT NULL,
  `last` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` enum('new','replied') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `first`, `last`, `email`, `message`, `ip`, `user_agent`, `status`, `created_at`) VALUES
(1, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'Test!!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'replied', '2025-09-18 12:47:25'),
(2, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'Good morning, I want you to be hired in our company. As a senior web developer! Please send you CV to us. We will be in touch to you later on. Thank you!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 12:54:06'),
(3, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'Mag Test ko usab!!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 13:43:26'),
(4, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'Usabbb napud!!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 13:44:50'),
(5, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'test napud usab!!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 13:53:43'),
(6, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'usabbb', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 13:56:49'),
(7, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'Hi, paramount. I want to partner with your company to build a multifamily house on North Dakota. Thank youuu!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 14:25:42'),
(8, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'Hi, I want to partner with your company to build a multifamily house. Thanks!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 14:51:08'),
(9, 'Julie', 'Bagnotan', 'juliemaebagnotan@gmail.com', 'Hi, I saw your website and am interested to partner with your company. Thanks!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 14:57:13'),
(10, 'Julie', 'Bagnotan', 'jmabagnotan03210@usep.edu.ph', 'Hi, I saw your website and am interested to partner with your company. Thanks!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 15:01:19'),
(11, 'Julie', 'Bagnotan', 'jmabagnotan03210@usep.edu.ph', 'helloooooooooo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-18 15:08:17'),
(12, 'Julie', 'Bagnotan', 'aopelandas02149@usep.edu.ph', 'helloooooooooo...', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 00:04:09'),
(13, 'Julie', 'Bagnotan', 'aopelandas02149@usep.edu.ph', 'Hello, Good Morning Paramount!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 00:43:45'),
(14, 'Julie', 'Bagnotan', 'aopelandas02149@usep.edu.ph', 'Hello, paramount I want to partner with you!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 00:48:19'),
(15, 'Julie', 'Bagnotan', 'aopelandas02149@usep.edu.ph', 'helloooooooooo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 00:56:30'),
(16, 'Julie', 'Bagnotan', 'aopelandas02149@usep.edu.ph', 'helloooooooooo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 00:58:34'),
(17, 'Julie', 'Bagnotan', 'aopelandas02149@usep.edu.ph', 'helloooooooooo pooo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 01:15:46'),
(18, 'Alejane', 'Pelandas', 'janeorb0331@gmail.com', 'helloooooooo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 01:22:23'),
(19, 'Jane', 'Orb', 'janeorb0331@gmail.com', 'Testing Email.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 12:04:06'),
(20, 'Jules', 'Lee', 'juliemaebagnotan@gmail.com', 'Test Message!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-19 12:34:52'),
(21, 'Ali', 'Pelandas', 'aopelandas02149@usep.edu.ph', 'Test Message!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'new', '2025-09-23 16:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `contact_replies`
--

DROP TABLE IF EXISTS `contact_replies`;
CREATE TABLE IF NOT EXISTS `contact_replies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message_id` int NOT NULL,
  `reply_body` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_replies`
--

INSERT INTO `contact_replies` (`id`, `message_id`, `reply_body`, `sent_at`) VALUES
(1, 1, 'hellooooo', '2025-09-18 12:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `contact_settings`
--

DROP TABLE IF EXISTS `contact_settings`;
CREATE TABLE IF NOT EXISTS `contact_settings` (
  `id` tinyint NOT NULL,
  `hero_image` varchar(255) DEFAULT 'asset/Office-.jpg',
  `headline` varchar(150) NOT NULL DEFAULT 'Have a project in mind?',
  `subtext` text NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_settings`
--

INSERT INTO `contact_settings` (`id`, `hero_image`, `headline`, `subtext`, `address`, `phone`, `email`, `updated_at`) VALUES
(1, '/uploads/hero_20250919_014825_4bbc72.jpg', 'Have a project in mind?', 'Please take a moment to fill out the form below so we can get in touch with you. Whether you have questions, need more information, or would like to schedule an appointment with us, our team will be happy to assist you. Once we receive your message, we’ll respond as soon as possible to make sure your needs are taken care of.', '1407 Tacoma Ave Bismarck ND 58504', '701-471-1783', 'paramount.develop@gmail.com', '2025-09-19 01:48:25');

-- --------------------------------------------------------

--
-- Table structure for table `dev_blocks`
--

DROP TABLE IF EXISTS `dev_blocks`;
CREATE TABLE IF NOT EXISTS `dev_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `layout` enum('image-left','image-right') NOT NULL DEFAULT 'image-left',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dev_blocks`
--

INSERT INTO `dev_blocks` (`id`, `title`, `description`, `image`, `layout`, `sort_order`, `created_at`) VALUES
(1, 'Multifamily  Housing', 'Apartments and residential communities that meet the demand for quality housing in growing markets.', 'uploads/dev_20250919_060738_9ce11983.jpg', 'image-left', 1, '2025-09-15 08:24:20'),
(2, 'Commercial Buildings', 'Purpose-built spaces designed to be functional, efficient, and supportive of long-term local business growth.', 'uploads/dev_20250919_061203_d98d50c0.jpg', 'image-right', 2, '2025-09-15 08:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `dev_bullets`
--

DROP TABLE IF EXISTS `dev_bullets`;
CREATE TABLE IF NOT EXISTS `dev_bullets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_id` int NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_bul_block` (`block_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dev_bullets`
--

INSERT INTO `dev_bullets` (`id`, `block_id`, `label`, `description`, `created_at`) VALUES
(1, 1, 'Stabilized Properties', 'Well-performing assets that provide steady cash flow and long-term investment security.', '2025-09-15 08:25:45'),
(2, 1, 'Value-Add Properties', 'Existing buildings enhanced through renovations, operational improvements, and repositioning to increase performance and returns.', '2025-09-15 08:52:59'),
(3, 1, 'New Development', 'Ground-up projects designed to bring new opportunities to undeserved markets, including:', '2025-09-15 08:53:33'),
(4, 2, 'Re-Development', 'Revitalized, integrated communities that blend residential, retail, and office uses to create vibrant, connected environments.', '2025-09-15 08:55:36'),
(5, 2, 'New Development', 'Projects built with efficiency, sustainability, and environmental responsibility at the forefront.', '2025-09-15 08:55:57');

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_codes`
--

DROP TABLE IF EXISTS `email_verification_codes`;
CREATE TABLE IF NOT EXISTS `email_verification_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `purpose` enum('email_verify','pin_reset') NOT NULL DEFAULT 'email_verify',
  `attempts` tinyint NOT NULL DEFAULT '0',
  `max_attempts` tinyint NOT NULL DEFAULT '5',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `email_verification_codes`
--

INSERT INTO `email_verification_codes` (`id`, `user_id`, `code_hash`, `purpose`, `attempts`, `max_attempts`, `created_at`, `expires_at`, `used_at`) VALUES
(1, 3, '$2y$12$8yF6AVjG9PByEZAKpvVlHOncpdGXflf4YEkyylPln1.vZ4iPuaf7S', 'email_verify', 0, 5, '2025-09-22 01:25:21', '2025-09-21 17:35:21', NULL),
(2, 4, '$2y$12$t.vNOusejIBalYPuOG0XHOOB4uSw76H1vjPvVjhDcQHNL9T5Ke0rK', 'email_verify', 1, 5, '2025-09-22 01:27:26', '2025-09-21 17:37:26', '2025-09-22 01:28:04'),
(3, 5, '$2y$12$D5kWPI3IUSSReQ2yhccY9.lmvIHX0wjWoTXMefW3J88FJDRrEhI/e', 'email_verify', 1, 5, '2025-09-22 19:16:10', '2025-09-22 11:26:10', '2025-09-22 19:16:34');

-- --------------------------------------------------------

--
-- Table structure for table `home_about_content`
--

DROP TABLE IF EXISTS `home_about_content`;
CREATE TABLE IF NOT EXISTS `home_about_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `home_about_content`
--

INSERT INTO `home_about_content` (`id`, `title`, `body`, `updated_at`) VALUES
(1, 'Our Purpose', 'Is to develop projects that go beyond buildings—creating environments where people can live, work, and connect in meaningful ways. Every development is an opportunity to strengthen neighborhoods, foster economic progress, and contribute to the long-term success of the communities we serve.', '2025-09-15 03:44:03');

-- --------------------------------------------------------

--
-- Table structure for table `home_hero_inner`
--

DROP TABLE IF EXISTS `home_hero_inner`;
CREATE TABLE IF NOT EXISTS `home_hero_inner` (
  `id` int NOT NULL AUTO_INCREMENT,
  `upper_title` varchar(255) NOT NULL,
  `down_title` varchar(255) NOT NULL,
  `subtitle` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `home_hero_inner`
--

INSERT INTO `home_hero_inner` (`id`, `upper_title`, `down_title`, `subtitle`, `updated_at`) VALUES
(1, 'Paramount', 'Development Group', 'We view real estate as a cornerstone of community growth and vitality.', '2025-09-18 17:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `login_challenge`
--

DROP TABLE IF EXISTS `login_challenge`;
CREATE TABLE IF NOT EXISTS `login_challenge` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` char(64) NOT NULL,
  `status` enum('pending','approved','denied','expired') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `approved_at` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `ua` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_challenge`
--

INSERT INTO `login_challenge` (`id`, `user_id`, `token`, `status`, `created_at`, `expires_at`, `approved_at`, `ip`, `ua`) VALUES
(1, 1, '19e76cf6409b3cf20db2e7626be1244ef94e9619b45647efdd27493ec1bb72cb', 'pending', '2025-09-21 01:36:03', '2025-09-21 01:39:03', NULL, '::1', ''),
(2, 1, '1202fa8775bf43d15665eeab28c19daf4d96f0cbd34f3d7bbe6d256e8f7f4f58', 'pending', '2025-09-21 01:39:09', '2025-09-21 01:42:09', NULL, '::1', ''),
(3, 1, '0c5df57a9ecc4c057ae6f5ffd86bd11c64ed98490a69362c611248557ec5efe4', 'pending', '2025-09-21 01:39:28', '2025-09-21 01:42:28', NULL, '::1', ''),
(4, 1, '96f02ff5a04bb3de66569a9a22db507fb962d547315f013b24de2d05091fccd8', 'pending', '2025-09-21 01:40:47', '2025-09-21 01:43:47', NULL, '::1', ''),
(5, 1, '381efbf7f5d393197a9bd3ca7bcc517202b3f8ee0c2d9cdb7811e72a1b09627e', 'pending', '2025-09-21 01:42:08', '2025-09-21 01:45:08', NULL, '::1', ''),
(6, 1, '8e55a4b52113a9362112f186aa5b20742b7563fe6bb19f93ea45d798dd8eb35f', 'pending', '2025-09-21 01:43:03', '2025-09-21 01:46:03', NULL, '::1', ''),
(7, 1, '5dd6ae554fc0a7b6b43f015b83789d645935773c8138a0915ad77c0b818fcb04', 'pending', '2025-09-21 01:45:57', '2025-09-21 01:48:57', NULL, '::1', ''),
(8, 1, '0c7455864f48e0aa9d5ed0e7721ef71f4dde7a36b739cc6acbec54a38178494a', 'pending', '2025-09-21 01:48:38', '2025-09-21 01:51:38', NULL, '::1', '');

-- --------------------------------------------------------

--
-- Table structure for table `partner_content`
--

DROP TABLE IF EXISTS `partner_content`;
CREATE TABLE IF NOT EXISTS `partner_content` (
  `key` varchar(80) NOT NULL,
  `value` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `partner_content`
--

INSERT INTO `partner_content` (`key`, `value`, `updated_at`) VALUES
('market_intro', 'Paramount Development Group is strategically focused on the Bismarck–Mandan metro and surrounding communities—an area defined by resilience, steady growth, and long-term housing demand.', '2025-09-17 12:30:33'),
('invest_intro', 'Paramount Development Group follows a disciplined investment strategy designed to protect investor capital while driving long-term growth.', '2025-09-17 12:30:33'),
('invest_result', 'Resilient returns for investors, sustainable growth for our portfolio, and meaningful impact for the communities we develop.', '2025-09-17 12:30:33'),
('vision_intro', 'At Paramount Development Group, we take a disciplined and strategic approach to development and investment—focused on creating long-term value while serving real community needs.', '2025-09-17 12:30:33'),
('community_intro', 'At Paramount Development Group, we believe development should serve more than investors—it should serve the community. Strong communities are built through intentional investment in both people and place. That’s why we actively engage with local organizations, civic leaders, and nonprofits to ensure our projects support broader community goals.\r\n\r\nFrom workforce housing that keeps talent local, to mixed-use developments that create vibrant hubs of activity, we are committed to building projects that address real needs and generate lasting value. Beyond the buildings, we support initiatives that strengthen community infrastructure, youth development, and quality of life—because true progress comes when development and community move forward together.\r\n', '2025-09-17 13:43:22'),
('why_we_do_it', 'We believe strong communities are built with intention. By aligning development with the needs of residents, employers, and investors, Paramount Development Group creates projects that not only perform financially but also enrich the fabric of the community. For us, success is measured in more than numbers—it’s measured in the strength, stability, and pride of the communities we help shape.', '2025-09-17 13:40:44'),
('market_image', '/uploads/market_54264a3f2d5e.jpg', '2025-09-19 07:13:47');

-- --------------------------------------------------------

--
-- Table structure for table `partner_invest_items`
--

DROP TABLE IF EXISTS `partner_invest_items`;
CREATE TABLE IF NOT EXISTS `partner_invest_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `heading` varchar(120) NOT NULL,
  `body` text NOT NULL,
  `sort_order` int NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `partner_invest_items`
--

INSERT INTO `partner_invest_items` (`id`, `heading`, `body`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Diversification', 'Balanced mix of stabilized cash-flow assets, value-add renovations, and selective developments.', 10, '2025-09-17 12:30:33', '2025-09-17 12:30:33'),
(2, 'Reinvestment', 'Cash flow and equity gains are reinvested into new opportunities, compounding value over time.', 20, '2025-09-17 12:30:33', '2025-09-17 12:30:33'),
(3, 'Risk Management', 'Conservative underwriting, strong reserves, and vertical integration reduce execution risk.', 30, '2025-09-17 12:30:33', '2025-09-17 12:30:33'),
(4, 'Growth Focus', 'We target stable, low-volatility markets with consistent renter demand and long-term fundamentals.', 40, '2025-09-17 12:30:33', '2025-09-17 12:30:33');

-- --------------------------------------------------------

--
-- Table structure for table `partner_market_bullets`
--

DROP TABLE IF EXISTS `partner_market_bullets`;
CREATE TABLE IF NOT EXISTS `partner_market_bullets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `body` text NOT NULL,
  `sort_order` int NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `partner_market_bullets`
--

INSERT INTO `partner_market_bullets` (`id`, `title`, `body`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Bismarck–Mandan Metro', 'A stable, low-volatility market with consistent renter demand and strong absorption, even during new supply cycles.', 10, '2025-09-17 12:30:33', '2025-09-17 12:30:33'),
(2, 'North Bismarck Growth Hub', 'One of the region’s fastest-growing corridors, where Paramount has two planned multifamily projects aligned with expanding infrastructure and demand.', 20, '2025-09-17 12:30:33', '2025-09-17 12:30:33'),
(3, 'Regional Strengths', 'A diverse employment base spanning healthcare, energy, education, finance, and government supports a strong economy and reliable demand for housing.', 30, '2025-09-17 12:30:33', '2025-09-17 12:30:33');

-- --------------------------------------------------------

--
-- Table structure for table `partner_partners`
--

DROP TABLE IF EXISTS `partner_partners`;
CREATE TABLE IF NOT EXISTS `partner_partners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `partner_partners`
--

INSERT INTO `partner_partners` (`id`, `name`, `logo_path`, `website`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Paramount Builders', '/uploads/d8ceae2b702f.png', NULL, 100, '2025-09-17 13:44:40', '2025-09-17 13:44:40'),
(2, 'Paramount Real Estate', '/uploads/831aa54135f2.png', NULL, 100, '2025-09-17 13:46:18', '2025-09-17 13:46:18'),
(3, 'Altura', '/uploads/57b59e0120f7.png', NULL, 100, '2025-09-17 13:46:33', '2025-09-17 13:46:33'),
(4, 'Axis', '/uploads/701b790847bd.png', NULL, 100, '2025-09-17 13:46:46', '2025-09-17 13:46:46'),
(5, 'Envision', '/uploads/ab71942c589f.png', NULL, 100, '2025-09-17 13:47:02', '2025-09-17 13:47:02');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `meta` varchar(255) DEFAULT NULL,
  `short_desc` text,
  `cover_image` varchar(500) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `slug`, `location`, `type`, `meta`, `short_desc`, `cover_image`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Mandan', 'mandan', 'Mandan, North Dakota', 'Residential,Multifamily', '36-Unit Value-Add Hold', ' Renovated and stabilized to improve performance and create reliable long-term cash flow.', '/uploads/20250916_074314_546f88.jpg', 0, '2025-09-16 07:43:14', '2025-09-19 02:36:28'),
(2, 'Divide', 'divide', 'Bismarck, North Dakota', 'Multifamily', '30-Unit Value-Add Flip', 'Upgraded and repositioned for a successful exit, delivering strong returns for investors.', '/uploads/20250919_023316_eedfa7.jpeg', 0, '2025-09-16 08:40:10', '2025-09-19 02:33:16'),
(3, 'Trestle', 'trestle', 'Bismarck, North Dakota', 'Residential,Commercial,Mixed-Use', '52-Unit Mixed-Use', 'Infill project combining residential and commercial space, designed to capture premium demand in a growing corridor.', '/uploads/20250919_022104_72b060.jpg', 0, '2025-09-16 08:43:06', '2025-09-19 02:21:04'),
(4, 'Yukon', 'yukon', '5601 Yukon Dr Bismarck, ND 58503', 'Residential', 'Studio: 528', 'In the heart of North Bismarck, an exciting opportunity is emerging—a chance to meet the growing demand for housing in a region poised for substantial commercial expansion.', '/uploads/20250916_090006_3063b0.png', 0, '2025-09-16 08:56:55', '2025-09-16 09:00:06'),
(8, 'Future Pipeline Projects', 'future', 'North Bismarck', 'Residential', '30-Unit', 'Renovated and stabilized to improve performance and create reliable long-term cash flow.', '/uploads/20250916_094321_a3c945.png', 0, '2025-09-16 09:43:21', '2025-09-16 09:43:21'),
(10, 'South Meadows Row Homes', 'south-meadows-row-homes', 'North Dakota', 'Residential', '36-Unit Value-Add Hold', 'Renovated and stabilized to improve performance and create reliable long-term cash flow.', '/uploads/20250919_024143_ad1638.jpeg', 1, '2025-09-19 02:41:43', '2025-09-19 02:41:43'),
(11, 'Parkwood', 'parkwood', 'North Dakota', 'Residential', '16-Unit', 'Renovated and stabilized to improve performance and create reliable long-term cash flow.', '/uploads/20250919_024737_401a23.jpeg', 0, '2025-09-19 02:47:37', '2025-09-19 02:47:37'),
(12, 'Lakewood Village', 'lakewood-village', 'North Dakota', 'Residential,Multifamily', '36 Unit', 'Renovated and stabilized to improve performance and create reliable long-term cash flow.', '/uploads/20250919_064706_01df89.jpeg', 0, '2025-09-19 06:47:06', '2025-09-19 06:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `project_content`
--

DROP TABLE IF EXISTS `project_content`;
CREATE TABLE IF NOT EXISTS `project_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_images`
--

DROP TABLE IF EXISTS `project_images`;
CREATE TABLE IF NOT EXISTS `project_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `img_path` varchar(500) NOT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_proj_imgs_project` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `project_images`
--

INSERT INTO `project_images` (`id`, `project_id`, `img_path`, `sort_order`, `created_at`) VALUES
(1, 1, '/uploads/20250916_074314_7fb983.jpg', 0, '2025-09-16 07:43:14'),
(2, 1, '/uploads/20250916_074314_5d3c62.jpg', 0, '2025-09-16 07:43:14'),
(3, 1, '/uploads/20250916_074314_0f86ce.jpg', 0, '2025-09-16 07:43:14'),
(4, 1, '/uploads/20250916_074314_4c8487.jpg', 0, '2025-09-16 07:43:14'),
(5, 1, '/uploads/20250916_074314_86537f.jpg', 0, '2025-09-16 07:43:14'),
(6, 1, '/uploads/20250916_074314_55a4ba.jpg', 0, '2025-09-16 07:43:14'),
(7, 1, '/uploads/20250916_074314_de1527.jpg', 0, '2025-09-16 07:43:14'),
(22, 4, '/uploads/20250916_085655_516a8d.png', 0, '2025-09-16 08:56:55'),
(23, 4, '/uploads/20250916_085655_f301e8.png', 0, '2025-09-16 08:56:55'),
(24, 4, '/uploads/20250916_085655_1db6f9.png', 0, '2025-09-16 08:56:55'),
(25, 4, '/uploads/20250916_085655_cd2638.png', 0, '2025-09-16 08:56:55'),
(34, 4, '/uploads/20250916_085757_9829b6.png', 0, '2025-09-16 08:57:57'),
(35, 4, '/uploads/20250916_085757_9fe356.png', 0, '2025-09-16 08:57:57'),
(36, 4, '/uploads/20250916_085757_911ac9.png', 0, '2025-09-16 08:57:57'),
(48, 8, '/uploads/20250916_094321_74bf7c.jpg', 0, '2025-09-16 09:43:21'),
(49, 8, '/uploads/20250916_094321_4d4d61.jpg', 0, '2025-09-16 09:43:21'),
(50, 8, '/uploads/20250916_094321_e68435.jpg', 0, '2025-09-16 09:43:21'),
(51, 8, '/uploads/20250916_094321_fd9037.jpg', 0, '2025-09-16 09:43:21'),
(52, 8, '/uploads/20250916_094321_bd167b.jpg', 0, '2025-09-16 09:43:21'),
(53, 8, '/uploads/20250916_094321_271c92.jpg', 0, '2025-09-16 09:43:21'),
(63, 8, '/uploads/20250916_105329_66803a.jpg', 1, '2025-09-16 10:53:29'),
(65, 3, '/uploads/20250919_022328_531b42.jpg', 0, '2025-09-19 02:23:28'),
(66, 3, '/uploads/20250919_022337_51a41e.jpg', 1, '2025-09-19 02:23:37'),
(67, 3, '/uploads/20250919_022402_ff1eb3.jpg', 2, '2025-09-19 02:24:02'),
(68, 3, '/uploads/20250919_022402_e73874.jpg', 3, '2025-09-19 02:24:02'),
(69, 3, '/uploads/20250919_022402_210437.jpg', 4, '2025-09-19 02:24:02'),
(70, 3, '/uploads/20250919_022431_17cad1.jpg', 5, '2025-09-19 02:24:31'),
(71, 3, '/uploads/20250919_022431_bf78f3.jpg', 6, '2025-09-19 02:24:31'),
(72, 2, '/uploads/20250919_023359_1a8502.jpeg', 0, '2025-09-19 02:33:59'),
(73, 2, '/uploads/20250919_023359_230cae.jpeg', 1, '2025-09-19 02:33:59'),
(74, 2, '/uploads/20250919_023359_a8f05f.jpeg', 2, '2025-09-19 02:33:59'),
(75, 2, '/uploads/20250919_023416_b06839.jpeg', 3, '2025-09-19 02:34:16'),
(76, 2, '/uploads/20250919_023416_d8c29f.jpeg', 4, '2025-09-19 02:34:16'),
(77, 2, '/uploads/20250919_023416_707520.jpeg', 5, '2025-09-19 02:34:16'),
(78, 2, '/uploads/20250919_023416_e2beee.jpeg', 6, '2025-09-19 02:34:16'),
(79, 10, '/uploads/20250919_024143_2c8190.jpeg', 0, '2025-09-19 02:41:43'),
(80, 10, '/uploads/20250919_024143_49702d.jpeg', 1, '2025-09-19 02:41:43'),
(81, 10, '/uploads/20250919_024143_7bfff2.jpeg', 2, '2025-09-19 02:41:43'),
(82, 10, '/uploads/20250919_024143_eaea89.jpeg', 3, '2025-09-19 02:41:43'),
(83, 10, '/uploads/20250919_024143_4d9c61.jpeg', 4, '2025-09-19 02:41:43'),
(84, 10, '/uploads/20250919_024143_c26173.jpeg', 5, '2025-09-19 02:41:43'),
(85, 10, '/uploads/20250919_024143_dca481.jpeg', 6, '2025-09-19 02:41:43'),
(86, 11, '/uploads/20250919_024737_f0665e.jpeg', 0, '2025-09-19 02:47:37'),
(87, 11, '/uploads/20250919_024737_d4c7d5.jpeg', 1, '2025-09-19 02:47:37'),
(88, 11, '/uploads/20250919_024737_c73872.jpeg', 2, '2025-09-19 02:47:37'),
(89, 11, '/uploads/20250919_024737_3f8741.jpeg', 3, '2025-09-19 02:47:37'),
(90, 11, '/uploads/20250919_024737_98005e.jpeg', 4, '2025-09-19 02:47:37'),
(91, 11, '/uploads/20250919_024737_100d5d.jpeg', 5, '2025-09-19 02:47:37'),
(92, 11, '/uploads/20250919_024737_8cc999.jpeg', 6, '2025-09-19 02:47:37'),
(93, 12, '/uploads/20250919_064706_5ee8ce.jpeg', 0, '2025-09-19 06:47:06'),
(94, 12, '/uploads/20250919_064706_4d2f93.jpeg', 1, '2025-09-19 06:47:06'),
(95, 12, '/uploads/20250919_064706_bdbe90.jpeg', 2, '2025-09-19 06:47:06'),
(96, 12, '/uploads/20250919_064706_95a615.jpeg', 3, '2025-09-19 06:47:06'),
(97, 12, '/uploads/20250919_064706_4afa8f.jpeg', 4, '2025-09-19 06:47:06'),
(98, 12, '/uploads/20250919_064706_081060.jpeg', 5, '2025-09-19 06:47:06'),
(99, 12, '/uploads/20250919_064706_e529d7.jpeg', 6, '2025-09-19 06:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `team_header`
--

DROP TABLE IF EXISTS `team_header`;
CREATE TABLE IF NOT EXISTS `team_header` (
  `id` tinyint NOT NULL DEFAULT '1',
  `title` varchar(200) NOT NULL,
  `subheader` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `team_header`
--

INSERT INTO `team_header` (`id`, `title`, `subheader`, `updated_at`) VALUES
(1, 'Meet Our Team', 'Our team is made up of dedicated real estate professionals who share a passion for what they do. We are not just agents; we are your trusted advisors. Our agents are experienced, knowledgeable, and ready to assist you in achieving your real estate goals.', '2025-09-17 11:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `role` varchar(200) NOT NULL,
  `bio` text NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '100',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `role`, `bio`, `photo_path`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Jarred Roloff', 'Director of Development and Asset Management', 'Jarred oversees identifying, evaluating, and executing real estate investment of stabilized and development opportunities. He oversees the coordination of design and engineering services as well as manages financial institution relations to ensure each investment has a strong foundation for success. Jarred graduated with a Bachelor of Science in Construction Management degree and a minor in Business Administration from NDSU (North Dakota State University). He has also received accredited certifications in Stabilize Transactions, Fund and JV Structure, Value Added Transactions, Condominium Transactions, and Opportunistic Transactions.', '/uploads/43be689b578fc4df.png', 1, 1, '2025-09-16 01:55:22', '2025-09-16 01:55:22'),
(3, 'Scott Stoeckel', 'Director of Construction  Services', 'With 20 years of industry experience, Scott assists with analyzing development opportunities on all potential development sites, provides construction cost analysis at budgeting and design development phases of preconstruction and oversees construction operations. Scott graduated with a Bachelor of Science in Construction Management degree and a minor in Business Administration from NDSU (North Dakota State University). He excels in problem solving and getting things done. He believes that connection through communication is how you build trust and ensure transparency with team members and clients.', '/uploads/9996759aa3759a5f.png', 1, 1, '2025-09-16 01:57:58', '2025-09-16 01:57:58'),
(4, 'Kyle Leftwich', 'Director of Acquisitions and Property Management', 'Kyle oversees and identifies development opportunities, leads negotiations of property and development option acquisitions. His extensive knowledge of local and regional real estate markets allows him to actively navigate and identify the best opportunities for investment and to get the deals done. Kyle graduated with a Doctor of Pharmaceutics degree from NDSU (North Dakota State University). He now leads Paramount Real Estate as Broker/Owner and oversees Property Management operations.', '/uploads/95405b7e66c58b10.png', 1, 1, '2025-09-16 01:59:11', '2025-09-16 02:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT '/uploads/default-avatar.jpg',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remember_token_hash` varchar(255) DEFAULT NULL,
  `remember_token_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `avatar`, `created_at`, `updated_at`, `remember_token_hash`, `remember_token_expires`) VALUES
(1, 'Paramount Development Group', 'paramount_admin@example.com', '$2y$12$fq0XvsG9hovuZgv6EtlPRe5yG/RnvU5iQ9fFrf9N9rkL/qBdysIFW', '/uploads/user1.png', '2025-09-23 01:19:13', '2025-09-23 01:21:01', NULL, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD CONSTRAINT `contact_replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_images`
--
ALTER TABLE `project_images`
  ADD CONSTRAINT `fk_proj_imgs_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
