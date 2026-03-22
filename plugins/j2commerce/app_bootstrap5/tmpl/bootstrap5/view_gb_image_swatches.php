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
 * $step         — Step definition object
 * $optionValues — Array of option value objects with swatch_image, display_label, price, etc.
 *                 Used for material textures, fabric patterns, etc.
 */
$step         = $step ?? new \stdClass();
$optionValues = $optionValues ?? [];
?>
<div class="gb-swatch-grid d-flex flex-wrap gap-3">
    <?php foreach ($optionValues as $val) :
        $label   = htmlspecialchars($val->display_label ?? $val->name ?? '', ENT_QUOTES, 'UTF-8');
        $valueId = (int) ($val->id ?? 0);

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
    <div class="gb-swatch gb-image-swatch"
         data-value-id="<?php echo $valueId; ?>"
         role="button"
         tabindex="0"
         title="<?php echo $label; ?>">

        <?php if (!empty($val->swatch_image)) : ?>
        <div class="gb-swatch-circle gb-image-swatch-thumb"
             style="background-image:url('<?php echo htmlspecialchars($val->swatch_image, ENT_QUOTES, 'UTF-8'); ?>');background-size:cover;background-position:center;"></div>
        <?php elseif (!empty($val->card_image)) : ?>
        <div class="gb-swatch-circle gb-image-swatch-thumb"
             style="background-image:url('<?php echo htmlspecialchars($val->card_image, ENT_QUOTES, 'UTF-8'); ?>');background-size:cover;background-position:center;"></div>
        <?php else : ?>
        <div class="gb-swatch-circle gb-image-swatch-thumb" style="background-color:#e2e8f0;"></div>
        <?php endif; ?>

        <div class="gb-swatch-label"><?php echo $label; ?></div>

        <?php if ($priceLabel !== '') : ?>
        <div class="gb-swatch-price small"><?php echo htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>
</div>
