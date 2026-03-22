--
-- J2Commerce 6.0.7 Update
-- Migrate option values data from J2Store to J2Commerce
--

-- Migrate option values from j2store to j2commerce if not already migrated
INSERT INTO `#__j2commerce_optionvalues`
    (`j2commerce_optionvalue_id`, `option_id`, `optionvalue_name`, `optionvalue_image`, `ordering`)
SELECT
    `j2store_optionvalue_id`,
    `option_id`,
    `optionvalue_name`,
    `optionvalue_image`,
    `ordering`
FROM `#__j2store_optionvalues`
WHERE NOT EXISTS (
    SELECT 1 FROM `#__j2commerce_optionvalues`
    WHERE `#__j2commerce_optionvalues`.`j2commerce_optionvalue_id` = `#__j2store_optionvalues`.`j2store_optionvalue_id`
);
