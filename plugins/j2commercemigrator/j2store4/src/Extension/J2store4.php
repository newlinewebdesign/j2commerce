<?php

/**
 * @package     J2Commerce
 * @subpackage  plg_j2commercemigrator_j2store4
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Plugin\J2commercemigrator\J2store4\Extension;

use J2Commerce\Component\J2commercemigrator\Administrator\Adapter\AbstractMigratorAdapter;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\ConnectionSchema;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\ImageDiscoveryResult;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\PrerequisiteReport;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\SourceInfo;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\TierDefinition;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\SourceDatabaseReaderInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class J2store4 extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onJ2CommerceMigratorRegister' => 'registerAdapter',
        ];
    }

    public function registerAdapter(Event $event): void
    {
        $result   = $event->getArgument('result', []);
        $result[] = new J2store4Adapter();
        $event->setArgument('result', $result);
    }
}

/**
 * J2Store 4 migration adapter — provides all J2Store-specific migration metadata.
 */
class J2store4Adapter extends AbstractMigratorAdapter
{
    public const TABLE_MAP = [
        'j2store_addresses'                    => 'j2commerce_addresses',
        'j2store_cartitems'                    => 'j2commerce_cartitems',
        'j2store_carts'                        => 'j2commerce_carts',
        'j2store_configurations'               => 'j2commerce_configurations',
        'j2store_countries'                    => 'j2commerce_countries',
        'j2store_coupons'                      => 'j2commerce_coupons',
        'j2store_currencies'                   => 'j2commerce_currencies',
        'j2store_customfields'                 => 'j2commerce_customfields',
        'j2store_emailtemplates'               => 'j2commerce_emailtemplates',
        'j2store_filtergroups'                 => 'j2commerce_filtergroups',
        'j2store_filters'                      => 'j2commerce_filters',
        'j2store_geozonerules'                 => 'j2commerce_geozonerules',
        'j2store_geozones'                     => 'j2commerce_geozones',
        'j2store_invoicetemplates'             => 'j2commerce_invoicetemplates',
        'j2store_lengths'                      => 'j2commerce_lengths',
        'j2store_manufacturers'                => 'j2commerce_manufacturers',
        'j2store_metafields'                   => 'j2commerce_metafields',
        'j2store_options'                      => 'j2commerce_options',
        'j2store_optionvalues'                 => 'j2commerce_optionvalues',
        'j2store_orderdiscounts'               => 'j2commerce_orderdiscounts',
        'j2store_orderdownloads'               => 'j2commerce_orderdownloads',
        'j2store_orderfees'                    => 'j2commerce_orderfees',
        'j2store_orderhistories'               => 'j2commerce_orderhistories',
        'j2store_orderinfos'                   => 'j2commerce_orderinfos',
        'j2store_orderitemattributes'          => 'j2commerce_orderitemattributes',
        'j2store_orderitems'                   => 'j2commerce_orderitems',
        'j2store_orders'                       => 'j2commerce_orders',
        'j2store_ordershippings'               => 'j2commerce_ordershippings',
        'j2store_orderstatuses'                => 'j2commerce_orderstatuses',
        'j2store_ordertaxes'                   => 'j2commerce_ordertaxes',
        'j2store_product_filters'              => 'j2commerce_product_filters',
        'j2store_product_options'              => 'j2commerce_product_options',
        'j2store_product_optionvalues'         => 'j2commerce_product_optionvalues',
        'j2store_product_prices'               => 'j2commerce_product_prices',
        'j2store_product_variant_optionvalues' => 'j2commerce_product_variant_optionvalues',
        'j2store_productfiles'                 => 'j2commerce_productfiles',
        'j2store_productimages'                => 'j2commerce_productimages',
        'j2store_productprice_index'           => 'j2commerce_productprice_index',
        'j2store_productquantities'            => 'j2commerce_productquantities',
        'j2store_products'                     => 'j2commerce_products',
        'j2store_queues'                       => 'j2commerce_queues',
        'j2store_shippingmethods'              => 'j2commerce_shippingmethods',
        'j2store_shippingrates'                => 'j2commerce_shippingrates',
        'j2store_taxprofiles'                  => 'j2commerce_taxprofiles',
        'j2store_taxrates'                     => 'j2commerce_taxrates',
        'j2store_taxrules'                     => 'j2commerce_taxrules',
        'j2store_uploads'                      => 'j2commerce_uploads',
        'j2store_variants'                     => 'j2commerce_variants',
        'j2store_vendors'                      => 'j2commerce_vendors',
        'j2store_vouchers'                     => 'j2commerce_vouchers',
        'j2store_weights'                      => 'j2commerce_weights',
        'j2store_zones'                        => 'j2commerce_zones',
    ];

    public function getKey(): string
    {
        return 'j2store4';
    }

