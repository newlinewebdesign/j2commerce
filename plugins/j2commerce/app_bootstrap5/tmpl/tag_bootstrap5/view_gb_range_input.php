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
 * $step         — Step definition object. Extended fields used here:
 *                   $step->min         — Minimum value (numeric)
 *                   $step->max         — Maximum value (numeric)
 *                   $step->step_size   — Input step increment
 *                   $step->unit        — Unit label (e.g., "in", "cm", "ft")
 *                   $step->help_text   — Additional help text below field
 *                   $step->price_per_unit — Price modifier per unit
 * $optionValues — Not used for range_input; kept for interface consistency
 */
$step        = $step ?? new \stdClass();
$stepNum     = (int) ($step->step_number ?? 0);
$min         = $step->min ?? 1;
$max         = $step->max ?? 100;
$stepSize    = $step->step_size ?? 1;
$unit        = htmlspecialchars($step->unit ?? '', ENT_QUOTES, 'UTF-8');
$helpText    = htmlspecialchars($step->help_text ?? '', ENT_QUOTES, 'UTF-8');
$pricePerUnit = (float) ($step->price_per_unit ?? 0);
$inputId     = 'gb-range-' . $stepNum;
?>
<div class="gb-range-field" data-step="<?php echo $stepNum; ?>">

    <div class="gb-range-wrapper">
        <input type="number"
               id="<?php echo $inputId; ?>"
               class="form-control gb-range-input"
               data-step="<?php echo $stepNum; ?>"
               min="<?php echo htmlspecialchars((string) $min, ENT_QUOTES, 'UTF-8'); ?>"
               max="<?php echo htmlspecialchars((string) $max, ENT_QUOTES, 'UTF-8'); ?>"
               step="<?php echo htmlspecialchars((string) $stepSize, ENT_QUOTES, 'UTF-8'); ?>"
               value="<?php echo htmlspecialchars((string) $min, ENT_QUOTES, 'UTF-8'); ?>"
               aria-label="<?php echo htmlspecialchars($step->step_label ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <?php if ($unit !== '') : ?>
        <span class="gb-range-unit"><?php echo $unit; ?></span>
        <?php endif; ?>
    </div>

    <?php if ($pricePerUnit !== 0.0) : ?>
    <div class="gb-range-help small text-muted mt-1">
        <?php
        $sign = $pricePerUnit > 0 ? '+' : '';
        echo htmlspecialchars($sign . number_format($pricePerUnit, 2), ENT_QUOTES, 'UTF-8');
        echo ' ' . Text::_('PLG_J2COMMERCE_APP_GUIDEDBUILDER_PER_UNIT');
        if ($unit !== '') {
            echo ' (' . $unit . ')';
        }
        ?>
    </div>
    <?php endif; ?>

    <?php if ($helpText !== '') : ?>
    <div class="gb-range-help small text-muted mt-1"><?php echo $helpText; ?></div>
    <?php endif; ?>

</div>
