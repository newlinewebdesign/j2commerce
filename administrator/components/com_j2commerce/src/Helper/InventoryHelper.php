<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Inventory Helper class for J2Commerce.
 *
 * Provides static methods for inventory and stock management operations.
 * Handles stock tracking, quantity validation, backorders, and stock status display.
 *
 * Migrated from J2Store FOF 2 J2Product class (inventory methods) to Joomla 6 native MVC.
 *
 * @since  6.0.0
 */
class InventoryHelper
{
    /**
     * Cached database instance
     *
     * @var   DatabaseInterface|null
     * @since 6.0.0
     */
    private static ?DatabaseInterface $db = null;

    // =========================================================================
    // DATABASE ACCESS
    // =========================================================================

    /**
     * Get the database instance via Joomla 6 DI container.
     *
     * @return  DatabaseInterface
     *
     * @since   6.0.0
     */
    private static function getDatabase(): DatabaseInterface
    {
        if (self::$db === null) {
            self::$db = Factory::getContainer()->get(DatabaseInterface::class);
        }

        return self::$db;
    }

    // =========================================================================
    // STOCK MANAGEMENT CORE METHODS
    // =========================================================================

    /**
     * Check if inventory management is enabled for a variant.
     *
     * A variant has managed stock when:
     * 1. Global inventory is enabled in component config
     * 2. The variant's manage_stock flag is set to 1
     *
     * @param   object       $variant  The variant object with manage_stock property.
     * @param   bool|null    $globalInventoryEnabled  Override for global setting (for testing).
     *
     * @return  bool  True if managing stock for this variant.
     *
     * @since   6.0.0
     */
    public static function isManagingStock(object $variant, ?bool $globalInventoryEnabled = null): bool
    {
        // Get global inventory setting if not overridden
        if ($globalInventoryEnabled === null) {
            // TODO: Get from J2Commerce config when ConfigHelper is available
            // $globalInventoryEnabled = ConfigHelper::get('enable_inventory', true);
            $globalInventoryEnabled = true;
        }

        // If global inventory is disabled, no variants are managed
        if (!$globalInventoryEnabled) {
            return false;
        }

        // Check variant-level manage_stock flag
        if (empty($variant->manage_stock) || $variant->manage_stock != 1) {
            return false;
        }

        return true;
    }

    /**
     * Check if backorders are allowed for a variant.
     *
     * Backorder values:
     * - 0 = No backorders allowed
     * - 1 = Allow backorders (silent)
     * - 2 = Allow backorders with notification
     *
     * @param   object  $variant  The variant object with allow_backorder property.
     *
     * @return  bool  True if backorders are allowed (value >= 1).
     *
     * @since   6.0.0
     */
    public static function isBackorderAllowed(object $variant): bool
    {
        return isset($variant->allow_backorder) && (int) $variant->allow_backorder >= 1;
    }

    /**
     * Check if backorders require customer notification.
     *
     * Only returns true when:
     * 1. Stock is being managed
     * 2. allow_backorder = 2 (notify customer)
     *
     * @param   object  $variant  The variant object.
     *
     * @return  bool  True if backorder notification is required.
     *
     * @since   6.0.0
     */
    public static function requiresBackorderNotification(object $variant): bool
    {
        return self::isManagingStock($variant)
            && !empty($variant->allow_backorder)
            && (int) $variant->allow_backorder === 2;
    }

    // =========================================================================
    // STOCK QUANTITY METHODS
    // =========================================================================

    /**
     * Get stock quantity for a variant from the productquantities table.
     *
     * @param   int  $variantId  The variant ID.
     *
     * @return  int  Available stock quantity (excluding on_hold).
     *
     * @since   6.0.0
     */
    public static function getStockQuantity(int $variantId): int
    {
        if ($variantId < 1) {
            return 0;
        }

        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('quantity'))
            ->from($db->quoteName('#__j2commerce_productquantities'))
            ->where($db->quoteName('variant_id') . ' = :variantId')
            ->bind(':variantId', $variantId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (int) ($db->loadResult() ?? 0);
    }

