<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\View\Dashboard;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public array $adapters = [];
    public array $recentRuns = [];

    public function display($tpl = null): void
    {
        $this->setToolbar();
        $this->loadAssets();

        $model = $this->getModel('Dashboard');
        $this->adapters   = $model->getAdapters();
        $this->recentRuns = $model->getRecentRuns(10);

        parent::display($tpl);
    }

    private function setToolbar(): void
    {
        $toolbar = $this->getDocument()->getToolbar();
        $toolbar->title(Text::_('COM_J2COMMERCEMIGRATOR'), 'fa-solid fa-right-left');
        $toolbar->preferences('com_j2commercemigrator');
    }

    private function loadAssets(): void
    {
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('com_j2commercemigrator.migrator', 'media/com_j2commercemigrator/css/administrator/migrator.css');
        $wa->registerAndUseScript('com_j2commercemigrator.migrator', 'media/com_j2commercemigrator/js/administrator/migrator.js', [], ['defer' => true]);
    }
}
