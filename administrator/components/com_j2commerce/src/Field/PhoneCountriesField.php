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

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\CheckboxesField;
use J2Commerce\Component\J2commerce\Administrator\Helper\PhoneHelper;

/**
 * Renders a checkbox list of all countries from PhoneHelper::DIAL_DATA,
 * with flag image, country name, and dial code.
 *
 * Stores selected ISO2 codes as a JSON array.
 */
class PhoneCountriesField extends CheckboxesField
{
    protected $type = 'PhoneCountries';

    /** @return  \SimpleXMLElement[] */
    protected function getOptions(): array
    {
        $dialData = PhoneHelper::getDialData();
        $countries = PhoneHelper::getCountryListForDropdown();

        // Build a name map from the DB-enabled countries
        $nameMap = [];
        foreach ($countries as $c) {
            $nameMap[$c['iso2']] = $c['name'];
        }

        // Build options for all entries in DIAL_DATA, sorted by name
        $options = [];
        foreach ($dialData as $iso2 => $data) {
            $name = $nameMap[$iso2] ?? $iso2;

            $options[$name . $iso2] = [
                'value' => $iso2,
                'text'  => htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
                    . ' <span class="text-muted">+' . htmlspecialchars($data['code'], ENT_QUOTES, 'UTF-8') . '</span>',
            ];
        }

        ksort($options);

        $result = [];
        foreach ($options as $opt) {
            $o        = new \stdClass();
            $o->value = $opt['value'];
            $o->text  = $opt['text'];
            $result[] = $o;
        }

        return $result;
    }

    /**
     * The field stores a JSON array; decode it to an array of checked values.
     */
    protected function getChecked(): array
    {
        $value = $this->value;

        if (\is_string($value) && str_starts_with(trim($value), '[')) {
            $decoded = json_decode($value, true);
            if (\is_array($decoded)) {
                return $decoded;
            }
        }

        if (\is_array($value)) {
            if (isset($value[0]) && \is_array($value[0])) {
                return $value[0];
            }
            return $value;
        }

        return [];
    }

    protected function getInput(): string
    {
        $checked = $this->getChecked();
        $options = $this->getOptions();
        $name    = $this->name;
        $id      = $this->id;

        $cols = 3;
        $colClass = 'col-md-' . (12 / $cols);

        $html = '<div class="row g-2">';
        foreach ($options as $i => $opt) {
            $isChecked = \in_array($opt->value, $checked, true) ? ' checked' : '';
            $optId     = $id . '_' . $i;
            $html .= '<div class="' . $colClass . '">'
                . '<div class="form-check">'
                . '<input type="checkbox" class="form-check-input" '
                . 'name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" '
                . 'id="' . htmlspecialchars($optId, ENT_QUOTES, 'UTF-8') . '" '
                . 'value="' . htmlspecialchars($opt->value, ENT_QUOTES, 'UTF-8') . '"'
                . $isChecked . '>'
                . '<label class="form-check-label" for="' . htmlspecialchars($optId, ENT_QUOTES, 'UTF-8') . '">'
                . $opt->text
                . '</label>'
                . '</div>'
                . '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
