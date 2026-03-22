<?php
/**
* @package     J2Commerce
* @subpackage  com_j2commerce
*
* @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/


// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

// Access view properties directly
$items = $this->items;
$pagination = $this->pagination;
$state = $this->state;
$product_id = $this->product_id;
$row = $this->row;
$files = $this->files ?? [];

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.core');
?>

<div class="product-files-view">
    <!-- Same HTML as before, but using $this-> variables -->
    <form action="<?php echo Route::_('index.php?option=com_j2commerce&view=productfiles&product_id=' . $product_id); ?>"
          method="post"
          name="adminForm"
          id="adminForm">
rest goes here.
        <!-- Rest of the template here -->

    </form>
</div>
