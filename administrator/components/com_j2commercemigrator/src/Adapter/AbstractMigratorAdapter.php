<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Adapter;

use J2Commerce\Component\J2commercemigrator\Administrator\Dto\PrerequisiteReport;

abstract class AbstractMigratorAdapter implements MigratorAdapterInterface
{
    public function getColumnMap(string $sourceTable): array
    {
        return [];
    }

    public function getRowTransformers(): array
    {
        return [];
    }

    public function getConflictKey(string $targetTable): string
    {
        // Derive PK from target table name: j2commerce_products → j2commerce_product_id
        // Strip the j2commerce_ prefix, remove trailing 's', append _id
        $name = preg_replace('/^j2commerce_/', '', $targetTable);

        // Handle irregular plurals we know about
        $irregulars = [
            'addresses'              => 'address',
            'currencies'             => 'currency',
            'countries'              => 'country',
            'orderstatuses'          => 'orderstatus',
            'geozonerules'           => 'geozonerule',
            'geozones'               => 'geozone',
            'taxprofiles'            => 'taxprofile',
            'taxrates'               => 'taxrate',
            'taxrules'               => 'taxrule',
            'manufacturers'          => 'manufacturer',
            'orderhistories'         => 'orderhistory',
            'orderitems'             => 'orderitem',
            'orderdiscounts'         => 'orderdiscount',
            'orderdownloads'         => 'orderdownload',
            'orderfees'              => 'orderfee',
            'ordershippings'         => 'ordershipping',
            'ordertaxes'             => 'ordertax',
            'orderitemattributes'    => 'orderitemattribute',
            'productimages'          => 'productimage',
            'productfiles'           => 'productfile',
            'productquantities'      => 'productquantity',
            'product_filters'        => 'product_filter',
            'product_options'        => 'product_option',
            'product_optionvalues'   => 'product_optionvalue',
            'product_variant_optionvalues' => 'product_variant_optionvalue',
            'product_prices'         => 'product_price',
            'filtergroups'           => 'filtergroup',
            'filters'                => 'filter',
            'customfields'           => 'customfield',
            'emailtemplates'         => 'emailtemplate',
            'invoicetemplates'       => 'invoicetemplate',
            'metafields'             => 'metafield',
            'optionvalues'           => 'optionvalue',
            'cartitems'              => 'cartitem',
            'shippingmethods'        => 'shippingmethod',
            'shippingrates'          => 'shippingrate',
            'configurations'         => 'configuration',
            'orderinfos'             => 'orderinfo',
            'uploads'                => 'upload',
            'vendors'                => 'vendor',
            'vouchers'               => 'voucher',
            'variants'               => 'variant',
        ];

        $singular = $irregulars[$name] ?? rtrim($name, 's');

        return 'j2commerce_' . $singular . '_id';
    }

    public function getTokenReplacements(): array
    {
        return [];
    }

    public function validatePrerequisites(): PrerequisiteReport
    {
        return new PrerequisiteReport(passed: true, issues: []);
    }
}
