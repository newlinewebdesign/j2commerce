<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
?>

<?php if($this->params->get('item_show_sdesc', 1)): ?>
	<div class="product-sdesc uk-margin-small">
		<?php echo $this->product->product_short_desc; ?>
	</div>
<?php endif; ?>
