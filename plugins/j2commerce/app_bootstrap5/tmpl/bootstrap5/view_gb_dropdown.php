<?php
/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.AppGuidedbuilder
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/**
 * Variables available (injected by JS rendering — this PHP serves as template override reference):
 * $step         — Step definition object (step_number, step_label, etc.)
 * $optionValues — Array of option value objects with display_label, price, price_prefix, etc.
 */
$step         = $step ?? new \stdClass();
$optionValues = $optionValues ?? [];
$stepNum      = (int) ($step->step_number ?? 0);
$stepLabel    = htmlspecialchars($step->step_label ?? '', ENT_QUOTES, 'UTF-8');
?>
<select class="form-select gb-dropdown" data-step="<?php echo $stepNum; ?>">
    <option value="">-- <?php echo $stepLabel; ?> --</option>

    <?php foreach ($optionValues as $val) :
        $label      = htmlspecialchars($val->display_label ?? $val->name ?? '', ENT_QUOTES, 'UTF-8');
        $valueId    = (int) ($val->id ?? 0);
        $price      = (float) ($val->price ?? 0);
        $prefix     = $val->price_prefix ?? '+';
        $priceLabel = '';

        if ($price !== 0.0) {
            $priceLabel = ' (' . match ($prefix) {
                '-'   => '-' . number_format(abs($price), 2),
                '='   => number_format($price, 2),
                '%+'  => '+' . number_format($price, 2) . '%',
                '%-'  => '-' . number_format(abs($price), 2) . '%',
                default => '+' . number_format($price, 2),
            } . ')';
        }
    ?>
    <option value="<?php echo $valueId; ?>"><?php echo $label . htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8'); ?></option>
    <?php endforeach; ?>

</select>
