<?php
/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.PaymentMoneyorder
 *
 * @copyright   Copyright (C) 2024-2026 J2Commerce, LLC. All rights reserved.
 * @license     GNU General Public License version 2 or later;  see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var array $displayData */
$vars = $displayData['vars'];
?>

<?php if (!empty($vars->message)): ?>
    <div class="alert alert-warning j2commerce-payment-message">
        <?php echo htmlspecialchars($vars->message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>
