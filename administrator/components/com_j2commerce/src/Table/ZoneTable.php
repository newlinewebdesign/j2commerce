<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Zone table class.
 *
 * @since  6.0.3
 */
class ZoneTable extends Table
{
    /**
     * Constructor.
     *
     * @param   DatabaseDriver  $db  Database connector object.
     *
     * @since   6.0.3
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__j2commerce_zones', 'j2commerce_zone_id', $db);

        // CRITICAL: J2Commerce uses 'enabled' column instead of Joomla's standard 'published'
        // This alias allows publish/unpublish toolbar actions to work correctly
        $this->setColumnAlias('published', 'enabled');
    }

    /**
     * Overloaded check function.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   6.0.3
     */
    public function check(): bool
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Set default ordering for new records
        if (empty($this->ordering)) {
            $this->ordering = 0;
        }

        // Set default enabled state for new records
        if (!isset($this->enabled) || $this->enabled === '') {
            $this->enabled = 1;
        }

        // Validate zone name
        if (empty($this->zone_name)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_ZONE_NAME')));
            return false;
        }

        // Validate zone code
        if (empty($this->zone_code)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_ZONE_CODE')));
            return false;
        }

        // Validate country_id
        if (empty($this->country_id) || !is_numeric($this->country_id)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_PLEASE_SELECT', Text::_('COM_J2COMMERCE_FIELD_COUNTRY')));
            return false;
        }

        // Ensure zone code is uppercase
        $this->zone_code = strtoupper(trim($this->zone_code));

        // Trim zone name
        if (!empty($this->zone_name)) {
            $this->zone_name = trim($this->zone_name);
        }

        return true;
    }
}