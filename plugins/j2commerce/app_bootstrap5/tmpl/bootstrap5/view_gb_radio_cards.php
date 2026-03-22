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
 * $step         — Step definition object (step_number, step_label, columns, required, etc.)
 * $optionValues — Array of option value objects with display_label, description, price, price_prefix
 */
$step         = $step ?? new \stdClass();
$optionValues = $optionValues ?? [];
$columns      = (int) ($step->columns ?? 2);
$colClass     = 'col-md-' . (int) floor(12 / max(1, min(4, $columns)));
?>
<div class="row g-3">
    <?php foreach ($optionValues as $val) :
        $label      = htmlspecialchars($val->display_label ?? $val->name ?? '', ENT_QUOTES, 'UTF-8');
        $valueId    = (int) ($val->id ?? 0);
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
        <div class="gb-radio-card" data-value-id="<?php echo $valueId; ?>" role="button" tabindex="0">
            <div class="d-flex align-items-center">
                <div class="gb-radio-indicator me-3"></div>

                <div class="flex-grow-1">
                    <div class="fw-semibold"><?php echo $label; ?></div>

                    <?php if (!empty($val->description)) : ?>
                    <div class="text-muted small">
                        <?php echo htmlspecialchars($val->description, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($priceLabel !== '') : ?>
                <div class="ms-auto fw-semibold">
                    <?php echo htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
