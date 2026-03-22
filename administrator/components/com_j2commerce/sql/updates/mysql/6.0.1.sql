-- J2Commerce 6.0.1 Update
-- Fix: Create missing tables that failed to install due to deprecated float(M,D) syntax
-- This update creates all tables from currencies onwards that were not created in 6.0.0

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_currencies`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_currencies` (
  `j2commerce_currency_id` int NOT NULL AUTO_INCREMENT,
  `currency_title` varchar(32) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `currency_position` varchar(12) NOT NULL,
  `currency_symbol` varchar(255) NOT NULL,
  `currency_num_decimals` int NOT NULL,
  `currency_decimal` varchar(12) NOT NULL,
  `currency_thousands` char(1) NOT NULL,
  `currency_value` decimal(15,8) NOT NULL,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL,
  `created_on` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` bigint unsigned NOT NULL DEFAULT 0,
  `modified_on` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` bigint unsigned NOT NULL DEFAULT 0,
  `locked_on` datetime DEFAULT NULL,
  `locked_by` bigint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`j2commerce_currency_id`),
  UNIQUE KEY `currency_code` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_customfields`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_customfields` (
  `j2commerce_customfield_id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `field_table` varchar(50) DEFAULT NULL,
  `field_name` varchar(250) NOT NULL,
  `field_namekey` varchar(50) NOT NULL,
  `field_type` varchar(50) DEFAULT NULL,
  `field_value` longtext NOT NULL,
  `enabled` int DEFAULT NULL,
  `ordering` int DEFAULT NULL,
  `field_options` text,
  `field_core` tinyint unsigned NOT NULL DEFAULT 0,
  `field_required` tinyint unsigned NOT NULL DEFAULT 0,
  `field_default` varchar(250) DEFAULT NULL,
  `field_access` varchar(255) NOT NULL DEFAULT 'all',
  `field_categories` varchar(255) NOT NULL DEFAULT 'all',
  `field_with_sub_categories` tinyint NOT NULL DEFAULT 0,
  `field_frontend` tinyint unsigned NOT NULL DEFAULT 0,
  `field_backend` tinyint unsigned NOT NULL DEFAULT 1,
  `field_display` text NOT NULL,
  `field_display_billing` smallint NOT NULL DEFAULT 0,
  `field_display_register` smallint NOT NULL DEFAULT 0,
  `field_display_shipping` smallint NOT NULL DEFAULT 0,
  `field_display_guest` smallint NOT NULL DEFAULT 0,
  `field_display_guest_shipping` smallint NOT NULL DEFAULT 0,
  `field_display_payment` smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (`j2commerce_customfield_id`),
  UNIQUE KEY `field_namekey` (`field_namekey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default custom fields data
INSERT IGNORE INTO `#__j2commerce_customfields` (`j2commerce_customfield_id`, `field_table`, `field_name`, `field_namekey`, `field_type`, `field_value`, `enabled`, `ordering`, `field_options`, `field_core`, `field_required`, `field_default`, `field_access`, `field_categories`, `field_with_sub_categories`, `field_frontend`, `field_backend`, `field_display`, `field_display_billing`, `field_display_register`, `field_display_shipping`, `field_display_guest`, `field_display_guest_shipping`, `field_display_payment`) VALUES
(1, 'address', 'J2COMMERCE_ADDRESS_FIRSTNAME', 'first_name', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(2, 'address', 'J2COMMERCE_ADDRESS_LASTNAME', 'last_name', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(3, 'address', 'J2COMMERCE_EMAIL', 'email', 'email', '', 1, 99, 'a:8:{s:12:"errormessage";s:38:"J2COMMERCE_VALIDATION_ENTER_VALID_EMAIL";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 0, 1, 0, 0),
(4, 'address', 'J2COMMERCE_ADDRESS_LINE1', 'address_1', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(5, 'address', 'J2COMMERCE_ADDRESS_LINE2', 'address_2', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 0, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(6, 'address', 'J2COMMERCE_ADDRESS_CITY', 'city', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(7, 'address', 'J2COMMERCE_ADDRESS_ZIP', 'zip', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(8, 'address', 'J2COMMERCE_ADDRESS_PHONE', 'phone_1', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:0:"";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 0, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(9, 'address', 'J2COMMERCE_ADDRESS_MOBILE', 'phone_2', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(10, 'address', 'J2COMMERCE_ADDRESS_COMPANY_NAME', 'company', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:0:"";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 0, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(11, 'address', 'J2COMMERCE_ADDRESS_TAX_NUMBER', 'tax_number', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:0:"";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 0, '', 'all', 'all', 0, 0, 1, '', 1, 1, 0, 1, 0, 0),
(12, 'address', 'J2COMMERCE_ADDRESS_COUNTRY', 'country_id', 'zone', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '223', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(13, 'address', 'J2COMMERCE_ADDRESS_ZONE', 'zone_id', 'zone', '', 1, 99, 'a:8:{s:12:"errormessage";s:24:"J2COMMERCE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:4:"zone";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '3624', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0);

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_emailtemplates`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_emailtemplates` (
  `j2commerce_emailtemplate_id` int NOT NULL AUTO_INCREMENT,
  `email_type` varchar(255) NOT NULL,
  `receiver_type` varchar(255) NOT NULL DEFAULT '*',
  `orderstatus_id` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  `paymentmethod` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `body_source` varchar(255) NOT NULL,
  `body_source_file` varchar(255) NOT NULL,
  `language` varchar(10) NOT NULL DEFAULT '*',
  `enabled` int NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_emailtemplate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default email template
INSERT IGNORE INTO `#__j2commerce_emailtemplates` (`j2commerce_emailtemplate_id`, `email_type`, `orderstatus_id`, `group_id`, `paymentmethod`, `subject`, `body`, `language`, `enabled`, `ordering`) VALUES
(1, '', '*', '', '*', 'Hello [BILLING_FIRSTNAME] [BILLING_LASTNAME], your order has been placed with [SITENAME]', '<table style="width: 100%;" border="0" cellspacing="0" cellpadding="2"><tbody><tr valign="top"><td colspan="2" rowspan="1"><p>Hello [BILLING_FIRSTNAME] [BILLING_LASTNAME], we thank you for placing an order with [SITENAME]. Your Order ID is:<strong>[ORDERID]</strong>. We have now started processing your order. The details of your order are as follows:</p></td></tr><tr valign="top"><td><h3>Order Information</h3><p><strong>Order ID: </strong>[ORDERID]</p><p><strong>Invoice Number: </strong>[INVOICENO]</p><p><strong>Date: </strong>[ORDERDATE]</p><p><strong>Order Amount: </strong>[ORDERAMOUNT]</p><p><strong>Order Status: </strong>[ORDERSTATUS]</p></td><td><h3>Customer Information</h3><p>[BILLING_FIRSTNAME] [BILLING_LASTNAME]</p><p>[BILLING_ADDRESS_1] [BILLING_ADDRESS_2]</p><p>[BILLING_CITY], [BILLING_ZIP]</p><p>[BILLING_STATE] [BILLING_COUNTRY]</p><p>[BILLING_PHONE] [BILLING_MOBILE]</p><p>[BILLING_COMPANY]</p></td></tr><tr valign="top"><td><h3>Payment Information</h3><p><strong>Payment Type: </strong>[PAYMENT_TYPE]</p></td><td><h3>Shipping Information</h3><p>[SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]</p><p>[SHIPPING_ADDRESS_1] [SHIPPING_ADDRESS_2]</p><p>[SHIPPING_CITY], [SHIPPING_ZIP]</p><p>[SHIPPING_STATE] [SHIPPING_COUNTRY]</p><p>[SHIPPING_PHONE] [SHIPPING_MOBILE]</p><p>[SHIPPING_COMPANY]</p><p>[SHIPPING_METHOD]</p></td></tr><tr valign="top"><td colspan="2" rowspan="1"><p>[ITEMS]</p></td></tr><tr valign="top"><td colspan="2"><p>For any queries and details please get in touch with us. We will be glad to be of service. You can also view the order details by visiting [INVOICE_URL]</p><p>You can use your email address and the following token to view the order [ORDER_TOKEN]</p></td></tr></tbody></table>', '*', 1, 1);

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_filtergroups`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_filtergroups` (
  `j2commerce_filtergroup_id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `ordering` int NOT NULL,
  `enabled` int NOT NULL,
  PRIMARY KEY (`j2commerce_filtergroup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_filters`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_filters` (
  `j2commerce_filter_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `filter_name` varchar(255) DEFAULT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_filter_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_geozones`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_geozones` (
  `j2commerce_geozone_id` int NOT NULL AUTO_INCREMENT,
  `geozone_name` varchar(255) NOT NULL,
  `enabled` int NOT NULL,
  PRIMARY KEY (`j2commerce_geozone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_geozonerules`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_geozonerules` (
  `j2commerce_geozonerule_id` int NOT NULL AUTO_INCREMENT,
  `geozone_id` int NOT NULL,
  `country_id` int NOT NULL,
  `zone_id` int NOT NULL,
  PRIMARY KEY (`j2commerce_geozonerule_id`),
  UNIQUE KEY `georule` (`geozone_id`,`country_id`,`zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_invoicetemplates`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_invoicetemplates` (
  `j2commerce_invoicetemplate_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `invoice_type` varchar(255) NOT NULL,
  `orderstatus_id` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  `paymentmethod` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `language` varchar(10) NOT NULL DEFAULT '*',
  `enabled` tinyint NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_invoicetemplate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_lengths`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_lengths` (
  `j2commerce_length_id` int NOT NULL AUTO_INCREMENT,
  `length_title` varchar(255) NOT NULL,
  `length_unit` varchar(4) NOT NULL,
  `length_value` decimal(15,8) NOT NULL,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`j2commerce_length_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_manufacturers`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_manufacturers` (
  `j2commerce_manufacturer_id` int NOT NULL AUTO_INCREMENT,
  `address_id` int NOT NULL,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_manufacturer_id`),
  KEY `address_id` (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_metafields`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_metafields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `metakey` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `metavalue` text NOT NULL,
  `valuetype` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `owner_id` int unsigned NOT NULL,
  `owner_resource` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `metafields_owner_id_index` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_options`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_options` (
  `j2commerce_option_id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `option_unique_name` varchar(255) NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `ordering` int NOT NULL,
  `enabled` int NOT NULL,
  `option_params` text,
  PRIMARY KEY (`j2commerce_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_optionvalues`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_optionvalues` (
  `j2commerce_optionvalue_id` int NOT NULL AUTO_INCREMENT,
  `option_id` int NOT NULL,
  `optionvalue_name` varchar(255) NOT NULL,
  `optionvalue_image` longtext NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_optionvalue_id`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_orderdiscounts`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_orderdiscounts` (
  `j2commerce_orderdiscount_id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `discount_type` varchar(255) NOT NULL,
  `discount_entity_id` int unsigned NOT NULL,
  `discount_title` varchar(255) NOT NULL,
  `discount_code` varchar(255) NOT NULL,
  `discount_value` varchar(255) NOT NULL,
  `discount_value_type` varchar(255) NOT NULL,
  `discount_customer_email` varchar(255) NOT NULL,
  `user_id` int unsigned NOT NULL,
  `discount_amount` decimal(15,5) NOT NULL,
  `discount_tax` decimal(15,5) NOT NULL,
  `discount_params` text NOT NULL,
  PRIMARY KEY (`j2commerce_orderdiscount_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_orderdownloads`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_orderdownloads` (
  `j2commerce_orderdownload_id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `product_id` int NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `limit_count` bigint NOT NULL,
  `access_granted` datetime NOT NULL,
  `access_expires` datetime NOT NULL,
  PRIMARY KEY (`j2commerce_orderdownload_id`),
  KEY `download_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_orderhistories`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_orderhistories` (
  `j2commerce_orderhistory_id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `order_state_id` int NOT NULL,
  `notify_customer` int NOT NULL,
  `comment` text NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`j2commerce_orderhistory_id`),
  KEY `history_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_orderinfos`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_orderinfos` (
  `j2commerce_orderinfo_id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `billing_company` varchar(255) DEFAULT NULL,
  `billing_last_name` varchar(255) DEFAULT NULL,
  `billing_first_name` varchar(255) DEFAULT NULL,
  `billing_middle_name` varchar(255) DEFAULT NULL,
  `billing_phone_1` varchar(255) DEFAULT NULL,
  `billing_phone_2` varchar(255) DEFAULT NULL,
  `billing_fax` varchar(255) DEFAULT NULL,
  `billing_address_1` varchar(255) NOT NULL DEFAULT '',
  `billing_address_2` varchar(255) DEFAULT NULL,
  `billing_city` varchar(255) NOT NULL DEFAULT '',
  `billing_zone_name` varchar(255) NOT NULL DEFAULT '',
  `billing_country_name` varchar(255) NOT NULL DEFAULT '',
  `billing_zone_id` int NOT NULL DEFAULT 0,
  `billing_country_id` int NOT NULL DEFAULT 0,
  `billing_zip` varchar(255) NOT NULL DEFAULT '',
  `billing_tax_number` varchar(255) DEFAULT NULL,
  `shipping_company` varchar(255) DEFAULT NULL,
  `shipping_last_name` varchar(255) DEFAULT NULL,
  `shipping_first_name` varchar(255) DEFAULT NULL,
  `shipping_middle_name` varchar(255) DEFAULT NULL,
  `shipping_phone_1` varchar(255) DEFAULT NULL,
  `shipping_phone_2` varchar(255) DEFAULT NULL,
  `shipping_fax` varchar(255) DEFAULT NULL,
  `shipping_address_1` varchar(255) NOT NULL DEFAULT '',
  `shipping_address_2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(255) NOT NULL DEFAULT '',
  `shipping_zip` varchar(255) NOT NULL,
  `shipping_zone_name` varchar(255) NOT NULL DEFAULT '',
  `shipping_country_name` varchar(255) NOT NULL DEFAULT '',
  `shipping_zone_id` int NOT NULL DEFAULT 0,
  `shipping_country_id` int NOT NULL DEFAULT 0,
  `shipping_id` varchar(255) NOT NULL DEFAULT '',
  `shipping_tax_number` varchar(255) DEFAULT NULL,
  `all_billing` longtext NOT NULL,
  `all_shipping` longtext NOT NULL,
  `all_payment` longtext NOT NULL,
  PRIMARY KEY (`j2commerce_orderinfo_id`),
  KEY `idx_orderinfo_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_orderitemattributes`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_orderitemattributes` (
  `j2commerce_orderitemattribute_id` int NOT NULL AUTO_INCREMENT,
  `orderitem_id` int NOT NULL,
  `productattributeoption_id` int NOT NULL,
  `productattributeoptionvalue_id` int NOT NULL,
  `orderitemattribute_name` varchar(255) NOT NULL,
  `orderitemattribute_value` varchar(255) NOT NULL,
  `orderitemattribute_prefix` varchar(1) NOT NULL,
  `orderitemattribute_price` decimal(15,5) NOT NULL,
  `orderitemattribute_code` varchar(255) NOT NULL,
  `orderitemattribute_type` varchar(255) NOT NULL,
  PRIMARY KEY (`j2commerce_orderitemattribute_id`),
  KEY `attribute_orderitem_id` (`orderitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_orderitems`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_orderitems` (
  `j2commerce_orderitem_id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `cart_id` int NOT NULL,
  `cartitem_id` int unsigned NOT NULL,
  `product_id` int NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `variant_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `orderitem_sku` varchar(255) NOT NULL,
  `orderitem_name` varchar(255) NOT NULL,
  `orderitem_attributes` text NOT NULL,
  `orderitem_quantity` varchar(255) NOT NULL,
  `orderitem_taxprofile_id` int NOT NULL,
  `orderitem_per_item_tax` decimal(15,5) NOT NULL,
  `orderitem_tax` decimal(15,5) NOT NULL,
  `orderitem_discount` decimal(15,5) NOT NULL,
  `orderitem_discount_tax` decimal(15,5) NOT NULL,
  `orderitem_price` decimal(15,5) NOT NULL,
  `orderitem_option_price` decimal(15,5) NOT NULL,
  `orderitem_finalprice` decimal(15,5) NOT NULL,
  `orderitem_finalprice_with_tax` decimal(15,5) NOT NULL,
  `orderitem_finalprice_without_tax` decimal(15,5) NOT NULL,
  `orderitem_params` text NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int NOT NULL,
  `orderitem_weight` varchar(255) NOT NULL,
  `orderitem_weight_total` varchar(255) NOT NULL,
  PRIMARY KEY (`j2commerce_orderitem_id`),
  KEY `orderitem_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_orders`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_orders` (
  `j2commerce_order_id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `cart_id` int unsigned NOT NULL,
  `invoice_prefix` varchar(255) NOT NULL,
  `invoice_number` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `order_total` decimal(15,5) NOT NULL,
  `order_subtotal` decimal(15,5) NOT NULL,
  `order_tax` decimal(15,5) NOT NULL,
  `order_shipping` decimal(15,5) NOT NULL,
  `order_shipping_tax` decimal(15,5) NOT NULL,
  `order_discount` decimal(15,5) NOT NULL,
  `order_credit` decimal(15,5) NOT NULL,
  `order_surcharge` decimal(15,5) NOT NULL,
  `orderpayment_type` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `transaction_status` varchar(255) NOT NULL,
  `transaction_details` text NOT NULL,
  `currency_id` int NOT NULL,
  `currency_code` varchar(255) NOT NULL,
  `currency_value` decimal(15,5) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `is_shippable` int NOT NULL,
  `is_including_tax` int NOT NULL,
  `customer_note` text NOT NULL,
  `customer_language` varchar(255) NOT NULL,
  `customer_group` varchar(255) NOT NULL,
  `order_state_id` int NOT NULL,
  `order_state` varchar(255) NOT NULL COMMENT 'Legacy compatibility',
  `created_on` datetime NOT NULL,
  `created_by` int NOT NULL,
  `modified_on` datetime NOT NULL,
  `modified_by` int NOT NULL,
  PRIMARY KEY (`j2commerce_order_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_ordershippings`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_ordershippings` (
  `j2commerce_ordershipping_id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL DEFAULT '0',
  `ordershipping_type` varchar(255) NOT NULL DEFAULT '' COMMENT 'Element name of shipping plugin',
  `ordershipping_price` decimal(15,5) DEFAULT 0.00000,
  `ordershipping_name` varchar(255) NOT NULL DEFAULT '',
  `ordershipping_code` varchar(255) NOT NULL DEFAULT '',
  `ordershipping_tax` decimal(15,5) DEFAULT 0.00000,
  `ordershipping_extra` decimal(15,5) DEFAULT 0.00000,
  `ordershipping_tracking_id` mediumtext NOT NULL,
  PRIMARY KEY (`j2commerce_ordershipping_id`),
  KEY `idx_order_shipping_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_orderstatuses`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_orderstatuses` (
  `j2commerce_orderstatus_id` int NOT NULL AUTO_INCREMENT,
  `orderstatus_name` varchar(32) NOT NULL,
  `orderstatus_cssclass` text NOT NULL,
  `orderstatus_core` int NOT NULL DEFAULT 0,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_orderstatus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default order statuses
INSERT IGNORE INTO `#__j2commerce_orderstatuses` (`j2commerce_orderstatus_id`, `orderstatus_name`, `orderstatus_cssclass`, `orderstatus_core`, `enabled`, `ordering`) VALUES
(1, 'J2COMMERCE_CONFIRMED', 'badge text-bg-success', 1, 1, 1),
(2, 'J2COMMERCE_PROCESSED', 'badge text-bg-info', 1, 1, 2),
(3, 'J2COMMERCE_FAILED', 'badge text-bg-danger', 1, 1, 3),
(4, 'J2COMMERCE_PENDING', 'badge text-bg-warning', 1, 1, 4),
(5, 'J2COMMERCE_NEW', 'badge text-bg-warning', 1, 1, 5),
(6, 'J2COMMERCE_CANCELLED', 'badge text-bg-secondary', 1, 1, 6),
(7, 'J2COMMERCE_SHIPPED', 'badge text-bg-success', 1, 1, 7);

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_ordertaxes`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_ordertaxes` (
  `j2commerce_ordertax_id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `ordertax_title` varchar(255) NOT NULL,
  `ordertax_percent` decimal(15,5) NOT NULL,
  `ordertax_amount` decimal(15,5) NOT NULL,
  PRIMARY KEY (`j2commerce_ordertax_id`),
  KEY `ordertax_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_productfiles`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_productfiles` (
  `j2commerce_productfile_id` int NOT NULL AUTO_INCREMENT,
  `product_file_display_name` varchar(255) NOT NULL,
  `product_file_save_name` varchar(255) NOT NULL,
  `product_id` int NOT NULL,
  `download_total` int NOT NULL,
  PRIMARY KEY (`j2commerce_productfile_id`),
  KEY `productfile_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_product_filters`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_product_filters` (
  `product_id` int NOT NULL,
  `filter_id` int NOT NULL,
  PRIMARY KEY (`product_id`,`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_productimages`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_productimages` (
  `j2commerce_productimage_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `main_image` text,
  `main_image_alt` varchar(255) NOT NULL,
  `thumb_image` text,
  `thumb_image_alt` varchar(255) NOT NULL,
  `additional_images` longtext,
  `additional_images_alt` longtext,
  PRIMARY KEY (`j2commerce_productimage_id`),
  KEY `productimage_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_productquantities`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_productquantities` (
  `j2commerce_productquantity_id` int NOT NULL AUTO_INCREMENT,
  `product_attributes` text NOT NULL COMMENT 'A CSV of productattributeoption_id values, always in numerical order',
  `variant_id` int NOT NULL,
  `quantity` int NOT NULL,
  `on_hold` int NOT NULL,
  `sold` int NOT NULL,
  PRIMARY KEY (`j2commerce_productquantity_id`),
  UNIQUE KEY `variantidx` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_products`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_products` (
  `j2commerce_product_id` int NOT NULL AUTO_INCREMENT,
  `visibility` int NOT NULL,
  `product_source` varchar(255) DEFAULT NULL,
  `product_source_id` int DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `taxprofile_id` int DEFAULT NULL,
  `manufacturer_id` int DEFAULT NULL,
  `vendor_id` int DEFAULT NULL,
  `has_options` int DEFAULT NULL,
  `addtocart_text` varchar(255) NOT NULL,
  `enabled` int DEFAULT NULL,
  `plugins` text,
  `params` text,
  `created_on` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `modified_on` varchar(45) DEFAULT NULL,
  `modified_by` int DEFAULT NULL,
  `up_sells` varchar(255) NOT NULL,
  `cross_sells` varchar(255) NOT NULL,
  `productfilter_ids` varchar(255) NOT NULL,
  PRIMARY KEY (`j2commerce_product_id`),
  UNIQUE KEY `catalogsource` (`product_source`,`product_source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_product_options`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_product_options` (
  `j2commerce_productoption_id` int NOT NULL AUTO_INCREMENT,
  `option_id` int NOT NULL,
  `parent_id` int NOT NULL,
  `product_id` int NOT NULL,
  `ordering` tinyint NOT NULL,
  `required` int NOT NULL,
  `is_variant` int NOT NULL,
  PRIMARY KEY (`j2commerce_productoption_id`),
  KEY `productoption_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_product_optionvalues`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_product_optionvalues` (
  `j2commerce_product_optionvalue_id` int NOT NULL AUTO_INCREMENT,
  `productoption_id` int NOT NULL,
  `optionvalue_id` int DEFAULT NULL,
  `parent_optionvalue` text NOT NULL,
  `product_optionvalue_price` decimal(15,8) NOT NULL,
  `product_optionvalue_prefix` varchar(255) NOT NULL,
  `product_optionvalue_weight` decimal(15,8) NOT NULL,
  `product_optionvalue_weight_prefix` varchar(255) NOT NULL,
  `product_optionvalue_sku` varchar(255) NOT NULL,
  `product_optionvalue_default` int NOT NULL,
  `ordering` tinyint DEFAULT '0',
  `product_optionvalue_attribs` text NOT NULL,
  PRIMARY KEY (`j2commerce_product_optionvalue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_product_prices`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_product_prices` (
  `j2commerce_productprice_id` int NOT NULL AUTO_INCREMENT,
  `variant_id` int DEFAULT NULL,
  `quantity_from` decimal(15,5) DEFAULT NULL,
  `quantity_to` decimal(15,5) DEFAULT NULL,
  `date_from` datetime DEFAULT NULL,
  `date_to` datetime DEFAULT NULL,
  `customer_group_id` int DEFAULT NULL,
  `price` decimal(15,5) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`j2commerce_productprice_id`),
  KEY `price_variant_id` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_productprice_index`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_productprice_index` (
  `product_id` int NOT NULL,
  `min_price` decimal(15,5) NOT NULL DEFAULT 0.00000,
  `max_price` decimal(15,5) NOT NULL DEFAULT 0.00000,
  PRIMARY KEY (`product_id`),
  KEY `min_price` (`min_price`),
  KEY `max_price` (`max_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_product_variant_optionvalues`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_product_variant_optionvalues` (
  `variant_id` int NOT NULL,
  `product_optionvalue_ids` varchar(255) NOT NULL,
  PRIMARY KEY (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_queues`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_queues` (
  `j2commerce_queue_id` int NOT NULL AUTO_INCREMENT,
  `relation_id` varchar(255) NOT NULL,
  `queue_type` varchar(255) NOT NULL,
  `queue_data` longtext NOT NULL,
  `params` longtext NOT NULL,
  `priority` int NOT NULL,
  `status` varchar(255) NOT NULL,
  `expired` datetime NOT NULL,
  `created_on` varchar(255) DEFAULT NULL,
  `modified_on` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`j2commerce_queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_shippingmethods`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_shippingmethods` (
  `j2commerce_shippingmethod_id` int NOT NULL AUTO_INCREMENT,
  `shipping_method_name` varchar(255) NOT NULL,
  `published` tinyint NOT NULL,
  `shipping_method_type` tinyint NOT NULL,
  `tax_class_id` int NOT NULL,
  `address_override` varchar(255) NOT NULL,
  `subtotal_minimum` decimal(15,3) NOT NULL,
  `subtotal_maximum` decimal(15,3) NOT NULL,
  PRIMARY KEY (`j2commerce_shippingmethod_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_shippingrates`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_shippingrates` (
  `j2commerce_shippingrate_id` int NOT NULL AUTO_INCREMENT,
  `shipping_method_id` int NOT NULL,
  `geozone_id` int NOT NULL,
  `shipping_rate_price` decimal(15,5) NOT NULL,
  `shipping_rate_weight_start` decimal(11,3) NOT NULL,
  `shipping_rate_weight_end` decimal(11,3) NOT NULL,
  `shipping_rate_handling` decimal(15,5) NOT NULL,
  `created_date` datetime NOT NULL COMMENT 'GMT Only',
  `modified_date` datetime NOT NULL COMMENT 'GMT Only',
  PRIMARY KEY (`j2commerce_shippingrate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_taxprofiles`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_taxprofiles` (
  `j2commerce_taxprofile_id` int NOT NULL AUTO_INCREMENT,
  `taxprofile_name` varchar(255) NOT NULL,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_taxprofile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_taxrates`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_taxrates` (
  `j2commerce_taxrate_id` int NOT NULL AUTO_INCREMENT,
  `geozone_id` int NOT NULL,
  `taxrate_name` varchar(255) NOT NULL,
  `tax_percent` decimal(11,3) NOT NULL,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_taxrate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_taxrules`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_taxrules` (
  `j2commerce_taxrule_id` int NOT NULL AUTO_INCREMENT,
  `taxprofile_id` int NOT NULL,
  `taxrate_id` int NOT NULL,
  `address` varchar(255) NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_taxrule_id`),
  UNIQUE KEY `taxrule_unique` (`taxprofile_id`, `taxrate_id`, `address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_uploads`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_uploads` (
  `j2commerce_upload_id` int NOT NULL AUTO_INCREMENT,
  `original_name` varchar(255) NOT NULL,
  `mangled_name` varchar(255) NOT NULL,
  `saved_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `created_by` int NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY (`j2commerce_upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_variants`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_variants` (
  `j2commerce_variant_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `is_master` int DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `upc` varchar(255) DEFAULT NULL,
  `price` decimal(15,5) DEFAULT NULL COMMENT 'Regular price of the product',
  `pricing_calculator` varchar(255) NOT NULL,
  `shipping` int NOT NULL,
  `length` decimal(15,5) DEFAULT NULL,
  `width` decimal(15,5) DEFAULT NULL,
  `height` decimal(15,5) DEFAULT NULL,
  `length_class_id` int DEFAULT NULL,
  `weight` decimal(15,5) DEFAULT NULL,
  `weight_class_id` int DEFAULT NULL,
  `created_on` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `modified_on` varchar(45) DEFAULT NULL,
  `modified_by` int DEFAULT NULL,
  `manage_stock` int DEFAULT NULL,
  `quantity_restriction` int NOT NULL,
  `min_out_qty` decimal(15,5) DEFAULT NULL,
  `use_store_config_min_out_qty` int DEFAULT NULL,
  `min_sale_qty` decimal(15,5) DEFAULT NULL,
  `use_store_config_min_sale_qty` int DEFAULT NULL,
  `max_sale_qty` decimal(15,5) DEFAULT NULL,
  `use_store_config_max_sale_qty` int DEFAULT NULL,
  `notify_qty` decimal(15,5) DEFAULT NULL,
  `use_store_config_notify_qty` int DEFAULT NULL,
  `availability` int DEFAULT NULL,
  `allow_backorder` int NOT NULL,
  `isdefault_variant` int NOT NULL,
  PRIMARY KEY (`j2commerce_variant_id`),
  KEY `variant_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_vendors`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_vendors` (
  `j2commerce_vendor_id` int NOT NULL AUTO_INCREMENT,
  `j2commerce_user_id` int NOT NULL,
  `address_id` int NOT NULL,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_vendor_id`),
  UNIQUE KEY `j2commerce_user_id` (`j2commerce_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_vouchers`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_vouchers` (
  `j2commerce_voucher_id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `email_to` varchar(255) NOT NULL,
  `voucher_code` varchar(255) NOT NULL,
  `voucher_type` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `email_body` longtext NOT NULL,
  `voucher_value` decimal(15,8) NOT NULL,
  `ordering` int NOT NULL,
  `enabled` int NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int NOT NULL,
  PRIMARY KEY (`j2commerce_voucher_id`),
  UNIQUE KEY `voucher_code` (`voucher_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_weights`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_weights` (
  `j2commerce_weight_id` int NOT NULL AUTO_INCREMENT,
  `weight_title` varchar(255) NOT NULL,
  `weight_unit` varchar(4) NOT NULL,
  `weight_value` decimal(15,8) NOT NULL,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`j2commerce_weight_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `#__j2commerce_zones`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__j2commerce_zones` (
  `j2commerce_zone_id` int NOT NULL AUTO_INCREMENT,
  `country_id` int NOT NULL,
  `zone_code` varchar(255) NOT NULL,
  `zone_name` varchar(255) NOT NULL,
  `enabled` int NOT NULL,
  `ordering` int NOT NULL,
  PRIMARY KEY (`j2commerce_zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- End of J2Commerce install script
