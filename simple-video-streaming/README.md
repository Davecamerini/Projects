# Video Streaming Plugin for WordPress

## Description

The Video Streaming Plugin for WordPress allows users to upload videos and stream them directly from their WordPress site. This plugin provides a user-friendly interface for managing video uploads and organizing them into folders, making it easy to create a video library.

## Features

- Upload videos directly from the WordPress admin dashboard.
- Organize videos into folders for better management.
- Stream videos in various formats (MP4, WebM, OGG).
- User-friendly interface with a shortcode for easy embedding.
- "Go Up" navigation for folder structure.

## Installation

1. **Download the Plugin**: Download the plugin ZIP file from the repository or clone the repository to your local machine.

2. **Upload the Plugin**:
   - Go to your WordPress admin dashboard.
   - Navigate to **Plugins > Add New**.
   - Click on **Upload Plugin** and select the downloaded ZIP file.
   - Click **Install Now** and then **Activate** the plugin.

3. **Create Upload Directory**: The plugin will automatically create an upload directory in the `wp-content/uploads/videos` folder if it does not already exist.

## Usage

1. After activating the plugin, navigate to the page where you want to display the video upload form and video list.
2. Use the shortcode `[video_streaming]` to embed the video upload form and list of videos on any page or post.
3. Users can upload videos, and they will be organized into folders based on the directory structure.

## Requirements

- WordPress 4.0 or higher
- PHP 5.6 or higher
- MySQL 5.0 or higher

## Troubleshooting

- If you encounter issues with video uploads, ensure that your server settings allow for file uploads and that the maximum file size is sufficient for your video files.
- Check the `wp-content/debug.log` file for any error messages if the plugin fails to function as expected.

## Contributing

Contributions are welcome! If you have suggestions for improvements or find bugs, please open an issue or submit a pull request.

## License

This plugin is licensed under the MIT License. See the LICENSE file for more information.

## Author

Developed by Davecamerini (https://www.davecamerini.com) - info@davecamerini.com (mailto:info@davecamerini.com)
