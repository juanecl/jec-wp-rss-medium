# WordPress Medium Integration

## Overview
This plugin integrates Medium's RSS feeds into your WordPress site, allowing you to display the latest posts from a Medium profile, publication, or topic. It provides a customizable widget that can be easily added to any widget area in your theme.

## Features
- Fetches the latest posts from Medium using RSS feeds.
- Customizable widget to display posts.
- **Theme Customizer integration** for easy configuration.
- Control over number of posts, cache duration, and display options.
- Responsive design that matches your theme.
- Easy installation and setup.

## Installation
1. Download the plugin files.
2. Upload the `jec-wp-rss-medium` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Go to **Appearance → Customize → Medium Integration** to configure the plugin settings.
5. Set your Medium RSS feed URL and configure display options.
6. Add the "Medium Latest Posts" widget to your desired widget area or use the `[jec-medium]` shortcode.

## Configuration
### Widget
- Go to **Appearance → Widgets**
- Add the "Medium Latest Posts" widget to your desired widget area
- Configure the widget title

### Shortcode
- Use the `[jec-medium]` shortcode in any post, page, or custom post type
- The shortcode will display posts according to your Customizer settings

All display settings are controlled from **Appearance → Customize → Medium Integration**
1. Go to **Appearance → Customize → Medium Integration**
2. Configure the following options:
   - **Medium Feed URL**: Your Medium RSS feed URL (e.g., https://medium.com/feed/@username)
   - **Number of Posts**: How many posts to display (1-20)
   - **Cache Duration**: How long to cache posts in seconds (300-86400)
   - **Show Featured Images**: Toggle featured images display
   - **Show Categories**: Toggle categories display
   - **Show Excerpt**: Toggle excerpt display

## Usage
- After adding the widget, you can customize the title, number of posts to display, and other settings.
- The widget will automatically fetch and display the latest posts from the specified Medium feed.

## Customization
- You can modify the styles in `assets/css/styles.css` to match your theme's design.
- JavaScript interactions can be added or modified in `assets/js/scripts.js`.

## Troubleshooting
If you encounter issues with the feed not loading or "Invalid XML" errors, please refer to the [TROUBLESHOOTING.md](TROUBLESHOOTING.md) guide for detailed solutions.

Common issues:
- **Invalid XML errors**: Usually caused by incorrect feed URL format
- **Feed not loading**: Check your Medium username and feed URL
- **See [TROUBLESHOOTING.md](TROUBLESHOOTING.md)** for complete solutions

## Localization
- The plugin is ready for translation. Add your language files in the `languages` directory.

## Support
For support, please open an issue on the GitHub repository or contact the plugin author.

## License
This plugin is licensed under the GPL v2 or later.