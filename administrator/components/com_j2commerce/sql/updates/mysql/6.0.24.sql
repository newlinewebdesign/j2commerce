-- Add DELIVERED order status for AtoShip tracking integration
INSERT INTO `#__j2commerce_orderstatuses`
  (`j2commerce_orderstatus_id`, `orderstatus_name`, `orderstatus_cssclass`, `orderstatus_core`, `enabled`, `ordering`)
VALUES
  (8, 'J2COMMERCE_DELIVERED', 'badge text-bg-primary', 1, 1, 8)
ON DUPLICATE KEY UPDATE `orderstatus_name` = VALUES(`orderstatus_name`);
