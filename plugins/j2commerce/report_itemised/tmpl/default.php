<?php
/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.ReportItemised
 *
 * @copyright   Copyright (C) 2024-2026 J2Commerce, LLC. All rights reserved.
 * @license     GNU General Public License version 2 or later;  see LICENSE.txt
 *
 * Legacy layout for PluginHelper::getLayoutPath() compatibility.
 * The new ReportpluginController route uses tmpl/report.php instead.
 */

defined('_JEXEC') or die;

// $displayData is set by the renderReport() method in the Extension class.
// This file delegates to report.php for the actual rendering.
include __DIR__ . '/report.php';