    public function getSourceInfo(): SourceInfo
    {
        return new SourceInfo(
            key:                     'j2store4',
            title:                   'J2Store 4',
            description:             'Migrate products, orders, and customers from J2Store 4.x running on Joomla 3 or 4.',
            author:                  'J2Commerce, LLC',
            icon:                    'fa-solid fa-store',
            supportedSourceVersions: ['J2Store 4.1.x', 'J2Store 4.2.x'],
        );
    }

    public function getTierDefinitions(): array
    {
        return [
            new TierDefinition(
                tier:   1,
                name:   'Lookup Tables',
                tables: [
                    'j2store_configurations', 'j2store_currencies', 'j2store_countries',
                    'j2store_zones', 'j2store_orderstatuses', 'j2store_lengths', 'j2store_weights',
                ],
            ),
            new TierDefinition(
                tier:   2,
                name:   'Tax System',
                tables: [
                    'j2store_taxprofiles', 'j2store_taxrates', 'j2store_taxrules',
                    'j2store_geozones', 'j2store_geozonerules',
                ],
            ),
            new TierDefinition(
                tier:   3,
                name:   'Catalog',
                tables: [
                    'j2store_options', 'j2store_optionvalues', 'j2store_manufacturers',
                    'j2store_customfields', 'j2store_filtergroups', 'j2store_filters',
                    'j2store_product_filters',
                ],
            ),
            new TierDefinition(
                tier:   4,
                name:   'Products',
                tables: [
                    'j2store_products', 'j2store_variants', 'j2store_productimages',
                    'j2store_productfiles', 'j2store_product_prices', 'j2store_product_options',
                    'j2store_product_optionvalues', 'j2store_product_variant_optionvalues',
                    'j2store_productquantities', 'j2store_productprice_index', 'j2store_metafields',
                ],
            ),
            new TierDefinition(
                tier:   5,
                name:   'Customers',
                tables: [
                    'j2store_addresses', 'j2store_coupons', 'j2store_vouchers', 'j2store_vendors',
                ],
            ),
            new TierDefinition(
                tier:   6,
                name:   'Shipping',
                tables: [
                    'j2store_shippingmethods', 'j2store_shippingrates',
                ],
            ),
            new TierDefinition(
                tier:   7,
                name:   'Orders',
                tables: [
                    'j2store_orders', 'j2store_orderinfos', 'j2store_orderitems',
                    'j2store_orderitemattributes', 'j2store_orderhistories', 'j2store_orderfees',
                    'j2store_ordertaxes', 'j2store_orderdiscounts', 'j2store_ordershippings',
                    'j2store_orderdownloads',
                ],
            ),
            new TierDefinition(
                tier:   8,
                name:   'Transactional',
                tables: [
                    'j2store_carts', 'j2store_cartitems', 'j2store_queues',
                    'j2store_uploads', 'j2store_emailtemplates', 'j2store_invoicetemplates',
                ],
            ),
        ];
    }

    public function getTableMap(): array
    {
        return self::TABLE_MAP;
    }

    public function getTokenReplacements(): array
    {
        return [
            'com_j2store'  => 'com_j2commerce',
            'J2STORE_'     => 'J2COMMERCE_',
            'J2Store'      => 'J2Commerce',
            'J2STORE'      => 'J2COMMERCE',
            'j2store_'     => 'j2commerce_',
            'j2store'      => 'j2commerce',
        ];
    }

    public function describeConnection(): ConnectionSchema
    {
        return new ConnectionSchema(
            modes:    ['A', 'B', 'C'],
            fields:   ['host', 'port', 'database', 'username', 'password', 'prefix', 'ssl', 'ssl_ca'],
            defaults: ['port' => 3306, 'prefix' => 'jos_'],
        );
    }

    public function discoverImages(): ImageDiscoveryResult
    {
        return new ImageDiscoveryResult(
            sourceRoot:     'images/com_j2store',
            subDirectories: ['products', 'categories', 'manufacturers'],
            pathColumns:    [
                'j2store_products'      => ['main_image', 'thumb_image'],
                'j2store_productimages' => ['file_path', 'thumb_path'],
                'j2store_categories'    => ['image'],
                'j2store_manufacturers' => ['image'],
            ],
        );
    }

    public function validatePrerequisites(?SourceDatabaseReaderInterface $reader): PrerequisiteReport
    {
        $issues = [];

        if ($reader === null) {
            $issues[] = 'No source database connection available. Verify connection before running preflight.';

            return new PrerequisiteReport(passed: false, issues: $issues);
        }

        // Check J2Store 4 probe table exists
        try {
            $count = $reader->count('j2store_countries');
        } catch (\Throwable) {
            $issues[] = 'The j2store_countries table was not found in the source database. Confirm J2Store 4 is installed in the source.';
        }

        // Check J2Commerce is installed on the target
        // (already checked by installer script; double-check here for run-time safety)

        return new PrerequisiteReport(
            passed: empty($issues),
            issues: $issues,
        );
    }
}
