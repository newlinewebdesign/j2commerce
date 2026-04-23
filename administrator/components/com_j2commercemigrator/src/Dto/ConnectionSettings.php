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

final class ConnectionSettings
{
    public function __construct(
        public readonly string $mode,
        public readonly string $host,
        public readonly int    $port,
        public readonly string $database,
        public readonly string $username,
        public readonly string $password,
        public readonly string $prefix,
        public readonly bool   $ssl,
        public readonly string $sslCa,
    ) {}
}
