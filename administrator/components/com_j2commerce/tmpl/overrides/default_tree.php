<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/** @var \J2Commerce\Component\J2commerce\Administrator\View\Overrides\HtmlView $this */

$currentFile = $this->source->filename ?? '';
$currentFileParts = $currentFile ? explode('/', str_replace('\\', '/', $currentFile)) : [];
?>

<?php if (!empty($this->overrideFiles)) : ?>
<ul class="directory-tree treeselect">
    <?php foreach ($this->overrideFiles as $item) : ?>
        <?php if ($item['type'] === 'folder') : ?>
            <?php
            // Determine if this folder is in the path of the currently open file
            $folderParts = explode('/', $item['path']);
            $isInPath = false;
            if (!empty($currentFileParts) && count($currentFileParts) >= count($folderParts)) {
                $isInPath = true;
                for ($i = 0; $i < count($folderParts); $i++) {
                    if ($folderParts[$i] !== $currentFileParts[$i]) {
                        $isInPath = false;
                        break;
                    }
                }
            }
            $class = $isInPath ? 'folder show' : 'folder';
            ?>
            <li class="<?php echo $class; ?>">
                <a class="folder-url" href="">
                    <span class="icon-folder icon-fw" aria-hidden="true"></span>&nbsp;<?php echo $this->escape($item['name']); ?>
                </a>
                <?php
                $temp = $this->overrideFiles;
                $this->overrideFiles = $item['children'];
                echo $this->loadTemplate('tree');
                $this->overrideFiles = $temp;
                ?>
            </li>
        <?php else : ?>
            <li>
                <a class="file" href="<?php echo Route::_('index.php?option=com_j2commerce&view=overrides&file=' . $item['id'] . '&tab=editor'); ?>">
                    <span class="icon-file-alt" aria-hidden="true"></span>&nbsp;<?php echo $this->escape($item['name']); ?>
                </a>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
