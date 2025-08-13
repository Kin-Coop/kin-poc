# Advanced Log Filter

A Drupal 10 module that enhances the default log messages page with advanced filtering capabilities.

## Features

- **Date Range Filtering**: Filter log messages by specific date ranges
- **User Filtering**: Filter by user ID or username
- **Category Exclusion**: Exclude specific log categories from results
- **Text Search**: Search for specific text within log messages
- **Severity Filtering**: Filter by log message severity levels
- **Enhanced UI**: Improved interface with visual severity indicators and better responsive design

## Installation

1. Download or clone this module to your `modules/custom/` directory
2. Enable the module: `drush en advanced_log_filter`
3. Clear cache: `drush cr`

## Usage

1. Navigate to **Administration » Reports » Advanced Log Messages**
2. Use the filter form to narrow down log messages:
  - **From/To Date**: Set date range for log entries
  - **User**: Enter user ID or username to filter by specific user
  - **Severity**: Select which severity levels to include
  - **Exclude Categories**: Choose log categories to exclude from results
  - **Search in Message**: Enter text to search within log messages
3. Click **Filter** to apply filters or **Reset** to clear all filters

## Requirements

- Drupal 10
- Database Logging (dblog) module (core)

## Permissions

The module uses the existing "Access site reports" permission from the core system module. Users with this permission can access the advanced log filtering interface.

## File Structure

```
advanced_log_filter/
├── advanced_log_filter.info.yml
├── advanced_log_filter.routing.yml
├── advanced_log_filter.links.menu.yml
├── advanced_log_filter.libraries.yml
├── advanced_log_filter.permissions.yml
├── advanced_log_filter.install
├── advanced_log_filter.module
├── src/
│   ├── Controller/
│   │   └── AdvancedLogController.php
│   └── Form/
│       └── AdvancedLogFilterForm.php
├── css/
│   └── filter-form.css
├── js/
│   └── filter-form.js
└── README.md
```

## Technical Details

- **Controller**: `AdvancedLogController` handles the main page display and filtering logic
- **Form**: `AdvancedLogFilterForm` provides the filter interface
- **Database**: Uses the standard `watchdog` table with optimized queries
- **Pagination**: Supports standard Drupal pagination (50 items per page)
- **Sorting**: Supports table header sorting

## Configuration

No additional configuration is required. The module works out of the box once enabled.

## Extending

The module is designed to be easily extensible. You can:

- Add new filter types by modifying the form and controller
- Customize the display by overriding the theme functions
- Add export functionality by extending the controller

## Support

For issues or feature requests, please create an issue in your project's issue tracker.

## License

GPL-2.0+
