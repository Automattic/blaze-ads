# Blaze Ads

A WordPress plugin that allow you to promote your site with a single-click advertising campaigns.

### Dependencies
* WordPress.com account

### Integrations
* **WooCommerce:** Blaze Ads implements a new Marketing channel inside WooCommerce.
* **Jetpack:** Internally, the plugin uses Jetpack to connect to WordPress.com. Additionally, we have some minor integrations with some of the Jetpack products (e.g., Stats).

## Development

### Prerequisites
* [NVM](https://github.com/nvm-sh/nvm#installing-and-updating): While you can always install Node through other means, we recommend using NVM to ensure you're aligned with the version used by our development teams. Our repository contains an .nvmrc file which helps ensure you are using the correct version of Node.
* [PNPM](https://pnpm.io/installation): Our repository utilizes PNPM to manage project dependencies and run various scripts involved in building and testing projects.
* [PHP 7.4+](https://www.php.net/manual/en/install.php): Blaze Ads currently features a minimum PHP version of 7.4. It is also needed to run Composer and various project build scripts.
* [Composer](https://getcomposer.org/doc/00-intro.md): We use Composer to manage all of the dependencies for PHP packages and plugins.

Once you've installed all the prerequisites, you can run the following commands to get everything working.
```
# Ensure that you're using the correct version of Node
nvm use
# Install the PHP and Composer dependencies
pnpm install
```

## Setup

For a local docker setup, you can see instructions [here](docker/README.md).

You can also use any WordPress environment tool such as [Studio by WordPress.com](https://developer.wordpress.com/studio/).
To generate this plugin's zip file, run this command:
```
pnpm build
```

The release file will be located at the root of the repo with the name `blaze-ads.zip`. 

Regardless of the path you choose for your environment, your installation will need a public tunnel. You can do this in Studio by WordPress.com using the built in [demo site](https://developer.wordpress.com/docs/developer-tools/studio/#demo-sites) option.

Blaze Ads internally uses a Jetpack connection, and Jetpack requires your site to be public to function correctly.

## Debugging

If you are following the Docker setup [here](docker/README.md), Xdebug is ready to use for debugging.

Install [Xdebug Helper browser extension mentioned here](https://xdebug.org/docs/remote) to enable Xdebug on demand.
