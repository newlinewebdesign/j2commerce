-- Add GrapeJS editor support columns to invoice templates table
ALTER TABLE `#__j2commerce_invoicetemplates`
    ADD COLUMN `body_json` MEDIUMTEXT NULL DEFAULT NULL AFTER `body`,
    ADD COLUMN `body_source` VARCHAR(255) NOT NULL DEFAULT 'editor' AFTER `body_json`,
    ADD COLUMN `body_source_file` VARCHAR(255) NOT NULL DEFAULT '' AFTER `body_source`,
    ADD COLUMN `custom_css` TEXT NULL DEFAULT NULL AFTER `body_source_file`/** CAN FAIL **/;
