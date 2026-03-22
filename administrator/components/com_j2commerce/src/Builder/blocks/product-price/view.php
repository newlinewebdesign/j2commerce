<?php
defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;

extract($displayData);

$settings      = $settings ?? [];
$showSpecial   = $settings['show_special'] ?? true;
$showTaxInfo   = $settings['show_tax_info'] ?? false;
$cssClass      = $settings['css_class'] ?? 'j2commerce-product-price';
$productHelper = J2CommerceHelper::product();

if (!($showPrice ?? true) || !$productHelper->canShowprice($params ?? null)) {
    return;
}

$pricing = $product->pricing ?? null;
if (!$pricing) {
    return;
}

$showBasePrice    = $showSpecial;
$showSpecialPrice = $showSpecial;

if (!$showBasePrice && !$showSpecialPrice) {
    return;
}

$beforeHtml = J2CommerceHelper::plugin()->eventWithHtml('BeforeRenderingProductPrice', [$product])->getArgument('html', '');
$afterHtml  = J2CommerceHelper::plugin()->eventWithHtml('AfterRenderingProductPrice', [$product])->getArgument('html', '');
$basePrice  = $pricing->base_price ?? 0;
$salePrice  = $pricing->price ?? 0;
?>
<?php echo $beforeHtml; ?>

<div class="<?php echo htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'); ?> d-flex align-items-center gap-1">
    <?php if ($showSpecialPrice && isset($pricing->price)): ?>
        <div class="sale-price lh-1 fs-5 fw-semibold">
            <?php echo $productHelper->displayPrice((float) $salePrice, $product, $params); ?>
        </div>
    <?php endif; ?>

    <?php if ($showBasePrice && $basePrice > 0 && $basePrice != $salePrice): ?>
        <del class="base-price fs-6 fw-normal text-body-tertiary lh-1">
            <?php echo $productHelper->displayPrice((float) $basePrice, $product, $params); ?>
        </del>
    <?php endif; ?>

    <?php if ($showTaxInfo): ?>
        <div class="tax-text">
            <small class="fw-normal text-body-tertiary"><?php echo $productHelper->get_tax_text(); ?></small>
        </div>
    <?php endif; ?>
</div>

<?php echo $afterHtml; ?>
