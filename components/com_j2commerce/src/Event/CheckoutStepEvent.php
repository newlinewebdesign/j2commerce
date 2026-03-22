<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Site\Event;

use Joomla\CMS\Event\AbstractEvent;

\defined('_JEXEC') or die;

class CheckoutStepEvent extends AbstractEvent
{
    public function getData(): array
    {
        return $this->getArgument('data', []);
    }

    public function setData(array $data): void
    {
        $this->setArgument('data', $data);
    }

    public function getErrors(): array
    {
        return $this->getArgument('errors', []);
    }

    public function addError(string $field, string $message): void
    {
        $errors          = $this->getArgument('errors', []);
        $errors[$field]  = $message;
        $this->setArgument('errors', $errors);
    }

    public function shouldStop(): bool
    {
        return $this->getArgument('stopPropagation', false);
    }

    public function stopPropagation(bool $stop = true, string $message = '', ?string $redirect = null): void
    {
        $this->setArgument('stopPropagation', $stop);

        if ($message) {
            $this->setArgument('message', $message);
        }

        if ($redirect) {
            $this->setArgument('redirectUrl', $redirect);
        }
    }

    public function getHtml(): string
    {
        return $this->getArgument('html', '');
    }

    public function appendHtml(string $html): void
    {
        $this->setArgument('html', $this->getArgument('html', '') . $html);
    }

    public function prependHtml(string $html): void
    {
        $this->setArgument('html', $html . $this->getArgument('html', ''));
    }
}
