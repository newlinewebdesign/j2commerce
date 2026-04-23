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
use Joomla\CMS\Router\Route;
?>
<div class="j2cm-dashboard" style="min-height:100dvh;">

    <header class="j2cm-banner d-flex align-items-center justify-content-between mb-4 p-4 bg-body-tertiary rounded-3">
        <div>
            <h1 class="display-6 fw-semibold mb-1"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_TITLE'); ?></h1>
            <p class="fs-5 text-muted mb-0"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_SUBTITLE'); ?></p>
        </div>
        <div class="j2cm-banner-illustration d-none d-lg-block">
            <span class="fa-stack fa-2x text-purple" aria-hidden="true">
                <i class="fa-solid fa-circle fa-stack-2x opacity-25"></i>
                <i class="fa-solid fa-right-left fa-stack-1x"></i>
            </span>
        </div>
    </header>

    <section aria-labelledby="j2cm-adapters-heading" class="mb-5">
        <h2 id="j2cm-adapters-heading" class="h4 fw-semibold mb-3"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_AVAILABLE_SOURCES'); ?></h2>

        <div class="j2cm-card-grid">
            <?php if (empty($this->adapters)) : ?>
                <div class="alert alert-info" role="alert">
                    <span class="fa-solid fa-circle-info me-2" aria-hidden="true"></span>
                    <?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_NO_ADAPTERS'); ?>
                </div>
            <?php else : ?>
                <?php foreach ($this->adapters as $adapter) : ?>
                    <?php $info = $adapter->getSourceInfo(); ?>
                    <article class="card j2cm-adapter-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <span class="fa-stack fa-lg text-purple" aria-hidden="true">
                                    <i class="fa-solid fa-square fa-stack-2x opacity-25"></i>
                                    <i class="<?php echo $this->escape($info->icon); ?> fa-stack-1x"></i>
                                </span>
                                <div>
                                    <h3 class="h5 mb-0"><?php echo $this->escape($info->title); ?></h3>
                                    <p class="text-muted small mb-0"><?php echo $this->escape($info->author); ?></p>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">
                                <?php echo $this->escape($info->description); ?>
                            </p>
                            <div class="d-flex gap-2">
                                <a class="btn btn-purple btn-sm"
                                   href="<?php echo Route::_('index.php?option=com_j2commercemigrator&view=migrate&adapter=' . $adapter->getKey()); ?>">
                                    <span class="fa-solid fa-play me-1" aria-hidden="true"></span>
                                    <?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_START_MIGRATION'); ?>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section aria-labelledby="j2cm-activity-heading">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 id="j2cm-activity-heading" class="h4 fw-semibold mb-0"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_RECENT_RUNS'); ?></h2>
            <a href="<?php echo Route::_('index.php?option=com_j2commercemigrator&view=runs'); ?>" class="btn btn-outline-secondary btn-sm">
                <?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_VIEW_ALL_RUNS'); ?>
            </a>
        </div>

        <?php if (empty($this->recentRuns)) : ?>
            <div class="border rounded-3 p-4 text-center text-muted">
                <span class="fa-solid fa-inbox fa-2x mb-2 d-block" aria-hidden="true"></span>
                <?php echo Text::_('COM_J2COMMERCEMIGRATOR_DASHBOARD_NO_RUNS'); ?>
            </div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_ID'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_ADAPTER'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_STATUS'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_STARTED'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_J2COMMERCEMIGRATOR_HEADING_FINISHED'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->recentRuns as $run) : ?>
                            <tr>
                                <td><?php echo (int) $run->j2commerce_migrator_run_id; ?></td>
                                <td><?php echo $this->escape($run->adapter); ?></td>
                                <td>
                                    <?php
                                    $statusClass = match ($run->status) {
                                        'completed' => 'success',
                                        'failed'    => 'danger',
                                        'running'   => 'info',
                                        'cancelled' => 'warning',
                                        default     => 'secondary',
                                    };
                                    ?>
                                    <span class="badge text-bg-<?php echo $statusClass; ?>">
                                        <?php echo $this->escape($run->status); ?>
                                    </span>
                                </td>
                                <td><?php echo $this->escape($run->started_on ?? '-'); ?></td>
                                <td><?php echo $this->escape($run->finished_on ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
