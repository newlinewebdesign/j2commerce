<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.ecommerce
 *
 * @copyright   (C) 2024-2026 J2Commerce, LLC All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Ecommerce\Event;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract base class for all ecommerce schema events.
 *
 * Provides common functionality for schema modification events.
 * Third-party plugins can subscribe to these events to modify
 * the generated schema data before output.
 *
 * @since  6.0.0
 */
abstract class AbstractSchemaEvent extends AbstractImmutableEvent
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
        if (!\array_key_exists('subject', $arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Get the schema data array.
     *
     * @return  array  The schema data
     *
     * @since   6.0.0
     */
    public function getSchema(): array
    {
        return $this->arguments['subject'];
    }

    /**
     * Update the schema data.
     *
     * @param   array  $schema  The modified schema data
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setSchema(array $schema): void
    {
        $this->arguments['subject'] = $schema;
    }

    /**
     * Get a specific schema property.
     *
     * @param   string  $key      The property key
     * @param   mixed   $default  The default value if not set
     *
     * @return  mixed  The property value
     *
     * @since   6.0.0
     */
    public function getSchemaProperty(string $key, $default = null)
    {
        return $this->arguments['subject'][$key] ?? $default;
    }

    /**
     * Set a specific schema property.
     *
     * @param   string  $key    The property key
     * @param   mixed   $value  The property value
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setSchemaProperty(string $key, $value): void
    {
        $this->arguments['subject'][$key] = $value;
    }

    /**
     * Remove a specific schema property.
     *
     * @param   string  $key  The property key to remove
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function removeSchemaProperty(string $key): void
    {
        unset($this->arguments['subject'][$key]);
    }

    /**
     * Check if a schema property exists.
     *
     * @param   string  $key  The property key
     *
     * @return  bool  True if the property exists
     *
     * @since   6.0.0
     */
    public function hasSchemaProperty(string $key): bool
    {
        return isset($this->arguments['subject'][$key]);
    }
}
