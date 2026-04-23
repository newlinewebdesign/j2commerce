<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Adapter;

use J2Commerce\Component\J2commercemigrator\Administrator\Dto\ConnectionSchema;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\ConnectionSettings;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\ImageDiscoveryResult;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\PrerequisiteReport;
use J2Commerce\Component\J2commercemigrator\Administrator\Dto\SourceInfo;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\SourceDatabaseReaderInterface;

interface MigratorAdapterInterface
{
    /** Machine key identifying the adapter (e.g. 'j2store4', 'woocommerce'). */
    public function getKey(): string;

    /** UI metadata — title, description, icon, author. */
    public function getSourceInfo(): SourceInfo;

    /**
     * Tier definitions. Shape:
     *   [int $tier => ['name' => string, 'tables' => string[]]]
     */
    public function getTierDefinitions(): array;

    /** Source table → target table map. */
    public function getTableMap(): array;

    /** Column map for a specific source table. Return [] to auto-derive by name. */
    public function getColumnMap(string $sourceTable): array;

    /** table => callable(array $row, array $context): array */
    public function getRowTransformers(): array;

    /** PK column name for a target table, e.g. 'j2commerce_product_id'. */
    public function getConflictKey(string $targetTable): string;

    /**
     * Token-level string replacements applied to serialized blobs, JSON bodies,
     * and template code.
     */
    public function getTokenReplacements(): array;

    /** Build a reader for the given settings. */
    public function getSourceReader(ConnectionSettings $c): SourceDatabaseReaderInterface;

    /** Fields the adapter requires for connection. */
    public function describeConnection(): ConnectionSchema;

    /** Image discovery — where the source keeps its images. */
    public function discoverImages(): ImageDiscoveryResult;

    /** Pre-flight environment checks. */
    public function validatePrerequisites(): PrerequisiteReport;
}
