<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Model;

use Hryvinskyi\CartProgressBar\Api\LinkPolicyInterface;

/**
 * Accepts an address on the web over http or https, and a path on this site that starts with a single slash
 *
 * A path that starts with two slashes is another site written without its scheme, and any other scheme (a script, a
 * file transfer) is refused.
 */
class LinkPolicy implements LinkPolicyInterface
{
    private const WEB_ADDRESS = '#^(https?://|/(?!/))#i';

    /**
     * @inheritDoc
     */
    public function accepts(string $link): bool
    {
        return preg_match(self::WEB_ADDRESS, $link) === 1;
    }
}
