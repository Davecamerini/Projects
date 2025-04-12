# Database Backup Downloader

A WordPress plugin that allows administrators to download a gzipped backup of the entire database.

## Features

- One-click database backup download
- Automatic GZIP compression
- Includes all database tables
- Secure download process with nonce verification
- Admin-only access

## Installation

1. Download the plugin files
2. Upload the plugin folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

1. Log in to your WordPress admin panel
2. Go to Tools > DB Backup Download
3. Click the "Download Backup" button
4. Your browser will download a .sql.gz file containing your database backup

## Security Features

- WordPress nonce verification
- Admin-only access
- Direct file access prevention
- Secure output handling

## Requirements

- WordPress 4.0 or higher
- PHP 5.6 or higher
- PHP GZip extension enabled

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please create an issue in the GitHub repository or contact the plugin author.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. 