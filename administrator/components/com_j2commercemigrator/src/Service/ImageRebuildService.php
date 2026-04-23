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

use J2Commerce\Component\J2commerce\Administrator\Helper\ImageProcessorHelper;
use J2Commerce\Component\J2commercemigrator\Administrator\Helper\MigrationLogger;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

class ImageRebuildService
{
    public function __construct(
        private DatabaseInterface $db,
        private MigrationLogger $logger
    ) {}

    public function listImageDirectories(string $root = 'images'): array
    {
        $root = $this->sanitizePath($root);

        if ($root === null) {
            return ['error' => 'Invalid directory path'];
        }

        $fullPath = JPATH_SITE . '/' . $root;

        if (!is_dir($fullPath)) {
            return ['error' => 'Directory does not exist'];
        }

        return [
            'success'     => true,
            'directories' => $this->buildDirectoryTree($fullPath, $root, 0),
        ];
    }

    public function createDirectory(string $parentDir, string $newDirName): array
    {
        $parentDir = $this->sanitizePath($parentDir);

        if ($parentDir === null) {
            return ['error' => 'Invalid parent directory'];
        }

        $safeName = File::makeSafe($newDirName);
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $safeName);

        if ($safeName === '' || $safeName !== $newDirName) {
            return ['error' => 'Invalid directory name. Use only letters, numbers, hyphens, and underscores.'];
        }

        $newPath  = $parentDir . '/' . $safeName;
        $fullPath = JPATH_SITE . '/' . $newPath;

        if (is_dir($fullPath)) {
            return ['error' => 'Directory already exists', 'path' => $newPath];
        }

        if (!Folder::create($fullPath)) {
            return ['error' => 'Failed to create directory'];
        }

        $this->logger->info("Created image directory: {$newPath}");

