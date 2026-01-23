=== jec-medium ===
Contributors: juanecl
Tags: medium, rss, feed, integration, posts
Requires at least: 5.0
Tested up to: 6.4
Stable tag: ..1..11.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display the latest posts from Medium using RSS feeds with customizable options via WordPress Customizer.

== Description ==

jec-medium integrates Medium's RSS feeds into your WordPress site, allowing you to display the latest posts from a Medium profile, publication, or topic. It provides a customizable widget and shortcode that can be easily added to any area of your site.

= Features =

* Fetches the latest posts from Medium using RSS feeds
* Theme Customizer integration for easy configuration
* Customizable widget to display posts
* Shortcode support for flexible placement
* Control over number of posts, cache duration, and display options
* Responsive design that matches your theme
* Multilingual support (English and Spanish)

= Usage =

1. Configure the plugin from **Appearance → Customize → Medium Integration**
2. Add the "Medium Latest Posts" widget to your sidebar
3. Or use the `[jec-medium]` shortcode in any post or page

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/jec-wp-rss-medium` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **Appearance → Customize → Medium Integration** to configure the plugin
4. Set your Medium RSS feed URL and configure display options
5. Add the widget or use the shortcode

== Frequently Asked Questions ==

= How do I get my Medium RSS feed URL? =

Your Medium RSS feed URL follows this format:
* For user profiles: `https://medium.com/feed/@username`
* For publications: `https://medium.com/feed/publication-name`

= Can I customize the appearance? =

Yes! You can modify the styles in `assets/css/styles.css` to match your theme's design.

= How often does the feed update? =

The cache duration is configurable from the Customizer. By default, feeds are cached for 1 hour (3600 seconds).

== Screenshots ==

1. Medium Integration settings in WordPress Customizer
2. Widget displaying Medium posts
3. Customizer options panel

== Changelog ==

= 1.0.0 =
* Initial release
* Medium RSS feed integration
* Theme Customizer settings
* Widget and shortcode support
* Multilingual support

== Upgrade Notice ==

= 1.0.0 =
Initial release of jec-medium plugin.
