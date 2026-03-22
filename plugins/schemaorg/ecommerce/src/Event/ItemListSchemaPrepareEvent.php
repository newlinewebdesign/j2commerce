<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.ecommerce
 *
 * @copyright   (C) 2024-2026 J2Commerce, LLC All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Ecommerce\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event triggered when preparing ItemList schema for category pages.
 *
 * This event allows plugins to modify the product list schema on category
 * pages, add filtering information, or modify the list ordering.
 *
 * Event name: onJ2CommerceSchemaItemListPrepare
 *
 * Example usage in a plugin:
 * ```php
 * public function onItemListPrepare(ItemListSchemaPrepareEvent $event): void
 * {
 *     // Add items to the list
 *     foreach ($products as $product) {
 *         $event->addListItem([
 *             '@type' => 'Product',
 *             'name' => $product->name,
 *             'url' => $product->url
 *         ], $position);
 *     }
 *
 *     // Set the number of items
 *     $event->setNumberOfItems(count($products));
 * }
 * ```
 *
 * @since  6.0.0
 */
class ItemListSchemaPrepareEvent extends AbstractSchemaEvent
{
    /**
     * Setter for the subject argument (item list schema data).
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since   6.0.0
     */
    protected function onSetSubject(array $value): array
    {
        return $value;
    }

    /**
     * Get the category ID.
     *
     * @return  int|null  The category ID or null
     *
     * @since   6.0.0
     */
    public function getCategoryId(): ?int
    {
        return $this->arguments['categoryId'] ?? null;
    }

    /**
     * Get the category object if available.
     *
     * @return  object|null  The category object
     *
     * @since   6.0.0
     */
    public function getCategory(): ?object
    {
        return $this->arguments['category'] ?? null;
    }

    /**
     * Get the current page number.
     *
     * @return  int  The page number (1-indexed)
     *
     * @since   6.0.0
     */
    public function getPage(): int
    {
        return (int) ($this->arguments['page'] ?? 1);
    }

    /**
     * Get the items per page limit.
     *
     * @return  int  The limit
     *
     * @since   6.0.0
     */
    public function getLimit(): int
    {
        return (int) ($this->arguments['limit'] ?? 20);
    }

    /**
     * Get the list items (itemListElement).
     *
     * @return  array  Array of ListItem schema objects
     *
     * @since   6.0.0
     */
    public function getListItems(): array
    {
        return $this->getSchemaProperty('itemListElement', []);
    }

    /**
     * Set the list items.
     *
     * @param   array  $items  Array of ListItem schema objects
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setListItems(array $items): void
    {
        $this->setSchemaProperty('itemListElement', $items);
    }

    /**
     * Add a list item (product) to the ItemList.
     *
     * @param   array  $item      The Product schema object
     * @param   int    $position  The position in the list (1-indexed)
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function addListItem(array $item, int $position): void
    {
        $items = $this->getListItems();

        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'item'     => $item,
        ];

        // Sort by position
        usort($items, function ($a, $b) {
            return ($a['position'] ?? 0) <=> ($b['position'] ?? 0);
        });

        $this->setListItems($items);
    }

    /**
     * Set the number of items in the list.
     *
     * @param   int  $count  The number of items
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setNumberOfItems(int $count): void
    {
        $this->setSchemaProperty('numberOfItems', $count);
    }

    /**
     * Get the number of items.
     *
     * @return  int  The number of items
     *
     * @since   6.0.0
     */
    public function getNumberOfItems(): int
    {
        return (int) $this->getSchemaProperty('numberOfItems', 0);
    }

    /**
     * Set the list name.
     *
     * @param   string  $name  The list name
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setName(string $name): void
    {
        $this->setSchemaProperty('name', $name);
    }

    /**
     * Set the list description.
     *
     * @param   string  $description  The description
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setDescription(string $description): void
    {
        $this->setSchemaProperty('description', $description);
    }

    /**
     * Set the item list order.
     *
     * @param   string  $order  The ItemListOrderType (e.g., ItemListOrderAscending)
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setItemListOrder(string $order): void
    {
        $this->setSchemaProperty('itemListOrder', 'https://schema.org/' . $order);
    }

    /**
     * Get the count of list items.
     *
     * @return  int  The count of items in itemListElement
     *
     * @since   6.0.0
     */
    public function getListItemCount(): int
    {
        return \count($this->getListItems());
    }
}
