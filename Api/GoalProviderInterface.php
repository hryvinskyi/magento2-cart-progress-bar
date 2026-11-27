<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Api;

use Hryvinskyi\CartProgressBar\Api\Data\GoalInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Contributes the spend goals of a store to the bar
 *
 * A module that has a goal to show adds its provider to the `providers` argument of
 * `Hryvinskyi\CartProgressBar\Model\Goals` in its `di.xml`. The goals are read when a page is rendered and printed
 * into HTML the full-page cache shares, so a provider returns data of the store only, never of the visitor.
 *
 * @api
 */
interface GoalProviderInterface
{
    /**
     * Get the goals the store shows
     *
     * @param StoreInterface $store Store whose page is rendered
     * @return list<GoalInterface> Goals in any order; an empty list when there is nothing to show
     */
    public function goalsFor(StoreInterface $store): array;
}
