-- Fix ordering column: make NOT NULL with default 0
ALTER TABLE `#__j2commerce_customfields`
  MODIFY `ordering` int NOT NULL DEFAULT 0;

-- Add field_width column for per-field CSS width customization
ALTER TABLE `#__j2commerce_customfields`
  ADD COLUMN `field_width` varchar(20) DEFAULT '' AFTER `field_autocomplete`/** CAN FAIL **/;
