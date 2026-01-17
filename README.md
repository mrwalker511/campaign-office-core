# CampaignPress Core

Core functionality plugin for the CampaignPress WordPress theme. This plugin provides essential campaign management features including custom post types, volunteer management, and event management that persist across theme changes.

## Features

### Custom Post Types
- **Issues/Policy Positions** - Document your campaign's stance on key issues
- **Events** - Manage campaign events and appearances
- **Endorsements** - Showcase support from organizations and individuals
- **Team Members** - Highlight your campaign staff and leadership
- **Volunteer Opportunities** - Post volunteer positions and needs
- **Press Releases** - Publish official campaign statements

### Volunteer Management
- Volunteer signup forms with customizable fields
- Skills and interest tracking
- Availability scheduling
- Contact information management
- Admin dashboard for volunteer oversight
- CSV export functionality
- Status tracking (New, Contacted, Active)

### Event Management
- RSVP system with capacity limits
- Recurring event support (daily, weekly, bi-weekly, monthly)
- Dietary restriction collection
- Guest count tracking
- Event-specific meta fields (date, time, location, address)
- Admin dashboard for RSVP management
- CSV export for event attendees

## Installation

### From WordPress Admin

1. Download the `campaign-office-core.zip` file
2. Go to **Plugins → Add New** in your WordPress admin
3. Click **Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Click **Activate Plugin**

### Manual Installation

1. Extract the plugin ZIP file
2. Upload the `campaign-office-core` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress

### Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- CampaignPress theme (recommended but not required)

## Usage

### Custom Post Types

Once activated, you'll find new menu items in your WordPress admin:

- **Issues** - Create and manage policy positions
- **Events** - Manage campaign events
- **Endorsements** - Add endorsements
- **Team** - Manage team members
- **Volunteer Opportunities** - Post volunteer positions
- **Press Releases** - Publish statements

Each post type includes relevant custom fields and taxonomies for effective content organization.

### Volunteer Signup Form

Use the `[cp_volunteer_form]` shortcode to add a volunteer signup form to any page or post.

**Basic usage:**
```
[cp_volunteer_form]
```

**With parameters:**
```
[cp_volunteer_form opportunity_id="123" title="Join Our Team" submit_text="Sign Up Now!"]
```

**Parameters:**
- `opportunity_id` - Link signup to a specific volunteer opportunity post
- `title` - Custom form heading (default: "Volunteer Sign Up")
- `submit_text` - Custom submit button text (default: "Sign Me Up!")

### Event RSVP Form

Use the `[cp_event_rsvp]` shortcode to add an RSVP form to event pages.

**Basic usage (automatically uses current event):**
```
[cp_event_rsvp]
```

**With specific event:**
```
[cp_event_rsvp event_id="456" title="RSVP for this Rally"]
```

**Parameters:**
- `event_id` - Specific event ID (defaults to current post)
- `title` - Custom form heading (default: "RSVP for this Event")

### Managing Volunteers

1. Go to **Volunteer Opportunities → Signups**
2. View all volunteer submissions
3. Filter by status (New, Contacted, Active)
4. Search by name or email
5. Use bulk actions to update status
6. Export to CSV for external use

### Managing Event RSVPs

1. Go to **Events → RSVPs**
2. View all event RSVPs
3. Filter by specific event
4. See guest counts and dietary restrictions
5. Export to CSV for event planning

### Recurring Events

When creating an event:

1. Check "This is a recurring event"
2. Select recurrence pattern (daily, weekly, bi-weekly, monthly)
3. Set end date for recurrence
4. Save the event

The plugin will automatically generate future event instances based on your settings.

## Developer Hooks

### Actions

**campaign_office_core_loaded**
```php
add_action('campaign_office_core_loaded', function() {
    // Plugin is fully loaded
});
```

**cp_volunteer_signup_success**
```php
add_action('cp_volunteer_signup_success', function($volunteer_id, $volunteer_data) {
    // Runs after successful volunteer signup
}, 10, 2);
```

**cp_event_rsvp_success**
```php
add_action('cp_event_rsvp_success', function($rsvp_id, $rsvp_data) {
    // Runs after successful event RSVP
}, 10, 2);
```

### Filters

**campaign_office_core_features**
```php
add_filter('campaign_office_core_features', function($features) {
    // Modify available features
    $features['custom_feature'] = true;
    return $features;
});
```

## Theme Integration

The CampaignPress theme automatically detects this plugin and integrates seamlessly. If you're using a different theme:

1. The plugin works standalone with default WordPress styling
2. Custom templates can be added to your theme
3. Use `do_action('campaign_office_core_loaded')` to detect the plugin

## Data Persistence

All data created by this plugin (volunteers, RSVPs, custom posts) persists even if you:
- Switch themes
- Deactivate the plugin temporarily
- Update WordPress

**Important:** Only delete the plugin if you want to permanently remove all campaign data.

## Compatibility

- Works with any WordPress theme
- Optimized for CampaignPress theme
- Compatible with popular page builders
- REST API enabled for all post types
- Gutenberg block editor ready

## Support

- [GitHub Repository](https://github.com/mrwalker511/campaign-office-core)
- [Documentation](https://github.com/mrwalker511/campaign-office-core/wiki)
- [Issue Tracker](https://github.com/mrwalker511/campaign-office-core/issues)

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## Changelog

### 1.0.0 - 2024-12-29

**Initial Release**

- Custom post types for campaign content
- Volunteer management system
- Event management with RSVP
- Recurring event support
- CSV export functionality
- WordPress 6.4 compatibility
- Full REST API support
