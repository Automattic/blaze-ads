=== Blaze Ads - Fully Integrated Ads Solution ===
Contributors: woocommerce, automattic
Tags: blaze ads, woo blaze, blaze, advertising
Requires at least: 6.3
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One-click and you're set! Create ads for your products and store simpler than ever. Get started now and watch your business grow.

== Description ==

*** Create ads with your content in a snap with Blaze ***

Blaze is an exclusive ad platform that allows you to grow your audience by promoting your content across Tumblr and WordPress.com.

== Getting Started ==

= Requirements =

* WordPress 6.3 or newer.
* WooCommerce 7.6 or newer. (optional)
* Jetpack latest version is recommended.
* PHP 7.4 or newer is recommended.

== Installation ==

#### Using without WooCommerce

Install Blaze Ads and Jetpack plugin (optional), then go to "Tools -> Advertising" in the WordPress admin menu and follow the instructions there.

#### Using with WooCommerce

Install and activate the WooCommerce, Blaze Ads plugins and Jetpack (optional), if you haven't already done so, then go to "Marketing->Blaze Ads" in the WordPress admin menu and follow the instructions there.

== Changelog ==

= 0.4.1 - 2024-08-19 =
* Dev - fix concerns reported by running Plugin Check (PCP)

= 0.4.0 - 2024-08-15 =
* Add - Adds additional flags to the dashboard config initial data
* Add - Adds the plugin version as one of the props that we send to the dashboard app
* Update - Extend plugin to support sites without woo
* Update - Rename Blaze for WooCommerce to Blaze Ads
* Update - update jetpack connect flow and idc for non-woo sites

= 0.3.2 - 2024-07-29 =
* Dev - Fix changelog entries missing when generating readme.txt
* Dev - update license

= 0.3.1 - 2024-06-25 =
* Fix - Update Woo header value

= 0.3.0 - 2024-06-25 =
* Add - Add Woo plugin header
* Update - Use WooCommerce style changelog

= 0.2.1 - 2024-06-21 =
* Fix - Fix linter issues

= 0.2.0 - 2024-06-20 =
* Update - Change plugin slug from woo-blaze to blaze-ads
* Update - Update the version of the packages: automattic/jetpack-blaze, automattic/jetpack-sync

= 0.1.1 - 2024-05-08 =
* Fix - Fixes the marketing channel setup check
* Add - Add Jetpack connect and IDC customization
* Add - Adds the integration with Jetpack Sync module
* Update - Updates the blaze disabled check to improve compatibility with the Jetapck plugin

= 0.1.0 - 2024-04-23 =
* Fix - Added translations fetching support from the translate.wordpress api service
* Add - Added admin notices to notify of plugin dependencies
* Update - Updates the Blaze module customization to adapt to the next Jetpack version

= 0.0.6 - 2024-02-29 =
* Fix - Hides the Woo Blaze marketing channel if the site is not Blaze eligible
* Add - Adds configuration needed to the dashboard to render the finish setup page

= 0.0.5 - 2024-02-22 =
* Fix - change the condition in load_admin_scripts to include all hooks ending with _wc-blaze
* Fix - Fixes initialization of the plugin in installations without WooCommerce
* Fix - Fixes the locale used to render the dashboard

= 0.0.4 - 2024-02-21 =
* Add - Adds a conversion pixel call when a order is made
* Add - Implemented MultiMedia Marketing Channel interface to include Blaze/Promote post as an available marketing channel

= 0.0.3 - 2024-02-12 =
* Add - Added i18n modules and build commands in package.json
* Add - Adds GitHub actions and workflows to the repository
* Add - Adds the Blaze for Woo entry point
* Add - translation github action

