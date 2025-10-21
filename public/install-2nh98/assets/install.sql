-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `status_message` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `avatar` varchar(250) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `auth_groups_users`
CREATE TABLE `auth_groups_users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `group` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_groups_users_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_groups_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `auth_identities`
CREATE TABLE `auth_identities` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `secret` varchar(255) NOT NULL,
  `secret2` varchar(255) DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `extra` text DEFAULT NULL,
  `force_reset` tinyint NOT NULL DEFAULT 0,
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_secret` (`type`,`secret`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_identities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `auth_logins`
CREATE TABLE `auth_logins` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `id_type` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_type_identifier` (`id_type`,`identifier`),
  KEY `user_id` (`user_id`)  
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `auth_permissions_users`
CREATE TABLE `auth_permissions_users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `permission` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_permissions_users_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_permissions_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `auth_remember_tokens`
CREATE TABLE `auth_remember_tokens` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `selector` varchar(255) NOT NULL,
  `hashedValidator` varchar(255) NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `auth_remember_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_remember_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `auth_token_logins`
CREATE TABLE `auth_token_logins` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `id_type` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_type_identifier` (`id_type`,`identifier`),
  KEY `user_id` (`user_id`)  
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `licenses`
CREATE TABLE `licenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `max_allowed_domains` int NOT NULL,
  `max_allowed_devices` int NOT NULL,
  `license_status` enum('pending','active','blocked','expired') NOT NULL DEFAULT 'active',
  `license_type` enum('trial','subscription','lifetime') NOT NULL DEFAULT 'trial',
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `email` varchar(64) NOT NULL,
  `item_reference` varchar(255) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `txn_id` varchar(64) NOT NULL,
  `manual_reset_count` varchar(128) NOT NULL,
  `purchase_id_` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_activated` datetime DEFAULT NULL,
  `date_renewed` datetime DEFAULT NULL,
  `date_expiry` datetime DEFAULT NULL,
  `reminder_sent` varchar(255) NOT NULL DEFAULT '0',
  `reminder_sent_date` datetime DEFAULT NULL,
  `product_ref` varchar(255) NOT NULL,
  `until` varchar(255) NOT NULL,
  `current_ver` varchar(255) NOT NULL,
  `subscr_id` varchar(128) NOT NULL,
  `billing_length` varchar(255) NOT NULL,
  `billing_interval` enum('days','weeks','months','years','onetime') NOT NULL DEFAULT 'days',
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `licenses_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `license_email_list`
CREATE TABLE `license_email_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `sent_to` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `sent` text,
  `date_sent` datetime DEFAULT NULL,
  `disable_notifications` text,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `license_email_list_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `license_email_list_license_key_foreign` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `license_logs`
CREATE TABLE `license_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(255) NOT NULL,
  `is_valid` enum('yes','no') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `license_logs_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `license_logs_license_key_foreign` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `license_registered_devices`
CREATE TABLE `license_registered_devices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `license_key_id` int NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `device_name` text NOT NULL,
  `item_reference` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `license_registered_devices_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `license_registered_devices_license_key_foreign` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`) ON DELETE CASCADE,
  CONSTRAINT `license_registered_devices_license_key_id_foreign` FOREIGN KEY (`license_key_id`) REFERENCES `licenses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `license_registered_domains`
CREATE TABLE `license_registered_domains` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `license_key_id` int NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `domain_name` text NOT NULL,
  `item_reference` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `license_registered_domains_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `license_registered_domains_license_key_foreign` FOREIGN KEY (`license_key`) REFERENCES `licenses` (`license_key`) ON DELETE CASCADE,
  CONSTRAINT `license_registered_domains_license_key_id_foreign` FOREIGN KEY (`license_key_id`) REFERENCES `licenses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `migrations`
CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `settings` (Global Application Settings)
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `class` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `context` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `settings_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `user_settings` (Per-Tenant Settings for SaaS)
CREATE TABLE `user_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `setting_name` varchar(255) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_setting` (`user_id`, `setting_name`),
  KEY `idx_user_setting` (`user_id`, `setting_name`),
  CONSTRAINT `user_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert system user (ID = 0) for global settings
-- Temporarily disable AUTO_INCREMENT to allow manual ID insertion
ALTER TABLE `users` AUTO_INCREMENT = 0;
INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `status`, `status_message`, `active`, `created_at`, `updated_at`) VALUES
(0, 'system', 'System', 'Admin', 'active', 'System administrator', 1, NOW(), NOW());
-- Reset AUTO_INCREMENT for regular user registration
ALTER TABLE `users` AUTO_INCREMENT = 1;

--
-- Dumping data for table `settings`
-- Note: Global application settings with owner_id = 0 for system-wide configuration
-- Individual tenant settings should use user_settings table instead
--

INSERT INTO `settings` (`owner_id`, `class`, `key`, `value`, `type`, `context`) VALUES
(0, 'Config\\App', 'appName', 'MERAF Production Panel SaaS', 'string', NULL),
(0, 'Config\\App', 'appVersion', '1.0.0', 'string', NULL),
(0, 'Config\\App', 'companyName', 'MERAF Digital Solutions', 'string', NULL),
(0, 'Config\\App', 'companyAddress', 'MERAF Digital Solutions Address', 'string', NULL),
(0, 'Config\\App', 'fromName', 'MERAF Production Panel', 'string', NULL),
(0, 'Config\\App', 'fromEmail', 'no-reply@{{domain_name}}', 'string', NULL),
(0, 'Config\\App', 'supportName', 'MERAF Support Team', 'string', NULL),
(0, 'Config\\App', 'supportEmail', 'support@{{domain_name}}', 'string', NULL),
(0, 'Config\\App', 'salesName', 'MERAF Sales Team', 'string', NULL),
(0, 'Config\\App', 'salesEmail', 'sales@{{domain_name}}', 'string', NULL),
(0, 'Config\\App', 'cacheHandler', 'file', 'string', NULL),
(0, 'Config\\App', 'userProductPath', 'products/', 'string', NULL),
(0, 'Config\\App', 'userEmailTemplatesPath', 'email-templates/', 'string', NULL),
(0, 'Config\\App', 'userLogsPath', 'logs/', 'string', NULL),
(0, 'Config\\App', 'userAppSettings', 'settings/', 'string', NULL),
(0, 'Config\\App', 'License_Invalid_Log_FileName', 'Invalid-License-List.csv', 'string', NULL),
(0, 'Config\\App', 'License_Valid_Log_FileName', 'Valid-License-List.csv', 'string', NULL),
(0, 'Config\\App', 'appLogo_light', '', 'string', NULL),
(0, 'Config\\App', 'appLogo_dark', '', 'string', NULL),
(0, 'Config\\App', 'appIcon', '', 'string', NULL),
(0, 'Config\\App', 'defaultTimezone', 'UTC', 'string', NULL),
(0, 'Config\\App', 'defaultLocale', 'en', 'string', NULL),
(0, 'Config\\App', 'defaultTheme', 'system', 'string', NULL),
(0, 'Config\\Tasks', 'enabled', '1', 'boolean', NULL),
(0, 'Config\\Tasks', 'log-autoexpiry-license', NULL, 'array', NULL),
(0, 'Config\\Tasks', 'log-remind-expiring-license', NULL, 'array', NULL),
(0, 'Config\\Tasks', 'log-check-abusive-ips', NULL, 'array', NULL),
(0, 'Config\\Tasks', 'log-clean-blocked-ips', NULL, 'array', NULL),
(0, 'Config\\App', 'reCAPTCHA_enabled', NULL, 'string', NULL),
(0, 'Config\\App', 'reCAPTCHA_Site_Key', NULL, 'string', NULL),
(0, 'Config\\App', 'reCAPTCHA_Secret_Key', NULL, 'string', NULL),
(0, 'Config\\App', 'preloadEnabled', '', 'string', NULL),
(0, 'Config\\App', 'packageCurrency', 'USD', 'string', NULL),
(0, 'Config\\App', 'htmlEmailFooter', '<div class=\"footer\" style=\"background:#f8f9fa;padding:10px;text-align:center;font-size:12px;color:#6c757d;border-radius:0 0 5px 5px;margin-top:10px\">\r\n<p>Simplify your licensing & digital product management with <strong>{app_name}</strong>, your all-in-one solution for license and digital product management—brought to you by <strong>{company_name}</strong>.</p>\r\n<p style=\"text-align:center\"><a href=\"{app_url}\" style=\"display:inline-block;padding:10px 20px;text-decoration:none;border-radius:3px;margin:10px;color:#fff;background-color:#007bff\">Discover More</a></p>\r\n</div>', 'string', NULL),
(0, 'Config\\App', 'textEmailFooter', '==========\r\nSimplify your licensing & digital product management with {app_name}, your all-in-one solution for license and digital product management—brought to you by {company_name} ({app_url})', 'string', NULL),
(0, 'Config\\App', 'PAYPAL_MODE', 'sandbox', 'string', NULL),
(0, 'Config\\App', 'PAYPAL_SANDBOX_CLIENT_ID', '', 'string', NULL),
(0, 'Config\\App', 'PAYPAL_SANDBOX_CLIENT_SECRET', '', 'string', NULL),
(0, 'Config\\App', 'PAYPAL_SANDBOX_WEBHOOK_ID', '', 'string', NULL),
(0, 'Config\\App', 'PAYPAL_LIVE_CLIENT_ID', '', 'string', NULL),
(0, 'Config\\App', 'PAYPAL_LIVE_CLIENT_SECRET', '', 'string', NULL),
(0, 'Config\\App', 'PAYPAL_LIVE_WEBHOOK_ID', '', 'string', NULL),
(0, 'Config\\App', 'PWA_App_enabled', NULL, 'string', NULL),
(0, 'Config\\App', 'PWA_App_name', 'MERAF Production Panel', 'string', NULL),
(0, 'Config\\App', 'PWA_App_shortname', 'ProdPanel', 'string', NULL),
(0, 'Config\\App', 'PWA_App_icon_192x192', '', 'string', NULL),
(0, 'Config\\App', 'PWA_App_icon_512x512', '', 'string', NULL),
(0, 'Config\\App', 'push_notification_feature_enabled', NULL, 'string', NULL),
(0, 'Config\\App', 'push_notification_badge', '', 'string', NULL),
(0, 'Config\\App', 'fcm_apiKey', NULL, 'string', NULL),
(0, 'Config\\App', 'fcm_authDomain', NULL, 'string', NULL),
(0, 'Config\\App', 'fcm_projectId', NULL, 'string', NULL),
(0, 'Config\\App', 'fcm_storageBucket', NULL, 'string', NULL),
(0, 'Config\\App', 'fcm_messagingSenderId', NULL, 'string', NULL),
(0, 'Config\\App', 'fcm_appId', NULL, 'string', NULL),
(0, 'Config\\App', 'fcm_measurementId', NULL, 'string', NULL),
(0, 'Config\\App', 'fcm_vapidKey', NULL, 'string', NULL),
(0, 'Config\\App', 'fcm_private_key_file', NULL, 'string', NULL);

-- Table structure for `module_category`
CREATE TABLE `module_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_name` (`category_name`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_status` (`status`),
  CONSTRAINT `chk_category_sort_order` CHECK (`sort_order` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `module_category`
--

INSERT INTO `module_category` (`category_name`, `description`, `sort_order`, `status`) VALUES
('License_Management', 'License_Management_description', 1, 'active'),
('Digital_Product_Management', 'Digital_Product_Management_description', 2, 'active'),
('Email_Features', 'Email_Features_description', 1, 'active');

-- Table structure for `package`
CREATE TABLE `package` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `validity` int NOT NULL,
  `validity_duration` enum('day','week','month','year','lifetime') NOT NULL DEFAULT 'month',
  `visible` enum('on','off') NOT NULL DEFAULT 'on',
  `highlight` enum('on','off') NOT NULL DEFAULT 'off',
  `is_default` enum('on','off') NOT NULL DEFAULT 'off',
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `sort_order` int DEFAULT 0,
  `package_modules` JSON DEFAULT NULL COMMENT 'JSON format: {"module_name": {"enabled": boolean, "settings": object}}',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_owner_package` (`owner_id`, `package_name`),
  KEY `owner_id` (`owner_id`),
  KEY `idx_status_visible` (`status`, `visible`),
  KEY `idx_is_default` (`is_default`),
  KEY `idx_sort_order` (`sort_order`),
  CONSTRAINT `package_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_package_price` CHECK (`price` >= 0),
  CONSTRAINT `chk_package_validity` CHECK (`validity` > 0),
  CONSTRAINT `chk_package_sort_order` CHECK (`sort_order` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping default data for table `package`
--

INSERT INTO `package` (`owner_id`, `package_name`, `price`, `validity`, `validity_duration`, `visible`, `highlight`, `is_default`, `status`, `sort_order`, `package_modules`) VALUES
(1, 'Super Admin', 0.00, 1, 'lifetime', 'off', 'off', 'off', 'active', 0, '{\"Email_Features\": {\"No_Email_Footer_Message\": {\"value\": \"true\", \"enabled\": \"true\"}}, \"License_Management\": {\"License_Prefix\": {\"value\": \"true\", \"enabled\": \"true\"}, \"License_Suffix\": {\"value\": \"true\", \"enabled\": \"true\"}}, \"Digital_Product_Management\": {\"File_Storage\": {\"value\": \"1000\", \"enabled\": \"true\"}, \"Product_Count_Limit\": {\"value\": \"1000\", \"enabled\": \"true\"}}}'),
(1, 'Trial', 0.00, 14, 'day', 'off', 'off', 'on', 'active', 1, '{\"Email_Features\": {\"No_Email_Footer_Message\": {\"value\": \"false\", \"enabled\": \"false\"}}, \"License_Management\": {\"License_Prefix\": {\"value\": \"true\", \"enabled\": \"true\"}, \"License_Suffix\": {\"value\": \"false\", \"enabled\": \"false\"}}, \"Digital_Product_Management\": {\"File_Storage\": {\"value\": \"20\", \"enabled\": \"true\"}, \"Product_Count_Limit\": {\"value\": \"2\", \"enabled\": \"true\"}}}');

-- Table structure for `package_modules`
CREATE TABLE `package_modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `package_id` int NOT NULL,
  `module_category_id` int NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_description` text DEFAULT NULL,
  `is_enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `measurement_unit` JSON DEFAULT NULL COMMENT 'Module specific settings in JSON format (type, label, description, unit, icon and etc)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_package_module` (`package_id`, `module_category_id`, `module_name`),
  KEY `idx_module_category` (`module_category_id`),
  KEY `idx_module_name` (`module_name`),
  CONSTRAINT `fk_package_modules_category` FOREIGN KEY (`module_category_id`) REFERENCES `module_category` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping default data for table `package_modules`
--

INSERT INTO `package_modules` (`package_id`, `module_category_id`, `module_name`, `module_description`, `measurement_unit`, `is_enabled`) VALUES
(100, 1, 'License_Prefix', 'License_Prefix_description', '{"type":"checkbox","label":"Enable License Prefix","description":"Allows custom prefix for license keys","unit":"Enabled","icon":""}', 'yes'),
(101, 1, 'License_Suffix', 'License_Suffix_description', '{"type":"checkbox","label":"Enable License Suffix","description":"Allows custom suffix for license keys","unit":"Enabled","icon":""}', 'yes'),
(102, 1, 'Envato_Sync', 'Envato_Sync_description', '{"type":"checkbox","label":"Enable Envato Envato purchase code integration","description":"Allows customers to activate their license by entering a valid Envato purchase code.","unit":"Enabled","icon":""}', 'yes'),
(201, 2, 'Product_Count_Limit', 'Product_Count_Limit_description', '{"type":"number","label":"Product Count Limit","description":"Maximum number of products allowed","unit":"Count","icon":"hash","min":1,"max":1000,"step":1,"default":10}', 'yes'),
(202, 2, 'File_Storage', 'File_Storage_description', '{"type":"number","label":"File Storage Limit (MB)","description":"Maximum storage space for the products in megabytes","unit":"MB","icon":"hard-drive","min":1,"max":10000,"step":1,"default":100}', 'yes'),
(300, 3, 'No_Email_Footer_Message', 'Email_Footer_Message_description', '{"type":"checkbox","label":"Remove email footer message","description":"Disables the promotional footer message in outgoing emails to maintain a cleaner, professional look.","unit":"Enabled","icon":""}', 'yes');

-- Table structure for `subscriptions`
-- Tracks the main subscription information
-- Links to users and packages
-- Handles subscription status and payment information
-- Manages billing cycles and important dates
-- Includes payment provider information

CREATE TABLE `subscriptions` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NULL COMMENT 'Can be null during webhook processing',
  `package_id` int NOT NULL,
  `subscription_status` enum('active','cancelled','expired','pending','failed','suspended') NOT NULL DEFAULT 'pending',
  `is_reactivated` enum('yes','no') NOT NULL DEFAULT 'no',
  `payment_status` enum('pending','completed','failed','refunded','partially_refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `transaction_token` varchar(255) DEFAULT NULL COMMENT 'The temporary transaction token from the payment provider to pay for the subscription',
  `subscription_reference` varchar(100) NOT NULL COMMENT 'Subscription ID',
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` enum('day','week','month','year','lifetime') NOT NULL COMMENT 'Matches payment method subscription intervals',
  `billing_period` int NOT NULL DEFAULT 1,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_reason` varchar(500) DEFAULT NULL,
  `last_payment_date` datetime DEFAULT NULL,
  `next_payment_date` datetime DEFAULT NULL,
  `retry_count` int UNSIGNED DEFAULT 0,
  `next_retry_date` datetime DEFAULT NULL,
  `retry_dates` JSON DEFAULT NULL,
  `last_payment_failure_reason` varchar(500) DEFAULT NULL,
  `sent_expiring_reminder` enum('yes','no') NOT NULL DEFAULT 'no',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_reference` (`subscription_reference`),
  KEY `idx_user_subscription` (`user_id`),
  KEY `idx_package_subscription` (`package_id`),
  KEY `idx_subscription_status` (`subscription_status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_payment_method` (`payment_method`),
  KEY `idx_payment_dates` (`last_payment_date`, `next_payment_date`),
  KEY `idx_user_status` (`user_id`, `subscription_status`),
  KEY `idx_next_payment_status` (`next_payment_date`, `subscription_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_subscription_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_subscription_package` FOREIGN KEY (`package_id`) REFERENCES `package` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_subscription_amount` CHECK (`amount_paid` >= 0),
  CONSTRAINT `chk_subscription_billing_period` CHECK (`billing_period` > 0),
  CONSTRAINT `chk_subscription_retry_count` CHECK (`retry_count` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subscription_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subscription_id` varchar(100) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded','partially_refunded') NOT NULL DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `refund_id` varchar(100) DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_currency` char(3) DEFAULT NULL,
  `refund_date` datetime DEFAULT NULL,
  `is_partial_refund` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `payment_status` (`payment_status`),
  KEY `payment_date` (`payment_date`),
  CONSTRAINT `fk_subscription_payment` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`subscription_reference`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `subscription_invoices`
-- Manages billing and payment records
-- Stores invoice details and payment status
-- Tracks transaction information
-- Includes billing details in JSON format for flexibility

CREATE TABLE `subscription_invoices` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscription_id` int UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `billing_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `paid_date` datetime DEFAULT NULL,
  `billing_details` JSON DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_subscription_invoice` (`subscription_id`),
  KEY `idx_invoice_status` (`payment_status`),
  CONSTRAINT `fk_invoice_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `subscription_changes`
-- Tracks package changes (upgrades/downgrades)
-- Records subscription renewal history
-- Maintains cancellation information
-- Preserves historical package changes
CREATE TABLE `subscription_changes` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscription_id` int UNSIGNED NOT NULL,
  `previous_package_id` int DEFAULT NULL,
  `new_package_id` int NOT NULL,
  `change_type` enum('upgrade','downgrade','renewal','cancellation') NOT NULL,
  `reason` text DEFAULT NULL,
  `effective_date` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subscription_change` (`subscription_id`),
  KEY `idx_previous_package` (`previous_package_id`),
  KEY `idx_new_package` (`new_package_id`),
  CONSTRAINT `fk_change_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_change_previous_package` FOREIGN KEY (`previous_package_id`) REFERENCES `package` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_change_new_package` FOREIGN KEY (`new_package_id`) REFERENCES `package` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `ip_block`
CREATE TABLE `ip_block` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL COMMENT 'Supports both IPv4 and IPv6',
  `license_key` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_ip_block_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `email_logs`
CREATE TABLE `email_logs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `to` varchar(64) NOT NULL,
  `from` varchar(64) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `format` enum('html','text') NOT NULL DEFAULT 'html',
  `body` longtext NOT NULL,
  `plain_text_message` longtext NOT NULL,
  `headers` text,
  `attachments` JSON DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `response` text,
  `extra` text,
  `retries` int UNSIGNED DEFAULT 0,
  `resent_count` int UNSIGNED DEFAULT 0,
  `source` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_owner_id` (`owner_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_email_logs_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `notifications`
CREATE TABLE `notifications` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255),
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `envato_purchases` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` int UNSIGNED NOT NULL,
  `purchase_code` varchar(255) NOT NULL,
  `item_id` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `buyer_username` varchar(255) DEFAULT NULL,
  `buyer_email` varchar(255) DEFAULT NULL,
  `purchase_date` datetime DEFAULT NULL,
  `license_type` varchar(50) DEFAULT NULL,
  `support_until` datetime DEFAULT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT 0,
  `license_created` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  UNIQUE KEY `purchase_code` (`purchase_code`),
  CONSTRAINT `envato_purchases_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fcm_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `device_id` varchar(64) DEFAULT NULL,
  `device` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_used` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `idx_device_id` (`device_id`),
  CONSTRAINT `fcm_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add trigger to prevent multiple active subscriptions per user
-- This prevents race conditions in subscription creation
CREATE TRIGGER prevent_multiple_active_subscriptions
BEFORE INSERT ON subscriptions
FOR EACH ROW
BEGIN
    DECLARE active_count INT DEFAULT 0;

    IF NEW.subscription_status = 'active' THEN
        SELECT COUNT(*) INTO active_count
        FROM subscriptions
        WHERE user_id = NEW.user_id
        AND subscription_status = 'active';

        IF active_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User already has an active subscription';
        END IF;
    END IF;
END;

CREATE TRIGGER prevent_multiple_active_subscriptions_update
BEFORE UPDATE ON subscriptions
FOR EACH ROW
BEGIN
    DECLARE active_count INT DEFAULT 0;

    IF NEW.subscription_status = 'active' AND OLD.subscription_status != 'active' THEN
        SELECT COUNT(*) INTO active_count
        FROM subscriptions
        WHERE user_id = NEW.user_id
        AND subscription_status = 'active'
        AND id != NEW.id;

        IF active_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User already has an active subscription';
        END IF;
    END IF;
END;

-- Table for tracking subscription usage analytics
CREATE TABLE `subscription_usage_tracking` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `subscription_id` int UNSIGNED NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `usage_count` int UNSIGNED NOT NULL DEFAULT 0,
  `limit_value` int UNSIGNED DEFAULT NULL,
  `usage_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_daily_usage` (`user_id`, `subscription_id`, `feature_name`, `usage_date`),
  KEY `idx_user_feature_date` (`user_id`, `feature_name`, `usage_date`),
  KEY `idx_subscription_usage` (`subscription_id`),
  KEY `idx_usage_date` (`usage_date`),
  CONSTRAINT `fk_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_usage_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_usage_count` CHECK (`usage_count` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for tracking subscription state changes
CREATE TABLE `subscription_state_log` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscription_id` int UNSIGNED NOT NULL,
  `old_status` enum('active','cancelled','expired','pending','failed','suspended') DEFAULT NULL,
  `new_status` enum('active','cancelled','expired','pending','failed','suspended') NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `changed_by` int UNSIGNED DEFAULT NULL COMMENT 'User ID who made the change',
  `change_source` enum('user','admin','system','webhook','cronjob') NOT NULL DEFAULT 'system',
  `metadata` JSON DEFAULT NULL COMMENT 'Additional change metadata',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subscription_log` (`subscription_id`),
  KEY `idx_status_change` (`old_status`, `new_status`),
  KEY `idx_change_source` (`change_source`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_state_log_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_state_log_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
