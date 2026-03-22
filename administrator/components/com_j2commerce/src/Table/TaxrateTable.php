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
 * Taxrate table class.
 *
 * @since  6.0.3
 */
class TaxrateTable extends Table
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
        parent::__construct('#__j2commerce_taxrates', 'j2commerce_taxrate_id', $db);
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

        // Validate taxrate name
        if (empty($this->taxrate_name)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_TAXRATE_NAME')));

            return false;
        }

        // Validate tax percent
        if (!isset($this->tax_percent) || $this->tax_percent === '') {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_TAX_PERCENT')));

            return false;
        }

        if ($this->tax_percent < 0) {
            $this->setError(Text::_('COM_J2COMMERCE_ERR_TAX_PERCENT_NEGATIVE'));

            return false;
        }

        // Validate geozone_id
        if (!isset($this->geozone_id) || $this->geozone_id === '' || $this->geozone_id <= 0) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_PLEASE_SELECT', Text::_('COM_J2COMMERCE_FIELD_GEOZONE')));

            return false;
        }

        return true;
    }
}