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

class Payment extends CMSPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = '';

    var $_j2version = '';

    var $_base = '';

    function __construct($subject, $config = [])
    {
        parent::__construct($subject, $config);

        $this->_base = new Base($subject, $config);
    }

    /**
     * Triggered before making the payment
     * You can perform any modification to the order table variables here. Like setting a surcharge
     *
     *
     * @param $order     object order table object
     * @return string   HTML to display. Normally an empty one.
     */
    function _beforePayment($order)
    {
        // Before the payment
        $html = '';
        return $html;
    }

    /**
     * Prepares the payment form
     * and returns HTML Form to be displayed to the user
     * generally will have a message saying, 'confirm entries, then click complete order'
     *
     * Submit button target for onsite payments & return URL for offsite payments should be:
     * index.php?option=com_j2commerce&view=billing&task=confirmPayment&orderpayment_type=xxxxxx
     * where xxxxxxx = $_element = the plugin's filename
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment($data)
    {
        // Process the payment

        $vars = new \stdClass();
        $vars->message = "Preprocessing successful. Double-check your entries.  Then, to complete your order, click Complete Order!";
        $path = PluginHelper::getLayoutPath($this->_type, $this->_element);
        return (new FileLayout('prepayment', $path))->render($vars);
    }

    /**
     * @param $order
     * @return void
     */
    function onJ2StoreCalculateFees($order) {
        //has the customer selected this method for payment? If yes, apply the fees
        $payment_method = $order->get_payment_method();
        if($payment_method == $this->_element) {
            $total = $order->order_subtotal + $order->order_shipping + $order->order_shipping_tax;
            $surcharge = 0;
            $surcharge_percent = $this->params->get('surcharge_percent', 0);
            $surcharge_fixed = $this->params->get('surcharge_fixed', 0);
            if( $total > 0 && ( (float) $surcharge_percent > 0 || (float) $surcharge_fixed > 0)) {
                //percentage
                if((float) $surcharge_percent > 0) {
                    $surcharge += ($total * (float) $surcharge_percent) / 100;
                }
                if((float) $surcharge_fixed > 0) {
                    $surcharge += (float) $surcharge_fixed;
                }
                $name = $this->params->get('surcharge_name', Text::_('COM_J2COMMERCE_CART_SURCHARGE'));
                $tax_class_id = $this->params->get('surcharge_tax_class_id', '');
                $taxable = false;
                if($tax_class_id && $tax_class_id > 0) $taxable = true;
                if($surcharge > 0) {
                    $order->add_fee($name, round($surcharge, 2), $taxable, $tax_class_id);
                }
            }
        }
    }

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     * @throws Exception
     */
    function _postPayment($data)
    {
        // Process the payment
        $app = J2CommerceHelper::platform()->application();
        $paction = $app->input->getString('paction', '');
        $base = new Base();
        $vars = new \stdClass();

        switch ($paction) {
            case "display":
                $vars->message = Text::_($this->params->get('onafterpayment', ''));
                $path = PluginHelper::getLayoutPath($this->_type, $this->_element);
                $html = (new FileLayout('message', $path))->render($vars);
                $html .= $base->_displayArticle();
                break;
            case "process":
                $vars->message = $this->_process();
                $path = PluginHelper::getLayoutPath($this->_type, $this->_element);
                $html = (new FileLayout('message', $path))->render($vars);
                echo $html;
                $app->close();
                break;
            case "cancel":
                $vars->message = Text::_($this->params->get('oncancelpayment', ''));
                $path = PluginHelper::getLayoutPath($this->_type, $this->_element);
                $html = (new FileLayout('message', $path))->render($vars);
                break;
            default:
                $vars->message = Text::_($this->params->get('onerrorpayment', ''));
                $path = PluginHelper::getLayoutPath($this->_type, $this->_element);
                $html = (new FileLayout('message', $path))->render($vars);
                break;
        }

        return $html;
    }

    /**
     * Prepares the 'view' tmpl layout
     * when viewing a payment record
     *
     * @param $orderPayment     object       a valid TableOrderPayment object
     * @return string   HTML to display
     */
    function _renderView($orderPayment)
    {
        // Load the payment from _orderpayments and render its html
        $vars = new \stdClass();
        $path = PluginHelper::getLayoutPath($this->_type, $this->_element);
        $html = (new FileLayout('view', $path))->render($vars);
        return $html;
    }

    /**
     * Prepares variables for the payment form
     *
     * @param $data     array       form post data for pre-populating form
     * @return string   HTML to display
     */
    function _renderForm($data)
    {
        $vars = new \stdClass();
        $vars->onselection_text = $this->params->get('onselection', '');
        $path = PluginHelper::getLayoutPath($this->_type, $this->_element);
        $html = (new FileLayout('form', $path))->render($vars);
        return $html;
    }

    /**
     * Verifies that all the required form fields are completed
     * if any fail verification, set
     * $object->error = true
     * $object->message .= '<li>x item failed verification</li>'
     *
     * @param $submitted_values     array   post data
     * @return stdClass
     */
    function _verifyForm($submitted_values)
    {
        $vars = new \stdClass();
        $vars->error = false;
        $vars->message = '';
        return $vars;
    }

    /**
     * Tells extension that this is a payment plugin
     *
     * @param $element  string      a valid payment plugin element
     * @return boolean
     */
    function onJ2StoreGetPaymentPlugins($element)
    {
        $success = false;

        if ($this->_base->_isMe($element)) {
            $success = true;
        }
        return $success;
    }

    function onJ2StoreGetPaymentOptions($element, $order)
    {
        // Check if this is the right plugin

        if (!$this->_base->_isMe($element)) {
            return null;
        }

        $found = true;

        // if this payment method should be available for this order, return true
        // if not, return false.
        // by default, all enabled payment methods are valid, so return true here,
        // but plugins may override this

        $order->setAddress();
        $address = $order->getBillingAddress();
        $geozone_id = $this->params->get('geozone_id', '');

        if (isset($geozone_id) && (int)$geozone_id > 0) {
            //get the geozones
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('gz.*,gzr.*')->from('#__j2commerce_geozones AS gz')
                ->innerJoin('#__j2commerce_geozonerules AS gzr ON gzr.geozone_id = gz.j2commerce_geozone_id')
                ->where('gz.j2commerce_geozone_id=' . $db->q($geozone_id))
                ->where('gzr.country_id=' . $db->q($address['country_id']) . ' AND (gzr.zone_id=0 OR gzr.zone_id=' . $db->q($address['zone_id']) . ')');
            $db->setQuery($query);
            $grows = $db->loadObjectList();

            if (!$geozone_id) {
                $found = true;
            } elseif ($grows) {
                $found = true;
            } else {
                $found = false;
            }
        }

        return $found;
    }


    /**
     * Wrapper for the internal _renderForm method
     *
     * @param $element  string      a valid payment plugin element
     * @param $data     array       form post data
     * @return string
     */
    function onJ2StoreGetPaymentForm($element, $data)
    {
        if (!$this->_base->_isMe($element)) {
            return null;
        }
        return $this->_renderForm($data);
    }

    /**
     * Wrapper for the internal _verifyForm method
     *
     * @param $element  string      a valid payment plugin element
     * @param $data     array       form post data
     * @return stdClass
     */
    function onJ2StoreGetPaymentFormVerify($element, $data)
    {
        if (!$this->_base->_isMe($element)) {
            return null;
        }
        return $this->_verifyForm($data);
    }

    /**
     * Wrapper for the internal _renderView method
     *
     * @param $element  string      a valid payment plugin element
     * @param $orderPayment  object      a valid TableOrderPayment object
     * @return string
     */
    function onJ2StoreGetPaymentView($element, $orderPayment)
    {
        if (!$this->_base->_isMe($element)) {
            return null;
        }

        return $this->_renderView($orderPayment);
    }

    /**
     * Wrapper for the internal _prePayment method
     * which performs any necessary actions before payment
     *
     * @param $element  string      a valid payment plugin element
     * @param $data     array       form post data
     * @return string
     */
    function onJ2StorePrePayment($element, $data)
    {
        if (!$this->_base->_isMe($element)) {
            return null;
        }
        return $this->_prePayment($data);
    }

    /**
     * Wrapper for the internal _postPayment method
     * that processes the payment after user submits
     *
     * @param $element  string      a valid payment plugin element
     * @param $data     array       form post data
     * @return string
     * @throws Exception
     */
    function onJ2StorePostPayment($element, $data)
    {
        if (!$this->_base->_isMe($element)) {
            return null;
        }
        return $this->_postPayment($data);
    }

    /**
     * Wrapper for the internal _beforePayment method
     * which performs any necessary actions before payment
     *
     * @param $element  string      a valid payment plugin element
     * @param $order    object      order object
     * @return string
     */
    function onJ2StoreBeforePayment($element, $order)
    {
        if (!$this->_base->_isMe($element)) {
            return null;
        }
        return $this->_beforePayment($order);
    }


    public function getVersion()
    {

        if (empty($this->_j2version)) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            // Get installed version
            $query = $db->getQuery(true);
            $query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('element') . ' = ' . $db->quote('com_j2commerce'));
            $db->setQuery($query);
            $manifest_cache = $db->loadResult();
            $registry = J2CommerceHelper::platform()->getRegistry($manifest_cache);
            $this->_j2version = $registry->get('version');
        }

        return $this->_j2version;
    }

    function getCurrency($order, $convert = false)
    {
        $results = array();
        $currency_code = $order->currency_code;
        $currency_value = $order->currency_value;

        $results['currency_code'] = $currency_code;
        $results['currency_value'] = $currency_value;
        $results['convert'] = $convert;

        return $results;
    }

    public function generateHash($order)
    {
        $secret_key = J2CommerceHelper::config()->get('queue_key', '');
        $status = $this->params->get('payment_status', 4);
        $session = J2CommerceHelper::platform()->application()->getSession();
        $session_id = $session->getId();
        $hash_string = $order->order_id . $secret_key . $order->orderpayment_type . $secret_key . $status . $secret_key . $order->user_email . $secret_key . $session_id . $secret_key;
        return md5($hash_string);
    }

    public function validateHash($order)
    {
        $app = J2CommerceHelper::platform()->application();
        $hash = $app->input->getString('hash', '');
        $generator_hash = $this->generateHash($order);
        $status = true;
        if ($hash != $generator_hash) {
            $status = false;
        }
        return $status;
    }

    /**
     * Return url for payment gateway
     */
    public function getReturnUrl()
    {
        $platform = J2CommerceHelper::platform();
        $url = $platform->getThankyouPageUrl(array('orderpayment_type' => $this->_element, 'paction' => 'display'));

        return $url;
    }

    function _getFormattedTransactionDetails( $data )
    {
        return json_encode($data);
    }
}
