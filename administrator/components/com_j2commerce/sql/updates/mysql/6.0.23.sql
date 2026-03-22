-- J2Commerce 6.0.23 — Geocode cache table (consolidated from standalone leafletmap plugin)

CREATE TABLE IF NOT EXISTS `#__j2commerce_geocode_cache` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `address_hash` char(32) NOT NULL,
  `address_text` varchar(500) NOT NULL DEFAULT '',
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_address_hash` (`address_hash`),
  KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
