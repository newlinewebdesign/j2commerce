ALTER TABLE `#__j2commerce_customfields`
  ADD COLUMN `field_placeholder` varchar(250) DEFAULT NULL AFTER `field_default`,
  ADD COLUMN `field_autocomplete` varchar(100) DEFAULT NULL AFTER `field_placeholder`/** CAN FAIL **/;

UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_FIRSTNAME', `field_autocomplete` = 'given-name' WHERE `field_namekey` = 'first_name';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_LASTNAME', `field_autocomplete` = 'family-name' WHERE `field_namekey` = 'last_name';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_EMAIL', `field_autocomplete` = 'email' WHERE `field_namekey` = 'email';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_ADDRESS_1', `field_autocomplete` = 'address-line1' WHERE `field_namekey` = 'address_1';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_ADDRESS_2', `field_autocomplete` = 'address-line2' WHERE `field_namekey` = 'address_2';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_CITY', `field_autocomplete` = 'address-level2' WHERE `field_namekey` = 'city';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_ZIP', `field_autocomplete` = 'postal-code' WHERE `field_namekey` = 'zip';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_PHONE', `field_autocomplete` = 'tel' WHERE `field_namekey` = 'phone_1';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_MOBILE', `field_autocomplete` = 'tel' WHERE `field_namekey` = 'phone_2';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_COMPANY', `field_autocomplete` = 'organization' WHERE `field_namekey` = 'company';
UPDATE `#__j2commerce_customfields` SET `field_placeholder` = 'J2COMMERCE_PLACEHOLDER_TAX_NUMBER', `field_autocomplete` = 'off' WHERE `field_namekey` = 'tax_number';

-- Fix any records with empty field_table (should be 'address' for checkout visibility)
UPDATE `#__j2commerce_customfields` SET `field_table` = 'address' WHERE `field_table` = '' OR `field_table` IS NULL;
