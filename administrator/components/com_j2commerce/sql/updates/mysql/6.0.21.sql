-- Queue table schema upgrade (idempotent — safe for fresh install and re-runs)
-- Column type changes are safe to re-run (MODIFY is idempotent)
ALTER TABLE `#__j2commerce_queues`
  MODIFY `queue_type` varchar(100) NOT NULL COMMENT 'Plugin identifier: shipstation, quickbooks, avalara';

ALTER TABLE `#__j2commerce_queues`
  MODIFY `queue_data` mediumtext NOT NULL COMMENT 'JSON payload for the processor';

ALTER TABLE `#__j2commerce_queues`
  MODIFY `params` mediumtext DEFAULT NULL COMMENT 'Additional parameters';

ALTER TABLE `#__j2commerce_queues`
  MODIFY `priority` tinyint NOT NULL DEFAULT 0 COMMENT 'Higher = processed first';

ALTER TABLE `#__j2commerce_queues`
  MODIFY `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, processing, completed, failed, dead';

-- created_on and modified_on already have CURRENT_TIMESTAMP defaults from CREATE TABLE
-- Joomla's schema checker cannot parse DEFAULT CURRENT_TIMESTAMP in MODIFY statements

-- Create queue logs table (IF NOT EXISTS = safe for re-run and fresh install)
CREATE TABLE IF NOT EXISTS `#__j2commerce_queue_logs` (
  `j2commerce_queue_log_id` int unsigned NOT NULL AUTO_INCREMENT,
  `queue_type` varchar(100) NOT NULL COMMENT 'Which queue was processed',
  `task_id` int unsigned DEFAULT NULL COMMENT 'Joomla scheduler task ID, if applicable',
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `duration_ms` int unsigned DEFAULT NULL COMMENT 'Execution duration in milliseconds',
  `items_total` smallint unsigned NOT NULL DEFAULT 0,
  `items_success` smallint unsigned NOT NULL DEFAULT 0,
  `items_failed` smallint unsigned NOT NULL DEFAULT 0,
  `items_skipped` smallint unsigned NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'running' COMMENT 'running, completed, error',
  `error_message` text DEFAULT NULL,
  `details` mediumtext DEFAULT NULL COMMENT 'JSON array of per-item results',
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`j2commerce_queue_log_id`),
  KEY `idx_queue_type` (`queue_type`),
  KEY `idx_status` (`status`),
  KEY `idx_started_at` (`started_at`),
  KEY `idx_created_on` (`created_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