        return ['success' => true, 'path' => $newPath, 'message' => 'Directory created successfully'];
    }

    public function getImageSettings(): array
    {
        $params = ComponentHelper::getParams('com_j2commerce');

        return [
            'success'  => true,
            'settings' => [
                'image_enable_webp'        => (int) $params->get('image_enable_webp', 1),
                'image_webp_quality'       => (int) $params->get('image_webp_quality', 80),
                'image_max_dimension'      => (int) $params->get('image_max_dimension', 1200),
                'image_maintain_ratio'     => (int) $params->get('image_maintain_ratio', 1),
                'image_keep_original'      => (int) $params->get('image_keep_original', 0),
                'image_auto_thumbnail'     => (int) $params->get('image_auto_thumbnail', 1),
                'image_thumb_width'        => (int) $params->get('image_thumb_width', 300),
                'image_thumb_height'       => (int) $params->get('image_thumb_height', 300),
                'image_thumb_quality'      => (int) $params->get('image_thumb_quality', 80),
                'image_tiny_width'         => (int) $params->get('image_tiny_width', 100),
                'image_tiny_height'        => (int) $params->get('image_tiny_height', 100),
                'image_tiny_quality'       => (int) $params->get('image_tiny_quality', 80),
                'image_max_file_size'      => (int) $params->get('image_max_file_size', 10),
                'image_allowed_extensions' => $params->get('image_allowed_extensions', 'jpg,jpeg,png,gif,webp,avif'),
            ],
        ];
    }

    public function scanProducts(): array
    {
        $db = $this->db;

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id', 'category_id'),
                $db->quoteName('c.title', 'category_title'),
                $db->quoteName('c.path', 'category_path'),
                $db->quoteName('c.level'),
                $db->quoteName('c.parent_id'),
                $db->quoteName('c.lft'),
            ])
            ->select('COUNT(' . $db->quoteName('pi.product_id') . ') AS ' . $db->quoteName('product_count'))
            ->from($db->quoteName('#__j2commerce_productimages', 'pi'))
            ->join('INNER', $db->quoteName('#__j2commerce_products', 'p')
                . ' ON ' . $db->quoteName('p.j2commerce_product_id') . ' = ' . $db->quoteName('pi.product_id'))
            ->join('INNER', $db->quoteName('#__content', 'a')
                . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('p.product_source_id'))
            ->join('INNER', $db->quoteName('#__categories', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->where($db->quoteName('p.enabled') . ' = 1')
            ->where($db->quoteName('a.state') . ' = 1')
            ->group([
                $db->quoteName('c.id'),
                $db->quoteName('c.title'),
                $db->quoteName('c.path'),
                $db->quoteName('c.level'),
                $db->quoteName('c.parent_id'),
                $db->quoteName('c.lft'),
            ])
            ->order($db->quoteName('c.lft') . ' ASC');

        $categories   = $db->setQuery($query)->loadObjectList();
        $results      = [];
        $totalProducts = 0;
        $totalReady    = 0;

        foreach ($categories as $cat) {
            $categoryId = (int) $cat->category_id;
            $stats      = $this->analyzeCategory($categoryId);

            $results[] = [
                'id'            => $categoryId,
                'title'         => $cat->category_title,
                'path'          => $cat->category_path ?? '',
                'level'         => (int) ($cat->level ?? 0),
                'parent_id'     => (int) ($cat->parent_id ?? 1),
                'product_count' => (int) $cat->product_count,
                'stats'         => $stats,
            ];

            $totalProducts += (int) $cat->product_count;
            $totalReady    += $stats['ready_for_rebuild'];
        }

        $orphanStats = $this->analyzeOrphanedImages();

        if ($orphanStats['product_count'] > 0) {
            $results[] = [
                'id'            => 0,
                'title'         => 'Orphaned Product Images',
                'path'          => '',
                'level'         => 0,
                'parent_id'     => 0,
                'product_count' => $orphanStats['product_count'],
                'stats'         => $orphanStats['stats'],
                'orphaned'      => true,
            ];

            $totalProducts += $orphanStats['product_count'];
            $totalReady    += $orphanStats['stats']['ready_for_rebuild'];
        }

        return [
            'success'        => true,
            'categories'     => $results,
            'total_products' => $totalProducts,
            'total_ready'    => $totalReady,
        ];
    }

    private function analyzeOrphanedImages(): array
    {
        $db = $this->db;

        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('pi.product_id') . ') AS cnt')
            ->from($db->quoteName('#__j2commerce_productimages', 'pi'))
            ->join('INNER', $db->quoteName('#__j2commerce_products', 'p')
                . ' ON ' . $db->quoteName('p.j2commerce_product_id') . ' = ' . $db->quoteName('pi.product_id'))
            ->join('INNER', $db->quoteName('#__content', 'a')
                . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('p.product_source_id'))
            ->join('LEFT', $db->quoteName('#__categories', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->where($db->quoteName('p.enabled') . ' = 1')
            ->where($db->quoteName('a.state') . ' = 1')
            ->where('(' . $db->quoteName('c.id') . ' IS NULL OR ' . $db->quoteName('c.published') . ' != 1)');

        $count = (int) $db->setQuery($query)->loadResult();

        if ($count === 0) {
            return ['product_count' => 0, 'stats' => []];
        }

        return ['product_count' => $count, 'stats' => $this->analyzeCategory(0)];
    }

    public function analyzeCategory(int $categoryId): array
    {
        $db = $this->db;

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pi.product_id'),
                $db->quoteName('pi.main_image'),
                $db->quoteName('pi.thumb_image'),
                $db->quoteName('pi.tiny_image'),
                $db->quoteName('pi.additional_images'),
            ])
            ->from($db->quoteName('#__j2commerce_productimages', 'pi'))
            ->join('INNER', $db->quoteName('#__j2commerce_products', 'p')
                . ' ON ' . $db->quoteName('p.j2commerce_product_id') . ' = ' . $db->quoteName('pi.product_id'))
            ->join('INNER', $db->quoteName('#__content', 'a')
                . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('p.product_source_id'))
            ->where($db->quoteName('p.enabled') . ' = 1')
            ->where($db->quoteName('a.state') . ' = 1');

        if ($categoryId > 0) {
            $query->join('INNER', $db->quoteName('#__categories', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
                ->where($db->quoteName('c.id') . ' = :catId')
                ->bind(':catId', $categoryId, ParameterType::INTEGER);
        } else {
            $query->join('LEFT', $db->quoteName('#__categories', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
                ->where('(' . $db->quoteName('c.id') . ' IS NULL OR ' . $db->quoteName('c.published') . ' != 1)');
        }

        $rows = $db->setQuery($query)->loadObjectList();

        $stats = [
            'with_main_image'   => 0,
            'thumb_fallback'    => 0,
            'verified'          => 0,
            'already_optimized' => 0,
            'ready_for_rebuild' => 0,
            'remote_images'     => 0,
        ];

        foreach ($rows as $row) {
            $mainImage  = $this->stripJoomlaMetadata($row->main_image ?? '');
            $thumbImage = $this->stripJoomlaMetadata($row->thumb_image ?? '');
            $tinyImage  = $this->stripJoomlaMetadata($row->tiny_image ?? '');

            $hasMain  = $mainImage !== '';
            $hasThumb = $thumbImage !== '';

            if ($hasMain) {
                $stats['with_main_image']++;
            } elseif ($hasThumb) {
                $stats['thumb_fallback']++;
            } else {
                continue;
            }

            $sourcePath = $hasMain ? $mainImage : $thumbImage;
            $isRemote   = $this->isRemoteImage($sourcePath);

            if ($isRemote) {
                $stats['remote_images']++;
                $stats['verified']++;
            } elseif (file_exists(JPATH_SITE . '/' . $sourcePath)) {
                $stats['verified']++;
            } else {
                continue;
            }

            if ($this->hasOptimizedStructure($thumbImage, $tinyImage)) {
                $stats['already_optimized']++;
            } else {
                $stats['ready_for_rebuild']++;
            }
        }

        return $stats;
    }

    public function rebuildBatch(
        int $categoryId,
        string $baseDir,
        int $offset = 0,
        int $batchSize = 10
    ): array {
        $baseDir = $this->sanitizePath($baseDir);

        if ($baseDir === null) {
            return ['error' => 'Invalid base directory'];
        }

        // GD required only for raster images — SVGs bypass GD entirely.
        $gdLoaded = extension_loaded('gd');

        $params    = ComponentHelper::getParams('com_j2commerce');
        $processor = new ImageProcessorHelper(
            webpQuality: (int) $params->get('image_webp_quality', 80),
            thumbQuality: (int) $params->get('image_thumb_quality', 80)
        );

        $products = $this->getProductsForCategory($categoryId, $batchSize, $offset);
        $total    = $this->getProductCountForCategory($categoryId);

        if (empty($products)) {
            return [
                'done'      => true,
                'offset'    => $offset,
                'processed' => 0,
                'skipped'   => 0,
                'errors'    => 0,
                'total'     => $total,
                'message'   => 'No more products to process',
            ];
        }

        $catPath = $this->getCategoryPath($categoryId);

        if ($catPath === '') {
            $catPath = 'orphaned';
        }

        $outputDir     = $baseDir . '/' . $catPath;
        $thumbDir      = $outputDir . '/thumbs';
        $tinyDir       = $outputDir . '/tiny';
        $fullOutputDir = JPATH_SITE . '/' . $outputDir;
        $fullThumbDir  = JPATH_SITE . '/' . $thumbDir;
        $fullTinyDir   = JPATH_SITE . '/' . $tinyDir;

        foreach ([$fullOutputDir, $fullThumbDir, $fullTinyDir] as $dir) {
            if (!is_dir($dir) && !Folder::create($dir)) {
                return ['error' => 'Failed to create directory: ' . $dir];
            }
        }

        $processed        = 0;
        $skipped          = 0;
        $alreadyOptimized = 0;
        $errors           = 0;
        $skipDetails      = [];

        $maxDimension  = (int) $params->get('image_max_dimension', 1200);
        $maintainRatio = (bool) $params->get('image_maintain_ratio', 1);
        $enableWebP    = (bool) $params->get('image_enable_webp', 1);
        $thumbWidth    = (int) $params->get('image_thumb_width', 300);
        $thumbHeight   = (int) $params->get('image_thumb_height', 300);
        $tinyWidth     = (int) $params->get('image_tiny_width', 100);
        $tinyHeight    = (int) $params->get('image_tiny_height', 100);

        foreach ($products as $row) {
            $productId  = (int) $row->product_id;
            $mainImage  = $this->stripJoomlaMetadata($row->main_image ?? '');
            $thumbImage = $this->stripJoomlaMetadata($row->thumb_image ?? '');
            $sourcePath = $mainImage !== '' ? $mainImage : $thumbImage;

            if ($sourcePath === '') {
                $skipped++;
                $skipDetails[] = [
                    'product_id' => $productId,
                    'reason'     => 'no_source_image',
                    'source'     => '',
                    'message'    => 'No main_image or thumb_image set',
                ];
                continue;
            }

            $isRemote = $this->isRemoteImage($sourcePath);
            $filename = $this->buildOutputFilename($sourcePath);

            if ($this->isAlreadyProcessed($fullOutputDir, $fullThumbDir, $fullTinyDir, $filename)) {
                $alreadyOptimized++;
                continue;
            }

            $localSource = $isRemote
                ? $this->downloadRemoteImage($sourcePath)
                : JPATH_SITE . '/' . $sourcePath;

            if ($localSource === null || !file_exists($localSource)) {
                $errors++;
                $skipDetails[] = [
                    'product_id' => $productId,
                    'reason'     => 'source_not_found',
                    'source'     => $sourcePath,
                    'message'    => $isRemote ? 'Remote image download failed' : 'Source file not found on disk',
                ];
                $this->logger->warning("Image not found: {$sourcePath} (product_id={$productId})");
                continue;
            }

            // SVG/SVGZ — copy as-is into main/thumbs/tiny; GD not needed.
            if ($this->isSvg($sourcePath) || $this->isSvg($localSource)) {
                $mainTarget  = $fullOutputDir . '/' . $filename;
                $thumbTarget = $fullThumbDir . '/' . $filename;
                $tinyTarget  = $fullTinyDir . '/' . $filename;

                $ok = @copy($localSource, $mainTarget)
                   && @copy($localSource, $thumbTarget)
                   && @copy($localSource, $tinyTarget);

                if (!$ok) {
                    $errors++;
                    $skipDetails[] = [
                        'product_id' => $productId,
                        'reason'     => 'svg_copy_failed',
                        'source'     => $sourcePath,
                        'message'    => 'Could not copy SVG into main/thumbs/tiny target folders',
                    ];
                    $this->logger->warning("SVG copy failed for {$sourcePath}");
                    continue;
                }

                $processed++;
                continue;
            }

            if (!$gdLoaded) {
                $errors++;
                $skipDetails[] = [
                    'product_id' => $productId,
                    'reason'     => 'gd_missing',
                    'source'     => $sourcePath,
                    'message'    => 'PHP GD extension is required to process this raster image but is not loaded',
                ];
                continue;
            }

            try {
                $mainTarget  = $fullOutputDir . '/' . $filename;
                $thumbTarget = $fullThumbDir . '/' . $filename;
                $tinyTarget  = $fullTinyDir . '/' . $filename;

                if (!$isRemote) {
                    $mainData = $processor->processMainImage($localSource, $maxDimension, $maintainRatio);

                    if ($mainData === false) {
                        $errors++;
                        $skipDetails[] = [
                            'product_id' => $productId,
                            'reason'     => 'processing_failed',
                            'source'     => $sourcePath,
                            'message'    => 'GD failed to process main image (corrupt or unsupported format)',
                        ];
                        $this->logger->warning("Failed to process main image: {$sourcePath}");
                        continue;
                    }

                    file_put_contents($mainTarget, $mainData);
                }

                $thumbnailSource = (!$isRemote && file_exists($mainTarget)) ? $mainTarget : $localSource;

                if (!$processor->createThumbnail($thumbnailSource, $thumbTarget, $thumbWidth, $thumbHeight)) {
                    $errors++;
                    $skipDetails[] = [
                        'product_id' => $productId,
                        'reason'     => 'thumbnail_failed',
                        'source'     => $sourcePath,
                        'message'    => 'Failed to create thumbnail image',
                    ];
                    $this->logger->warning("Failed to create thumbnail: {$sourcePath}");
                    continue;
                }

                if (!$processor->createThumbnail($thumbnailSource, $tinyTarget, $tinyWidth, $tinyHeight)) {
                    $errors++;
                    $skipDetails[] = [
                        'product_id' => $productId,
                        'reason'     => 'tiny_failed',
                        'source'     => $sourcePath,
                        'message'    => 'Failed to create tiny image',
                    ];
                    $this->logger->warning("Failed to create tiny image: {$sourcePath}");
                    continue;
                }

                $this->processAdditionalImages(
                    $row,
                    $processor,
                    $fullOutputDir,
                    $fullThumbDir,
                    $fullTinyDir,
                    $maxDimension,
                    $maintainRatio,
                    $thumbWidth,
                    $thumbHeight,
                    $tinyWidth,
                    $tinyHeight
                );

                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                $skipDetails[] = [
                    'product_id' => $productId,
                    'reason'     => 'exception',
                    'source'     => $sourcePath,
                    'message'    => $e->getMessage(),
                ];
                $this->logger->error("Image rebuild error (product_id={$productId}): " . $e->getMessage());
            } finally {
                if ($isRemote && $localSource !== null && file_exists($localSource)) {
                    @unlink($localSource);
                }
            }
        }

        gc_collect_cycles();

        $newOffset  = $offset + $batchSize;
        $totalDone  = $offset + $processed + $skipped + $alreadyOptimized + $errors;
        $done       = \count($products) < $batchSize;
        $catTitle   = $this->getCategoryTitle($categoryId);

        return [
            'done'             => $done,
            'offset'           => $newOffset,
            'processed'        => $processed,
            'alreadyOptimized' => $alreadyOptimized,
            'skipped'          => $skipped,
            'errors'           => $errors,
            'skipDetails'      => $skipDetails,
            'total'            => $total,
            'message'          => $done
                ? "All product image sets created for {$catTitle}"
                : "Processed {$totalDone} of {$total} products in {$catTitle}",
        ];
    }

    public function updateImagePaths(int $categoryId, string $baseDir): array
    {
        $baseDir = $this->sanitizePath($baseDir);

        if ($baseDir === null) {
            return ['error' => 'Invalid base directory'];
        }

        $catPath = $this->getCategoryPath($categoryId);

        if ($catPath === '') {
            $catPath = 'orphaned';
        }

        $outputDir = $baseDir . '/' . $catPath;
        $thumbDir  = $outputDir . '/thumbs';
        $tinyDir   = $outputDir . '/tiny';

        $products = $this->getProductsForCategory($categoryId, 0, 0);
        $db       = $this->db;

        $updated = 0;
        $skipped = 0;

        foreach ($products as $row) {
            $mainImage  = $this->stripJoomlaMetadata($row->main_image ?? '');
            $thumbImage = $this->stripJoomlaMetadata($row->thumb_image ?? '');
            $sourcePath = $mainImage !== '' ? $mainImage : $thumbImage;

            if ($sourcePath === '') {
                $skipped++;
                continue;
            }

            $isRemote = $this->isRemoteImage($sourcePath);
            $filename = $this->buildOutputFilename($sourcePath);

            $fullThumb = JPATH_SITE . '/' . $thumbDir . '/' . $filename;
            $fullTiny  = JPATH_SITE . '/' . $tinyDir . '/' . $filename;

            if (!file_exists($fullThumb) || !file_exists($fullTiny)) {
                $skipped++;
                continue;
            }

            $newMainPath  = $isRemote ? ($row->main_image ?? '') : $this->buildImagePathWithMetadata($outputDir . '/' . $filename);
            $newThumbPath = $this->buildImagePathWithMetadata($thumbDir . '/' . $filename);
            $newTinyPath  = $tinyDir . '/' . $filename;

            $newAdditionalJson      = $this->buildUpdatedAdditionalImages($row, $outputDir);
            $newAdditionalThumbJson = $this->buildUpdatedAdditionalThumbs($row, $thumbDir);
            $newAdditionalTinyJson  = $this->buildUpdatedAdditionalTiny($row, $tinyDir);

            $productId = (int) $row->product_id;

            $update = $db->getQuery(true)
                ->update($db->quoteName('#__j2commerce_productimages'))
                ->set($db->quoteName('main_image') . ' = :main')
                ->set($db->quoteName('thumb_image') . ' = :thumb')
                ->set($db->quoteName('tiny_image') . ' = :tiny')
                ->set($db->quoteName('additional_images') . ' = :addl')
                ->set($db->quoteName('additional_thumb_images') . ' = :addlThumb')
                ->set($db->quoteName('additional_tiny_images') . ' = :addlTiny')
                ->where($db->quoteName('product_id') . ' = :pid')
                ->bind(':main', $newMainPath)
                ->bind(':thumb', $newThumbPath)
                ->bind(':tiny', $newTinyPath)
                ->bind(':addl', $newAdditionalJson)
                ->bind(':addlThumb', $newAdditionalThumbJson)
                ->bind(':addlTiny', $newAdditionalTinyJson)
                ->bind(':pid', $productId, ParameterType::INTEGER);

            $db->setQuery($update)->execute();
            $updated++;
        }

        $catTitle = $this->getCategoryTitle($categoryId);
        $this->logger->info("Updated {$updated} product image records in {$catTitle}");

        return [
            'success' => true,
            'updated' => $updated,
            'skipped' => $skipped,
            'message' => $updated > 0
                ? "{$updated} image sets have been created and updated in the database for {$catTitle}"
                : "No image records updated in {$catTitle}" . ($skipped > 0 ? " ({$skipped} skipped — rebuilt files not found)" : ''),
        ];
    }

    // ── Private helpers ──

    private function sanitizePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        $path = rtrim($path, '/');

        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            return null;
        }

        if (!str_starts_with($path, 'images')) {
            return null;
        }

        return $path;
    }

    private function buildDirectoryTree(string $fullPath, string $relativePath, int $level): array
    {
        $result = [
            'path'     => $relativePath,
            'name'     => basename($relativePath),
            'level'    => $level,
            'children' => [],
        ];

        $entries = @scandir($fullPath);

        if ($entries === false) {
            return $result;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }

            $childFull     = $fullPath . '/' . $entry;
            $childRelative = $relativePath . '/' . $entry;

            if (is_dir($childFull) && $level < 5) {
                $result['children'][] = $this->buildDirectoryTree($childFull, $childRelative, $level + 1);
            }
        }

        return $result;
    }

    private function stripJoomlaMetadata(string $imagePath): string
    {
        if ($imagePath === '') {
            return '';
        }

        $hashPos = strpos($imagePath, '#joomlaImage://');

        if ($hashPos !== false) {
            $imagePath = substr($imagePath, 0, $hashPos);
        }

        return trim($imagePath);
    }

    private function isRemoteImage(string $imagePath): bool
    {
        return str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://');
    }

    private function isSvg(string $imagePath): bool
    {
        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        return $ext === 'svg' || $ext === 'svgz';
    }

    private function hasOptimizedStructure(string $thumbImage, string $tinyImage): bool
    {
        return $thumbImage !== '' && str_contains($thumbImage, '/thumbs/')
            && $tinyImage !== '' && str_contains($tinyImage, '/tiny/');
    }

    private function buildOutputFilename(string $sourcePath): string
    {
        $info     = pathinfo($this->isRemoteImage($sourcePath) ? parse_url($sourcePath, PHP_URL_PATH) ?? 'image.jpg' : $sourcePath);
        $basename = File::makeSafe($info['filename'] ?? 'image');
        $ext      = strtolower($info['extension'] ?? '');

        if (\in_array($ext, ['svg', 'svgz'], true)) {
            return $basename . '.' . $ext;
        }

        return $basename . '.webp';
    }

    private function isAlreadyProcessed(string $outputDir, string $thumbDir, string $tinyDir, string $filename): bool
    {
        return file_exists($thumbDir . '/' . $filename) && file_exists($tinyDir . '/' . $filename);
    }

    private function downloadRemoteImage(string $url): ?string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'j2img_');

        if ($tmpFile === false) {
            return null;
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout'         => 30,
                'max_redirects'   => 3,
                'ignore_errors'   => true,
                'follow_location' => true,
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $data = @file_get_contents($url, false, $ctx);

        if ($data === false || \strlen($data) < 100 || \strlen($data) > 50 * 1024 * 1024) {
            @unlink($tmpFile);
            return null;
        }

        file_put_contents($tmpFile, $data);

        return $tmpFile;
    }

    private function buildImagePathWithMetadata(string $relativePath): string
    {
        $fullPath = JPATH_SITE . '/' . $relativePath;

        if (!file_exists($fullPath)) {
            return $relativePath;
        }

        $dims = @getimagesize($fullPath);

        if ($dims === false) {
            return $relativePath;
        }

        [$width, $height] = $dims;

        if ($width <= 0 || $height <= 0) {
            return $relativePath;
        }

        return $relativePath . '#joomlaImage://local-images/'
            . ltrim($relativePath, '/') . '?width=' . $width . '&height=' . $height;
    }

    private function getProductsForCategory(int $categoryId, int $limit = 0, int $offset = 0): array
    {
        $db    = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName('pi') . '.*')
            ->from($db->quoteName('#__j2commerce_productimages', 'pi'))
            ->join('INNER', $db->quoteName('#__j2commerce_products', 'p')
                . ' ON ' . $db->quoteName('p.j2commerce_product_id') . ' = ' . $db->quoteName('pi.product_id'))
            ->join('INNER', $db->quoteName('#__content', 'a')
                . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('p.product_source_id'))
            ->where($db->quoteName('p.enabled') . ' = 1')
            ->where($db->quoteName('a.state') . ' = 1')
            ->order($db->quoteName('pi.product_id') . ' ASC');

        if ($categoryId > 0) {
            $query->join('INNER', $db->quoteName('#__categories', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
                ->where($db->quoteName('c.id') . ' = :catId')
                ->bind(':catId', $categoryId, ParameterType::INTEGER);
        } else {
            $query->join('LEFT', $db->quoteName('#__categories', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
                ->where('(' . $db->quoteName('c.id') . ' IS NULL OR ' . $db->quoteName('c.published') . ' != 1)');
        }

        if ($limit > 0) {
            $query->setLimit($limit, $offset);
        }

        return $db->setQuery($query)->loadObjectList();
    }

    private function getProductCountForCategory(int $categoryId): int
    {
        $db    = $this->db;
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__j2commerce_productimages', 'pi'))
            ->join('INNER', $db->quoteName('#__j2commerce_products', 'p')
                . ' ON ' . $db->quoteName('p.j2commerce_product_id') . ' = ' . $db->quoteName('pi.product_id'))
            ->join('INNER', $db->quoteName('#__content', 'a')
                . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('p.product_source_id'))
            ->where($db->quoteName('p.enabled') . ' = 1')
            ->where($db->quoteName('a.state') . ' = 1');

        if ($categoryId > 0) {
            $query->join('INNER', $db->quoteName('#__categories', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
                ->where($db->quoteName('c.id') . ' = :catId')
                ->bind(':catId', $categoryId, ParameterType::INTEGER);
        } else {
            $query->join('LEFT', $db->quoteName('#__categories', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
                ->where('(' . $db->quoteName('c.id') . ' IS NULL OR ' . $db->quoteName('c.published') . ' != 1)');
        }

        return (int) $db->setQuery($query)->loadResult();
    }

    private function getCategoryPath(int $categoryId): string
    {
        if ($categoryId <= 1) {
            return '';
        }

        $db    = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName('path'))
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $categoryId, ParameterType::INTEGER);

        return (string) ($db->setQuery($query)->loadResult() ?? '');
    }

    private function getCategoryTitle(int $categoryId): string
    {
        if ($categoryId <= 1) {
            return 'Orphaned Product Images';
        }

        $db    = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $categoryId, ParameterType::INTEGER);

        return (string) ($db->setQuery($query)->loadResult() ?? 'Unknown');
    }

    private function processAdditionalImages(
        object $row,
        ImageProcessorHelper $processor,
        string $outputDir,
        string $thumbDir,
        string $tinyDir,
        int $maxDimension,
        bool $maintainRatio,
        int $thumbWidth,
        int $thumbHeight,
        int $tinyWidth,
        int $tinyHeight
    ): void {
        $additional = $row->additional_images ?? '';

        if ($additional === '' || $additional === null) {
            return;
        }

        $images = json_decode($additional, true);

        if (!\is_array($images) || empty($images)) {
            return;
        }

        foreach ($images as $imgPath) {
            $cleanPath = $this->stripJoomlaMetadata((string) $imgPath);

            if ($cleanPath === '') {
                continue;
            }

            $isRemote = $this->isRemoteImage($cleanPath);
            $filename = $this->buildOutputFilename($cleanPath);

            if ($this->isAlreadyProcessed($outputDir, $thumbDir, $tinyDir, $filename)) {
                continue;
            }

            $localSource = $isRemote
                ? $this->downloadRemoteImage($cleanPath)
                : JPATH_SITE . '/' . $cleanPath;

            if ($localSource === null || !file_exists($localSource)) {
                continue;
            }

            // SVG bypass — GD can't rasterize them and they scale cleanly.
            if ($this->isSvg($cleanPath) || $this->isSvg($localSource)) {
                @copy($localSource, $outputDir . '/' . $filename);
                @copy($localSource, $thumbDir . '/' . $filename);
                @copy($localSource, $tinyDir . '/' . $filename);
                if ($isRemote) {
                    @unlink($localSource);
                }
                continue;
            }

            try {
                if (!$isRemote) {
                    $mainData = $processor->processMainImage($localSource, $maxDimension, $maintainRatio);

                    if ($mainData !== false) {
                        file_put_contents($outputDir . '/' . $filename, $mainData);
                    }
                }

                $thumbSource = (!$isRemote && file_exists($outputDir . '/' . $filename))
                    ? $outputDir . '/' . $filename
                    : $localSource;

                $processor->createThumbnail($thumbSource, $thumbDir . '/' . $filename, $thumbWidth, $thumbHeight);
                $processor->createThumbnail($thumbSource, $tinyDir . '/' . $filename, $tinyWidth, $tinyHeight);
            } catch (\Throwable $e) {
                $this->logger->warning("Additional image error: {$cleanPath} — " . $e->getMessage());
            } finally {
                if ($isRemote && $localSource !== null && file_exists($localSource)) {
                    @unlink($localSource);
                }
            }
        }
    }

    private function buildUpdatedAdditionalImages(object $row, string $outputDir): string
    {
        return $this->rebuildAdditionalJsonPaths($row->additional_images ?? '', $outputDir, true);
    }

    private function buildUpdatedAdditionalThumbs(object $row, string $thumbDir): string
    {
        return $this->rebuildAdditionalJsonPaths($row->additional_images ?? '', $thumbDir, true);
    }

    private function buildUpdatedAdditionalTiny(object $row, string $tinyDir): string
    {
        return $this->rebuildAdditionalJsonPaths($row->additional_images ?? '', $tinyDir, false);
    }

    private function rebuildAdditionalJsonPaths(string $originalJson, string $targetDir, bool $withMetadata): string
    {
        if ($originalJson === '') {
            return '';
        }

        $images = json_decode($originalJson, true);

        if (!\is_array($images) || empty($images)) {
            return $originalJson;
        }

        $result = [];

        foreach ($images as $key => $imgPath) {
            $cleanPath = $this->stripJoomlaMetadata((string) $imgPath);

            if ($cleanPath === '') {
                $result[$key] = $imgPath;
                continue;
            }

            $filename = $this->buildOutputFilename($cleanPath);
            $newPath  = $targetDir . '/' . $filename;

            if (file_exists(JPATH_SITE . '/' . $newPath)) {
                $result[$key] = $withMetadata ? $this->buildImagePathWithMetadata($newPath) : $newPath;
            } else {
                $result[$key] = $imgPath;
            }
        }

        return json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    // ── Per-category rebuild log ──

    public function writeRebuildLog(
        int $categoryId,
        string $baseDir,
        int $totalProcessed,
        int $totalAlreadyOptimized,
        int $totalSkipped,
        int $totalErrors,
        array $allSkipDetails
    ): array {
        $catTitle = $this->getCategoryTitle($categoryId);
        $catSlug  = preg_replace('/[^a-z0-9_-]/', '_', strtolower($catTitle));
        $date     = date('Y-m-d_His');
        $filename = "image_rebuild_{$catSlug}_{$date}.log";
        $logDir   = JPATH_ADMINISTRATOR . '/logs';

        if (!is_dir($logDir)) {
            Folder::create($logDir);
        }

        $logPath = $logDir . '/' . $filename;
        $params  = ComponentHelper::getParams('com_j2commerce');

        $lines   = [];
        $lines[] = '============================================================';
        $lines[] = "Image Rebuild Log — {$catTitle} (category_id: {$categoryId})";
        $lines[] = 'Date: ' . date('Y-m-d H:i:s');
        $lines[] = "Base Directory: {$baseDir}";
        $lines[] = '============================================================';
        $lines[] = '';
        $lines[] = '--- Settings ---';
        $lines[] = 'Max dimension: ' . $params->get('image_max_dimension', 1200);
        $lines[] = 'WebP enabled:  ' . ($params->get('image_enable_webp', 1) ? 'Yes' : 'No');
        $lines[] = 'WebP quality:  ' . $params->get('image_webp_quality', 80);
        $lines[] = 'Thumb size:    ' . $params->get('image_thumb_width', 300) . 'x' . $params->get('image_thumb_height', 300);
        $lines[] = 'Tiny size:     ' . $params->get('image_tiny_width', 100) . 'x' . $params->get('image_tiny_height', 100);
        $lines[] = '';
        $lines[] = '--- Summary ---';
        $lines[] = "Newly created:     {$totalProcessed}";
        $lines[] = "Already optimized: {$totalAlreadyOptimized}";
        $lines[] = "Skipped:           {$totalSkipped}";
        $lines[] = "Errors:            {$totalErrors}";
        $lines[] = 'Total:             ' . ($totalProcessed + $totalAlreadyOptimized + $totalSkipped + $totalErrors);
        $lines[] = '';

        if (!empty($allSkipDetails)) {
            $grouped = [];

            foreach ($allSkipDetails as $detail) {
                $reason = $detail['reason'] ?? 'unknown';
                $grouped[$reason][] = $detail;
            }

            $lines[] = '--- Skipped / Error Details ---';
            $lines[] = '';

            $reasonLabels = [
                'no_source_image'   => 'No source image (main_image and thumb_image both empty)',
                'source_not_found'  => 'Source file not found on disk or remote download failed',
                'processing_failed' => 'GD failed to process image (corrupt or unsupported format)',
                'thumbnail_failed'  => 'Thumbnail creation failed',
                'tiny_failed'       => 'Tiny image creation failed',
                'exception'         => 'Unexpected error / exception',
            ];

            foreach ($grouped as $reason => $items) {
                $label   = $reasonLabels[$reason] ?? $reason;
                $lines[] = "[{$reason}] {$label} — " . \count($items) . ' product(s)';

                foreach ($items as $item) {
                    $pid    = $item['product_id'] ?? '?';
                    $source = $item['source'] ?? '';
                    $msg    = $item['message'] ?? '';
                    $lines[] = "  product_id={$pid}  source={$source}";

                    if ($msg !== '' && $msg !== $label) {
                        $lines[] = "    → {$msg}";
                    }
                }

                $lines[] = '';
            }
        }

        $lines[] = '============================================================';
        $lines[] = 'End of log';

        file_put_contents($logPath, implode("\n", $lines));

        return [
            'success'  => true,
            'filename' => $filename,
            'path'     => $logPath,
        ];
    }

    public function getLatestRebuildLog(int $categoryId): array
    {
        $catTitle = $this->getCategoryTitle($categoryId);
        $catSlug  = preg_replace('/[^a-z0-9_-]/', '_', strtolower($catTitle));
        $logDir   = JPATH_ADMINISTRATOR . '/logs';
        $pattern  = "image_rebuild_{$catSlug}_*.log";

        $files = glob($logDir . '/' . $pattern);

        if (empty($files)) {
            return ['error' => 'No log file found for this category'];
        }

        sort($files);
        $latest = end($files);

        return [
            'success'  => true,
            'filename' => basename($latest),
            'content'  => file_get_contents($latest),
        ];
    }

    // ── Persisted image folder — stored in com_j2commercemigrator component params ──

    public function saveImageFolder(string $folder): array
    {
        $folder = trim($folder);

        if ($folder !== '' && $this->sanitizePath($folder) === null) {
            return ['error' => 'Invalid folder path'];
        }

        $db    = $this->db;
        $query = $db->getQuery(true)
            ->select([$db->quoteName('extension_id'), $db->quoteName('params')])
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_j2commercemigrator'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $ext = $db->setQuery($query)->loadObject();

        if (!$ext) {
            return ['error' => 'com_j2commercemigrator not found in extensions table'];
        }

        $params = new Registry($ext->params);
        $params->set('image_base_dir', $folder);
        $newParams = $params->toString();
        $extId     = (int) $ext->extension_id;

        $update = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('extension_id') . ' = :eid')
            ->bind(':params', $newParams)
            ->bind(':eid', $extId, ParameterType::INTEGER);

        $db->setQuery($update)->execute();

        try {
            $cacheFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cacheFactory->createCacheController('', ['defaultgroup' => '_system'])->clean();
        } catch (\Throwable) {
            // Cache clear is best-effort
        }

        $this->logger->info("Saved image base directory: {$folder}");

        return ['success' => true, 'folder' => $folder];
    }

    public function getSavedImageFolder(): array
    {
        $db    = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_j2commercemigrator'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $paramsJson = (string) $db->setQuery($query)->loadResult();
        $params     = new Registry($paramsJson);

        return [
            'success' => true,
            'folder'  => $params->get('image_base_dir', ''),
        ];
    }

    // ── Delete legacy image directories ──

    public function deleteImageDirectories(array $folders): array
    {
        $savedFolder = $this->getSavedImageFolder()['folder'] ?? '';
        $deleted     = [];
        $failed      = [];

        foreach ($folders as $folder) {
            $folder = trim((string) $folder);
            $safe   = $this->sanitizePath($folder);

            if ($safe === null) {
                $failed[] = ['folder' => $folder, 'reason' => 'Invalid path'];
                continue;
            }

            if ($safe === $savedFolder) {
                $failed[] = ['folder' => $folder, 'reason' => 'Cannot delete the current store image folder'];
                continue;
            }

            $fullPath = JPATH_SITE . '/' . $safe;

            if (!is_dir($fullPath)) {
                $failed[] = ['folder' => $folder, 'reason' => 'Directory does not exist'];
                continue;
            }

            try {
                if (Folder::delete($fullPath)) {
                    $deleted[] = $safe;
                    $this->logger->info("Deleted image directory: {$safe}");
                } else {
                    $failed[] = ['folder' => $safe, 'reason' => 'Folder::delete() returned false'];
                }
            } catch (\Throwable $e) {
                $failed[] = ['folder' => $safe, 'reason' => $e->getMessage()];
                $this->logger->error("Failed to delete directory {$safe}: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'deleted' => $deleted,
            'failed'  => $failed,
        ];
    }

    // ── Optimize Images Feature ──

    public function scanOptimizeDirectory(string $directory): array
    {
        $directory = $this->sanitizePath($directory);

        if ($directory === null) {
            return ['error' => 'Invalid directory path'];
        }

        $fullPath = JPATH_SITE . '/' . $directory;

        if (!is_dir($fullPath)) {
            return ['error' => 'Directory does not exist: ' . $directory];
        }

        $extensions   = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $thumbDir     = $fullPath . '/thumbs';
        $tinyDir      = $fullPath . '/tiny';
        $total        = 0;
        $alreadyWebp  = 0;
        $needsConvert = 0;
        $hasThumbs    = 0;
        $hasTiny      = 0;
        $files        = [];

        $entries = @scandir($fullPath);

        if ($entries === false) {
            return ['error' => 'Cannot read directory: ' . $directory];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }

            $filePath = $fullPath . '/' . $entry;

            if (!is_file($filePath)) {
                continue;
            }

            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));

            if (!\in_array($ext, $extensions, true)) {
                continue;
            }

            $total++;
            $isWebp   = $ext === 'webp';
            $size     = @filesize($filePath) ?: 0;
            $basename = pathinfo($entry, PATHINFO_FILENAME);

            $thumbExists = file_exists($thumbDir . '/' . $basename . '.webp');
            $tinyExists  = file_exists($tinyDir . '/' . $basename . '.webp');

            if ($isWebp) {
                $alreadyWebp++;
            } else {
                $needsConvert++;
            }

            if ($thumbExists) {
                $hasThumbs++;
            }

            if ($tinyExists) {
                $hasTiny++;
            }

            $files[] = [
                'name'      => $entry,
                'path'      => $directory . '/' . $entry,
                'size'      => $size,
                'is_webp'   => $isWebp,
                'has_thumb' => $thumbExists,
                'has_tiny'  => $tinyExists,
            ];
        }

        return [
            'success'          => true,
            'directory'        => $directory,
            'total'            => $total,
            'already_webp'     => $alreadyWebp,
            'needs_conversion' => $needsConvert,
            'has_thumbs'       => $hasThumbs,
            'has_tiny'         => $hasTiny,
            'files'            => $files,
        ];
    }

    public function optimizeBatch(string $directory, int $offset, int $batchSize, array $dimensions): array
    {
        $directory = $this->sanitizePath($directory);

        if ($directory === null) {
            return ['error' => 'Invalid directory path'];
        }

        $fullPath = JPATH_SITE . '/' . $directory;

        if (!is_dir($fullPath)) {
            return ['error' => 'Directory does not exist: ' . $directory];
        }

        if (!extension_loaded('gd')) {
            return ['error' => 'GD library is required for image processing'];
        }

        $params    = ComponentHelper::getParams('com_j2commerce');
        $processor = new ImageProcessorHelper(
            webpQuality: (int) $params->get('image_webp_quality', 80),
            thumbQuality: (int) $params->get('image_thumb_quality', 80)
        );

        $thumbWidth  = (int) ($dimensions['thumb_width']  ?? 0) ?: (int) $params->get('image_thumb_width', 300);
        $thumbHeight = (int) ($dimensions['thumb_height'] ?? 0) ?: (int) $params->get('image_thumb_height', 300);
        $tinyWidth   = (int) ($dimensions['tiny_width']   ?? 0) ?: (int) $params->get('image_tiny_width', 100);
        $tinyHeight  = (int) ($dimensions['tiny_height']  ?? 0) ?: (int) $params->get('image_tiny_height', 100);

        $thumbDir     = $directory . '/thumbs';
        $tinyDir      = $directory . '/tiny';
        $fullThumbDir = JPATH_SITE . '/' . $thumbDir;
        $fullTinyDir  = JPATH_SITE . '/' . $tinyDir;

        foreach ([$fullThumbDir, $fullTinyDir] as $dir) {
            if (!is_dir($dir) && !Folder::create($dir)) {
                return ['error' => 'Failed to create directory: ' . $dir];
            }
        }

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $entries    = @scandir($fullPath);

        if ($entries === false) {
            return ['error' => 'Cannot read directory'];
        }

        $imageFiles = array_values(array_filter($entries, static function (string $entry) use ($fullPath, $extensions): bool {
            return $entry !== '.' && $entry !== '..'
                && !str_starts_with($entry, '.')
                && is_file($fullPath . '/' . $entry)
                && \in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $extensions, true);
        }));

        $total     = \count($imageFiles);
        $batch     = \array_slice($imageFiles, $offset, $batchSize);
        $processed = 0;
        $skipped   = 0;
        $errors    = 0;

        foreach ($batch as $filename) {
            $sourcePath  = $fullPath . '/' . $filename;
            $basename    = pathinfo($filename, PATHINFO_FILENAME);
            $outFilename = $basename . '.webp';
            $thumbDest   = $fullThumbDir . '/' . $outFilename;
            $tinyDest    = $fullTinyDir . '/' . $outFilename;

            if (file_exists($thumbDest) && file_exists($tinyDest)) {
                $skipped++;
                continue;
            }

            try {
                if (!file_exists($thumbDest) && !$processor->createThumbnail($sourcePath, $thumbDest, $thumbWidth, $thumbHeight)) {
                    $errors++;
                    $this->logger->error("Thumbnail failed for {$filename}");
                    continue;
                }

                if (!file_exists($tinyDest) && !$processor->createThumbnail($sourcePath, $tinyDest, $tinyWidth, $tinyHeight)) {
                    $errors++;
                    $this->logger->error("Tiny image failed for {$filename}");
                    continue;
                }

                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                $this->logger->error("Error processing {$filename}: " . $e->getMessage());
            }
        }

        $newOffset = $offset + \count($batch);
        $done      = $newOffset >= $total;

        return [
            'done'      => $done,
            'offset'    => $newOffset,
            'processed' => $processed,
            'skipped'   => $skipped,
            'errors'    => $errors,
            'total'     => $total,
            'message'   => $done
                ? "Optimization complete: {$processed} processed, {$skipped} skipped, {$errors} errors"
                : "Processed {$newOffset} of {$total} images",
        ];
    }

    public function scanImagePathTables(): array
    {
        $optionvaluesCount = 0;
        $tagsCount         = 0;

        try {
            $query = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__j2commerce_optionvalues'))
                ->where($this->db->quoteName('optionvalue_image') . ' LIKE ' . $this->db->quote('%.jpg%'));
            $optionvaluesCount = (int) $this->db->setQuery($query)->loadResult();
        } catch (\Throwable $e) {
            $this->logger->error('scanImagePathTables optionvalues: ' . $e->getMessage());
        }

        try {
            $query = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__tags'))
                ->where($this->db->quoteName('images') . ' LIKE ' . $this->db->quote('%.jpg%'));
            $tagsCount = (int) $this->db->setQuery($query)->loadResult();
        } catch (\Throwable $e) {
            $this->logger->error('scanImagePathTables tags: ' . $e->getMessage());
        }

        return [
            'success'      => true,
            'optionvalues' => ['count' => $optionvaluesCount],
            'tags'         => ['count' => $tagsCount],
        ];
    }

    public function updateImagePathTables(array $tables): array
    {
        $result = ['success' => true];

        if (\in_array('optionvalues', $tables, true)) {
            try {
                $query = 'UPDATE ' . $this->db->quoteName('#__j2commerce_optionvalues')
                    . ' SET ' . $this->db->quoteName('optionvalue_image')
                    . ' = REPLACE(' . $this->db->quoteName('optionvalue_image') . ', ' . $this->db->quote('.jpg') . ', ' . $this->db->quote('.webp') . ')'
                    . ' WHERE ' . $this->db->quoteName('optionvalue_image') . ' LIKE ' . $this->db->quote('%.jpg%');
                $this->db->setQuery($query)->execute();
                $result['optionvalues'] = ['updated' => $this->db->getAffectedRows()];
            } catch (\Throwable $e) {
                $this->logger->error('updateImagePathTables optionvalues: ' . $e->getMessage());
                $result['optionvalues'] = ['error' => $e->getMessage()];
            }
        }

        if (\in_array('tags', $tables, true)) {
            try {
                $query = 'UPDATE ' . $this->db->quoteName('#__tags')
                    . ' SET ' . $this->db->quoteName('images')
                    . ' = REPLACE(' . $this->db->quoteName('images') . ', ' . $this->db->quote('.jpg') . ', ' . $this->db->quote('.webp') . ')'
                    . ' WHERE ' . $this->db->quoteName('images') . ' LIKE ' . $this->db->quote('%.jpg%');
                $this->db->setQuery($query)->execute();
                $result['tags'] = ['updated' => $this->db->getAffectedRows()];
            } catch (\Throwable $e) {
                $this->logger->error('updateImagePathTables tags: ' . $e->getMessage());
                $result['tags'] = ['error' => $e->getMessage()];
            }
        }

        return $result;
    }
}
