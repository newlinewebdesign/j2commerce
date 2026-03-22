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
 *                   $step->max_length  — Maximum character count (0 = unlimited)
 *                   $step->placeholder — Input placeholder text
 *                   $step->help_text   — Additional help text below field
 * $optionValues — Not used for text_input; kept for interface consistency
 */
$step        = $step ?? new \stdClass();
$stepNum     = (int) ($step->step_number ?? 0);
$maxLength   = (int) ($step->max_length ?? 0);
$placeholder = htmlspecialchars($step->placeholder ?? '', ENT_QUOTES, 'UTF-8');
$helpText    = htmlspecialchars($step->help_text ?? '', ENT_QUOTES, 'UTF-8');
$inputId     = 'gb-text-' . $stepNum;
?>
<div class="gb-text-field" data-step="<?php echo $stepNum; ?>">

    <div class="input-group">
        <input type="text"
               id="<?php echo $inputId; ?>"
               class="form-control gb-text-input"
               data-step="<?php echo $stepNum; ?>"
               <?php if ($maxLength > 0) : ?>
               maxlength="<?php echo $maxLength; ?>"
               <?php endif; ?>
               <?php if ($placeholder !== '') : ?>
               placeholder="<?php echo $placeholder; ?>"
               <?php endif; ?>
               aria-label="<?php echo htmlspecialchars($step->step_label ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <?php if ($maxLength > 0) : ?>
    <div class="gb-text-char-count d-flex justify-content-end mt-1">
        <small class="text-muted">
            <span class="gb-chars-used">0</span> / <?php echo $maxLength; ?>
        </small>
    </div>
    <?php endif; ?>

    <?php if ($helpText !== '') : ?>
    <div class="form-text text-muted"><?php echo $helpText; ?></div>
    <?php endif; ?>

</div>
