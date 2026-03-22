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
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event triggered when preparing the schema form.
 *
 * This event allows plugins to add custom fields to the schema form,
 * modify existing fields, or inject additional form XML.
 *
 * Event name: onJ2CommerceSchemaFormPrepare
 *
 * Example usage in a plugin:
 * ```php
 * public function onFormPrepare(FormPrepareEvent $event): void
 * {
 *     $form = $event->getForm();
 *
 *     // Add a custom field XML
 *     $xml = '<field name="customField" type="text" label="Custom Field" />';
 *     $event->addFormXml($xml, 'schema.Ecommerce');
 * }
 * ```
 *
 * @since  6.0.0
 */
class FormPrepareEvent extends AbstractImmutableEvent
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
            throw new \BadMethodCallException("Argument 'subject' (form) of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument (form).
     *
     * @param   Form  $value  The form object
     *
     * @return  Form
     *
     * @since   6.0.0
     */
    protected function onSetSubject(Form $value): Form
    {
        return $value;
    }

    /**
     * Get the form object.
     *
     * @return  Form  The form
     *
     * @since   6.0.0
     */
    public function getForm(): Form
    {
        return $this->arguments['subject'];
    }

    /**
     * Get the form context (e.g., 'com_content.article').
     *
     * @return  string  The form context
     *
     * @since   6.0.0
     */
    public function getContext(): string
    {
        return $this->arguments['context'] ?? '';
    }

    /**
     * Get the article/item ID if available.
     *
     * @return  int|null  The item ID
     *
     * @since   6.0.0
     */
    public function getItemId(): ?int
    {
        return $this->arguments['itemId'] ?? null;
    }

    /**
     * Add XML to the form.
     *
     * @param   string  $xml    The XML string containing field definitions
     * @param   string  $group  The field group path (e.g., 'schema.Ecommerce')
     *
     * @return  bool  True on success
     *
     * @since   6.0.0
     */
    public function addFormXml(string $xml, string $group = ''): bool
    {
        $form = $this->getForm();

        // Wrap in form tags if not present
        if (strpos($xml, '<form') === false) {
            $xml = '<form>' . $xml . '</form>';
        }

        return $form->load($xml, true, '//form');
    }

    /**
     * Add a field path for custom field types.
     *
     * @param   string  $path  The field path
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function addFieldPath(string $path): void
    {
        $this->getForm()->addFieldPath($path);
    }

    /**
     * Set a field attribute.
     *
     * @param   string  $name       The field name
     * @param   string  $attribute  The attribute name
     * @param   mixed   $value      The attribute value
     * @param   string  $group      The field group
     *
     * @return  bool  True on success
     *
     * @since   6.0.0
     */
    public function setFieldAttribute(string $name, string $attribute, $value, string $group = ''): bool
    {
        return $this->getForm()->setFieldAttribute($name, $attribute, $value, $group);
    }

    /**
     * Remove a field from the form.
     *
     * @param   string  $name   The field name
     * @param   string  $group  The field group
     *
     * @return  bool  True on success
     *
     * @since   6.0.0
     */
    public function removeField(string $name, string $group = ''): bool
    {
        return $this->getForm()->removeField($name, $group);
    }
}
