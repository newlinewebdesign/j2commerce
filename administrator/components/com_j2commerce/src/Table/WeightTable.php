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
 * Weight table class.
 *
 * @since  6.0.2
 */
class WeightTable extends Table
{
    /**
     * Constructor.
     *
     * @param   DatabaseDriver  $db  Database connector object.
     *
     * @since   6.0.2
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__j2commerce_weights', 'j2commerce_weight_id', $db);
        $this->setColumnAlias('published', 'enabled');
    }

    /**
     * Overloaded check function.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   6.0.2
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

        // Set default decimal places for new records
        if (!isset($this->num_decimals) || $this->num_decimals === '') {
            $this->num_decimals = 2;
        }

        // Validate weight title
        if (empty($this->weight_title)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_WEIGHT_TITLE')));

            return false;
        }

        // Validate weight unit
        if (empty($this->weight_unit)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_WEIGHT_UNIT')));

            return false;
        }

        // Validate weight value
        if (!isset($this->weight_value) || $this->weight_value === '') {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_WEIGHT_VALUE')));

            return false;
        }

        if ($this->weight_value <= 0) {
            $this->setError(Text::_('COM_J2COMMERCE_ERR_WEIGHT_VALUE_POSITIVE'));

            return false;
        }

        return true;
    }
}