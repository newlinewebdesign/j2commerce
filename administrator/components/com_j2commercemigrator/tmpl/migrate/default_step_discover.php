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
    <h2 id="j2cm-step-discover-title" class="h3 fw-semibold mb-1"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_DISCOVER_TITLE'); ?></h2>
    <p class="text-muted mb-0"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_DISCOVER_DESC'); ?></p>
</header>

<div id="j2cm-discover-results">
    <!-- Skeleton loader shown while audit runs -->
    <div id="j2cm-discover-skeleton">
        <?php for ($i = 0; $i < 4; $i++) : ?>
        <div class="card mb-3 border-0 bg-body-secondary" aria-hidden="true">
            <div class="card-body">
                <div class="placeholder-glow">
                    <span class="placeholder col-3 mb-2 d-block"></span>
                    <span class="placeholder col-8 mb-1 d-block"></span>
                    <span class="placeholder col-8 mb-1 d-block"></span>
                    <span class="placeholder col-6 d-block"></span>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Real results injected here by JS -->
    <div id="j2cm-discover-tiers" class="d-none"></div>
</div>

<div id="j2cm-discover-status" class="mb-3 d-none">
    <div class="alert" role="alert" id="j2cm-discover-alert"></div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="button" class="btn btn-outline-secondary" id="j2cm-btn-back-connect">
        <span class="fa-solid fa-arrow-left me-1" aria-hidden="true"></span>
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_BACK'); ?>
    </button>
    <button type="button" class="btn btn-outline-secondary" id="j2cm-btn-refresh-discover">
        <span class="fa-solid fa-rotate me-1" aria-hidden="true"></span>
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_REFRESH'); ?>
    </button>
    <button type="button" class="btn btn-primary ms-auto d-none" id="j2cm-btn-next-preflight">
        <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_NEXT'); ?>
        <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
    </button>
</div>
