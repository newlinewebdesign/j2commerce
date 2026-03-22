<?php
defined('_JEXEC') or die;

extract($displayData);

$settings  = $settings ?? [];
$tag       = $settings['tag'] ?? 'h3';
$cssClass  = $settings['css_class'] ?? 'j2commerce-product-title fs-6';
$linkEnabled = $settings['link'] ?? true;

if (!($showTitle ?? true)) {
    return;
}

$productName = htmlspecialchars($product->product_name ?? '', ENT_QUOTES, 'UTF-8');
$linkHref    = htmlspecialchars($productLink ?? '#', ENT_QUOTES, 'UTF-8');
?>
<<?php echo $tag; ?> class="<?php echo htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($linkEnabled && !empty($productLink)): ?>
        <a href="<?php echo $linkHref; ?>" title="<?php echo $productName; ?>" class="text-decoration-none">
    <?php endif; ?>

    <?php echo $productName; ?>

    <?php if ($linkEnabled && !empty($productLink)): ?>
        </a>
    <?php endif; ?>
</<?php echo $tag; ?>>
