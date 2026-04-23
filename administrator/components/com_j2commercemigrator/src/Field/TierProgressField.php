<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\Database\ParameterType;

/**
 * Renders a Bootstrap 5 progress bar for a migration tier, sourcing totals from
 * #__j2commerce_migrator_queues for the given run_id and tier.
 */
class TierProgressField extends FormField
{
    protected $type = 'TierProgress';

    protected function getInput(): string
    {
        $runId = (int) ($this->element['run_id'] ?? $this->value ?? 0);
        $tier  = (int) ($this->element['tier'] ?? 0);

        if ($runId < 1) {
            return $this->renderBar(0, 0);
        }

        [$total, $processed] = $this->fetchTotals($runId, $tier);

        return $this->renderBar($total, $processed);
    }

    private function fetchTotals(int $runId, int $tier): array
    {
        $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([
                'COALESCE(SUM(' . $db->quoteName('total_rows') . '), 0) AS total',
                'COALESCE(SUM(' . $db->quoteName('processed_rows') . '), 0) AS processed',
            ])
            ->from($db->quoteName('#__j2commerce_migrator_queues'))
            ->where($db->quoteName('run_id') . ' = :run_id')
            ->bind(':run_id', $runId, ParameterType::INTEGER);

        if ($tier > 0) {
            $query->where($db->quoteName('tier') . ' = :tier')
                  ->bind(':tier', $tier, ParameterType::INTEGER);
        }

        $row = $db->setQuery($query)->loadObject();

        return [(int) ($row->total ?? 0), (int) ($row->processed ?? 0)];
    }

    private function renderBar(int $total, int $processed): string
    {
        $percent = $total > 0 ? (int) round(($processed / $total) * 100) : 0;
        $percent = min(100, max(0, $percent));

        $id    = htmlspecialchars($this->id, ENT_COMPAT, 'UTF-8');
        $label = $total > 0
            ? sprintf('%d / %d', $processed, $total)
            : htmlspecialchars(\Joomla\CMS\Language\Text::_('COM_J2COMMERCEMIGRATOR_TIER_PROGRESS_PENDING'), ENT_COMPAT, 'UTF-8');

        return <<<HTML
            <div class="progress" role="progressbar" aria-valuenow="{$percent}" aria-valuemin="0" aria-valuemax="100">
                <div id="{$id}" class="progress-bar" style="width:{$percent}%">{$label}</div>
            </div>
            HTML;
    }

    protected function getLabel(): string
    {
        return '';
    }
}
