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
 * $step         — Step definition object. For toggle, optionValues has exactly 2 entries:
 *                   [0] = "No" option (value_id for the off state)
 *                   [1] = "Yes" option (value_id for the on state, may have a price modifier)
 * $optionValues — Array with exactly 2 entries (no/yes pair)
 */
$step         = $step ?? new \stdClass();
$optionValues = $optionValues ?? [];
$stepNum      = (int) ($step->step_number ?? 0);

// Determine yes/no values from optionValues (first=no, second=yes by convention)
$noVal  = $optionValues[0] ?? null;
$yesVal = $optionValues[1] ?? null;

$noId    = (int) ($noVal->id ?? 0);
$yesId   = (int) ($yesVal->id ?? 0);
$yesLabel = htmlspecialchars($yesVal->display_label ?? $yesVal->name ?? Text::_('JYES'), ENT_QUOTES, 'UTF-8');
$noLabel  = htmlspecialchars($noVal->display_label ?? $noVal->name ?? Text::_('JNO'), ENT_QUOTES, 'UTF-8');

$price      = (float) ($yesVal->price ?? 0);
$prefix     = $yesVal->price_prefix ?? '+';
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

$toggleId = 'gb-toggle-' . $stepNum;
?>
<div class="gb-toggle" data-step="<?php echo $stepNum; ?>" data-no-id="<?php echo $noId; ?>" data-yes-id="<?php echo $yesId; ?>">

    <div class="uk-flex uk-flex-middle">
        <span class="gb-toggle-label-no uk-text-muted"><?php echo $noLabel; ?></span>

        <div class="gb-toggle-switch uk-margin-small-left uk-margin-small-right">
            <input class="gb-toggle-input"
                   type="checkbox"
                   role="switch"
                   id="<?php echo $toggleId; ?>"
                   data-step="<?php echo $stepNum; ?>"
                   data-no-id="<?php echo $noId; ?>"
                   data-yes-id="<?php echo $yesId; ?>">
            <label class="uk-hidden" for="<?php echo $toggleId; ?>">
                <?php echo htmlspecialchars($step->step_label ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </label>
        </div>

        <span class="gb-toggle-label-yes uk-text-bold"><?php echo $yesLabel; ?></span>

        <?php if ($priceLabel !== '') : ?>
        <span class="gb-toggle-price uk-text-muted uk-text-small uk-margin-small-left">
            (<?php echo htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8'); ?>)
        </span>
        <?php endif; ?>
    </div>

</div>
