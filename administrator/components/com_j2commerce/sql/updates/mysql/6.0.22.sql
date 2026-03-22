-- Update "Creating Your First Product" guided tour to walk through the article editor
-- with J2Commerce tab fields for a simple product.

-- Update tour URL to start on the products list page
UPDATE `#__guidedtours`
   SET `url` = 'administrator/index.php?option=com_j2commerce&view=products'
 WHERE `uid` = 'j2commerce-creating-product';

-- Remove old steps (safe: DELETE with subquery returns 0 rows if tour doesn't exist)
DELETE FROM `#__guidedtour_steps`
 WHERE `tour_id` = (SELECT `id` FROM `#__guidedtours` WHERE `uid` = 'j2commerce-creating-product');

-- Insert new steps (15 steps walking through products list -> article editor -> J2Commerce tabs)
-- Uses INSERT INTO ... SELECT to avoid NULL tour_id error when tour doesn't exist
INSERT INTO `#__guidedtour_steps` (`tour_id`, `title`, `description`, `ordering`, `position`, `target`, `type`, `interactive_type`, `url`, `published`, `created`, `modified`, `language`, `note`, `params`)
SELECT t.`id`, s.`title`, s.`description`, s.`ordering`, s.`position`, s.`target`, s.`type`, s.`interactive_type`, s.`url`, s.`published`, NOW(), NOW(), '*', '', '{}'
FROM `#__guidedtours` t
CROSS JOIN (
    SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP1_TITLE' AS `title`, 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP1_DESC' AS `description`, 1 AS `ordering`, 'bottom' AS `position`, '' AS `target`, 0 AS `type`, 1 AS `interactive_type`, '' AS `url`, 1 AS `published`
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP2_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP2_DESC', 2, 'bottom', '#toolbar-new button, #toolbar-new a', 2, 3, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP3_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP3_DESC', 3, 'bottom', '#jform_title', 2, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP4_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP4_DESC', 4, 'bottom', '#j2commercetab-generalTab-tab', 2, 3, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP5_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP5_DESC', 5, 'bottom', '#j2commerce-product-visibility-radio-group', 2, 4, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP6_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP6_DESC', 6, 'bottom', '#j2commerce-product-sku-group', 2, 1, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP7_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP7_DESC', 7, 'bottom', '#j2commerce-product-taxprofile_id-select-group', 0, 1, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP8_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP8_DESC', 8, 'bottom', '#j2commercetab-pricingTab-tab', 2, 3, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP9_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP9_DESC', 9, 'bottom', '#j2commerce-product-price-field', 2, 1, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP10_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP10_DESC', 10, 'bottom', '#j2commercetab-inventoryTab-tab', 2, 3, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP11_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP11_DESC', 11, 'bottom', '#j2commerce-product-manage_stock-radio-group', 0, 1, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP12_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP12_DESC', 12, 'bottom', '#j2commercetab-shippingTab-tab', 2, 3, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP13_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP13_DESC', 13, 'bottom', '#j2commerce-product-shipping-radio-group', 0, 1, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP14_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP14_DESC', 14, 'bottom', '#j2commerce-product-weight', 0, 1, '', 1
    UNION ALL SELECT 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP15_TITLE', 'COM_J2COMMERCE_GUIDEDTOUR_CREATING_PRODUCT_STEP15_DESC', 15, 'bottom', '#toolbar-apply button', 2, 3, '', 1
) s
WHERE t.`uid` = 'j2commerce-creating-product';
