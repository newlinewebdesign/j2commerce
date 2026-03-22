<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

return [
    'slug'     => 'product-image',
    'label'    => 'Product Image',
    'icon'     => 'fa-solid fa-image',
    'category' => 'Product',
    'settings' => [
        'link'      => ['type' => 'checkbox', 'label' => 'Link to Product', 'default' => true],
        'css_class' => ['type' => 'text', 'label' => 'CSS Classes', 'default' => 'j2commerce-product-image position-relative border mb-3'],
    ],
];
