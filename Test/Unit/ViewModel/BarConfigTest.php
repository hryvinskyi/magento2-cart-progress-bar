<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Test\Unit\ViewModel;

use Hryvinskyi\CartProgressBar\Api\Data\GoalInterface;
use Hryvinskyi\CartProgressBar\Api\GoalProviderInterface;
use Hryvinskyi\CartProgressBar\Api\LinkPolicyInterface;
use Hryvinskyi\CartProgressBar\Api\PageTypesInterface;
use Hryvinskyi\CartProgressBar\ViewModel\BarConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The link of a goal reaches the page as the target of a link, so only a link the bar's link policy accepts goes
 * through; any other leaves the goal without a link
 */
class BarConfigTest extends TestCase
{
    private const LINK = 'https://shop.example/lamp.html';

    /**
     * @param bool $accepted Whether the link policy accepts the goal's link
     * @param string $expected Link the bar's configuration holds
     * @return void
     */
    #[DataProvider('links')]
    public function testOnlyALinkThePolicyAcceptsReachesTheBar(bool $accepted, string $expected): void
    {
        $config = $this->barConfig(self::LINK, $accepted)->config();

        self::assertSame($expected, $config['goals'][0]['link'] ?? null);
    }

    /**
     * @return array<string, array{bool, string}>
     */
    public static function links(): array
    {
        return [
            'a link the policy accepts' => [true, self::LINK],
            'a link the policy refuses' => [false, ''],
        ];
    }

    /**
     * Build the view model over one goal carrying the given link
     *
     * @param string $link Link the goal carries
     * @param bool $accepted Whether the link policy accepts that link
     * @return BarConfig
     */
    private function barConfig(string $link, bool $accepted): BarConfig
    {
        $goal = $this->createStub(GoalInterface::class);
        $goal->method('getCode')->willReturn('spend');
        $goal->method('getAmount')->willReturn(70.0);
        $goal->method('getAmountKey')->willReturn('goals.spend');
        $goal->method('getLabels')->willReturn(
            ['initial' => 'Spend {{goal}}', 'progress' => '{{goal_left}} to go', 'achieved' => 'Done', 'terms' => '']
        );
        $goal->method('getLink')->willReturn($link);
        $goal->method('getImage')->willReturn('');

        $goals = $this->createStub(GoalProviderInterface::class);
        $goals->method('goalsFor')->willReturn([$goal]);

        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($this->createStub(StoreInterface::class));

        $localeFormat = $this->createStub(FormatInterface::class);
        $localeFormat->method('getPriceFormat')->willReturn(['pattern' => '£%s', 'precision' => 2]);

        $linkPolicy = $this->createStub(LinkPolicyInterface::class);
        $linkPolicy->method('accepts')->willReturnMap([[$link, $accepted]]);

        return new BarConfig(
            $this->createStub(ScopeConfigInterface::class),
            $storeManager,
            $goals,
            $localeFormat,
            $this->createStub(PageTypesInterface::class),
            $this->createStub(SerializerInterface::class),
            $linkPolicy
        );
    }
}
