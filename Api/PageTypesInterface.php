<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Api;

/**
 * The page types an admin can choose for the bar at the top of the header
 *
 * Each page type is a code saved in the configuration, the label the admin reads, and the full action name of the
 * page it stands for. A module adds a page type with an item in the `pageTypes` argument of the implementation.
 */
interface PageTypesInterface
{
    /**
     * Get the full action name of the page a page type stands for
     *
     * @param string $code Page type code as saved in the configuration
     * @return string|null Full action name, or null for a code no page type has
     */
    public function actionOf(string $code): ?string;

    /**
     * Get the label of every page type
     *
     * @return array<string, string> Translated labels by page type code
     */
    public function labels(): array;
}
