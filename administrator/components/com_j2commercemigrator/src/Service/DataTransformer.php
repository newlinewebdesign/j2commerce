<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Service;

class DataTransformer
{
    private const JSON_FIELDS = [
        'plugins', 'params', 'order_params', 'orderitem_attributes', 'cart_params',
        'transaction_details', 'all_billing', 'all_shipping',
        'all_payment', 'all_surcharge', 'orderdiscount_options',
        'orderfee_options', 'ordershipping_options', 'ordertax_options',
        'field_options', 'field_display',
    ];

    public const PK_REPLACEMENTS = [
        'j2store_address_id'             => 'j2commerce_address_id',
        'j2store_cartitem_id'            => 'j2commerce_cartitem_id',
        'j2store_cart_id'                => 'j2commerce_cart_id',
        'j2store_configuration_id'       => 'j2commerce_configuration_id',
        'j2store_country_id'             => 'j2commerce_country_id',
        'j2store_coupon_id'              => 'j2commerce_coupon_id',
        'j2store_currency_id'            => 'j2commerce_currency_id',
        'j2store_customfield_id'         => 'j2commerce_customfield_id',
        'j2store_emailtemplate_id'       => 'j2commerce_emailtemplate_id',
        'j2store_filtergroup_id'         => 'j2commerce_filtergroup_id',
        'j2store_filter_id'              => 'j2commerce_filter_id',
        'j2store_geozonerule_id'         => 'j2commerce_geozonerule_id',
        'j2store_geozone_id'             => 'j2commerce_geozone_id',
        'j2store_invoicetemplate_id'     => 'j2commerce_invoicetemplate_id',
        'j2store_length_id'              => 'j2commerce_length_id',
        'j2store_manufacturer_id'        => 'j2commerce_manufacturer_id',
        'j2store_metafield_id'           => 'j2commerce_metafield_id',
        'j2store_option_id'              => 'j2commerce_option_id',
        'j2store_optionvalue_id'         => 'j2commerce_optionvalue_id',
        'j2store_orderdiscount_id'       => 'j2commerce_orderdiscount_id',
        'j2store_orderdownload_id'       => 'j2commerce_orderdownload_id',
        'j2store_orderfee_id'            => 'j2commerce_orderfee_id',
        'j2store_orderhistory_id'        => 'j2commerce_orderhistory_id',
        'j2store_orderinfo_id'           => 'j2commerce_orderinfo_id',
        'j2store_orderitemattribute_id'  => 'j2commerce_orderitemattribute_id',
        'j2store_orderitem_id'           => 'j2commerce_orderitem_id',
        'j2store_order_id'               => 'j2commerce_order_id',
        'j2store_ordershipping_id'       => 'j2commerce_ordershipping_id',
        'j2store_orderstatus_id'         => 'j2commerce_orderstatus_id',
        'j2store_ordertax_id'            => 'j2commerce_ordertax_id',
        'j2store_product_filter_id'      => 'j2commerce_product_filter_id',
        'j2store_productoption_id'       => 'j2commerce_productoption_id',
        'j2store_product_optionvalue_id' => 'j2commerce_product_optionvalue_id',
        'j2store_productprice_id'        => 'j2commerce_productprice_id',
        'j2store_productfile_id'         => 'j2commerce_productfile_id',
        'j2store_productimage_id'        => 'j2commerce_productimage_id',
        'j2store_productquantity_id'     => 'j2commerce_productquantity_id',
        'j2store_product_id'             => 'j2commerce_product_id',
        'j2store_queue_id'               => 'j2commerce_queue_id',
        'j2store_shippingmethod_id'      => 'j2commerce_shippingmethod_id',
        'j2store_shippingrate_id'        => 'j2commerce_shippingrate_id',
        'j2store_taxprofile_id'          => 'j2commerce_taxprofile_id',
        'j2store_taxrate_id'             => 'j2commerce_taxrate_id',
        'j2store_taxrule_id'             => 'j2commerce_taxrule_id',
        'j2store_upload_id'              => 'j2commerce_upload_id',
        'j2store_variant_id'             => 'j2commerce_variant_id',
        'j2store_vendor_id'              => 'j2commerce_vendor_id',
        'j2store_voucher_id'             => 'j2commerce_voucher_id',
        'j2store_weight_id'              => 'j2commerce_weight_id',
        'j2store_zone_id'                => 'j2commerce_zone_id',
    ];

    private const DATE_COLUMNS = [
        'created_on', 'modified_on', 'created_date', 'modified_date',
        'ordered_date', 'order_created', 'order_state_date',
        'paid_date', 'shipped_date', 'expected_delivery_date',
        'coupon_start_date', 'coupon_end_date',
        'voucher_start_date', 'voucher_end_date',
        'publish_up', 'publish_down',
        'checked_out_time', 'reset_date',
        'from_date', 'to_date', 'valid_from', 'valid_to',
        'download_start_date', 'download_end_date',
        'sale_start_date', 'sale_end_date',
        'available_date',
    ];

    private const ZERO_DATE       = '0000-00-00 00:00:00';
    private const ZERO_DATE_SHORT = '0000-00-00';

