-- --------------------------------------------------------
-- J2Commerce 6.1.1 Database Updates
-- Fix: Add DEFAULT '' to all columns in addresses table
-- Prevents MySQL strict mode error 1364 when inserting without all columns
-- --------------------------------------------------------

-- Add campaign_addr_id column if missing (older installs)
ALTER TABLE `#__j2commerce_addresses`
  ADD COLUMN `campaign_addr_id` varchar(255) NOT NULL DEFAULT ''/** CAN FAIL **/;

-- Clean existing NULL values before changing to NOT NULL DEFAULT ''
UPDATE `#__j2commerce_addresses` SET `user_id` = 0 WHERE `user_id` IS NULL;
UPDATE `#__j2commerce_addresses` SET `first_name` = '' WHERE `first_name` IS NULL;
UPDATE `#__j2commerce_addresses` SET `last_name` = '' WHERE `last_name` IS NULL;
UPDATE `#__j2commerce_addresses` SET `email` = '' WHERE `email` IS NULL;
UPDATE `#__j2commerce_addresses` SET `address_1` = '' WHERE `address_1` IS NULL;
UPDATE `#__j2commerce_addresses` SET `address_2` = '' WHERE `address_2` IS NULL;
UPDATE `#__j2commerce_addresses` SET `city` = '' WHERE `city` IS NULL;
UPDATE `#__j2commerce_addresses` SET `zip` = '' WHERE `zip` IS NULL;
UPDATE `#__j2commerce_addresses` SET `zone_id` = '' WHERE `zone_id` IS NULL;
UPDATE `#__j2commerce_addresses` SET `country_id` = '' WHERE `country_id` IS NULL;
UPDATE `#__j2commerce_addresses` SET `phone_1` = '' WHERE `phone_1` IS NULL;
UPDATE `#__j2commerce_addresses` SET `phone_2` = '' WHERE `phone_2` IS NULL;
UPDATE `#__j2commerce_addresses` SET `fax` = '' WHERE `fax` IS NULL;
UPDATE `#__j2commerce_addresses` SET `type` = '' WHERE `type` IS NULL;
UPDATE `#__j2commerce_addresses` SET `company` = '' WHERE `company` IS NULL;
UPDATE `#__j2commerce_addresses` SET `tax_number` = '' WHERE `tax_number` IS NULL;
UPDATE `#__j2commerce_addresses` SET `campaign_addr_id` = '' WHERE `campaign_addr_id` IS NULL;

-- Now add proper defaults to all columns (one ALTER per column for Joomla schema checker compatibility)
ALTER TABLE `#__j2commerce_addresses`
  MODIFY `user_id` int NOT NULL DEFAULT 0;

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `first_name` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `last_name` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `email` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `address_1` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `address_2` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `city` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `zip` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `zone_id` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `country_id` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `phone_1` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `phone_2` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `fax` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `type` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `company` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `tax_number` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__j2commerce_addresses`
  MODIFY `campaign_addr_id` varchar(255) NOT NULL DEFAULT '';
