-- --------------------------------------------------------
-- J2Commerce 6.1.0 Database Updates
-- Central Email Hub: Email type tags and contexts tables
-- Gift Certificate: from_order_id column for vouchers
-- --------------------------------------------------------

-- Add from_order_id to vouchers table (for gift certificate tracking)
ALTER TABLE `#__j2commerce_vouchers`
  ADD COLUMN `from_order_id` varchar(255) NOT NULL DEFAULT '0' AFTER `email_body`/** CAN FAIL **/;

-- Add context column to emailtemplates table
ALTER TABLE `#__j2commerce_emailtemplates`
  ADD COLUMN `context` varchar(100) NOT NULL DEFAULT '' AFTER `email_type`/** CAN FAIL **/;

-- Add index on email_type for filtering
ALTER TABLE `#__j2commerce_emailtemplates`
  ADD INDEX `idx_email_type` (`email_type`)/** CAN FAIL **/;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_emailtype_tags`
-- Stores available tags for each email type
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_emailtype_tags` (
  `j2commerce_emailtype_tag_id` int NOT NULL AUTO_INCREMENT,
  `email_type` varchar(255) NOT NULL,
  `tag_name` varchar(100) NOT NULL,
  `tag_label` varchar(255) NOT NULL,
  `tag_description` text,
  `tag_group` varchar(100) DEFAULT 'general',
  `ordering` int DEFAULT 0,
  PRIMARY KEY (`j2commerce_emailtype_tag_id`),
  KEY `idx_email_type` (`email_type`),
  KEY `idx_tag_name` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_emailtype_contexts`
-- Defines contexts for each email type (sent, expired, etc.)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_emailtype_contexts` (
  `j2commerce_emailtype_context_id` int NOT NULL AUTO_INCREMENT,
  `email_type` varchar(255) NOT NULL,
  `context` varchar(100) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text,
  `ordering` int DEFAULT 0,
  PRIMARY KEY (`j2commerce_emailtype_context_id`),
  UNIQUE KEY `idx_type_context` (`email_type`, `context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert default tags for transactional email type
-- --------------------------------------------------------

INSERT IGNORE INTO `#__j2commerce_emailtype_tags`
  (`email_type`, `tag_name`, `tag_label`, `tag_description`, `tag_group`, `ordering`)
VALUES
  ('transactional', 'ORDER_ID', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ORDERID', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ORDERID_DESC', 'order', 1),
  ('transactional', 'ORDER_DATE', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ORDERDATE', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ORDERDATE_DESC', 'order', 2),
  ('transactional', 'ORDER_STATUS', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ORDERSTATUS', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ORDERSTATUS_DESC', 'order', 3),
  ('transactional', 'ORDER_TOTAL', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ORDERAMOUNT', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ORDERAMOUNT_DESC', 'order', 4),
  ('transactional', 'ORDER_SUBTOTAL', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_SUBTOTAL', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_SUBTOTAL_DESC', 'order', 5),
  ('transactional', 'ORDER_TAX', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_TAX_AMOUNT', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_TAX_AMOUNT_DESC', 'order', 6),
  ('transactional', 'ORDER_SHIPPING', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_SHIPPING_AMOUNT', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_SHIPPING_AMOUNT_DESC', 'order', 7),
  ('transactional', 'ORDER_DISCOUNT', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_DISCOUNT_AMOUNT', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_DISCOUNT_AMOUNT_DESC', 'order', 8),
  ('transactional', 'ORDER_ITEMS', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ITEMS', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_ITEMS_DESC', 'order', 9),
  ('transactional', 'CUSTOMER_NAME', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_CUSTOMER_NAME', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_CUSTOMER_NAME_DESC', 'customer', 10),
  ('transactional', 'CUSTOMER_EMAIL', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_CUSTOMER_EMAIL', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_CUSTOMER_EMAIL_DESC', 'customer', 11),
  ('transactional', 'BILLING_ADDRESS', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_BILLING_ADDRESS', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_BILLING_ADDRESS_DESC', 'customer', 12),
  ('transactional', 'SHIPPING_ADDRESS', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_SHIPPING_ADDRESS', 'COM_J2COMMERCE_EMAILTEMPLATE_TAG_SHIPPING_ADDRESS_DESC', 'customer', 13),
  ('transactional', 'PAYMENT_METHOD', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_PAYMENT_METHOD', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_PAYMENT_METHOD_DESC', 'payment', 14),
  ('transactional', 'SHIPPING_METHOD', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_SHIPPING_METHOD', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_SHIPPING_METHOD_DESC', 'shipping', 15),
  ('transactional', 'SITE_NAME', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_SITE_NAME', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_SITE_NAME_DESC', 'store', 16),
  ('transactional', 'SITE_URL', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_STORE_URL', 'COM_J2COMMERCE_EMAILTEMPLATE_SHORTCODE_STORE_URL_DESC', 'store', 17);

-- --------------------------------------------------------
-- Insert default contexts for transactional email type
-- --------------------------------------------------------

INSERT IGNORE INTO `#__j2commerce_emailtype_contexts`
  (`email_type`, `context`, `label`, `description`, `ordering`)
VALUES
  ('transactional', 'order_confirmed', 'COM_J2COMMERCE_EMAILTYPE_CONTEXT_ORDER_CONFIRMED', '', 1),
  ('transactional', 'order_cancelled', 'COM_J2COMMERCE_EMAILTYPE_CONTEXT_ORDER_CANCELLED', '', 2),
  ('transactional', 'order_shipped', 'COM_J2COMMERCE_EMAILTYPE_CONTEXT_ORDER_SHIPPED', '', 3),
  ('transactional', 'order_refunded', 'COM_J2COMMERCE_EMAILTYPE_CONTEXT_ORDER_REFUNDED', '', 4),
  ('transactional', 'payment_received', 'COM_J2COMMERCE_EMAILTYPE_CONTEXT_PAYMENT_RECEIVED', '', 5);
