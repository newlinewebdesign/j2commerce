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
<div class="j2cm-wizard" data-adapter="<?php echo $this->escape($this->adapterKey); ?>">

    <?php if (!$this->adapter) : ?>
        <div class="alert alert-danger" role="alert">
            <strong><?php echo Text::_('COM_J2COMMERCEMIGRATOR_ERR_ADAPTER_NOT_FOUND'); ?></strong>
        </div>
    <?php else : ?>

    <?php $info = $this->adapter->getSourceInfo(); ?>

    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="<?php echo Text::_('COM_J2COMMERCEMIGRATOR_WIZARD_PROGRESS'); ?>">
                <ol class="breadcrumb j2cm-wizard-steps" id="j2cm-wizard-steps">
                    <li class="breadcrumb-item" data-step="connect"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_CONNECT'); ?></li>
                    <li class="breadcrumb-item" data-step="discover"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_DISCOVER'); ?></li>
                    <li class="breadcrumb-item" data-step="preflight"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_PREFLIGHT'); ?></li>
                    <li class="breadcrumb-item" data-step="plan"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_PLAN'); ?></li>
                    <li class="breadcrumb-item" data-step="run"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_RUN'); ?></li>
                    <li class="breadcrumb-item" data-step="verify"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_VERIFY'); ?></li>
                    <li class="breadcrumb-item" data-step="finalize"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_STEP_FINALIZE'); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div id="j2cm-wizard-canvas" class="j2cm-wizard-step">

        <!-- Step: Connect (default) -->
        <section id="j2cm-step-connect" aria-labelledby="j2cm-step-connect-title">
            <?php echo $this->loadTemplate('step_connect'); ?>
        </section>

        <!-- Step: Discover (hidden) -->
        <section id="j2cm-step-discover" class="d-none" aria-labelledby="j2cm-step-discover-title">
            <?php echo $this->loadTemplate('step_discover'); ?>
        </section>

        <!-- Step: Preflight (hidden) -->
        <section id="j2cm-step-preflight" class="d-none" aria-labelledby="j2cm-step-preflight-title">
            <?php echo $this->loadTemplate('step_preflight'); ?>
        </section>

        <!-- Step: Plan (hidden) -->
        <section id="j2cm-step-plan" class="d-none" aria-labelledby="j2cm-step-plan-title">
            <?php echo $this->loadTemplate('step_plan'); ?>
        </section>

        <!-- Step: Run (hidden) -->
        <section id="j2cm-step-run" class="d-none" aria-labelledby="j2cm-step-run-title">
            <?php echo $this->loadTemplate('step_run'); ?>
        </section>

        <!-- Step: Verify (hidden) -->
        <section id="j2cm-step-verify" class="d-none" aria-labelledby="j2cm-step-verify-title">
            <?php echo $this->loadTemplate('step_verify'); ?>
        </section>

        <!-- Step: Finalize (hidden) -->
        <section id="j2cm-step-finalize" class="d-none" aria-labelledby="j2cm-step-finalize-title">
            <?php echo $this->loadTemplate('step_finalize'); ?>
        </section>

    </div>

    <?php endif; ?>
</div>
