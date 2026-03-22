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
 * $step         — Step definition object (step_number, columns, required, etc.)
 * $optionValues — Array of option value objects. Multiple can be selected simultaneously.
 */
$step         = $step ?? new \stdClass();
$optionValues = $optionValues ?? [];
$stepNum      = (int) ($step->step_number ?? 0);
$columns      = (int) ($step->columns ?? 2);
$colClass     = 'col-md-' . (int) floor(12 / max(1, min(4, $columns)));
?>
<div class="row g-2 gb-checkbox-group" data-step="<?php echo $stepNum; ?>">
    <?php foreach ($optionValues as $val) :
        $label      = htmlspecialchars($val->display_label ?? $val->name ?? '', ENT_QUOTES, 'UTF-8');
        $valueId    = (int) ($val->id ?? 0);
        $checkId    = 'gb-cb-' . $stepNum . '-' . $valueId;
        $price      = (float) ($val->price ?? 0);
        $prefix     = $val->price_prefix ?? '+';
        $priceLabel = '';

        if ($price !== 0.0) {
            $priceLabel = match ($prefix) {
                '-'   => '-' . number_format(abs($price), 2),
                '='   => number_format($price, 2),
                '%+'  => '+' . number_format($price, 2) . '%',
                '%-'  => '-' . number_format(abs($price), 2) . '%',
                default => '+' . number_format($price, 2),
            };
        }
    ?>
    <div class="<?php echo $colClass; ?>">
        <div class="gb-checkbox-item" data-value-id="<?php echo $valueId; ?>">
            <div class="form-check d-flex align-items-start gap-2 h-100">
                <input class="form-check-input gb-checkbox-input flex-shrink-0 mt-1"
                       type="checkbox"
                       id="<?php echo $checkId; ?>"
                       data-step="<?php echo $stepNum; ?>"
                       data-value-id="<?php echo $valueId; ?>"
                       value="<?php echo $valueId; ?>">

                <label class="form-check-label flex-grow-1" for="<?php echo $checkId; ?>">
                    <span class="fw-semibold d-block"><?php echo $label; ?></span>

                    <?php if (!empty($val->description)) : ?>
                    <span class="text-muted small d-block">
                        <?php echo htmlspecialchars($val->description, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <?php endif; ?>

                    <?php if ($priceLabel !== '') : ?>
                    <span class="gb-checkbox-price text-primary small fw-semibold d-block">
                        <?php echo htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <?php endif; ?>
                </label>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
