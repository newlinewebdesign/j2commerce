<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

\defined('_JEXEC') or die;

final class BuilderModel extends BaseDatabaseModel
{
    public function getProject(string $pluginElement, string $fileId): ?object
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__j2commerce_builder_projects'))
            ->where($db->quoteName('plugin_element') . ' = :element')
            ->where($db->quoteName('file_id') . ' = :fileId')
            ->bind(':element', $pluginElement)
            ->bind(':fileId', $fileId);

        return $db->setQuery($query)->loadObject() ?: null;
    }

    public function saveProject(string $pluginElement, string $fileId, array $data): bool
    {
        $table    = $this->getTable('BuilderProject', 'Administrator');
        $user     = Factory::getApplication()->getIdentity();
        $existing = $this->getProject($pluginElement, $fileId);

        if ($existing) {
            $table->load($existing->j2commerce_builder_project_id);
        }

        $table->plugin_element = $pluginElement;
        $table->file_id        = $fileId;

        if (isset($data['file_type'])) {
            $table->file_type = $data['file_type'];
        }

        if (isset($data['project_data'])) {
            $table->project_data = \is_string($data['project_data']) ? $data['project_data'] : json_encode($data['project_data']);
        }

        if (isset($data['block_order'])) {
            $table->block_order = \is_string($data['block_order']) ? $data['block_order'] : json_encode($data['block_order']);
        }

        if (isset($data['region_data'])) {
            $table->region_data = \is_string($data['region_data']) ? $data['region_data'] : json_encode($data['region_data']);
        }

        if (isset($data['generated_php'])) {
            $table->generated_php = $data['generated_php'];
        }

        if (isset($data['preview_product_id'])) {
            $table->preview_product_id = (int) $data['preview_product_id'];
        }

        $table->modified_by = $user?->id ?? 0;

        if (!$existing) {
            $table->created_by = $user?->id ?? 0;
        }

        if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }

        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        return true;
    }

    public function deleteProject(string $pluginElement, string $fileId): bool
    {
        $existing = $this->getProject($pluginElement, $fileId);

        if (!$existing) {
            return true;
        }

        $table = $this->getTable('BuilderProject', 'Administrator');

        return $table->delete($existing->j2commerce_builder_project_id);
    }

    public function getTable($name = 'BuilderProject', $prefix = 'Administrator', $options = []): \Joomla\CMS\Table\Table
    {
        return parent::getTable($name, $prefix, $options);
    }
}
