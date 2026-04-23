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

defined('_JEXEC') or die;

use J2Commerce\Component\J2commercemigrator\Administrator\Helper\MigrationLogger;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\JoomlaSourceReader;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\SourceDatabaseReaderInterface;
use Joomla\Database\DatabaseInterface;

/**
 * Lists J2Store frontend menu items, migrates selected items to J2Commerce routes,
 * creates canonical J2Commerce menu items, rollback, and redirect creation.
 */
class MenuMigrator
{
    public function __construct(
        private DatabaseInterface $db,
        private MigrationLogger $logger,
        ?SourceDatabaseReaderInterface $sourceReader = null
    ) {
        // $sourceReader injected but not used directly — menu operations target the live DB
    }

    public function getJ2StoreMenuItems(): array
    {
        try {
            $query = $this->db->getQuery(true)
                ->select(['m.id', 'm.title', 'm.alias', 'm.link', 'm.published', 'm.menutype', 'm.language'])
                ->from($this->db->quoteName('#__menu', 'm'))
                ->where($this->db->quoteName('m.link') . ' LIKE ' . $this->db->quote('%com_j2store%'))
                ->where($this->db->quoteName('m.client_id') . ' = 0')
                ->order($this->db->quoteName('m.title') . ' ASC');

            $items = $this->db->setQuery($query)->loadAssocList();

            foreach ($items as &$item) {
                $parsed = [];
                parse_str(str_replace('index.php?', '', $item['link']), $parsed);
                $item['j2store_view'] = $parsed['view'] ?? 'unknown';

                $item['catids'] = [];
                if (!empty($parsed['catid'])) {
                    $item['catids'] = \is_array($parsed['catid'])
                        ? array_values($parsed['catid'])
                        : [(string) $parsed['catid']];
                }

                $existingId = (int) $this->db->setQuery(
                    $this->db->getQuery(true)
                        ->select('id')
                        ->from($this->db->quoteName('#__menu'))
                        ->where($this->db->quoteName('link') . ' LIKE ' . $this->db->quote('%com_j2commerce%'))
                        ->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($item['alias']))
                        ->where($this->db->quoteName('client_id') . ' = 0')
                )->loadResult();

                $item['already_migrated'] = $existingId > 0;

                $item['suggested_type'] = match ($item['j2store_view']) {
                    'products'  => \count($item['catids']) === 1 ? 'categories' : 'producttags',
                    'checkout'  => 'checkout',
                    'carts'     => 'carts',
                    'myprofile' => 'myprofile',
                    default     => 'producttags',
                };
            }
            unset($item);

            $tags = $this->db->setQuery(
                $this->db->getQuery(true)
                    ->select(['id', 'title'])
                    ->from($this->db->quoteName('#__tags'))
                    ->where($this->db->quoteName('published') . ' = 1')
                    ->where($this->db->quoteName('id') . ' > 1')
                    ->order($this->db->quoteName('title') . ' ASC')
            )->loadAssocList();

            $categories = $this->db->setQuery(
                $this->db->getQuery(true)
                    ->select(['id', 'title'])
                    ->from($this->db->quoteName('#__categories'))
                    ->where($this->db->quoteName('published') . ' >= 0')
                    ->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_content'))
                    ->where($this->db->quoteName('id') . ' > 1')
                    ->order($this->db->quoteName('title') . ' ASC')
            )->loadAssocList();

            return [
                'success'    => true,
                'items'      => $items,
                'tags'       => $tags,
                'categories' => $categories,
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Failed to load J2Store menu items: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function migrateSelected(array $selections): array
    {
        $this->logger->info('Starting selective menu migration for ' . \count($selections) . ' items');

        if (empty($selections)) {
            return ['error' => 'No menu items selected'];
        }

        $j2commerceComponentId = $this->getJ2CommerceComponentId();
        if (!$j2commerceComponentId) {
            return ['error' => 'com_j2commerce component not found'];
        }

        $migrated = 0;
        $skipped  = 0;
        $results  = [];

        $this->db->transactionStart();

        try {
            foreach ($selections as $sel) {
                $menuId     = (int) ($sel['id'] ?? 0);
                $targetType = $sel['targetType'] ?? '';
                $tagId      = (int) ($sel['tagId'] ?? 0);
                $categoryId = (int) ($sel['categoryId'] ?? 0);

                if (!$menuId || !$targetType) {
                    $results[] = ['id' => $menuId, 'skipped' => true, 'message' => 'Missing ID or target type'];
                    $skipped++;
                    continue;
                }

                $item = $this->db->setQuery(
                    $this->db->getQuery(true)
                        ->select('*')
                        ->from($this->db->quoteName('#__menu'))
                        ->where($this->db->quoteName('id') . ' = ' . $menuId)
                )->loadAssoc();

                if (!$item) {
                    $results[] = ['id' => $menuId, 'skipped' => true, 'message' => 'Menu item not found'];
                    $skipped++;
                    continue;
                }

                $newLink = $this->buildJ2CommerceLink($targetType, $tagId, $categoryId);

                if (!$newLink) {
                    $results[] = ['id' => $menuId, 'skipped' => true, 'message' => 'Unknown target type: ' . $targetType];
                    $skipped++;
                    continue;
                }

                $originalAlias = $item['alias'];

                $existingId = (int) $this->db->setQuery(
                    $this->db->getQuery(true)
                        ->select('id')
                        ->from($this->db->quoteName('#__menu'))
                        ->where($this->db->quoteName('link') . ' LIKE ' . $this->db->quote('%com_j2commerce%'))
                        ->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($originalAlias))
                        ->where($this->db->quoteName('client_id') . ' = 0')
                )->loadResult();

                if ($existingId) {
                    $results[] = ['id' => $menuId, 'alias' => $originalAlias, 'skipped' => true, 'message' => 'Already migrated'];
                    $skipped++;
                    continue;
                }

                // Rename old item alias to free it for the new item
                $this->db->setQuery(
                    $this->db->getQuery(true)
                        ->update($this->db->quoteName('#__menu'))
                        ->set($this->db->quoteName('alias') . ' = ' . $this->db->quote($originalAlias . '-j2store'))
                        ->where($this->db->quoteName('id') . ' = ' . $menuId)
                )->execute();

                $newParams = str_replace(
                    ['com_j2store', 'j2store'],
                    ['com_j2commerce', 'j2commerce'],
                    $item['params']
                );

                $newItem = (object) [
                    'menutype'          => $item['menutype'],
                    'title'             => str_replace(['J2Store', 'j2store'], ['J2Commerce', 'j2commerce'], $item['title']),
                    'alias'             => $originalAlias,
                    'note'              => $item['note'] ?: '',
                    'path'              => $item['path'],
                    'link'              => $newLink,
                    'type'              => 'component',
                    'published'         => $item['published'],
                    'parent_id'         => $item['parent_id'],
                    'level'             => $item['level'],
                    'component_id'      => $j2commerceComponentId,
                    'checked_out'       => null,
                    'checked_out_time'  => null,
                    'browserNav'        => $item['browserNav'],
                    'access'            => $item['access'],
                    'img'               => $item['img'] ?: '',
                    'template_style_id' => $item['template_style_id'],
                    'params'            => $newParams,
                    'lft'               => 0,
                    'rgt'               => 0,
                    'home'              => 0,
                    'language'          => $item['language'],
                    'client_id'         => 0,
                ];

                $this->db->insertObject('#__menu', $newItem, 'id');
                $this->createRedirect($item['link'], $newLink);

                $results[] = [
                    'id'       => $menuId,
                    'new_id'   => $newItem->id,
                    'alias'    => $originalAlias,
                    'target'   => $targetType,
                    'new_link' => $newLink,
                ];

                $migrated++;
            }

            $this->db->transactionCommit();
        } catch (\Throwable $e) {
            $this->db->transactionRollback();
            $this->logger->error('Selective menu migration failed: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }

        $this->logger->info("Selective menu migration complete: {$migrated} migrated, {$skipped} skipped");

        return ['success' => true, 'migrated' => $migrated, 'skipped' => $skipped, 'items' => $results];
    }

    public function migrate(): array
    {
        $this->logger->info('Starting menu migration');

        try {
            $query = $this->db->getQuery(true)
                ->select('*')
                ->from($this->db->quoteName('#__menu'))
                ->where($this->db->quoteName('link') . ' LIKE ' . $this->db->quote('%com_j2store%'))
                ->where($this->db->quoteName('client_id') . ' = 0');

            $menuItems = $this->db->setQuery($query)->loadAssocList();

            if (empty($menuItems)) {
                return ['success' => true, 'migrated' => 0, 'message' => 'No J2Store menu items found'];
            }

            $j2commerceComponentId = $this->getJ2CommerceComponentId();
            $migrated = 0;
            $results  = [];

            $this->db->transactionStart();

            try {
                foreach ($menuItems as $item) {
                    $newLink       = str_replace('com_j2store', 'com_j2commerce', $item['link']);
                    $originalAlias = $item['alias'];

                    $existingId = $this->db->setQuery(
                        $this->db->getQuery(true)
                            ->select('id')
                            ->from($this->db->quoteName('#__menu'))
                            ->where($this->db->quoteName('link') . ' = ' . $this->db->quote($newLink))
                            ->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($originalAlias))
                    )->loadResult();

                    if ($existingId) {
                        $results[] = [
                            'original_id' => $item['id'],
                            'alias'       => $originalAlias,
                            'skipped'     => true,
                            'message'     => 'J2Commerce menu item already exists',
                        ];
                        continue;
                    }

                    $this->db->setQuery(
                        $this->db->getQuery(true)
                            ->update($this->db->quoteName('#__menu'))
                            ->set($this->db->quoteName('alias') . ' = ' . $this->db->quote($originalAlias . '-j2store'))
                            ->where($this->db->quoteName('id') . ' = ' . (int) $item['id'])
                    )->execute();

                    $newItem = (object) [
                        'menutype'          => $item['menutype'],
                        'title'             => str_replace(['J2Store', 'j2store'], ['J2Commerce', 'j2commerce'], $item['title']),
                        'alias'             => $originalAlias,
                        'note'              => $item['note'] ?: '',
                        'path'              => $item['path'],
                        'link'              => $newLink,
                        'type'              => $item['type'],
                        'published'         => $item['published'],
                        'parent_id'         => $item['parent_id'],
                        'level'             => $item['level'],
                        'component_id'      => $j2commerceComponentId,
                        'checked_out'       => null,
                        'checked_out_time'  => null,
                        'browserNav'        => $item['browserNav'],
                        'access'            => $item['access'],
                        'img'               => $item['img'] ?: '',
                        'template_style_id' => $item['template_style_id'],
                        'params'            => str_replace(
                            ['com_j2store', 'j2store'],
                            ['com_j2commerce', 'j2commerce'],
                            $item['params']
                        ),
                        'lft'               => 0,
                        'rgt'               => 0,
                        'home'              => 0,
                        'language'          => $item['language'],
                        'client_id'         => 0,
                    ];

                    $this->db->insertObject('#__menu', $newItem, 'id');
                    $this->createRedirect($item['link'], $newLink);

                    $results[] = [
                        'original_id'   => $item['id'],
                        'new_id'        => $newItem->id,
                        'alias'         => $originalAlias,
                        'original_link' => $item['link'],
                        'new_link'      => $newLink,
                    ];

                    $migrated++;
                }

                $this->db->transactionCommit();
            } catch (\Throwable $e) {
                $this->db->transactionRollback();
                throw $e;
            }

            $this->logger->info("Menu migration complete: {$migrated} items");

            return ['success' => true, 'migrated' => $migrated, 'items' => $results];
        } catch (\Throwable $e) {
            $this->logger->error('Menu migration failed: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function createMenuItems(): array
    {
        $this->logger->info('Creating J2Commerce menu items');

        try {
            $menutype = 'j2commerce';

            $exists = (int) $this->db->setQuery(
                $this->db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($this->db->quoteName('#__menu_types'))
                    ->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote($menutype))
            )->loadResult();

            if (!$exists) {
                $menuTypeObj = (object) [
                    'menutype'    => $menutype,
                    'title'       => 'J2Commerce',
                    'description' => 'J2Commerce store menu items',
                    'client_id'   => 0,
                ];
                $this->db->insertObject('#__menu_types', $menuTypeObj);
                $this->logger->info('Created menu type: j2commerce');
            }

            $componentId = $this->getJ2CommerceComponentId();
            if (!$componentId) {
                return ['error' => 'com_j2commerce component not found'];
            }

            $items = [
                [
                    'title' => 'Checkout',
                    'alias' => 'checkout',
                    'link'  => 'index.php?option=com_j2commerce&view=checkout',
                ],
                [
                    'title' => 'Shopping Cart',
                    'alias' => 'shopping-cart',
                    'link'  => 'index.php?option=com_j2commerce&view=carts',
                ],
                [
                    'title' => 'My Account',
                    'alias' => 'my-account',
                    'link'  => 'index.php?option=com_j2commerce&view=myprofile',
                ],
                [
                    'title' => 'Order Confirmation',
                    'alias' => 'order-confirmation',
                    'link'  => 'index.php?option=com_j2commerce&view=confirmation',
                ],
            ];

            $created = 0;
            $skipped = 0;
            $results = [];

            $this->db->transactionStart();

            try {
                foreach ($items as $item) {
                    $existingId = (int) $this->db->setQuery(
                        $this->db->getQuery(true)
                            ->select('id')
                            ->from($this->db->quoteName('#__menu'))
                            ->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote($menutype))
                            ->where($this->db->quoteName('link') . ' = ' . $this->db->quote($item['link']))
                    )->loadResult();

                    if ($existingId) {
                        $results[] = ['alias' => $item['alias'], 'skipped' => true, 'existing_id' => $existingId];
                        $skipped++;
                        continue;
                    }

                    $alias     = $item['alias'];
                    $baseAlias = $alias;
                    $counter   = 2;

                    while ((int) $this->db->setQuery(
                        $this->db->getQuery(true)
                            ->select('id')
                            ->from($this->db->quoteName('#__menu'))
                            ->where($this->db->quoteName('client_id') . ' = 0')
                            ->where($this->db->quoteName('parent_id') . ' = 1')
                            ->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias))
                            ->where($this->db->quoteName('language') . ' = ' . $this->db->quote('*'))
                    )->loadResult()) {
                        $alias = $baseAlias . '-' . $counter++;
                    }

                    $newItem = (object) [
                        'menutype'          => $menutype,
                        'title'             => $item['title'],
                        'alias'             => $alias,
                        'note'              => '',
                        'path'              => $alias,
                        'link'              => $item['link'],
                        'type'              => 'component',
                        'published'         => 1,
                        'parent_id'         => 1,
                        'level'             => 1,
                        'component_id'      => $componentId,
                        'checked_out'       => null,
                        'checked_out_time'  => null,
                        'browserNav'        => 0,
                        'access'            => 1,
                        'img'               => '',
                        'template_style_id' => 0,
                        'params'            => '{}',
                        'lft'               => 0,
                        'rgt'               => 0,
                        'home'              => 0,
                        'language'          => '*',
                        'client_id'         => 0,
                    ];

                    $this->db->insertObject('#__menu', $newItem, 'id');
                    $results[] = ['alias' => $item['alias'], 'new_id' => $newItem->id];
                    $created++;
                }

                $this->db->transactionCommit();
            } catch (\Throwable $e) {
                $this->db->transactionRollback();
                throw $e;
            }

            $this->logger->info("Created {$created} J2Commerce menu items, {$skipped} skipped");

            return ['success' => true, 'created' => $created, 'skipped' => $skipped, 'items' => $results];
        } catch (\Throwable $e) {
            $this->logger->error('Create menu items failed: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function rollback(): array
    {
        $this->logger->info('Starting menu migration rollback');

        try {
            $this->db->transactionStart();

            try {
                $j2cItems = $this->db->setQuery(
                    $this->db->getQuery(true)
                        ->select(['id', 'alias', 'menutype'])
                        ->from($this->db->quoteName('#__menu'))
                        ->where($this->db->quoteName('link') . ' LIKE ' . $this->db->quote('%com_j2commerce%'))
                        ->where($this->db->quoteName('client_id') . ' = 0')
                )->loadAssocList();

                $deleted  = 0;
                $restored = 0;

                foreach ($j2cItems as $j2cItem) {
                    $originalId = (int) $this->db->setQuery(
                        $this->db->getQuery(true)
                            ->select('id')
                            ->from($this->db->quoteName('#__menu'))
                            ->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($j2cItem['alias'] . '-j2store'))
                            ->where($this->db->quoteName('menutype') . ' = ' . $this->db->quote($j2cItem['menutype']))
                            ->where($this->db->quoteName('link') . ' LIKE ' . $this->db->quote('%com_j2store%'))
                    )->loadResult();

                    $this->db->setQuery(
                        $this->db->getQuery(true)
                            ->delete($this->db->quoteName('#__menu'))
                            ->where($this->db->quoteName('id') . ' = ' . (int) $j2cItem['id'])
                    )->execute();
                    $deleted++;

                    if ($originalId) {
                        $this->db->setQuery(
                            $this->db->getQuery(true)
                                ->update($this->db->quoteName('#__menu'))
                                ->set($this->db->quoteName('alias') . ' = ' . $this->db->quote($j2cItem['alias']))
                                ->where($this->db->quoteName('id') . ' = ' . $originalId)
                        )->execute();
                        $restored++;
                    }
                }

                $redirectsDeleted = $this->deleteRedirects();

                $this->db->transactionCommit();
            } catch (\Throwable $e) {
                $this->db->transactionRollback();
                throw $e;
            }

            $this->logger->info("Menu rollback complete: {$deleted} deleted, {$restored} restored, {$redirectsDeleted} redirects removed");

            return [
                'success'           => true,
                'deleted'           => $deleted,
                'restored'          => $restored,
                'redirects_removed' => $redirectsDeleted,
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Menu rollback failed: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    private function buildJ2CommerceLink(string $targetType, int $tagId, int $categoryId): string
    {
        return match ($targetType) {
            'categories'    => 'index.php?option=com_j2commerce&view=categories' . ($categoryId ? '&id=' . $categoryId : ''),
            'producttags'   => 'index.php?option=com_j2commerce&view=producttags' . ($tagId ? '&tag_id=' . $tagId : ''),
            'categoryalias' => 'index.php?option=com_j2commerce&view=categoryalias' . ($categoryId ? '&id=' . $categoryId : ''),
            'product'       => 'index.php?option=com_j2commerce&view=product',
            'checkout'      => 'index.php?option=com_j2commerce&view=checkout',
            'carts'         => 'index.php?option=com_j2commerce&view=carts',
            'myprofile'     => 'index.php?option=com_j2commerce&view=myprofile',
            'confirmation'  => 'index.php?option=com_j2commerce&view=confirmation',
            default         => '',
        };
    }

    private function getJ2CommerceComponentId(): int
    {
        return (int) $this->db->setQuery(
            $this->db->getQuery(true)
                ->select('extension_id')
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_j2commerce'))
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
        )->loadResult();
    }

    private function createRedirect(string $oldUrl, string $newUrl): void
    {
        try {
            $tableName = $this->db->getPrefix() . 'redirect_links';
            $query     = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('INFORMATION_SCHEMA.TABLES'))
                ->where($this->db->quoteName('TABLE_SCHEMA') . ' = DATABASE()')
                ->where($this->db->quoteName('TABLE_NAME') . ' = :tableName')
                ->bind(':tableName', $tableName);

            if (!(int) $this->db->setQuery($query)->loadResult()) {
                return;
            }

            $redirect = (object) [
                'old_url'       => $oldUrl,
                'new_url'       => $newUrl,
                'referer'       => '',
                'comment'       => 'J2Store to J2Commerce migration',
                'hits'          => 0,
                'published'     => 1,
                'created_date'  => date('Y-m-d H:i:s'),
                'modified_date' => date('Y-m-d H:i:s'),
                'header'        => 301,
            ];

            $this->db->insertObject('#__redirect_links', $redirect);
        } catch (\Throwable $e) {
            $this->logger->warning('Could not create redirect: ' . $e->getMessage());
        }
    }

    private function deleteRedirects(): int
    {
        try {
            $tableName = $this->db->getPrefix() . 'redirect_links';
            $query     = $this->db->getQuery(true)
                ->select('COUNT(*)')
                ->from($this->db->quoteName('INFORMATION_SCHEMA.TABLES'))
                ->where($this->db->quoteName('TABLE_SCHEMA') . ' = DATABASE()')
                ->where($this->db->quoteName('TABLE_NAME') . ' = :tableName')
                ->bind(':tableName', $tableName);

            if (!(int) $this->db->setQuery($query)->loadResult()) {
                return 0;
            }

            $this->db->setQuery(
                $this->db->getQuery(true)
                    ->delete($this->db->quoteName('#__redirect_links'))
                    ->where($this->db->quoteName('comment') . ' = ' . $this->db->quote('J2Store to J2Commerce migration'))
            )->execute();

            return (int) $this->db->getAffectedRows();
        } catch (\Throwable $e) {
            $this->logger->warning('Could not clean up redirects: ' . $e->getMessage());
            return 0;
        }
    }
}
