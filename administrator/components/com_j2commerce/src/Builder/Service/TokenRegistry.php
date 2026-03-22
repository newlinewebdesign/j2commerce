<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\Builder\Service;

\defined('_JEXEC') or die;

final class TokenRegistry
{
    private static array $tokens = [
        'PRODUCT_NAME' => [
            'label' => 'Product Name',
            'php' => '<?php echo htmlspecialchars($product->product_name ?? \'\', ENT_QUOTES, \'UTF-8\'); ?>',
            'preview_field' => 'product_name',
        ],
        'PRODUCT_SKU' => [
            'label' => 'SKU',
            'php' => '<?php echo htmlspecialchars($product->product_sku ?? \'\', ENT_QUOTES, \'UTF-8\'); ?>',
            'preview_field' => 'product_sku',
        ],
        'PRODUCT_LINK' => [
            'label' => 'Product URL',
            'php' => '<?php echo htmlspecialchars($productLink, ENT_QUOTES, \'UTF-8\'); ?>',
            'preview_field' => 'productLink',
        ],
        'PRODUCT_PRICE' => [
            'label' => 'Price (formatted)',
            'php' => '<?php echo J2Commerce\\Component\\J2commerce\\Site\\Helper\\CurrencyHelper::format($variant->price ?? 0); ?>',
            'preview_field' => 'price',
        ],
        'PRODUCT_SPECIAL_PRICE' => [
            'label' => 'Special Price (formatted)',
            'php' => '<?php echo J2Commerce\\Component\\J2commerce\\Site\\Helper\\CurrencyHelper::format($variant->special_price ?? 0); ?>',
            'preview_field' => 'special_price',
        ],
        'PRODUCT_IMAGE_URL' => [
            'label' => 'Main Image URL',
            'php' => '<?php echo htmlspecialchars(J2Commerce\\Component\\J2commerce\\Site\\Helper\\ImageHelper::getImageUrl($product->main_image ?? \'\'), ENT_QUOTES, \'UTF-8\'); ?>',
            'preview_field' => 'main_image',
        ],
        'PRODUCT_IMAGE_ALT' => [
            'label' => 'Main Image Alt Text',
            'php' => '<?php echo htmlspecialchars($product->product_name ?? \'\', ENT_QUOTES, \'UTF-8\'); ?>',
            'preview_field' => 'product_name',
        ],
        'STOCK_STATUS' => [
            'label' => 'Stock Status Text',
            'php' => '<?php echo htmlspecialchars($stockStatus ?? \'\', ENT_QUOTES, \'UTF-8\'); ?>',
            'preview_field' => 'stockStatus',
        ],
        'STOCK_QUANTITY' => [
            'label' => 'Stock Quantity',
            'php' => '<?php echo (int) ($variant->quantity ?? 0); ?>',
            'preview_field' => 'quantity',
        ],
    ];

    public function getTokenList(): array
    {
        $list = [];
        foreach (self::$tokens as $name => $config) {
            $list[$name] = $config['label'];
        }
        return $list;
    }

    public function getTokenDefinitions(): array
    {
        return self::$tokens;
    }

    public function getPhpForToken(string $tokenName): ?string
    {
        return self::$tokens[$tokenName]['php'] ?? null;
    }

    public function replaceTokensWithPhp(string $html): string
    {
        // Replace <j2c-token data-j2c-token="TOKEN_NAME">preview text</j2c-token>
        return preg_replace_callback(
            '/<j2c-token\s+data-j2c-token="([A-Z_]+)"[^>]*>.*?<\/j2c-token>/s',
            function (array $matches): string {
                $tokenName = $matches[1];
                $php = $this->getPhpForToken($tokenName);
                return $php ?? $matches[0]; // Keep original if unknown token
            },
            $html
        ) ?? $html;
    }

    public function getPreviewValue(string $tokenName, object $product, array $displayData = []): string
    {
        $field = self::$tokens[$tokenName]['preview_field'] ?? null;
        if (!$field) {
            return '[' . $tokenName . ']';
        }

        // Check displayData first, then product properties
        if (isset($displayData[$field])) {
            return (string) $displayData[$field];
        }

        if (isset($product->$field)) {
            return (string) $product->$field;
        }

        return '[' . $tokenName . ']';
    }
}
