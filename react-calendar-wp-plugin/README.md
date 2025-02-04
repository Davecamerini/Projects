# React Calendar WP Plugin

## Overview

The React Calendar WP Plugin is a WordPress plugin that integrates a calendar feature into your WordPress site. It allows users to add, edit, and manage events easily. Built with React, this plugin leverages modern web technologies to provide a seamless user experience.

## Features

- **Event Management**: Add, edit, and delete events with ease.
- **Responsive Design**: The calendar is fully responsive and works on all devices.
- **Customizable**: Easily customize the appearance and functionality to fit your needs.
- **Integration with WordPress**: Seamlessly integrates with WordPress, utilizing its built-in features and functionalities.

## Installation

1. **Download the Plugin**: Clone or download the repository.
   ```bash
   git clone https://github.com/yourusername/react-calendar-wp-plugin.git
   ```

2. **Upload to WordPress**: Upload the plugin folder to the `/wp-content/plugins/` directory.

3. **Activate the Plugin**: Go to the WordPress admin panel, navigate to the "Plugins" section, and activate the "React Calendar WP Plugin".

4. **Configure Settings**: After activation, configure the plugin settings as needed.

## Usage

- **Adding Events**: Navigate to the calendar section in the WordPress admin panel and use the provided form to add new events.
- **Editing Events**: Click on an event to edit its details.
- **Deleting Events**: Use the delete option to remove events from the calendar.

## Development

### Prerequisites

- Node.js
- npm (Node Package Manager)

### Setup

1. **Install Dependencies**: Navigate to the plugin directory and install the required dependencies.
   ```bash
   npm install
   ```

2. **Run the Development Server**: Start the development server to see changes in real-time.
   ```bash
   npm start
   ```

### Build

To create a production build of the plugin, run:

```bash
npm run build
```

## File Structure

- `admin/`: Contains the admin page and related functionalities.
- `build/`: Contains the production build files.
- `public/`: Contains public assets like HTML, manifest, and robots.txt.
- `src/`: Contains the source code for the React components.
- `package.json`: Lists the dependencies and scripts for the project.
- `craco.config.js`: Configuration for Create React App customization.

## Contributing

Contributions are welcome! If you have suggestions for improvements or new features, please open an issue or submit a pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For any inquiries or support, please contact [your-email@example.com](mailto:your-email@example.com).
