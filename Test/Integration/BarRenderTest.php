<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\CartProgressBar\Test\Integration;

use Hryvinskyi\CartProgressBar\Api\BarCacheInterface;
use Hryvinskyi\CartProgressBar\Api\GoalProviderInterface;
use Hryvinskyi\CartProgressBar\Test\Integration\Fixture\EmptyGoalProvider;
use Hryvinskyi\CartProgressBar\Test\Integration\Fixture\TestGoalProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\PageCache\IdentifierInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\PageCache\Model\Cache\Type as FullPageCache;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Cache;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Storefront pages as a guest receives them, through the real layout, view model, templates and asset renderer,
 * with one spend goal contributed by a provider registered for the test: the configuration block, the inline style
 * in the head and the placement at the top of the header where the store enables the bar, and nothing of it where
 * the bar is off or no module has a goal to show; and the page the built-in full-page cache stores with the bar,
 * which the bar's cache port cleans.
 */
#[AppArea('frontend')]
class BarRenderTest extends AbstractController
{
    private const STORE = 'default';
    private const TOP_PLACEMENT = 'data-role="cart-progress-bar-top"';
    private const MINICART_COMPONENT = '"cart-progress-bar-minicart"';
    private const CONFIG_BLOCK = '#<script type="application/json" data-role="cart-progress-bar-config">(.*?)</script>#s';

