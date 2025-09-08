# HT Design Assets Server

A WordPress plugin that serves HT Design assets to fix 403 errors on staging sites.

## Description

This plugin intercepts requests to `/ht-design-assets/` paths and serves the corresponding CSS and JavaScript files from various possible locations within the WordPress installation.

## Features

- Handles CSS and JavaScript asset requests
- Tries multiple file paths to locate assets
- Sets appropriate content types and caching headers
- Includes error logging for debugging

## Installation

1. Upload the plugin files to `/wp-content/plugins/ht-design-assets-server/`
2. Activate the plugin through the 'Plugins' screen in WordPress

## Version

1.0

## Author

HoliThemes