<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Block;

use Hryvinskyi\CartProgressBar\Api\BarCacheInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

/**
 * A template block of the bar that tags the page it renders in with the bar's cache tag
 *
 * The full-page cache stores the page under the tag, so `BarCacheInterface::clean()` reaches every cached page that
 * holds the bar. What the block prints comes from its template and view model.
 */
class Bar extends Template implements IdentityInterface
{
    /**
     * Get the cache tags of the page that holds the block
     *
     * @return list<string>
     */
    public function getIdentities(): array
    {
        return [BarCacheInterface::CACHE_TAG];
    }
}
