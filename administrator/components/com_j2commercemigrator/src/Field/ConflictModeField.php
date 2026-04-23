<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;

/**
 * List field for selecting the conflict resolution mode used during migration.
 * Extends ListField so it inherits full list-field rendering (fancy-select, etc.).
 */
class ConflictModeField extends ListField
{
    protected $type = 'ConflictMode';

    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $modes = [
            'skip'        => 'COM_J2COMMERCEMIGRATOR_CONFLICT_MODE_SKIP',
            'overwrite'   => 'COM_J2COMMERCEMIGRATOR_CONFLICT_MODE_OVERWRITE',
            'merge'       => 'COM_J2COMMERCEMIGRATOR_CONFLICT_MODE_MERGE',
            'report-only' => 'COM_J2COMMERCEMIGRATOR_CONFLICT_MODE_REPORT_ONLY',
        ];

        foreach ($modes as $value => $langKey) {
            $option        = new \stdClass();
            $option->value = $value;
            $option->text  = \Joomla\CMS\Language\Text::_($langKey);

            $options[] = $option;
        }

        return $options;
    }
}