    private const EXTRA_COLUMN_DEFAULTS = [
        'j2commerce_products' => [
            'hits' => 0,
        ],
        'j2commerce_emailtemplates' => [
            'body_json'  => null,
            'context'    => '',
            'custom_css' => null,
        ],
        'j2commerce_invoicetemplates' => [
            'body_json'        => null,
            'body_source'      => 'editor',
            'body_source_file' => '',
            'custom_css'       => null,
        ],
        'j2commerce_customfields' => [
            'field_placeholder'  => null,
            'field_autocomplete' => null,
            'field_width'        => '',
        ],
        'j2commerce_orders' => [
            'from_order_id' => '0',
        ],
    ];

    /**
     * Transform a row using adapter-provided PK replacements and token replacements,
     * plus the built-in JSON, date, and extra-column defaults pipeline.
     */
    public function transformRow(
        array $row,
        string $sourceTable,
        string $targetTable,
        array $adapterPkReplacements = [],
        array $adapterTokenReplacements = [],
        array $adapterRowTransformers = []
    ): array {
        $pkReplacements    = array_merge(self::PK_REPLACEMENTS, $adapterPkReplacements);
        $tokenReplacements = array_merge($this->getDefaultTokenReplacements(), $adapterTokenReplacements);

        $transformed = [];

        foreach ($row as $column => $value) {
            $newColumn = $pkReplacements[$column] ?? $column;

            if ($value !== null && in_array($column, self::JSON_FIELDS, true)) {
                $value = $this->transformJsonField((string) $value, $tokenReplacements);
            }

            if ($value !== null && in_array($newColumn, self::DATE_COLUMNS, true)
                && ($value === self::ZERO_DATE || $value === self::ZERO_DATE_SHORT)) {
                $value = null;
            }

            $transformed[$newColumn] = $value;
        }

        if (isset(self::EXTRA_COLUMN_DEFAULTS[$targetTable])) {
            foreach (self::EXTRA_COLUMN_DEFAULTS[$targetTable] as $col => $default) {
                if (!array_key_exists($col, $transformed)) {
                    $transformed[$col] = $default;
                }
            }
        }

        // Run adapter-provided row transformers
        foreach ($adapterRowTransformers as $table => $callable) {
            if ($table === $sourceTable || $table === $targetTable) {
                $transformed = $callable($transformed, ['sourceTable' => $sourceTable, 'targetTable' => $targetTable]);
            }
        }

        return $transformed;
    }

    public function transformJsonField(string $json, array $tokenReplacements = []): string
    {
        if ($json === '') {
            return $json;
        }

        $replacements = array_merge($this->getDefaultTokenReplacements(), $tokenReplacements);

        $decoded = json_decode($json, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return json_encode(
                $this->walkArray($decoded, $replacements),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        if ($this->isSerialized($json)) {
            return $this->transformSerializedField($json, $replacements);
        }

        return $this->replaceReferences($json, $replacements);
    }

    public function transformSerializedField(string $data, array $tokenReplacements = []): string
    {
        if ($data === '') {
            return $data;
        }

        $replacements = array_merge($this->getDefaultTokenReplacements(), $tokenReplacements);
        $unserialized = @unserialize($data);

        if ($unserialized === false && $data !== 'b:0;') {
            return $this->fixSerializedLengths($this->replaceReferences($data, $replacements));
        }

        if (is_array($unserialized)) {
            $unserialized = $this->walkArray($unserialized, $replacements);
        } elseif (is_string($unserialized)) {
            $unserialized = $this->replaceReferences($unserialized, $replacements);
        }

        return serialize($unserialized);
    }

    private function walkArray(array $arr, array $replacements): array
    {
        $result = [];

        foreach ($arr as $key => $value) {
            $newKey = is_string($key) ? $this->replaceReferences($key, $replacements) : $key;

            $result[$newKey] = match (true) {
                is_array($value)  => $this->walkArray($value, $replacements),
                is_string($value) => $this->replaceReferences($value, $replacements),
                default           => $value,
            };
        }

        return $result;
    }

    private function replaceReferences(string $text, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    private function getDefaultTokenReplacements(): array
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

    private function isSerialized(string $data): bool
    {
        return (bool) preg_match('/^[aOsbiNd]:/', $data);
    }

    private function fixSerializedLengths(string $data): string
    {
        return preg_replace_callback('/s:(\d+):"(.*?)";/s', static function (array $matches): string {
            return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
        }, $data);
    }

    public function normalizeOrderStatusCssClass(string $cssClass): ?string
    {
        $cssClass = trim($cssClass);

        if ($cssClass === '') {
            return null;
        }

        if (preg_match('/^badge text-bg-(info|success|danger|warning|secondary)$/', $cssClass)) {
            return null;
        }

        if (str_contains($cssClass, 'important')) {
            return 'badge text-bg-purple';
        }

        foreach (['info', 'success', 'danger', 'warning', 'secondary'] as $keyword) {
            if (str_contains($cssClass, $keyword)) {
                return 'badge text-bg-' . $keyword;
            }
        }

        return null;
    }
}
