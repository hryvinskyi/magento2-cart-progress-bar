<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Model\Config\Source;

use Hryvinskyi\CartProgressBar\Api\PageTypesInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * The page types the admin can choose for the bar at the top of the header
 */
class PageTypes implements OptionSourceInterface
{
    /**
     * @param PageTypesInterface $pageTypes Declared page types
     */
    public function __construct(private readonly PageTypesInterface $pageTypes)
    {
    }

    /**
     * @inheritDoc
     *
     * @return list<array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->pageTypes->labels() as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }

        return $options;
    }
}
