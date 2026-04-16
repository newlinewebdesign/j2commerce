<?php
/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.ReportProducts
 *
 * @copyright   Copyright (C) 2024-2026 J2Commerce, LLC. All rights reserved.
 * @license     GNU General Public License version 2 or later;  see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/** @var \stdClass $displayData */
$vars = $displayData;

$currency = $vars->currency;
$items = $vars->items;
$orderStatuses = $vars->orderStatuses;
$dateTypes = $vars->dateTypes;
$chartAmounts = $vars->chartAmounts;
$chartNames = $vars->chartNames;



?>

<div class="j2commerce-report-products">
    <form class="form-horizontal" method="post" action="<?php echo $vars->formAction; ?>" name="adminForm" id="adminForm">
        <div class="row mb-4">
            <!-- Filters Column -->
            <div class="col-lg-6">
                <!-- SKU Search -->
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_FILTER_SEARCH'); ?></label>
                        <div class="input-group">
                            <input type="text" name="filter_search" id="filter_search" class="form-control"
                                   value="<?php echo htmlspecialchars($vars->filterSearch, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="<?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_FILTER_SEARCH'); ?>">
                            <button type="button" class="btn btn-outline-secondary" id="btnResetSearch"
                                    title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>">
                                <span class="icon-times" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Status -->
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_FILTER_ORDERSTATUS'); ?></label>
                        <select name="filter_orderstatus[]" id="filter_orderstatus" class="form-select" multiple size="4">
                            <?php foreach ($orderStatuses as $statusId => $statusName) : ?>
                                <option value="<?php echo (int) $statusId; ?>"
                                    <?php echo \in_array($statusId, $vars->filterOrderStatus) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($statusName, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Duration -->
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_FILTER_DURATION'); ?></label>
                        <select name="filter_datetype" id="filter_datetype" class="form-select" onchange="this.form.submit();">
                            <?php foreach ($dateTypes as $value => $label) : ?>
                                <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php echo $vars->filterDateType === $value ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Range -->
                <?php if ($vars->filterDateType === 'custom') : ?>
                <div class="row mb-3">
                    <div class="col-sm-5">
                        <label class="form-label fw-bold"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_FROM_DATE'); ?></label>
                        <?php echo HTMLHelper::_('calendar', $vars->filterFromDate, 'filter_order_from_date', 'filter_order_from_date', '%Y-%m-%d', ['class' => 'form-control form-control-sm']); ?>
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label fw-bold"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_TO_DATE'); ?></label>
                        <?php echo HTMLHelper::_('calendar', $vars->filterToDate, 'filter_order_to_date', 'filter_order_to_date', '%Y-%m-%d', ['class' => 'form-control form-control-sm']); ?>
                    </div>
                    <div class="col-sm-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetDates"
                                title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>">
                            <span class="icon-times" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Buttons -->
                <div class="row mb-3">
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-warning" id="btnExportCsv">
                            <span class="icon-download" aria-hidden="true"></span>
                            <?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_EXPORT'); ?>
                        </button>
                        <button type="submit" class="btn btn-success">
                            <span class="icon-search" aria-hidden="true"></span>
                            <?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_FILTER'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chart Column -->
            <div class="col-lg-6">
                <div id="j2commerce-report-chart" class="mt-3"></div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_PRODUCT_NAME'); ?></th>
                        <th class="text-end"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_TOTAL_QUANTITY'); ?></th>
                        <th class="text-end"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_DISCOUNT'); ?></th>
                        <th class="text-end"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_TAX'); ?></th>
                        <th class="text-end"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_WITHOUT_TAX'); ?></th>
                        <th class="text-end"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_WITH_TAX'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)) : ?>
                        <?php
                        $qtyTotal = 0;
                        $discountTotal = 0.0;
                        $totalWithoutTax = 0.0;
                        $totalWithTax = 0.0;
                        $totalTax = 0.0;
                        ?>
                        <?php foreach ($items as $product) : ?>
                            <?php
                            $qtyTotal += (int) $product->total_qty;
                            $discountTotal += (float) $product->total_item_discount + (float) $product->total_item_discount_tax;
                            $totalWithoutTax += (float) $product->total_final_price_without_tax;
                            $totalWithTax += (float) $product->total_final_price_with_tax;
                            $totalTax += (float) $product->total_item_tax;
                            ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($product->orderitem_name, ENT_QUOTES, 'UTF-8'); ?>
                                    <br>
                                    <small class="text-muted"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_SKU'); ?>: <?php echo htmlspecialchars($product->orderitem_sku, ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td class="text-end"><?php echo (int) $product->total_qty; ?></td>
                                <td class="text-end"><?php echo $currency->format((float) $product->total_item_discount + (float) $product->total_item_discount_tax); ?></td>
                                <td class="text-end"><?php echo $currency->format((float) $product->total_item_tax); ?></td>
                                <td class="text-end"><?php echo $currency->format((float) $product->total_final_price_without_tax); ?></td>
                                <td class="text-end"><?php echo $currency->format((float) $product->total_final_price_with_tax); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-dark fw-bold">
                            <td><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_TOTAL'); ?></td>
                            <td class="text-end"><?php echo $qtyTotal; ?></td>
                            <td class="text-end"><?php echo $currency->format($discountTotal); ?></td>
                            <td class="text-end"><?php echo $currency->format($totalTax); ?></td>
                            <td class="text-end"><?php echo $currency->format($totalWithoutTax); ?></td>
                            <td class="text-end"><?php echo $currency->format($totalWithTax); ?></td>
                        </tr>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center"><?php echo Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_NO_ITEMS'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" name="filter_order" value="">
        <input type="hidden" name="filter_order_Dir" value="">
        <input type="hidden" name="task" value="view">
        <input type="hidden" name="report_id" value="<?php echo (int) $vars->reportId; ?>">
        <div class="csvdiv"></div>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>

<?php if (!empty($chartAmounts)) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof MaterialCharts !== 'undefined') {
        MaterialCharts.bar('#j2commerce-report-chart', {
            datasets: {
                values: <?php echo json_encode($chartAmounts); ?>,
                labels: <?php echo json_encode($chartNames); ?>,
                color: 'blue'
            },
            title: <?php echo json_encode(Text::_('PLG_J2COMMERCE_REPORT_PRODUCTS_CHART_TITLE')); ?>,
            height: '300px',
            width: '500px',
            background: '#FFFFFF',
            shadowDepth: '1'
        });
    }

    // Reset search button
    const btnResetSearch = document.getElementById('btnResetSearch');
    if (btnResetSearch) {
        btnResetSearch.addEventListener('click', function() {
            document.getElementById('filter_search').value = '';
            document.querySelector('.csvdiv').innerHTML = '';
            document.adminForm.submit();
        });
    }

    // Reset dates button
    const btnResetDates = document.getElementById('btnResetDates');
    if (btnResetDates) {
        btnResetDates.addEventListener('click', function() {
            const fromDate = document.getElementById('filter_order_from_date');
            const toDate = document.getElementById('filter_order_to_date');
            if (fromDate) fromDate.value = '';
            if (toDate) toDate.value = '';
            document.adminForm.submit();
        });
    }

    // CSV export button
    const btnExport = document.getElementById('btnExportCsv');
    if (btnExport) {
        btnExport.addEventListener('click', function() {
            document.querySelector('.csvdiv').innerHTML = '<input type="hidden" name="format" value="csv">';
            document.adminForm.submit();
            // Clean up after submit so next regular submit doesn't export
            setTimeout(function() {
                document.querySelector('.csvdiv').innerHTML = '';
            }, 100);
        });
    }
});
</script>
<?php endif; ?>
