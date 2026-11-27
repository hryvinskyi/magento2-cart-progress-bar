<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Test\Integration\Fixture;

use Hryvinskyi\CartProgressBar\Api\Data\GoalInterface;
use Hryvinskyi\CartProgressBar\Api\Data\GoalInterfaceFactory;
use Hryvinskyi\CartProgressBar\Api\GoalProviderInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Contributes one spend goal of 70 to every store, created through the module's goal factory
 */
class TestGoalProvider implements GoalProviderInterface
{
    public const AMOUNT = 70.0;
    public const AMOUNT_KEY = 'test_amount';
    public const INITIAL_LABEL = 'Spend {{goal}} & get a FREE lamp!';
    public const PROGRESS_LABEL = 'Spend {{goal_left}} more to unlock your FREE lamp!';
    public const ACHIEVED_LABEL = 'You have unlocked your FREE lamp!';

    /**
     * @param GoalInterfaceFactory $goalFactory Creates goals
     */
    public function __construct(private readonly GoalInterfaceFactory $goalFactory)
    {
    }

    /**
     * @inheritDoc
     */
    public function goalsFor(StoreInterface $store): array
    {
        return [$this->goalFactory->create([
            'code' => 'test',
            'priority' => 10,
            'amount' => self::AMOUNT,
            'amountKey' => self::AMOUNT_KEY,
            'initialLabel' => self::INITIAL_LABEL,
            'progressLabel' => self::PROGRESS_LABEL,
            'achievedLabel' => self::ACHIEVED_LABEL,
        ])];
    }
}
