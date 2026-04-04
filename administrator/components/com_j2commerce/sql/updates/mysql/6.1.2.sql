-- J2Commerce 6.1.2 — Re-apply column fixes that may have failed due to multi-MODIFY ALTER syntax
-- Joomla's ChangeItem schema parser requires one column change per ALTER TABLE statement

-- Fix: productprice_index decimal precision (originally in 6.0.10.sql)
ALTER TABLE `#__j2commerce_productprice_index`
  MODIFY `min_price` decimal(15,5) NOT NULL DEFAULT 0.00000;

ALTER TABLE `#__j2commerce_productprice_index`
  MODIFY `max_price` decimal(15,5) NOT NULL DEFAULT 0.00000;

-- Fix: product_options default values (originally in 6.0.27.sql)
ALTER TABLE `#__j2commerce_product_options`
  MODIFY `parent_id` int NOT NULL DEFAULT 0;

ALTER TABLE `#__j2commerce_product_options`
  MODIFY `ordering` int NOT NULL DEFAULT 0;

ALTER TABLE `#__j2commerce_product_options`
  MODIFY `required` int NOT NULL DEFAULT 0;

ALTER TABLE `#__j2commerce_product_options`
  MODIFY `is_variant` int NOT NULL DEFAULT 0;

-- Fix: addresses default values (originally in 6.1.1.sql)
UPDATE `#__j2commerce_addresses` SET `user_id` = 0 WHERE `user_id` IS NULL;

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
