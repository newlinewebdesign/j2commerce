<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

extract($displayData);

// Calculate total bundle item count from product params
$bundleItems = [];
$productParams = $product->params ?? null;

if ($productParams instanceof Registry) {
    $bundleItems = $productParams->get('bundleproduct', []);
} elseif (is_object($productParams) || is_array($productParams)) {
    $registry = new Registry($productParams);
    $bundleItems = $registry->get('bundleproduct', []);
}

$totalCount = 0;
foreach ($bundleItems as $item) {
    $totalCount += is_array($item) ? (int) ($item['quantity'] ?? 1) : (int) ($item->quantity ?? 1);
}

// Get custom unit labels from product params, fall back to plugin language string
$singularLabel = '';
$pluralLabel = '';

if ($productParams instanceof Registry) {
    $singularLabel = $productParams->get('price_per_each_unit_singular_label', '');
    $pluralLabel = $productParams->get('price_per_each_unit_plural_label', '');
} elseif (isset($registry)) {
    $singularLabel = $registry->get('price_per_each_unit_singular_label', '');
    $pluralLabel = $registry->get('price_per_each_unit_plural_label', '');
}

if (empty($singularLabel)) {
    $singularLabel = Text::_('PLG_J2COMMERCE_APP_BUNDLEPRODUCT_FIELD_PPE_ITEM_LABEL');
}
if (empty($pluralLabel)) {
    $pluralLabel = $singularLabel . 's';
}

$unitLabel = $totalCount === 1 ? $singularLabel : $pluralLabel;

?>
<?php if ($totalCount > 0) : ?>
<div class="j2commerce-bundle-options py-2">
    <small><?php echo Text::sprintf('PLG_J2COMMERCE_APP_BUNDLEPRODUCT_BUNDLE_CONTAINS_N_ITEMS', $totalCount, $unitLabel); ?></small>
</div>
<?php endif; ?>

