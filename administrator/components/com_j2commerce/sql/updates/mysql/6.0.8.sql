--
-- J2Commerce 6.0.8 Update
-- Add num_decimals column to weights and lengths tables
-- for per-unit decimal place configuration
--

-- Add num_decimals column to weights table
ALTER TABLE `#__j2commerce_weights`
    ADD COLUMN `num_decimals` INT NOT NULL DEFAULT 2 AFTER `weight_value`/** CAN FAIL **/;

-- Add num_decimals column to lengths table
ALTER TABLE `#__j2commerce_lengths`
    ADD COLUMN `num_decimals` INT NOT NULL DEFAULT 2 AFTER `length_value`/** CAN FAIL **/;
