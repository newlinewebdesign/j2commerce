-- Add hits column for product view tracking (popularity)
ALTER TABLE `#__j2commerce_products` ADD COLUMN `hits` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `productfilter_ids`/** CAN FAIL **/;

-- Add index for sorting by popularity
ALTER TABLE `#__j2commerce_products` ADD INDEX `idx_hits` (`hits`)/** CAN FAIL **/;
