-- VK Tent Services Database Schema
-- Samastam Technologies Private Limited
-- Version 1.0 | June 2026

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table: admin_users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `reset_token` VARCHAR(64) NULL DEFAULT NULL,
  `reset_expires` DATETIME NULL DEFAULT NULL,
  `last_login` DATETIME NULL DEFAULT NULL,
  `login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: username=admin@vktent.com  password=Admin@1234 (change on first login)
INSERT INTO `admin_users` (`name`, `email`, `password_hash`) VALUES
('Vivek Shukla', 'admin@vktent.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------
-- Table: services
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`title`, `description`, `sort_order`) VALUES
('Tent Setup', 'Professional tent installation for all types of events — weddings, receptions, corporate gatherings and social functions.', 1),
('Decoration', 'Elegant floral and fabric decoration that transforms any venue into a stunning event space matching your theme.', 2),
('Lighting', 'Atmospheric LED and traditional lighting arrangements to set the perfect mood for your occasion.', 3),
('Stage Setup', 'Custom stage fabrication and dressing for weddings, concerts, award nights, and corporate events.', 4),
('Seating Arrangements', 'Premium seating layouts — banquet, theatre, classroom or custom — for any guest count.', 5),
('Catering Support Setup', 'Full catering-support infrastructure: service counters, utensil stands, food stall canopies and more.', 6);

-- --------------------------------------------------------
-- Table: gallery_categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(80) NOT NULL UNIQUE,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gallery_categories` (`name`, `slug`, `sort_order`) VALUES
('Weddings', 'weddings', 1),
('Functions', 'functions', 2),
('Corporate Events', 'corporate', 3),
('Decorations', 'decorations', 4),
('Stage Setups', 'stage-setups', 5);

-- --------------------------------------------------------
-- Table: gallery_media
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_media` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NULL DEFAULT NULL,
  `type` ENUM('image','video') NOT NULL DEFAULT 'image',
  `file_path` VARCHAR(255) NOT NULL,
  `thumbnail` VARCHAR(255) NULL DEFAULT NULL,
  `title` VARCHAR(120) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_media_category` (`category_id`),
  CONSTRAINT `fk_media_category` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: enquiries
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NULL DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `event_type` VARCHAR(80) NOT NULL,
  `venue` VARCHAR(200) NOT NULL,
  `message` TEXT NULL,
  `status` ENUM('new','confirmed','rejected','completed') NOT NULL DEFAULT 'new',
  `notes` TEXT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add ip_address to existing installs (MariaDB/MySQL 8.0+ supports IF NOT EXISTS for columns)
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `ip_address` VARCHAR(45) NULL DEFAULT NULL AFTER `message`;

-- --------------------------------------------------------
-- Table: bookings
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enquiry_id` INT UNSIGNED NULL DEFAULT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NULL DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `venue` VARCHAR(200) NOT NULL,
  `event_type` VARCHAR(80) NOT NULL,
  `status` ENUM('upcoming','held','cancelled') NOT NULL DEFAULT 'upcoming',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_booking_enquiry` (`enquiry_id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_booking_enquiry` FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: payments
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `paid_on` DATE NOT NULL,
  `method` VARCHAR(50) NULL DEFAULT NULL,
  `note` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_payment_booking` (`booking_id`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: blocked_dates
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blocked_dates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blocked_date` DATE NOT NULL UNIQUE,
  `reason` VARCHAR(200) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blocked_date` (`blocked_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: settings
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(80) NOT NULL UNIQUE,
  `value` TEXT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`, `value`) VALUES
('business_name', 'VK Tent Services'),
('tagline', 'Your Event, Our Expertise — Every Detail, Perfectly Placed'),
('owner_name', 'Vivek Shukla'),
('owner_photo', ''),
('about_text', 'With over a decade of experience in tent setup, decoration, and event management, VK Tent Services has been the trusted choice for weddings, corporate events, and social functions across the region. We bring passion, precision, and professionalism to every event we handle.'),
('experience_years', '10+'),
('area_served', 'Allahabad & Surrounding Districts'),
('phone', '+91 98765 43210'),
('whatsapp', '+919876543210'),
('email', 'info@vktentservices.com'),
('address', 'Near Civil Lines, Prayagraj, Uttar Pradesh 211001'),
('facebook', ''),
('instagram', ''),
('youtube', ''),
('session_timeout', '3600'),
('max_login_attempts', '5');

SET FOREIGN_KEY_CHECKS = 1;
