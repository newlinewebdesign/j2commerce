-- J2Commerce 6.0.14 - GrapesJS Visual Email Editor
-- Add body_json column for storing GrapesJS project data

ALTER TABLE `#__j2commerce_emailtemplates`
  ADD COLUMN `body_json` MEDIUMTEXT NULL DEFAULT NULL AFTER `body`/** CAN FAIL **/;
