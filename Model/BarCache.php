<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Model;

use Hryvinskyi\CartProgressBar\Api\BarCacheInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface;

/**
 * Cleans the cached pages that hold the bar by announcing the bar's cache tag on `clean_cache_by_tags`
 *
 * The platform's page cache listeners clean what that event names: the built-in full-page cache removes the pages
 * stored with the tag, and a caching proxy is sent a purge for it. The object announced is this service, whose
 * identity is the bar's tag.
 */
class BarCache implements BarCacheInterface, IdentityInterface
{
    private const EVENT = 'clean_cache_by_tags';

    /**
     * @param ManagerInterface $eventManager Dispatches events
     */
    public function __construct(
        private readonly ManagerInterface $eventManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function clean(): void
    {
        $this->eventManager->dispatch(self::EVENT, ['object' => $this]);
    }

    /**
     * Get the tags the clean names
     *
     * @return list<string>
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG];
    }
}
