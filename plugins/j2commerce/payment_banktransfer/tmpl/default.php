<?php

/**
 * @package     J2Commerce
 * @subpackage  plg_j2commerce_payment_banktransfer
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract((array) $displayData);
?>

<div class="j2commerce-payment-option">
    <h4><?php echo htmlspecialchars($vars->display_name, ENT_COMPAT, 'UTF-8'); ?></h4>
    <?php if (!empty($vars->description)): ?>
    <p class="payment-description">
        <?php echo htmlspecialchars($vars->description, ENT_COMPAT, 'UTF-8'); ?>
    </p>
    <?php endif; ?>
</div>