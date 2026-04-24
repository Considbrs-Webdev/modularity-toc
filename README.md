# Modularity Table of Contents

A Modularity module that automatically generates a dynamic, linked table of contents based on headings found on the page. Features a mobile-optimized drawer interface and optional sticky sidebar positioning.

## Description

This plugin provides a Table of Contents module for the Modularity plugin ecosystem. It automatically scans the page content and generates a navigable list of headings, making it easier for users to quickly jump to different sections of long-form content.

## Features

- **Automatic Heading Detection**: Automatically finds and lists headings from the page content
- **Customizable Heading Levels**: Choose which heading levels (H2, H3, H4) to include in the table of contents
- **Multiple Container Support**: Scans specific content areas including:
  - Sidebar content areas
  - Article content
  - Main content area
- **Card Display Option**: Option to display the TOC inside a styled card component
- **Smart Card Filtering**: Option to ignore headings inside cards except for card headers
- **Smooth Navigation**: Clickable links that navigate smoothly to each section
- **Active State Tracking**: Highlights the current section as users scroll through the page
- **Configurable Mobile Style**: Choose between a collapsible dropdown or always-expanded layout on mobile
- **Per-instance Visibility Control**: Hide the TOC on mobile, desktop, or both per module instance
- **Sliding Track Indicator**: Visual indicator that follows the active section
- **Sticky Sidebar Support**: Option to make the entire sidebar column sticky when TOC is present
- **Block Compatible**: Works as a native Gutenberg block with editable title
- **Extensible via PHP Filters**: Add custom sidebar containers and choices without modifying the plugin
- **Responsive Design**: Works seamlessly across all device sizes with dedicated mobile and desktop UX

## Configuration Options

### Module Instance Settings

Configure these settings for each individual TOC module instance:

1. **Include headings from following sidebars**:

- **Content area (above article)**
- **Content area (below article)**
- **Main container bottom**

2. **Which heading levels to include**: Choose from H2, H3, and/or H4 headings (default: H2)
3. **Place in card**: Toggle to display the TOC within a card component
4. **Ignore card sub-headers**: Option to exclude headings inside cards (except card headers)
5. **Hide on mobile**: Hide this TOC instance on mobile devices (below 78em / ~1248px)
6. **Hide on desktop**: Hide this TOC instance on desktop (78em and above)

### Global Settings

Configure these settings once for all TOC modules (found in Settings > Table of Contents):

1. **Show sliding track**: Display a visual sliding track indicator that follows the active section (default: enabled)
2. **Mobile style**: How the TOC is displayed on mobile — `dropdown` (collapsible, default) or `expanded` (always visible)
3. **Sticky list**: Make the entire sidebar column sticky when it contains a TOC module

## CSS Variables

The module uses the following CSS custom properties that can be customized in your theme:

### Sliding Track

- `--toc-border-width`: Width of the track border line (default: 4px)
- `--toc-track-width`: Width of the background track line (default: 4px)
- `--toc-track-indicator`: Color of the active sliding indicator (default: #666)
- `--toc-track-color`: Color of the background track (default: #ccc)
- `--toc-track-top`: Calculated top position of the sliding indicator (set by JS)
- `--toc-track-height`: Calculated height of the sliding indicator (set by JS)

### Links

- `--toc-link-color`: Color of TOC links (default: `var(--color-link, #000)`)
- `--toc-link-color-hover`: Color of TOC links on hover (default: `var(--color-link-hover, #333)`)
- `--toc-link-font-size`: Font size of TOC links (default: `inherit`)
- `--toc-link-font-weight`: Font weight of TOC links (default: `normal`)
- `--toc-link-decoration`: Text decoration of TOC links (default: `none`)

### Mobile Dropdown

- `--toc-mobile-border-color`: Border color of the mobile TOC container (default: #d6d6d6)
- `--toc-mobile-background`: Background color of the mobile TOC container (default: #fff)
- `--toc-mobile-color`: Text color inside the mobile TOC container (default: `inherit`)

### General

- `--base`: Base spacing unit (default: 8px)

## Example CSS Customization

```css
/* Customize TOC link appearance */
.modularity-mod-toc {
  --toc-link-color: #0073aa;
  --toc-link-color-hover: #005a87;
  --toc-link-font-weight: 500;
}

/* Customize sliding track appearance */
.modularity-mod-toc {
  --toc-border-width: 3px;
  --toc-track-indicator: #0073aa;
  --toc-track-color: #e0e0e0;
}

/* Customize mobile TOC container */
.modularity-mod-toc {
  --toc-mobile-background: #f5f5f5;
  --toc-mobile-border-color: #ccc;
}
```

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

## Mobile Style

Below the 78em breakpoint, the TOC switches to a mobile layout. The style can be configured globally:

- **Dropdown** (default): The TOC content is collapsed behind a toggle button. Clicking it expands/collapses the list in place.
- **Expanded**: The TOC content is always visible on mobile, without a toggle.

Per-instance, you can also hide the TOC entirely on mobile or desktop using the **Hide on mobile** / **Hide on desktop** instance settings.

## PHP Filters

The plugin exposes two filters for extending sidebar support without modifying the plugin:

### `Modularity/Module/TableOfContents/SidebarChoices`

Add custom choices to the "Include headings from following sidebars" ACF field:

```php
add_filter('Modularity/Module/TableOfContents/SidebarChoices', function (array $choices): array {
    $choices['my-custom-sidebar'] = __('My Custom Sidebar', 'my-theme');
    return $choices;
});
```

### `Modularity/Module/TableOfContents/SidebarSelectorMap`

Map custom sidebar keys to their CSS selectors so the JS knows where to scan for headings:

```php
add_filter('Modularity/Module/TableOfContents/SidebarSelectorMap', function (array $map): array {
    $map['my-custom-sidebar'] = '#my-custom-sidebar-element';
    return $map;
});
```

Both filters must be used together for a custom sidebar to appear in the field and be scanned for headings.

## Development

### Build Assets

```bash
npm install
npm run build
```

### Development Mode

```bash
npm run dev
```

### Watch Mode (rebuild on changes)

```bash
npm run watch
```

### Build Development Version (unminified)

```bash
npm run build:dev
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- **Author**: Consid Borås AB
- **Repository**: [considbrs-webdev/modularity-toc](https://github.com/considbrs-webdev/modularity-toc)
