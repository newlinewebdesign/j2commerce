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

use J2Commerce\Component\J2commercemigrator\Administrator\Helper\AdapterHelper;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\AdapterRegistry;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\RunRepository;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Dashboard model — provides adapter list and recent run activity.
 */
class DashboardModel extends BaseDatabaseModel
{
    /**
     * Returns enriched adapter display data for the dashboard cards template.
     *
     * Each item is a flat array with:
     *   extensionId, key, title, description, icon, author,
     *   status, enabled, prerequisiteErrors, lastRunStatus
     */
    public function getAdapters(): array
    {
        $adapters  = (new AdapterRegistry())->getAll();
        $pluginMap = $this->loadPluginMap(array_keys($adapters));
        $result    = [];

        foreach ($adapters as $key => $adapter) {
            $plugin      = $pluginMap[$key] ?? null;
            $extensionId = $plugin !== null ? (int) $plugin->extension_id : 0;
            $enabled     = $plugin !== null && (bool) $plugin->enabled;

            $result[] = AdapterHelper::enrichAdapter($adapter, $extensionId, $enabled);
        }

        return $result;
    }

    /** Loads #__extensions rows for the given adapter element keys, indexed by element. */
    private function loadPluginMap(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $db    = $this->getDatabase();
        $type  = 'plugin';
        $group = 'j2commercemigrator';

        $query = $db->getQuery(true)
            ->select($db->quoteName(['extension_id', 'element', 'enabled']))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = :type')
            ->where($db->quoteName('folder') . ' = :folder')
            ->bind(':type', $type)
            ->bind(':folder', $group);

        $rows = $db->setQuery($query)->loadObjectList() ?: [];
        $map  = [];

        foreach ($rows as $row) {
            if (in_array($row->element, $keys, true)) {
                $map[$row->element] = $row;
            }
        }

        return $map;
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
