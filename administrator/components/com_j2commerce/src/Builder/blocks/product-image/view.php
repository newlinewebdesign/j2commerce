<?php
defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Administrator\Helper\ImageHelper;
use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

$settings    = $settings ?? [];
$linkEnabled = $settings['link'] ?? true;
$cssClass    = $settings['css_class'] ?? 'j2commerce-product-image position-relative border mb-3';

if (!($showImage ?? true)) {
    return;
}

$platform   = J2CommerceHelper::platform();
$imageWidth = (int) $params->get('list_image_thumbnail_width', 350);
$image      = $platform->getImagePath($product->thumb_image ?? '');
$imageAlt   = htmlspecialchars($product->thumb_image_alt ?? $product->product_name ?? '', ENT_QUOTES, 'UTF-8');
$image      = HTMLHelper::_('cleanImageURL', $image)->url;

if (empty($image)) {
    return;
}
?>
<div class="<?php echo htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($linkEnabled && !empty($productLink)): ?>
        <a href="<?php echo htmlspecialchars($productLink, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <?php echo ImageHelper::getProductImage($image, $imageWidth, 'html', $imageWidth, 'j2commerce-img-responsive img-fluid', $imageAlt); ?>

    <?php if ($linkEnabled && !empty($productLink)): ?>
        </a>
    <?php endif; ?>
</div>
