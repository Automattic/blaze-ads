# Blaze Ads

WordPress plugin for running ad campaigns across Tumblr and WordPress.com. Thin wrapper around the `automattic/jetpack-blaze` Composer package.

## Development

- A **publicly accessible** site is required — Jetpack cannot connect to localhost without a tunnel
- Recommended: [Studio by WordPress.com](https://developer.wordpress.com/studio/) or [Jurassic Ninja](https://jurassic.ninja/)
- Shared test site: https://blaze-ads.jurassic.tube/

## Release

Blaze Ads is a **managed plugin** — the Atomic team handles deployment to Atomic sites.

1. Merge to `trunk`
2. GitHub Action: **Release — Prepare a release PR** (specify version)
3. Smoke test the generated zip on a live site
4. Squash-merge the release PR
5. GitHub Action: **Release — Create tag and release trunk**

### WordPress.org

After GitHub release, manually push to SVN:
- SVN: `https://plugins.svn.wordpress.org/blaze-ads/`
- Username: `automattic` (case sensitive), password in secret store
- WooCommerce Marketplace auto-syncs from WordPress.org

## Safety

- Do not bypass Jetpack connection flows — plugin is deployed to Atomic sites by the Atomic team
- Do not remove Jetpack Sync calls without understanding mobile app implications (Blaze SPA in mobile loads via `wordpress.com/advertising`)
- When bumping `jetpack-blaze` version, use `composer update --with-all-dependencies` if transitive deps changed

## Related

- **Dashboard frontend**: `wp-calypso/apps/blaze-dashboard`
- **Jetpack Blaze package**: release standalone versions via [Jetpack release process](https://fieldguide.automattic.com/releasing-jetpack/jetpack-release-best-practices/releasing-stand-alone-package-versions/)
- Previously called **Woo Blaze** (old repo `Automattic/woo-blaze` is archived)
