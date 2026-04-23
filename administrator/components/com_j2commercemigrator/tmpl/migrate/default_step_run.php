<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<header class="mb-4">
    <h2 id="j2cm-step-run-title" class="h3 fw-semibold mb-1"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_RUN_TITLE'); ?></h2>
    <p class="text-muted mb-0"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_RUN_DESC'); ?></p>
</header>

<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-1">
        <span class="fw-semibold small" id="j2cm-run-progress-label"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_RUN_INITIALIZING'); ?></span>
        <span class="small text-muted" id="j2cm-run-progress-pct">0%</span>
    </div>
    <div class="progress" style="height: 12px;" role="progressbar" aria-label="<?php echo Text::_('COM_J2COMMERCEMIGRATOR_RUN_PROGRESS'); ?>" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="j2cm-run-progressbar-wrap">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-purple" id="j2cm-run-progressbar" style="width: 0%"></div>
    </div>
</div>

<div id="j2cm-run-tiers" class="mb-4"></div>

<div id="j2cm-run-log" class="mb-4">
    <h3 class="h6 fw-semibold mb-2"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_RUN_LOG_HEADING'); ?></h3>
    <div class="bg-body-secondary rounded-3 p-3 font-monospace small" style="max-height: 280px; overflow-y: auto;" id="j2cm-run-log-output" aria-live="polite" aria-label="<?php echo Text::_('COM_J2COMMERCEMIGRATOR_RUN_LOG_HEADING'); ?>"></div>
</div>

<div id="j2cm-run-status" class="mb-3 d-none">
    <div class="alert" role="alert" id="j2cm-run-alert"></div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="button" class="btn btn-outline-danger d-none" id="j2cm-btn-cancel-run">
        <span class="fa-solid fa-stop me-1" aria-hidden="true"></span>
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_CANCEL'); ?>
    </button>
    <button type="button" class="btn btn-primary ms-auto d-none" id="j2cm-btn-next-verify">
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_NEXT'); ?>
        <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
    </button>
</div>
