# Magento 2 Cart Progress Bar

Shows the shopper how far the cart is from a spend goal: a bar with a message and a progress track, at the top of the header, in the minicart and on the cart page. The module knows no kind of goal. Other modules contribute goals through a provider, so one bar serves any target amount.

## What it does for a store

- **Three placements.** At the top of the header on the page types the store chose, in the minicart and on the cart page. The minicart and cart placements show whenever the bar is enabled and a goal exists.
- **A message by phase.** Each goal has a wording for three phases: **initial** (the cart holds nothing towards the goal), **progress** (on the way) and **achieved** (the goal is reached), and its terms. The wording takes the placeholders `{{goal}}`, the amount of the goal, and `{{goal_left}}`, the amount still to spend, both formatted in the store's price format. The text is always shown as text, never as markup. The track fills with the share reached, in whole percent, and is full once the goal is reached.
- **Several goals, one bar.** Goals are ordered by priority, the lowest number first. The bar shows the first goal the cart has not reached; once every goal is reached it shows the first goal's achieved message. The choice is made in the browser, because the amounts are private.
- **Optional link and image.** A goal can link the whole bar to an address and show an image beside the text; its terms become the link's `title`. Only an address on the web that starts with `http://` or `https://`, or a path on the site that starts with a single `/`, is printed as a link; any other link (`//other.example/x`, `ftp://…`, `javascript:…`) leaves the bar without one. The rule is `Hryvinskyi\CartProgressBar\Api\LinkPolicyInterface`, so a setting that stores a goal's link can refuse what the bar would drop.
- **Safe with the full-page cache.** Everything printed into a page is the same for every visitor of the store: the goals and the price format, in one JSON block printed once per page. The shopper's own amount is read in the browser from the `cart` customer-data section, so the pages stay shared. Until the section arrives the bar shows the first goal's initial message.
- **Cached pages follow the goals.** Every page that holds the bar's configuration block or its top placement is cached with the tag `cart_progress_bar`. `Hryvinskyi\CartProgressBar\Api\BarCacheInterface::clean()` invalidates them all, in the built-in full-page cache and in a caching proxy alike, so a module calls it when data its goals are built from changes.
- **No layout jump.** The bar's style is printed inline in the head, and the top placement reserves its height before the bar fills it.
- **Nothing without a goal.** Where the bar is disabled, or no module contributes a goal to the store view, the page carries no configuration block, no inline style and no top placement; the minicart and cart components find no configuration and render nothing.

## Requirements

- PHP 8.1 to 8.5.
- `hryvinskyi/magento2-theme-assets` ^1.0.1.
- The platform's Framework, Backend, Catalog, Checkout, Config, Customer, Store and Ui modules (what `composer.json` and `etc/module.xml` declare).

## Installation

```bash
composer require hryvinskyi/magento2-cart-progress-bar
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

The bar shows nothing until a module contributes a goal.

## Configuration

**Stores › Configuration › Sales › Cart Progress Bar › General**. The section is allowed by the ACL resource `Hryvinskyi_CartProgressBar::config`.

| Path | Label | Default | Scope |
|---|---|---|---|
| `cart_progress_bar/general/enabled` | Enabled | No | default, website, store view |
| `cart_progress_bar/general/page_types` | Show at the Top on | Home Page, Category Page, Search Results, Shopping Cart | default, website, store view |

**Show at the Top on** chooses the pages that show the bar at the top of the header; the list may be left empty, and the minicart and cart placements do not depend on it. The page types are:

| Code | Label | Page (full action name) |
|---|---|---|
| `home` | Home Page | `cms_index_index` |
| `category` | Category Page | `catalog_category_view` |
| `search` | Search Results | `catalogsearch_result_index` |
| `cart` | Shopping Cart | `checkout_cart_index` |
| `product` | Product Page | `catalog_product_view` |

A module adds a page type with an item in the `pageTypes` argument of `Hryvinskyi\CartProgressBar\Model\PageTypes` (`action` and `label`).

## Contributing a goal

A module that has a goal implements `Hryvinskyi\CartProgressBar\Api\GoalProviderInterface` and adds the provider to the `providers` argument of `Hryvinskyi\CartProgressBar\Model\Goals`:

```xml
<type name="Hryvinskyi\CartProgressBar\Model\Goals">
    <arguments>
        <argument name="providers" xsi:type="array">
            <item name="my_goal" xsi:type="object">Vendor\Module\Model\MyGoalProvider</item>
        </argument>
    </arguments>
