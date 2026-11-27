<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Test\Unit\Model;

use Hryvinskyi\CartProgressBar\Model\LinkPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The bar links only to an address on the web, over http or https, or to a path on this site that starts with a
 * single slash
 */
class LinkPolicyTest extends TestCase
{
    /**
     * @param string $link Link to check
     * @param bool $accepted Whether the bar may print it as the target of a link
     * @return void
     */
    #[DataProvider('links')]
    public function testOnlyAWebAddressOrAPathOnThisSiteIsAccepted(string $link, bool $accepted): void
    {
        self::assertSame($accepted, (new LinkPolicy())->accepts($link));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function links(): array
    {
        return [
            'an absolute address on the web' => ['https://shop.example/lamp.html', true],
            'an address on the web over plain http' => ['http://shop.example/lamp.html', true],
            'a path on this site' => ['/lamp.html', true],
            'another site written without its scheme' => ['//elsewhere.example/lamp.html', false],
            'a file transfer address' => ['ftp://shop.example/lamp.html', false],
            'a script' => ['javascript:alert(1)', false],
        ];
    }
}
