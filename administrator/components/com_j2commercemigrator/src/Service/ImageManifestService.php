<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Service;

use J2Commerce\Component\J2commercemigrator\Administrator\Helper\MigrationLogger;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\SourceDatabaseReaderInterface;

/**
 * Builds a manifest of product image paths referenced by the SOURCE j2store database, then
 * copies only those files from the source filesystem into the target's `images/shop/`
 * folder — bucketed into `main/` and `thumbs/` subfolders.
 *
 * This is far tighter than a blind tree copy: it only moves files the store actually uses,
 * flags broken references (DB says the file exists, filesystem says it doesn't) before the
 * cutover, and gives accurate progress stats.
 */
class ImageManifestService
{
    /**
     * Table → list of [column, bucket, kind] triples.
     * - bucket is 'main' or 'thumbs'.
     * - kind:
     *     'path' — column holds a single relative path string.
     *     'json' — column holds a JSON array of relative path strings.
     * Columns that don't exist on a given source install are silently skipped via the
     * upfront describe() check, so this map can be a superset.
     */
    private const SCAN_MAP = [
        'j2store_productimages' => [
            ['main_image',        'main',   'path'],
            ['thumb_image',       'thumbs', 'path'],
            ['additional_images', 'main',   'json'],
        ],
        'j2store_optionvalues' => [
            ['optionvalue_image', 'main', 'path'],
        ],
    ];

    private array $lastDiagnostics = [];

    public function __construct(
        private SourceDatabaseReaderInterface $source,
        private MigrationLogger $logger
    ) {}

    /**
     * Scans the source DB and returns a deduped manifest of image references.
     *
     * Shape: [
     *   'images/j2store/products/abc.jpg' => 'main',
     *   'images/j2store/thumbs/abc_t.jpg' => 'thumbs',
     *   ...
     * ]
     *
     * If the same path appears in both a main-image column and a thumbnail column it's
     * classified as 'main' (main wins — an image the store displays at full size should
     * not be buried in /thumbs/).
     */
    public function build(): array
    {
        $manifest = [];
        $diag     = ['tables' => [], 'source_prefix' => $this->source->getPrefix()];

        foreach (self::SCAN_MAP as $table => $columns) {
            $describeCols = array_column($this->source->describe($table), 'Field');
            $tableDiag    = ['present' => !empty($describeCols), 'columns' => []];

            if (empty($describeCols)) {
                $this->logger->info("ImageManifestService: table {$table} not present on source — skipping");
                $diag['tables'][$table] = $tableDiag;
                continue;
            }

            $pk = $this->source->getPrimaryKey($table) ?? $describeCols[0];

            foreach ($columns as [$col, $bucket, $kind]) {
                $colDiag = ['exists' => in_array($col, $describeCols, true), 'rows_with_value' => 0, 'paths_added' => 0];

                if (!$colDiag['exists']) {
                    $tableDiag['columns'][$col] = $colDiag;
                    continue;
                }

                $rows = $this->source->fetchBatch($table, $pk, 0, 500000);

                foreach ($rows as $row) {
                    $raw = (string) ($row[$col] ?? '');
                    if ($raw === '') {
                        continue;
                    }
                    $colDiag['rows_with_value']++;

                    $paths = $kind === 'json' ? $this->extractJsonPaths($raw) : [$raw];

                    foreach ($paths as $p) {
                        $path = $this->normalize((string) $p);
                        if ($path === '') {
                            continue;
                        }
                        if (!isset($manifest[$path]) || $manifest[$path] === 'thumbs') {
                            $manifest[$path] = $bucket;
                        }
                        $colDiag['paths_added']++;
                    }
                }

                $this->logger->info("ImageManifestService: {$table}.{$col} — rows_with_value={$colDiag['rows_with_value']}, paths_added={$colDiag['paths_added']}");
                $tableDiag['columns'][$col] = $colDiag;
            }

            $diag['tables'][$table] = $tableDiag;
        }

        $mainCount  = count(array_filter($manifest, fn($b) => $b === 'main'));
        $thumbCount = count(array_filter($manifest, fn($b) => $b === 'thumbs'));

        $this->logger->info(sprintf(
            'ImageManifestService: manifest built with %d unique paths (%d main, %d thumbs)',
            count($manifest),
            $mainCount,
            $thumbCount
        ));

        $diag['manifest_size'] = count($manifest);
        $diag['main_count']    = $mainCount;
        $diag['thumbs_count']  = $thumbCount;
        $this->lastDiagnostics = $diag;

        return $manifest;
    }

