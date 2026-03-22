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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Voucher table class.
 *
 * @since  6.0.6
 */
class VoucherTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__j2commerce_vouchers', 'j2commerce_voucher_id', $db);

        // CRITICAL: J2Commerce uses 'enabled' column instead of Joomla's standard 'published'
        $this->setColumnAlias('published', 'enabled');
    }

    public function check(): bool
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Validate voucher code
        if (empty($this->voucher_code)) {
            $this->setError(Text::sprintf('COM_J2COMMERCE_ERR_FIELD_REQUIRED', Text::_('COM_J2COMMERCE_FIELD_VOUCHER_CODE')));
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

        // Set created_on and created_by for new records
        if (empty($this->j2commerce_voucher_id)) {
            if (empty($this->created_on)) {
                $this->created_on = Factory::getDate()->toSql();
            }
            if (empty($this->created_by)) {
                $this->created_by = Factory::getApplication()->getIdentity()->id;
            }
        }

        // Set default voucher_type if empty
        if (empty($this->voucher_type)) {
            $this->voucher_type = 'giftcard';
        }

        // Set default voucher_value if empty
        if (!isset($this->voucher_value) || $this->voucher_value === '') {
            $this->voucher_value = 0.00000000;
        }

        // Set default empty strings for required NOT NULL fields if empty
        if (!isset($this->order_id)) {
            $this->order_id = '';
        }

        if (!isset($this->email_to)) {
            $this->email_to = '';
        }

        if (!isset($this->subject)) {
            $this->subject = '';
        }

        if (!isset($this->email_body)) {
            $this->email_body = '';
        }

        return true;
    }
}
