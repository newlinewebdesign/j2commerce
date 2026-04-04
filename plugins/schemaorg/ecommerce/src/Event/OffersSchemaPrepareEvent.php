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
 * Event triggered when preparing Offer schema data.
 *
 * This event allows third-party plugins to modify pricing, availability,
 * and other offer-related schema properties. Useful for plugins that
 * handle special pricing, memberships, or promotional offers.
 *
 * Event name: onJ2CommerceSchemaOffersPrepare
 *
 * Example usage in a plugin:
 * ```php
 * public function onOffersPrepare(OffersSchemaPrepareEvent $event): void
 * {
 *     $offer = $event->getSchema();
 *
 *     // Add sale price if applicable
 *     if ($this->isOnSale($event->getVariantId())) {
 *         $offer['priceSpecification'] = [
 *             '@type' => 'PriceSpecification',
 *             'price' => '99.99',
 *             'priceCurrency' => 'USD',
 *             'validFrom' => '2024-01-01',
 *             'validThrough' => '2024-12-31'
 *         ];
 *         $event->setSchema($offer);
 *     }
 * }
 * ```
 *
 * @since  6.0.0
 */
class OffersSchemaPrepareEvent extends AbstractSchemaEvent
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

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument (offer schema data).
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
     * @return  object|null  The variant object
     *
     * @since   6.0.0
     */
    public function getVariant(): ?object
    {
        return $this->arguments['variant'] ?? null;
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
        $variant = $this->getVariant();

        return $variant ? (int) ($variant->j2commerce_variant_id ?? 0) : 0;
    }

    /**
     * Get the product ID.
     *
     * @return  int  The product ID
     *
     * @since   6.0.0
     */
    public function getProductId(): int
    {
        return (int) ($this->arguments['productId'] ?? 0);
    }

    /**
     * Set the offer price.
     *
     * @param   float   $price     The price value
     * @param   string  $currency  The currency code (e.g., 'USD')
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setPrice(float $price, string $currency = ''): void
    {
        $this->setSchemaProperty('price', (string) number_format($price, 2, '.', ''));

        if (!empty($currency)) {
            $this->setSchemaProperty('priceCurrency', $currency);
        }
    }

    /**
     * Set the availability status.
     *
     * @param   string  $availability  The schema.org availability URL
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setAvailability(string $availability): void
    {
        $this->setSchemaProperty('availability', $availability);
    }

    /**
     * Set the price valid until date.
     *
     * @param   string  $date  The date in ISO 8601 format
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setPriceValidUntil(string $date): void
    {
        $this->setSchemaProperty('priceValidUntil', $date);
    }

    /**
     * Set a price specification for complex pricing scenarios.
     *
     * @param   array  $priceSpec  The PriceSpecification schema array
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setPriceSpecification(array $priceSpec): void
    {
        $this->setSchemaProperty('priceSpecification', $priceSpec);
    }

    /**
     * Set the seller organization.
     *
     * @param   array  $seller  The Organization/Person schema array
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setSeller(array $seller): void
    {
        $this->setSchemaProperty('seller', $seller);
    }

    /**
     * Set item condition.
     *
     * @param   string  $condition  The OfferItemCondition URL (e.g., https://schema.org/NewCondition)
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setItemCondition(string $condition): void
    {
        $this->setSchemaProperty('itemCondition', $condition);
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
        $variant = $this->getVariant();

        return $variant && (int) ($variant->is_master ?? 0) === 1;
    }
}
