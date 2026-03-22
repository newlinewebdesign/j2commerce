<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace J2Commerce\Component\J2commerce\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

/**
 * Filtergroup Table
 *
 * @since  6.0.0
 */
class FiltergroupTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since  6.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_j2commerce.filtergroup';

        parent::__construct('#__j2commerce_filtergroups', 'j2commerce_filtergroup_id', $db);
    }

    /**
     * Method to perform sanity checks on the Table instance properties to ensure
     * they are safe to store in the database.
     *
     * @return  boolean  True if the instance is sane and able to be stored in the database.
     *
     * @throws  \Exception
     * @since  6.0.0
     */
    public function check()
    {
        parent::check();

        // Check for a group name.
        if (trim($this->group_name) == '') {
            throw new \InvalidArgumentException(Text::_('COM_J2COMMERCE_FILTERGROUP_ERROR_NAME'));
        }

        // Verify that the group name is unique
        if ($this->group_name) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__j2commerce_filtergroups'))
                ->where($db->quoteName('group_name') . ' = :name')
                ->bind(':name', $this->group_name);

            if ($this->j2commerce_filtergroup_id) {
                $query->where($db->quoteName('j2commerce_filtergroup_id') . ' != :id')
                    ->bind(':id', $this->j2commerce_filtergroup_id, ParameterType::INTEGER);
            }

            $db->setQuery($query);
            $duplicate = (int) $db->loadResult();

            if ($duplicate > 0) {
                throw new \InvalidArgumentException(Text::_('COM_J2COMMERCE_FILTERGROUP_ERROR_NAME_EXISTS'));
            }
        }

        return true;
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since  6.0.0
     */
    public function store($updateNulls = true)
    {
        // Set default enabled value for new records
        if (!(int) $this->j2commerce_filtergroup_id && !isset($this->enabled)) {
            $this->enabled = 1;
        }

        // Set default ordering value for new records
        if (!(int) $this->j2commerce_filtergroup_id && !isset($this->ordering)) {
            $this->ordering = $this->getNextOrder();
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to set the enabled state for a row or list of rows in the database
     * table. The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param   mixed    $pks     An optional array of primary key values to update.
     *                           If not set the instance property value is used.
     * @param   integer  $state   The enabled state. eg. [0 = disabled, 1 = enabled]
     * @param   integer  $userId  The user ID of the user performing the operation.
     *
     * @return  boolean  True on success.
     *
     * @throws  \RuntimeException
     * @since  6.0.0
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        $k = $this->_tbl_key;

        // Sanitize input.
        $pks = array_unique((array) $pks);
        $userId = (int) $userId;
        $state = (int) $state;

        // If there are no primary keys set then use the instance.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = [(int) $this->$k];
            } else {
                throw new \RuntimeException(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Update the enabled field for the list of primary keys.
        $query = $this->_db->getQuery(true)
            ->update($this->_tbl)
            ->set($this->_db->quoteName('enabled') . ' = :state')
            ->where($where)
            ->bind(':state', $state, ParameterType::INTEGER);

        $this->_db->setQuery($query);
        $this->_db->execute();

        // If the Table instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->enabled = $state;
        }

        return true;
    }
}
