# Blaze for WooCommerce

A WooCommerce plugin that allow you to promote your store and products with a single-click advertising campaigns.

## Dependencies
* WooCommerce
* Jetpack

## Development

### Prerequisites
* [NVM](https://github.com/nvm-sh/nvm#installing-and-updating): While you can always install Node through other means, we recommend using NVM to ensure you're aligned with the version used by our development teams. Our repository contains an .nvmrc file which helps ensure you are using the correct version of Node.
* [PNPM](https://pnpm.io/installation): Our repository utilizes PNPM to manage project dependencies and run various scripts involved in building and testing projects.
* [PHP 7.4+](https://www.php.net/manual/en/install.php): Blaze for WooCommerce currently features a minimum PHP version of 7.4. It is also needed to run Composer and various project build scripts.
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

You can also use any WordPress environment tool. If you follow that path, you will need to install/activate these plugins:
* WooCommerce
* Jetpack

To generate this plugin's zip file, run this command:
```
pnpm build
```

The release file will be located at the root of the repo with the name `blaze-ads.zip`. 

## Debugging

If you are following the Docker setup [here](docker/README.md), Xdebug is ready to use for debugging.

Install [Xdebug Helper browser extension mentioned here](https://xdebug.org/docs/remote) to enable Xdebug on demand.
