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
    <h2 id="j2cm-step-verify-title" class="h3 fw-semibold mb-1"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_VERIFY_TITLE'); ?></h2>
    <p class="text-muted mb-0"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_VERIFY_DESC'); ?></p>
</header>

<div id="j2cm-verify-results">
    <div id="j2cm-verify-skeleton">
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

    <div id="j2cm-verify-checks" class="d-none">
        <div class="table-responsive mb-4">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th scope="col"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_TABLE'); ?></th>
                        <th scope="col" class="text-end"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_SOURCE_COUNT'); ?></th>
                        <th scope="col" class="text-end"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_TARGET_COUNT'); ?></th>
                        <th scope="col" class="text-center"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_STATUS'); ?></th>
                    </tr>
                </thead>
                <tbody id="j2cm-verify-table-body"></tbody>
            </table>
        </div>

        <div id="j2cm-verify-errors" class="d-none">
            <h3 class="h5 fw-semibold mb-2 text-danger">
                <span class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></span>
                <?php echo Text::_('COM_J2COMMERCEMIGRATOR_VERIFY_ERRORS_HEADING'); ?>
            </h3>
            <div class="bg-body-secondary rounded-3 p-3 font-monospace small" style="max-height: 200px; overflow-y: auto;" id="j2cm-verify-errors-list"></div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="button" class="btn btn-outline-secondary d-none" id="j2cm-btn-back-run">
        <span class="fa-solid fa-arrow-left me-1" aria-hidden="true"></span>
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_BACK'); ?>
    </button>
    <button type="button" class="btn btn-primary ms-auto d-none" id="j2cm-btn-next-finalize">
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_NEXT'); ?>
        <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
    </button>
</div>
