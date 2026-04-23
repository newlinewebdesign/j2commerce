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
    <h2 id="j2cm-step-plan-title" class="h3 fw-semibold mb-1"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_PLAN_TITLE'); ?></h2>
    <p class="text-muted mb-0"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_PLAN_DESC'); ?></p>
</header>

<div id="j2cm-plan-summary" class="mb-4">
    <div class="row g-3 mb-4" id="j2cm-plan-stats">
        <div class="col-sm-4">
            <div class="card text-center border-0 bg-body-secondary h-100">
                <div class="card-body">
                    <div class="h2 fw-bold text-purple mb-1" id="j2cm-plan-total-rows">—</div>
                    <div class="text-muted small"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_PLAN_TOTAL_ROWS'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-center border-0 bg-body-secondary h-100">
                <div class="card-body">
                    <div class="h2 fw-bold text-purple mb-1" id="j2cm-plan-total-tables">—</div>
                    <div class="text-muted small"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_PLAN_TOTAL_TABLES'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-center border-0 bg-body-secondary h-100">
                <div class="card-body">
                    <div class="h2 fw-bold text-purple mb-1" id="j2cm-plan-estimated-time">—</div>
                    <div class="text-muted small"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_PLAN_EST_TIME'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div id="j2cm-plan-tiers"></div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="button" class="btn btn-outline-secondary" id="j2cm-btn-back-preflight">
        <span class="fa-solid fa-arrow-left me-1" aria-hidden="true"></span>
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_BACK'); ?>
    </button>
    <button type="button" class="btn btn-success ms-auto" id="j2cm-btn-start-run">
        <span class="fa-solid fa-play me-1" aria-hidden="true"></span>
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_START_MIGRATION'); ?>
    </button>
</div>
