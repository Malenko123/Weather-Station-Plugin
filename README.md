# Weather Station WordPress Plugin

A powerful and elegant WordPress plugin that displays weather stations from around the world on a beautiful, interactive Leaflet map. Features real-time weather data, a custom block, and a fully customizable sidebar interface.

![Weather Station Plugin](https://via.placeholder.com/800x400.png?text=Weather+Station+Interactive+Map+Screenshot) *// Replace with a real screenshot of your map*

---

## Features

- **Interactive World Map**: Built with Leaflet.js, offering smooth zooming and panning.
- **Real-Time Weather Data**: Fetches and displays current weather conditions from the OpenWeatherMap API.
- **Custom Post Type**: Manage all your weather stations easily from the WordPress admin (`Weather Stations`).
- **Gutenberg Block**: Easily add the interactive map to any post or page using the `Weather Map` block.
- **Smart Location Detection**: Click anywhere on the map to find the nearest weather station.
- **Elegant Sidebar**: Displays detailed weather information in a sleek, animated sidebar when a station is selected.
- **Customizable Settings**: Set your OpenWeatherMap API key, map title, and description from the WordPress settings panel.
- **Modern Development Stack**: Built with Vite, SCSS, and GSAP for smooth animations and a optimized front-end experience.

---

---

## Requirements

- WordPress 6.0+
- PHP 7.4+
- **[Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/)** (required to add latitude/longitude fields to Weather Stations)

---


## Installation

1. **Upload the Plugin**: 
   - Download the latest `weather-station-plugin.zip` file.
   - Navigate to your WordPress Admin Panel -> `Plugins` -> `Add New` -> `Upload Plugin`.
   - Upload the ZIP file and click `Install Now`, then `Activate`.

2. **Install via Composer (For Developers)**:
   - Navigate to your plugins directory: `cd wp-content/plugins/`
   - Clone the repository: `git clone <your-repo-url> weather-station-plugin`
   - Navigate into the plugin directory: `cd weather-station-plugin`
   - Install PHP dependencies: `composer install`
   - Install npm dependencies: `npm install`

3. **Configure the Plugin**:
   - Go to `Settings` -> `Weather Station`.
   - Enter your [OpenWeatherMap API Key](https://home.openweathermap.org/api_keys).
   - Configure the map's title and description as desired.

4. **Add Your First Station**:
   - Go to `Weather Stations` -> `Add New`.
   - Give the station a title (e.g., "London Weather").
   - In the provided meta boxes, enter the **Latitude** and **Longitude** of the station's location.
   - Publish the station. Its pin will now appear on the map.

5. **Add the Map to a Page**:
   - Edit any post or page.
   - Add the `Weather Map` block from the Gutenberg block inserter.
   - Publish/Update the page and view it on the front end.

---

## Development

This plugin uses a modern JavaScript build process with Vite.

### Prerequisites
- Node.js (v16 or higher)
- npm
- Composer

### Getting Started

1.  **Clone the repository** into your `wp-content/plugins/` directory.
2.  **Install dependencies**:
   ⚠️ Note: The `node_modules` and `vendor` directories are not included in the repository (they are ignored by Git).  
   Make sure to run both:
   - `npm install` to install Node dependencies (JavaScript build tools, assets).  
   - `composer install` to install PHP dependencies (WordPress coding standards, etc).

3.  **Build for Development** (with file watching and hot reload):
    ```bash
    npm run dev
    ```
4.  **Build for Production** (minified and optimized):
    ```bash
    npm run build
    ```

### File Structure

├── README.md
├── assets
│   ├── dist
│   │   ├── css
│   │   │   └── main.css
│   │   ├── images
│   │   │   └── svg
│   │   │       ├── Bookmark.svg
│   │   │       └── BookmarkFilled.svg
│   │   └── js
│   │       ├── main.js
│   │       └── map.js
│   └── src
│       ├── images
│       │   └── svg
│       │       ├── Bookmark.svg
│       │       └── BookmarkFilled.svg
│       ├── js
│       │   ├── bookmark.js
│       │   ├── main.js
│       │   └── map.js
│       └── scss
│           ├── abstracts
│           │   ├── _variables.scss
│           │   └── variables
│           │       ├── _colors.scss
│           │       ├── _general.scss
│           │       ├── _mixins.scss
│           │       └── _typography.scss
│           ├── admin.scss
│           ├── components
│           │   ├── _map.scss
│           │   └── _sidebar.scss
│           └── main.scss
├── blocks
│   └── weather-map
│       ├── block.json
│       ├── style.css
│       ├── view.php
│       └── weather-station-map-editor.js
├── composer.json
├── composer.lock
├── includes
│   ├── class-weather-station-acf.php
│   ├── class-weather-station-activator.php
│   ├── class-weather-station-admin.php
│   ├── class-weather-station-ajax.php
│   ├── class-weather-station-blocks.php
│   ├── class-weather-station-cpt.php
│   ├── class-weather-station-deactivator.php
│   ├── class-weather-station-frontend.php
│   ├── class-weather-station-i18n.php
│   ├── class-weather-station-loader.php
│   ├── class-weather-station-map.php
│   ├── class-weather-station-templates.php
│   └── class-weather-station.php
├── package-lock.json
├── package.json
├── templates
│   └── map-template.php
├── vite.config.js
└── weather-station-plugin.php


### Available NPM Scripts
- `npm run dev` – Builds assets in development mode with hot reload.
- `npm run build` – Builds assets for production.
- `npm run lint` – Lints JavaScript and CSS code.

### Available Composer Scripts
- `composer run lint` – Checks PHP code against WordPress coding standards.
- `composer run lint-fix` – Attempts to automatically fix PHP coding standards issues.

---

## Usage

### The Map
- **View Stations**: All published Weather Stations are automatically plotted on the map as pins.
- **Get Weather Info**: Click on any station's pin to open the sidebar and view its current weather data.
- **Find Nearest Station**: Click on any empty space on the map to automatically select and display the weather for the nearest station.

## The Admin Area

### Adding a New Weather Station
1. Go to `Weather Stations` → `Add New`.
2. Enter a station title (e.g., "Paris Downtown").
3. Enter the **Latitude** and **Longitude** values in the station meta box.
4. Publish the station — it will automatically appear on the map.
- **Managing Stations**: Manage your weather stations like any other post under the `Weather Stations` menu.
- **Global Settings**: Configure the API key and default text in `Settings` -> `Weather Station`.

---

## Frequently Asked Questions

### Where do I get an OpenWeatherMap API key?
1. Sign up for a free account at [https://openweathermap.org/](https://openweathermap.org/).
2. Navigate to your [API Keys](https://home.openweathermap.org/api_keys) section.
3. Copy the default key or generate a new one and paste it into the plugin's settings.

### The map is showing but the pins are missing. What's wrong?
1. Ensure you have published at least one "Weather Station" with valid latitude and longitude.
2. Check that your API key is correctly entered in the settings and that it is active on OpenWeatherMap.

### Can I customize the look of the map?
Yes. The styles are written in SCSS. You can modify the files in `assets/src/scss/` and then run `npm run build` to compile your changes.

---

## Support

If you encounter any bugs or have suggestions for improvements, please open an issue on the [Malenko123](https://github.com/Malenko123).

---

## Changelog

### 1.0.0
*   Initial release.
*   Features: Interactive map, OpenWeatherMap integration, Gutenberg block, Custom Post Type, Admin settings.

---

## Credits

- **Leaflet.js**: For the powerful and lightweight mapping library.
- **OpenWeatherMap**: For providing the global weather data API.
- **GSAP**: For the smooth, high-performance animations on the frontend.
- **Vite**: For the fast and modern build tooling.