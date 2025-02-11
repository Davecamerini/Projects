# Nuke Cache

## Description

The Nuke Cache plugin for WordPress scans the `wp-content` directory for cache folders and provides options to empty them. This plugin is useful for users who want to manage their cache effectively, ensuring that outdated or unnecessary cache files do not take up space on their server.

## Features

- **Cache Folder Detection**: Automatically scans for common cache folders, including `/cache` and `/et-cache`.
- **Display Cache Size**: Shows the size of the detected cache folders, helping users understand how much space is being used.
- **Empty Cache Options**: Provides buttons to empty the contents of the detected cache folders with a single click.
- **User-Friendly Interface**: Integrated into the WordPress admin dashboard for easy access and management.

## Installation

1. **Download the Plugin**: Download the plugin ZIP file from the repository or clone the repository to your local machine.

2. **Upload the Plugin**:
   - Go to your WordPress admin dashboard.
   - Navigate to **Plugins > Add New**.
   - Click on **Upload Plugin** and select the downloaded ZIP file.
   - Click **Install Now** and then **Activate** the plugin.

## Usage

1. After activating the plugin, navigate to **Cache Nuker** in the WordPress admin menu.
2. The plugin will scan for cache folders and display their sizes.
3. Click the **Empty Cache Folder** button to delete all files within the `/cache` folder.
4. Click the **Empty Et-cache Folder** button to delete all files within the `/et-cache` folder.

## Requirements

- WordPress 4.0 or higher
- PHP 5.6 or higher

## Troubleshooting

- If you do not see the cache folders listed, ensure that they exist in the `wp-content` directory.
- Check the `wp-content/debug.log` file for any error messages if the plugin fails to function as expected.

## Contributing

Contributions are welcome! If you have suggestions for improvements or find bugs, please open an issue or submit a pull request.

## License

This plugin is licensed under the MIT License. See the LICENSE file for more information.

## Author

Developed by [Davecamerini](https://www.davecamerini.it) - [info@davecamerini.it](mailto:info@davecamerini.it) 