<?php

/**
 * @package     J2Commerce
 * @subpackage  mod_j2commerce_currency
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Module\Currency\Site\Dispatcher;

use J2Commerce\Component\J2commerce\Administrator\Helper\CurrencyHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class Dispatcher extends AbstractModuleDispatcher
{
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        Factory::getLanguage()->load('com_j2commerce', JPATH_SITE);

        $currencies      = CurrencyHelper::getAll();
        $currentCode     = CurrencyHelper::getCode();
        $redirectUrl     = base64_encode(Uri::getInstance()->toString());

        $data['currencies']   = $currencies;
        $data['currentCode']  = $currentCode;
        $data['redirectUrl']  = $redirectUrl;

        return $data;
    }
}
