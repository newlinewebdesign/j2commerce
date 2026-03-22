<?php

/**
 * @package     J2Commerce
 * @subpackage  plg_j2commerce_payment_cash
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

/** @var array $displayData */
$vars = $displayData['vars'];
?>

<?php if (!empty($vars->onafterpayment_text)): ?>
    <div class="alert alert-success j2commerce-after-payment-text">
        <?php echo htmlspecialchars($vars->onafterpayment_text, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>
