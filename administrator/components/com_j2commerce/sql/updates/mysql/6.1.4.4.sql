-- J2Commerce 6.1.7
-- Normalise `enabled` columns to tinyint NOT NULL DEFAULT 0
-- and fix `ordering` column types across all tables


-- Remove guided tours related to payment and shipping setup

DELETE FROM `#__guidedtour_steps`
WHERE `tour_id` = (SELECT `id` FROM `#__guidedtours` WHERE `uid` = 'com_j2commerce.setting-up-payments');

DELETE FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.setting-up-payments';

DELETE FROM `#__guidedtour_steps`
WHERE `tour_id` = (SELECT `id` FROM `#__guidedtours` WHERE `uid` = 'com_j2commerce.configuring-shipping');

DELETE FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.configuring-shipping';

-- -------------------------------------------------------
-- Pre-update: clear NULLs in columns that become NOT NULL
-- -------------------------------------------------------

UPDATE `#__j2commerce_customfields` SET `enabled` = 0 WHERE `enabled`  IS NULL/** CAN FAIL **/;
UPDATE `#__j2commerce_products` SET `enabled` = 0 WHERE `enabled`  IS NULL/** CAN FAIL **/;
UPDATE `#__j2commerce_product_optionvalues` SET `ordering` = 0 WHERE `ordering` IS NULL/** CAN FAIL **/;

-- -------------------------------------------------------
-- ALTER statements
-- -------------------------------------------------------

-- addresses: int -> tinyint (DEFAULT stays 1)
ALTER TABLE `#__j2commerce_addresses`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 1/** CAN FAIL **/;

-- countries: int NOT NULL (no default) -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_countries`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- coupons: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_coupons`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- currencies: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_currencies`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- customfields: int DEFAULT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_customfields`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- emailtemplates: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_emailtemplates`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- emailtype_tags: ordering int DEFAULT 0 -> int NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_emailtype_tags`
    MODIFY `ordering` int NOT NULL DEFAULT 0/** CAN FAIL **/;

-- emailtype_contexts: ordering int DEFAULT 0 -> int NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_emailtype_contexts`
    MODIFY `ordering` int NOT NULL DEFAULT 0/** CAN FAIL **/;

-- filtergroups: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_filtergroups`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- geozones: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_geozones`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- invoicetemplates: tinyint NOT NULL (no default) -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_invoicetemplates`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- lengths: enabled int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_lengths`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- lengths: ordering int NOT NULL DEFAULT 1 -> int NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_lengths`
    MODIFY `ordering` int NOT NULL DEFAULT 0/** CAN FAIL **/;

-- manufacturers: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_manufacturers`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- options: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_options`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- orderstatuses: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_orderstatuses`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- products: int DEFAULT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_products`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- product_options: ordering tinyint NOT NULL DEFAULT 0 -> int NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_product_options`
    MODIFY `ordering` int NOT NULL DEFAULT 0/** CAN FAIL **/;

-- product_optionvalues: ordering tinyint DEFAULT '0' -> int NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_product_optionvalues`
    MODIFY `ordering` int NOT NULL DEFAULT 0/** CAN FAIL **/;

-- taxprofiles: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_taxprofiles`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- taxrates: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_taxrates`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- vendors: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_vendors`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- vouchers: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_vouchers`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- weights: enabled int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_weights`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;

-- weights: ordering int NOT NULL DEFAULT 1 -> int NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_weights`
    MODIFY `ordering` int NOT NULL DEFAULT 0/** CAN FAIL **/;

-- zones: int NOT NULL -> tinyint NOT NULL DEFAULT 0
ALTER TABLE `#__j2commerce_zones`
    MODIFY `enabled` tinyint NOT NULL DEFAULT 0/** CAN FAIL **/;