</type>
```

The item belongs in the module's `etc/frontend/di.xml`, as the bar is a storefront feature. A provider reads the store, never the visitor.

```php
public function goalsFor(StoreInterface $store): array
{
    return [$this->goalFactory->create([
        'code' => 'my_goal',
        'priority' => 10,
        'amount' => 70.0,
        'amountKey' => 'my_module.amount',
        'initialLabel' => 'Spend {{goal}} to reach the goal!',
        'progressLabel' => 'Spend {{goal_left}} more!',
        'achievedLabel' => 'You have reached the goal!',
    ])];
}
```

`$goalFactory` is the generated `Hryvinskyi\CartProgressBar\Api\Data\GoalInterfaceFactory`.

| Part of a goal | Meaning |
|---|---|
| `code` | Tells the goal from the other goals of the store; not blank. The bar adds it to its CSS classes. |
| `priority` | Lower first. Goals of equal priority keep the order of the providers. |
| `amount` | The amount to spend, in the store's current currency; above nothing. |
| `amountKey` | The dot path into the `cart` customer-data section that holds the shopper's amount towards the goal, in the cart currency, such as `subtotalAmount` or `my_module.12`. A path the section does not hold, or a value that is not a number of zero or more, counts as nothing spent. Not blank. |
| `initialLabel`, `progressLabel`, `achievedLabel`, `termsLabel` | The wording by phase and the terms (`termsLabel` is optional). |
| `link` | The address the bar links to; optional. |
| `image` | The address of the image beside the text; optional. |

A goal with a blank code or path, or an amount that is not above nothing, is refused when it is created, with an `InvalidArgumentException`. Goals from every provider are ordered by priority. Providers run when a page is rendered, which is on a page-cache miss, and their goals are printed into pages the cache shares.

The module that contributes a goal also puts the shopper's amount at `amountKey` into the `cart` section, for example with a plugin on `Magento\Checkout\CustomerData\Cart::getSectionData`.

Goals are printed into cached pages, so a provider whose goals change with its own data cleans those pages when the data changes, through the injected `Hryvinskyi\CartProgressBar\Api\BarCacheInterface`:

```php
$this->barCache->clean();
```

`clean()` dispatches `clean_cache_by_tags` with an object whose identity is the tag `BarCacheInterface::CACHE_TAG` (`cart_progress_bar`). The platform's page cache cleans the pages stored with that tag, and its caching proxy integration sends a purge for it. A change to a store's settings is not covered: saving the configuration asks the admin to refresh the page cache, as for every setting.

A setting that stores a goal's link checks it with `Hryvinskyi\CartProgressBar\Api\LinkPolicyInterface::accepts(string $link): bool`, the same rule the bar applies when it prints the link.

## Theme hooks

**Components.** Each placement is a UI component in the layout, named by its scope:

| Scope | Placement | Registered in |
|---|---|---|
| `cart-progress-bar-top` | top of the header | the block `cart_progress_bar.top` of the container `header.container`, before `header.panel.wrapper`; printed only on the chosen page types |
| `cart-progress-bar-minicart` | minicart | the `jsLayout` of the block `minicart` |
| `cart-progress-bar-cart` | cart page | the `jsLayout` of the block `checkout.cart.form` |

The top placement renders itself. A theme renders the other two where it wants them, inside the area of the block that holds them:

```html
<div data-bind="scope: 'cart-progress-bar-minicart'">
    <!-- ko template: getTemplate() --><!-- /ko -->
</div>
```

**Look.** The bar reads four CSS custom properties, which a theme sets on `:root` or on any ancestor of the bar:

| Property | Sets | Default |
|---|---|---|
| `--cart-progress-bar-bg` | background | `#f4f4f4` |
| `--cart-progress-bar-text` | text colour | `#000` |
| `--cart-progress-bar-accent` | colour of the progress track | `#333` |
| `--cart-progress-bar-top-height` | height the top placement reserves | `2.75em` |

**Classes.** The markup is `cart-progress-bar` with `cart-progress-bar__body` (the link), `__image`, `__content`, `__text`, `__track` and `__fill`. The root carries `cart-progress-bar--<phase>` (`initial`, `progress`, `achieved`), `cart-progress-bar--<placement>` (`top`, `minicart`, `cart`) and `cart-progress-bar--goal-<code>`, so a theme can style one goal or one phase apart. The top placement's container is `cart-progress-bar-top`.

**Template.** The component's template is `Hryvinskyi_CartProgressBar/bar` (`view/frontend/web/template/bar.html`); a theme overrides it the way it overrides any module's web template.

## Limits

- Amounts are compared in hundredths, so a float sum never misses a goal by a fraction of a penny.
- The page's configuration block is read once; a goal of the wrong shape in it is left out, and a block that leaves no goal makes the bar render nothing.

## Registrations

| Where | What |
|---|---|
| preference for `Api\GoalProviderInterface` | `Model\Goals`, the goals of every provider in its `providers` argument |
| preference for `Api\BarCacheInterface` | `Model\BarCache`, which cleans the pages tagged `cart_progress_bar` |
| preference for `Api\LinkPolicyInterface` | `Model\LinkPolicy`: `http://`, `https://` or a path that starts with a single `/` |
| `pageTypes` of `Model\PageTypes` | the page types above |
| `default` layout | the inline style in `head.additional`, the top placement, the minicart component and the configuration block in `before.body.end`; the top placement and the configuration block are `Block\Bar`, which tags the page `cart_progress_bar` |
| `checkout_cart_index` layout | the cart component in the `jsLayout` of `checkout.cart.form` |
