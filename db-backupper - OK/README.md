# Database Backup Plugin for WordPress

## Description

The Database Backup Plugin for WordPress is a simple and effective tool that allows you to create backups of your WordPress database. This plugin enables you to download the backup as a plain SQL file, ensuring that you can easily restore your database if needed. The plugin also automatically deletes the backup file from the server after it has been successfully downloaded.

## Features

- Create a backup of your WordPress database.
- Download the backup as a plain SQL file.
- Automatically delete the backup file from the server after it has been downloaded.
- Simple and user-friendly interface in the WordPress admin dashboard.

## Installation

1. **Download the Plugin**: Download the plugin ZIP file from the repository or clone the repository to your local machine.

2. **Upload the Plugin**:
   - Go to your WordPress admin dashboard.
   - Navigate to **Plugins > Add New**.
   - Click on **Upload Plugin** and select the downloaded ZIP file.
   - Click **Install Now** and then **Activate** the plugin.

3. **Create Backup Directory**: The plugin will automatically create a `backups` directory in the plugin folder if it does not already exist.

## Usage

1. After activating the plugin, navigate to **Database Backup** in the WordPress admin menu.
2. Click the **Create Database Backup** button to generate a backup of your database.
3. Once the backup is created, a download link will appear. Click the link to download the backup as a SQL file.
4. The SQL file will be deleted from the server after it has been successfully downloaded.

## Requirements

- WordPress 4.0 or higher
- PHP 5.6 or higher
- MySQL 5.0 or higher

## Troubleshooting

- If you encounter issues with the backup file being corrupted, ensure that there is no output (like whitespace or error messages) before the backup process.
- Check the `wp-content/debug.log` file for any error messages if the backup fails to create.

## Contributing

Contributions are welcome! If you have suggestions for improvements or find bugs, please open an issue or submit a pull request.

## License

This plugin is licensed under the MIT License. See the LICENSE file for more information.

## Author

Developed by Davecamerini (https://www.davecamerini.com) - info@davecamerini.com (mailto:info@davecamerini.com)
