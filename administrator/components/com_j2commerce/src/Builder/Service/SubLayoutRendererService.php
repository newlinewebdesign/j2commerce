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

final class SubLayoutRendererService
{
    private TokenRegistry $tokenRegistry;

    // No block groups — all blocks are independent or handled as combined slugs (e.g. cart-form)

    public function __construct()
    {
        $this->tokenRegistry = new TokenRegistry();
    }

    public function regenerateFromBlockOrder(array $blockOrder): array
    {
        if (empty($blockOrder)) {
            return ['success' => false, 'error' => 'No blocks specified', 'php' => ''];
        }

        $php = $this->buildCompositionPhp($blockOrder);

        $syntaxCheck = $this->validatePhpSyntax($php);

        if (!$syntaxCheck['valid']) {
            return ['success' => false, 'error' => 'PHP syntax error: ' . $syntaxCheck['error'], 'php' => $php];
        }

        return ['success' => true, 'error' => '', 'php' => $php];
    }

    public function regeneratePhp(string $modifiedHtml, array $settings = [], string $sourceFile = ''): array
    {
        $validation = $this->validateInput($modifiedHtml);

        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error'], 'php' => ''];
        }

        $php = $this->tokenRegistry->replaceTokensWithPhp($modifiedHtml);
        $php = $this->stripBuilderAttributes($php);
        $php = $this->wrapWithHeader($php, $sourceFile);

        $syntaxCheck = $this->validatePhpSyntax($php);

        if (!$syntaxCheck['valid']) {
            return ['success' => false, 'error' => 'PHP syntax error: ' . $syntaxCheck['error'], 'php' => $php];
        }

