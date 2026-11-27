<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Api\Data;

/**
 * Declaration of the factory Magento generates for GoalInterface
 * (`setup:di:compile` writes it to `generated/code`), so static analysis runs on an installation that has
 * not been compiled. Loaded through `scanFiles`, which reads the declaration without executing it.
 */
class GoalInterfaceFactory
{
    /**
     * Create an instance
     *
     * @param array<string, mixed> $data constructor arguments by parameter name
     * @return GoalInterface
     */
    public function create(array $data = []): GoalInterface
    {
        throw new \BadMethodCallException('Declaration for static analysis only.');
    }
}
