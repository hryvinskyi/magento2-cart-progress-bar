# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.2] - 2026-11-27

### Fixed
- Pointing at the info button no longer resizes the bar's text and track: the button's hover, focus and active
  states reset only the theme's button look, so a theme's own margin or size for the button holds in every state.

## [1.2.1] - 2026-11-27

### Fixed
- Pointing at a bar with a link no longer turns its text into the theme's link colour and underlines it; the link
  keeps the bar's colours on hover, focus and once visited.
- The link no longer shows the terms as a tooltip, which repeated what the info button shows.

### Removed
- `text` and `terms` from what the progress model's `display()` returns; the bar reads `parts` and `termsParts`.

## [1.2.0] - 2026-11-27

### Added
- A goal with terms gets an info button (`cart-progress-bar__terms-toggle`) at the end of the bar. It shows the
  terms in place of the text, with their amounts in bold, and turns into a close button; a second press, or Enter,
  brings the text back. The root carries `cart-progress-bar--terms` while the terms are shown. The button's label,
  "Show the terms" or "Hide the terms", is translatable.

### Changed
- The bar's root is a flex row, so the button sits beside the link.

## [1.1.0] - 2026-11-27

### Added
- The amounts in the bar's text (`{{goal}}`, `{{goal_left}}`) are printed in bold, each in a
  `cart-progress-bar__amount` element, with the colour `--cart-progress-bar-amount` (the text colour by default).

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
