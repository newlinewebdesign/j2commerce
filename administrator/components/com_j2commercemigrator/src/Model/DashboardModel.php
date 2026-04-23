<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Model;

defined('_JEXEC') or die;

use J2Commerce\Component\J2commercemigrator\Administrator\Service\AdapterRegistry;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\RunRepository;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Dashboard model — provides adapter list and recent run activity.
 */
class DashboardModel extends BaseDatabaseModel
{
    /** Returns all registered adapter instances. */
    public function getAdapters(): array
    {
        return array_values((new AdapterRegistry())->getAll());
    }

    /** Returns the most recent migration runs for the activity panel. */
    public function getRecentRuns(int $limit = 10): array
    {
        return (new RunRepository($this->getDatabase()))->getList($limit);
    }

    /** Returns summary statistics across all runs for the dashboard stats bar. */
    public function getStats(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'COUNT(*) AS total',
                'SUM(CASE WHEN ' . $db->quoteName('status') . ' = \'completed\' THEN 1 ELSE 0 END) AS completed',
                'SUM(CASE WHEN ' . $db->quoteName('status') . ' = \'failed\' THEN 1 ELSE 0 END) AS failed',
                'SUM(CASE WHEN ' . $db->quoteName('status') . ' = \'running\' THEN 1 ELSE 0 END) AS running',
            ])
            ->from($db->quoteName('#__j2commerce_migrator_runs'));

        $row = $db->setQuery($query)->loadObject();

        return [
            'total'     => (int) ($row->total ?? 0),
            'completed' => (int) ($row->completed ?? 0),
            'failed'    => (int) ($row->failed ?? 0),
            'running'   => (int) ($row->running ?? 0),
        ];
    }
}
