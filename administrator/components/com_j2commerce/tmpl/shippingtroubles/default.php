<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Shippingtroubles\HtmlView $this */

J2CommerceHelper::strapper()->addCSS();

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.modal')->useScript('joomla.dialog-autocreate');

$summaryStats = $this->getSummaryStats();
?>
<?php echo $this->navbar;?>

<div class="row">
    <div class="col-md-12">
        <div id="j-main-container" class="j-main-container">
            <div class="card mb-4">
                <div class="card-body">
                    <h2>
                        <span class="fa-solid fa-truck-medical me-2 text-primary" aria-hidden="true"></span>
                        <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER'); ?>
                    </h2>
                    <p class="mb-0">
                        <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_WELCOME_DESCRIPTION'); ?>
                    </p>
                </div>
            </div>
            <div class="cpanel-modules cpanel mt-3">
                <?php if (!empty($summaryStats)):
                    $enabled_shipping_methods_class = (int) $summaryStats['enabled_shipping_methods'] == 0 ? 'danger' : 'success';
                    $enabled_geozones_class = (int) $summaryStats['enabled_geozones'] == 0 ? 'danger' : 'success';
                    $products_with_shipping_class = (int) $summaryStats['products_with_shipping'] == 0 ? 'danger' : 'success';
                    ?>
                    <div class="card mb-5 mt-3">
                        <div class="card-body">
                            <nav class="quick-icons" aria-label="<?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_WELCOME_TITLE'); ?>">
                                <div class="row flex-wrap">
                                    <div class="quickicon quickicon-single col-12 col-md-6 col-lg-3 mb-3 mb-lg-0 border-0">
                                        <div class="alert alert-<?php echo $enabled_shipping_methods_class;?> my-0 w-100 border-0">
                                            <div class="quickicon-info">
                                                <div class="quickicon-value display-6 mb-3"><?php echo (int) $summaryStats['enabled_shipping_methods']; ?></div>
                                            </div>
                                            <div class="quickicon-name d-flex align-items-center">
                                                <span class="j-links-link">
                                                    <div class="text-capitalize"><?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_ENABLED_METHODS'); ?></div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="quickicon quickicon-single col-12 col-md-6 col-lg-3 mb-3 mb-lg-0 border-0">
                                        <div class="alert alert-<?php echo $enabled_geozones_class;?> my-0 w-100 border-0">
                                            <div class="quickicon-info">
                                                <div class="quickicon-value display-6 mb-3"><?php echo (int) $summaryStats['enabled_geozones']; ?></div>
                                            </div>
                                            <div class="quickicon-name d-flex align-items-center">
                                                <span class="j-links-link">
                                                    <div class="text-capitalize"><?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_ENABLED_GEOZONES'); ?></div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="quickicon quickicon-single col-12 col-md-6 col-lg-3 mb-3 mb-lg-0 border-0">
                                        <div class="alert alert-<?php echo $products_with_shipping_class;?> my-0 w-100 border-0">
                                            <div class="quickicon-info">
                                                <div class="quickicon-value display-6 mb-3"><?php echo (int) $summaryStats['products_with_shipping']; ?></div>
                                            </div>
                                            <div class="quickicon-name d-flex align-items-center">
                                                <span class="j-links-link">
                                                    <div class="text-capitalize"><?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_PRODUCTS_WITH_SHIPPING'); ?></div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="quickicon quickicon-single col-12 col-md-6 col-lg-3 mb-3 mb-lg-0 border-0">
                                        <div class="alert alert-info my-0 w-100 border-0">
                                            <div class="quickicon-info">
                                                <div class="quickicon-value display-6 mb-3"><?php echo (int) $summaryStats['total_products']; ?></div>
                                            </div>
                                            <div class="quickicon-name d-flex align-items-center">
                                                <span class="j-links-link">
                                                    <div class="text-capitalize"><?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_TOTAL_PRODUCTS'); ?></div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                <?php endif;?>

                <div class="card-columns">
                    <div class="col-md-12 quickicons-for-troubleshooting_quickicon module-wrapper">
                        <div class="card mb-3">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item fs-6">
                                    <span class="icon-check text-success me-2" aria-hidden="true"></span>
                                    <strong class="fs-5"><?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_STEP1_TITLE'); ?></strong>
                                    <p class="card-text fs-6">
                                        <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_STEP1_DESCRIPTION'); ?>
                                    </p>
                                    <div>
                                        <a href="<?php echo Route::_('index.php?option=com_j2commerce&view=shippingtroubles&step=shipping'); ?>"
                                           class="btn btn-primary align-self-start btn-sm">
                                            <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_CHECK_METHODS'); ?>
                                            <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-12 quickicons-for-troubleshooting_quickicon module-wrapper">
                        <div class="card mb-3">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item fs-6">
                                    <span class="icon-check text-success me-2" aria-hidden="true"></span>
                                    <strong class="fs-5"><?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_STEP2_TITLE'); ?></strong>
                                    <p class="card-text fs-6">
                                        <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_STEP2_DESCRIPTION'); ?>
                                    </p>
                                    <div>
                                        <a href="<?php echo Route::_('index.php?option=com_j2commerce&view=shippingtroubles&step=products'); ?>"
                                           class="btn btn-success align-self-start btn-sm">
                                            <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_CHECK_PRODUCTS'); ?>
                                            <span class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-12 quickicons-for-quick-actions_quickicon module-wrapper">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h2>
                                    <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_QUICK_ACTIONS'); ?>
                                </h2>
                            </div>
                            <div class="card-body">
                                <nav class="quick-icons" aria-label="<?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_QUICK_ACTIONS'); ?>">
                                    <ul class="nav flex-wrap">
                                        <li class="quickicon quickicon-single">
                                            <a href="<?php echo Route::_('index.php?option=com_j2commerce&view=shippingmethods'); ?>">
                                                <div class="quickicon-info">
                                                    <div class="quickicon-icon">
                                                        <div class="fa-solid fa-shipping-fast" aria-hidden="true"></div>
                                                    </div>
                                                </div>
                                                <div class="quickicon-name d-flex align-items-end"><?php echo Text::_('COM_J2COMMERCE_MANAGE_SHIPPING_METHODS'); ?></div>
                                            </a>
                                        </li>
                                        <li class="quickicon quickicon-single">
                                            <a href="<?php echo Route::_('index.php?option=com_j2commerce&view=geozones'); ?>">
                                                <div class="quickicon-info">
                                                    <div class="quickicon-icon">
                                                        <div class="fa-solid fa-pie-chart fa-chart-pie" aria-hidden="true"></div>
                                                    </div>
                                                </div>
                                                <div class="quickicon-name d-flex align-items-end"><?php echo Text::_('COM_J2COMMERCE_MANAGE_GEOZONES'); ?></div>
                                            </a>
                                        </li>
                                        <li class="quickicon quickicon-single">
                                            <a href="<?php echo Route::_('index.php?option=com_j2commerce&view=products'); ?>">
                                                <div class="quickicon-info">
                                                    <div class="quickicon-icon">
                                                        <div class="fa-solid fa-cubes" aria-hidden="true"></div>
                                                    </div>
                                                </div>
                                                <div class="quickicon-name d-flex align-items-end"><?php echo Text::_('COM_J2COMMERCE_MANAGE_PRODUCTS'); ?></div>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 quickicons-for-quick-commonfaq_quickicon module-wrapper">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h2>
                                    <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_COMMON_ISSUES'); ?>
                                </h2>
                            </div>
                            <div class="list-group list-group-flush">
                                <div class="accordion" id="commonIssuesAccordion">
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button collapsed shadow-none fs-6 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                                <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_ISSUE1_TITLE'); ?>
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#commonIssuesAccordion">
                                            <div class="accordion-body fs-6">
                                                <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_ISSUE1_SOLUTION'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header" id="headingTwo">
                                            <button class="accordion-button collapsed shadow-none fs-6 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                                <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_ISSUE2_TITLE'); ?>
                                            </button>
                                        </h2>
                                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#commonIssuesAccordion">
                                            <div class="accordion-body fs-6">
                                                <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_ISSUE2_SOLUTION'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header" id="headingThree">
                                            <button class="accordion-button collapsed shadow-none fs-6 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                                <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_ISSUE3_TITLE'); ?>
                                            </button>
                                        </h2>
                                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#commonIssuesAccordion">
                                            <div class="accordion-body fs-6">
                                                <?php echo Text::_('COM_J2COMMERCE_SHIPPING_TROUBLESHOOTER_ISSUE3_SOLUTION'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->footer ?? ''; ?>
