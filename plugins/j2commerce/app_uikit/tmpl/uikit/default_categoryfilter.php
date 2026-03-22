<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Site\Helper\RouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \J2Commerce\Component\J2commerce\Site\View\Products\HtmlView $this */

$app = Factory::getApplication();
$filterCatid = $this->filter_catid ?? '';



$currentSefPath = Uri::getInstance()->getPath();
?>

<div class="j2commerce-subcategory-nav uk-margin-top" id="j2commerce_category">
    <div class="uk-flex uk-flex-wrap" style="gap:.5rem;">
        <?php if ($this->params->get('list_show_filter_category_all', 0)) : ?>
            <a href="<?php echo Route::_(RouteHelper::getProductsRoute()); ?>" class="uk-button uk-button-small j2commerce-item-rootcategory <?php echo empty($filterCatid) ? 'uk-button-secondary' : 'uk-button-default'; ?>" data-key="filter_catid" data-value="">
                <?php echo Text::_('COM_J2COMMERCE_ALL'); ?>
            </a>
        <?php endif; ?>
        <?php foreach ($this->filters['filter_categories'] as $item) : ?>
            <?php
            $isCurrentCategory = (!empty($filterCatid) && $filterCatid == $item->id);

            if (!$isCurrentCategory) {
                $categoryUrl = Route::_(RouteHelper::getCategoryRoute((int) $item->id, $item->parent_id ?? null));
            }

            $categoryLabel = $this->escape($item->title);
            ?>
            <?php if ($isCurrentCategory) : ?>
                <span class="uk-button uk-button-primary uk-button-small j2commerce-item-category-current" data-key="filter_catid" data-value="<?php echo $item->id; ?>">
                    <?php echo $categoryLabel; ?>
                </span>
            <?php else : ?>
                <a href="<?php echo $categoryUrl; ?>" class="uk-button uk-button-default uk-button-small j2commerce-item-category" data-key="filter_catid" data-value="<?php echo $item->id; ?>">
                    <?php echo $categoryLabel; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>




