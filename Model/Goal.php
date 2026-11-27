<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Model;

use Hryvinskyi\CartProgressBar\Api\Data\GoalInterface;

/**
 * A spend goal, refused at construction unless it has a code, an amount above nothing and a path to the shopper's
 * amount
 *
 * Providers create it through the generated `GoalInterfaceFactory`, passing the constructor arguments by name.
 */
class Goal implements GoalInterface
{
    /**
     * @param string $code Code of the goal; must not be blank
     * @param int $priority Priority; lower first
     * @param float $amount Amount to spend in the store's current currency; must be above nothing
     * @param string $amountKey Dot path into the cart section to the shopper's amount; must not be blank
     * @param string $initialLabel Wording while the cart holds nothing towards the goal
     * @param string $progressLabel Wording while the cart is on its way to the goal
     * @param string $achievedLabel Wording once the cart reaches the goal
     * @param string $termsLabel Terms of the goal
     * @param string $link Address the bar links to, or an empty string
     * @param string $image Address of the image beside the text, or an empty string
     * @throws \InvalidArgumentException When the code or the amount key is blank, or the amount is not above nothing
     */
    public function __construct(
        private readonly string $code,
        private readonly int $priority,
        private readonly float $amount,
        private readonly string $amountKey,
        private readonly string $initialLabel,
        private readonly string $progressLabel,
        private readonly string $achievedLabel,
        private readonly string $termsLabel = '',
        private readonly string $link = '',
        private readonly string $image = ''
    ) {
        if (trim($code) === '') {
            throw new \InvalidArgumentException('A spend goal needs a code; the code is blank.');
        }
        if (!($amount > 0.0)) {
            throw new \InvalidArgumentException(sprintf(
                'Spend goal "%s" needs an amount above nothing; the amount is %s.',
                $code,
                (string)$amount
            ));
        }
        if (trim($amountKey) === '') {
            throw new \InvalidArgumentException(sprintf(
                'Spend goal "%s" needs the path to the shopper\'s amount in the cart section; the path is blank.',
                $code
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function getAmountKey(): string
    {
        return $this->amountKey;
    }

    /**
     * @inheritDoc
     */
    public function getLabels(): array
    {
        return [
            'initial' => $this->initialLabel,
            'progress' => $this->progressLabel,
            'achieved' => $this->achievedLabel,
            'terms' => $this->termsLabel,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @inheritDoc
     */
    public function getImage(): string
    {
        return $this->image;
    }
}
