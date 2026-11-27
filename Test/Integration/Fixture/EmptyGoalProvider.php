<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Test\Integration\Fixture;

use Hryvinskyi\CartProgressBar\Api\GoalProviderInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * A provider that has no goal to show in any store
 */
class EmptyGoalProvider implements GoalProviderInterface
{
    /**
     * @inheritDoc
     */
    public function goalsFor(StoreInterface $store): array
    {
        return [];
    }
}
