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

use J2Commerce\Component\J2commercemigrator\Administrator\Helper\AdapterHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<section aria-labelledby="j2cm-adapters-heading" class="mb-5">
    <h2 id="j2cm-adapters-heading" class="h4 fw-semibold mb-3"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_AVAILABLE_SOURCES'); ?></h2>

    <div class="j2cm-card-grid">
        <?php if (empty($this->adapters)) : ?>
            <div class="alert alert-info" role="alert">
                <span class="fa-solid fa-circle-info me-2" aria-hidden="true"></span>
                <?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_NO_ADAPTERS'); ?>
            </div>
        <?php else : ?>
            <?php foreach ($this->adapters as $adapterObj) : ?>
                <?php $adapter = AdapterHelper::enrichAdapter($adapterObj, 0, true); ?>
                <article class="card j2cm-adapter-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-2">
                            <span class="fa-stack fa-lg text-purple" aria-hidden="true">
                                <i class="fa-solid fa-square fa-stack-2x opacity-25"></i>
                                <i class="<?php echo $this->escape($adapter['icon']); ?> fa-stack-1x"></i>
                            </span>
                            <div>
                                <h3 class="h5 mb-0"><?php echo $this->escape($adapter['title']); ?></h3>
                                <p class="text-muted small mb-0"><?php echo $this->escape($adapter['author']); ?></p>
                            </div>
                            <?php
                            $statusPill = match ($adapter['status']) {
                                'enabled'      => ['bg-success',  Text::_('COM_J2COMMERCEMIGRATOR_STATUS_ENABLED')],
                                'needs_config' => ['bg-warning text-dark', Text::_('COM_J2COMMERCEMIGRATOR_STATUS_NEEDS_CONFIG')],
                                'running'      => ['bg-info',    Text::_('COM_J2COMMERCEMIGRATOR_STATUS_RUNNING')],
                                default        => ['bg-secondary', Text::_('COM_J2COMMERCEMIGRATOR_STATUS_DISABLED')],
                            };
                            ?>
                            <span class="badge <?php echo $statusPill[0]; ?> ms-auto"><?php echo $statusPill[1]; ?></span>
                        </div>
                        <p class="small text-muted mb-3">
                            <?php echo $this->escape($adapter['description']); ?>
                        </p>
                        <div class="d-flex gap-2">
                            <a class="btn btn-purple btn-sm"
                               href="<?php echo Route::_('index.php?option=com_j2commercemigrator&view=migrate&adapter=' . $this->escape($adapter['key'])); ?>">
                                <span class="fa-solid fa-play me-1" aria-hidden="true"></span>
                                <?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_START_MIGRATION'); ?>
                            </a>
                            <?php if ($adapter['extensionId'] > 0) : ?>
                                <?php if ($adapter['enabled']) : ?>
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            data-task="plugin.unpublish"
                                            data-extension-id="<?php echo $adapter['extensionId']; ?>"
                                            aria-label="<?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_DISABLE'); ?>">
                                        <span class="fa-solid fa-pause" aria-hidden="true"></span>
                                        <span class="visually-hidden"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_DISABLE'); ?></span>
                                    </button>
                                <?php else : ?>
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            data-task="plugin.publish"
                                            data-extension-id="<?php echo $adapter['extensionId']; ?>"
                                            aria-label="<?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_ENABLE'); ?>">
                                        <span class="fa-solid fa-play-pause" aria-hidden="true"></span>
                                        <span class="visually-hidden"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_BTN_ENABLE'); ?></span>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
