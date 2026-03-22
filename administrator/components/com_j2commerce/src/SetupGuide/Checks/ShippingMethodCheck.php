<?php

declare(strict_types=1);

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace J2Commerce\Component\J2commerce\Administrator\SetupGuide\Checks;

use J2Commerce\Component\J2commerce\Administrator\SetupGuide\AbstractSetupCheck;
use J2Commerce\Component\J2commerce\Administrator\SetupGuide\SetupCheckResult;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class ShippingMethodCheck extends AbstractSetupCheck
{
    public function getId(): string
    {
        return 'shipping_method';
    }

    public function getGroup(): string
    {
        return 'payments_shipping';
    }

    public function getGroupOrder(): int
    {
        return 500;
    }

    public function isDismissible(): bool
    {
        return false;
    }

    public function getLabel(): string
    {
        return Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_SHIPPING_METHOD');
    }

    public function getDescription(): string
    {
        return Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_SHIPPING_METHOD_DESC');
    }

    public function check(): SetupCheckResult
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__j2commerce_shippingmethods'))
            ->where($db->quoteName('published') . ' = 1');

        $count = (int) $db->setQuery($query)->loadResult();

        return $count > 0
            ? new SetupCheckResult('pass', Text::sprintf('COM_J2COMMERCE_SETUP_GUIDE_CHECK_SHIPPING_METHOD_PASS', $count))
            : new SetupCheckResult('fail', Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_SHIPPING_METHOD_FAIL'));
    }

    public function getDetailView(): string
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('j2commerce_shippingmethod_id'), $db->quoteName('shipping_method_name')])
            ->from($db->quoteName('#__j2commerce_shippingmethods'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('shipping_method_name') . ' ASC');

        $methods = $db->setQuery($query)->loadObjectList();

        $shippingUrl = 'index.php?option=com_j2commerce&view=shippingmethods';

        $html = '<h5>' . Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_SHIPPING_METHOD') . '</h5>'
            . '<p>' . Text::_('COM_J2COMMERCE_SETUP_GUIDE_CHECK_SHIPPING_METHOD_DESC') . '</p>';

        if ($methods) {
            $html .= '<p class="text-muted small mb-1">' . Text::_('COM_J2COMMERCE_SETUP_GUIDE_ENABLED_SHIPPING_METHODS') . '</p>'
                . '<ul class="list-unstyled mb-3">';

            foreach ($methods as $method) {
                $editUrl = 'index.php?option=com_j2commerce&task=shippingmethod.edit&id=' . (int) $method->j2commerce_shippingmethod_id;

                $html .= '<li class="py-1 small">'
                    . '<i class="fa-regular fa-circle-check text-success me-1" aria-hidden="true"></i>'
                    . '<a href="' . $editUrl . '">' . htmlspecialchars($method->shipping_method_name) . '</a>'
                    . '</li>';
            }

            $html .= '</ul>';
        }

        $html .= '<a href="' . $shippingUrl . '" class="btn btn-primary w-100">'
            . Text::_('COM_J2COMMERCE_SETUP_GUIDE_ACTION_MANAGE_SHIPPING')
            . '</a>';

        return $html;
    }
}
