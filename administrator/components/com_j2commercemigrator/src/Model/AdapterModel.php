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

use J2Commerce\Component\J2commercemigrator\Administrator\Adapter\MigratorAdapterInterface;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\AdapterRegistry;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Adapter model — resolves registered adapter plugins and surfaces their metadata.
 */
class AdapterModel extends BaseDatabaseModel
{
    private function registry(): AdapterRegistry
    {
        return new AdapterRegistry();
    }

    /** Returns all registered adapters as instances. */
    public function getAll(): array
    {
        return array_values($this->registry()->getAll());
    }

    /** Returns a single adapter by its key, or null if not registered. */
    public function getByKey(string $key): ?MigratorAdapterInterface
    {
        return $this->registry()->get($key);
    }

    /** Returns true when the given adapter key is registered. */
    public function has(string $key): bool
    {
        return $this->registry()->has($key);
    }

    /**
     * Returns serializable metadata for all adapters suitable for JSON output
     * (avoids leaking internal service objects to the controller).
     */
    public function getAdapterList(): array
    {
        $list = [];

        foreach ($this->registry()->getAll() as $adapter) {
            $info   = $adapter->getSourceInfo();
            $list[] = [
                'key'         => $adapter->getKey(),
                'title'       => $info->title,
                'description' => $info->description,
                'icon'        => $info->icon,
                'author'      => $info->author,
                'version'     => $info->version,
            ];
        }

        return $list;
    }
}
