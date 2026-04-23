<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Dto;

final class PrerequisiteReport
{
    /** @param list<array{severity: string, message: string}> $issues */
    public function __construct(
        public readonly bool  $passed,
        public readonly array $issues,
    ) {}
}
