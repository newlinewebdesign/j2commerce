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
 * Invoicetemplate Table class.
 *
 * @since  6.0.0
 */
class InvoicetemplateTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   6.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_j2commerce.invoicetemplate';

        parent::__construct('#__j2commerce_invoicetemplates', 'j2commerce_invoicetemplate_id', $db);
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   6.0.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Check for valid title
        if (empty($this->title)) {
            $this->setError(Text::_('COM_J2COMMERCE_ERROR_INVOICETEMPLATE_TITLE_REQUIRED'));
            return false;
        }

        // Check for valid invoice type
        if (empty($this->invoice_type)) {
            $this->setError(Text::_('COM_J2COMMERCE_ERROR_INVOICETEMPLATE_TYPE_REQUIRED'));
            return false;
        }

        // Check for valid body content
        if (empty($this->body)) {
            $this->setError(Text::_('COM_J2COMMERCE_ERROR_INVOICETEMPLATE_BODY_REQUIRED'));
            return false;
        }

        // Defaults for new GrapeJS editor columns
        if (empty($this->body_source)) {
            $this->body_source = 'editor';
        }

        if (!isset($this->body_source_file)) {
            $this->body_source_file = '';
        }

        // Trim the title and other text fields
        $this->title = trim($this->title);
        $this->invoice_type = trim($this->invoice_type);
        $this->orderstatus_id = trim($this->orderstatus_id);
        $this->group_id = trim($this->group_id);
        $this->paymentmethod = trim($this->paymentmethod);

        // Set default values if not provided
        if (!isset($this->enabled)) {
            $this->enabled = 1;
        }

        if (!isset($this->ordering)) {
            $this->ordering = $this->getNextOrder();
        }

        // Set default language if empty
        if (empty($this->language)) {
            $this->language = '*';
        }

        // Validate invoice type — built-in types always allowed, plugin types must match safe pattern
        $builtInTypes = ['invoice', 'receipt', 'packingslip'];
        if (!in_array($this->invoice_type, $builtInTypes) && !preg_match('/^[a-z][a-z0-9_]{1,49}$/', $this->invoice_type)) {
            $this->setError(Text::_('COM_J2COMMERCE_ERROR_INVOICETEMPLATE_INVALID_TYPE'));
            return false;
        }

        return true;
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
     * @return  boolean  True on success; false if $pks is empty.
     *
     * @since   6.0.0
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
                $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
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

        try {
            $this->_db->execute();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // If the Table instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->enabled = $state;
        }

        return true;
    }

    /**
     * Method to return the next ordering value for a new record.
     *
     * @param   string  $where  Additional where clause to use for the query.
     *
     * @return  integer  The next ordering value.
     *
     * @since   6.0.0
     */
    public function getNextOrder($where = '')
    {
        // If there is no ordering column just return 1
        if (!property_exists($this, 'ordering')) {
            return 1;
        }

        $query = $this->_db->getQuery(true)
            ->select('MAX(ordering)')
            ->from($this->_tbl);

        if ($where) {
            $query->where($where);
        }

        $this->_db->setQuery($query);
        $max = (int) $this->_db->loadResult();

        return $max + 1;
    }

    /**
     * Method to reorder the rows.
     *
     * @param   string   $where  The WHERE clause for the reorder query.
     *
     * @return  boolean  True on success.
     *
     * @since   6.0.0
     */
    public function reorder($where = '')
    {
        // If there is no ordering column do nothing.
        if (!property_exists($this, 'ordering')) {
            return false;
        }

        $query = $this->_db->getQuery(true)
            ->select($this->_tbl_key . ', ordering')
            ->from($this->_tbl)
            ->order('ordering ASC');

        if ($where) {
            $query->where($where);
        }

        $this->_db->setQuery($query);
        $rows = $this->_db->loadRowList();

        // Reorder the rows
        $order = 1;
        foreach ($rows as $row) {
            if ($row[1] >= 0) {
                // Only reorder if the ordering value is not negative
                $query = $this->_db->getQuery(true)
                    ->update($this->_tbl)
                    ->set($this->_db->quoteName('ordering') . ' = :ordering')
                    ->where($this->_tbl_key . ' = :pk')
                    ->bind(':ordering', $order, ParameterType::INTEGER)
                    ->bind(':pk', $row[0], ParameterType::INTEGER);

                $this->_db->setQuery($query);
                $this->_db->execute();
                $order++;
            }
        }

        return true;
    }

    /**
     * Method to move a row up in the ordering.
     *
     * @return  boolean  True on success.
     *
     * @since   6.0.0
     */
    public function orderUp()
    {
        $k = $this->_tbl_key;

        if (empty($this->$k)) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
            return false;
        }

        // Get the previous row
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where($this->_db->quoteName('ordering') . ' < :ordering')
            ->order('ordering DESC')
            ->bind(':ordering', $this->ordering, ParameterType::INTEGER);

        $this->_db->setQuery($query, 0, 1);
        $row = $this->_db->loadAssoc();

        if (!$row) {
            return true; // Already at the top
        }

        // Swap ordering values
        $query = $this->_db->getQuery(true)
            ->update($this->_tbl)
            ->set($this->_db->quoteName('ordering') . ' = :old_ordering')
            ->where($this->_tbl_key . ' = :pk')
            ->bind(':old_ordering', $this->ordering, ParameterType::INTEGER)
            ->bind(':pk', $row[$k], ParameterType::INTEGER);

        $this->_db->setQuery($query);
        $this->_db->execute();

        $query = $this->_db->getQuery(true)
            ->update($this->_tbl)
            ->set($this->_db->quoteName('ordering') . ' = :new_ordering')
            ->where($this->_tbl_key . ' = :pk')
            ->bind(':new_ordering', $row['ordering'], ParameterType::INTEGER)
            ->bind(':pk', $this->$k, ParameterType::INTEGER);

        $this->_db->setQuery($query);
        $this->_db->execute();

        $this->ordering = $row['ordering'];

        return true;
    }

    /**
     * Method to move a row down in the ordering.
     *
     * @return  boolean  True on success.
     *
     * @since   6.0.0
     */
    public function orderDown()
    {
        $k = $this->_tbl_key;

        if (empty($this->$k)) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
            return false;
        }

        // Get the next row
        $query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_tbl)
            ->where($this->_db->quoteName('ordering') . ' > :ordering')
            ->order('ordering ASC')
            ->bind(':ordering', $this->ordering, ParameterType::INTEGER);

        $this->_db->setQuery($query, 0, 1);
        $row = $this->_db->loadAssoc();

        if (!$row) {
            return true; // Already at the bottom
        }

        // Swap ordering values
        $query = $this->_db->getQuery(true)
            ->update($this->_tbl)
            ->set($this->_db->quoteName('ordering') . ' = :old_ordering')
            ->where($this->_tbl_key . ' = :pk')
            ->bind(':old_ordering', $this->ordering, ParameterType::INTEGER)
            ->bind(':pk', $row[$k], ParameterType::INTEGER);

        $this->_db->setQuery($query);
        $this->_db->execute();

        $query = $this->_db->getQuery(true)
            ->update($this->_tbl)
            ->set($this->_db->quoteName('ordering') . ' = :new_ordering')
            ->where($this->_tbl_key . ' = :pk')
            ->bind(':new_ordering', $row['ordering'], ParameterType::INTEGER)
            ->bind(':pk', $this->$k, ParameterType::INTEGER);

        $this->_db->setQuery($query);
        $this->_db->execute();

        $this->ordering = $row['ordering'];

        return true;
    }
}
