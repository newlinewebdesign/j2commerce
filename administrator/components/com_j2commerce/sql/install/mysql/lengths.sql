-- J2Commerce Default Data: Lengths
-- @package     J2Commerce
-- @subpackage  com_j2commerce
-- @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
-- @license     GNU General Public License version 2 or later; see LICENSE.txt
--
-- Base unit: Centimetre (1.0)
-- Note: Using INSERT IGNORE to prevent errors on component reinstall

INSERT IGNORE INTO `#__j2commerce_lengths` (`j2commerce_length_id`, `length_title`, `length_unit`, `length_value`, `enabled`, `ordering`) VALUES
(1, 'Centimetre', 'cm', 1.00000000, 1, 1),
(2, 'Inch', 'in', 0.39370000, 1, 1),
(3, 'Millimetre', 'mm', 10.00000000, 1, 1),
(4, 'Meter', 'm', 0.01000000, 1, 1),
(5, 'Foot', 'ft', 0.03280840, 1, 1),
(6, 'Yard', 'yd', 0.01093613, 1, 1);
