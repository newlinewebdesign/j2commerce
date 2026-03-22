-- J2Commerce 6.1.3 — Add missing paymentprofiles table for payment gateway integrations

CREATE TABLE IF NOT EXISTS `#__j2commerce_paymentprofiles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'authorizenet',
  `customer_profile_id` varchar(50) NOT NULL,
  `environment` varchar(10) NOT NULL DEFAULT 'production',
  `is_default` tinyint unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_customer_profile_id` (`customer_profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
