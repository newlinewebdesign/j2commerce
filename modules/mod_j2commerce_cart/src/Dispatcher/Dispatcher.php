<?php

/**
 * @package     J2Commerce
 * @subpackage  mod_j2commerce_cart
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Module\Cart\Site\Dispatcher;

\defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Administrator\Helper\CurrencyHelper;
use J2Commerce\Component\J2commerce\Administrator\Helper\OrderHelper;
use J2Commerce\Component\J2commerce\Site\Helper\RouteHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Find a published menu item by its link.
     *
     * @param  string  $link  The menu item link to search for (e.g. 'index.php?option=com_j2commerce&view=carts')
     *
     * @return int  The menu item ID, or 0 if not found
     */
    private function findMenuItemId(string $link): int
    {
        $menu  = Factory::getApplication()->getMenu();
        $items = $menu->getItems('link', $link);

        foreach ($items as $item) {
            if ((int) ($item->published ?? 0) === 1) {
                return (int) $item->id;
            }
        }

        return 0;
    }

    protected function getLayoutData(): array
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];
        $app    = Factory::getApplication();

        // Load language files — load from component's own directory as fallback
        // to ensure COM_J2COMMERCE_* strings are available even when the root
        // language/ directory doesn't have com_j2commerce.ini
        $language = $app->getLanguage();
        $language->load('com_j2commerce', JPATH_SITE);
        $language->load('com_j2commerce', JPATH_SITE . '/components/com_j2commerce');
        $language->load('mod_j2commerce_cart', JPATH_SITE . '/modules/mod_j2commerce_cart');

        // Safe defaults
        $items          = [];
        $order          = null;
        $productCount   = 0;
        $total          = 0.0;
        $formattedTotal = '';
        $cartUrl        = '';
        $checkoutUrl    = '';
        $ajaxUrl        = '';

        try {
            // Boot J2Commerce component and get the MVC factory
            $mvcFactory = $app->bootComponent('com_j2commerce')->getMVCFactory();

            // Use MVC factory to create admin CartModel (proper Joomla 6 pattern)
            $cartModel = $mvcFactory->createModel('Cart', 'Administrator', ['ignore_request' => true]);

            if ($cartModel) {
                $items = $cartModel->getItems();
            }

            if (!empty($items)) {
                // Count items: by quantity or distinct line items
                // Cart items use 'product_qty', order items use 'orderitem_quantity'
                if ((int) $params->get('quantity_count', 1) === 1) {
                    foreach ($items as $item) {
                        $productCount += (int) ($item->orderitem_quantity ?? $item->product_qty ?? 0);
                    }
                } else {
                    $productCount = \count($items);
                }

                // Get order with calculated totals (same chain as Site\CartsModel::getOrder())
                $order = OrderHelper::getInstance()
                    ->populateOrder($items)
                    ->getOrder();

                if ($order && isset($order->order_total)) {
                    $total = (float) $order->order_total;
                }

                // Get processed items from order (includes attributes)
                if ($order && method_exists($order, 'getItems')) {
                    $items = $order->getItems();
                }
            }

            // Format the total
            $formattedTotal = CurrencyHelper::format($total);

            // Build URLs — use menu item params if set, auto-detect matching
            // menu item, or fallback to RouteHelper
            $cartMenuItemId     = (int) $params->get('cart_menu_item', 0);
            $checkoutMenuItemId = (int) $params->get('checkout_menu_item', 0);

            // Auto-detect cart menu item when not manually configured
            if ($cartMenuItemId < 1) {
                $cartMenuItemId = $this->findMenuItemId('index.php?option=com_j2commerce&view=carts');
            }

            if ($cartMenuItemId > 0) {
                $cartUrl = Route::_('index.php?option=com_j2commerce&view=carts&Itemid=' . $cartMenuItemId);
            } else {
                $cartUrl = Route::_(RouteHelper::getCartRoute());
            }

            // Auto-detect checkout menu item when not manually configured
            if ($checkoutMenuItemId < 1) {
                $checkoutMenuItemId = $this->findMenuItemId('index.php?option=com_j2commerce&view=checkout');
            }

            if ($checkoutMenuItemId > 0) {
                $checkoutUrl = Route::_('index.php?option=com_j2commerce&view=checkout&Itemid=' . $checkoutMenuItemId);
            } else {
                $checkoutUrl = Route::_(RouteHelper::getCheckoutRoute());
            }

            $ajaxUrl = Route::_('index.php?option=com_j2commerce&task=carts.ajaxmini&format=raw', false);
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(
                'mod_j2commerce_cart: ' . $e->getMessage(),
                \Joomla\CMS\Log\Log::ERROR,
                'mod_j2commerce_cart'
            );
        }

        // Check if this is an AJAX request (set by CartsController::ajaxmini)
        $isAjax = $app->getUserState('mod_j2commerce_mini_cart.isAjax', false);

        $data['productCount']   = $productCount;
        $data['cartTotal']      = $total;
        $data['formattedTotal'] = $formattedTotal;
        $data['cartUrl']        = $cartUrl;
        $data['checkoutUrl']    = $checkoutUrl;
        $data['ajaxUrl']        = $ajaxUrl;
        $data['isAjax']         = $isAjax;
        $data['items']          = $items;
        $data['order']          = $order;

        // Append _uikit suffix to the layout name when UIkit subtemplate is selected
        $subtemplate = $params->get('subtemplate', 'app_bootstrap5');
        $layout      = $params->get('layout', 'default');

        if ($subtemplate === 'app_uikit' && !str_ends_with($layout, '_uikit')) {
            $params->set('layout', $layout . '_uikit');
        }

        return $data;
    }
}
