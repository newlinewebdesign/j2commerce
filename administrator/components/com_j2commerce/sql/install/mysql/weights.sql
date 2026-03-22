-- J2Commerce Default Data: Weights
-- @package     J2Commerce
-- @subpackage  com_j2commerce
-- @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
-- @license     GNU General Public License version 2 or later; see LICENSE.txt
--
-- Base unit: Kilogram (1.0)
-- Note: Using INSERT IGNORE to prevent errors on component reinstall

INSERT IGNORE INTO `#__j2commerce_weights` (`j2commerce_weight_id`, `weight_title`, `weight_unit`, `weight_value`, `enabled`, `ordering`) VALUES
(1, 'Kilogram', 'kg', 1.00000000, 1, 1),
(2, 'Gram', 'g', 1000.00000000, 1, 1),
(3, 'Ounce', 'oz', 35.27400000, 1, 1),
(4, 'Pound', 'lb', 2.20462000, 1, 1);
