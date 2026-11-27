# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2026-11-27

### Fixed
- The terms shown on hover fill `{{goal}}` and `{{goal_left}}` as the other labels do, as text, instead of showing
  the placeholders.

## [1.0.0] - 2026-11-27

### Added
- A progress bar towards a spend goal, at the top of the header on the chosen page types, in the minicart and on the
  cart page. The shopper's amount is read in the browser from the `cart` customer-data section, so the pages stay
  shared by the full-page cache.
- The contract `GoalProviderInterface` / `GoalInterface`: other modules contribute goals through the `providers`
  argument of `Model\Goals`. With several goals the bar shows the first one, by priority, that the cart has not
  reached, or the first goal once all are reached.
- The wording of a goal by phase (initial, progress, achieved) and its terms, with the placeholders `{{goal}}` and
  `{{goal_left}}`; an optional link and image.
- The settings **Enabled** and **Show at the Top on** per store view, under **Stores › Configuration › Sales › Cart
  Progress Bar**.
- The cache tag `cart_progress_bar` on every page that holds the bar's configuration block or its top placement, and
  the port `Api\BarCacheInterface::clean()`, which invalidates those pages in the built-in full-page cache and in
  a caching proxy.
- The port `Api\LinkPolicyInterface`: the bar prints a goal's link only when it starts with `http://` or `https://`,
  or is a path on the site that starts with a single `/`.
- The CSS custom properties `--cart-progress-bar-bg`, `--cart-progress-bar-text`, `--cart-progress-bar-accent` and
  `--cart-progress-bar-top-height`, and the scopes `cart-progress-bar-top`, `cart-progress-bar-minicart` and
  `cart-progress-bar-cart` for a theme.
