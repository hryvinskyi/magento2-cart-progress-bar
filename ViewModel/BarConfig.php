<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\ViewModel;

use Hryvinskyi\CartProgressBar\Api\Data\GoalInterface;
use Hryvinskyi\CartProgressBar\Api\GoalProviderInterface;
use Hryvinskyi\CartProgressBar\Api\LinkPolicyInterface;
use Hryvinskyi\CartProgressBar\Api\PageTypesInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * What the bar's templates print for the current store: whether the bar shows, where, and its configuration
 *
 * Everything here is the same for every visitor of the store, because it is printed into pages the full-page cache
 * shares; the shopper's own amount is read in the browser. The goals and the configuration are read once per
 * instance, which runs on a page-cache miss only.
 *
 * @phpstan-type Labels array{initial: string, progress: string, achieved: string, terms: string}
 * @phpstan-type GoalConfig array{
 *     code: string,
 *     priority: int,
 *     amount: float,
 *     amountKey: string,
 *     labels: Labels,
 *     link: string,
 *     image: string
 * }
 * @phpstan-type Config array{goals: list<GoalConfig>, priceFormat: array<string, mixed>}
 */
class BarConfig implements ArgumentInterface
{
    private const XML_PATH_ENABLED = 'cart_progress_bar/general/enabled';
    private const XML_PATH_PAGE_TYPES = 'cart_progress_bar/general/page_types';

    /**
     * @var list<GoalInterface>|null
     */
    private ?array $goals = null;

    /**
     * @var Config|null
     */
    private ?array $config = null;

    /**
     * @param ScopeConfigInterface $scopeConfig Reads the store's settings
     * @param StoreManagerInterface $storeManager Gives the current store
     * @param GoalProviderInterface $goalProvider Gives the goals of a store
     * @param FormatInterface $localeFormat Gives the price format of the current store
     * @param PageTypesInterface $pageTypes Maps page types to the pages they stand for
     * @param SerializerInterface $serializer JSON serializer that escapes markup, so the configuration can be printed
     *     inside a script element
     * @param LinkPolicyInterface $linkPolicy Decides which links the bar prints as the target of a link
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly GoalProviderInterface $goalProvider,
        private readonly FormatInterface $localeFormat,
        private readonly PageTypesInterface $pageTypes,
        private readonly SerializerInterface $serializer,
        private readonly LinkPolicyInterface $linkPolicy
    ) {
    }

    /**
     * Check whether the bar shows in the current store: it is enabled and at least one goal exists
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            && $this->goals() !== [];
    }

    /**
     * Check whether the bar shows at the top of the header on a page
     *
     * @param string $fullActionName Full action name of the page, e.g. `cms_index_index`
     * @return bool True when the bar shows and the page is of a page type chosen for the top
     */
    public function isShownAtTopOn(string $fullActionName): bool
    {
        return $this->isEnabled() && in_array($fullActionName, $this->topActions(), true);
    }

    /**
     * Get the configuration of the bar for the browser
     *
     * @return Config
     */
    public function config(): array
    {
        return $this->config ??= [
            'goals' => array_map(fn (GoalInterface $goal): array => $this->goalConfig($goal), $this->goals()),
            'priceFormat' => $this->priceFormat(),
        ];
    }

    /**
     * Get the configuration of the bar as JSON safe to print inside a script element
     *
     * @return string
     * @throws \InvalidArgumentException When the configuration cannot be serialized
     */
    public function configJson(): string
    {
        $json = $this->serializer->serialize($this->config());
        if (!is_string($json)) {
            throw new \InvalidArgumentException('The configuration of the cart progress bar cannot be serialized.');
        }

        return $json;
    }

    /**
     * Get the configuration of one goal for the browser
     *
     * @param GoalInterface $goal Goal of the store
     * @return GoalConfig
     */
    private function goalConfig(GoalInterface $goal): array
    {
        $link = $goal->getLink();

        return [
            'code' => $goal->getCode(),
            'priority' => $goal->getPriority(),
            'amount' => $goal->getAmount(),
            'amountKey' => $goal->getAmountKey(),
            'labels' => $goal->getLabels(),
            'link' => $this->linkPolicy->accepts($link) ? $link : '',
            'image' => $goal->getImage(),
        ];
    }

    /**
     * Get the goals of the current store
     *
     * @return list<GoalInterface>
     */
    private function goals(): array
    {
        return $this->goals ??= $this->goalProvider->goalsFor($this->storeManager->getStore());
    }

    /**
     * Get the full action names of the pages chosen for the top of the header
     *
     * @return list<string>
     */
    private function topActions(): array
    {
        $codes = $this->scopeConfig->getValue(self::XML_PATH_PAGE_TYPES, ScopeInterface::SCOPE_STORE);
        $actions = [];
        foreach (explode(',', is_string($codes) ? $codes : '') as $code) {
            $action = $this->pageTypes->actionOf(trim($code));
            if ($action !== null) {
                $actions[] = $action;
            }
        }

        return $actions;
    }

    /**
     * Get the price format of the current store's currency, keyed by name
     *
     * @return array<string, mixed>
     */
    private function priceFormat(): array
    {
        $format = [];
        foreach ($this->localeFormat->getPriceFormat() as $name => $value) {
            if (is_string($name)) {
                $format[$name] = $value;
            }
        }

        return $format;
    }
}
