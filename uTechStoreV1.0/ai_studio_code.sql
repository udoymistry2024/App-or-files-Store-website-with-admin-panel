-- প্রথমে ডাটাবেস তৈরি করা হচ্ছে (যদি আগে থেকে না থাকে)
CREATE DATABASE IF NOT EXISTS `app_store_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- সদ্য তৈরি করা ডাটাবেসটি ব্যবহারের জন্য নির্বাচন করা হচ্ছে
USE `app_store_db`;

--
-- টেবিলের গঠন: `admins`
-- অ্যাডমিন এবং ওনারদের তথ্য রাখার জন্য
--
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','admin') NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- টেবিলের গঠন: `categories`
-- অ্যাপের ধরণগুলো রাখার জন্য
--
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- কিছু ডিফল্ট ক্যাটাগরি যোগ করা হচ্ছে
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Android Games'),
(2, 'Android Apps'),
(3, 'PC Games'),
(4, 'PC Apps');

--
-- টেবিলের গঠন: `apps`
-- সমস্ত অ্যাপ, গেম এবং সফটওয়্যারের তথ্য রাখার জন্য
--
CREATE TABLE `apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_code` varchar(20) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image1` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `youtube_link` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`unique_code`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `apps_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- টেবিলের গঠন: `contact_details`
-- فوٹر-এর কন্টাক্ট লিঙ্কগুলো রাখার জন্য
--
CREATE TABLE `contact_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon_class` varchar(100) NOT NULL,
  `link_url` varchar(255) NOT NULL,
  `display_text` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- টেবিলের গঠন: `invitation_codes`
-- নতুন অ্যাডমিনদের ইনভাইট করার জন্য
--
CREATE TABLE `invitation_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- টেবিলের গঠন: `security_codes`
-- ওনারের পাসওয়ার্ড রিসেট করার জন্য
--
CREATE TABLE `security_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `security_codes_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- টেবিলের গঠন: `settings`
-- ওয়েবসাইটের সাধারণ সেটিংস রাখার জন্য
--
CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ডিফল্ট সেটিংস যোগ করা হচ্ছে
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('copyright_text', '© 2025 Your App Store. All Rights Reserved.'),
('site_logo', 'assets/images/default_logo.png'),
('site_name', 'My App Store'),
('whatsapp_enabled', '1'),
('whatsapp_number', '');