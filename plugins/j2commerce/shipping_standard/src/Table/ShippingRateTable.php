<?php

/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.ShippingStandard
 *
 * @copyright   Copyright (C) 2024-2026 J2Commerce, LLC. All rights reserved.
 * @license     GNU General Public License version 2 or later;  see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Plugin\J2Commerce\ShippingStandard\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Shipping Rate table class.
 *
 * Maps to `#__j2commerce_shippingrates` table.
 *
 * @since  6.0.0
 */
class ShippingRateTable extends Table
{
    /**
     * Constructor.
     *
     * @param   DatabaseDriver  $db  Database connector object.
     *
     * @since   6.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__j2commerce_shippingrates', 'j2commerce_shippingrate_id', $db);
    }

    /**
     * Overloaded check function.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   6.0.0
     */
    public function check(): bool
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Set timestamps
        if (empty($this->created_date)) {
            $this->created_date = Factory::getDate()->toSql();
        }

        $this->modified_date = Factory::getDate()->toSql();

        // Ensure shipping_method_id is set
        if (empty($this->shipping_method_id)) {
            $this->shipping_method_id = 0;
        }

        // Ensure geozone_id is set
        if (empty($this->geozone_id)) {
            $this->geozone_id = 0;
        }

        // Set default numeric fields
        if (!isset($this->shipping_rate_price) || $this->shipping_rate_price === '') {
            $this->shipping_rate_price = '0.00000';
        }

        if (!isset($this->shipping_rate_weight_start) || $this->shipping_rate_weight_start === '') {
            $this->shipping_rate_weight_start = '0.000';
        }

        if (!isset($this->shipping_rate_weight_end) || $this->shipping_rate_weight_end === '') {
            $this->shipping_rate_weight_end = '0.000';
        }

        if (!isset($this->shipping_rate_handling) || $this->shipping_rate_handling === '') {
            $this->shipping_rate_handling = '0.00000';
        }

        return true;
    }
}
