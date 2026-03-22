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
 * Event triggered when preparing variant Product schema data.
 *
 * This event is dispatched for each variant when building ProductGroup schema.
 * Plugins can modify individual variant data, add variant-specific images,
 * or set variant-specific properties like color or size.
 *
 * Event name: onJ2CommerceSchemaVariantPrepare
 *
 * Example usage in a plugin:
 * ```php
 * public function onVariantPrepare(VariantSchemaPrepareEvent $event): void
 * {
 *     $variant = $event->getVariant();
 *     $schema = $event->getSchema();
 *
 *     // Add variant-specific image
 *     $variantImage = $this->getVariantImage($variant->j2store_variant_id);
 *     if ($variantImage) {
 *         $schema['image'] = $variantImage;
 *         $event->setSchema($schema);
 *     }
 *
 *     // Add color property
 *     $event->setVariantProperty('color', 'Red');
 * }
 * ```
 *
 * @since  6.0.0
 */
class VariantSchemaPrepareEvent extends AbstractSchemaEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   6.0.0
     */
    public function __construct(string $name, array $arguments = [])
    {
        if (!\array_key_exists('variant', $arguments)) {
            throw new \BadMethodCallException("Argument 'variant' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('productId', $arguments)) {
            throw new \BadMethodCallException("Argument 'productId' of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument (variant schema data).
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
     * Get the variant object.
     *
     * @return  object  The variant object
     *
     * @since   6.0.0
     */
    public function getVariant(): object
    {
        return $this->arguments['variant'];
    }

    /**
     * Get the variant ID.
     *
     * @return  int  The variant ID
     *
     * @since   6.0.0
     */
    public function getVariantId(): int
    {
        return (int) ($this->arguments['variant']->j2store_variant_id ?? 0);
    }

    /**
     * Get the parent product ID.
     *
     * @return  int  The product ID
     *
     * @since   6.0.0
     */
    public function getProductId(): int
    {
        return (int) $this->arguments['productId'];
    }

    /**
     * Get the parent product object if available.
     *
     * @return  object|null  The parent product object
     *
     * @since   6.0.0
     */
    public function getParentProduct(): ?object
    {
        return $this->arguments['parentProduct'] ?? null;
    }

    /**
     * Get variant options (e.g., color, size selections).
     *
     * @return  array  Array of option name => value pairs
     *
     * @since   6.0.0
     */
    public function getVariantOptions(): array
    {
        return $this->arguments['variantOptions'] ?? [];
    }

    /**
     * Check if this is the master variant.
     *
     * @return  bool  True if master variant
     *
     * @since   6.0.0
     */
    public function isMasterVariant(): bool
    {
        return (int) ($this->arguments['variant']->is_master ?? 0) === 1;
    }

    /**
     * Set a variant-specific property (e.g., color, size, material).
     *
     * @param   string  $property  The schema.org property name
     * @param   mixed   $value     The property value
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setVariantProperty(string $property, $value): void
    {
        $this->setSchemaProperty($property, $value);
    }

    /**
     * Set the variant image.
     *
     * @param   string|array  $image  Image URL or ImageObject schema
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setVariantImage($image): void
    {
        $this->setSchemaProperty('image', $image);
    }

    /**
     * Set the variant SKU.
     *
     * @param   string  $sku  The SKU
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setVariantSku(string $sku): void
    {
        $this->setSchemaProperty('sku', $sku);
    }

    /**
     * Set the variant GTIN.
     *
     * @param   string  $gtin  The GTIN/UPC/EAN
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setVariantGtin(string $gtin): void
    {
        $this->setSchemaProperty('gtin', $gtin);
    }
}