    public function getLastDiagnostics(): array
    {
        return $this->lastDiagnostics;
    }

    /**
     * Copy every file in the manifest from the source filesystem into the target,
     * bucketed into `images/shop/main/` (thumbs are regenerated by Rebuild Product Images).
     *
     * @param array  $manifest   path => bucket ('main'|'thumbs')
     * @param string $sourceRoot absolute path to source site root (so $sourceRoot/$path resolves)
     *
     * @return array{copied:int, skipped:int, missing:int, missing_paths:array<int,string>, bytes:int, main:int, thumbs:int, thumbs_skipped:int}
     */
    public function copyByManifest(array $manifest, string $sourceRoot): array
    {
        $sourceRoot = rtrim(str_replace('\\', '/', $sourceRoot), '/');
        $target     = rtrim(str_replace('\\', '/', JPATH_ROOT), '/') . '/images';
        $targetMain = $target . '/shop/main';

        // Only create the main/ bucket. Thumbnails get regenerated by the Rebuild Product
        // Images step from the main originals, so copying source thumbs would be wasted
        // disk I/O — they'd be overwritten on first rebuild.
        if (!is_dir($targetMain) && !@mkdir($targetMain, 0755, true) && !is_dir($targetMain)) {
            return [
                'copied' => 0, 'skipped' => 0, 'missing' => 0, 'missing_paths' => [],
                'bytes' => 0, 'main' => 0, 'thumbs' => 0, 'thumbs_skipped' => 0,
                'error' => 'Target directory could not be created: ' . $targetMain,
            ];
        }

        $copied = $skipped = $missing = $bytes = 0;
        $mainCount     = 0;
        $thumbsSkipped = 0;
        $missingPaths  = [];

        foreach ($manifest as $relPath => $bucket) {
            if ($bucket === 'thumbs') {
                // Staged for regeneration by Rebuild Product Images.
                $thumbsSkipped++;
                continue;
            }

            $sourceFile = $sourceRoot . '/' . $relPath;

            if (!is_file($sourceFile)) {
                $missing++;
                if (count($missingPaths) < 200) {
                    $missingPaths[] = $relPath;
                }
                continue;
            }

            // Derive the path INSIDE the shop/main bucket. Strip the common Joomla
            // prefixes that don't carry meaning for the target layout:
            //   images/j2store/products/foo.jpg                         → products/foo.jpg
            //   images/catalog/foo.jpg                                  → catalog/foo.jpg
            //   media/plg_sampledata_j2store/images/electronics/1.svg   → electronics/1.svg
            //   media/com_j2store/products/abc.jpg                      → products/abc.jpg
            $rel  = $this->stripCommonPrefixes($relPath);
            $dest = $targetMain . '/' . $rel;

            if (is_file($dest) && filesize($dest) === filesize($sourceFile) && filemtime($dest) >= filemtime($sourceFile)) {
                $skipped++;
                continue;
            }

            $destDir = \dirname($dest);
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }

            if (@copy($sourceFile, $dest)) {
                $copied++;
                $mainCount++;
                $bytes += (int) filesize($sourceFile);
            } else {
                $this->logger->warning("ImageManifestService: copy failed for {$sourceFile}");
            }
        }

        $this->logger->info("ImageManifestService: done. copied={$copied}, skipped={$skipped}, thumbs_skipped={$thumbsSkipped}, missing={$missing}, bytes={$bytes}");

