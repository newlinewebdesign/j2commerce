--
-- Issue #636: Reorder core address fields so country/zone render after city/zip
-- and address_1/address_2 render first (US/Canada default convention).
--
-- Old install ordering had country/zone (3/4) between the address lines and
-- city/zip (5/6). This migration shifts them so the rendered order becomes:
--   email, first_name, last_name, address_1, address_2, city, zip,
--   country, zone, phone_1, phone_2, company, tax_number
--
-- Non-US/CA stores should re-run the onboarding country step (or manually
-- swap address_1 and address_2) to pick up the "unit first" convention; the
-- old reorderAddressFields() helper used colliding values (4/5) and is fixed
-- alongside this update.
--

UPDATE `#__j2commerce_customfields` SET `ordering` = 1 WHERE `field_namekey` = 'address_1' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `ordering` = 2 WHERE `field_namekey` = 'address_2' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `ordering` = 3 WHERE `field_namekey` = 'city' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `ordering` = 4 WHERE `field_namekey` = 'zip' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `ordering` = 5 WHERE `field_namekey` = 'country_id' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `ordering` = 6 WHERE `field_namekey` = 'zone_id' AND `field_core` = 1;

--
-- Default Bootstrap 5 column widths for core address fields.
-- Ensures existing installs pick up the intended paired half-columns and
-- full-width address lines that match the fresh install layout.
--
UPDATE `#__j2commerce_customfields` SET `field_width` = 'col-md-6' WHERE `field_namekey` = 'last_name' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `field_width` = 'col-12'   WHERE `field_namekey` = 'address_1' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `field_width` = 'col-12'   WHERE `field_namekey` = 'address_2' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `field_width` = 'col-md-6' WHERE `field_namekey` = 'city' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `field_width` = 'col-md-6' WHERE `field_namekey` = 'zip' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `field_width` = 'col-md-6' WHERE `field_namekey` = 'country_id' AND `field_core` = 1;
UPDATE `#__j2commerce_customfields` SET `field_width` = 'col-md-6' WHERE `field_namekey` = 'zone_id' AND `field_core` = 1;
