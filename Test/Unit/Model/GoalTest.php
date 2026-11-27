<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Test\Unit\Model;

use Hryvinskyi\CartProgressBar\Model\Goal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A goal exists only with a code, an amount above nothing and a path to the shopper's amount
 */
class GoalTest extends TestCase
{
    /**
     * @return void
     */
    public function testAGoalWithEverythingItNeedsCarriesItsLabelsByPhase(): void
    {
        $goal = $this->goal('spend', 70.0, 'goals.spend');

        self::assertSame(
            ['initial' => 'Spend {{goal}}', 'progress' => '{{goal_left}} to go', 'achieved' => 'Done', 'terms' => ''],
            $goal->getLabels()
        );
    }

    /**
     * @param string $code Goal code
     * @param float $amount Goal amount
     * @param string $amountKey Path to the shopper's amount
     * @return void
     */
    #[DataProvider('invalidGoals')]
    public function testAGoalWithoutWhatItNeedsIsRefused(string $code, float $amount, string $amountKey): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->goal($code, $amount, $amountKey);
    }

    /**
     * @return array<string, array{string, float, string}>
     */
    public static function invalidGoals(): array
    {
        return [
            'a blank code' => ['  ', 70.0, 'goals.spend'],
            'an amount of nothing' => ['spend', 0.0, 'goals.spend'],
            'a blank amount key' => ['spend', 70.0, ' '],
        ];
    }

    /**
     * Build a goal with valid labels
     *
     * @param string $code Goal code
     * @param float $amount Goal amount
     * @param string $amountKey Path to the shopper's amount
     * @return Goal
     */
    private function goal(string $code, float $amount, string $amountKey): Goal
    {
        return new Goal(
            code: $code,
            priority: 10,
            amount: $amount,
            amountKey: $amountKey,
            initialLabel: 'Spend {{goal}}',
            progressLabel: '{{goal_left}} to go',
            achievedLabel: 'Done'
        );
    }
}