        return [
            'copied'         => $copied,
            'skipped'        => $skipped,
            'missing'        => $missing,
            'missing_paths'  => $missingPaths,
            'bytes'          => $bytes,
            'main'           => $mainCount,
            'thumbs'         => 0,
            'thumbs_skipped' => $thumbsSkipped,
        ];
    }

    /**
     * Rewrite image-path columns in the TARGET j2commerce tables so they point at the
     * post-copy locations under `images/shop/main/`. Called after copyByManifest so
     * the Rebuild Product Images scanner can find the files on disk.
     *
     * Returns the number of rows updated per column, e.g.
     *   ['productimages_main' => 15, 'productimages_thumb' => 15, 'productimages_additional' => 0, 'optionvalues' => 0]
     */
    public function rewriteTargetImagePaths(array $manifest, \Joomla\Database\DatabaseInterface $targetDb): array
    {
        $updates = [
            'productimages_main'       => 0,
            'productimages_thumb'      => 0,
            'productimages_additional' => 0,
            'optionvalues'             => 0,
        ];

        if (empty($manifest)) {
            return $updates;
        }

        // Build a rewrite map: source-DB path → post-copy target path.
        $rewriteMap = [];
        foreach ($manifest as $srcPath => $bucket) {
            $sub = $this->stripCommonPrefixes($srcPath);
            $rewriteMap[$srcPath] = 'images/shop/main/' . $sub;
        }

        // Simple path columns — exact match + LIKE for the #joomlaImage://... suffix case.
        foreach ($rewriteMap as $old => $new) {
            $updates['productimages_main']  += $this->updatePathColumn($targetDb, '#__j2commerce_productimages', 'main_image', $old, $new);
            $updates['productimages_thumb'] += $this->updatePathColumn($targetDb, '#__j2commerce_productimages', 'thumb_image', $old, $new);
            $updates['optionvalues']        += $this->updatePathColumn($targetDb, '#__j2commerce_optionvalues', 'optionvalue_image', $old, $new);
        }

        // JSON column — decode, remap, re-encode. Only rows that actually contain at least
        // one remapped path get written back.
        $updates['productimages_additional'] = $this->rewriteJsonColumn(
            $targetDb,
            '#__j2commerce_productimages',
            'j2store_productimage_id',
            'additional_images',
            $rewriteMap
        );

        $this->logger->info(sprintf(
            'ImageManifestService: DB path rewrite — pi.main=%d, pi.thumb=%d, pi.additional=%d, ov=%d',
            $updates['productimages_main'],
            $updates['productimages_thumb'],
            $updates['productimages_additional'],
            $updates['optionvalues']
        ));

        return $updates;
    }

    private function updatePathColumn(\Joomla\Database\DatabaseInterface $db, string $table, string $col, string $old, string $new): int
    {
        try {
            // Exact match (most common — no Joomla metadata suffix).
            $sqlExact = 'UPDATE ' . $db->quoteName($table)
                . ' SET ' . $db->quoteName($col) . ' = ' . $db->quote($new)
                . ' WHERE ' . $db->quoteName($col) . ' = ' . $db->quote($old);
            $db->setQuery($sqlExact)->execute();
            $n = (int) $db->getAffectedRows();

            // Plus any "{old}#joomlaImage://..." suffixed rows — preserve the suffix via
            // REPLACE() so per-image alt/crop metadata survives the rewrite.
            $sqlPrefix = 'UPDATE ' . $db->quoteName($table)
                . ' SET ' . $db->quoteName($col) . ' = REPLACE(' . $db->quoteName($col) . ', '
                . $db->quote($old . '#') . ', ' . $db->quote($new . '#') . ')'
                . ' WHERE ' . $db->quoteName($col) . ' LIKE ' . $db->quote($old . '#%');
            $db->setQuery($sqlPrefix)->execute();
            $n += (int) $db->getAffectedRows();

            return $n;
        } catch (\Throwable $e) {
            $this->logger->warning("updatePathColumn({$table}.{$col}) failed: " . $e->getMessage());
            return 0;
        }
    }

    private function rewriteJsonColumn(\Joomla\Database\DatabaseInterface $db, string $table, string $pkCol, string $col, array $rewriteMap): int
    {
        try {
            $query = $db->getQuery(true)
                ->select([$db->quoteName($pkCol), $db->quoteName($col)])
                ->from($db->quoteName($table))
                ->where($db->quoteName($col) . ' IS NOT NULL')
                ->where($db->quoteName($col) . " <> ''");

            $rows    = $db->setQuery($query)->loadAssocList() ?: [];
            $updated = 0;

            foreach ($rows as $row) {
                $raw   = (string) $row[$col];
                $paths = $this->extractJsonPaths($raw);
                if (empty($paths)) {
                    continue;
                }

                $decoded = json_decode($raw, true);
                $changed = false;

                if (is_array($decoded)) {
                    foreach ($decoded as $i => $entry) {
                        if (is_string($entry) && isset($rewriteMap[$entry])) {
                            $decoded[$i] = $rewriteMap[$entry];
                            $changed     = true;
                        } elseif (is_array($entry)) {
                            foreach (['file', 'image', 'main_image', 'path', 'src', 'url'] as $k) {
                                if (isset($entry[$k], $rewriteMap[$entry[$k]])) {
                                    $decoded[$i][$k] = $rewriteMap[$entry[$k]];
                                    $changed         = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($changed) {
                    $sql = 'UPDATE ' . $db->quoteName($table)
                        . ' SET ' . $db->quoteName($col) . ' = ' . $db->quote(json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))
                        . ' WHERE ' . $db->quoteName($pkCol) . ' = ' . (int) $row[$pkCol];
                    $db->setQuery($sql)->execute();
                    $updated++;
                }
            }

            return $updated;
        } catch (\Throwable $e) {
            $this->logger->warning("rewriteJsonColumn({$table}.{$col}) failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Strip common Joomla source prefixes so the target subpath inside shop/main/
     * ends up readable and short.
     */
    private function stripCommonPrefixes(string $path): string
    {
        $patterns = [
            '#^media/plg_sampledata_j2store/images/#i',
            '#^media/com_j2store/#i',
            '#^media/j2store/#i',
            '#^images/j2store/#i',
            '#^images/#i',
            '#^media/#i',
        ];

        foreach ($patterns as $pat) {
            $stripped = preg_replace($pat, '', $path);
            if ($stripped !== null && $stripped !== $path) {
                return $stripped;
            }
        }

        return $path;
    }

    /**
     * j2store's additional_images column can be either a JSON array of strings, a JSON
     * array of {file, alt} objects, or (very old installs) a comma/pipe-separated list.
     */
    private function extractJsonPaths(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '[]' || $raw === 'null') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $out = [];
            foreach ($decoded as $entry) {
                if (is_string($entry)) {
                    $out[] = $entry;
                } elseif (is_array($entry)) {
                    foreach (['file', 'image', 'main_image', 'path', 'src', 'url'] as $key) {
                        if (!empty($entry[$key]) && is_string($entry[$key])) {
                            $out[] = $entry[$key];
                            break;
                        }
                    }
                }
            }
            if (!empty($out)) {
                return $out;
            }
        }

        // Legacy comma/pipe-separated fallback.
        $parts = preg_split('/[|,\r\n]+/', $raw) ?: [];

        return array_filter(array_map('trim', $parts), fn($p) => $p !== '');
    }

    /**
     * Normalize a stored image reference into a clean relative path.
     * Rejects anything that would escape the images/ tree after normalization.
     */
    private function normalize(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if (preg_match('#^https?://[^/]+(/.*)$#i', $raw, $m)) {
            $raw = $m[1];
        }

        $raw = ltrim(str_replace('\\', '/', $raw), '/');

        if ($raw === '' || str_contains($raw, '..')) {
            return '';
        }

        return $raw;
    }
}
