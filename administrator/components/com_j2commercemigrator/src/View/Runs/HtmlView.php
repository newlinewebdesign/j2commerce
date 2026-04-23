<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\View\Runs;

use J2Commerce\Component\J2commercemigrator\Administrator\Service\RunRepository;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Database\DatabaseInterface;

class HtmlView extends BaseHtmlView
{
    public array $runs = [];

    public function display($tpl = null): void
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $runRepo = new RunRepository($db);

        $this->runs = $runRepo->getList(50);

        $this->setToolbar();

        parent::display($tpl);
    }

    private function setToolbar(): void
    {
        $toolbar = $this->getDocument()->getToolbar();
        $toolbar->title(Text::_('COM_J2COMMERCEMIGRATOR_VIEW_RUNS_TITLE'), 'fa-solid fa-clock-rotate-left');
        $toolbar->link(Text::_('COM_J2COMMERCEMIGRATOR_TOOLBAR_DASHBOARD'), 'index.php?option=com_j2commercemigrator', 'fa-solid fa-house');
    }
}
