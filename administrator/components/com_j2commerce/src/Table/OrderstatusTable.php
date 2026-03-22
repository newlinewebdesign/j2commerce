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
 * Order Status table class.
 *
 * @since  6.0.7
 */
class OrderstatusTable extends Table
{
    /**
     * Constructor.
     *
     * @param   DatabaseDriver  $db  Database connector object.
     *
     * @since   6.0.7
     */
    public function __construct(DatabaseDriver $db)
    {
        // IMPORTANT: The primary key here ('j2commerce_orderstatus_id') MUST match
        // the $urlVar value in OrderstatusController's edit/save/cancel methods
        parent::__construct('#__j2commerce_orderstatuses', 'j2commerce_orderstatus_id', $db);

        // CRITICAL: J2Commerce uses 'enabled' column instead of Joomla's standard 'published'
        // This alias allows publish/unpublish toolbar actions to work correctly
        $this->setColumnAlias('published', 'enabled');
    }

    /**
     * Overloaded check function.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   6.0.7
     */
    public function check(): bool
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Validate status name
        if (empty($this->orderstatus_name)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_ORDERSTATUS_NAME')));
            return false;
        }

        // Trim status name
        $this->orderstatus_name = trim($this->orderstatus_name);

        // Validate CSS class
        if (empty($this->orderstatus_cssclass)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_ORDERSTATUS_CSSCLASS')));
            return false;
        }

        // Trim CSS class
        $this->orderstatus_cssclass = trim($this->orderstatus_cssclass);

        // Set default ordering for new records
        if (empty($this->ordering)) {
            $this->ordering = 0;
        }

        // Set default enabled state for new records
        if (!isset($this->enabled) || $this->enabled === '') {
            $this->enabled = 1;
        }

        // Ensure orderstatus_core is set (default to 0 for non-core)
        if (!isset($this->orderstatus_core)) {
            $this->orderstatus_core = 0;
        }

        return true;
    }
}