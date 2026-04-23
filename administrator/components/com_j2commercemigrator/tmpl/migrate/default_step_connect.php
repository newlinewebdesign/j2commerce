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
    <h2 id="j2cm-step-connect-title" class="h3 fw-semibold mb-1"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_CONNECT_TITLE'); ?></h2>
    <p class="text-muted mb-0"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_CONNECT_DESC'); ?></p>
</header>

<div id="j2cm-connection-form">
    <div class="mb-3">
        <label class="form-label fw-semibold"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_CONNECTION_MODE'); ?></label>
        <div class="btn-group d-flex" role="group" aria-label="<?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_CONNECTION_MODE'); ?>">
            <input type="radio" class="btn-check" name="j2cm-mode" id="j2cm-mode-a" value="A" checked>
            <label for="j2cm-mode-a" class="btn btn-outline-primary flex-fill">
                <?php echo Text::_('COM_J2COMMERCEMIGRATOR_MODE_A_LABEL'); ?>
                <small class="d-block text-muted"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_MODE_A_DESC'); ?></small>
            </label>
            <input type="radio" class="btn-check" name="j2cm-mode" id="j2cm-mode-b" value="B">
            <label for="j2cm-mode-b" class="btn btn-outline-primary flex-fill">
                <?php echo Text::_('COM_J2COMMERCEMIGRATOR_MODE_B_LABEL'); ?>
                <small class="d-block text-muted"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_MODE_B_DESC'); ?></small>
            </label>
            <input type="radio" class="btn-check" name="j2cm-mode" id="j2cm-mode-c" value="C">
            <label for="j2cm-mode-c" class="btn btn-outline-primary flex-fill">
                <?php echo Text::_('COM_J2COMMERCEMIGRATOR_MODE_C_LABEL'); ?>
                <small class="d-block text-muted"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_MODE_C_DESC'); ?></small>
            </label>
        </div>
    </div>

    <div id="j2cm-conn-fields-bc" class="d-none">
        <div class="row g-3 mb-3">
            <div class="col-md-6" id="j2cm-field-host-wrap">
                <label for="j2cm-host" class="form-label"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_HOST'); ?></label>
                <input type="text" class="form-control" id="j2cm-host" name="host" value="localhost">
            </div>
            <div class="col-md-2">
                <label for="j2cm-port" class="form-label"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_PORT'); ?></label>
                <input type="number" class="form-control" id="j2cm-port" name="port" value="3306" min="1" max="65535">
            </div>
            <div class="col-md-4">
                <label for="j2cm-prefix" class="form-label"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_DB_PREFIX'); ?></label>
                <input type="text" class="form-control" id="j2cm-prefix" name="prefix" value="jos_">
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="j2cm-database" class="form-label"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_DATABASE'); ?></label>
                <input type="text" class="form-control" id="j2cm-database" name="database">
            </div>
            <div class="col-md-4">
                <label for="j2cm-username" class="form-label"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_USERNAME'); ?></label>
                <input type="text" class="form-control" id="j2cm-username" name="username">
            </div>
            <div class="col-md-4">
                <label for="j2cm-password" class="form-label"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_PASSWORD'); ?></label>
                <input type="password" class="form-control" id="j2cm-password" name="password" autocomplete="current-password">
            </div>
        </div>
        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="j2cm-ssl" name="ssl" role="switch">
                <label class="form-check-label" for="j2cm-ssl"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_SSL'); ?></label>
            </div>
        </div>
        <div id="j2cm-ssl-ca-wrap" class="mb-3 d-none">
            <label for="j2cm-ssl-ca" class="form-label"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_FIELD_SSL_CA'); ?></label>
            <input type="text" class="form-control" id="j2cm-ssl-ca" name="ssl_ca"
                   placeholder="/etc/ssl/certs/ca-bundle.crt">
        </div>
    </div>

    <div id="j2cm-conn-status" class="mb-3 d-none">
        <div class="alert" role="alert" id="j2cm-conn-alert"></div>
    </div>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-purple" id="j2cm-btn-verify">
            <span class="fa-solid fa-plug me-1" aria-hidden="true"></span>
            <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_VERIFY_CONNECTION'); ?>
        </button>
        <button type="button" class="btn btn-outline-secondary d-none" id="j2cm-btn-clear-conn">
            <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_CLEAR_CONNECTION'); ?>
        </button>
        <button type="button" class="btn btn-primary ms-auto d-none" id="j2cm-btn-next-discover">
            <?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_NEXT'); ?>
            <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
        </button>
    </div>
</div>
