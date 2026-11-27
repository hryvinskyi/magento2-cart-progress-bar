<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Api;

/**
 * The cached pages that hold the bar
 *
 * Every page that prints the bar's configuration or its top placement carries the tag `CACHE_TAG` in the full-page
 * cache. A module whose goals are built from data it owns calls `clean()` when that data changes, so cached pages stop
 * showing the old goal.
 *
 * @api
 */
interface BarCacheInterface
{
    /**
     * Tag of every cached page that holds the bar
     */
    public const CACHE_TAG = 'cart_progress_bar';

    /**
     * Invalidate every cached page that holds the bar, in the built-in full-page cache and in a caching proxy alike
     *
     * @return void
     */
    public function clean(): void;
}
