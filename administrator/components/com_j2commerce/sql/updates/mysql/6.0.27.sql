-- J2Commerce 6.0.27 — Add default values to product_options columns
-- Prevents INSERT errors when optional fields are omitted

ALTER TABLE `#__j2commerce_product_options`
  MODIFY `parent_id` int NOT NULL DEFAULT 0;

ALTER TABLE `#__j2commerce_product_options`
  MODIFY `ordering` tinyint NOT NULL DEFAULT 0;

ALTER TABLE `#__j2commerce_product_options`
  MODIFY `required` int NOT NULL DEFAULT 0;

ALTER TABLE `#__j2commerce_product_options`
  MODIFY `is_variant` int NOT NULL DEFAULT 0;

-- Fix duplicate master variants: keep lowest-ID variant as master per product
UPDATE `#__j2commerce_variants` v
INNER JOIN (
    SELECT `product_id`, MIN(`j2commerce_variant_id`) AS keep_id
    FROM `#__j2commerce_variants`
    WHERE `is_master` = 1
    GROUP BY `product_id`
    HAVING COUNT(*) > 1
) dup ON v.`product_id` = dup.`product_id` AND v.`j2commerce_variant_id` != dup.`keep_id`
SET v.`is_master` = 0
WHERE v.`is_master` = 1;
