=== Blaze Ads - Fully Integrated Ads Solution ===
Contributors: woocommerce, automattic
Tags: blaze ads, woo blaze, blaze, advertising
Requires at least: 6.3
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Promote your products and services to over 100 million users across Tumblr and WordPress blogs, with Blaze Ads.

== Description ==

Blaze Ads helps you reach over 100 million users across Tumblr and WordPress blogs—whether you're building an audience of readers, fans, customers, or subscribers.

Turn your content into an ad with just a few clicks!

Blaze Ads is the simplest way to promote your site. Start advertising in minutes and reach over 100 million shoppers across the Automattic ecosystem. Designed to work across all retail verticals and product types, Blaze helps site owners drive more traffic and generate more sales with minimal effort.

Blaze Ads offers a fast, intuitive, and streamlined interface to create a Blaze Ad campaign for any of your pages, products or services, even if you’ve never advertised before. Customize your ad image, copy, and audience targeting to maximize impact, reach, and performance.

= Want to learn more about Blaze? =

Explore more about how Blaze can help grow your business by visiting our [information page](https://wordpress.com/advertising/). You can also dive into our comprehensive [support documents](https://wordpress.com/support/promote-a-post/) for step-by-step guides and tips, or reach out to our [dedicated support team](https://wordpress.com/help/contact/), always ready to assist with any questions you have.

= Integrations =

Blaze Ads seamlessly integrates with WooCommerce, offering a tailored advertising experience designed specifically for merchants. Effortlessly promote your products to a wider audience and drive more traffic to your WooCommerce store.

== Getting Started ==

= Requirements =

* WordPress 6.3 or newer.
* PHP 7.4 or newer is recommended.

== Installation ==

Install Blaze Ads, then go to **Tools → Advertising** in the WordPress admin menu and follow the instructions there.

= Using with WooCommerce =

Install and activate the WooCommerce and Blaze Ads plugins, if you haven't already done so, then go to **Marketing → Blaze Ads** in the WordPress admin menu and follow the instructions there.

== Frequently Asked Questions ==

= Why do I need a WordPress.com account? =

A WordPress.com account is required because Blaze Ads and its services are powered and hosted by WordPress.com. This ensures smooth functionality and access to all features.

= How do I access the Blaze Ads dashboard? =

You can find the Blaze Ads dashboard by navigating to **Tools → Advertising** in your WordPress admin menu.

If your site uses WooCommerce, you’ll also find Blaze Ads alongside your other marketing channels under **Marketing → Blaze Ads**.

= Are there Terms of Service and data usage policies? =

Yes, you can review our [Terms of Service](https://wordpress.com/tos/), our [Privacy Policy](https://automattic.com/privacy/) and our [Advertising Policy](https://automattic.com/advertising-policy/) for full details on how we handle data and ensure compliance.

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

