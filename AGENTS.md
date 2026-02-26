# Architecture Context

Blaze Ads is a standalone WordPress plugin that lets site owners run ad campaigns across Tumblr and WordPress.com sites.

Key architectural facts:

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

## PR Requirements

- Every PR must include a changelog entry: run `pnpm changelog`, choose `patch` for non-significant changes
- CI runs PHP linting and PHPUnit tests automatically
- The repo is **public** — do not include sensitive information in PRs or comments

# Testing

- PHPUnit tests live in `tests/php/`; run via Docker with `pnpm test`
- For CI: `bin/run-ci-tests.sh` handles full setup (composer install, MySQL, WordPress + WooCommerce test lib installation)
- Manual smoke testing on a live site is standard before releases — install the built zip on a Jurassic Ninja site

# Release

Blaze Ads is a **managed plugin** — the Atomic team handles deployment to Atomic sites.

## Release Flow

1. Merge branch to `trunk`
2. Run GitHub Action: **Release — Prepare a release PR to trunk** (specify version number)
3. Follow the PR checklist, smoke test the generated zip on a live site
4. Squash-merge the release PR
5. Run GitHub Action: **Release — Create tag and release trunk**
6. Atomic team picks up the new release automatically

## Release to WordPress.org

After GitHub release, manually push to SVN:
- SVN URL: `https://plugins.svn.wordpress.org/blaze-ads/`
- Username: `automattic` (case sensitive), password in the secret store
- WooCommerce Marketplace auto-syncs from WordPress.org — no separate step needed

# Safety

- This plugin is deployed to Atomic sites by the Atomic team — do not bypass Jetpack connection flows
- Do not remove Jetpack Sync calls without understanding mobile app implications (Blaze SPA in mobile loads via `wordpress.com/advertising`)
- When bumping `jetpack-blaze` version, use `composer update --with-all-dependencies` if transitive deps changed

# Related Systems

- **Dashboard frontend**: `wp-calypso/apps/blaze-dashboard` (Calypso repo)
- **Jetpack Blaze package**: `automattic/jetpack-blaze` on Packagist — release standalone versions via [Jetpack release process](https://fieldguide.automattic.com/releasing-jetpack/jetpack-release-best-practices/releasing-stand-alone-package-versions/)
- **DSP backend**: proxied through WPCOM REST API
- **Payment**: Adpurchase (dedicated Woo instance with Stripe)
- Previously called **Woo Blaze** — old repo `Automattic/woo-blaze` is archived
