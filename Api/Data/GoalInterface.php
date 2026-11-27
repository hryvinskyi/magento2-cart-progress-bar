<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Api\Data;

/**
 * A spend goal the bar shows to every visitor of a store
 *
 * It holds only what is the same for every visitor: the shopper's own amount towards the goal is read in the
 * browser from the cart section, at the path the goal names. A goal is valid by construction: it has a code, an
 * amount above nothing and a path to the shopper's amount.
 *
 * @api
 */
interface GoalInterface
{
    /**
     * Get the code that tells the goal from the other goals of the store
     *
     * @return string Non-empty code
     */
    public function getCode(): string;

    /**
     * Get the priority of the goal; the bar considers goals of lower priority first
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Get the amount to spend, in the store's current currency
     *
     * @return float Amount above nothing
     */
    public function getAmount(): float;

    /**
     * Get the dot path into the cart customer-data section that holds the shopper's amount towards the goal
     *
     * The amount at that path is in the cart currency, for example `subtotalAmount` or `some_module.12`.
     *
     * @return string Non-empty path
     */
    public function getAmountKey(): string;

    /**
     * Get the wording of the goal by phase, and its terms
     *
     * The wording may hold the placeholders `{{goal}}` (the amount of the goal) and `{{goal_left}}` (the amount
     * still to spend). It is shown as text, never as markup.
     *
     * @return array{initial: string, progress: string, achieved: string, terms: string}
     */
    public function getLabels(): array;

    /**
     * Get the address the bar links to
     *
     * @return string Address, or an empty string for no link
     */
    public function getLink(): string;

    /**
     * Get the address of the image the bar shows beside its text
     *
     * @return string Address, or an empty string for no image
     */
    public function getImage(): string;
}
