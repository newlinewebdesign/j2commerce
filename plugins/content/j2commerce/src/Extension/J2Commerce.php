<?php
/**
 * @package     J2Commerce
 * @subpackage  plg_content_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Plugin\Content\J2Commerce\Extension;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Content\AfterDeleteEvent;
use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Event\Content\AfterSaveEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Event\Content\BeforeSaveEvent;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Event\Model\BeforeSaveEvent as ModelBeforeSaveEvent;
use Joomla\CMS\Event\Model\AfterSaveEvent as ModelAfterSaveEvent;
use Joomla\CMS\Event\Model\AfterDeleteEvent as ModelAfterDeleteEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use J2Commerce\Component\J2commerce\Administrator\Helper\ProductHelper;
use J2Commerce\Component\J2commerce\Administrator\Service\ProductService;
use Joomla\CMS\Session\Session;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Psr\Log\LoggerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * J2Commerce Content Plugin - Integrates products with Joomla articles via shortcodes and forms.
 *
 * @since  6.0.0
 */
final class J2Commerce extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Auto-load plugin language files.
     * Note: This loads from administrator/language/ which is where install copies files.
     *
     * @var bool
     */
    protected $autoloadLanguage = true;

    private bool $cacheCleared = false;

    private static array $articleCache = [];

    /**
     * Clear the static article cache when an article is saved or deleted.
     *
     * @param   int  $articleId  The article ID to clear from cache.
     *
     * @return  void
     *
     * @since   6.1.0
     */
    private function clearArticleCache(int $articleId): void
    {
        unset(self::$articleCache[$articleId]);
    }

    /** @since 6.0.0 */
    public function __construct(DispatcherInterface $dispatcher, array $config = [])
    {
        parent::__construct($dispatcher, $config);

        // Load component language for COM_J2COMMERCE_* strings used in product form templates
        Factory::getApplication()->getLanguage()->load('com_j2commerce', JPATH_ADMINISTRATOR . '/components/com_j2commerce');
    }

    /** @since 6.0.0 */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare'       => 'onContentPrepare',
            'onContentPrepareForm'   => 'onContentPrepareForm',
            'onContentBeforeSave'    => 'onContentBeforeSave',
            'onContentAfterSave'     => 'onContentAfterSave',
            'onContentAfterDelete'   => 'onContentAfterDelete',
            'onContentBeforeDisplay' => 'onContentBeforeDisplay',
            'onContentAfterDisplay'  => 'onContentAfterDisplay',
            'onContentAfterFieldset' => 'onContentAfterFieldset',
        ];
    }

    /** @since 6.0.0 */
    public function onContentPrepare(ContentPrepareEvent $event): void
    {
        $context = $event->getContext();
        $article = $event->getItem();
        $params  = $event->getParams();

        // Skip backend and API processing — Route::_() crashes in API context
        if ($this->getApplication()->isClient('administrator') || $this->getApplication()->isClient('api')) {
            return;
        }

        // Skip category list views
        if (strpos($context, 'categories') !== false) {
            return;
        }

        // Handle product list context - strip shortcodes
        if (strpos($context, 'productlist') !== false) {
            $this->stripShortcodes($article);
            return;
        }

        // Clear caches if enabled
        if ($this->params->get('cache_control', 1) && !$this->cacheCleared) {
            $this->clearContentCaches();
        }

        // Get J2Commerce component params for placement setting
        $j2params   = ComponentHelper::getParams('com_j2commerce');
        $placement  = $j2params->get('addtocart_placement', 'default');

        // Handle default position placement
        if (strpos($context, 'com_content') !== false) {
            if (\in_array($placement, ['default', 'both'], true)) {
                if ($this->checkPublishDate($article)) {
                    $this->renderDefaultPosition($context, $article, $params);
                }
            }
        }

        // Handle tag-based placement
        if (\in_array($placement, ['tag', 'both'], true)) {
            $this->processWithinArticle($article);
        }

        // Always process shortcodes
        $this->processShortcodes($context, $article, $params);
    }

    /** @since 6.0.0 */
    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        // Check frontend editing permission
        if ($this->getApplication()->isClient('site')
            && $this->params->get('allow_frontend_product_edit', 0) == 0) {
            return;
        }

        // Verify form is a Joomla Form object
        if (!($form instanceof Form)) {
            return;
        }

        $formName = $form->getName();
        $option = $this->getApplication()->getInput()->get('option');
        $extension = $this->getApplication()->getInput()->get('extension');

        // Handle article forms
        if (\in_array($formName, ['com_content.article'], true)) {
            $this->injectArticleForm($form);
            return;
        }

        // Handle category forms - use same pattern as app_bulkdiscount
        if ($option === 'com_categories' && $extension === 'com_content') {
            // Trigger the onJ2CommerceBeforeContentPrepareForm event for other plugins
            $this->dispatchJ2CommerceFormEvent($form, $data);

            // Also inject our own category form
            $this->injectCategoryForm($form, $data);
        }
    }

    /**
     * Dispatch J2Commerce form event for other plugins to hook into
     */
    protected function dispatchJ2CommerceFormEvent(Form $form, $data): void
    {
        J2CommerceHelper::plugin()->event('BeforeContentPrepareForm', [$form, $data]);
    }

    protected function injectArticleForm(Form $form): void
    {

        $language = Factory::getApplication()->getLanguage();
        $language->load('plg_content_j2commerce', JPATH_ADMINISTRATOR);
        $language->load('com_j2commerce', JPATH_ADMINISTRATOR);

        Form::addFormPath(__DIR__ . '/../../forms');
        Form::addFieldPath(__DIR__ . '/../Field');
        $form->loadFile('j2commerce', false);

        $contentParams   = ComponentHelper::getParams('com_content');
        $messageDisplay  = $contentParams->get('show_article_options', 0);

        if (!$messageDisplay) {
            $this->getApplication()->enqueueMessage(
                Text::_('PLG_CONTENT_J2COMMERCE_TAB_NOT_DISPLAYED'),
                'warning'
            );
        }
    }

    protected function injectCategoryForm(Form $form): void
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('plg_content_j2commerce', JPATH_ADMINISTRATOR);
        $language->load('com_j2commerce', JPATH_ADMINISTRATOR);

        // Load category form fields from XML file (Joomla 6 best practice)
        $form->loadFile(JPATH_PLUGINS . '/content/j2commerce/forms/category.xml');
    }

    /**
     * Render the J2Commerce accordion template after the fieldset.
     *
     * @since  6.1.0
     */
    public function onContentAfterFieldset($event): string
    {
        $context = $event->getArgument('context');
        $fieldset = $event->getArgument('fieldset');
        $data = $event->getArgument('data');

        // Only for J2Commerce fieldset in com_content category forms
        if ($context !== 'com_categories.category' || $fieldset->name !== 'j2commerce') {
            return '';
        }

        // Ensure this is a com_content category
        $extension = $this->getApplication()->getInput()->get('extension');
        if ($extension !== 'com_content') {
            return '';
        }

        $category = is_object($data) ? $data : new \stdClass();

        // Load the accordion template
        $layout = new \Joomla\CMS\Layout\FileLayout(
            'category.form_j2commerce',
            JPATH_ADMINISTRATOR . '/components/com_j2commerce/tmpl'
        );

        return $layout->render([
            'category' => $category,
            'form_prefix' => 'jform[attribs][j2commerce]',
        ]);
    }

    /**
     * Handle content before save event.
     *
     *
     * @since 6.0.0
     */
    public function onContentBeforeSave(BeforeSaveEvent|ModelBeforeSaveEvent $event): void
    {
        $context = $event->getContext();

        // Only process com_content contexts - ignore all other components
        if (!str_starts_with($context, 'com_content.')) {
            return;
        }

        // Determine valid contexts for our product integration
        $validContexts = ['com_content.article'];
        if ($this->getApplication()->isClient('site')) {
            $validContexts = ['com_content.form'];
        }

        if (!\in_array($context, $validContexts, true)) {
            return;
        }

        $data = $event->getItem();
        $app  = $this->getApplication();

        // Store full attribs for later processing
        $app->getInput()->set('j2commerce_all_attribs', $data->attribs ?? '', 'RAW');

        // Remove j2commerce data from attribs before article save
        $allAttribs = json_decode($data->attribs ?? '{}');
        if (isset($allAttribs->j2commerce)) {
            unset($allAttribs->j2commerce);
        }
        $data->attribs = json_encode($allAttribs);
    }

    /**
     * Handle content after save event.
     *
     * @since 6.0.0
     */
    public function onContentAfterSave(AfterSaveEvent|ModelAfterSaveEvent $event): void
    {
        $context = $event->getContext();

        // Only process com_content contexts - ignore all other components
        if (!str_starts_with($context, 'com_content.')) {
            return;
        }

        $data    = $event->getItem();
        $isNew   = $event->getIsNew();

        // Determine valid contexts
        $validContexts = ['com_content.article'];
        if ($this->getApplication()->isClient('site')) {
            $validContexts = ['com_content.form'];
        }

        if (!\in_array($context, $validContexts, true)) {
            return;
        }

        $app       = $this->getApplication();
        $articleId = (int) ($data->id ?? 0);

        if (!$articleId) {
            return;
        }

        // Get stored attribs
        $allAttribs = $app->getInput()->get('j2commerce_all_attribs', '', 'RAW');
        $attribs    = json_decode($allAttribs);

        if (!isset($attribs->j2commerce)) {
            return;
        }

        $j2data = $attribs->j2commerce;

        // Check if product is enabled and has a product type
        if (empty($j2data->enabled) || empty($j2data->product_type)) {
            return;
        }

        // Check if product already exists for this article
        $existingProduct = $this->getProductBySource('com_content', $articleId);
        $alreadyExists   = ($existingProduct && !empty($existingProduct->enabled));

        // Only save if enabled or already exists
        if ($j2data->enabled != 1 && !$alreadyExists) {
            return;
        }

        // Handle save2copy task
        $task = $app->getInput()->getString('task', '');
        if ($task === 'save2copy') {
            // Skip variable product types on copy
            $variableTypes = ['variable', 'advancedvariable', 'flexivariable', 'variablesubscriptionproduct'];
            if (\in_array($j2data->product_type, $variableTypes, true)) {
                return;
            }

            // Reset IDs for new product
            $j2data->j2commerce_product_id        = null;
            $j2data->j2commerce_variant_id        = null;
            $j2data->j2store_productimage_id   = null;
            if (isset($j2data->quantity)) {
                $j2data->quantity->j2commerce_productquantity_id = null;
            }
            unset($j2data->item_options);
        }

        // Set product source information
        $j2data->product_source    = 'com_content';
        $j2data->product_source_id = $articleId;

        // Save product using native MVC
        $this->saveProduct($j2data);

        // Clear the static article cache to ensure fresh data on next load
        $this->clearArticleCache($articleId);
    }

    /**
     * Handle content after delete event.
     *
     * @since 6.0.0
     */
    public function onContentAfterDelete(AfterDeleteEvent|ModelAfterDeleteEvent $event): void
    {
        $context = $event->getContext();

        // Only process com_content contexts - ignore all other components
        if (!str_starts_with($context, 'com_content.')) {
            return;
        }

        $data      = $event->getItem();
        $articleId = $data->id ?? 0;

        if (!$articleId) {
            return;
        }

        // Delete products linked to this article
        $this->deleteProductsBySource('com_content', $articleId);

        // Clear the static article cache
        $this->clearArticleCache((int) $articleId);
    }

    /** @since 6.0.0 */
    public function onContentBeforeDisplay(BeforeDisplayEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();
        $params  = $event->getParams();

        if (!$this->checkPublishDate($item)) {
            return;
        }

        $j2params  = ComponentHelper::getParams('com_j2commerce');
        $placement = $j2params->get('addtocart_placement', 'default');

        if ($placement === 'tag') {
            return;
        }

        $html = $this->getProductImages('beforecontent', $context, $item, $params);
        $event->addResult($html);
    }

    /** @since 6.0.0 */
    public function onContentAfterDisplay(AfterDisplayEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();
        $params  = $event->getParams();

        if (strpos($context, 'com_content') === false) {
            return;
        }

        if (!$this->checkPublishDate($item)) {
            return;
        }

        $j2params  = ComponentHelper::getParams('com_j2commerce');
        $placement = $j2params->get('addtocart_placement', 'default');

        if ($placement === 'tag') {
            return;
        }

        // Skip product list context
        if ($context === 'com_content.category.productlist') {
            return;
        }

        $html = '';

        // Determine position setting
        if (\in_array($context, ['com_content.category', 'com_content.featured'], true)) {
            $position = $this->params->get('category_product_block_position', 'bottom');
        } else {
            $position = $this->params->get('item_product_block_position', 'bottom');
        }

        // Only render if position is afterdisplaycontent
        if (isset($item->id) && $item->id > 0 && $position === 'afterdisplaycontent') {
            $product = $this->getProductBySource('com_content', (int) $item->id);

            if ($product) {
                $html .= $this->getProductImageHtml($product, $context, $item, $params);
                $html .= $this->getProductBlock($product, $context);
            }
        }

        $html .= $this->getProductImages('aftercontent', $context, $item, $params);
        $event->addResult($html);
    }

    /**
     * @since   6.0.0
     */
    private function clearContentCaches(): void
    {
        try {
            $cacheFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);

            $contentCache = $cacheFactory->createCacheController('', ['defaultgroup' => 'com_content']);
            $contentCache->clean();

            $j2commerceCache = $cacheFactory->createCacheController('', ['defaultgroup' => 'com_j2commerce']);
            $j2commerceCache->clean();

            $this->cacheCleared = true;
        } catch (\Exception $e) {
            // Silently fail on cache clear
        }
    }

    /**
     * @since   6.0.0
     */
    private function stripShortcodes(object $article): void
    {
        if (!isset($article->text)) {
            return;
        }

        $matches = $this->parseShortcodes($article->text);

        if (empty($matches)) {
            return;
        }

        foreach ($matches as $match) {
            $article->text = str_replace($match[0], '', $article->text);
        }
    }

    /**
     * @since   6.0.0
     */
    private function renderDefaultPosition(string $context, object $article, object $params): void
    {
        // Determine position setting
        if (\in_array($context, ['com_content.category', 'com_content.featured'], true)) {
            $position = $this->params->get('category_product_block_position', 'bottom');
        } else {
            $position = $this->params->get('item_product_block_position', 'bottom');
        }

        if (!isset($article->id) || !$article->id || $position === 'afterdisplaycontent') {
            return;
        }

        $product = $this->getProductBySource('com_content', (int) $article->id);

        if (!$product) {
            return;
        }

        $html      = $this->getProductBlock($product, $context);
        $imageHtml = $this->getProductImageHtml($product, $context, $article, $params);

        if ($position === 'top') {
            $article->text = $imageHtml . $html . $article->text;
        } else {
            $article->text = $article->text . $imageHtml . $html;
        }
    }

    /**
     * @since   6.0.0
     */
    private function getProductBlock(object $product, string $context): string
    {
        // Check if we should show options in category view
        $categoryOptions = $this->params->get('category_product_options', 1);

        if (\in_array($context, ['com_content.category', 'com_content.featured'], true)
            && \in_array($categoryOptions, [2, 3], true)) {
            // Redirect to product or show without options
            return $this->renderProductHtml($product, false);
        }

        return $this->renderProductHtml($product, true);
    }

    /**
     * @since   6.0.0
     */
    private function renderProductHtml(object $product, bool $showOptions = true): string
    {
        // Build product display HTML
        $html = '<div class="j2commerce-product-block" data-product-id="' . (int) $product->j2commerce_product_id . '">';

        // Get variant/pricing info
        $variant = $this->getProductVariant($product->j2commerce_product_id);

        if ($variant) {
            // Price display
            $html .= '<div class="j2commerce-product-price">';
            $html .= $this->formatPrice($variant->price ?? 0);
            $html .= '</div>';

            // Add to cart button
            $j2params = ComponentHelper::getParams('com_j2commerce');

            if (!$j2params->get('catalog_mode', 0)) {
                $buttonClass = $j2params->get('addtocart_button_class', 'btn btn-primary');
                $buttonText  = $product->addtocart_text ?: Text::_('PLG_CONTENT_J2COMMERCE_ADD_TO_CART');

                $html .= '<div class="j2commerce-addtocart">';
                $html .= '<form class="j2commerce-cart-form" method="post">';
                $html .= '<input type="hidden" name="product_id" value="' . (int) $product->j2commerce_product_id . '">';
                $html .= '<input type="hidden" name="variant_id" value="' . (int) ($variant->j2commerce_variant_id ?? 0) . '">';
                $html .= '<input type="hidden" name="' . Session::getFormToken() . '" value="1">';

                if ($showOptions && $product->has_options) {
                    $html .= $this->renderProductOptions($product->j2commerce_product_id);
                }

                // Quantity field
                if ($j2params->get('show_qty_field', 1)) {
                    $html .= '<div class="j2commerce-quantity">';
                    $html .= '<label for="qty_' . $product->j2commerce_product_id . '">' . Text::_('PLG_CONTENT_J2COMMERCE_QUANTITY') . '</label>';
                    $html .= '<input type="number" name="quantity" id="qty_' . $product->j2commerce_product_id . '" value="1" min="1" class="form-control">';
                    $html .= '</div>';
                }

                $html .= '<button type="submit" class="' . $this->escape($buttonClass) . '">';
                $html .= $this->escape($buttonText);
                $html .= '</button>';
                $html .= '</form>';
                $html .= '</div>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @since   6.0.0
     */
    private function renderProductOptions(int $productId): string
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('po.j2commerce_product_option_id'),
                $db->quoteName('po.option_id'),
                $db->quoteName('po.required'),
                $db->quoteName('o.option_name'),
                $db->quoteName('o.type'),
            ])
            ->from($db->quoteName('#__j2commerce_product_options', 'po'))
            ->join('LEFT', $db->quoteName('#__j2commerce_options', 'o'), $db->quoteName('po.option_id') . ' = ' . $db->quoteName('o.j2commerce_option_id'))
            ->where($db->quoteName('po.product_id') . ' = :productId')
            ->bind(':productId', $productId, ParameterType::INTEGER)
            ->order($db->quoteName('po.ordering') . ' ASC');

        $db->setQuery($query);
        $options = $db->loadObjectList();

        $html = '';

        if (!empty($options)) {
            $html .= '<div class="j2commerce-product-options">';

            foreach ($options as $option) {
                $html .= '<div class="j2commerce-option">';
                $html .= '<label>' . $this->escape($option->option_name);
                if ($option->required) {
                    $html .= ' <span class="required">*</span>';
                }
                $html .= '</label>';
                $html .= $this->renderOptionValues($option);
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @since   6.0.0
     */
    private function renderOptionValues(object $option): string
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pov.j2commerce_product_optionvalue_id'),
                $db->quoteName('pov.optionvalue_id'),
                $db->quoteName('pov.price'),
                $db->quoteName('pov.price_prefix'),
                $db->quoteName('ov.optionvalue_name'),
            ])
            ->from($db->quoteName('#__j2commerce_product_optionvalues', 'pov'))
            ->join('LEFT', $db->quoteName('#__j2commerce_optionvalues', 'ov'), $db->quoteName('pov.optionvalue_id') . ' = ' . $db->quoteName('ov.j2commerce_optionvalue_id'))
            ->where($db->quoteName('pov.product_option_id') . ' = :optionId')
            ->bind(':optionId', $option->j2commerce_product_option_id, ParameterType::INTEGER)
            ->order($db->quoteName('pov.ordering') . ' ASC');

        $db->setQuery($query);
        $values = $db->loadObjectList();

        $html      = '';
        $fieldName = 'option[' . $option->j2commerce_product_option_id . ']';

        switch ($option->type) {
            case 'select':
                $html .= '<select name="' . $fieldName . '" class="form-select">';
                $html .= '<option value="">' . Text::_('PLG_CONTENT_J2COMMERCE_SELECT_OPTION') . '</option>';
                foreach ($values as $value) {
                    $priceText = $this->formatOptionPrice($value);
                    $html     .= '<option value="' . (int) $value->j2commerce_product_optionvalue_id . '">';
                    $html     .= $this->escape($value->optionvalue_name) . $priceText;
                    $html     .= '</option>';
                }
                $html .= '</select>';
                break;

            case 'radio':
                foreach ($values as $value) {
                    $priceText = $this->formatOptionPrice($value);
                    $html     .= '<div class="form-check">';
                    $html     .= '<input type="radio" name="' . $fieldName . '" value="' . (int) $value->j2commerce_product_optionvalue_id . '" class="form-check-input" id="opt_' . (int) $value->j2commerce_product_optionvalue_id . '">';
                    $html     .= '<label class="form-check-label" for="opt_' . (int) $value->j2commerce_product_optionvalue_id . '">';
                    $html     .= $this->escape($value->optionvalue_name) . $priceText;
                    $html     .= '</label>';
                    $html     .= '</div>';
                }
                break;

            case 'checkbox':
                foreach ($values as $value) {
                    $priceText = $this->formatOptionPrice($value);
                    $html     .= '<div class="form-check">';
                    $html     .= '<input type="checkbox" name="' . $fieldName . '[]" value="' . (int) $value->j2commerce_product_optionvalue_id . '" class="form-check-input" id="opt_' . (int) $value->j2commerce_product_optionvalue_id . '">';
                    $html     .= '<label class="form-check-label" for="opt_' . (int) $value->j2commerce_product_optionvalue_id . '">';
                    $html     .= $this->escape($value->optionvalue_name) . $priceText;
                    $html     .= '</label>';
                    $html     .= '</div>';
                }
                break;
        }

        return $html;
    }

    /** @since 6.0.0 */
    private function formatOptionPrice(object $value): string
    {
        $j2params = ComponentHelper::getParams('com_j2commerce');

        if (!$j2params->get('show_option_price', 1) || empty($value->price)) {
            return '';
        }

        $prefix = $value->price_prefix ?: '+';

        if ($j2params->get('show_option_price_prefix', 1)) {
            return ' (' . $prefix . $this->formatPrice((float) $value->price) . ')';
        }

        return ' (' . $this->formatPrice((float) $value->price) . ')';
    }

    /** @since 6.0.0 */
    private function getProductImageHtml(object $product, string $context, object $item, object $params): string
    {
        $html = '';

        // Determine settings based on context
        if (\in_array($context, ['com_content.category', 'com_content.featured'], true)) {
            $showImage     = $this->params->get('category_display_j2store_images', 1);
            $imageType     = $this->params->get('category_image_type', 'thumbnail');
            $imageLocation = 'default';
            $mainWidth     = $this->params->get('list_image_thumbnail_width', 120);
        } else {
            $showImage     = $this->params->get('item_display_j2store_images', 1);
            $imageType     = $this->params->get('item_image_type', 'main');
            $imageLocation = $this->params->get('item_image_placement', 'default');
            $mainWidth     = $this->params->get('item_product_main_image_width', 300);
        }

        if (!$showImage || $imageLocation !== 'default') {
            return $html;
        }

        $images = $this->getProductImagesData($product->j2commerce_product_id, $imageType);

        if (!empty($images)) {
            $html .= '<div class="j2commerce-product-images">';
            foreach ($images as $image) {
                $html .= '<img src="' . $this->escape($image->image_path) . '" alt="" class="j2commerce-product-image" style="max-width: ' . (int) $mainWidth . 'px;">';
            }
            $html .= '</div>';
        }

        return $html;
    }

    /** @since 6.0.0 */
    private function getProductImages(string $event, string $context, object $item, object $params): string
    {
        $imageLocation = $this->params->get('item_image_placement', 'default');
        $showImage     = $this->params->get('item_display_j2store_images', 1);

        if ($imageLocation !== $event || !$showImage || !isset($item->id) || $item->id < 1) {
            return '';
        }

        if (!str_contains($context, 'com_content.article')) {
            return '';
        }

        $j2params  = ComponentHelper::getParams('com_j2commerce');
        $placement = $j2params->get('addtocart_placement', 'default');

        if (!\in_array($placement, ['default', 'both'], true)) {
            return '';
        }

        $product = $this->getProductBySource('com_content', (int) $item->id);

        if (!$product) {
            return '';
        }

        $imageType = $this->params->get('item_image_type', 'main');
        $images    = $this->getProductImagesData($product->j2commerce_product_id, $imageType);
        $html      = '';

        if (!empty($images)) {
            $html .= '<div class="j2commerce-product-images">';
            foreach ($images as $image) {
                $html .= '<img src="' . $this->escape($image->image_path) . '" alt="" class="j2commerce-product-image">';
            }
            $html .= '</div>';
        }

        return $html;
    }

    /** @since 6.0.0 */
    private function processWithinArticle(object $article): void
    {
        if (!isset($article->text) || !str_contains($article->text, '{j2commerce}')) {
            return;
        }

        $this->processShortcodes('', $article, new Registry());
    }

    /** @since 6.0.0 */
    private function processShortcodes(string $context, object $article, object $params): void
    {
        if (!isset($article->text)) {
            return;
        }

        $matches = $this->parseShortcodes($article->text);

        if (empty($matches)) {
            return;
        }

        $j2params  = ComponentHelper::getParams('com_j2commerce');
        $placement = $j2params->get('addtocart_placement', 'default');

        foreach ($matches as $match) {
            if (empty($match[1])) {
                continue;
            }

            $values = explode('|', $match[1]);
            $html   = '';

            // First value is always the product ID
            if (!isset($values[0]) || !is_numeric($values[0])) {
                continue;
            }

            $productId = (int) $values[0];
            $product   = $this->getProductById($productId);

            if (!$product) {
                $article->text = str_replace($match[0], '', $article->text);
                continue;
            }

            // Check publish date if product has source article
            if (!empty($product->product_source_id)) {
                $sourceArticle = $this->getArticle((int) $product->product_source_id);
                if (!$this->checkPublishDate($sourceArticle)) {
                    $article->text = str_replace($match[0], '', $article->text);
                    continue;
                }
            }

            // Process shortcode options
            if (\in_array($placement, ['tag', 'both'], true) && \in_array('cart', $values, true)) {
                $html .= $this->renderProductHtml($product, true);
            }

            if (\in_array('cartonly', $values, true)) {
                $html .= $this->renderProductHtml($product, false);
            }

            if (\in_array('price', $values, true)) {
                $html .= $this->getProductPriceHtml($product, 'price');
            }

            if (\in_array('saleprice', $values, true)) {
                $html .= $this->getProductPriceHtml($product, 'saleprice');
            }

            if (\in_array('regularprice', $values, true)) {
                $html .= $this->getProductPriceHtml($product, 'regularprice');
            }

            if (\in_array('thumbnail', $values, true)) {
                $html .= $this->getProductImagesHtml($product, 'thumbnail');
            }

            if (\in_array('mainimage', $values, true)) {
                $html .= $this->getProductImagesHtml($product, 'main');
            }

            if (\in_array('mainadditional', $values, true)) {
                $html .= $this->getProductImagesHtml($product, 'mainadditional');
            }

            if (\in_array('upsells', $values, true)) {
                $html .= $this->getProductUpsellsHtml($product);
            }

            if (\in_array('crosssells', $values, true)) {
                $html .= $this->getProductCrossSellsHtml($product);
            }

            if (\in_array('manufacturer', $values, true) || \in_array('brand', $values, true)) {
                $html .= $this->getProductBrandHtml($product);
            }

            $article->text = str_replace($match[0], $html, $article->text);
        }
    }

    /** @since 6.0.0 */
    private function parseShortcodes(string $text): array
    {
        $regex = '/{j2commerce}(.*?){\/j2commerce}/';
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

        return $matches;
    }

    /** @since 6.0.0 */
    private function getProductPriceHtml(object $product, string $priceType): string
    {
        $variant = $this->getProductVariant($product->j2commerce_product_id);

        if (!$variant) {
            return '';
        }

        $price = 0;

        switch ($priceType) {
            case 'price':
                $price = $variant->special_price ?: $variant->price;
                break;
            case 'saleprice':
                $price = $variant->special_price ?: 0;
                break;
            case 'regularprice':
                $price = $variant->price;
                break;
        }

        if (!$price) {
            return '';
        }

        return '<span class="j2commerce-price j2commerce-' . $priceType . '">' . $this->formatPrice((float) $price) . '</span>';
    }

    /** @since 6.0.0 */
    private function getProductImagesHtml(object $product, string $imageType): string
    {
        $images = $this->getProductImagesData($product->j2commerce_product_id, $imageType);
        $html   = '';

        if (!empty($images)) {
            $html .= '<div class="j2commerce-product-images j2commerce-' . $imageType . '">';
            foreach ($images as $image) {
                $html .= '<img src="' . $this->escape($image->image_path) . '" alt="" class="j2commerce-product-image">';
            }
            $html .= '</div>';
        }

        return $html;
    }

    /** @since 6.0.0 */
    private function getProductUpsellsHtml(object $product): string
    {
        if (empty($product->up_sells)) {
            return '';
        }

        $ids      = array_map('intval', explode(',', $product->up_sells));
        $products = $this->getProductsByIds($ids);

        if (empty($products)) {
            return '';
        }

        $html = '<div class="j2commerce-upsells">';
        $html .= '<h4>' . Text::_('PLG_CONTENT_J2COMMERCE_UPSELLS') . '</h4>';
        $html .= '<div class="j2commerce-related-products">';

        foreach ($products as $relatedProduct) {
            $html .= $this->renderRelatedProductHtml($relatedProduct);
        }

        $html .= '</div></div>';

        return $html;
    }

    /** @since 6.0.0 */
    private function getProductCrossSellsHtml(object $product): string
    {
        if (empty($product->cross_sells)) {
            return '';
        }

        $ids      = array_map('intval', explode(',', $product->cross_sells));
        $products = $this->getProductsByIds($ids);

        if (empty($products)) {
            return '';
        }

        $html = '<div class="j2commerce-crosssells">';
        $html .= '<h4>' . Text::_('PLG_CONTENT_J2COMMERCE_CROSSSELLS') . '</h4>';
        $html .= '<div class="j2commerce-related-products">';

        foreach ($products as $relatedProduct) {
            $html .= $this->renderRelatedProductHtml($relatedProduct);
        }

        $html .= '</div></div>';

        return $html;
    }

    /** @since 6.0.0 */
    private function renderRelatedProductHtml(object $product): string
    {
        $html = '<div class="j2commerce-related-product">';

        // Get product name from source
        $name = '';
        if ($product->product_source === 'com_content' && !empty($product->product_source_id)) {
            $article = $this->getArticle((int) $product->product_source_id);
            if ($article) {
                $name = $article->title;
                $link = RouteHelper::getArticleRoute($article->id, $article->catid, $article->language ?? '*');
                $html .= '<a href="' . Route::_($link) . '">' . $this->escape($name) . '</a>';
            }
        }

        // Price
        $variant = $this->getProductVariant($product->j2commerce_product_id);
        if ($variant) {
            $html .= '<span class="j2commerce-price">' . $this->formatPrice((float) ($variant->special_price ?: $variant->price)) . '</span>';
        }

        $html .= '</div>';

        return $html;
    }

    /** @since 6.0.0 */
    private function getProductBrandHtml(object $product): string
    {
        if (empty($product->manufacturer_id)) {
            return '';
        }

        $manufacturer = $this->getManufacturer((int) $product->manufacturer_id);

        if (!$manufacturer) {
            return '';
        }

        return '<div class="j2commerce-brand"><span class="brand-label">' . Text::_('PLG_CONTENT_J2COMMERCE_BRAND') . ':</span> <span class="brand-name">' . $this->escape($manufacturer->company ?? '') . '</span></div>';
    }

    /** @since 6.0.0 */
    private function checkPublishDate(?object $article): bool
    {
        if (!$this->params->get('check_publish_date', 0)) {
            return true;
        }

        if (!$article || !isset($article->publish_up) || !isset($article->publish_down)) {
            return true;
        }

        $db       = $this->getDatabase();
        $nullDate = $db->getNullDate();
        $nowDate  = Factory::getDate('now')->toSql();

        $publishUp   = $article->publish_up;
        $publishDown = $article->publish_down;

        $validUp   = ($publishUp === $nullDate || $publishUp <= $nowDate);
        $validDown = ($publishDown === $nullDate || $publishDown >= $nowDate);

        return $validUp && $validDown;
    }

    /**
     * Get full product by source using centralized ProductHelper.
     *
     * @since 6.0.0
     * @since 6.0.8 Uses centralized ProductHelper::getFullProductBySource()
     */
    private function getProductBySource(string $source, int $sourceId): ?object
    {
        return ProductHelper::getFullProductBySource($source, $sourceId);
    }

    /**
     * Get full product by ID using centralized ProductHelper.
     *
     * @since 6.0.0
     * @since 6.0.8 Uses centralized ProductHelper::getFullProduct()
     */
    private function getProductById(int $productId): ?object
    {
        return ProductHelper::getFullProduct($productId);
    }

    /** @since 6.0.0 */
    private function getProductsByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__j2commerce_products'))
            ->whereIn($db->quoteName('j2commerce_product_id'), $ids)
            ->where($db->quoteName('enabled') . ' = 1');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** @since 6.0.0 */
    private function getProductVariant(int $productId): ?object
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__j2commerce_variants'))
            ->where($db->quoteName('product_id') . ' = :productId')
            ->where($db->quoteName('is_master') . ' = 1')
            ->bind(':productId', $productId, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    /** @since 6.0.0 */
    private function getProductImagesData(int $productId, string $imageType): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('main_image', 'image_path'),
                $db->quoteName('thumb_image'),
            ])
            ->from($db->quoteName('#__j2commerce_productimages'))
            ->where($db->quoteName('product_id') . ' = :productId')
            ->bind(':productId', $productId, ParameterType::INTEGER)
            ->order($db->quoteName('ordering') . ' ASC');

        $db->setQuery($query);
        $images = $db->loadObjectList() ?: [];

        // Filter by type
        $result = [];
        foreach ($images as $index => $image) {
            if ($imageType === 'thumbnail' && !empty($image->thumb_image)) {
                $image->image_path = $image->thumb_image;
                $result[]          = $image;
            } elseif ($imageType === 'main' && $index === 0) {
                $result[] = $image;
            } elseif ($imageType === 'mainadditional') {
                $result[] = $image;
            }
        }

        return $result;
    }

    /** @since 6.0.0 */
    private function getManufacturer(int $manufacturerId): ?object
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.j2commerce_manufacturer_id'),
                $db->quoteName('a.company'),
            ])
            ->from($db->quoteName('#__j2commerce_manufacturers', 'm'))
            ->join('LEFT', $db->quoteName('#__j2commerce_addresses', 'a'), $db->quoteName('m.address_id') . ' = ' . $db->quoteName('a.j2commerce_address_id'))
            ->where($db->quoteName('m.j2commerce_manufacturer_id') . ' = :manufacturerId')
            ->bind(':manufacturerId', $manufacturerId, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    /** @since 6.0.0 */
    private function getArticle(int $articleId): ?object
    {
        if (isset(self::$articleCache[$articleId])) {
            return self::$articleCache[$articleId];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('a.*'),
                $db->quoteName('c.title', 'category_title'),
                $db->quoteName('c.alias', 'category_alias'),
                $db->quoteName('c.access', 'category_access'),
            ])
            ->from($db->quoteName('#__content', 'a'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->where($db->quoteName('a.id') . ' = :articleId')
            ->bind(':articleId', $articleId, ParameterType::INTEGER);

        $db->setQuery($query);
        $article = $db->loadObject();

        self::$articleCache[$articleId] = $article ?: null;

        return self::$articleCache[$articleId];
    }

    /**
     * Save product using the behavior system via ProductService.
     *
     * This replaces the previous direct SQL approach with a behavior-based approach
     * that ensures all product types (simple, downloadable, flexivariable, etc.)
     * trigger appropriate lifecycle events and are handled consistently with
     * admin component saves.
     *
     * @param   object  $data  The product data from the article form.
     *
     * @return  bool  True on success, false on failure.
     *
     * @since   6.0.0
     * @since   6.0.11 Refactored to use ProductService with behavior system
     */
    private function saveProduct(object $data): bool
    {
        try {
            // Use ProductService directly (same pattern as ProductController and ProductHelper)
            $productService = new ProductService();

            // Save the product using the behavior system
            // The ProductService will:
            // 1. Get the appropriate behavior based on product_type
            // 2. Call onBeforeSave to validate/transform data
            // 3. Save the main product record via Table class
            // 4. Call onAfterSave to save related data (variants, options, images, filters)
            $productId = $productService->saveProduct($data);

            if ($productId === false) {
                return false;
            }

            // Update the product ID in data if needed
            $data->j2commerce_product_id = $productId;

            return true;
        } catch (\Exception $e) {
            $this->getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Delete products linked to a source using the behavior system.
     *
     * This replaces the previous direct SQL approach with a behavior-based approach
     * that ensures proper cleanup of product-type-specific data (e.g., flexivariable
     * variants, downloadable files, etc.) via the onBeforeDelete behavior event.
     *
     * @param   string  $source    The product source (e.g., 'com_content').
     * @param   int     $sourceId  The source ID (e.g., article ID).
     *
     * @return  bool  True on success, false on failure.
     *
     * @since   6.0.0
     * @since   6.0.11 Refactored to use ProductService with behavior system
     */
    private function deleteProductsBySource(string $source, int $sourceId): bool
    {
        try {
            $db = $this->getDatabase();

            // Find product IDs linked to this source
            $query = $db->getQuery(true)
                ->select($db->quoteName('j2commerce_product_id'))
                ->from($db->quoteName('#__j2commerce_products'))
                ->where($db->quoteName('product_source') . ' = :source')
                ->where($db->quoteName('product_source_id') . ' = :sourceId')
                ->bind(':source', $source)
                ->bind(':sourceId', $sourceId, ParameterType::INTEGER);

            $db->setQuery($query);
            $productIds = $db->loadColumn();

            if (empty($productIds)) {
                return true;
            }

            // Use ProductService directly (same pattern as saveProduct)
            // This ensures onBeforeDelete is called for each product type
            // to properly clean up variants, options, images, etc.
            $productService = new ProductService();

            foreach ($productIds as $productId) {
                $productService->deleteProduct((int) $productId);
            }

            return true;
        } catch (\Exception $e) {
            $this->getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }

    /** @since 6.0.0 */
    private function formatPrice(float $price): string
    {
        // Get default currency settings
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('currency_symbol'),
                $db->quoteName('currency_position'),
                $db->quoteName('currency_num_decimals'),
                $db->quoteName('currency_decimal'),
                $db->quoteName('currency_thousands'),
            ])
            ->from($db->quoteName('#__j2commerce_currencies'))
            ->where($db->quoteName('enabled') . ' = 1')
            ->order($db->quoteName('j2commerce_currency_id') . ' ASC')
            ->setLimit(1);

        $db->setQuery($query);
        $currency = $db->loadObject();

        if (!$currency) {
            return number_format($price, 2, '.', ',');
        }

        $formattedPrice = number_format(
            $price,
            (int) ($currency->currency_num_decimals ?? 2),
            $currency->currency_decimal ?? '.',
            $currency->currency_thousands ?? ','
        );

        $symbol = $currency->currency_symbol ?? '$';

        if ($currency->currency_position === 'post') {
            return $formattedPrice . $symbol;
        }

        return $symbol . $formattedPrice;
    }

    /** @since 6.0.0 */
    private function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

}
