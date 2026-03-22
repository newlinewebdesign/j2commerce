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

\defined('_JEXEC') or die;

interface CheckoutStepInterface
{
    public function getStepId(): string;

    public function getStepName(): string;

    public function getStepPosition(): string;

    public function getStepPriority(): int;

    public function shouldShow(array $context): bool;

    public function render(array $context): string;

    public function validate(array $data, array $context): array;

    public function save(array $data, array $context): bool;

    public function getStepData(array $context): array;
}
