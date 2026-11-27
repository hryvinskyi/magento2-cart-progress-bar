<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Test\Unit\Model;

use Hryvinskyi\CartProgressBar\Api\Data\GoalInterface;
use Hryvinskyi\CartProgressBar\Api\GoalProviderInterface;
use Hryvinskyi\CartProgressBar\Model\Goals;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;

/**
 * The goals of a store are those of every contributing module, lowest priority first
 */
class GoalsTest extends TestCase
{
    /**
     * @return void
     */
    public function testTheGoalsOfEveryProviderComeTogetherLowestPriorityFirst(): void
    {
        $later = $this->goal('later', 20);
        $first = $this->goal('first', 10);
        $goals = new Goals([
            'one' => $this->provider([$later]),
            'two' => $this->provider([$first]),
        ]);

        $codes = array_map(
            static fn (GoalInterface $goal): string => $goal->getCode(),
            $goals->goalsFor($this->createStub(StoreInterface::class))
        );

        self::assertSame(['first', 'later'], $codes);
    }

    /**
     * Build a goal with a code and a priority
     *
     * @param string $code Goal code
     * @param int $priority Goal priority
     * @return GoalInterface
     */
    private function goal(string $code, int $priority): GoalInterface
    {
        $goal = $this->createStub(GoalInterface::class);
        $goal->method('getCode')->willReturn($code);
        $goal->method('getPriority')->willReturn($priority);

        return $goal;
    }

    /**
     * Build a provider that contributes the given goals
     *
     * @param list<GoalInterface> $goals Goals the provider contributes
     * @return GoalProviderInterface
     */
    private function provider(array $goals): GoalProviderInterface
    {
        $provider = $this->createStub(GoalProviderInterface::class);
        $provider->method('goalsFor')->willReturn($goals);

        return $provider;
    }
}
