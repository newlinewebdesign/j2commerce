<?php

/**
 * @package     J2Commerce
 * @subpackage  plg_j2commerce_payment_banktransfer
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

extract((array) $displayData);
?>

<div class="alert alert-info">
    <?php echo htmlspecialchars($vars->onafterpayment_text, ENT_QUOTES, 'UTF-8'); ?>
</div>