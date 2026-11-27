<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Model;

use Hryvinskyi\CartProgressBar\Api\PageTypesInterface;

/**
 * The page types declared in `di.xml`: each code with the full action name of its page and its label
 *
 * A label is declared in English and translated when it is read, in the locale of the request.
 */
class PageTypes implements PageTypesInterface
{
    /**
     * @param array<string, array{action: string, label: string}> $pageTypes Page types by code
     */
    public function __construct(private readonly array $pageTypes = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function actionOf(string $code): ?string
    {
        return $this->pageTypes[$code]['action'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function labels(): array
    {
        return array_map(
            static fn (array $pageType): string => (string)__($pageType['label']),
            $this->pageTypes
        );
    }
}
