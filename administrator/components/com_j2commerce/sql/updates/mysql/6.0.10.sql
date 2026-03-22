ALTER TABLE `#__j2commerce_productimages`
  ADD COLUMN `tiny_image` text DEFAULT NULL AFTER `thumb_image_alt`,
  ADD COLUMN `tiny_image_alt` varchar(255) NOT NULL DEFAULT '' AFTER `tiny_image`,
  ADD COLUMN `additional_thumb_images` longtext DEFAULT NULL AFTER `additional_images_alt`,
  ADD COLUMN `additional_thumb_images_alt` longtext DEFAULT NULL AFTER `additional_thumb_images`,
  ADD COLUMN `additional_tiny_images` longtext DEFAULT NULL AFTER `additional_thumb_images_alt`,
  ADD COLUMN `additional_tiny_images_alt` longtext DEFAULT NULL AFTER `additional_tiny_images`/** CAN FAIL **/;

ALTER TABLE `#__j2commerce_productprice_index`
  MODIFY `min_price` decimal(15,5) NOT NULL DEFAULT 0.00000;

ALTER TABLE `#__j2commerce_productprice_index`
  MODIFY `max_price` decimal(15,5) NOT NULL DEFAULT 0.00000;
