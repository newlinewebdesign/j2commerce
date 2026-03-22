-- uninstall old tour j2commerce-creating-product

DELETE FROM `#__guidedtour_steps`
WHERE `tour_id` = (SELECT `id` FROM `#__guidedtours` WHERE `uid` = 'j2commerce-creating-product');

DELETE FROM `#__guidedtours`
WHERE `uid` = 'j2commerce-creating-product';

-- Guided Tour: Creating Your First Product (17 steps)

INSERT INTO `#__guidedtours` (`title`, `description`, `extensions`, `url`, `published`, `language`, `note`, `access`, `uid`, `autostart`, `created`, `created_by`, `modified`, `modified_by`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_DESC', '["com_j2commerce"]', 'administrator/index.php?option=com_j2commerce&view=products', 1, '*', '', 1, 'com_j2commerce.creating-product', 0, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0
WHERE NOT EXISTS (SELECT * FROM `#__guidedtours` g WHERE g.`uid` = 'com_j2commerce.creating-product');

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP0_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP0_DESC', 'bottom', '#toolbar-new', 2, 1, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP1_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP1_DESC', 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_content&view=article&layout=edit', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP2_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP2_DESC', 'bottom', 'button[aria-controls="attrib-j2commerce"]', 2, 4, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP3_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP3_DESC', 'top', '#j2commerce-product-enabled-radio-group0', 2, 5, '', 1, '*', '', '{"required":1,"requiredvalue":"1"}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP4_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP4_DESC', 'top', '#product_type', 2, 6, '', 1, '*', '', '{"required":1,"requiredvalue":"simple"}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP5_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP5_DESC', 'bottom', '#submit_button', 2, 1, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP6_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP6_DESC', 'bottom', '#j2commerce-product-visibility-radio-group0', 2, 5, '', 1, '*', '', '{"required":0,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP7_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP7_DESC', 'bottom', '#j2commerce-product-sku-group', 2, 2, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP8_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP8_DESC', 'top', 'joomla-field-fancy-select:has(#j2commerce-product-taxprofile_id-select-group)', 2, 6, '', 1, '*', '', '{"required":0,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP9_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP9_DESC', 'top', 'button[aria-controls="pricingTab"]', 2, 4, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP10_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP10_DESC', 'bottom', '#j2commerce-product-price-field', 2, 2, '', 1, '*', '', '{"required":0,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP11_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP11_DESC', 'top', 'button[aria-controls="inventoryTab"]', 2, 4, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP12_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP12_DESC', 'bottom', '#j2commerce-product-manage_stock-radio-group1', 2, 5, '', 1, '*', '', '{"required":0,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP13_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP13_DESC', 'top', 'button[aria-controls="shippingTab"]', 2, 4, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP14_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP14_DESC', 'bottom', '#j2commerce-product-shipping-radio-group1', 2, 5, '', 1, '*', '', '{"required":0,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP15_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP15_DESC', 'bottom', '#save-group-children-save .button-save', 2, 1, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';

INSERT INTO `#__guidedtour_steps` (`title`, `description`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `language`, `note`, `params`, `created`, `created_by`, `modified`, `modified_by`, `tour_id`)
SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP16_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP16_DESC', 'center', '', 0, 1, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, MAX(`id`)
FROM `#__guidedtours`
WHERE `uid` = 'com_j2commerce.creating-product';
