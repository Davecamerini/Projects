=== Nuke Cache ===
Contributors: davecamerini
Tags: cache, performance, optimization
Requires at least: 5.0
Tested up to: 6.7.2
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple and effective WordPress plugin to manage and clear cache folders in wp-content directory.

== Description ==

The Nuke Cache plugin for WordPress scans the `wp-content` directory for cache folders and provides options to empty them. This plugin is useful for users who want to manage their cache effectively, ensuring that outdated or unnecessary cache files do not take up space on their server.

== Features ==

* Cache Folder Detection: Automatically scans for common cache folders, including /cache and /et-cache
* Display Cache Size: Shows the size of the detected cache folders
* Empty Cache Options: Provides buttons to empty the contents of the detected cache folders
* User-Friendly Interface: Integrated into the WordPress admin dashboard

== Installation ==

1. Download the plugin ZIP file from the repository or clone the repository to your local machine.
2. Go to your WordPress admin dashboard.
3. Navigate to **Plugins > Add New**.
4. Click on **Upload Plugin** and select the downloaded ZIP file.
5. Click **Install Now** and then **Activate** the plugin.

== Usage ==

1. After activating the plugin, navigate to **Cache Nuker** in the WordPress admin menu.
2. The plugin will scan for cache folders and display their sizes.
3. Click the **Empty Cache Folder** button to delete all files within the /cache folder.
4. Click the **Empty Et-cache Folder** button to delete all files within the /et-cache folder.

== Frequently Asked Questions ==

= Does this plugin work with any WordPress theme? =

Yes, this plugin works with any WordPress theme as it only interacts with the wp-content directory.

= What cache folders does this plugin detect? =

Currently, the plugin detects and can clear:
* /wp-content/cache
* /wp-content/et-cache

= Is this plugin safe to use? =

Yes, this plugin is safe to use. It only deletes cache files and does not modify any core WordPress files or database content.

== Screenshots ==

1. Main plugin interface showing cache sizes and clear options
2. Cache size display
3. Empty cache confirmation

== Changelog ==

= 1.0.0 =
* Initial release
* Cache folder scanning functionality
* Support for emptying /cache and /et-cache folders
* Size display for cache folders
* WordPress admin menu integration
* Security features including nonce verification
* Translation support
* Uninstall functionality

== Upgrade Notice ==

= 1.0.0 =
Initial release of Nuke Cache plugin.

== License ==

This plugin is licensed under the GNU General Public License v2 or later.

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA 