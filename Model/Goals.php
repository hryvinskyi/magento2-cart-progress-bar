<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Model;

use Hryvinskyi\CartProgressBar\Api\Data\GoalInterface;
use Hryvinskyi\CartProgressBar\Api\GoalProviderInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * The goals of a store: those of every provider contributed in `di.xml`, lowest priority first
 *
 * Goals of equal priority keep the order of the providers that contributed them.
 */
class Goals implements GoalProviderInterface
{
    /**
     * @param array<string, GoalProviderInterface> $providers Providers by name
     */
    public function __construct(private readonly array $providers = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function goalsFor(StoreInterface $store): array
    {
        $goals = [];
        foreach ($this->providers as $provider) {
            foreach ($provider->goalsFor($store) as $goal) {
                $goals[] = $goal;
            }
        }
        usort(
            $goals,
            static fn (GoalInterface $left, GoalInterface $right): int => $left->getPriority() <=> $right->getPriority()
        );

        return $goals;
    }
}
