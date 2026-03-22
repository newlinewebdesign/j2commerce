<?php
/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.AppCurrencyupdater
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;

$layoutStyle = $displayData->params?->get('layout_style', 'bootstrap5') ?? 'bootstrap5';
$layout      = new FileLayout('default', __DIR__ . '/' . $layoutStyle);
echo $layout->render($displayData);
