=== Blaze Ads - Fully Integrated Ads Solution ===
Contributors: woocommerce, automattic
Tags: blaze ads, woo blaze, blaze, advertising
Requires at least: 6.3
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Promote your products and services to over 100 million users across Tumblr and WordPress blogs, with Blaze Ads.

== Description ==

Blaze is by far the simplest way to start promoting your site, and it can reach over 100 million users across Tumblr and WordPress blogs. With an intuitive ad-building interface seamlessly integrated into your WordPress or WooCommerce site, you can get started in just minutes.

Blaze is built for people who have a business to run or stories to tell, and don’t have time for endless settings and options. You can truly get started advertising in just a few minutes.

* Get readers to your blog
* Find new customers
* Get new newsletter subscribers
* Reach new fans and followers

Create ads using existing content on your site or upload custom images. The AI assistant can help you write the text, and with geographic and interest targeting, you can connect with the audience that matters most to you.

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



== Screenshots ==
1. Transform your content to an ad with a click
2. Choose an eye-catching image, an engaging title and pick your audience budget and duration
3. Review your campaign
4. Or make more advanced changes
5. All Set! Your campaign has been sent for moderation
6. See how your campaign has performed!

== Changelog ==

= 0.7.0 - 2025-07-02 =
* Fix - Fixes the i18n notices on the plugin (Jetpack IDC config and marketing channel descriptions)
* Fix - Update actions/upload-artifact from v3 to v4 to use the latest version

= 0.6.0 - 2025-01-08 =
* Update - Updates the plugin documentation page

= 0.5.3 - 2025-01-06 =
* Fix - Fixes a compatibility bug with with newer versions of WooCommerce
* Update - Updates composer dependencies to use the new Blaze package version

= 0.5.2 - 2024-10-31 =
* Add - Adds the assets for the DotOrg directory
* Update - updates composer dependency for the Blaze package

= 0.5.1 - 2024-10-01 =
* Update - Run error reponses though esc_html

= 0.5.0 - 2024-09-30 =
* Fix - fixing "The workflow is requesting 'actions: write', but is only allowed 'actions: none'."
* Fix - Reinstate GH token in release PR action
* Fix - Removes old dashboard compatibility classes. They are no longer needed
* Fix - Removes permissions that were on the step level, this is not supported and caused action failures
* Update - Changes the menu slug from wc-blaze to wp-blaze
* Update - Improves the handling of the DSP API responses
* Update - minor code cleanups, type hinting, access modifiers, return types
* Update - Refactors the rest of the files to follow the new plugin's name
* Update - Removes a reference to a remote image, and brings it into the plugin
* Update - Removes the custom translation code to start using the default dotorg mechanism
* Update - Update action permissions on a per job basis
* Dev - Adds composer.json to the release build

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

