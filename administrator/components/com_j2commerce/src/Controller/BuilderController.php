<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commerce\Administrator\Controller;

\defined('_JEXEC') or die;

use J2Commerce\Component\J2commerce\Administrator\Builder\Service\BlockPreviewService;
use J2Commerce\Component\J2commerce\Administrator\Builder\Service\BlockRenderService;
use J2Commerce\Component\J2commerce\Administrator\Builder\Service\SubLayoutRendererService;
use J2Commerce\Component\J2commerce\Administrator\Service\OverrideRegistry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Path;

final class BuilderController extends BaseController
{
    public function renderBlock(): void
    {
        $this->validateRequest();

        $input = $this->app->getInput();
        $slug      = $input->getString('slug', '');
        $productId = $input->getInt('product_id', 0);
        $settings  = $input->get('settings', [], 'array');
        $editMode  = $input->getBool('edit_mode', true);

        $db = Factory::getContainer()->get('DatabaseDriver');
        $renderService = new BlockRenderService($db);

        $html = $renderService->renderBlock($slug, $settings, $productId, $editMode);

        $this->sendJson(['html' => $html]);
    }

    public function renderAllBlocks(): void
    {
        $this->validateRequest();

        $body      = json_decode((string) file_get_contents('php://input'), true);
        $productId = (int) ($body['product_id'] ?? 0);
        $editMode  = (bool) ($body['edit_mode'] ?? true);
        $blocks    = $body['blocks'] ?? [];

        if (empty($blocks)) {
            $this->sendJson(['blocks' => []]);
            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $renderService = new BlockRenderService($db);

        $rendered = $renderService->renderAllBlocks($blocks, $productId, $editMode);

        $this->sendJson(['blocks' => $rendered]);
    }

    public function saveSubLayout(): void
    {
        $this->validateRequest();

        $body = json_decode((string) file_get_contents('php://input'), true);

        $pluginElement = $body['plugin_element'] ?? '';
        $fileId        = $body['file_id'] ?? '';
        $blockOrder    = $body['block_order'] ?? [];

        if (empty($pluginElement) || empty($fileId) || empty($blockOrder)) {
            $this->sendJson(null, 'Missing required fields: plugin_element, file_id, block_order', true);
            return;
        }

        // Validate block_order is an array of strings
        $blockOrder = array_values(array_filter(
            array_map('strval', $blockOrder),
            fn(string $slug): bool => (bool) preg_match('/^[a-z0-9-]+$/', $slug)
        ));

        if (empty($blockOrder)) {
            $this->sendJson(null, 'Invalid block order — no valid block slugs found', true);
            return;
        }

        // Guard: reject dispatcher files
        $sourcePath = OverrideRegistry::getSourcePath($pluginElement, $fileId);
        if (OverrideRegistry::classifyLayoutFile($sourcePath) === OverrideRegistry::FILE_TYPE_DISPATCHER) {
            $this->sendJson(null, 'This file is a product type dispatcher and cannot be saved from the visual builder. Edit the type-specific layout files instead.', true);
            return;
        }

        // Resolve the override file path and validate it's safe to write
        $overridePath = $this->resolveOverridePath($pluginElement, $fileId);

        if ($overridePath === null) {
            $this->sendJson(null, 'Cannot resolve override path or file is not a valid override', true);
            return;
        }

        // Generate composition PHP from the block order
        $renderer = new SubLayoutRendererService();
        $result   = $renderer->regenerateFromBlockOrder($blockOrder);

        if (!$result['success']) {
            $this->sendJson(null, $result['error'], true);
            return;
        }

        // Write to override file
        if (file_put_contents($overridePath, $result['php']) === false) {
            $this->sendJson(null, 'Failed to write override file', true);
            return;
        }

        $this->sendJson(['saved' => true, 'overridePath' => $overridePath, 'block_order' => $blockOrder]);
    }

    public function loadProject(): void
    {
        $this->validateRequest();

        $input         = $this->app->getInput();
        $pluginElement = $input->getString('plugin_element', '');
        $fileId        = $input->getString('file_id', '');

        if (empty($pluginElement) || empty($fileId)) {
            $this->sendJson(null, 'Missing required fields: plugin_element, file_id', true);
            return;
        }

        // Resolve override path and read the current override content
        $overridePath = $this->resolveOverridePath($pluginElement, $fileId);
        $overrideContent = '';

        if ($overridePath !== null && is_file($overridePath)) {
            $overrideContent = file_get_contents($overridePath) ?: '';
        }

        // Also read the original source file for reference
        $sourcePath = OverrideRegistry::getSourcePath($pluginElement, $fileId);
        $sourceContent = is_file($sourcePath) ? (file_get_contents($sourcePath) ?: '') : '';

        // Classify the file — dispatchers cannot be edited visually
        $fileType = OverrideRegistry::classifyLayoutFile($sourcePath);

        if ($fileType === OverrideRegistry::FILE_TYPE_DISPATCHER) {
            $this->sendJson([
                'file_type' => $fileType,
                'message'   => 'This file is a product type dispatcher. Edit the type-specific layout files (e.g. item_simple.php) instead.',
            ]);
            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        $renderService   = new BlockRenderService($db);
        $availableBlocks = $renderService->getAvailableBlocks();

        // Parse the override (or source) file to determine block order
        $contentToParse = !empty($overrideContent) ? $overrideContent : $sourceContent;
        $blockOrder     = $renderService->parseLayoutBlockOrder($contentToParse);

        $previewService  = new BlockPreviewService($db);
        $previewProducts = $previewService->getPreviewProducts();

        $this->sendJson([
            'file_type'        => $fileType,
            'override_content' => $overrideContent,
            'source_content'   => $sourceContent,
            'available_blocks' => $availableBlocks,
            'block_order'      => $blockOrder,
            'preview_products' => $previewProducts,
        ]);
    }

    public function getPreviewProducts(): void
    {
        $this->validateRequest();

        $db             = Factory::getContainer()->get('DatabaseDriver');
        $previewService = new BlockPreviewService($db);
        $products       = $previewService->getPreviewProducts();

        $this->sendJson(['products' => $products]);
    }

    /**
     * Resolve and validate the override file path.
     *
     * Safety: ensures the resolved path is inside the active template's
     * html/com_j2commerce override directory and that the file already exists
     * (store owner must create the override first via the Overrides tab).
     */
    private function resolveOverridePath(string $pluginElement, string $fileId): ?string
    {
        // Validate plugin element is a known subtemplate
        if (!preg_match('/^app_[a-z0-9_]+$/', $pluginElement)) {
            return null;
        }

        // Validate file ID doesn't contain path traversal
        if (str_contains($fileId, '..') || str_contains($fileId, "\0")) {
            return null;
        }

        // Get the active SITE template (not admin) — same approach as OverridesModel
        $template = $this->getActiveSiteTemplate();
        $templateOverridePath = Path::clean(
            JPATH_ROOT . '/templates/' . $template . '/html/layouts/com_j2commerce/' . $pluginElement
        );

        $overrideFile = Path::clean($templateOverridePath . '/' . $fileId);

        // Ensure the resolved path is inside the template override directory (prevent traversal)
        if (strpos($overrideFile, Path::clean($templateOverridePath)) !== 0) {
            return null;
        }

        // The override file MUST already exist — we never create overrides, only edit existing ones
        if (!is_file($overrideFile)) {
            return null;
        }

        // Ensure we're NOT inside a core component/plugin directory
        $corePaths = [
            Path::clean(JPATH_ROOT . '/components/'),
            Path::clean(JPATH_ROOT . '/plugins/'),
            Path::clean(JPATH_ADMINISTRATOR . '/components/'),
        ];

        foreach ($corePaths as $corePath) {
            if (strpos($overrideFile, $corePath) === 0) {
                return null;
            }
        }

        return $overrideFile;
    }

    private function getActiveSiteTemplate(): string
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('template'))
            ->from($db->quoteName('#__template_styles'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('home') . ' = ' . $db->quote('1'));
        $db->setQuery($query);

        return $db->loadResult() ?: 'cassiopeia';
    }

    private function validateRequest(): void
    {
        if (!$this->app->getIdentity()->authorise('core.manage', 'com_j2commerce')) {
            $this->sendJson(null, 'Access denied', true);
        }

        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            $this->sendJson(null, 'Invalid token', true);
        }
    }

    private function sendJson(mixed $data, string $error = '', bool $isError = false): void
    {
        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8');
        echo new JsonResponse($data, $error, $isError);
        $this->app->close();
    }
}
