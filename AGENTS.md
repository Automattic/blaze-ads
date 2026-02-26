# Agent Overview

- **Role:** Assist with development of the Blaze Ads WordPress plugin
- **Goals:** Write correct, maintainable PHP/JS code following existing conventions; run tests/lints before submitting changes
- **Non-goals:** Modifying the Blaze Dashboard SPA (lives in `wp-calypso/apps/blaze-dashboard`), DSP server changes (`a8c-dsp`), or AdFlow changes

# Architecture Context

Blaze Ads is a standalone WordPress plugin that lets site owners run ad campaigns across Tumblr and WordPress.com sites.

Key architectural facts that are NOT obvious from the code:

- The **Blaze Dashboard UI** is a Calypso SPA loaded from `widgets.wp.com` — not built in this repo. The plugin only provides the container and configuration via `Blaze_Dashboard`
- All DSP API calls are proxied through **Jetpack Connect** → WPCOM API → DSP server. The plugin never calls the DSP directly
- The `automattic/jetpack-blaze` Composer package provides the core dashboard controllers and the Blaze module — this plugin is essentially a thin WordPress wrapper around it
- **Jetpack Sync** is required for stats data (likes, monthly views) but posts can now be served from the local DB before sync completes
- The plugin implements WooCommerce's `MarketingChannelInterface` to appear in Marketing > Overview on Woo stores
- The conversion pixel (`Blaze_Conversions`) fires on `woocommerce_thankyou` to track purchases back to Blaze campaigns

# Development

## Prerequisites

- A **publicly accessible** WordPress site is required — Jetpack cannot connect to localhost without a tunnel
- Recommended: [Studio by WordPress.com](https://developer.wordpress.com/studio/) or [Jurassic Ninja](https://jurassic.ninja/) for test sites
- Shared test site: https://blaze-ads.jurassic.tube/

## Key Commands

```bash
pnpm install          # Install JS + PHP deps (runs composer install via postinstall)
pnpm build            # Full build → produces blaze-ads.zip
pnpm test             # Run PHPUnit tests (via Docker)
pnpm lint             # PHP linting (PHPCS with WooCommerce-Core rules)
pnpm lint:php:fix     # Auto-fix PHP lint issues
pnpm changelog        # Add changelog entry (required for every PR)
pnpm docker:up        # Start Docker dev environment (WordPress on :8082, MariaDB on :3308)
```

## PR Requirements

- Every PR must include a changelog entry: run `pnpm changelog`, choose `patch` for non-significant changes
- CI runs PHP linting and PHPUnit tests automatically
- The repo is **public** — do not include sensitive information in PRs or comments

# Testing

- PHPUnit tests live in `tests/php/`; run via Docker with `pnpm test`
- For CI: `bin/run-ci-tests.sh` handles full setup (composer install, MySQL, WordPress + WooCommerce test lib installation)
- Manual smoke testing on a live site is standard before releases — install the built zip on a Jurassic Ninja site

# Release Process

1. Run GitHub Action **"Release — Prepare a release PR to trunk"** (inputs version number)
2. Smoke test the generated zip from the release PR
3. Squash-merge the release PR
4. Run GitHub Action **"Release — Create tag and release trunk"**
5. SVN publish to WordPress.org (username: `automattic`, password in [secret store](https://mc.a8c.com/secret-store/?secret_id=9657))
6. WooCommerce Marketplace auto-syncs from WordPress.org — no separate action needed

## Release Gotchas

- If release workflow fails with `HTTP 401: Bad credentials`, refresh the bot token in the secret store
- WordPress.org search index propagation can take up to 72 hours after SVN publish

# Safety

- This plugin is deployed to Atomic sites by the Atomic team — do not bypass Jetpack connection flows
- Do not remove Jetpack Sync calls without understanding mobile app implications (Blaze SPA in mobile loads via `wordpress.com/advertising`)
- When bumping `jetpack-blaze` version, use `composer update --with-all-dependencies` if transitive deps changed