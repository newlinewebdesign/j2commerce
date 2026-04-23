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
    <h2 id="j2cm-step-preflight-title" class="h3 fw-semibold mb-1"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_PREFLIGHT_TITLE'); ?></h2>
    <p class="text-muted mb-0"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_PREFLIGHT_DESC'); ?></p>
</header>

<div id="j2cm-preflight-results">
    <div id="j2cm-preflight-skeleton">
        <?php for ($i = 0; $i < 3; $i++) : ?>
        <div class="card mb-3 border-0 bg-body-secondary" aria-hidden="true">
            <div class="card-body">
                <div class="placeholder-glow">
                    <span class="placeholder col-4 mb-2 d-block"></span>
                    <span class="placeholder col-9 mb-1 d-block"></span>
                    <span class="placeholder col-7 d-block"></span>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div id="j2cm-preflight-checks" class="d-none"></div>
</div>

<div id="j2cm-conflict-settings" class="mt-4 d-none">
    <h3 class="h5 fw-semibold mb-3"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_PREFLIGHT_CONFLICT_HEADING'); ?></h3>
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="j2cm-conflict-mode" class="form-label fw-semibold"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_CONFLICT_MODE'); ?></label>
            <select class="form-select" id="j2cm-conflict-mode" name="conflict_mode">
                <option value="skip"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_CONFLICT_SKIP'); ?></option>
                <option value="overwrite"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_CONFLICT_OVERWRITE'); ?></option>
                <option value="merge"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_CONFLICT_MERGE'); ?></option>
                <option value="report"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_CONFLICT_REPORT'); ?></option>
            </select>
            <div class="form-text"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_CONFLICT_MODE_DESC'); ?></div>
        </div>
        <div class="col-md-6">
            <label for="j2cm-batch-size" class="form-label fw-semibold"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_BATCH_SIZE'); ?></label>
            <input type="number" class="form-control" id="j2cm-batch-size" name="batch_size" value="500" min="50" max="5000" step="50">
            <div class="form-text"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_BATCH_SIZE_DESC'); ?></div>
        </div>
    </div>
</div>

<div id="j2cm-preflight-status" class="mb-3 d-none">
    <div class="alert" role="alert" id="j2cm-preflight-alert"></div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="button" class="btn btn-outline-secondary" id="j2cm-btn-back-discover">
        <span class="fa-solid fa-arrow-left me-1" aria-hidden="true"></span>
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_BACK'); ?>
    </button>
    <button type="button" class="btn btn-primary ms-auto d-none" id="j2cm-btn-next-plan">
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_NEXT'); ?>
        <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
    </button>
</div>