    /**
     * @return void
     */
    #[
        AppIsolation(true),
        Config('cart_progress_bar/general/enabled', '1', 'store', self::STORE),
        Config('cart_progress_bar/general/page_types', 'home,category,search,cart', 'store', self::STORE)
    ]
    public function testTheHomePageCarriesTheGoalStyleAndTopPlacementAndNothingAboutTheCart(): void
    {
        $this->contributeGoalsFrom(TestGoalProvider::class);

        $this->dispatch('/');
        $body = $this->body();

        $config = $this->configBlock($body);
        self::assertSame(['goals', 'priceFormat'], array_keys($config));
        $goals = $config['goals'];
        self::assertIsArray($goals);
        self::assertCount(1, $goals);
        $goal = $goals[0];
        self::assertIsArray($goal);
        self::assertSame(
            ['code', 'priority', 'amount', 'amountKey', 'labels', 'link', 'image'],
            array_keys($goal),
            'the block holds the goal as every visitor sees it, and nothing about the cart'
        );
        self::assertEquals(TestGoalProvider::AMOUNT, $goal['amount']);
        self::assertSame(TestGoalProvider::AMOUNT_KEY, $goal['amountKey']);
        $labels = $goal['labels'];
        self::assertIsArray($labels);
        self::assertSame(TestGoalProvider::INITIAL_LABEL, $labels['initial']);

        self::assertMatchesRegularExpression(
            '#<style[^>]*>[^<]*\.cart-progress-bar[^<]*</style>#',
            $this->part('#<head[^>]*>(.*?)</head>#s', $body),
            'the head carries the inline style of the bar'
        );
        self::assertStringContainsString(
            self::TOP_PLACEMENT,
            $this->part('#<header[^>]*class="page-header"[^>]*>(.*?)</header>#s', $body)
        );
    }

    /**
     * @return void
     */
    #[
        AppIsolation(true),
        Cache('full_page', true),
        Config('cart_progress_bar/general/enabled', '1', 'store', self::STORE),
        Config('cart_progress_bar/general/page_types', 'home,category,search,cart', 'store', self::STORE)
    ]
    public function testCleaningTheBarCacheRemovesTheCachedPagesThatHoldTheBar(): void
    {
        $this->contributeGoalsFrom(TestGoalProvider::class);
        $fullPageCache = $this->service(FullPageCache::class);
        $request = $this->getRequest();
        self::assertInstanceOf(HttpRequest::class, $request);
        $request->setRequestUri('/');
        $pageId = $this->service(IdentifierInterface::class)->getValue();
        $fullPageCache->remove($pageId);

        $this->dispatch('/');
        self::assertNotFalse($fullPageCache->load($pageId), 'the full-page cache stores the home page with the bar');

        $this->service(BarCacheInterface::class)->clean();

        self::assertFalse($fullPageCache->load($pageId));
    }

    /**
     * @return void
     */
    #[
        AppIsolation(true),
        Config('cart_progress_bar/general/enabled', '1', 'store', self::STORE),
        Config('cart_progress_bar/general/page_types', 'home,category,search,cart', 'store', self::STORE),
        DataFixture(ProductFixture::class, as: 'product')
    ]
    public function testAProductPageOutsideThePageTypesHasNoTopPlacementWhileItsMinicartHasTheBar(): void
    {
        $this->contributeGoalsFrom(TestGoalProvider::class);
        $product = DataFixtureStorageManager::getStorage()->get('product');
        self::assertInstanceOf(ProductInterface::class, $product);

        $this->dispatch(sprintf('catalog/product/view/id/%s', (string)$product->getId()));
        $body = $this->body();

        self::assertStringContainsString(self::MINICART_COMPONENT, $body);
        self::assertStringNotContainsString(self::TOP_PLACEMENT, $body);
    }

    /**
     * @return void
     */
    #[
        AppIsolation(true),
        Config('cart_progress_bar/general/enabled', '1', 'store', self::STORE)
    ]
    public function testTheCartPageOffersTheBarToTheThemeThroughTheCartFormLayout(): void
    {
        $this->contributeGoalsFrom(TestGoalProvider::class);

        $this->dispatch('checkout/cart');

        $form = $this->service(LayoutInterface::class)->getBlock('checkout.cart.form');
        self::assertInstanceOf(AbstractBlock::class, $form);
        self::assertStringContainsString('"cart-progress-bar-cart"', $form->getJsLayout());
    }

    /**
     * @return void
     */
    #[
        AppIsolation(true),
        Config('cart_progress_bar/general/enabled', '0', 'store', self::STORE)
    ]
    public function testABarDisabledForTheStoreRendersNothing(): void
    {
        $this->contributeGoalsFrom(TestGoalProvider::class);

        $this->dispatch('/');
        $body = $this->body();

        self::assertStringContainsString(self::MINICART_COMPONENT, $body, 'the module\'s layout is applied to the page');
        self::assertDoesNotMatchRegularExpression(self::CONFIG_BLOCK, $body);
        self::assertStringNotContainsString(self::TOP_PLACEMENT, $body);
        self::assertStringNotContainsString('.cart-progress-bar', $this->part('#<head[^>]*>(.*?)</head>#s', $body));
    }

    /**
     * @return void
     */
    #[
        AppIsolation(true),
        Config('cart_progress_bar/general/enabled', '1', 'store', self::STORE)
    ]
    public function testABarWithNoGoalToShowRendersNothing(): void
    {
        $this->contributeGoalsFrom(EmptyGoalProvider::class);

        $this->dispatch('/');
        $body = $this->body();

        self::assertStringContainsString(self::MINICART_COMPONENT, $body, 'the module\'s layout is applied to the page');
        self::assertDoesNotMatchRegularExpression(self::CONFIG_BLOCK, $body);
        self::assertStringNotContainsString(self::TOP_PLACEMENT, $body);
    }

    /**
     * Make the given provider the only one that contributes goals
     *
     * @param class-string $provider Provider class
     * @return void
     */
    private function contributeGoalsFrom(string $provider): void
    {
        $objectManager = Bootstrap::getObjectManager();
        self::assertInstanceOf(ObjectManager::class, $objectManager);
        $objectManager->configure(['preferences' => [GoalProviderInterface::class => $provider]]);
        $objectManager->removeSharedInstance(GoalProviderInterface::class);
    }

    /**
     * Get the decoded configuration block of the page
     *
     * @param string $body Page HTML
     * @return array<mixed>
     */
    private function configBlock(string $body): array
    {
        if (preg_match(self::CONFIG_BLOCK, $body, $match) !== 1) {
            self::fail('The page carries the configuration block of the bar.');
        }
        $config = json_decode($match[1], true);
        self::assertIsArray($config);

        return $config;
    }

    /**
     * Get the part of the page a pattern captures
     *
     * @param string $pattern Pattern with one capturing group
     * @param string $body Page HTML
     * @return string
     */
    private function part(string $pattern, string $body): string
    {
        if (preg_match($pattern, $body, $match) !== 1) {
            self::fail(sprintf('The page has the part %s.', $pattern));
        }

        return $match[1];
    }

    /**
     * Get the body of the response
     *
     * @return string
     */
    private function body(): string
    {
        $response = $this->getResponse();
        self::assertInstanceOf(HttpResponse::class, $response);

        return $response->getBody();
    }

    /**
     * Get a shared service from the real container, checked to be of the requested type
     *
     * @template T of object
     * @param class-string<T> $type Interface or class to resolve
     * @return T
     */
    private function service(string $type): object
    {
        $service = Bootstrap::getObjectManager()->get($type);
        if (!$service instanceof $type) {
            throw new \LogicException(sprintf('The container returned a service that is not a %s.', $type));
        }

        return $service;
    }
}