    /**
     * Get full stock record for a variant including on_hold and sold counts.
     *
     * @param   int  $variantId  The variant ID.
     *
     * @return  object|null  Stock record object or null if not found.
     *
     * @since   6.0.0
     */
    public static function getStockRecord(int $variantId): ?object
    {
        if ($variantId < 1) {
            return null;
        }

        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName([
                'j2commerce_productquantity_id',
                'variant_id',
                'quantity',
                'on_hold',
                'sold',
                'product_attributes'
            ]))
            ->from($db->quoteName('#__j2commerce_productquantities'))
            ->where($db->quoteName('variant_id') . ' = :variantId')
            ->bind(':variantId', $variantId, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    /**
     * Get available quantity (quantity minus on_hold).
     *
     * @param   int  $variantId  The variant ID.
     *
     * @return  int  Available quantity for purchase.
     *
     * @since   6.0.0
     */
    public static function getAvailableQuantity(int $variantId): int
    {
        $record = self::getStockRecord($variantId);

        if (!$record) {
            return 0;
        }

        $available = (int) $record->quantity - (int) $record->on_hold;

        return max(0, $available);
    }

    /**
     * Get on-hold quantity for a variant.
     *
     * On-hold stock is reserved for pending orders but not yet confirmed.
     *
     * @param   int  $variantId  The variant ID.
     *
     * @return  int  On-hold quantity.
     *
     * @since   6.0.0
     */
    public static function getOnHoldQuantity(int $variantId): int
    {
        if ($variantId < 1) {
            return 0;
        }

        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('on_hold'))
            ->from($db->quoteName('#__j2commerce_productquantities'))
            ->where($db->quoteName('variant_id') . ' = :variantId')
            ->bind(':variantId', $variantId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (int) ($db->loadResult() ?? 0);
    }

    /**
     * Get sold quantity for a variant.
     *
     * @param   int  $variantId  The variant ID.
     *
     * @return  int  Total sold quantity.
     *
     * @since   6.0.0
     */
    public static function getSoldQuantity(int $variantId): int
    {
        if ($variantId < 1) {
            return 0;
        }

        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('sold'))
            ->from($db->quoteName('#__j2commerce_productquantities'))
            ->where($db->quoteName('variant_id') . ' = :variantId')
            ->bind(':variantId', $variantId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (int) ($db->loadResult() ?? 0);
    }

    // =========================================================================
    // STOCK VALIDATION METHODS
    // =========================================================================

    /**
     * Check overall stock status for a variant and requested quantity.
     *
     * This is the main entry point for stock validation during add-to-cart.
     *
     * @param   object  $variant   The variant object.
     * @param   int     $quantity  The requested quantity.
     *
     * @return  bool  True if stock is available (or backorders allowed).
     *
     * @since   6.0.0
     */
    public static function checkStockStatus(object $variant, int $quantity): bool
    {
        // If not managing stock, always available
        if (!self::isManagingStock($variant)) {
            return true;
        }

        // If backorders are allowed, always available
        if (self::isBackorderAllowed($variant)) {
            return true;
        }

        // Validate against actual stock
        return self::validateStock($variant, $quantity);
    }

    /**
     * Validate stock quantity against variant availability.
     *
     * Checks:
     * 1. Stock quantity > 0
     * 2. Requested qty <= available stock
     * 3. Variant availability flag is set
     *
     * @param   object  $variant   The variant object with quantity and availability.
     * @param   int     $quantity  The requested quantity.
     *
     * @return  bool  True if stock is sufficient.
     *
     * @since   6.0.0
     */
    public static function validateStock(object $variant, int $quantity = 1): bool
    {
        $variantQty = (int) ($variant->quantity ?? 0);

        // No stock available
        if ($variantQty <= 0) {
            return false;
        }

        // Requested quantity exceeds available stock
        if ($quantity > $variantQty) {
            return false;
        }

        // Check availability flag (product can be set to unavailable even with stock)
        if (empty($variant->availability)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a variant is in stock.
     *
     * Simple check for stock > 0 OR backorders allowed.
     *
     * @param   object  $variant  The variant object.
     *
     * @return  bool  True if in stock or backorders allowed.
     *
     * @since   6.0.0
     */
    public static function isInStock(object $variant): bool
    {
        // If backorders are allowed, always considered "in stock"
        if (self::isBackorderAllowed($variant)) {
            return true;
        }

        $quantity = (int) ($variant->quantity ?? 0);

        return $quantity > 0 && !empty($variant->availability);
    }

    /**
     * Check if a variant is out of stock.
     *
     * @param   object  $variant  The variant object.
     *
     * @return  bool  True if out of stock.
     *
     * @since   6.0.0
     */
    public static function isOutOfStock(object $variant): bool
    {
        return !self::isInStock($variant);
    }

    /**
     * Check if the stock is low (at or below a notification threshold).
     *
     * @param   object  $variant  The variant object with quantity and notify_qty.
     *
     * @return  bool  True if the stock is at or below notification level.
     *
     * @since   6.0.0
     */
    public static function isLowStock(object $variant): bool
    {
        $quantity = (int) ($variant->quantity ?? 0);
        $notifyQty = (int) ($variant->notify_qty ?? 0);

        // If no notification threshold set, never considered "low"
        if ($notifyQty <= 0) {
            return false;
        }

        return $quantity > 0 && $quantity <= $notifyQty;
    }

    // =========================================================================
    // QUANTITY RESTRICTION METHODS
    // =========================================================================

    /**
     * Apply store configuration defaults to variant quantity restrictions.
     *
     * If variant is configured to use store defaults, copies those values.
     * Modifies the variant object by reference.
     *
     * @param   object    $variant       The variant object (modified by reference).
     * @param   Registry  $storeConfig   Store configuration (optional).
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public static function applyQuantityRestrictions(object &$variant, ?Registry $storeConfig = null): void
    {
        // Get store defaults from config or use sensible defaults
        // TODO: Get from J2Commerce StoreHelper when available
        $storeMinSaleQty = $storeConfig?->get('store_min_sale_qty', 1.0) ?? 1.0;
        $storeMaxSaleQty = $storeConfig?->get('store_max_sale_qty', 0.0) ?? 0.0;
        $storeNotifyQty = $storeConfig?->get('store_notify_qty', 5.0) ?? 5.0;

        // Apply min sale quantity from store config
        if (!empty($variant->use_store_config_min_sale_qty) && $variant->use_store_config_min_sale_qty > 0) {
            $variant->min_sale_qty = (float) $storeMinSaleQty;
        }

        // Apply max sale quantity from store config
        if (!empty($variant->use_store_config_max_sale_qty) && $variant->use_store_config_max_sale_qty > 0) {
            $variant->max_sale_qty = (float) $storeMaxSaleQty;
        }

        // Apply notification quantity from store config
        if (!empty($variant->use_store_config_notify_qty) && $variant->use_store_config_notify_qty > 0) {
            $variant->notify_qty = (float) $storeNotifyQty;
        }
    }

    /**
     * Validate quantity against min/max sale restrictions.
     *
     * @param   object  $variant       The variant object with restriction settings.
     * @param   float   $cartTotalQty  Current quantity of this variant in cart.
     * @param   float   $addToQty      Additional quantity being added.
     *
     * @return  string  Error message if validation fails, empty string if valid.
     *
     * @since   6.0.0
     */
    public static function validateQuantityRestrictions(
        object $variant,
        float $cartTotalQty,
        float $addToQty = 0.0
    ): string {
        // Skip validation if restrictions not enabled
        if (empty($variant->quantity_restriction)) {
            return '';
        }

        $quantityTotal = $cartTotalQty + $addToQty;
        $min = (float) ($variant->min_sale_qty ?? 0);
        $max = (float) ($variant->max_sale_qty ?? 0);

        // Check maximum first (more common restriction)
        if ($max > 0 && $quantityTotal > $max) {
            return Text::sprintf(
                'COM_J2COMMERCE_ERROR_MAX_QUANTITY_FOR_PRODUCT',
                (int) $max,
                (int) $cartTotalQty
            );
        }

        // Check minimum
        if ($min > 0 && $quantityTotal < $min) {
            return Text::sprintf(
                'COM_J2COMMERCE_ERROR_MIN_QUANTITY_FOR_PRODUCT',
                (int) $min
            );
        }

        return '';
    }

    /**
     * Get the minimum purchase quantity for a variant.
     *
     * @param   object  $variant  The variant object.
     *
     * @return  float  Minimum quantity (0 = no minimum).
     *
     * @since   6.0.0
     */
    public static function getMinimumQuantity(object $variant): float
    {
        if (empty($variant->quantity_restriction)) {
            return 0.0;
        }

        return (float) ($variant->min_sale_qty ?? 0);
    }

    /**
     * Get the maximum purchase quantity for a variant.
     *
     * @param   object  $variant  The variant object.
     *
     * @return  float  Maximum quantity (0 = no maximum).
     *
     * @since   6.0.0
     */
    public static function getMaximumQuantity(object $variant): float
    {
        if (empty($variant->quantity_restriction)) {
            return 0.0;
        }

        return (float) ($variant->max_sale_qty ?? 0);
    }

    // =========================================================================
    // CART QUANTITY METHODS
    // =========================================================================

    /**
     * Get total quantity of a variant currently in a cart.
     *
     * @param   int  $variantId  The variant ID.
     * @param   int  $cartId     Optional specific cart ID (0 = all carts).
     *
     * @return  int  Total quantity in cart(s).
     *
     * @since   6.0.0
     */
    public static function getCartQuantity(int $variantId, int $cartId = 0): int
    {
        if ($variantId < 1) {
            return 0;
        }

        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select('SUM(' . $db->quoteName('product_qty') . ') AS total_qty')
            ->from($db->quoteName('#__j2commerce_cartitems'))
            ->where($db->quoteName('variant_id') . ' = :variantId')
            ->bind(':variantId', $variantId, ParameterType::INTEGER);

        if ($cartId > 0) {
            $query->where($db->quoteName('cart_id') . ' = :cartId')
                ->bind(':cartId', $cartId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return (int) ($db->loadResult() ?? 0);
    }

    /**
     * Check if adding quantity would exceed stock.
     *
     * Considers current cart quantity + new quantity against available stock.
     *
     * @param   object  $variant   The variant object.
     * @param   int     $addQty    Quantity to add.
     * @param   int     $cartId    Current cart ID (for existing quantity check).
     *
     * @return  bool  True if addition is allowed.
     *
     * @since   6.0.0
     */
    public static function canAddToCart(object $variant, int $addQty, int $cartId = 0): bool
    {
        // If not managing stock or backorders allowed, can always add
        if (!self::isManagingStock($variant) || self::isBackorderAllowed($variant)) {
            return true;
        }

        $variantId = (int) ($variant->j2commerce_variant_id ?? 0);
        $currentCartQty = self::getCartQuantity($variantId, $cartId);
        $totalQty = $currentCartQty + $addQty;
        $availableQty = (int) ($variant->quantity ?? 0);

        return $totalQty <= $availableQty;
    }

    // =========================================================================
    // STOCK DISPLAY METHODS
    // =========================================================================

    /**
     * Get stock status display text for a variant.
     *
     * Display options:
     * - always_show: Always show quantity or "in stock"
     * - low_stock: Only show when at/below notification threshold
     * - no_display: Never show stock info
     *
     * @param   object    $variant  The variant object.
     * @param   Registry  $params   Display parameters with stock_display_format.
     *
     * @return  string  Stock status text (may be empty based on settings).
     *
     * @since   6.0.0
     */
    public static function getStockDisplayText(object $variant, Registry $params): string
    {
        $displayFormat = $params->get('stock_display_format', 'always_show');
        $quantity = (int) ($variant->quantity ?? 0);
        $notifyQty = (int) ($variant->notify_qty ?? 0);

        switch ($displayFormat) {
            case 'always_show':
            default:
                if ($quantity > 0) {
                    $text = Text::sprintf('COM_J2COMMERCE_IN_STOCK_WITH_QUANTITY', $quantity);
                } else {
                    $text = Text::_('COM_J2COMMERCE_IN_STOCK');
                }

                // Backorder notification overrides normal display
                if (self::isBackorderAllowed($variant)
                    && self::requiresBackorderNotification($variant)
                    && $quantity < 1
                ) {
                    $text = Text::_('COM_J2COMMERCE_BACKORDER_NOTIFICATION');
                }

                return $text;

            case 'low_stock':
                // Only show if low stock
                if ($quantity > 0 && $quantity <= $notifyQty) {
                    $text = Text::sprintf('COM_J2COMMERCE_LOW_STOCK_WITH_QUANTITY', $quantity);
                } else {
                    $text = '';
                }

                // Backorder notification
                if (self::isBackorderAllowed($variant)
                    && self::requiresBackorderNotification($variant)
                    && $quantity < 1
                ) {
                    $text = Text::_('COM_J2COMMERCE_BACKORDER_NOTIFICATION');
                }

                return $text;

            case 'no_display':
                return '';
        }
    }

    /**
     * Get stock status CSS class for styling.
     *
     * @param   object  $variant  The variant object.
     *
     * @return  string  CSS class name.
     *
     * @since   6.0.0
     */
    public static function getStockStatusClass(object $variant): string
    {
        if (self::isOutOfStock($variant)) {
            if (self::isBackorderAllowed($variant)) {
                return 'j2commerce-stock-backorder';
            }

            return 'j2commerce-stock-out';
        }

        if (self::isLowStock($variant)) {
            return 'j2commerce-stock-low';
        }

        return 'j2commerce-stock-in';
    }

    /**
     * Get stock status badge HTML.
     *
     * @param   object    $variant  The variant object.
     * @param   Registry  $params   Display parameters.
     *
     * @return  string  HTML for stock status badge.
     *
     * @since   6.0.0
     */
    public static function getStockBadge(object $variant, Registry $params): string
    {
        $text = self::getStockDisplayText($variant, $params);

        if (empty($text)) {
            return '';
        }

        $class = self::getStockStatusClass($variant);

        return sprintf(
            '<span class="badge %s">%s</span>',
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
        );
    }

    // =========================================================================
    // INVENTORY AGGREGATION METHODS
    // =========================================================================

    /**
     * Get total stock across all variants for a product.
     *
     * @param   int  $productId  The product ID.
     *
     * @return  int  Total stock quantity.
     *
     * @since   6.0.0
     */
    public static function getProductTotalStock(int $productId): int
    {
        if ($productId < 1) {
            return 0;
        }

        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select('SUM(' . $db->quoteName('pq.quantity') . ') AS total_stock')
            ->from($db->quoteName('#__j2commerce_productquantities', 'pq'))
            ->join(
                'INNER',
                $db->quoteName('#__j2commerce_variants', 'v') . ' ON ' .
                $db->quoteName('v.j2commerce_variant_id') . ' = ' . $db->quoteName('pq.variant_id')
            )
            ->where($db->quoteName('v.product_id') . ' = :productId')
            ->bind(':productId', $productId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (int) ($db->loadResult() ?? 0);
    }

    /**
     * Get stock status summary for all variants of a product.
     *
     * @param   int  $productId  The product ID.
     *
     * @return  array  Array with keys: total_stock, variants_in_stock, variants_out_of_stock.
     *
     * @since   6.0.0
     */
    public static function getProductStockSummary(int $productId): array
    {
        if ($productId < 1) {
            return [
                'total_stock' => 0,
                'variants_in_stock' => 0,
                'variants_out_of_stock' => 0,
            ];
        }

        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'SUM(' . $db->quoteName('pq.quantity') . ') AS total_stock',
                'SUM(CASE WHEN ' . $db->quoteName('pq.quantity') . ' > 0 THEN 1 ELSE 0 END) AS in_stock',
                'SUM(CASE WHEN ' . $db->quoteName('pq.quantity') . ' <= 0 THEN 1 ELSE 0 END) AS out_of_stock',
            ])
            ->from($db->quoteName('#__j2commerce_productquantities', 'pq'))
            ->join(
                'INNER',
                $db->quoteName('#__j2commerce_variants', 'v') . ' ON ' .
                $db->quoteName('v.j2commerce_variant_id') . ' = ' . $db->quoteName('pq.variant_id')
            )
            ->where($db->quoteName('v.product_id') . ' = :productId')
            ->bind(':productId', $productId, ParameterType::INTEGER);

        $db->setQuery($query);
        $result = $db->loadObject();

        return [
            'total_stock' => (int) ($result->total_stock ?? 0),
            'variants_in_stock' => (int) ($result->in_stock ?? 0),
            'variants_out_of_stock' => (int) ($result->out_of_stock ?? 0),
        ];
    }

    /**
     * Check if all variants of a product are sold out.
     *
     * @param   int  $productId  The product ID.
     *
     * @return  bool  True if all variants are out of stock.
     *
     * @since   6.0.0
     */
    public static function isProductSoldOut(int $productId): bool
    {
        $summary = self::getProductStockSummary($productId);

        return $summary['variants_in_stock'] === 0;
    }

    // =========================================================================
    // LOW STOCK REPORTING METHODS
    // =========================================================================

    /**
     * Get list of variants with low stock.
     *
     * @param   int|null  $limit  Maximum results to return (null = all).
     *
     * @return  array  Array of variant objects with low stock.
     *
     * @since   6.0.0
     */
    public static function getLowStockVariants(?int $limit = null): array
    {
        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName([
                'v.j2commerce_variant_id',
                'v.product_id',
                'v.sku',
                'v.notify_qty',
                'pq.quantity',
            ]))
            ->from($db->quoteName('#__j2commerce_variants', 'v'))
            ->join(
                'LEFT',
                $db->quoteName('#__j2commerce_productquantities', 'pq') . ' ON ' .
                $db->quoteName('pq.variant_id') . ' = ' . $db->quoteName('v.j2commerce_variant_id')
            )
            ->where($db->quoteName('v.manage_stock') . ' = 1')
            ->where($db->quoteName('pq.quantity') . ' > 0')
            ->where($db->quoteName('pq.quantity') . ' <= ' . $db->quoteName('v.notify_qty'))
            ->where($db->quoteName('v.notify_qty') . ' > 0')
            ->order($db->quoteName('pq.quantity') . ' ASC');

        if ($limit !== null && $limit > 0) {
            $query->setLimit($limit);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Get list of variants that are out of stock.
     *
     * @param   int|null  $limit  Maximum results to return (null = all).
     *
     * @return  array  Array of variant objects that are out of stock.
     *
     * @since   6.0.0
     */
    public static function getOutOfStockVariants(?int $limit = null): array
    {
        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName([
                'v.j2commerce_variant_id',
                'v.product_id',
                'v.sku',
                'v.allow_backorder',
                'pq.quantity',
            ]))
            ->from($db->quoteName('#__j2commerce_variants', 'v'))
            ->join(
                'LEFT',
                $db->quoteName('#__j2commerce_productquantities', 'pq') . ' ON ' .
                $db->quoteName('pq.variant_id') . ' = ' . $db->quoteName('v.j2commerce_variant_id')
            )
            ->where($db->quoteName('v.manage_stock') . ' = 1')
            ->where('(' . $db->quoteName('pq.quantity') . ' <= 0 OR ' . $db->quoteName('pq.quantity') . ' IS NULL)')
            ->order($db->quoteName('v.j2commerce_variant_id') . ' ASC');

        if ($limit !== null && $limit > 0) {
            $query->setLimit($limit);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Get count of variants with low stock.
     *
     * @return  int  Count of low stock variants.
     *
     * @since   6.0.0
     */
    public static function getLowStockCount(): int
    {
        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__j2commerce_variants', 'v'))
            ->join(
                'LEFT',
                $db->quoteName('#__j2commerce_productquantities', 'pq') . ' ON ' .
                $db->quoteName('pq.variant_id') . ' = ' . $db->quoteName('v.j2commerce_variant_id')
            )
            ->where($db->quoteName('v.manage_stock') . ' = 1')
            ->where($db->quoteName('pq.quantity') . ' > 0')
            ->where($db->quoteName('pq.quantity') . ' <= ' . $db->quoteName('v.notify_qty'))
            ->where($db->quoteName('v.notify_qty') . ' > 0');

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Get count of out of stock variants.
     *
     * @return  int  Count of out of stock variants.
     *
     * @since   6.0.0
     */
    public static function getOutOfStockCount(): int
    {
        $db = self::getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__j2commerce_variants', 'v'))
            ->join(
                'LEFT',
                $db->quoteName('#__j2commerce_productquantities', 'pq') . ' ON ' .
                $db->quoteName('pq.variant_id') . ' = ' . $db->quoteName('v.j2commerce_variant_id')
            )
            ->where($db->quoteName('v.manage_stock') . ' = 1')
            ->where('(' . $db->quoteName('pq.quantity') . ' <= 0 OR ' . $db->quoteName('pq.quantity') . ' IS NULL)');

        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
