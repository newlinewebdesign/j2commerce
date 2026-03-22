<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\Builder\Service;

use Joomla\Database\DatabaseInterface;

\defined('_JEXEC') or die;

final class BlockRenderService
{
    private string $blocksPath;
    private BlockPreviewService $previewService;

    public function __construct(DatabaseInterface $db)
    {
        $this->blocksPath     = JPATH_ADMINISTRATOR . '/components/com_j2commerce/src/Builder/blocks';
        $this->previewService = new BlockPreviewService($db);
    }

    public function renderBlock(string $slug, array $settings, int $productId, bool $editMode = true): string
    {
        if (!$this->isValidSlug($slug)) {
            return '<div class="alert alert-danger">Invalid block: ' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $viewFile = $editMode ? 'view-edit.php' : 'view.php';
        $filePath = $this->blocksPath . '/' . $slug . '/' . $viewFile;

        // Fallback to view.php if view-edit.php doesn't exist
        if ($editMode && !file_exists($filePath)) {
            $filePath = $this->blocksPath . '/' . $slug . '/view.php';
        }

        if (!file_exists($filePath)) {
            return '<div class="alert alert-warning">Block template not found: ' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $displayData               = $this->previewService->getDisplayData($productId);
        $displayData['settings']   = $settings;

        return $this->executeTemplate($filePath, $displayData);
    }

    public function renderAllBlocks(array $blocks, int $productId, bool $editMode = true): array
    {
        $rendered = [];

        foreach ($blocks as $block) {
            $slug     = $block['slug'] ?? '';
            $settings = $block['settings'] ?? [];

            $rendered[$slug] = $this->renderBlock($slug, $settings, $productId, $editMode);
        }

        return $rendered;
    }

    public function getAvailableBlocks(): array
    {
        $blocks = [];

        if (!is_dir($this->blocksPath)) {
            return $blocks;
        }

        $dirs = glob($this->blocksPath . '/*/config.php');

        foreach ($dirs as $configFile) {
            $config = include $configFile;

            if (\is_array($config) && !empty($config['slug'])) {
                $blocks[$config['slug']] = $config;
            }
        }

        return $blocks;
    }

    public function parseLayoutBlockOrder(string $fileContent): array
    {
        // Map sub-layout names to builder block slugs
        // item_options and item_cart both map to the combined cart-form block
        $layoutToBlock = [
            'item_images'      => 'product-image',
            'item_title'       => 'product-title',
            'item_description' => 'product-description',
            'item_price'       => 'product-price',
            'item_sku'         => 'product-sku',
            'item_options'     => 'cart-form',
            'item_cart'        => 'cart-form',
            'item_stock'       => 'product-stock',
            'item_quickview'   => 'product-quickview',
        ];

        $orderedSlugs = [];

        // Match renderLayout calls like: renderLayout('list.category.item_images', ...)
        if (preg_match_all('/renderLayout\s*\(\s*[\'"]list\.[a-z]+\.(item_[a-z_]+)[\'"]/', $fileContent, $matches)) {
            foreach ($matches[1] as $layoutName) {
                $slug = $layoutToBlock[$layoutName] ?? null;
                if ($slug && !\in_array($slug, $orderedSlugs, true)) {
                    $orderedSlugs[] = $slug;
                }
            }
        }

        return $orderedSlugs;
    }

    private function isValidSlug(string $slug): bool
    {
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            return false;
        }

        $configPath = $this->blocksPath . '/' . $slug . '/config.php';
        $realPath   = realpath($configPath);

        if ($realPath === false) {
            return false;
        }

        $realBlocksPath = realpath($this->blocksPath);

        return $realBlocksPath !== false && str_starts_with($realPath, $realBlocksPath);
    }

    private function executeTemplate(string $filePath, array $displayData): string
    {
        ob_start();

        try {
            (static function (string $__file, array $displayData): void {
                extract($displayData);
                include $__file;
            })($filePath, $displayData);
        } catch (\Throwable $e) {
            ob_end_clean();

            return '<div class="alert alert-danger">Block render error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }

        return ob_get_clean() ?: '';
    }
}
