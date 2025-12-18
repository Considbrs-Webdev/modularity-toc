# Modularity Table of Contents

A Modularity module that automatically generates a dynamic, linked table of contents based on headings found on the page.

## Description

This plugin provides a Table of Contents module for the Modularity plugin ecosystem. It automatically scans the page content and generates a navigable list of headings, making it easier for users to quickly jump to different sections of long-form content.

## Features

- **Automatic Heading Detection**: Automatically finds and lists headings from the page content
- **Customizable Heading Levels**: Choose which heading levels (H2, H3, H4) to include in the table of contents
- **Multiple Container Support**: Scans specific content areas including:
  - Sidebar content areas (top, bottom)
  - Article content
  - Main content area
- **Card Display Option**: Option to display the TOC inside a styled card component
- **Smart Card Filtering**: Option to ignore headings inside cards except for card headers
- **Smooth Navigation**: Clickable links that navigate smoothly to each section
- **Active State Tracking**: Highlights the current section as users scroll through the page
- **Responsive Design**: Works seamlessly across all device sizes

## Configuration Options

The module includes the following Advanced Custom Fields (ACF) settings:

1. **Include headings from following sidebars**: Select which sidebars to scan for headings (main container is always included)
2. **Which heading levels to include**: Choose from H2, H3, and/or H4 headings
3. **Place in card**: Toggle to display the TOC within a card component
4. **Ignore card sub-headers**: Option to exclude headings inside cards (except card headers)

## Requirements

- WordPress
- [Modularity](https://github.com/helsingborg-stad/modularity) plugin
- [Municipio](https://github.com/helsingborg-stad/municipio) theme (v6.0.0 or higher)
- Advanced Custom Fields (ACF) Pro

## Installation

1. Clone or download this repository to your WordPress plugins directory
2. Run `composer install` to install dependencies
3. Run `npm install && npm run build` to build assets
4. Activate the plugin through the WordPress admin panel
5. The "Table of Contents" module will now be available in Modularity

## Usage

1. Edit a page or post where Modularity is enabled
2. Add a new module and select "Table of Contents"
3. Configure the module settings according to your needs
4. Publish or update the page
5. The table of contents will automatically populate based on the headings found on the page

## Development

### Build Assets

```bash
npm install
npm run build
```

### Watch for Changes

```bash
npm run dev
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- **Author**: Consid Borås AB
- **Repository**: [alingsas-kommun/modularity-toc](https://github.com/alingsas-kommun/modularity-toc)