        return ['success' => true, 'error' => '', 'php' => $php];
    }

    private function buildCompositionPhp(array $blockOrder): string
    {
        $sections = [];

        foreach ($blockOrder as $slug) {
            $section = $this->getBlockSection($slug);

            if ($section !== null) {
                $sections[] = $section;
            }
        }

        return $this->getCompositionHeader()
            . $this->getContainerOpen()
            . implode("\n", $sections)
            . $this->getContainerClose();
    }

    private function getBlockSection(string $slug): ?string
    {
        return match ($slug) {
            'product-image' => <<<'PHP'
    <?php if ($showImage): ?>
        <?php echo ProductLayoutService::renderLayout('list.category.item_images', $displayData); ?>
    <?php endif; ?>
PHP,
            'product-title' => <<<'PHP'
    <?php if ($showTitle): ?>
        <?php echo ProductLayoutService::renderLayout('list.category.item_title', $displayData); ?>
    <?php endif; ?>

    <?php if (isset($product->event->afterDisplayTitle)): ?>
        <?php echo $product->event->afterDisplayTitle; ?>
    <?php endif; ?>

    <?php if (isset($product->event->beforeDisplayContent)): ?>
        <?php echo $product->event->beforeDisplayContent; ?>
    <?php endif; ?>
PHP,
            'product-description' => <<<'PHP'
    <?php if ($showDescription): ?>
        <?php echo ProductLayoutService::renderLayout('list.category.item_description', $displayData); ?>
    <?php endif; ?>
PHP,
            'product-price' => <<<'PHP'
    <?php if ($showPrice): ?>
        <?php echo ProductLayoutService::renderLayout('list.category.item_price', $displayData); ?>
    <?php endif; ?>
PHP,
            'product-sku' => <<<'PHP'
    <?php if ($showSku): ?>
        <?php echo ProductLayoutService::renderLayout('list.category.item_sku', $displayData); ?>
    <?php endif; ?>
PHP,
            'product-stock' => <<<'PHP'
    <?php if ($showStock): ?>
        <?php echo ProductLayoutService::renderLayout('list.category.item_stock', $displayData); ?>
    <?php endif; ?>
PHP,
            'cart-form' => <<<'PHP'
    <?php if ($showCart): ?>
        <form action="<?php echo htmlspecialchars($product->cart_form_action ?? '', ENT_QUOTES, 'UTF-8'); ?>"
              method="post"
              class="j2commerce-addtocart-form mt-auto"
              id="j2commerce-addtocart-form-<?php echo $productId; ?>"
              data-product_id="<?php echo $productId; ?>"
              data-product_type="<?php echo $product->product_type; ?>"
              enctype="multipart/form-data"
              >

            <?php if ($cartType == 1) : ?>
                <?php echo ProductLayoutService::renderLayout('list.category.item_options', $displayData); ?>
                <?php echo ProductLayoutService::renderLayout('list.category.item_cart', $displayData); ?>
            <?php elseif (($cartType == 2 && !empty($product->options)) || $cartType == 3) : ?>
                <a href="<?php echo $productLink; ?>" class="btn btn-outline-primary">
                    <?php echo Text::_('COM_J2COMMERCE_VIEW_PRODUCT_DETAILS'); ?>
                </a>
            <?php else : ?>
                <?php echo ProductLayoutService::renderLayout('list.category.item_cart', $displayData); ?>
            <?php endif; ?>
        </form>
    <?php endif; ?>
PHP,
            default => null,
        };
    }

    private function getCompositionHeader(): string
    {
        return <<<'PHP'
<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Generated by J2Commerce Visual Builder
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use J2Commerce\Component\J2commerce\Site\Service\ProductLayoutService;

extract($displayData);

$productId = $product->j2commerce_product_id;
$cssClass = $product->params->get('product_css_class', '') ?? '';
$beforeHtml = J2CommerceHelper::plugin()->eventWithHtml('BeforeProductListItemDisplay',[$product, $context, &$displayData])->getArgument('html', '');
$afterHtml = J2CommerceHelper::plugin()->eventWithHtml('AfterProductListItemDisplay',[$product, $context, &$displayData])->getArgument('html', '');
$cartType = (int) $params->get('list_show_cart', 1);
?>

PHP;
    }

    private function getContainerOpen(): string
    {
        return <<<'PHP'
<div class="j2commerce-product-item j2commerce-product-<?php echo $productId; ?> j2commerce-type-<?php echo $product->product_type; ?> <?php echo $cssClass; ?> d-flex flex-column" data-product-id="<?php echo $productId; ?>" data-product-type="<?php echo $product->product_type;?>" data-equal-height="itemContainer">

    <?php echo $beforeHtml; ?>

PHP;
    }

    private function getContainerClose(): string
    {
        return <<<'PHP'

    <?php if (isset($product->event->afterDisplayContent)): ?>
        <?php echo $product->event->afterDisplayContent; ?>
    <?php endif; ?>

    <?php echo $afterHtml; ?>
</div>

PHP;
    }

    private function validateInput(string $html): array
    {
        if (preg_match('/<\?(?!xml)/i', $html)) {
            return ['valid' => false, 'error' => 'Raw PHP tags are not allowed in builder output'];
        }

        if (preg_match('/<script\b/i', $html)) {
            return ['valid' => false, 'error' => 'Script tags are not allowed in builder output'];
        }

        if (preg_match('/\bon\w+\s*=/i', $html)) {
            return ['valid' => false, 'error' => 'Inline event handlers are not allowed in builder output'];
        }

        return ['valid' => true, 'error' => ''];
    }

    private function stripBuilderAttributes(string $html): string
    {
        return preg_replace('/\s*data-j2c-[a-z-]+="[^"]*"/', '', $html) ?? $html;
    }

    private function wrapWithHeader(string $body, string $sourceFile): string
    {
        $header  = "<?php\n";
        $header .= "/**\n";
        $header .= " * @package     J2Commerce\n";
        $header .= " * @subpackage  Layout Override\n";
        $header .= " *\n";
        $header .= " * @copyright   (C)2024-2026 J2Commerce, LLC\n";
        $header .= " * @license     GNU General Public License version 2 or later\n";
        $header .= " *\n";
        $header .= " * Generated by J2Commerce Visual Builder\n";

        if ($sourceFile) {
            $header .= ' * Source: ' . basename($sourceFile) . "\n";
        }

        $header .= " */\n\n";
        $header .= "\\defined('_JEXEC') or die;\n\n";
        $header .= "extract(\$displayData);\n";
        $header .= "?>\n";

        return $header . $body . "\n";
    }

    private function validatePhpSyntax(string $php): array
    {
        try {
            @token_get_all($php, TOKEN_PARSE);

            return ['valid' => true, 'error' => ''];
        } catch (\ParseError $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
}
