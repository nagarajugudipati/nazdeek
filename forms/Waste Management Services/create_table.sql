-- CREATE TABLE QUERY FOR Waste Management Services
-- Place this table inside the 'nazdeek' database.

CREATE DATABASE IF NOT EXISTS `nazdeek` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nazdeek`;

CREATE TABLE IF NOT EXISTS `waste_management_services_leads` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `mobile` VARCHAR(10) NOT NULL,
    `email` VARCHAR(100) NULL,
    `city` VARCHAR(100) NOT NULL,
    `pincode` VARCHAR(6) NOT NULL,
    `locality` VARCHAR(255) NOT NULL,
    `service_type` VARCHAR(255) NOT NULL,
    `property_type` VARCHAR(255) NOT NULL,
    `waste_volume` VARCHAR(255) NOT NULL,
    `collection_frequency` VARCHAR(255) NOT NULL,
    `site_address` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL,
    `requirements` TEXT NOT NULL,
    `preferred_time` VARCHAR(50) NULL,
    `budget` DECIMAL(10, 2) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
