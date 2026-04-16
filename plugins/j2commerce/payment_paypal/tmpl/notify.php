<?php
/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.PaymentPaypal
 *
 * @copyright   Copyright (C) 2024-2026 J2Commerce, LLC. All rights reserved.
 * @license     GNU General Public License version 2 or later;  see LICENSE.txt
 */

/* Initialize Joomla framework */
if (!defined('_JEXEC')) {
    define('_JEXEC', 1);
}

header('Content-type: text/plain; charset=UTF-8');

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// Get plugin path
$plg_name = basename(dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', str_replace(
    DS . 'plugins' . DS . 'j2commerce' . DS . $plg_name . DS . $plg_name . DS . 'tmpl' . DS,
    '',
    dirname(__FILE__)
));

require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';

jimport('joomla.registry.registry');
jimport('joomla.session.session');
jimport('joomla.uri.uri');

// Instantiate the application.
$app = Factory::getApplication('site');

$post = $app->input->getArray($_REQUEST);
$rawUrl = Uri::root();
// First remove references to the plugin names
$baseUrl = str_replace(
    'plugins' . DS . 'j2commerce' . DS . $plg_name . DS . $plg_name . DS . 'tmpl' . DS,
    '',
    $rawUrl
);
$url = ltrim($baseUrl, '/');
$siteUrl = rtrim($url, '/');
$request = '';
foreach ($post as $key => $value) {
    $request .= '&' . $key . '=' . $value;
}

$redirect = $siteUrl . '/index.php?option=com_j2commerce&view=checkout&task=confirmPayment&orderpayment_type=' . $plg_name . '&paction=process&tmpl=component' . $request;
header('location: ' . $redirect);
