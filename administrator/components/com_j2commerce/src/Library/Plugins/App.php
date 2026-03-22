<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace J2Commerce\Component\J2commerce\Administrator\Library\Plugins;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use J2Commerce\Component\J2commerce\Administrator\Library\Plugins\Base;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Utility\Utility;
use Joomla\Input\Input;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;



if (!class_exists('J2StoreAppPlugin')) {
    class J2StoreAppPlugin extends J2StorePluginBase
    {
        /**
         * @var $_element  string  Should always correspond with the plugin's filename,
         *                         forcing it to be unique
         */
        var $_element = '';

        function __construct(&$subject, $config)
        {
            parent::__construct($subject, $config);
            $this->loadLanguage('', JPATH_ADMINISTRATOR);
            $this->loadLanguage('', JPATH_SITE);
        }

        /************************************
         * Note to 3pd:
         *
         * The methods between here
         * and the next comment block are
         * yours to modify by overriding them in your shipping plugin
         *
         ************************************/


        public function onJ2StoreGetAppView($row)
        {
            if (!$this->_isMe($row)) {
                return null;
            }
        }


        /************************************
         * Note to 3pd:
         *
         * DO NOT MODIFY ANYTHING AFTER THIS
         * TEXT BLOCK UNLESS YOU KNOW WHAT YOU
         * ARE DOING!!!!!
         *
         ************************************/

        /**
         * Tells extension that this is a shipping plugin
         *
         * @param $element  string      a valid shipping plugin element
         * @return boolean    true if it is this particular shipping plugin
         */
        public function onJ2StoreGetAppPlugins($element)
        {
            $success = false;
            if ($this->_isMe($element)) {
                $success = true;
            }
            return $success;
        }

        /**
         * Prepares the 'view' tmpl layout
         * when viewing a app
         *
         * @return string
         */
        function _renderView($view = 'view', $vars = null)
        {
            if ($vars == null) $vars = new \stdClass();

            return $this->_getLayout($view, $vars);
        }

        /**
         * Prepares variables for the app form
         *
         * @return string
         */
        function _renderForm($data)
        {
            $vars = new \stdClass();
            return $this->_getLayout('form', $vars);
        }

        /**
         * Gets the appropriate values from the request
         */
        function _getState()
        {
            $state = new JObject();
            $app = J2Store::platform()->application();
            foreach ($state->getProperties() as $key => $value) {
                $new_value = $app->input->get($key);
                $value_exists = array_key_exists($key, $_POST);
                if ($value_exists && !empty($key)) {
                    $state->$key = $new_value;
                }
            }
            return $state;
        }
    }
}
