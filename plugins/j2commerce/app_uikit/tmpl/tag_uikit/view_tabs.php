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

use Joomla\CMS\Language\Text;
?>
	<div class="uk-grid" uk-grid>
		<div class="uk-width-1-1">
			<?php
				$set_specification_active = true;
				if($this->params->get('item_show_sdesc') || $this->params->get('item_show_ldesc')){
					$set_specification_active = false;
				}
			?>
			<ul uk-tab>
				<?php if($this->params->get('item_show_sdesc') || $this->params->get('item_show_ldesc')): ?>
					<li class="uk-active"><a href="#"><?php echo Text::_('J2STORE_PRODUCT_DESCRIPTION')?></a></li>
				<?php endif; ?>

				<?php if($this->params->get('item_show_product_specification')): ?>
					<li<?php echo isset($set_specification_active) && $set_specification_active ? ' class="uk-active"' : ''; ?>><a href="#"><?php echo Text::_('J2STORE_PRODUCT_SPECIFICATIONS')?></a></li>
				<?php endif; ?>
			</ul>

			<ul class="uk-switcher uk-margin">
				<?php if($this->params->get('item_show_sdesc') || $this->params->get('item_show_ldesc')): ?>
				<li>
					<?php echo $this->loadTemplate('sdesc'); ?>
					<?php echo $this->loadTemplate('ldesc'); ?>
				</li>
				<?php endif; ?>

				<?php if($this->params->get('item_show_product_specification')): ?>
				<li<?php echo isset($set_specification_active) && $set_specification_active ? ' class="uk-active"' : ''; ?>>
					<?php echo $this->loadTemplate('specs'); ?>
				</li>
				<?php endif; ?>
			</ul>

		</div>
	</div>
