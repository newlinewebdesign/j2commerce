<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Customfields list controller class.
 *
 * @since  6.0.4
 */
class CustomfieldsController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * IMPORTANT: Use general 'COM_J2COMMERCE' prefix so bulk action messages
     * like N_ITEMS_PUBLISHED use shared language strings, not view-specific ones.
     *
     * @var    string
     * @since  6.0.4
     */
    protected $text_prefix = 'COM_J2COMMERCE';

    /**
     * Constructor.
     *
     * @param   array                     $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface|null  $factory  The factory.
     * @param   CMSApplication|null       $app      The Application for the dispatcher
     * @param   Input|null                $input    Input
     *
     * @since   6.0.4
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
    {
        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since   6.0.4
     */
    public function getModel($name = 'Customfield', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function saveOrderAjax(): void
    {
        $pks   = $this->input->post->get('cid', [], 'array');
        $order = $this->input->post->get('order', [], 'array');

        $pks   = ArrayHelper::toInteger($pks);
        $order = ArrayHelper::toInteger($order);

        $model  = $this->getModel();
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo '1';
        }

        Factory::getApplication()->close();
    }
}