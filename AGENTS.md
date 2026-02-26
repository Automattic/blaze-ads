# Blaze Ads

WordPress/WooCommerce plugin for Blaze advertising campaigns. Repo: Automattic/blaze-ads.

## Setup

- `nvm use` before anything — Node v20.8.1 required
- `pnpm install` — also runs `composer install` via postinstall
- Use `pnpm`, not npm or yarn

## Verification

- PHP changes: `pnpm test && pnpm lint`
- JS/CSS changes: `pnpm lint:js` / `pnpm lint:css`

## Gotchas

- Main branch: `trunk`
- Tests run inside Docker — start with `docker compose up -d`
- PHP linting uses **WooCommerce-Core** ruleset (not WordPress default) — tabs for indentation
- Changelog entry required for every PR: `pnpm changelog` (types: fix, add, update, dev)
- PHP autoloading via `automattic/jetpack-autoloader`, not standard Composer autoload
- i18n text domain: `blaze-ads`
