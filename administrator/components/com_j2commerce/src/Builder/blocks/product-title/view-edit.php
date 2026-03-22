<?php
defined('_JEXEC') or die;

extract($displayData);

$settings    = $settings ?? [];
$tag         = $settings['tag'] ?? 'h3';
$cssClass    = $settings['css_class'] ?? 'j2commerce-product-title fs-6';
$linkEnabled = $settings['link'] ?? true;
?>
<<?php echo $tag; ?> class="<?php echo htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'); ?>" data-j2c-block="product-title">
    <?php if ($linkEnabled): ?>
        <a href="#" class="text-decoration-none">
    <?php endif; ?>
    <j2c-token data-j2c-token="PRODUCT_NAME"><?php echo htmlspecialchars($product->product_name ?? 'Product Name', ENT_QUOTES, 'UTF-8'); ?></j2c-token>
    <?php if ($linkEnabled): ?>
        </a>
    <?php endif; ?>
</<?php echo $tag; ?>>
