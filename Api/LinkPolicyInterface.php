<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Api;

/**
 * Decides which links the bar prints as the target of a link
 *
 * The bar prints a goal's link only when this policy accepts it, and leaves the goal without a link otherwise. A
 * setting that holds a goal's link checks it here too, so it saves exactly the links the bar prints.
 *
 * @api
 */
interface LinkPolicyInterface
{
    /**
     * Check whether the bar may print a link as the target of a link
     *
     * @param string $link Link to check
     * @return bool True for an address on the web over http or https, or a path on this site that starts with a
     *     single slash
     */
    public function accepts(string $link): bool;
}
