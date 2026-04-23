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

final class RunSummary
{
    public function __construct(
        public readonly int    $runId,
        public readonly string $adapter,
        public readonly string $status,
        public readonly int    $inserted,
        public readonly int    $skipped,
        public readonly int    $overwritten,
        public readonly int    $merged,
        public readonly int    $errors,
        public readonly ?string $startedOn,
        public readonly ?string $finishedOn,
    ) {}
}
