<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\Field;

use Joomla\CMS\Form\Field\FolderlistField;
use Joomla\CMS\HTML\HTMLHelper;

\defined('_JEXEC') or die;

class ImageDirectoryField extends FolderlistField
{
    protected $type = 'ImageDirectory';

    protected function getOptions(): array
    {
        $this->directory  = 'images';
        $this->recursive  = true;
        $this->hideNone   = true;

        $parentOptions = parent::getOptions();

        $options = [
            HTMLHelper::_('select.option', 'images', 'images'),
        ];

        foreach ($parentOptions as $option) {
            if ($option->value === '' || $option->value === '-1') {
                continue;
            }

            $option->value = 'images/' . $option->value;
            $option->text  = 'images/' . $option->text;
            $options[]     = $option;
        }

        return $options;
    }
}
